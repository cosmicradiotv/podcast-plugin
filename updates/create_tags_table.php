<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateTagsTable extends Migration
{

    public function up()
    {
        DB::transaction(function () {
            Schema::create('cosmicradiotv_podcast_tags', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('slug');
                $table->timestamps();

                $table->unique('slug');
            });

            Schema::create('cosmicradiotv_podcast_episodes_tags', function (Blueprint $table) {
                $table->unsignedInteger('episode_id');
                $table->unsignedInteger('tag_id');

                $table->primary(['episode_id', 'tag_id']);

                $table->foreign('episode_id')->references('id')->on('cosmicradiotv_podcast_episodes')
                      ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('tag_id')->references('id')->on('cosmicradiotv_podcast_tags')
                      ->onUpdate('cascade')->onDelete('cascade');
            });
        });
    }

    public function down()
    {
        DB::transaction(function () {
            Schema::drop('cosmicradiotv_podcast_episodes_tags');
            Schema::drop('cosmicradiotv_podcast_tags');
        });

    }

}