<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\HttpFoundation\Response;
use Lcobucci\JWT\Configuration;

class CustomCheckScope
{

    protected Configuration $config;
    private string $requiredPermission;

    public function __construct($requiredPermission = null)
    {
        $publicKey = InMemory::file(storage_path('oauth-public.key'));
        $privateKey = InMemory::file(storage_path('oauth-private.key'));

        $this->config = Configuration::forAsymmetricSigner(
            new \Lcobucci\JWT\Signer\Rsa\Sha256(),
            $privateKey,
            $publicKey
        );
        $this->requiredPermission = $requiredPermission;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token missing'], 401);
        }

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
            $tokenScopes = $claims->get('scope', []);  // Get scopes from JWT token


            if (!in_array($this->requiredPermission, $tokenScopes)) {
                return response()->json([
                    'error' => 'Insufficient scope',
                    'required' => $this->requiredPermission,
                    'available' => $tokenScopes
                ], 403);
            }


        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
