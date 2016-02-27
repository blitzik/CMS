<?php

namespace Pages\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Translation\DI\ITranslationProvider;

class PagesExtension extends CompilerExtension implements IEntityProvider, ITranslationProvider
{
    private $defaults = [
        'pagesPerPage' => 10,
        'texy' => [
            'images' => [
                'root' => null,
                'fileRoot' => null
            ]
        ]
    ];


    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $config = $this->getConfig() + $this->defaults;
        $this->setConfig($config);

        $cb = $this->getContainerBuilder();

        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/services.neon'), $this->name);

        $pagesOverview = $cb->getDefinition($this->prefix('pagesOverviewControlFactory'));
        $pagesOverview->addSetup('setPagesPerPage', [$config['pagesPerPage']]);

        $texyFactory = $cb->getDefinition($this->prefix('texyFactory'));
        $texyFactory->setArguments(
            [
                'imagesRoot' => $config['texy']['images']['root'],
                'imagesFileRoot' => $config['texy']['images']['fileRoot'],
            ]
        );
    }


    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $config = $this->getConfig();

        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Pages' => 'Pages\\*Module\\Presenters\\*Presenter']);
        $this->setPresenterMapping($cb, ['Tags' => 'Tags\\*Module\\Presenters\\*Presenter']);

        $cb->getDefinition($this->prefix('pagesOverviewControlFactory'))
            ->addSetup('setPagesPerPage', [$config['pagesPerPage']]);

        $latteFactory = $cb->getDefinition('latte.latteFactory');
        $latteFactory->addSetup('addFilter', [null, ['@Pages\\Filters\\FilterLoader', 'loader']]);
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return [
            'Pages' => __DIR__ . '/..',
            'Tags'  => __DIR__ . '/..',
            'Comments'  => __DIR__ . '/..'
        ];
    }


    /**
     * Return array of directories, that contain resources for translator.
     *
     * @return string[]
     */
    function getTranslationResources()
    {
        return [
            __DIR__ . '/../lang'
        ];
    }


}