<?php

namespace App\Http\Livewire;

use App\Models\GarminActivity;
use Livewire\Component;

class Counter extends Component
{
	public $count = 0;
	public $speed = '00:00';

	public function fastest1km()
    {
        $activity = GarminActivity::where('fastest_1km', '!=', 0)->orderBy('fastest_1km', 'asc')->first();

        $this->speed = $activity ? $activity->formatTime($activity->fastest_1km) : 'Not set yet';
    }

    public function fastest5km()
    {
        $activity = GarminActivity::where('fastest_5km', '!=', 0)->orderBy('fastest_5km', 'asc')->first();

        $this->speed = $activity ? $activity->formatTime($activity->fastest_5km) : 'Not set yet';
    }

    public function fastest10km()
    {
        $activity = GarminActivity::where('fastest_10km', '!=', 0)->orderBy('fastest_10km', 'asc')->first();

        $this->speed = $activity ? $activity->formatTime($activity->fastest_10km) : 'Not set yet';
    }

    public function fastest21km()
    {
        $activity = GarminActivity::where('fastest_21km', '!=', 0)->orderBy('fastest_21km', 'asc')->first();

        $this->speed = $activity ? $activity->formatTime($activity->fastest_21km) : 'Not set yet';
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
