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

class LogOverviewControl extends BaseControl
{
    /** @var IPaginatorFactory */
    private $paginatorFactory;

    /** @var LogQuery */
    private $logQuery;

    /** @var LogFacade */
    private $logFacade;


    public function __construct(
        LogQuery $logQuery,
        LogFacade $logFacade,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->logQuery = $logQuery;
        $this->logFacade = $logFacade;
        $this->paginatorFactory = $paginatorFactory;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/logOverview.latte');

        $resultSet = $this->logFacade->fetchLogs($this->logQuery);

        $paginator = $this['vp']->getPaginator();
        $resultSet->applyPaginator($paginator, 15);

        $template->logs = $resultSet->toArray();

        $template->render();
    }


    protected function createComponentVp()
    {
        $comp = $this->paginatorFactory->create();
        $comp->onPaginate[] = function () {
            $this->redrawControl();
        };

        return $comp;
    }
}


interface ILogOverviewControlFactory
{
    /**
     * @param LogQuery $logQuery
     * @return LogOverviewControl
     */
    public function create(LogQuery $logQuery);
}