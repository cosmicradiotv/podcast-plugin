<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddStandardRssFields extends Migration
{

    /**
     * Migration
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::table('cosmicradiotv_podcast_shows', function (Blueprint $table) {
                $table->string('feed_language')->default('en-us')->after('description');
                $table->string('feed_copyright')->nullable()->after('feed_language');
                $table->string('feed_author')->nullable()->after('feed_copyright');
                $table->text('itunes_category')->nullable()->after('feed_author');
                $table->boolean('itunes_explicit')->nullable()->after('itunes_category');
                $table->string('itunes_owner_name')->nullable()->after('itunes_explicit');
                $table->string('itunes_owner_email')->nullable()->after('itunes_owner_name');
            });
            Schema::table('cosmicradiotv_podcast_episodes', function (Blueprint $table) {
                $table->boolean('itunes_explicit')->nullable()->after('published');
            });
        });
    }

    /**
     * Rollback
     */
    public function down()
    {
        DB::transaction(function () {
            Schema::table('cosmicradiotv_podcast_shows', function (Blueprint $table) {
                $table->dropColumn([
                    'feed_language',
                    'feed_copyright',
                    'feed_author',
                    'itunes_category',
                    'itunes_explicit',
                    'itunes_owner_name',
                    'itunes_owner_email',
                ]);
            });
            Schema::table('cosmicradiotv_podcast_episodes', function (Blueprint $table) {
                $table->dropColumn([
                    'itunes_explicit',
                ]);
            });
        });
    }

}