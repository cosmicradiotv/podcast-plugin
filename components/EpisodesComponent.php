<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\Page;
use CosmicRadioTV\Podcast\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Classes\TitlePlaceholdersTrait;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Show;
use CosmicRadioTV\Podcast\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use October\Rain\Database\Builder;
use URL;

/**
 * Component that lists all of the episodes of a show
 *
 * @package CosmicRadioTV\Podcast\Components
 */
class EpisodesComponent extends ComponentBase
{

    use TitlePlaceholdersTrait;

    /**
     * @var bool
     */
    public $allowPagination;

    /**
     * @var LengthAwarePaginator|Collection|Episode[] Paginator instance of all of the episodes
     */
    public $episodes;

    /**
     * @var string[]
     */
    public $meta_tags = [];

    /**
     * @var Show The show being displayed
     */
    public $show;
    /**
     * @var Tag Tag to filter episodes by
     */
    public $tag;

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
            'showSlug'        => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.show_slug.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.show_slug.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
                'group'       => trans('cosmicradiotv.podcast::components.episodes.groups.filters'),
            ],
            'tagSlug'         => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.tag_slug.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.tag_slug.description',
                'default'     => '',
                'type'        => 'string',
                'group'       => trans('cosmicradiotv.podcast::components.episodes.groups.filters'),
            ],
            'episodePage'     => [
                'title'       => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.description',
                'type'        => 'dropdown',
                'default'     => 'podcast/episode',
                'required'    => true,
                'group'       => trans('cosmicradiotv.podcast::components.episodes.groups.links'),
            ],
            'perPage'         => [
                'title'             => 'cosmicradiotv.podcast::components.episodes.properties.per_page.title',
                'description'       => 'cosmicradiotv.podcast::components.episodes.properties.per_page.description',
                'default'           => 10,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => trans('cosmicradiotv.podcast::components.episodes.properties.per_page.validationMessage'),
                'required'          => true,
                'group'             => trans('cosmicradiotv.podcast::components.episodes.groups.pagination')
            ],
            'allowPagination' => [
                'title'       => 'cosmicradiotv.podcast::components.episodes.properties.allow_pagination.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.allow_pagination.description',
                'default'     => true,
                'type'        => 'checkbox',
                'group'       => trans('cosmicradiotv.podcast::components.episodes.groups.pagination')
            ],
            'updateTitle'     => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.update_title.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.update_title.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],
            'metaTags'        => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.meta_tags.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.meta_tags.description',
                'default'     => false,
                'type'        => 'checkbox',
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

        if ($this->property('metaTags')) {
            $this->setMetaTags();
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
        $this->allowPagination = (bool) $this->property('allowPagination');
        $this->show = $this->loadShow();
        if ($this->property('tagSlug')) {
            $this->tag = Tag::query()->where('slug', $this->property('tagSlug'))->firstOrFail();
        }
        $this->episodes = $this->loadEpisodes();
    }

    /**
     * If a show is requested by slug loads it, or null if all shows (left blank)
     *
     * @throws ModelNotFoundException
     * @return Show|null
     */
    protected function loadShow()
    {
        $slug = $this->property('showSlug');

        if ($slug) {
            return Show::query()->where('slug', $this->property('showSlug'))->firstOrFail();
        } else {
            return null;
        }
    }

    /**
     * Loads episodes for the current show (or all shows if not set
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection|Episode[]
     */
    protected function loadEpisodes()
    {
        if ($this->show) {
            // Show's episodes
            $query = $this->show->episodes();
            $setShows = true; // Skips loading from database
        } else {
            // All shows' episodes
            $query = Episode::query();
            $query->with('show');
            $setShows = false;
        }

        $query->with('image')
              ->where('published', true)
              ->orderBy('release', 'desc');

        if ($this->tag) {
            $query->whereHas('tags', function (Builder $q) {
                $q->where('cosmicradiotv_podcast_tags.id', $this->tag->id);
            });
        }

        if ($this->allowPagination) {
            /** @var LengthAwarePaginator|Episode[] $returns */
            $returns = $query->paginate(intval($this->property('perPage')));
            $collection = $returns->getCollection();
        } else {
            $returns = $collection = $query->take($this->property('perPage'))->get();
        }

        $collection->each(function (Episode $episode) use ($setShows) {
            if ($setShows) {
                $episode->setRelation('show', $this->show);
            }

            // Cache URL value to the model
            $episode->url = $this->getEpisodeURL($episode);
        });

        return $returns;
    }

    /**
     * Gives values for Episode Page dropdown
     *
     * @return array
     */
    public function getEpisodePageOptions()
    {
        return Page::getNameList();
    }

    /**
     * Get episode's URL
     *
     * @param Episode $episode
     *
     * @return string
     */
    public function getEpisodeURL(Episode $episode)
    {
        return $this->controller->pageUrl($this->property('episodePage'),
            ['show_slug' => $episode->show->slug, 'episode_slug' => $episode->slug]);
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

    /**
     * Injects meta tags into the header
     */
    protected function setMetaTags()
    {
        // Show related tags
        if ($this->show) {
            $this->page->meta_title = $this->show->name;
            $this->page->meta_description = $this->show->description;

            // Extra meta tags, available via {% placeholder head %}
            $this->meta_tags = [
                'twitter:card'   => 'summary',
                'og:title'       => $this->show->name,
                'og:description' => $this->show->description,
                'og:type'        => 'video.tv_show',
                'og:url'         => $this->controller->currentPageUrl(),
            ];

            if ($this->show->image) {
                // Full path
                $this->meta_tags['og:image'] = URL::to($this->show->image->getPath());
            }

        }
    }

}