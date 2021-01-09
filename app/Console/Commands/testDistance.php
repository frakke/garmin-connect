<?php

namespace App\Console\Commands;

use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Console\Command;

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

        return 0;
    }
}
