<?php

namespace CosmicRadioTV\Podcast\updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateEpisodesTable extends Migration
{

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::create('cosmicradiotv_podcast_episodes', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->unsignedInteger('show_id');
                $table->string('title');
                $table->string('slug');
                $table->text('summary')->nullable();
                $table->text('content')->nullable();
                $table->integer('length');
                $table->dateTime('release')->nullable();
                $table->boolean('published')->default(false);
                $table->timestamps();

                $table->foreign('show_id')->references('id')->on('cosmicradiotv_podcast_shows')
                      ->onUpdate('cascade');
                $table->unique(['show_id', 'slug']);
            });
        });
    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::drop('cosmicradiotv_podcast_episodes');
        });
    }
}