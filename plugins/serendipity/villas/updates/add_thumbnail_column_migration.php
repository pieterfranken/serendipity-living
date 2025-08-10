<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddThumbnailColumnMigration extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'thumbnail_url')) {
                $table->string('thumbnail_url')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'thumbnail_url')) {
                $table->dropColumn('thumbnail_url');
            }
        });
    }
}

