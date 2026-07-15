<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddIsSoldToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'is_sold')) {
                $table->boolean('is_sold')->default(false)->after('delivery_date');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'is_sold')) {
                $table->dropColumn('is_sold');
            }
        });
    }
}
