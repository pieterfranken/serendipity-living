<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddFeaturedToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'featured_in_catalog')) {
                $table->boolean('featured_in_catalog')->default(false)->after('visible_in_catalog');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'featured_in_catalog')) {
                $table->dropColumn('featured_in_catalog');
            }
        });
    }
}

