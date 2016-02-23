<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 23.02.2016
 */

namespace Url\Services;

use Nette\Http\IRequest;
use Nette\Object;
use Url\Exceptions\Runtime\NoLocalesSetException;

class UrlPath extends Object
{
    /** @var IRequest */
    private $httpRequest;

    /** @var array */
    private $locales = [];

    /** @var string */
    private $localeRegexp;


    // ---


    /** @var string */
    private $path;

    /** @var string */
    private $locale;


    public function __construct(IRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }


    /**
     * @param array $locales
     */
    public function setPredefinedLocales(array $locales)
    {
        $this->locales = $locales;
    }


    /**
     * @param bool $withoutLocale
     * @return string
     * @throws NoLocalesSetException
     */
    public function getPath($withoutLocale = false)
    {
        if (!isset($this->path)) {
            $this->path = $this->prepareUrlPath($this->httpRequest);
        }

        if ($withoutLocale === true) {
            if (empty($this->locales)) {
                throw new NoLocalesSetException;
            }
            // clears locale from path only if the locale is found
            // so if there is no matching locale, it will return entire path
            // including nonexisting "locale"
            return $this->clearLocaleFromPath($this->path);
        }

        return $this->path;
    }


    /**
     * @return string
     */
    public function getLocale()
    {
        if (!isset($this->locale)) {
            $this->locale = $this->processLocale($this->getPath());
        }

        return $this->locale;
    }


    /**
     * @param IRequest $httpRequest
     * @return string
     */
    private function prepareUrlPath(IRequest $httpRequest)
    {
        $url = $httpRequest->getUrl();
        $basePath = $url->getPath();

        $path = mb_substr($basePath, \mb_strlen($url->getBasePath()));
        if ($path !== '') {
            $path = rtrim(rawurldecode($path), '/');
        }

        return $path;
    }


    /**
     * @return string
     */
    private function getLocaleRegexp()
    {
        if (isset($this->localeRegexp)) {
            return $this->localeRegexp;
        }

        return '~^(?P<locale>' .(implode('|', array_keys($this->locales))). ')/~';
    }


    /**
     * Returns locale from path
     *
     * @param string $path
     * @return string
     */
    private function processLocale($path)
    {
        $matches = [];
        $r = preg_match($this->getLocaleRegexp(), $path, $matches);
        if ($r === 0) { // none of the predefined locales were found
            return array_search(true, $this->locales); // returns default locale
        }

        return $matches['locale'];
    }


    /**
     * @param string $path
     * @return string
     */
    private function clearLocaleFromPath($path)
    {
        return preg_replace($this->getLocaleRegexp(), '', $path);
    }

}