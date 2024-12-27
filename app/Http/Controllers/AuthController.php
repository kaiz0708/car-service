<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
use App\Auth\CustomTokenEnhancer;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $server;

    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    public function getToken(Request $request)
    {
        try {
            // Convert Laravel request to PSR-7 request
            // $psr7Request = new ServerRequest(
            //     'POST',
            //     '/auth/token',
            //     [],
            //     null,
            //     '1.1',
            //     array_merge($request->headers->all(), [
            //         'Content-Type' => 'application/json',
            //     ])
            // );

            // // Set request body with grant parameters
            // $psr7Request = $psr7Request->withParsedBody([
            //     'grant_type' => 'custom', // hoặc 'anonymous' tùy use case
            //     'username' => $request->email,
            //     'password' => $request->password,
            //     'client_id' => config('passport.client_id'),
            //     'client_secret' => config('passport.client_secret'),
            //     'scope' => ''
            // ]);

            // // Create empty PSR-7 response
            // $psr7Response = new Response();

            // // Get token response
            // $response = $this->server->respondToAccessTokenRequest(
            //     $psr7Request,
            //     $psr7Response
            // );

            // // Parse response body
            // $responseBody = json_decode((string) $response->getBody(), true);

            // // Enhance token with CustomTokenEnhancer
            // $enhancer = new CustomTokenEnhancer();
            // $enhancedToken = $enhancer->enhance($responseBody['access_token']);

            // return response()->json([
            //     'status' => 'success',
            //     'token' => (string)$enhancedToken,
            //     'expires_in' => $responseBody['expires_in'] ?? 3600
            // ]);

            return response()->json([
                'message' => 'Received data successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Token generation error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}