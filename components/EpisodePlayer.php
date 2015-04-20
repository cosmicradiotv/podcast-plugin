<?php namespace CosmicRadioTV\Podcast\Components;

use Request;
use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\ReleaseType;

class EpisodePlayer extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'EpisodePlayer Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'episodeSlug' => [
                'title' =>          'Episode Slug',
                'type' =>           'string',
                'default' =>        '',
                'required' =>       true,
            ],
            'releaseId' => [
                'title' =>          'Release ID',
                'description' =>    'Choose a specific release (otherwise defaults to first one).',
                'type' =>           'dropdown',
                'depends' =>        ['episode_slug'],
            ],
            'releaseType' => [
                'title' =>          'Release Type',
                'description' =>    'If no release id is set, you can use this to choose the first release of a release type.',
                'type' =>           'dropdown',
            ]
        ];
    }

    public function getReleaseIdOptions()
    {
        $episodeSlug = Request::input('episodeSlug');
        $episode = Episode::where('slug', '=', $episodeSlug)->firstOrFail();

        return Release::where('episode_id', '=', $episode->id)->lists('url','id');
    }

    public function release()
    {
        $release = null;
        if (trim($this->property('releaseId')) != false) {
            $release = Release::where('id', '=', trim($this->property('releaseId')))->firstOrFail();
        } else {
            $episode = Episode::where('slug','=',trim($this->property('episodeSlug')))->firstOrFail();
            $release_query = Release::where('episode_id','=',$episode->id);
            if (trim($this->property('releaseType')) != false) {
                $release_query = $release_query->where('release_type_id','=',trim($this->property('releaseType')));
            }
            $release = $release_query->firstOrFail();
        }
        return $release;
    }

    public function releaseType()
    {
        $release = $this->release();
        return ReleaseType::where('id','=',$release->release_type_id)->firstOrFail();
    }

    public function onRun()
    {
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/vendor/jquery/js/jquery.min.js');
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/vendor/mediaelement/js/mediaelement-and-player.min.js');
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/js/podcast.js');

        $this->addCss('/plugins/cosmicradiotv/podcast/assets/vendor/mediaelement/css/mediaelementplayer.css');
    }
}