<?php

namespace CosmicRadioTV\Podcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

/**
 * Show
 *
 * @package CosmicRadioTV\Podcast\Models
 * @property      int                  $id          ID
 * @property      string               $name        Show name
 * @property      string               $slug        URL slug
 * @property      string               $description Show description
 * @property      Carbon               $created_at  Show creation time
 * @property      Carbon               $updated_at  Show update time
 * @property-read Collection|Episode[] $episodes    Show's episodes
 * @property      File                 $image       Show's image
 * @method \October\Rain\Database\Relations\HasMany  episodes()
 * @method \October\Rain\Database\Relations\MorphOne image()
 */
class Show extends Model
{

    use Sluggable;
    use Validation;

    protected $table = 'cosmicradiotv_podcast_shows';

    protected $slugs = ['slug' => 'name'];

    protected $fillable = ['name', 'slug', 'description'];

    public $rules = [
        'name'        => ['required'],
        'slug'        => ['alpha_dash', 'unique:cosmicradiotv_podcast_shows'],
        'description' => [],
    ];


    /*
     * Relations
     */

    public $hasMany = [
        'episodes' => ['CosmicRadioTV\Podcast\Models\Episode']
    ];

    public $attachOne = [
        'image' => ['System\Models\File']
    ];


}