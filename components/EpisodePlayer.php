<?php namespace CosmicRadioTV\Podcast\Components;

use Request;
use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Classes\VideoUrlParser;

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
                'placeholder'       => 'Select Release',
            ],
            'releaseType' => [
                'title' =>          'Release Type',
                'description' =>    'If no release id is set, you can use this to choose the first release of a release type.',
                'type' =>           'dropdown',
                'depends' =>        ['episode_slug'],
                'placeholder'       => 'Select Release Type',
            ],
            'width' => [
                'title' =>          'Width',
                'type' =>           'string',
                'default' =>        '640',
            ],
            'height' => [
                'title' =>          'Height',
                'type' =>           'string',
                'default' =>        '360',
            ]
        ];
    }

    public function getReleaseIdOptions()
    {
        $episodeSlug = Request::input('episodeSlug');
        $episode = Episode::where('slug', '=', $episodeSlug)->take(1)->get()->first();
        if (!empty($episode)) {
            return Release::where('episode_id', '=', $episode->id)->lists('url','id');
        } else {
            return [];
        }
    }

    public function getReleaseTypeOptions()
    {
        return ReleaseType::all()->lists('name','slug');
    }

    public function release()
    {
        $release_query = null;
        if (trim($this->property('releaseId')) !== '') {
            $release_query = Release::where('id', '=', trim($this->property('releaseId')));
        } else {
            $episode = Episode::where('slug','=',trim($this->property('episodeSlug')))->take(1)->get()->first();
            if (!empty($episode)) {
                $release_query = Release::where('episode_id','=',$episode->id);
                if (trim($this->property('releaseType')) !== '') {
                    $release_type = ReleaseType::where('slug','=',trim($this->property('releaseType')))->take(1)->get()->first();

                    if (!empty($release_type)) {
                        $release_query = $release_query->where('release_type_id','=',$release_type->id);
                    }
                }
            }
        }
        if (!empty($release_query)) {
            return $release_query->take(1)->get()->first();;
        }
    }

    public function releaseType()
    {
        $release = $this->release();
        return ReleaseType::where('id','=',$release->release_type_id)->take(1)->get()->first();
    }

    /**
     * Gets the embed url from a youtube url.
     * Uses https://gist.github.com/astockwell/11055104
     * @return string Youtube embed url
     */
    public function youtubeEmbedUrl() {
        $release = $this->release();
        $releaseType = $this->releaseType();
        if ($releaseType->type == "youtube") {
            return VideoURLParser::get_url_embed($release->url);
        }
    }
}