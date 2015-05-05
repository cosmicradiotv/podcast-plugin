<?php

namespace CosmicRadioTV\Podcast\components;


use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use CosmicRadioTV\Podcast\Models\Episode as EpisodeModel;
use CosmicRadioTV\Podcast\Models\Release;
use CosmicRadioTV\Podcast\Models\ReleaseType;
use CosmicRadioTV\Podcast\Models\Show;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Response;
use October\Rain\Database\Model;
use SimpleXMLElement;
use System\Classes\PluginManager;

class FeedComponent extends ComponentBase
{

    /**
     * @var Collection|Episode[] All of the episodes
     */
    public $episodes;

    /**
     * @var ReleaseType Release type that matches the feed
     */
    public $releaseType;

    /**
     * @var Show The show being displayed
     */
    public $show;

    /**
     * @var array Fields to map to RSS feed in channel
     */
    protected $feed_channel_fields = [
        'name'           => 'title',
        'description'    => ['description', 'itunes:summary'],
        'feed_language'  => 'language',
        'feed_copyright' => 'copyright',
        'feed_author'    => ['itunes:author', 'managingEditor']
    ];

    /**
     * @var array Fields to map to RSS feed in items
     */
    protected $feed_episode_fields = [
        'title'   => 'title',
        'summary' => ['description', 'itunes:summary'],
        'length'  => 'itunes:duration',
    ];

    /**
     * Returns information about this component, including name and description.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'cosmicradiotv.podcast::components.feed.name',
            'description' => 'cosmicradiotv.podcast::components.feed.description',
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
                'description' => 'cosmicradiotv.podcast::components.common.properties.show_slug.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
            ],
            'releaseTypeSlug' => [
                'title'       => 'cosmicradiotv.podcast::components.feed.properties.release_type_slug.title',
                'description' => 'cosmicradiotv.podcast::components.feed.properties.release_type_slug.description',
                'default'     => '{{ :release_type_slug }}',
                'type'        => 'string',
            ],
            'itemLimit'       => [
                'title'             => 'cosmicradiotv.podcast::components.feed.properties.item_limit.title',
                'description'       => 'cosmicradiotv.podcast::components.feed.properties.item_limit.description',
                'default'           => 100,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => trans('cosmicradiotv.podcast::components.feed.properties.item_limit.validationMessage'),
                'required'          => true,
            ],
            'showPage'        => [
                'title'       => 'cosmicradiotv.podcast::components.feed.properties.show_page.title',
                'description' => 'cosmicradiotv.podcast::components.feed.properties.show_page.description',
                'type'        => 'dropdown',
                'default'     => 'podcast/show',
                'required'    => true,
                'group'       => trans('cosmicradiotv.podcast::components.feed.groups.links'),
            ],
            'episodePage'     => [
                'title'       => 'cosmicradiotv.podcast::components.feed.properties.episode_page.title',
                'description' => 'cosmicradiotv.podcast::components.feed.properties.episode_page.description',
                'type'        => 'dropdown',
                'default'     => 'podcast/episode',
                'required'    => true,
                'group'       => trans('cosmicradiotv.podcast::components.feed.groups.links'),
            ],

        ];
    }

    /**
     * Return pages list for dropdowns
     *
     * @return array
     */
    protected function returnPagesList()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getShowPageOptions()
    {
        return $this->returnPagesList();
    }

    public function getEpisodePageOptions()
    {
        return $this->returnPagesList();
    }


    /**
     * Generates RSS feed and overwrites page with it
     *
     * @return \Symfony\Component\HttpFoundation\Response
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

        $feed = $this->generateXML();

        $this->disableConflictingPlugins();

        $response = Response::create($feed->asXML(), 200, [
            'Content-Type' => 'application/rss+xml',
        ]);

        return $response;
    }


    /**
     * Set components state based on parameters
     *
     * @throws ModelNotFoundException
     */
    public function setState()
    {
        $this->show = $show = Show::query()
                                  ->where('slug', $this->property('showSlug'))
                                  ->firstOrFail();
        $this->releaseType = $releaseType = ReleaseType::query()
                                                       ->where('slug', $this->property('releaseTypeSlug'))
                                                       ->firstOrFail();
        $this->episodes = $this->show->episodes()
                                     ->with([
                                         'releases' => function (Relation $query) use ($releaseType) {
                                             /** @var Relation|Builder $query */
                                             $query->where('release_type_id', $releaseType->id);
                                         }
                                     ])
                                     ->where('published', true)
                                     ->whereHas('releases', function (Builder $query) use ($releaseType) {
                                         $query->where('release_type_id', $releaseType->id);
                                     })
                                     ->orderBy('release', 'desc')
                                     ->take(intval($this->property('itemLimit')))
                                     ->get();

        $this->episodes->map(function (EpisodeModel $episode) use ($show) {
            $episode->setRelation('show', $show);
        });

    }


    /**
     * Generates an xml feed based on show
     *
     * @return SimpleXMLElement
     */
    public function generateXML()
    {
        $root = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss></rss>');
        $root['version'] = '2.0';
        $root['xmlns:atom'] = 'http://www.w3.org/2005/Atom';
        $root['xmlns:itunes'] = 'http://www.itunes.com/dtds/podcast-1.0.dtd';

        $channel = $this->addChannelToFeed($root);
        $this->addEpisodesToChannel($channel);

        return $root;
    }

    /**
     * Disables any other plugins that are known to butt in
     */
    protected function disableConflictingPlugins()
    {
        $pluginManager = PluginManager::instance();

        // Debugbar - Injects extra crap into xml
        if ($pluginManager->exists('bedard.debugbar')) {
            app('config')->set('debugbar.enabled', false);
        }
    }

    /**
     * Adds channel to the feed
     *
     * @param SimpleXMLElement $root
     *
     * @return SimpleXMLElement
     */
    protected function addChannelToFeed(SimpleXMLElement $root)
    {
        $channel = $root->addChild('channel');
        $this->addFields($channel, $this->feed_channel_fields, $this->show);

        $channel->link = $this->controller->pageUrl($this->property('showPage'), ['show_slug' => $this->show->slug]);
        $channel->generator = 'CosmicRadioTV/podcast-plugin';
        $channel->docs = 'http://blogs.law.harvard.edu/tech/rss';
        $channel->{'atom:link'}['rel'] = 'self';
        $channel->{'atom:link'}['href'] = $this->controller->currentPageUrl();

        if ($this->show->image) {
            $channel->image->url = asset($this->show->image->getPath());
            $channel->image->title = $this->show->name;
            $channel->image->link = $channel->link;
            $channel->{'itunes:image'}['href'] = asset($this->show->image->getPath());
        }
        // iTunes categories
        if ($this->show->itunes_category) {
            $categories = explode("\r\n", $this->show->itunes_category);
            /** @var SimpleXMLElement $previous */
            $previous = null;
            foreach ($categories as $category) {
                if (substr($category, 0, 1) == ' ' && isset($previous)) {
                    $element = $previous->addChild('xmlns:itunes:category');
                    $text = substr($category, 1);
                } else {
                    $previous = $element = $channel->addChild('xmlns:itunes:category');
                    $text = $category;
                }
                $element['text'] = $text;
            }
        }
        if ($this->show->itunes_explicit) {
            $channel->{'itunes:explicit'} = 'yes';
        }
        if ($this->show->itunes_owner_name) {
            $channel->{'itunes:owner'}->{'itunes:name'} = $this->show->itunes_owner_name;
        }
        if ($this->show->itunes_owner_email) {
            $channel->{'itunes:owner'}->{'itunes:email'} = $this->show->itunes_owner_email;
        }

        return $channel;
    }

    /**
     * @param SimpleXMLElement $channel
     */
    protected function addEpisodesToChannel(SimpleXMLElement $channel)
    {
        foreach ($this->episodes as $episode) {
            $episodeNode = $channel->addChild('item');

            $this->addFields($episodeNode, $this->feed_episode_fields, $episode);

            $episodeNode->link = $this->controller->pageUrl($this->property('episodePage'),
                ['show_slug' => $this->show->slug, 'episode_slug' => $episode->slug]);
            $episodeNode->guid = $episodeNode->link;
            $episodeNode->pubDate = $episode->release->toRfc2822String();
            if ($episode->itunes_explicit) {
                $episodeNode->{'itunes:explicit'} = 'yes';
            }

            /** @var Release $release */
            $release = $episode->releases->first(); // Filtered to be the one in eager loader
            if (in_array($this->releaseType->type, ['audio', 'video'])) {
                $episodeNode->enclosure['url'] = $release->url;
                $episodeNode->enclosure['length'] = $release->size;
                $episodeNode->enclosure['type'] = $this->releaseType->filetype;
            } else {
                $episodeNode->comments = $episodeNode->link;
                $episodeNode->link = $release->url;
            }
        }
    }

    /**
     * Attaches fields from model to XML element
     *
     * @param SimpleXMLElement $channel
     * @param array            $fields
     * @param Model            $souceModel
     */
    protected function addFields(SimpleXMLElement $channel, $fields, Model $souceModel)
    {
        foreach ($fields as $source => $target) {
            if ($souceModel->{$source}) {
                foreach ((array) $target as $field) {
                    $channel->{$field} = $souceModel->{$source};
                }
            }
        }
    }

}