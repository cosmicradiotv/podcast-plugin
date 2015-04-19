<?php namespace CosmicRadioTV\Podcast\Controllers;
use Flash;
use BackendMenu;
use BackendAuth;
use Backend\Classes\Controller;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Show;
/**
 * Episodes Back-end Controller
 */
class Episodes extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['cosmicradiotv.podcast.access_episode*'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('CosmicRadioTV.Podcast', 'podcast', 'episodes');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $episodeId) {
                if (!$episode = Episode::find($episodeId))
                    continue;

                $episode->delete();
            }

            Flash::success(e(trans('cosmicradiotv.podcast::lang.episode.delete_success_multiple')));
        }

        return $this->listRefresh();
    }

    public function listExtendQuery($query, $definition = null) {
        $user = BackendAuth::getUser();

        if (!$user->hasPermission(['cosmicradiotv.podcast.access_episodes_all'])) {
            $shows = Show::all();
            $show_ids_allowed = [];

            foreach ($shows as $show) {
                if ($user->hasPermission(['cosmicradiotv.podcast.access_show_'.$show->slug])) {
                    $show_ids_allowed[] = $show->id;
                }
            }

            $query->whereIn('show_id',$show_ids_allowed);
        }

    }
}