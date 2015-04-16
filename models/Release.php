<?php

namespace CosmicRadioTV\Podcast\Models;

use Carbon\Carbon;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Validation;

/**
 * Release
 *
 * @package CosmicRadioTV\Podcast\Models
 * @property      int         $id              ID
 * @property      int         $episode_id      Episode ID
 * @property      int         $release_type_id Release type ID
 * @property      string      $url             Release URL
 * @property      int         $size            Release size in bytes
 * @property      string      $description     Show description
 * @property      Carbon      $created_at      Show creation time
 * @property      Carbon      $updated_at      Show update time
 * @property-read Episode     $episode         Episode
 * @property-read ReleaseType $type            Release type
 * @method \October\Rain\Database\Relations\BelongsTo episode()
 * @method \October\Rain\Database\Relations\BelongsTo type()
 */
class Release extends Model
{

    use Validation;

    protected $table = 'cosmicradiotv_podcast_releases';

    protected $fillable = ['episode_id', 'release_type_id', 'url', 'size'];

    public $rules = [
        'episode_id'      => ['required', 'exists:cosmicradiotv_podcast_episodes,id'],
        'release_type_id' => ['required', 'exists:cosmicradiotv_podcast_release_types,id'],
        'url'             => ['require', 'url'],
        'size'            => ['numeric'],
        'description'     => [],
    ];

    /*
     * Relations
     */

    public $belongsTo = [
        'episode' => ['CosmicRadioTV\Podcast\Models\Episode'],
        'type'    => ['CosmicRadioTV\Podcast\Models\ReleaseType'],
    ];

}