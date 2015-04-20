<?php namespace CosmicRadioTV\Podcast;

use System\Classes\PluginBase;
use Backend;
use Event;
use CosmicRadioTV\Podcast\Models\Show;

/**
 * Podcast Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'cosmicradiotv.podcast::lang.plugin.name',
            'description' => 'cosmicradiotv.podcast::lang.plugin.description',
            'author'      => 'CosmicRadioTV',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Called right before the request route.
     */
    public function boot() {
        
        // Extends the list query for shows so that it is restricted to shows the user has permission to access.
        Event::listen('backend.list.extendQueryBefore', function($list,$query) {
            
            if ($list->model instanceof \CosmicRadioTV\Podcast\Models\Show) {
                if (!$list->getController()->user->hasPermission(['cosmicradiotv.podcast.access_shows_all'])) {
                    $shows = Show::all();
                    $show_ids_allowed = [];

                    // Fills the list of allowed show ids.
                    foreach ($shows as $show) {
                        if ($list->getController()->user->hasPermission(['cosmicradiotv.podcast.access_show_'.$show->slug])) {
                            $show_ids_allowed[] = $show->id;
                        }
                    }

                    $query->whereIn('id',$show_ids_allowed);
                }
            }
        });
    }

    /**
     * Sets up the permissions for the plugin. It sets up permissions for every show that's in the database.
     * @return array The permissions array.
     */
    public function registerPermissions()
    {
        $permissions = [
            'cosmicradiotv.podcast.access_release_types' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_release_types'],
            'cosmicradiotv.podcast.access_tags' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_tags'],
            'cosmicradiotv.podcast.access_shows_all' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_shows_all'],
            'cosmicradiotv.podcast.access_shows' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_shows'],
            'cosmicradiotv.podcast.access_episodes_all' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_episodes_all'],
            'cosmicradiotv.podcast.access_episodes' => ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.general', 'label' => 'cosmicradiotv.podcast::lang.permissions.labels.access_episodes'],
        ];

        // Add the permissions for individual shows
        $shows = Show::all();
        foreach ($shows as $show) {
            $permissions['cosmicradiotv.podcast.access_show_'.$show->slug] = ['tab' => 'cosmicradiotv.podcast::lang.permissions.tab_labels.shows', 'label' => e(trans('cosmicradiotv.podcast::lang.permissions.labels.access_show')).' '.$show->name];
        }

        return $permissions;
    }

    /**
     * Sets up the back end navigation for the plugin
     * @return array The navigation array.
     */
    public function registerNavigation()
    {
        return [
            'podcast' => [
                'label' => 'cosmicradiotv.podcast::lang.podcast.menu_label',
                'url' => Backend::url('cosmicradiotv/podcast/episodes'),
                'icon' => 'icon-play',
                'permissions' => ['cosmicradiotv.podcast.access_episode*'],
                'order' => 500,

                'sideMenu' => [
                    'episodes' => [
                        'label' => 'cosmicradiotv.podcast::lang.episode.menu_label',
                        'url' => Backend::url('cosmicradiotv/podcast/episodes'),
                        'icon' => 'icon-play',
                        'permissions' => ['cosmicradiotv.podcast.access_episode*']
                    ],

                    'shows' => [
                        'label' => 'cosmicradiotv.podcast::lang.show.menu_label',
                        'url' => Backend::url('cosmicradiotv/podcast/shows'),
                        'icon' => 'icon-list',
                        'permissions' => ['cosmicradiotv.podcast.access_show*']
                    ],

                    'tags' => [
                        'label' => 'cosmicradiotv.podcast::lang.tag.menu_label',
                        'url' => Backend::url('cosmicradiotv/podcast/tags'),
                        'icon' => 'icon-tags',
                        'permissions' => ['cosmicradiotv.podcast.access_tags']
                    ],

                    'releasetypes' => [
                        'label' => 'cosmicradiotv.podcast::lang.release_type.menu_label',
                        'url' => Backend::url('cosmicradiotv/podcast/releasetypes'),
                        'icon' => 'icon-video-camera',
                        'permissions' => ['cosmicradiotv.podcast.access_release_types']
                    ]
                ]
            ]
        ];
    }
}