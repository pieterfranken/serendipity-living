<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddLayoutsToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'enable_layouts_download')) {
                $table->boolean('enable_layouts_download')->default(false)->after('enable_renders_download');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'enable_layouts_download')) {
                $table->dropColumn('enable_layouts_download');
            }
        });
    }
}

