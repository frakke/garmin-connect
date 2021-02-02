<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarminActivity extends Model
{
    use HasFactory;

    public $fillable = [
    	'activity_id',
    	'xml',
    	'fastest_1km',
    	'fastest_5km',
    	'fastest_10km',
    	'fastest_21km',
    ];

    public function formatTime($seconds): string
    {
        return sprintf('%s:%s',
            str_pad(floor($seconds / 60), 2, 0, STR_PAD_LEFT),
            str_pad(floor($seconds % 60), 2, 0, STR_PAD_LEFT),
        );
    }
}
