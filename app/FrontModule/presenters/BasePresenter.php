<?php

namespace App\FrontModule\Presenters;

use App\Presenters\AppPresenter;
use Nette;

class BasePresenter extends AppPresenter
{
    /** @persistent */
    public $locale;


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
        $this->template->googleAnalyticsMeasureCode = $this->options->google_analytics_measure_code;
    }

}