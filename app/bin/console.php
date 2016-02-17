<?php

/** @var \Nette\DI\Container $container */
$container = require __DIR__ . '/../bootstrap.php';

$container->getByType('Symfony\Component\Console\Application')->run();