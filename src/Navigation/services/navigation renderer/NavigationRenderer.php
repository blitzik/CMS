<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 15.01.2016
 */

namespace Navigations;

use Nette\Application\UI\ITemplateFactory;
use Nette\Object;

class NavigationRenderer extends Object
{
    /** @var NavigationReader */
    private $navigationReader;

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var TreeBuilder */
    private $treeBuilder;


    public function __construct(
        NavigationReader $navigationReader,
        ITemplateFactory $templateFactory,
        TreeBuilder $treeBuilder
    ) {
        $this->navigationReader = $navigationReader;
        $this->templateFactory = $templateFactory;
        $this->treeBuilder = $treeBuilder;
    }


    public function getRenderedNavigation($navigationId)
    {
        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/navigation.latte');

        $nodes = $this->navigationReader->getEntireNavigation($navigationId);
        $rootNode = $this->treeBuilder->buildTree($nodes);

        $template->node = $rootNode;

        return $template->__toString();
    }
}