<?php

namespace Url;

use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Object;
use Tracy\Dumper;
use Tracy\IBarPanel;

class RequestPanel extends Object implements IBarPanel
{
    /** @var  Request */
    private $appRequest;

    /** @var \HttpRequest  */
    private $httpRequest;

    /** @var Router  */
    private $router;

    public function __construct(IRequest $httpRequest, Router $router)
    {
        $this->httpRequest = $httpRequest;
        $this->router = $router;
        $this->appRequest = $router->match($httpRequest);
    }

    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    function getTab()
    {
        ob_start();
        echo 'APP_REQUEST';
        return ob_get_clean();
    }

    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    function getPanel()
    {
        ob_start();
        echo Dumper::toHtml($this->appRequest);
        return ob_get_clean();
    }

}