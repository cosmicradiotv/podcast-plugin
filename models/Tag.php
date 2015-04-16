<?php

namespace CosmicRadioTV\Podcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;

/**
 * Class Tag
 *
 * @package CosmicRadioTV\Podcast\Models
 * @property      int                  $id         ID
 * @property      string               $name       Tag text
 * @property      string               $slug       URL Slug
 * @property      Carbon               $created_at Tag's creation time
 * @property      Carbon               $updated_at Tag's update time
 * @property-read Collection|Episode[] $episodes   Episodes with this tag
 * @method \October\Rain\Database\Relations\BelongsToMany episodes()
 */
class Tag extends Model
{

    use Sluggable;
    use Validation;

    protected $table = 'cosmicradiotv_podcast_tags';

    protected $slugs = ['slug' => 'name'];

    protected $fillable = ['name', 'slug'];

    public $rules = [
        'name' => ['required'],
        'slug' => ['alpha_dash', 'unique:cosmicradiotv_podcast_tags'],
    ];

    /*
     * Relations
     */

    public $belongsToMany = [
        'episodes' => ['CosmicRadioTV\Podcast\Models\Episode', 'table' => 'cosmicradiotv_podcast_episodes_tags'],
    ];
}