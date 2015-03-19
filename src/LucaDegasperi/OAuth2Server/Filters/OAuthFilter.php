<?php namespace LucaDegasperi\OAuth2Server\Filters;

use ResourceServer;
use Response;
use Config;

class OAuthFilter
{

    /**
     * Run the oauth filter
     *
     * @param Route $route the route being called
     * @param Request $request the request object
     * @param string $scope additional filter arguments
     * @return Response|null a bad response in case the request is invalid
     */
    public function filter($route, $request, $scope = null)
    {
        $custom_exception = Config::get('lucadegasperi/oauth2-server-laravel::oauth2.custom_exception');
        try {
            ResourceServer::isValid(Config::get('lucadegasperi/oauth2-server-laravel::oauth2.http_headers_only'));
        } catch (\League\OAuth2\Server\Exception\InvalidAccessTokenException $e) {

            if($custom_exception)
            {
                throw new $custom_exception( $e->getMessage(), 2 );
            }

            return Response::json(array(
                'status' => 403,
                'error' => 'forbidden',
                'error_message' => $e->getMessage(),
            ), 403);
        }

        if (! is_null($scope)) {
            $scopes = explode(',', $scope);

            foreach ($scopes as $s) {
                if (! ResourceServer::hasScope($s)) {

                    if($custom_exception)
                    {
                        throw new $custom_exception('Only access token with scope '.$s.' can use this endpoint', 2);
                    }

                    return Response::json(array(
                        'status' => 403,
                        'error' => 'forbidden',
                        'error_message' => 'Only access token with scope '.$s.' can use this endpoint',
                    ), 403);
                }
            }
        }
    }
}
