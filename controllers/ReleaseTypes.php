<?php namespace CosmicRadioTV\Podcast\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use CosmicRadioTV\Podcast\Models\ReleaseType;

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

    public function reorder()
    {
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/javascript/jquery.sortable.min.js');
        $this->addJs('/plugins/cosmicradiotv/podcast/assets/javascript/release_type_sort.js');
        $this->addCss('/plugins/cosmicradiotv/podcast/assets/stylesheet/release_type_sort.css');

        $this->pageTitle = e(trans('cosmicradiotv.podcast::lang.release_type.reorder'));
        $this->vars['records'] = ReleaseType::query()->orderBy('sort_order','asc')->get();
    }

    public function onSaveOrder()
    {
        $model = ReleaseType::find(post('item_ids')[0]);
        $model->setSortableOrder(post('item_ids'), post('item_orders'));

        if ($redirect = $this->makeRedirect('reorder')) {
            return $redirect;
        }
    }
}