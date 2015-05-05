<?php

namespace CosmicRadioTV\Podcast\Updates;

use DB;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddSortOrderToReleaseTypes extends Migration
{
	/**
	 * Migration
	 */
	public function up()
	{
		DB::transaction(function() {
			Schema::table('cosmicradiotv_podcast_release_types', function (Blueprint $table) {
				$table->unsignedInteger('sort_order');
			});
		});
	}

	/**
	 * Rollback
	 */
	public function down()
	{
		DB::transaction(function() {
			Schema::table('cosmicradiotv_podcast_release_types', function (Blueprint $table) {
				$table->dropColumn([
					'sort_order',
				]);
			});
		});
	}
}