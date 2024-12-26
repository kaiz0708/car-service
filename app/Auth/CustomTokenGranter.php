<?php
namespace App\Auth;

use League\OAuth2\Server\Grant\AbstractGrant;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Facades\Log;

class CustomTokenGranter extends AbstractGrant
{
    protected $guard;
    protected $grantType;

    public function __construct($guard, $grantType)
    {
        $this->guard = $guard;
        $this->grantType = $grantType;
    }

    public function getIdentifier(): string {
        return 'custom_grant';
    }

    public function canGrant(ServerRequestInterface $request)
    {
        return $request->getParsedBody()['grant_type'] === $this->grantType;
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, $responseType, \DateInterval $accessTokenTTL)
    {
        $username = $request->getParsedBody()['username'];
        $password = $request->getParsedBody()['password'];

        // Authenticate user
        $user = $this->guard->attempt([
            'email' => $username,
            'password' => $password
        ]);

        Log::info('User đã đăng nhập', $username);

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        // Issue the access token
        $accessToken = $this->issueAccessToken($accessTokenTTL, $user->getAuthIdentifier(), null);

        // Enhance the token (Return JWT string)
        $enhancer = new CustomTokenEnhancer();
        $jwt = $enhancer->enhance($accessToken);

        // Trả về phản hồi JSON chứa chuỗi JWT
        return $responseType->setAccessToken($jwt);
    }
}