<?php

use Tester\Assert as Assert;
use App\Exceptions\LogicExceptions;

require '../../bootstrap.php';



$url = new \Url\Url;
$url->setDestination('Module:Presenter', 'action');

Assert::same('Module:Presenter:action', $url->getDestination());

Assert::same('Module:Presenter', $url->getPresenter());

Assert::same('action', $url->getAction());



$url2 = new \Url\Url;
$url2->setDestination('Presenter', 'action');

Assert::same('Presenter:action', $url2->getDestination());

Assert::same('Presenter', $url2->getPresenter());

Assert::same('action', $url2->getAction());



$url3 = new \Url\Url;
$url3->setDestination('Module:Presenter:action');

Assert::same('Module:Presenter:action', $url3->getDestination());

Assert::same('Module:Presenter', $url3->getPresenter());

Assert::same('action', $url3->getAction());



$url4 = new \Url\Url;
$url4->setDestination('Module:Module:Presenter:action');

Assert::same('Module:Module:Presenter:action', $url4->getDestination());

Assert::same('Module:Module:Presenter', $url4->getPresenter());

Assert::same('action', $url4->getAction());



Assert::exception(
    function () {
        $url = new \Url\Url;
        $url->setDestination('Module:Pres3nter:action');
    },
  '\App\Exceptions\LogicExceptions\InvalidArgumentException',
  'Wrong format of argument $destination. Check if action have lower-case characters.'
);



Assert::exception(
    function () {
        $url = new \Url\Url;
        // action can have only lower-case letters
        $url->setDestination('Module:Presenter', 'Action');
    },
    '\App\Exceptions\LogicExceptions\InvalidArgumentException',
    'Wrong format of argument $destination. Check if action have lower-case characters.'
);