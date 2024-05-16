<?php

declare(strict_types=1);

namespace App\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\RoadRunner\KeyValue\Serializer\IgbinarySerializer;
use Spiral\RoadRunner\KeyValue\StorageInterface;

final class Auth implements MiddlewareInterface
{
    public const TOKEN_HEADER = "Auth-Token";

    public function __construct(
        private StorageInterface $storage,
        private \Spiral\RoadRunner\KeyValue\Factory $factory
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(!$this->headerHasToken($request)) {
            throw new \Exception(static::TOKEN_HEADER . ' header is required.', StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
        }

        [$headerTokenValidStatus, $validatedRequest, $token] = $this->headerTokenIsValid($request);

        if(!$headerTokenValidStatus) {
            throw new \Exception("Request is not authorized.", StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        $rateLimitStorage = $this->factory->select('rate-limit-token');

        $currentRate = (int) $rateLimitStorage->get($token);
        if($currentRate > 1000) {
            throw new \Exception("Too many requests", StatusCodeInterface::STATUS_TOO_MANY_REQUESTS);
        }
        $rateLimitStorage->set($token, $currentRate + 1);

        return $handler->handle($validatedRequest);
    }

    private function headerHasToken(ServerRequestInterface $request): bool
    {
        return array_key_exists(0, $request->getHeader(static::TOKEN_HEADER));
    }

    private function headerTokenIsValid(ServerRequestInterface $request): array
    {
        $token = $request->getHeader(static::TOKEN_HEADER)[0];
        $user = $this->storage->get($token);

        if(!$user) {
            return [false, $request, $token];
        }

        return [true, $request->withAttribute("user", $user, null)];
    }
}
