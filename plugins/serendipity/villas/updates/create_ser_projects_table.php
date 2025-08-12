<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSerProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('ser_projects', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('is_previous')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ser_projects');
    }
}

