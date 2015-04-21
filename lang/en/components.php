<?php
return [
    'episodes' => [
        'name'        => 'Episodes',
        'description' => 'Lists all of the episodes of a show',
        'properties'  => [
            'show_slug'    => [
                'title'       => "Show's slug",
                'description' => "The slug of the show to load"
            ],
            'per_page'     => [
                'title'             => 'Episodes per page',
                'description'       => 'Maximum amount of episodes to list per page',
                'validationMessage' => 'Episodes per page must be a number'
            ],
            'episode_page' => [
                'title'       => 'Episode page',
                'description' => 'URL of the episode page',
                'group'       => 'Links',
            ],
        ],
    ],
];