<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestIdMiddleware implements MiddlewareInterface
{
    public const HEADER = 'X-Request-Id';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // pega do header, ou gera um novo
        $requestId = $request->getHeaderLine(self::HEADER);
        if ($requestId === '') {
            $requestId = bin2hex(random_bytes(16));
        }

        $request = $request->withAttribute('request_id', $requestId);

        // segue o fluxo
        $response = $handler->handle($request);

        // devolve o header no response
        return $response->withHeader(self::HEADER, $requestId);
    }
}
