<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;

class NavigationFacade extends Object
{
    /** @var NavigationReader */
    private $navigationReader;

    /** @var TreeBuilder  */
    private $treeBuilder;

    /** @var Cache */
    private $cache;


    public function __construct(
        NavigationReader $navigationReader,
        TreeBuilder $treeBuilder,
        IStorage $storage
    ) {
        $this->treeBuilder = $treeBuilder;

        $this->cache = new Cache($storage, 'navigations');
        $this->navigationReader = $navigationReader;
    }


    public function saveCategory(Category $category)
    {
        // todo
    }


    public function removeCategory($categoryId)
    {
        // todo
    }
    

    /**
     * @param int $navigationId
     * @param bool $useCache
     * @return Node
     */
    public function getEntireNavigation($navigationId, $useCache = true)
    {
        $navigationCacheKey = "nav-$navigationId";
        if ((bool)$useCache) {
            $nav = $this->cache->load($navigationCacheKey);
            if ($nav !== null) {
                return $nav;
            }
        }

        $nodes = $this->navigationReader->getEntireNavigation($navigationId);
        $nav = $this->treeBuilder->buildTree($nodes);

        if ((bool)$useCache) {
            $this->cache->save($navigationCacheKey, $nav);
        }

        return $nav;
    }


    private function loadNavigationFromCache($navigationId)
    {
        return $this->cache->load("nav-$navigationId", function (&$dependencies) use ($navigationId){
            $nodes = $this->navigationReader->getEntireNavigation($navigationId);
            return $this->treeBuilder->buildTree($nodes);
        });
    }

}