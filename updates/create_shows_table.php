<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

class CreateShowsTable extends Migration
{

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::create('cosmicradiotv_podcast_shows', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique('slug');
            });
        });
    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::drop('cosmicradiotv_podcast_shows');
        });
    }
}