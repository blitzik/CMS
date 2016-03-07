<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Url\Generators;

use Nette\Object;
use Url\Url;

class UrlGenerator extends Object
{
    /**
     * @param $urlPath
     * @param $presenter
     * @param $action
     * @param null $internal_id
     * @return Url
     */
    public static function create($urlPath, $presenter, $action, $internal_id = null)
    {
        $url = new Url();
        $url->setUrlPath($urlPath);
        $url->setDestination($presenter, $action);
        $url->setInternalId($internal_id);

        return $url;
    }
}