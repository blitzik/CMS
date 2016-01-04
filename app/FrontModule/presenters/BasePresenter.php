<?php

namespace App\FrontModule\Presenters;

use App\Components\IMetaTagsControlFactory;
use App\Presenters\AppPresenter;
use Nette;

class BasePresenter extends AppPresenter
{
    /**
     * @var IMetaTagsControlFactory
     * @inject
     */
    public $metaTagsControlFactory;


    public function findLayoutTemplateFile()
    {
        if ($this->layout === FALSE) {
            return;
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . '@layout.latte';
    }

    /**
     * Common render method.
     * @return void
     */
    protected function beforeRender()
    {
        $this->template->blogTitle = $this->options->blog_title;
        $this->template->blogSubtitle = $this->options->blog_subtitle;
        $this->template->copyright = $this->options->copyright;
    }


    protected function createComponentMetas()
    {
        $comp = $this->metaTagsControlFactory->create();

        return $comp;
    }
}