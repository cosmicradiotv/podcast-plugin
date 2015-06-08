<?php namespace CosmicRadioTV\Podcast\Components;

use CosmicRadioTV\Podcast\Classes\ComponentBase;
use CosmicRadioTV\Podcast\Classes\TitlePlaceholdersTrait;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\Show;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use October\Rain\Database\Builder;
use URL;

class EpisodeComponent extends ComponentBase
{

    use TitlePlaceholdersTrait;

    /**
     * @var Episode The show being displayed
     */
    public $episode;

    /**
     * @var string[]
     */
    public $meta_tags = [];

    /**
     * @var Collection|Release[]
     */
    public $releases;

    /**
     * @var Show The show being displayed
     */
    public $show;

    /**
     * @var Tag The tag being used
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
                'group'       => trans('cosmicradiotv.podcast::components.episode.groups.filters'),
            ],
            'episodeSlug' => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.episode_slug.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.episode_slug.description',
                'default'     => '{{ :episode_slug }}',
                'type'        => 'string',
                'group'       => trans('cosmicradiotv.podcast::components.episode.groups.filters'),
            ],
            'tagSlug'     => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.tag_slug.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.tag_slug.description',
                'default'     => '',
                'type'        => 'string',
                'group'       => trans('cosmicradiotv.podcast::components.episode.groups.filters'),
            ],
            'updateTitle' => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.update_title.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.update_title.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],
            'metaTags'    => [
                'title'       => 'cosmicradiotv.podcast::components.common.properties.meta_tags.title',
                'description' => 'cosmicradiotv.podcast::components.common.properties.meta_tags.description',
                'default'     => false,
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
        $this->show = $this->loadShow();
        if ($this->property('tagSlug')) {
            $this->tag = Tag::query()->where('slug', $this->property('tagSlug'))->firstOrFail();
        }
        $this->episode = $this->loadEpisode();

        $this->releases = Collection::make($this->episode->releases); // Creates a copy
        $this->releases->sort(function (Release $a, Release $b) {
            // Order by the sort_order column
            $aRating = $a->release_type->sort_order;
            $bRating = $b->release_type->sort_order;

            return $aRating - $bRating;
        });
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
     * Load the episode as requested
     *
     * @throws ModelNotFoundException
     * @return Episode
     */
    protected function loadEpisode()
    {
        // Show filter / Query base
        if ($this->show) {
            // Show's episodes
            $query = $this->show->episodes();
            $setShow = true; // Skips loading from database
            $latest = false; // In case of Show & Episode no need to find latest
        } else {
            // All shows' episodes
            $query = Episode::query();
            $query->with('show');
            $setShow = false;
            $latest = true;
        }

        // Episode slug filter
        if($this->property('episodeSlug')) {
            // Load specific episode, unless show isn't set
            $latest = $latest || false;

            $query->where('slug', $this->property('episodeSlug'));
        } else {
            // Load latest episode
            $latest = true;
        }

        // Tag filter
        if ($this->tag) {
            $query->whereHas('tags', function (Builder $q) {
                $q->where('cosmicradiotv_podcast_tags.id', $this->tag->id);
            });
        }

        // Generic rules
        $query->with(['releases', 'releases.release_type', 'image', 'tags'])
              ->where('published', true);

        // If latest also order by
        if($latest) {
            $query->orderBy('release', 'desc');
        }

        $episode = $query->firstOrFail();

        if($setShow) {
            // Set show on episode
            $episode->setRelation('show', $this->show);
        } else {
            // Set component's show to episode's show
            $this->show = $episode->show;
        }

        return $episode;
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

    /**
     * Injects meta tags into the header
     */
    protected function setMetaTags()
    {
        if ($this->episode) {
            $this->page->meta_title = $this->episode->title;
            $this->page->meta_description = $this->episode->summary;

            // Extra meta tags, available via {% placeholder head %}
            $this->meta_tags = [
                'twitter:card'   => 'summary',
                'og:title'       => $this->episode->title,
                'og:description' => $this->episode->summary,
                'og:type'        => 'video.episode',
                'og:url'         => $this->controller->currentPageUrl(),
            ];

            if ($this->episode->image) {
                // Full path
                $this->meta_tags['og:image'] = URL::to($this->episode->image->getPath());
            }

        }
    }

}