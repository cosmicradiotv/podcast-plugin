<?php namespace CosmicRadioTV\Podcast\Components;

use Request;
use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Classes\VideoUrlParser;

class Episode extends ComponentBase
{
    public $episode;
    public $show;
    public $tags;
    public $releases;

    public $playerRelease;
    public $playerReleaseType;
    public $playerYoutubeEmbedUrl;

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

    /**
     * Runs when the page or layout loads (sets up properties available to the component partial)
     */
    public function onRun()
    {
        $episodeSlug = trim($this->property('episodeSlug'));
        $this->episode = Models\Episode::where('slug', '=', $episodeSlug)->with(['releases'=>function($q){$q->with('release_type');},'image','tags','show'])->get()->first();

        $this->show = $this->episode->relations['show']->first();
        $this->tags = $this->episode->relations['tags'];
        $this->releases = $this->episode->relations['releases'];

        if (trim($this->property('playerReleaseId')) !== '') {
            $this->playerRelease = $this->releases->find(trim($this->property('playerReleaseId')));
        } else if (trim($this->property('playerReleaseType')) !== '') {
            // Doing foreach instead of $this->releases->filter so I can break after I find one
            foreach($this->releases as $release) {
                if($release->relations['release_type']->slug === trim($this->property('playerReleaseType'))) {
                    $this->playerRelease = $release;
                    break;
                }
            }
        } else {
            $this->playerRelease = $this->releases->first();
        }

        $this->playerReleaseType = $this->playerRelease->relations['release_type'];

        if ($this->playerReleaseType->type === "youtube") {
            // Gets the embed url from a youtube url.
            // Uses https://gist.github.com/astockwell/11055104
            $this->playerYoutubeEmbedUrl = VideoURLParser::get_url_embed($this->playerRelease->url);
        }
    }   
}