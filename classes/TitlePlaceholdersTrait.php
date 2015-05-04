<?php

namespace CosmicRadioTV\Podcast\classes;


trait TitlePlaceholdersTrait
{

    /**
     * Replaces placeholders in title
     * Matches all {{rule}} placeholders, escaped @{{ blocks (w/ optional }})
     * Technically also matches "{{something"
     *
     * @param $title         string Title with placeholders
     * @param $replaced_with object Things to replace with
     *
     * @return mixed
     */
    protected function replacePlaceholders($title, $replaced_with)
    {
        return preg_replace_callback('/(\@)?\{\{([\w\d\.]+)(?:\}\})/', function ($matches) use ($replaced_with) {
            if ($matches[1] == '@') {
                // Escape
                return substr($matches[0], 1);
            }
            // Find the requested thing
            $value = object_get($replaced_with, $matches[2]);
            if ($value) {
                return $value;
            }

            // No idea
            return $matches[0];
        }, $title);
    }
}