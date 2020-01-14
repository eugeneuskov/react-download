<?php


namespace app\middleware;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as HttpResponse;

/**
 * Class Router
 *
 * @property GroupCountBased $dispatcher
 *
 * @package app\middleware
 */
final class Router
{
    /**
     * @var GroupCountBased
     */
    private $dispatcher;

    /**
     * Router constructor.
     * @param RouteCollector $routes
     */
    public function __construct(RouteCollector $routes)
    {
        $this->dispatcher = new GroupCountBased($routes->getData());
    }

    /**
     * @param ServerRequestInterface $request
     * @return HttpResponse
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return new HttpResponse(
                    404,
                    ['Content-Type' => 'text/plain'],
                    'Not found'
                );
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new HttpResponse(
                    405,
                    ['Content-Type' => 'text/plain'],
                    'Method not allowed'
                );
            case Dispatcher::FOUND:
                $params = $routeInfo[2];
                return $routeInfo[1]($request, ... array_values($params));
        }

        throw new LogicException('Something went wrong in routing.');
    }
}