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

    public $playerRelease;
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
            'showSlug' => [
                'title' =>          'Show Slug',
                'type' =>           'string',
                'default' =>        '',
                'required' =>       true,
            ],
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
        
        $episode = Models\Episode::where('slug', '=', $episodeSlug)->whereHas('show', function($q) {
            $showSlug = Request::input('showSlug');
            $q->where('slug','=',$showSlug);
        })->with('releases')->get()->first();
        
        if (!empty($episode) && !$episode->relations['releases']->isEmpty()) {
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
        $this->episode = $this->page['episode'] = Models\Episode::where('slug', '=', $episodeSlug)->whereHas('show', function($q) {
            $showSlug = trim($this->property('showSlug'));
            $q->where('slug','=',$showSlug);
        })->with(['releases'=>function($q){
            $q->with('release_type');
        },'image','tags','show'])->get()->first();

        // Get the release to be used in the player (based on conditions set for the component)
        if (!empty($this->episode)) {

            // If we have the release id, just use it, skip everything else.
            if (trim($this->property('playerReleaseId')) !== '') {

                $this->playerRelease = $this->episode->releases->find(trim($this->property('playerReleaseId')));

            } 

            // If we have the release type, get the first release of that type.
            else if (trim($this->property('playerReleaseType')) !== '') 
            {

                // Doing foreach instead of $this->releases->filter so I can break after I find one
                foreach($this->episode->releases as $release) {

                    if($release->relations['release_type']->slug === trim($this->property('playerReleaseType'))) {
                        $this->playerRelease = $release;
                        break;
                    }

                }
            } 

            // Otherwise choose the first release
            else 
            {
                $this->playerRelease = $this->releases->first();
            }

            // If it's a youtube player set the youtube embed url
            if ($this->playerRelease->release_type->type === "youtube") {
                // Gets the embed url from a youtube url.
                // Uses https://gist.github.com/astockwell/11055104
                $this->playerYoutubeEmbedUrl = VideoURLParser::get_youtube_embed(VideoURLParser::get_youtube_id($this->playerRelease->url),0);
            }
        }
    }   
}