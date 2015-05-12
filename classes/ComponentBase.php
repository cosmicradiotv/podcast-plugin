<?php

namespace CosmicRadioTV\Podcast\Classes;

use Cms\Classes\CodeBase;
use Cms\Classes\ComponentBase as OctoberComponentBase;

abstract class ComponentBase extends OctoberComponentBase {

    public function __construct(CodeBase $cmsObject = null, $properties = [])
    {
        parent::__construct($cmsObject, $properties);

        // Remove extra component from end
        if(ends_with($this->dirName, 'component')) {
            $this->dirName = substr($this->dirName, 0, -9);
        }
    }

}