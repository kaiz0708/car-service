<?php

namespace App\Http\Middleware;

use App\Attributes\PreAuthorize;
use App\Constants\Constants;
use App\DTO\ApiMessageDto;
use App\Exceptions\CustomAuthenticationException;
use App\Response\CustomResponseMessage;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use ReflectionMethod;
use Spatie\FlareClient\Api;
use Symfony\Component\HttpFoundation\Response;
use Lcobucci\JWT\Configuration;

class PreAuthorizeMiddleware
{

    protected Configuration $config;

    public function __construct()
    {
        $publicKey = InMemory::file(storage_path('oauth-public.key'));
        $privateKey = InMemory::file(storage_path('oauth-private.key'));

        $this->config = Configuration::forAsymmetricSigner(
            new \Lcobucci\JWT\Signer\Rsa\Sha256(),
            $privateKey,
            $publicKey
        );

    }

    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     * @throws \ReflectionException
     * @throws CustomAuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        Log::info('token : ' . $token);

        if (!$token) {
            throw new CustomAuthenticationException('Unauthenticated.', Constants::UNAUTHORIZED);
        }

        $controller = $request->route()->getController();
        $method = $request->route()->getActionMethod();


        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(PreAuthorize::class);

        $instance = $attributes[0]->newInstance();

        try {
            $jwt = $this->config->parser()->parse($token);
            assert($jwt instanceof UnencryptedToken);

            $isValid = $this->config->validator()->validate(
                $jwt,
                new SignedWith($this->config->signer(), $this->config->verificationKey())
            );

            if (!$isValid) {
                throw new CustomAuthenticationException('Unauthenticated.', Constants::UNAUTHORIZED);
            }

            $claims = $jwt->claims();
            $tokenScopes = $claims->get('permissions', []);
            if (!in_array($instance->permission, $tokenScopes)) {
                throw new CustomAuthenticationException('Forbidden.', 403);
            }
        } catch (\Exception $e) {
            throw new CustomAuthenticationException($e->getMessage(), $e->getCode());
        }

        return $next($request);
    }
}
