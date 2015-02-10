<?php
require_once(__DIR__ . '/twitteroauth/twitteroauth.php');

class TwitterHelper
{
    private $app_consumer_key;
    private $app_consumer_secret;
    private $token_request_url;
    private $twitter_oauth;
    private $token_request;

    public function setConsumerKeys($consumer_key, $consumer_secret)
    {
        $this->app_consumer_key = $consumer_key;
        $this->app_consumer_secret = $consumer_secret;
    }

    public function setTokenRequestUrl($url)
    {
        $this->token_request_url = $url;
    }

    public function InitializeTwitterOauth()
    {
        //Initialize OAuth instance
        $this->twitter_oauth = new TwitterOAuth($this->app_consumer_key, $this->app_consumer_secret);
        //Auth token request as well as redirect url
        $this->token_request = $this->twitter_oauth->getRequestToken($this->token_request_url);
        $_SESSION['request_token'] = $this->token_request['oauth_token'];
        $_SESSION['request_token_secret'] = $this->token_request['oauth_token_secret'];

        $this->VerifyHttpCode();
    }

    public function getTwitterOauth()
    {
        return $this->twitter_oauth;
    }

    public function getOauthTokenRequest()
    {
        return $this->token_request;
    }

    public function VerifyHttpCode()
    {
        switch ($this->twitter_oauth->http_code)
        {
            case 200:
                $url = $this->twitter_oauth->getAuthorizeURL($this->token_request['oauth_token']);
                header('Location: ' . $url);
                break;
            default:
                echo 'Failed connection to Twitter';
                break;
        }
    }

    public function getUserData()
    {
        $connection = new TwitterOAuth($this->app_consumer_key, $this->app_consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        if ($access_token) {
            $connection = new TwitterOAuth($this->app_consumer_key, $this->app_consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
            $params = array();
            $params['include_entities'] = 'false';
            $content = $connection->get('account/verify_credentials', $params);

            if ($content) {

                return $content;
            } else {
                throw new Exception ('Twitter Login error');
            }
        }
    }
}