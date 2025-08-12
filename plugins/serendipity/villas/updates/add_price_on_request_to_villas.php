<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddPriceOnRequestToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'price_on_request')) {
                $table->boolean('price_on_request')->default(false)->after('price');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'price_on_request')) {
                $table->dropColumn('price_on_request');
            }
        });
    }
}

