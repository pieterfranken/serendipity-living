<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddEnableRendersDownloadToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'enable_renders_download')) {
                $table->boolean('enable_renders_download')->nullable()->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'enable_renders_download')) {
                $table->dropColumn('enable_renders_download');
            }
        });
    }
}

