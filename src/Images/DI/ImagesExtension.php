<?php

namespace Images\DI;

use App\Extensions\CompilerExtension;
use App\Fixtures\IFixtureProvider;
use Images\Fixtures\ImagesFixture;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\Statement;

class ImagesExtension extends CompilerExtension implements IEntityProvider, ITranslationProvider, IFixtureProvider
{
    /** @var array */
    private $defaults = [
        'fileRoot' => '%wwwDir%/uploads/images',
        'root' => '/uploads/images'
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
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);

        $imagesUploader = $cb->getDefinition($this->prefix('imagesUploader'));
        $imagesUploader->setArguments(['imageFileRoot' => $config['fileRoot']]);

        $imagesRemover = $cb->getDefinition($this->prefix('imagesRemover'));
        $imagesRemover->setArguments(['imageFileRoot' => $config['fileRoot']]);

        $log_imageSubscriber = $cb->getDefinition($this->prefix('log_imageSubscriber'));
        $log_imageSubscriber->addSetup('setImageFileRoot', ['fileRoot' => $config['fileRoot']]);
    }


    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Images' => 'Images\\*Module\\Presenters\\*Presenter']);
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return ['Images' => __DIR__ . '/..'];
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
                ImagesFixture::class
            ]
        ];
    }


}