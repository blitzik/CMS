<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Dashboard\Components;

use App\Components\BaseControl;
use blitzik\IPaginatorFactory;
use Log\Facades\LogFacade;
use Log\Query\LogQuery;
use Nette\Application\IPresenter;
use Nette\Application\UI\Form;

class LogOverviewControl extends BaseControl
{
    /** @persistent */
    public $type;

    /** @persistent */
    public $event;

    /** @var IPaginatorFactory */
    private $paginatorFactory;

    /** @var LogFacade */
    private $logFacade;

    /** @var array */
    private $logTypesNames = [];

    /** @var array */
    private $logEvents = [];


    public function __construct(
        LogFacade $logFacade,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->logFacade = $logFacade;
        $this->paginatorFactory = $paginatorFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/logOverview.latte');

        $resultSet = $this->logFacade
                          ->fetchLogs(
                              (new LogQuery())
                              ->byLogEvent($this->event)
                              ->descendingOrder()
                          );

        $paginator = $this['vp']->getPaginator();
        $resultSet->applyPaginator($paginator, 15);

        $template->logs = $resultSet->toArray();

        //$this->logFacade->findEventsByType(3);

        $template->render();
    }


    /**
     * This method will be called when the component (or component's parent)
     * becomes attached to a monitored object. Do not call this method yourself.
     * @param  Nette\ComponentModel\IComponent
     * @return void
     */
    protected function attached($presenter)
    {
        parent::attached($presenter);

        if ($presenter instanceof IPresenter) {
            $this->logTypesNames = $this->logFacade->findTypesNames();
            $this['filter-type']->setItems($this->logTypesNames);

            if ($this->type !== null) {
                $this['filter-type']->setDefaultValue($this->type);

                $this->logEvents = $this->logFacade->findEventsByType($this->type);
                $this['filter-event']->setItems($this->logEvents);

                if ($this->event !== null) {
                    $this['filter-event']->setDefaultValue($this->event);
                }
            }
        }
    }


    protected function createComponentVp()
    {
        $comp = $this->paginatorFactory->create();
        $comp->onPaginate[] = function () {
            $this->redrawControl();
        };

        return $comp;
    }


    protected function createComponentFilter()
    {
        $form = new Form();

        $form->addSelect('type', 'Type')
                ->setPrompt('All');

        $form->addSelect('event', 'Event')
                ->setPrompt('All');

        $form->addSubmit('filter', 'filter');

        $form->onSuccess[] = [$this, 'processFiltering'];

        return $form;
    }


    public function processFiltering(Form $form, $values)
    {
        $this->type = $values->type;
        $this->event = $values->event;

        $this->redirect('this');
    }
}


interface ILogOverviewControlFactory
{
    /**
     * @return LogOverviewControl
     */
    public function create();
}