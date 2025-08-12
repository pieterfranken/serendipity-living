<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSerProjectVillasTable extends Migration
{
    public function up()
    {
        Schema::create('ser_project_villas', function($table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('build_size')->nullable();
            $table->integer('plot_size')->nullable();
            $table->integer('rooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->boolean('on_request')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ser_project_villas');
    }
}

