<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomAuthenticationException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class CustomAuthenticate
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
     * @throws CustomAuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token || !$this->validateToken($token)) {
            throw new CustomAuthenticationException('Unauthenticated.', 401);
        }
        return $next($request);
    }

    protected function validateToken($token): bool
    {
        try {
            $jwt = $this->config->parser()->parse($token);
            assert($jwt instanceof UnencryptedToken);

            return $this->config->validator()->validate(
                $jwt,
                new SignedWith($this->config->signer(), $this->config->verificationKey())
            );
        } catch (\Exception $e) {
            return false;
        }
    }
}
