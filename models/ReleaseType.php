<?php

namespace CosmicRadioTV\Podcast\Models;

use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;

/**
 * Class ReleaseType
 *
 * @package CosmicRadioTV\Podcast\Models
 * @property      int                  $id       ID
 * @property      string               $name     Release type's name
 * @property      string               $slug     Release type's slug
 * @property      string               $type     Release type
 * @property      string               $filetype Release's filetype
 * @property-read Collection|Release[] $releases Releases that are of this type
 * @method \October\Rain\Database\Relations\HasMany releases()
 */
class ReleaseType extends Model
{

    use Sluggable;
    use Validation {
        makeValidator as baseMakeValidator;
    }

    protected $table = 'cosmicradiotv_podcast_release_types';

    protected $slugs = ['slug' => 'name'];

    protected $fillable = ['name', 'slug', 'type', 'filetype'];

    public $timestamps = false;

    public $rules = [
        'name'     => ['required'],
        'slug'     => ['alpha_dash', 'unique:cosmicradiotv_podcast_release_types'],
        'type'     => ['required', 'in:audio,video,youtube'],
        'filetype' => [],
    ];

    /*
     * Relations
     */

    public $hasMany = [
        'releases' => ['CosmicRadioTV\Podcast\Models\Release','delete' => 'true'],
    ];


    /**
     * Returns a custom validator that also validates filetype depending on type
     *
     * @param $data
     * @param $rules
     * @param $customMessages
     * @param $attributeNames
     *
     * @return \Illuminate\Validation\Validator
     */
    protected static function makeValidator($data, $rules, $customMessages, $attributeNames)
    {
        $validator = self::baseMakeValidator($data, $rules, $customMessages, $attributeNames);

        // Audio & video
        $validator->sometimes('filetype', ['required'], function ($input) {
            return in_array($input->type, ['audio', 'video']);
        });

        return $validator;
    }
}