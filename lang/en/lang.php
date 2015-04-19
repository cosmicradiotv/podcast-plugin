<?php
// '' => '',
return [
	'plugin' => [
		'name' => 'Podcast',
		'description' => 'A platform for podcast networks.'
	],
	'podcast' => [
		'menu_label' => 'Podcasts'
	],
	'general' => [
		'slug' => 'Slug',
		'name' => 'Name',

		'save' => 'Save',
		'save_and_close' => 'Save and Close',
		'cancel' => 'Cancel',

		'create' => 'Create',
		'create_and_close' => 'Create and Close',

		'you_sure' => 'Are you sure?',

	],
	'episode' => [
		'singular' => 'Episode',
		'plural' => 'Episodes',

		'menu_label' => 'Episodes',

		# Fields and columns
		'title' => 'Title',
		'title_placeholder' => 'New episode title',
		'slug' => 'Slug',
		'slug_placeholder' => 'new-episode-slug',
		'release' => 'Release Date',
		'show' => 'Show',
		'show_prompt' => 'Click the %s button to find a show',
		'published' => 'Published',
		
		'summary' => 'Summary',
		'summary_comment' => 'A short summary of what the episode is about',
		'content' => 'Show Notes / Long Content',
		'content_comment' => 'A long description of the episode if needed, good place for show notes.',
		'length' => 'Length',
		'image' => 'Thumbnail',

		'tab_info' => 'Info',
		'tab_media' => 'Media',
		'tab_tags' => 'Tags',

		# Actions and statuses
		'delete_success_multiple' => 'Successfully deleted those episodes.',

		'create' => 'Create Episode',
		'new' => 'New Episode',
		'update' => 'Edit Episode',
		'preview' => 'Preview Episode',

		'saving' => 'Saving Episode...',
		'deleting' => 'Deleting Episode...',
		'creating' => 'Creating Episode...',
		'confirm_delete' => 'Do you really want to delete this episode?',

		'return_to_list' => 'Return to episodes list',

		'list_title' => 'Manage Episodes',

		'filter_show' => 'Filter by Show',
	],
	'show' => [
		'singular' => 'Show',
		'plural' => 'Shows',

		'menu_label' => 'Shows',

		# Fields and columns
		'name' => 'Name',
		'name_placeholder' => 'New show name',
		'image' => 'Show Artwork',
		'slug' => 'Slug',
		'slug_placeholder' => 'new-show-slug',
		'description' => 'Description',
		'description_comment' => 'A description of what the show is about.',

		# Actions and statuses
		'create' => 'Create Show',
		'new' => 'New Show',
		'update' => 'Edit Show',
		'preview' => 'Preview Show',

		'saving' => 'Saving Show...',
		'deleting' => 'Deleting Show...',
		'creating' => 'Creating Show...',
		'confirm_delete' => 'Do you really want to delete this show?',

		'return_to_list' => 'Return to show list',

		'list_title' => 'Manage Shows',
	],
	'tag' => [
		'singular' => 'Tag',
		'plural' => 'Tags',

		'menu_label' => 'Tags',

		# Fields and columns
		'name' => 'Name',
		'name_placeholder' => 'New tag name',
		'slug' => 'Slug',
		'slug_placeholder' => 'new-tag-slug',

		# Actions and statuses
		'create' => 'Create Tag',
		'new' => 'New Tag',
		'update' => 'Edit Tag',
		'preview' => 'Preview Tag',

		'saving' => 'Saving Tag...',
		'deleting' => 'Deleting Tag...',
		'creating' => 'Creating Tag...',
		'confirm_delete' => 'Do you really want to delete this tag?',

		'return_to_list' => 'Return to tags list',

		'list_title' => 'Manage Tags',
	],
	'release_type' => [
		'singular' => 'Release Type',
		'plural' => 'Release Types',

		'menu_label' => 'Release Types',

		# Fields and columns
		'name' => 'Name',
		'name_placeholder' => 'New release type name',
		'slug' => 'Slug',
		'slug_placeholder' => 'new-release-type-slug',
		'type' => 'Type',
		'type_audio' => 'Audio',
		'type_video' => 'Video',
		'type_youtube' => 'YouTube',
		'filetype' => 'Filetype',
		'filetype_placeholder' => 'New release type filetype',

		# Actions and statuses
	    'create' => 'Create Release Type',
		'new' => 'New Release Type',
		'update' => 'Edit Release Type',
		'preview' => 'Preview Release Type',

		'saving' => 'Saving Release Type...',
		'deleting' => 'Deleting Release Type...',
		'creating' => 'Creating Release Type...',
		'confirm_delete' => 'Do you really want to delete this release type?',

		'return_to_list' => 'Return to release type list',

		'list_title' => 'Manage Release Types',
	],
	'release' => [
		'singular' => 'Release',
		'plural' => 'Releases',

		# Fields and columns
		'release_type' => 'Release Type',
		'url' => 'URL',
		'url_placeholder' => 'https://mypodcasthost.com/new-release-media-url.mp4',
		'size' => 'Release Size',
	],
	'permissions' => [
		'labels' => [
			'access_release_types' => 'Manage Release Types',
			'access_tags' => 'Manage Tags',
			'access_shows_all' => 'Manage All Shows',
			'access_shows' => 'Manage Specific Shows',
			'access_episodes_all' => 'Manage All Episodes',
			'access_episodes' => 'Manage Episodes for Specific Show',
			'access_show' => 'Manage',
		],
		'tab_labels' => [
			'general' => 'Podcast',
			'shows' => 'Shows',
		]
	]
];