<?php

namespace App\Http\Middleware;

use App\Attributes\PreAuthorize;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use ReflectionMethod;
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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        $controller = $request->route()->getController();
        $method = $request->route()->getActionMethod();


        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(PreAuthorize::class);

        $instance = $attributes[0]->newInstance();

        Log::info('permission : '. json_encode($instance->permission));


        try {
            $jwt = $this->config->parser()->parse($token);
            assert($jwt instanceof UnencryptedToken);

            $isValid = $this->config->validator()->validate(
                $jwt,
                new SignedWith($this->config->signer(), $this->config->verificationKey())
            );

            if (!$isValid) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            $claims = $jwt->claims();
            $tokenScopes = $claims->get('scope', []);
            if (!in_array($instance->permission, $tokenScopes)) {
                return response()->json([
                    'error' => 'Insufficient scope',
                    'required' => $instance->permission,
                    'available' => $tokenScopes
                ], 403);
            }


        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
