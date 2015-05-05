<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use CosmicRadioTV\Podcast\classes\TitlePlaceholdersTrait;
use CosmicRadioTV\Podcast\Models\Episode as EpisodeModel;
use CosmicRadioTV\Podcast\Models\Show;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Component that lists all of the episodes of a show
 *
 * @package CosmicRadioTV\Podcast\Components
 */
class Episodes extends ComponentBase
{

    use TitlePlaceholdersTrait;

    /**
     * @var Show The show being displayed
     */
    public $show;

    /**
     * @var LengthAwarePaginator|Episode[] Pagintor instance of all of the episodes
     */
    public $episodes;

    /**
     * Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'cosmicradiotv.podcast::components.episodes.name',
            'description' => 'cosmicradiotv.podcast::components.episodes.description',
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
            'perPage'     => [
                'title'             => 'cosmicradiotv.podcast::components.episodes.properties.per_page.title',
                'description'       => 'cosmicradiotv.podcast::components.episodes.properties.per_page.description',
                'default'           => 10,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => trans('cosmicradiotv.podcast::components.episodes.properties.per_page.validationMessage'),
                'required'          => true,
            ],
            'updateTitle' => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.update_title.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.update_title.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],
            'episodePage' => [
                'title'       => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.description',
                'type'        => 'dropdown',
                'default'     => 'podcast/episode',
                'required'    => true,
                'group'       => trans('cosmicradiotv.podcast::components.episodes.groups.links'),
            ],
        ];
    }

    /**
     * Prepare component
     *
     * @returns null|string
     */
    public function onRun()
    {
        try {
            $this->setState();
        } catch (ModelNotFoundException $e) {
            // Show not found, return 404
            $this->controller->setStatusCode(404);

            return $this->controller->run('404');
        }

        if ($this->property('updateTitle')) {
            $this->updateTitle();
        }

        return null;
    }

    /**
     * Set components state based on parameters
     *
     * @throws ModelNotFoundException
     */
    public function setState()
    {
        $this->show = Show::query()->where('slug', $this->property('showSlug'))->firstOrFail();
        $this->episodes = $this->show->episodes()
                                     ->with('image')
                                     ->where('published', true)
                                     ->orderBy('release', 'desc')
                                     ->paginate(intval($this->property('perPage')));

        $this->episodes->getCollection()->each(function (EpisodeModel $episode) {
            // Cache URL value to the model
            $episode->url = $this->getEpisodeURL($episode);
        });
    }

    /**
     * Gives values for Episode Page dropdown
     *
     * @return array
     */
    public function getEpisodePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Get episode's URL
     *
     * @param Episode $episode
     *
     * @return string
     */
    public function getEpisodeURL(EpisodeModel $episode)
    {
        return $this->controller->pageUrl($this->property('episodePage'),
            ['show_slug' => $this->show->slug, 'episode_slug' => $episode->slug]);
    }

    /**
     * Things to replace placeholders with
     *
     * @return object
     */
    protected function getTitlePlaceholderReplaces()
    {
        return (object) [
            'show' => $this->show,
        ];
    }

}