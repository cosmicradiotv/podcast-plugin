<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\Show;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Episode as EpisodeModel;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Classes\VideoUrlParser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Request;

class Episode extends ComponentBase
{

    /**
     * @var EpisodeModel The show being displayed
     */
    public $episode;

    public $playerRelease;
    public $playerYoutubeEmbedUrl;

    /**
     * @var Collection|Release[]
     */
    public $releases;

    /**
     * @var Show The show being displayed
     */
    public $show;

    /**
     * Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Episode Component',
            'description' => 'Used to display an Episode'
        ];
    }

    /**
     * User editable properties
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'showSlug'          => [
                'title'    => 'Show Slug',
                'type'     => 'string',
                'default'  => '{{ :show_slug }}',
                'required' => true,
            ],
            'episodeSlug'       => [
                'title'    => 'Episode Slug',
                'type'     => 'string',
                'default'  => '{{ :episode_slug }}',
                'required' => true,
            ]
        ];
    }

    /**
     * Gives values for Player Release ID options
     *
     * @return mixed
     */
    public function getPlayerReleaseIdOptions()
    {
        $episodeSlug = Request::input('episodeSlug');

        $episode = Models\Episode::where('slug', '=', $episodeSlug)->whereHas('show', function ($q) {
            $showSlug = Request::input('showSlug');
            $q->where('slug', '=', $showSlug);
        })->with('releases')->get()->first();

        if (!empty($episode) && !$episode->relations['releases']->isEmpty()) {
            return $episode->relations['releases']->lists('url', 'id');
        }

        return [];
    }

    /**
     * Gives values for Release Type options
     *
     * @return array
     */
    public function getPlayerReleaseTypeOptions()
    {
        return ReleaseType::all()->lists('name', 'slug');
    }

    /**
     * Runs when the page or layout loads (sets up properties available to the component partial)
     */
    public function onRun()
    {
        try {
            $this->setState();
        } catch (ModelNotFoundException $e) {
            // Show/Episode not found, return 404
            $this->controller->setStatusCode(404);

            return $this->controller->run('404');
        }

        $this->addCss('/plugins/cosmicradiotv/podcast/assets/stylesheet/player.css');
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/javascript/player.js');

        return null;
    }

    /**
     * Set components state based on parameters
     *
     * @throws ModelNotFoundException
     */
    public function setState()
    {
        $this->show = Show::query()
                          ->where('slug', $this->property('showSlug'))
                          ->firstOrFail();
        $this->episode = $this->show->episodes()
                                    ->getQuery()
                                    ->where('published', true)
                                    ->where('slug', $this->property('episodeSlug'))
                                    ->firstOrFail();
        $this->releases = Collection::make($this->episode->releases); // Creates a copy
        $this->releases->sort(function(Release $a, Release $b) {
            // Order: Youtube > (rest) > Video > Audio
            $ratings = [
                'youtube' => 1,
                'video' => 8,
                'audio' => 9
            ];
            $aRating = $ratings[$a->release_type->type] ?: 7;
            $bRating = $ratings[$b->release_type->type] ?: 7;

            return $aRating - $bRating;
        });
    }
}