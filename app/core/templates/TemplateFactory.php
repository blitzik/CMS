<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.03.2016
 */

namespace App\Templates;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Users\Authorization\Authorizator;
use Nette\Application\UI;
use Nette;

class TemplateFactory extends \Nette\Bridges\ApplicationLatte\TemplateFactory
{
    /** @var Authorizator */
    private $authorizator;


    public function __construct(
        ILatteFactory $latteFactory,
        Nette\Http\IRequest $httpRequest,
        Nette\Http\IResponse $httpResponse,
        Nette\Security\User $user,
        Nette\Caching\IStorage $cacheStorage,
        Authorizator $authorizator
    ) {
        parent::__construct($latteFactory, $httpRequest, $httpResponse, $user, $cacheStorage);

        $this->authorizator = $authorizator;
    }


    /**
     * @return Template
     */
    public function createTemplate(UI\Control $control = null)
    {
        $template =  parent::createTemplate($control);

        $template->authorizator = $this->authorizator;

        return $template;
    }

}