<?php

namespace Url;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Monolog\Logger;
use Nette\Application\Routers\RouteList;
use Nette;
use Tracy\Debugger;

class Router extends RouteList
{
    const ROUTE_NAMESPACE = 'blitzikRoute';

    /** @var EntityManager  */
    private $em;

    /** @var  Nette\Caching\Cache */
    private $cache;

    /** @var Logger  */
    private $logger;

    /** @var  EntityRepository */
    private $urlRepository;

    /** @var  array */
    private $appLanguages = ['cz' => true, 'en' => false];

    public function __construct(
        EntityManager $em,
        Nette\Caching\IStorage $storage,
        Logger $logger
    ) {
        $this->em = $em;
        $this->cache = new Nette\Caching\Cache($storage, self::ROUTE_NAMESPACE);

        $this->urlRepository = $em->getRepository(Url::class);
        $this->logger = $logger->channel('router');
    }

    /**
     * CLI commands run from app/console.php
     *
     * Maps HTTP request to a Request object.
     * @return Nette\Application\Request|NULL
     */
    public function match(Nette\Http\IRequest $httpRequest)
    {
        $path = $this->prepareUrlPath($httpRequest);

        // language
        $regExp = '~^(' .implode('|', array_keys($this->appLanguages)). ')/~';
        $lang = $this->resolveLanguage($path, $regExp);

        $path = preg_replace($regExp, '', $path);

        /** @var Url $urlEntity */
        $urlEntity = $this->loadUrlEntity($path);

        if ($urlEntity === null) { // no route found
            return null;
        }

        if ($urlEntity->getActualUrlToRedirect() === null) {
            $presenter = $urlEntity->getPresenter();
            $internal_id = $urlEntity->internalId;
        } else {
            $presenter = $urlEntity->getActualUrlToRedirect()->getPresenter();
            $internal_id = $urlEntity->getActualUrlToRedirect()->internalId;
        }

        $params = $httpRequest->getQuery();
        $params['action'] = $urlEntity->getAction();
        $params['lang'] = $lang;
        if ($internal_id !== null) {
            $params['id'] = $internal_id;
        }

        if (isset($params['p']) and !Nette\Utils\Validators::is($params['p'], 'numericint:1..')) {
            unset($params['p']); // if page of paginator isn't integer number, then reset the page
        }

        return new Nette\Application\Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles()
        );
    }

    /**
     * Constructs absolute URL from Request object.
     * @return string|NULL
     */
    public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
    {
        $appPath = $appRequest->getPresenterName().':'.$appRequest->getParameter('action').':'.$appRequest->getParameter('id');

        /** @var Url $urlEntity */
        $cachedResult = $this->cache->load($appPath, function (& $dependencies) use ($appRequest) {;
            $req['presenter'] = $appRequest->getPresenterName();
            $req['action'] = $appRequest->getParameter('action');
            $internal_id = $req['internalId'] = $appRequest->getParameter('id');

            $fallback = false;
            if (isset($req['internalId'])) {
                /** @var Url $url */
                $url = $this->urlRepository->findOneBy($req);
                if ($url === null) {
                    $fallback = true;
                    unset($req['internalId']);
                    $url = $this->urlRepository->findOneBy($req);
                }

            } else {
                $url = $this->urlRepository->findOneBy($req);
            }

            if ($url === null) {
                $this->logger
                    ->addWarning(
                        sprintf('No route found.
                                 TIME: %s
                                 | presenter: %s
                                 | action: %s
                                 | id %s',
                                date('Y-m-d H:i:s'),
                                $req['presenter'],
                                $req['action'],
                                $internal_id)
                    );
                return null;
            }

            $dependencies = [Nette\Caching\Cache::TAGS => 'route/' . $url->getId()];
            return [$url, $fallback];
        });
        $urlEntity = $cachedResult[0];
        $fallback = $cachedResult[1];

        if ($urlEntity === null) {
            return null;
        }

        $baseUrl = 'http://' . $refUrl->getAuthority() . $refUrl->getBasePath();

        if ($urlEntity->getActualUrlToRedirect() === null) {
            $path = $urlEntity->urlPath;
        } else {
            $path = $urlEntity->getActualUrlToRedirect()->urlPath;
        }

        $params = $appRequest->getParameters();

        $lang = (isset($params['lang']) and !$this->appLanguages[$params['lang']]) ? $params['lang'].'/' : null;
        $resultUrl = $baseUrl . $lang . Nette\Utils\Strings::webalize($path, '/');

        unset($params['action'], $params['lang']);
        if ($fallback === false) {
            unset($params['id']);
        }

        // articles pagination on main page
        if (isset($params['do']) and $params['do'] == 'articlesOverview-vs-paginate') {
            $params['p'] = $params['articlesOverview-vs-page'];
            unset($params['articlesOverview-vs-page'], $params['do']);
        }

        $q = http_build_query($params, null, '&');
        if ($q != '') {
            $resultUrl .= '?' . $q;
        }

        return $resultUrl;
    }

    private function prepareUrlPath(Nette\Http\IRequest $httpRequest)
    {
        $url = $httpRequest->getUrl();

        $basePath = $url->getPath(); // /subdom/blog/en/test/aa
        $path = substr($basePath, \mb_strlen($url->getBasePath())); // en/test/aa
        if ($path !== '') {
            $path = rtrim(rawurldecode($path), '/');
        }

        return $path;
    }

    private function resolveLanguage($path, $regexp)
    {
        if (preg_match($regexp, $path, $matches)) {
            $lang = $matches[1];
        } else {
            $lang = array_search(true, $this->appLanguages);
        }

        return $lang;
    }

    /**
     * @param $path
     * @return null|Url
     */
    private function loadUrlEntity($path)
    {
        $path = $path === '' ? null : $path;
        /** @var Url $urlEntity */
        $urlEntity = $this->cache->load($path, function (& $dependencies) use ($path) {
            /** @var Url $urlEntity */
            $urlEntity = $this->urlRepository->findOneBy(['urlPath' => $path]);
            if ($urlEntity === null) {
                $this->logger->addError(sprintf('Page not found. TIME: %s | URL_PATH: %s', date('Y-m-d H:i:s'), $path));
                return null;
            }

            $dependencies = [Nette\Caching\Cache::TAGS => 'route/' . $urlEntity->getId()];
            return $urlEntity;
        });

        return $urlEntity;
    }

}