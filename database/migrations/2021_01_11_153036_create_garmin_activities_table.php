<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGarminActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('garmin_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->unique();
            // title
            $table->longText('xml');
            $table->integer('fastest_1km')->default(0);
            $table->integer('fastest_5km')->default(0);
            $table->integer('fastest_10km')->default(0);
            $table->integer('fastest_21km')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('garmin_activities');
    }
}
