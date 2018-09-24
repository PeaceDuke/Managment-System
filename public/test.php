<?php

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['errorHandler'] = function ($c) {
    return function ($request, $response, Exception $exception) use ($c) {
        return $c['response']->withStatus($exception->getCode())
            ->withHeader('Content-Type', 'text/html')
            ->write($exception->getMessage());
    };
};

$app->get('/', function () {
    throw new Exception('dsf', 500);
});

$app->run();