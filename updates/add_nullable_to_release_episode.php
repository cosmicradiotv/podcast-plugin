<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddNullableToReleaseEpisode extends Migration
{

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function () {
            // Clean up all current bindings
            \October\Rain\Database\Models\DeferredBinding::cleanUp(0);

            Schema::table('cosmicradiotv_podcast_releases', function (Blueprint $table) {
                $table->unsignedInteger('episode_id')->nullable()->change();
            });
        });
    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            // Clean up all current bindings
            \October\Rain\Database\Models\DeferredBinding::cleanUp(0);

            Schema::table('cosmicradiotv_podcast_releases', function (Blueprint $table) {
                $table->unsignedInteger('episode_id')->change();
            });
        });

    }
}