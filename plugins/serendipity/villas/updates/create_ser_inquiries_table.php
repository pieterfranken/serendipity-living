<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSerInquiriesTable extends Migration
{
    public function up()
    {
        Schema::create('ser_inquiries', function($table) {
            $table->increments('id');
            $table->integer('villa_id')->nullable()->index();
            $table->string('villa_title')->nullable();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->string('source_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ser_inquiries');
    }
}

