<?php

namespace CosmicRadioTV\Podcast\classes;


/**
 * Class TitlePlaceholdersTrait
 *
 * @property \Cms\Classes\PageCode $page Current page
 */
trait TitlePlaceholdersTrait
{

    /**
     * Returns things to replace placeholders with.
     * Must be an object so models can be easily walked to.
     * For ease you can do return (object) [...];
     *
     * @return object
     */
    protected function getTitlePlaceholderReplaces()
    {
        return new \stdClass();
    }

    /**
     * Update page's title using placeholders
     */
    protected function updateTitle()
    {
        $raw = $this->page->title;

        $this->page->title = $this->replacePlaceholders($raw, $this->getTitlePlaceholderReplaces());
    }

    /**
     * Replaces placeholders in title
     * Matches all {{rule}} placeholders, escaped @{{ blocks (w/ optional }})
     * Technically also matches "{{something"
     *
     * @param $title        string Title with placeholders
     * @param $replacedWith object Things to replace with
     *
     * @return mixed
     */
    protected function replacePlaceholders($title, $replacedWith)
    {
        return preg_replace_callback('/(\@)?\{\{([\w\d\.]+)(?:\}\})/', function ($matches) use ($replacedWith) {
            if ($matches[1] == '@') {
                // Escape
                return substr($matches[0], 1);
            }
            // Find the requested thing
            $value = object_get($replacedWith, $matches[2]);
            if ($value) {
                return $value;
            }

            // No idea
            return $matches[0];
        }, $title);
    }
}