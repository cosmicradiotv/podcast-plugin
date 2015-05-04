<?php
return [
    'common'   => [
        'properties' => [
            'show_slug'    => [
                'title'       => "Show's slug",
                'description' => "The slug of the show to load"
            ],
            'episode_slug' => [
                'title'       => "Episode's slug",
                'description' => "The slug of the episode to load"
            ],
            'update_title' => [
                'title'       => 'Update page title',
                'description' => "Updates page title using placeholders. Ex: {{show.name}}. Place @ infront of {{ to escape.",
            ],
        ],
    ],
    'episode' => [
        'name'          => 'Episode Component',
        'description'   => 'Used to display an Episode'
    ],
    'latest_episode' => [
        'name'          => 'Latest Episode Component',
        'description'   => 'Used to display the latest Episode',
        'properties'    => [
            'show_slug_filter' => [
                'title'         => "Show slug filter (optional)",
                'description'   => 'Specifies which show to pull the latest episode from (all if left blank)',
            ]
        ]
    ],
    'episodes' => [
        'name'        => 'Episodes',
        'description' => 'Lists all of the episodes of a show',
        'properties'  => [
            'per_page'     => [
                'title'             => 'Episodes per page',
                'description'       => 'Maximum amount of episodes to list per page',
                'validationMessage' => 'Episodes per page must be a number'
            ],
            'episode_page' => [
                'title'       => 'Episode page',
                'description' => 'Episode details page',
                'group'       => 'Links',
            ],
        ],
        'groups'      => [
            'links' => 'Links',
        ]
    ],
    'feed'     => [
        'name'        => 'RSS Feed',
        'description' => 'Generates an RSS feed based on show slug and release type slug',
        'properties'  => [
            'release_type_slug' => [
                'title'       => "Release type's slug",
                'description' => "The slug of the release type to load"
            ],
            'item_limit'        => [
                'title'             => 'Episodes to show',
                'description'       => 'Maximum amount of episodes to list in feed',
                'validationMessage' => 'Episodes to show must be a number'
            ],
            'show_page'         => [
                'title'       => "Show page",
                'description' => "Show page"
            ],
            'episode_page'      => [
                'title'       => "Episode page",
                'description' => "Episode details page"
            ],

        ],
        'groups'      => [
            'links' => 'Links',
        ]
    ]
];