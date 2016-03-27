<?php

namespace App\AdminModule\Presenters;

use App\Components\ILocaleSwitcherControlFactory;
use Nette\Application\ForbiddenRequestException;
use App\Components\IPageTitleControlFactory;
use blitzik\FlashMessages\TFlashMessages;
use Nette\Localization\ITranslator;
use App\Presenters\AppPresenter;

abstract class ProtectedPresenter extends AppPresenter
{
    use TFlashMessages;

    /** @persistent */
    public $locale;

    /**
     * @var ILocaleSwitcherControlFactory
     * @inject
     */
    public $localeSwitcherFactory;

    /**
     * @var ITranslator
     * @inject
     */
    public $translator;

    /**
     * @var IPageTitleControlFactory
     * @inject
     */
    public $pageTitleFactory;


    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('admin.signIn', 'warning');
            $this->redirect(':Users:Front:Auth:login');
        }

        if ($this->session->hasSection('cms_localization')) {
            $localizationSection = $this->session->getSection('cms_localization');
            if ($localizationSection->locale !== null) {
                $this->locale = $localizationSection->locale;
            }
        }
    }


    /**
     * Finds layout template file name.
     * @return string
     * @internal
     */
    public function findLayoutTemplateFile()
    {
        return __DIR__ . '/templates/@layout.latte';
    }


    protected function createComponent($name)
    {
        $ucname = ucfirst($name);
        $method = 'createComponent' . $ucname;
        $presenterReflection = $this->getReflection();
        if ($presenterReflection->hasMethod($method)) {
            $methodReflection = $presenterReflection->getMethod($method);
            $this->checkRequirements($methodReflection);

            if ($methodReflection->hasAnnotation('Actions')) {
                $actions = explode(',', $methodReflection->getAnnotation('Actions'));

                foreach ($actions as $key => $action) {
                    $actions[$key] = trim($action);
                }

                if (!empty($actions) and !in_array($this->getAction(), $actions)) {
                    throw new ForbiddenRequestException("Creation of component '$name' is forbidden for action '$this->action'.");
                }
            }
        }

        return parent::createComponent($name);
    }


    protected function createComponentLocaleSwitcher()
    {
        $comp = $this->localeSwitcherFactory->create();
        $comp->setLocale($this->locale);

        return $comp;
    }
}