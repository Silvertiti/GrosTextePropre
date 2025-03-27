<?php

namespace App\Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;

class TuteurMiddleware implements MiddlewareInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {   

        if($this->container->get('session')->get('role') != 'tuteur'){
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('login');
            $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        $response = $handler->handle($request);
        return $response;
    }
}
