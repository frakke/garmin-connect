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
    ];
}
