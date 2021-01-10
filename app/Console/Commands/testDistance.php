<?php

namespace App\Console\Commands;

use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Console\Command;
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

        $filePath = storage_path('app/public/garminConnect/activity_6078298164.tcx');
        $xml = XmlParser::load($filePath);
        //var_dump($xml->getContent());
        $tracks = $xml->parse([
            'Author' => ['uses' => 'Author.Name'],
            'id' => ['uses' => 'Activities.Activity.Id'],
            /*'time' => ['uses' => 'Activities.Activity.Lap.Track.Trackpoint.Time'],
            'distance' => ['uses' => 'Activities.Activity.Lap.Track.Trackpoint.DistanceMeters'],
            'tracks' => [
                'uses' => 'Activities.Activity.Lap.Track',
                'track' => ['uses' => 'Trackpoint.DistanceMeters'],
            ],*/
            'points' => ['uses' => 'Activities.Activity.Lap.Track.Trackpoint[Time>time,DistanceMeters>distance]'],
        ]);


        //var_dump($tracks);

        $lapDistance = 1000;
        $currentInterval = 0;
        $lastTime = 0;

        $leftOverTime = 0;
        $leftOverDistance = 0;

        $lastDistance = 0;

        $allSegments = [];

        $laps = [];
        $key = 0;
        foreach ($tracks['points'] as $track) {
            $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.vP', $track['time']);
            $time = (double) ($date->getTimestamp().','.$date->format('u'));


            $distance = (double) $track['distance'];

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

            //echo $distanceSegment . " ";

            // Skip standing still.
            if ($distanceSegment < 4) {
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
            $this->formatTime($this->getFastestLap($allSegments, 10000))
        );

        return 0;
    }

    private function formatTime($seconds)
    {
        return sprintf('%s:%s',
            str_pad(floor($seconds / 60), 2, 0, STR_PAD_LEFT),
            str_pad(floor($seconds % 60), 2, 0, STR_PAD_RIGHT),
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
