<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddThumbnailUrlToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            $table->string('thumbnail_url', 1024)->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            $table->dropColumn('thumbnail_url');
        });
    }
}

