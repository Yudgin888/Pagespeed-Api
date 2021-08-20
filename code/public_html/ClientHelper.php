<?php

class ClientHelper
{
    public static $API = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    private static $client = null;
    private static $home_uri = 'http://localhost';
    private static $redirect_uri = 'http://localhost/oauth2callback.php';
    private static $creds = 'client_secret.json';
    private static $scope = 'openid';

    private function __construct()
    {
    }

    public static function getClient(): Google\Client
    {
        if (self::$client === null) {
            session_start();
            //unset($_SESSION['access_token']);die;

            if (!isset($_SESSION['access_token'])) {
                header('Location: ' . filter_var(self::$redirect_uri, FILTER_SANITIZE_URL));
                die;
            }

            self::$client = new Google\Client();
            self::$client->setAuthConfig(self::$creds);
            self::$client->addScope(self::$scope);
            self::$client->setAccessToken($_SESSION['access_token']);
        }
        return self::$client;
    }

    public static function oauthCallback(): void
    {
        session_start();
        $client = new Google\Client();
        $client->setAuthConfig(self::$creds);
        $client->setRedirectUri(self::$redirect_uri);
        $client->addScope(self::$scope);

        if (!isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
            die;
        }

        $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $client->getAccessToken();
        header('Location: ' . filter_var(self::$home_uri, FILTER_SANITIZE_URL));
        die;
    }
}