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
        ]);

        ReleaseType::create([
            'name' => 'Video',
            'type' => 'video',
            'filetype' => 'video/mp4',
        ]);

        ReleaseType::create([
            'name' => 'Youtube',
            'type' => 'youtube',
        ]);
    }
}