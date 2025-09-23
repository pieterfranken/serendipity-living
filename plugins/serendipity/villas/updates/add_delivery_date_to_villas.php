<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddDeliveryDateToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'delivery_date')) {
                $table->string('delivery_date')->nullable()->after('currency');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'delivery_date')) {
                $table->dropColumn('delivery_date');
            }
        });
    }
}

