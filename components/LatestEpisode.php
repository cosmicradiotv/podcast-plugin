<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\ComponentBase;
use CosmicRadioTV\Podcast\classes\TitlePlaceholdersTrait;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\Show;
use CosmicRadioTV\Podcast\Models;
use CosmicRadioTV\Podcast\Models\Episode as EpisodeModel;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Classes\VideoUrlParser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Request;

class LatestEpisode extends ComponentBase
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
            'name'        => 'cosmicradiotv.podcast::components.latest_episode.name',
            'description' => 'cosmicradiotv.podcast::components.latest_episode.description'
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
            'showSlugFilter'    => [
                'title'       => 'cosmicradiotv.podcast::components.latest_episode.properties.show_slug_filter.title',
                'description' => 'cosmicradiotv.podcast::components.latest_episode.properties.show_slug_filter.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
                'required'    => false,
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
        if (!empty($this->property('showSlugFilter'))) {
            $this->show = Show::query()
                  ->where('slug', $this->property('showSlugFilter'))
                  ->firstOrFail();

            $this->episode = $this->show->episodes()
                    ->getQuery()
                    ->where('published', true)
                    ->orderBy('release', 'desc')
                    ->with(['releases', 'releases.release_type', 'image', 'tags', 'show'])
                    ->firstOrFail();
        } else {
            $this->episode = EpisodeModel::query()
                    ->where('published', true)
                    ->orderBy('release', 'desc')
                    ->with(['releases', 'releases.release_type', 'image', 'tags', 'show'])
                    ->firstOrFail();
        }


        $this->releases = Collection::make($this->episode->releases); // Creates a copy
        $this->releases->sort(function (Release $a, Release $b) {
            // Order: Youtube > (rest) > Video > Audio
            $ratings = [
                'youtube' => 1,
                'video'   => 8,
                'audio'   => 9
            ];
            $aRating = $ratings[$a->release_type->type] ?: 7;
            $bRating = $ratings[$b->release_type->type] ?: 7;

            return $aRating - $bRating;
        });
    }

    /**
     * Update page's title using placeholders
     */
    protected function updateTitle()
    {
        $raw = $this->page->title;
        $paths = (object) [
            'show'    => $this->show,
            'episode' => $this->episode,
        ];

        $this->page->title = $this->replacePlaceholders($raw, $paths);
    }
}