<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSerVillasTable extends Migration
{
    public function up()
    {
        Schema::create('ser_villas', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->decimal('price', 14, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('interior_area_m2')->nullable();
            $table->integer('plot_area_m2')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ser_villas');
    }
}

