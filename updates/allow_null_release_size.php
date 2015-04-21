<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AllowNullReleaseSize extends Migration
{

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::table('cosmicradiotv_podcast_releases', function (Blueprint $table) {
                $table->bigInteger('size')->nullable()->change();
            });
        });
    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::table('cosmicradiotv_podcast_releases', function (Blueprint $table) {
                $table->bigInteger('size')->change();
            });
        });
    }
}