<?php

namespace App\Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Doctrine\ORM\EntityManager;
use App\Model\User;

class UserMiddleware implements MiddlewareInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {   

        if($this->container->get('session')->get('role') == NULL){
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('login');
            $response = $this->container->get(ResponseFactoryInterface::class)->createResponse();
            return $response
                ->withHeader('Location', $url)
                ->withStatus(302);
        }
        
        $em = $this->container->get(EntityManager::class);

        $idUser = $this->container->get('session')->get('idUser'); 
        $user = $em->getRepository(User::class)->find($idUser);
        $this->container->get('view')->getEnvironment()->addGlobal('user', $user);
        $request = $request->withAttribute('user', $user);

        $response = $handler->handle($request);
        return $response;
    }
}
