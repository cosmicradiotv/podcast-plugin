<?php

namespace CosmicRadioTV\Podcast\updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateReleasesTable extends Migration {

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function() {
            Schema::create('cosmicradiotv_podcast_release_types', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('slug');
                $table->string('type');
                $table->string('filetype');

                $table->unique('slug');
            });

            Schema::create('cosmicradiotv_podcast_releases', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('episode_id');
                $table->unsignedInteger('release_type_id');
                $table->text('url');
                $table->bigInteger('size');
                $table->timestamps();

                $table->foreign('episode_id')->references('id')->on('cosmicradiotv_podcast_episodes')
                      ->onUpdate('cascade');
                $table->foreign('release_type_id')->references('id')->on('cosmicradiotv_podcast_release_types')
                      ->onUpdate('cascade');

            });
        });

    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::drop('cosmicradiotv_podcast_releases');
            Schema::drop('cosmicradiotv_podcast_release_types');
        });
    }
}