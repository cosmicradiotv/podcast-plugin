<?php

namespace CosmicRadioTV\Podcast\Updates;

use CosmicRadioTV\Podcast\Models\ReleaseType;
use October\Rain\Database\Updates\Seeder;

class SeedDefaultReleaseTypes extends Seeder
{

    /**
     * Add audio and video as default types
     */
    public function run()
    {
        ReleaseType::create([
            'name' => 'Audio',
            'type' => 'audio',
            'filetype' => 'audio/mpeg',
            'sort_order' => '2',
        ]);

        ReleaseType::create([
            'name' => 'Video',
            'type' => 'video',
            'filetype' => 'video/mp4',
            'sort_order' => '1',
        ]);

        ReleaseType::create([
            'name' => 'Youtube',
            'type' => 'youtube',
            'sort_order' => '0',
        ]);
    }
}