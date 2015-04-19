<?php namespace CosmicRadioTV\Podcast\Controllers;

use BackendMenu;
use BackendAuth;
use Backend\Classes\Controller;
use CosmicRadioTV\Podcast\Models\Show;

/**
 * Shows Back-end Controller
 */
class Shows extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['cosmicradiotv.podcast.access_show*'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('CosmicRadioTV.Podcast', 'podcast', 'shows');
    }
}