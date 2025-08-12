<?php namespace Serendipity\Villas\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddProjectAndVisibilityToVillas extends Migration
{
    public function up()
    {
        Schema::table('ser_villas', function($table) {
            if (!Schema::hasColumn('ser_villas', 'project_id')) {
                $table->integer('project_id')->unsigned()->nullable()->after('id');
            }
            if (!Schema::hasColumn('ser_villas', 'visible_in_catalog')) {
                $table->boolean('visible_in_catalog')->default(true)->after('thumbnail_url');
            }
        });
    }

    public function down()
    {
        Schema::table('ser_villas', function($table) {
            if (Schema::hasColumn('ser_villas', 'project_id')) {
                $table->dropColumn('project_id');
            }
            if (Schema::hasColumn('ser_villas', 'visible_in_catalog')) {
                $table->dropColumn('visible_in_catalog');
            }
        });
    }
}

