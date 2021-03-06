<?php

namespace App\Console\Commands;

use App\Models\GarminActivity;
use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Orchestra\Parser\Xml\Facade as XmlParser;

class testDistance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xxx:test-distance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // https://github.com/allebb/distical

        // Set our Lat/Long coordinates
        $ipswich = new LatLong(52.057941, 1.147172);
        $london = new LatLong(51.507608, -0.127822);

        // Get the distance between these two Lat/Long coordinates...
        $distanceCalculator = new DistanceCalculator($ipswich, $london);

        // You can then compute the distance...
        $distance = $distanceCalculator->get();
        // you can also chain these methods together eg. $distanceCalculator->get()->asMiles();

        // We can now output the miles using the asMiles() method, you can also calculate and use asKilometres() or asNauticalMiles() as required!
        echo 'Distance in miles between Central Ipswich and Central London is: ' . $distance->asKilometres() . "\n";

        $start = new LatLong(55.66575192846358, 12.521814834326506);
        $end = new LatLong(55.66573290154338, 12.521811733022332);

        $distanceCalculator = new DistanceCalculator($start, $end);
        $distance = $distanceCalculator->get();
        echo 'Distance between start and end is: ' . ($distance->asKilometres() * 1000) . "\n";

        // Exported data file:
        // storage/app/public/garminConnect/activity_6078298164.tcx
        // https://connect.garmin.com/modern/activities
        //   .activity-list-page-wrapper li .activity-name-edit a
        // https://connect.garmin.com/modern/proxy/download-service/export/tcx/activity/6069506796

        $files = Storage::disk('public')->files('garminConnect');
        $files = array_filter($files, function ($filename) {
            return strpos($filename, '.tcx') !== false;
        });

        $this->import($files);

        return;

        GarminActivity::all()->each(function ($garminActivity) {
            $xml = XmlParser::extract($garminActivity->xml);
            $tracks = $xml->parse([
                'Author' => ['uses' => 'Author.Name'],
                'date' => ['uses' => 'Activities.Activity.Id'],
                'data' => ['uses' => 'Activities.Activity.Lap[Calories,Track{Trackpoint{Time>time,DistanceMeters>distance}>trackpoints}>laps]'],
            ]);

            $this->traverseLaps($tracks);
        });

        return;

        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file);

            if (!file_exists($filePath)) {
                continue;
            }
            
            $xml = XmlParser::load($filePath);
            $tracks = $xml->parse([
                'Author' => ['uses' => 'Author.Name'],
                'date' => ['uses' => 'Activities.Activity.Id'],
                'data' => ['uses' => 'Activities.Activity.Lap[Calories,Track{Trackpoint{Time>time,DistanceMeters>distance}>trackpoints}>laps]'],
            ]);

            $this->traverseLaps($tracks);
        }
    }

    /**
     * Create GarminActivities from file paths.
     * 
     * @param array $files
     * @return void
     */
    private function import(array $files): void
    {
        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file);

            if (!file_exists($filePath)) {
                continue;
            }

            $matches = [];
            preg_match('/activity_(\d+)\.tcx$/', $file, $matches);
            if (empty($matches[1])) {
                continue;
            }

            $activityId = $matches[1];

            if (GarminActivity::where('activity_id', $activityId)->exists()) {
                continue;
            }

            $xml = file_get_contents($filePath);

            if (!$xml) {
                continue;
            }

            $laps = $this->unpackLaps($xml);
            $data = $this->traverseLaps($laps);

            GarminActivity::create([
                'activity_id' => $activityId,
                'xml' => $xml,
                'fastest_1km' => $data['fastest_1km'],
                'fastest_5km' => $data['fastest_5km'],
                'fastest_10km' => $data['fastest_10km'],
                'fastest_21km' => $data['fastest_21km'],
            ]);
        }
    }

    private function unpackLaps(string $xmlString): array
    {
        $xml = XmlParser::extract($xmlString);
        $laps = $xml->parse([
            'Author' => ['uses' => 'Author.Name'],
            'date' => ['uses' => 'Activities.Activity.Id'],
            'data' => ['uses' => 'Activities.Activity.Lap[Calories,Track{Trackpoint{Time>time,DistanceMeters>distance}>trackpoints}>laps]'],
        ]);

        return $laps;
    }

    private function traverseLaps($tracks): array
    {   
        $return = [
            'fastest_1km' => 0,
            'fastest_5km' => 0,
            'fastest_10km' => 0,
            'fastest_21km' => 0,
        ];
        $lapDistance = 1000;
        $currentInterval = 0;
        $lastTime = 0;

        $leftOverTime = 0;
        $leftOverDistance = 0;

        $lastDistance = 0;

        $allSegments = [];

        $laps = [];
        $key = 0;
        foreach ($tracks['data'] as $track) {
            foreach ($track['laps'] as $trackpoints) {
                foreach ($trackpoints['trackpoints'] as $trackpoint) {
                    $date = DateTime::createFromFormat('Y-m-d\TH:i:s.vP', $trackpoint['time']);
                    $time = (double) ($date->getTimestamp().','.$date->format('u'));


                    $distance = (double) $trackpoint['distance'];

                    // Starting point.
                    if (count($laps) == 0) {
                        $laps[$key] = [
                            'time' => 0,
                            'distance' => $distance,
                        ];

                        $lastDistance = $distance;
                        $lastTime = $time;

                        continue;
                    }

                    $distanceSegment = $distance - $lastDistance;
                    $timeSegment = $time - $lastTime;

                    // Skip standing still.
                    if ($distanceSegment < 2) {
                        $lastDistance = $distance;
                        $lastTime = $time;

                        continue;
                    }

                    $allSegments[] = [
                        'time' => $timeSegment,
                        'distance' => $distanceSegment,
                    ];

                    if ($laps[$key]['distance'] + $distanceSegment > $lapDistance) {
                        $leftOverDistance = fmod($laps[$key]['distance'] + $distanceSegment, $lapDistance);
                        $leftOverTime = $timeSegment * $leftOverDistance / $distanceSegment;

                        $laps[$key]['time'] += $timeSegment - $leftOverTime;
                        $laps[$key]['distance'] += $distanceSegment - $leftOverDistance;

                        $key++;

                        $laps[$key] = [
                            'time' => $leftOverTime,
                            'distance' => $leftOverDistance,
                        ];

                        $leftOverTime = 0;
                        $leftOverDistance = 0;

                        $lastDistance = $distance;
                        $lastTime = $time;

                        continue;
                    }

                    $laps[$key]['time'] += $timeSegment;
                    $laps[$key]['distance'] += $distanceSegment;
                    
                    $lastDistance = $distance;
                    $lastTime = $time;
                }
            }
        }

        echo str_pad('', 43, '=') . "\n";
        
        $date = DateTime::createFromFormat('Y-m-d\TH:i:s.vP', $tracks['date']);
        echo sprintf("%s\n", $date->format('D j. F Y'));

        echo str_pad('', 43, '-') . "\n";
        echo sprintf("%s %s %s %s\n",
            str_pad('Lap', 10, ' '),
            str_pad('Time', 10, ' '),
            str_pad('Distance', 10, ' '),
            str_pad('km/t', 10, ' '),
        );
        foreach ($laps as $key => $lap) {
            echo str_pad('', 43, '-') . "\n";

            $speed = round(3600 * $lap['distance'] / $lap['time'] / 1000, 2);
            echo sprintf("%s %s %s %skm/t\n",
                str_pad($key + 1, 10, ' '),
                str_pad($this->formatTime($lap['time']), 10, ' '),
                str_pad(round($lap['distance']), 10, ' '),
                $speed,
            );
        }
        echo str_pad('', 43, '-') . "\n";

        echo sprintf("Fastest 1km: %s\nFastest 5km: %s\nFastest 10km: %s\n",
            $this->formatTime($this->getFastestLap($allSegments, 1000)),
            $this->formatTime($this->getFastestLap($allSegments, 5000)),
            $this->formatTime($this->getFastestLap($allSegments, 10000)),
            $this->formatTime($this->getFastestLap($allSegments, 21000))
        );

        $return = [
            'fastest_1km' => (int) $this->getFastestLap($allSegments, 1000),
            'fastest_5km' => (int) $this->getFastestLap($allSegments, 5000),
            'fastest_10km' => (int) $this->getFastestLap($allSegments, 10000),
            'fastest_21km' => (int) $this->getFastestLap($allSegments, 21097),
        ];

        return $return;
    }

    private function formatTime($seconds)
    {
        return sprintf('%s:%s',
            str_pad(floor($seconds / 60), 2, 0, STR_PAD_LEFT),
            str_pad(floor($seconds % 60), 2, 0, STR_PAD_LEFT),
        );
    }

    private function getFastestLap($segments, $lapDistance)
    {
        $times = [];
        $distances = [];
        $fastestSpeed = null;
        //array_sum()
        //
        foreach ($segments as $segment) {
            $times[] = $segment['time'];
            $distances[] = $segment['distance'];

            if (array_sum($distances) > $lapDistance) {
                while (array_sum($distances) > $lapDistance) {
                    array_shift($times);
                    array_shift($distances);
                }

                // Adjust for distances in segments being smaller than the target distance by taking the
                // average speed by measured distance and multiply it up.
                $speed = array_sum($times) * ($lapDistance / array_sum($distances));
                $speed = array_sum($times);
                if (is_null($fastestSpeed)) {
                    $fastestSpeed = $speed;
                }
                else {
                    $fastestSpeed = min($fastestSpeed, $speed);
                }
            }
        }

        return $fastestSpeed;
    }

    //private 
}
