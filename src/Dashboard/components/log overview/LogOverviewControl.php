<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Dashboard\Components;

use Nette\Application\IPresenter;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use blitzik\IPaginatorFactory;
use Log\Facades\LogFacade;
use Log\Query\LogQuery;
use Tracy\Debugger;

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

    /** @var LogQuery */
    private $logQuery;


    public function __construct(
        LogFacade $logFacade,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->logFacade = $logFacade;
        $this->paginatorFactory = $paginatorFactory;

        $this->logQuery = new LogQuery();
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/logOverview.latte');

        $template->_form = $this['filter'];

        $resultSet = $this->logFacade
                          ->fetchLogs($this->logQuery->descendingOrder());

        $paginator = $this['vp']->getPaginator();
        $resultSet->applyPaginator($paginator, 50);

        $template->logs = $resultSet->toArray();

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
            if ($presenter->isAjax()) {
                if ($this->type !== null) {
                    $logEvents = $this->logFacade->findEventsByType($this->type);
                    $this->logQuery->byLogEvent(array_keys($logEvents));
                }

            } else {
                $logTypesNames = $this->logFacade->findTypesNames();
                $this['filter-type']->setItems($logTypesNames);

                if ($this->type !== null) {
                    $this['filter-type']->setDefaultValue($this->type);

                    $logEvents = $this->logFacade->findEventsByType($this->type);
                    $this['filter-event']->setItems($logEvents);

                    if ($this->event !== null) {
                        $this['filter-event']->setDefaultValue($this->event);
                        $this->logQuery->byLogEvent($this->event);
                    } else {
                        $this->logQuery->byLogEvent(array_keys($logEvents));
                    }
                }
            }
        }
    }


    protected function createComponentVp()
    {
        $comp = $this->paginatorFactory->create();
        $comp->onPaginate[] = function () {
            $this->redrawControl('overview');
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


    public function handleLoadEvents($value)
    {
        if ($this->presenter->isAjax()) {
            if ($value !== null) {
                $events = $this->logFacade->findEventsByType($value);
                $this['filter-event']->setItems($events);

            } else {
                $this['filter-event']->setItems([]);
            }

            $this->redrawControl();
        }

    }


    public function processFiltering(Form $form)
    {
        $values = $form->getHttpData();

        if ($this->type === null or $this->type == $values['type']) {
            $this->event = $values['event'] === '' ? null : $values['event'];
        } else {
            $this->event = null;
        }

        $this->type = $values['type'];

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