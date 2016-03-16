<?php

namespace Url;

use Url\Services\UrlParametersConverter;
use Nette\Application\Routers\RouteList;
use Localization\Facades\LocaleFacade;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\IStorage;
use Kdyby\Monolog\Logger;
use Nette;

class Router extends RouteList
{
    public $onUrlNotFound;

    const ROUTE_NAMESPACE = 'appRoute';

    /**
     * @var UrlParametersConverter
     */
    private $urlParametersConverter;

    /** @var  EntityRepository */
    private $urlRepository;

    /** @var Logger  */
    private $logger;

    /** @var  Nette\Caching\Cache */
    private $cache;

    /** @var EntityManager  */
    private $em;

    /** @var array */
    private $locales;

    /** @var LocaleFacade */
    private $localeFacade;

    public function __construct(
        UrlParametersConverter $urlParametersConverter,
        LocaleFacade $localeFacade,
        IStorage $storage,
        EntityManager $em,
        Logger $logger
    ) {
        $this->localeFacade = $localeFacade;
        $this->urlParametersConverter = $urlParametersConverter;
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
        $this->loadLocales();

        $urlPath = new Services\UrlPath($httpRequest);
        $urlPath->setPredefinedLocales($this->locales);

        /** @var Url $urlEntity */
        $urlEntity = $this->loadUrlEntity($urlPath->getPath(true));
        if ($urlEntity === null) { // no route found
            $this->onUrlNotFound($urlPath);
            return null;
        }

        if ($urlEntity->actualUrlToRedirect === null) {
            $presenter = $urlEntity->getPresenter();
            $internal_id = $urlEntity->internalId;
            $action = $urlEntity->getAction();
        } else {
            $presenter = $urlEntity->actualUrlToRedirect->getPresenter();
            $internal_id = $urlEntity->actualUrlToRedirect->internalId;
            $action = $urlEntity->actualUrlToRedirect->getAction();
        }

        $params = $httpRequest->getQuery();
        $params['action'] = $action;
        $params['locale'] = $urlPath->getLocale();

        $this->urlParametersConverter->in($urlEntity, $params); // todo

        if ($internal_id !== null) {
            $params['internal_id'] = $internal_id;
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
        $this->loadLocales();

        $appPath = $appRequest->getPresenterName().':'.$appRequest->getParameter('action').':'.$appRequest->getParameter('internal_id');

        /** @var Url $urlEntity */
        $cachedResult = $this->cache->load($appPath, function (& $dependencies) use ($appRequest) {;
            $presenter = $appRequest->getPresenterName();
            $action = $appRequest->getParameter('action');
            $internal_id = $appRequest->getParameter('internal_id');

            $fallback = false;
            if (isset($internal_id)) {
                /** @var Url $url */
                $urlEntity = $this->getUrlEntity($presenter, $action, $internal_id);
                if ($urlEntity === null) {
                    $fallback = true;
                    $urlEntity = $this->getUrlEntity($presenter, $action);
                }

            } else {
                $urlEntity = $this->getUrlEntity($presenter, $action);
            }

            if ($urlEntity === null) {
                $this->logger
                     ->addWarning(
                         sprintf('No route found
                                  | presenter: %s
                                  | action: %s
                                  | id %s',
                                 $presenter,
                                 $action,
                                 $internal_id)
                     );
                return null;
            }

            $dependencies = [Nette\Caching\Cache::TAGS => $urlEntity->getCacheKey()];
            return [$urlEntity, $fallback];
        });

        $urlEntity = $cachedResult[0];
        $fallback = $cachedResult[1];

        if ($urlEntity === null) {
            return null;
        }

        $baseUrl = 'http://' . $refUrl->getAuthority() . $refUrl->getBasePath();

        if ($urlEntity->actualUrlToRedirect === null) {
            $path = $urlEntity->urlPath;
        } else {
            $path = $urlEntity->actualUrlToRedirect->urlPath;
        }

        $params = $appRequest->getParameters();

        unset($params['action']);
        if ($fallback === false) {
            unset($params['internal_id']);
        }

        $defaultLocale = array_search(true, $this->locales);
        $locale = isset($params['locale']) ? $params['locale'] : $defaultLocale;
        unset($params['locale']);

        if ($defaultLocale === $locale) {
            $locale = '';
        } else {
            $locale .= '/';
        }

        $resultUrl = $baseUrl . $locale . Nette\Utils\Strings::webalize($path, '/.');

        $this->urlParametersConverter->out($urlEntity, $params); // todo

        $q = http_build_query($params, null, '&');
        if ($q != '') {
            $resultUrl .= '?' . $q;
        }

        return $resultUrl;
    }


    /**
     * @param $path
     * @return null|Url
     */
    private function loadUrlEntity($path)
    {
        /** @var Url $urlEntity */
        $urlEntity = $this->cache->load($path, function (& $dependencies) use ($path) {
            /** @var Url $urlEntity */
            $urlEntity = $this->em->createQuery(
                'SELECT u, rt FROM ' .Url::class. ' u
                 LEFT JOIN u.actualUrlToRedirect rt
                 WHERE u.urlPath = :urlPath'
            )->setParameter('urlPath', $path)
             ->getOneOrNullResult();

            if ($urlEntity === null) {
                $this->logger->addError(sprintf('Page not found. URL_PATH: %s', $path));
                return null;
            }

            $dependencies = [Nette\Caching\Cache::TAGS => $urlEntity->getCacheKey()];
            return $urlEntity;
        });

        return $urlEntity;
    }


    /**
     * @param $presenter
     * @param $action
     * @param $internal_id
     * @return Url
     */
    private function getUrlEntity($presenter, $action, $internal_id = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u, rt')
           ->from(Url::class, 'u')
           ->leftJoin('u.actualUrlToRedirect', 'rt')
           ->where('u.presenter = :p AND u.action = :a')
           ->setParameters(['p' => $presenter, 'a' => $action]);

        if ($internal_id !== null) {
            $qb->andWhere('u.internalId = :i')
               ->setParameter('i', $internal_id);
        }

        $url = $qb->getQuery()->setMaxResults(1)->getResult();

        if (empty($url)) {
            return null;
        }

        return $url[0];
    }

    
    private function loadLocales()
    {
        if ($this->locales !== null) {
            return;
        }

        $localization = $this->localeFacade->findAllLocales();

        foreach ($localization as $name => $locale) {
            $this->locales[$locale['code']] = $locale['default'];
        }
    }

}