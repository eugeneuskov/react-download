<?php

use app\controllers\DownloadController;
use app\middleware\Router;
use app\services\Download;
use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Http\Server;
use React\HttpClient\Client;
use FastRoute\{
    DataGenerator\GroupCountBased,
    RouteCollector,
    RouteParser\Std
};
use React\Socket\Server as SocketServer;

require __DIR__ . '/vendor/autoload.php';


$loop = Factory::create();
$client = new Client($loop);
$downloadService = new Download($client, Filesystem::create($loop));

# init routes
$routes = new RouteCollector(new Std(), new GroupCountBased());
$routes->post('/', new DownloadController($downloadService));

# init and start server
$server = new Server([new Router($routes)]);
$socket = new SocketServer('127.0.0.1:8000', $loop);
$server->listen($socket);

echo 'Listening on '
    . str_replace('tcp:', 'http:', $socket->getAddress())
    . PHP_EOL;

$loop->run();
