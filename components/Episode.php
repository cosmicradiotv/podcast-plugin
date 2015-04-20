<?php namespace CosmicRadioTV\Podcast\Components;

use Request;
use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Classes\VideoUrlParser;

class Episode extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Episode Component',
            'description' => 'Used to display an Episode'
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
            'playerReleaseId' => [
                'title' =>          'Player Release ID',
                'description' =>    'Choose a specific release (otherwise defaults to first one).',
                'type' =>           'dropdown',
                'depends' =>        ['episode_slug'],
                'placeholder'       => 'Select Release',
            ],
            'playerReleaseType' => [
                'title' =>          'Player Release Type',
                'description' =>    'If no release id is set, you can use this to choose the first release of a release type.',
                'type' =>           'dropdown',
                'depends' =>        ['episode_slug'],
                'placeholder'       => 'Select Release Type',
            ],
            'playerWidth' => [
                'title' =>          'Player Width',
                'type' =>           'string',
                'default' =>        '640',
            ],
            'playerHeight' => [
                'title' =>          'Player Height',
                'type' =>           'string',
                'default' =>        '360',
            ]
        ];
    }

    public function getPlayerReleaseIdOptions()
    {
        $episodeSlug = Request::input('episodeSlug');
        $episode = Models\Episode::where('slug', '=', $episodeSlug)->with('releases')->get()->first();
        
        if (!$episode->relations['releases']->isEmpty()) {
            return $episode->relations['releases']->lists('url','id');
        }
    }

    public function getPlayerReleaseTypeOptions()
    {
        return ReleaseType::all()->lists('name','slug');
    }

    public function playerRelease()
    {
        if (trim($this->property('playerReleaseId')) !== '') {
            return Release::where('id', '=', trim($this->property('playerReleaseId')))->get()->first();
        } else {
            $episode = Models\Episode::where('slug','=',trim($this->property('episodeSlug')))->with(['releases' => function($q) {
                if (trim($this->property('playerReleaseType')) !== '') {
                    $q->whereHas('release_type', function($rt_q) {
                        $rt_q->where('slug','=',trim($this->property('playerReleaseType')));
                    });
                }
            }])->get()->first();

            return $episode->relations['releases']->first();
        }
    }

    public function playerReleaseType()
    {
        $release = $this->playerRelease();
        return ReleaseType::where('id','=',$release->release_type_id)->get()->first();
    }

    /**
     * Gets the embed url from a youtube url.
     * Uses https://gist.github.com/astockwell/11055104
     * @return string Youtube embed url
     */
    public function playerYoutubeEmbedUrl() {
        $release = $this->playerRelease();
        $releaseType = $this->playerReleaseType();
        if ($releaseType->type == "youtube") {
            return VideoURLParser::get_url_embed($release->url);
        }
    }
}