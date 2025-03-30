<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpForbiddenException;
use Psr\Http\Server\MiddlewareInterface;

class RoleCheckMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $_SESSION ?? [];
        $role = $session['role'] ?? null;

        if (!$role || $role === 'user') {
            throw new HttpForbiddenException($request, 'Accès refusé.');
        }

        return $handler->handle($request);
    }
}
