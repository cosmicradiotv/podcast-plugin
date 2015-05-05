<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\classes\TitlePlaceholdersTrait;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\Show;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Episode as EpisodeModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Episode extends ComponentBase
{

    use TitlePlaceholdersTrait;

    /**
     * @var EpisodeModel The show being displayed
     */
    public $episode;

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
            'name'        => 'cosmicradiotv.podcast::components.episode.name',
            'description' => 'cosmicradiotv.podcast::components.episode.description'
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
            'showSlug'    => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.show_slug.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.show_slug.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
                'required'    => true,
            ],
            'episodeSlug' => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.episode_slug.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.episode_slug.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
                'required'    => true,
            ],
            'updateTitle' => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.update_title.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.update_title.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],
        ];
    }

    /**
     * Runs when the page or layout loads (sets up properties available to the component partial)
     *
     * @returns null|string
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

        if ($this->property('updateTitle')) {
            $this->updateTitle();
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
                                    ->with(['releases', 'releases.release_type', 'image', 'tags', 'show'])
                                    ->firstOrFail();
        $this->releases = Collection::make($this->episode->releases); // Creates a copy
        $this->releases->sort(function (Release $a, Release $b) {
            // Order by the sort_order column
            $aRating = $a->release_type->sort_order;
            $bRating = $b->release_type->sort_order;

            return $aRating - $bRating;
        });
    }

    /**
     * Things to replace placeholders with
     *
     * @return object
     */
    protected function getTitlePlaceholderReplaces()
    {
        return (object) [
            'show'    => $this->show,
            'episode' => $this->episode,
        ];
    }

}