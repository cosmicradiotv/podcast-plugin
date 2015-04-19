<?php namespace CosmicRadioTV\Podcast\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Release Types Back-end Controller
 */
class ReleaseTypes extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['cosmicradiotv.podcast.access_release_types'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('CosmicRadioTV.Podcast', 'podcast', 'releasetypes');
    }
}