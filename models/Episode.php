<?php

namespace CosmicRadioTV\Podcast\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

/**
 * Episode
 *
 * @package CosmicRadioTV\Podcast\Models
 * @property      int              $id         ID
 * @property      int              $show_id    Show's ID
 * @property      string           $title      Episode's title
 * @property      string           $slug       URL slug
 * @property      string           $summary    Episode description
 * @property      string           $content    Episode's Show notes
 * @property      int              $length     Episode's length in seconds
 * @property      Carbon           $release    Episode's release time
 * @property      bool             $published  Published state
 * @property      Carbon           $created_at Show creation time
 * @property      Carbon           $updated_at Show update time
 * @property-read Show             $show       The show of this episode
 * @property-read Collection|Tag[] $tags       Tags for this episode
 * @property      File             $image      Shows image
 * @method \October\Rain\Database\Relations\BelongsTo show()
 * @method \October\Rain\Database\Relations\BelongsToMany tags()
 * @method \October\Rain\Database\Relations\MorphOne image()
 */
class Episode extends Model
{

    use Sluggable;
    use Validation {
        makeValidator as baseMakeValidator;
    };

    protected $table = 'cosmicradiotv_podcast_episodes';

    protected $slugs = ['slug' => 'title'];

    protected $dates = ['release'];

    protected $fillable = ['show_id', 'title', 'slug', 'summary', 'content', 'length', 'release', 'published'];

    public $rules = [
        'show_id'   => ['required', 'exists:cosmicradiotv_podcast_shows,id'],
        'title'     => ['required'],
        // Unique rule: NULL gets replaced with ID if exists, must have all 3 for show_id based bellow
        'slug'      => ['alpha_dash', 'unique:cosmicradiotv_podcast_episodes,slug,NULL'],
        'summary'   => [],
        'content'   => [],
        'length'    => ['numeric', 'min:0'],
        'release'   => ['date'],
        'published' => ['boolean']
    ];

    /*
     * Relations
     */

    public $belongsTo = [
        'show' => ['CosmicRadioTV\Podcast\Models\Show'],
    ];

    public $belongsToMany = [
        'tags' => ['CosmicRadioTV\Podcast\Models\Tag', 'table' => 'cosmicradiotv_podcast_episodes_tags'],
    ];

    public $attachOne = [
        'image' => ['System\Models\File']
    ];

    /**
     * Modifies validation rules so unique checks take show_id into account
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
        foreach ($rules as $field => $ruleParts) {
            foreach ($ruleParts as $key => $rulePart) {
                if (starts_with($rulePart, 'unique')) {
                    // Has format up to current instance's ID
                    if ($data['show_id']) {
                        $ruleParts[$key] .= ',id,show_id,' . $data['show_id'];
                    }
                }
            }
            $rules[$field] = $ruleParts;
        }

        return self::baseMakeValidator($data, $rules, $customMessages, $attributeNames);
    }

    /**
     * Modified unique slug finder to take into account the show_id
     * @param string $name The database column name.
     * @param string $value The desired column value.
     * @return string A safe value that is unique.
     */
    protected function getSluggableUniqueAttributeValue($name, $value)
    {
        $counter = 1;
        $separator = $this->getSluggableSeparator();

        // Remove any existing suffixes
        $_value = preg_replace('/'.preg_quote($separator).'[0-9]+$/', '', trim($value));

        while ($this->newQuery()->where('show_id', $this->show_id)->where($name, $_value)->count() > 0) {
            $counter++;
            $_value = $value . $separator . $counter;
        }

        return $_value;
    }

}