<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\CodeBase;
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

class LatestEpisode extends Episode
{

    /**
     * Component constructor. Takes in the page or layout code section object
     * and properties set by the page or layout.
     * Override: change dirname
     *
     * @param CodeBase $cmsObject
     * @param array    $properties
     */
    public function __construct(CodeBase $cmsObject = null, $properties = [])
    {
        parent::__construct($cmsObject, $properties);

        $this->dirName = str_replace('latestepisode', 'episode', $this->dirName);
    }

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
     * Set components state based on parameters
     *
     * @throws ModelNotFoundException
     */
    public function setState()
    {
        $showSlugFilter = $this->property('showSlugFilter');
        if (!empty($showSlugFilter)) {
            $this->show = Show::query()
                  ->where('slug', $showSlugFilter)
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
            // Order by the sort_order column
            $aRating = $a->release_type->sort_order;
            $bRating = $b->release_type->sort_order;

            return $aRating - $bRating;
        });
    }
}