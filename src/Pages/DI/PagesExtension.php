<?php

namespace Pages\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use App\Extensions\CompilerExtension;
use App\Fixtures\IFixtureProvider;
use Pages\Fixtures\PagesFixture;
use Nette;

class PagesExtension extends CompilerExtension
    implements IEntityProvider,
               ITranslationProvider,
               IFixtureProvider
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


        // URL parameters mapping
        $parametersConverter = $cb->getDefinition('url.urlParametersConverter');
        $mappings = Nette\Neon\Neon::decode(file_get_contents(__DIR__ . '/../url/parametersMapping.neon'));
        foreach ($mappings as $presenter => $mapping) {
            $parametersConverter->addSetup('addMapping', [$presenter, $mapping]);
        }
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


    /**
     * @return array
     */
    public function getDataFixtures()
    {
        return [
            __DIR__ . '/../fixtures/basic' => [
                PagesFixture::class
            ]
        ];
    }

}