<?php

namespace Echo511\Dropbox;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class Rooftop extends \Nette\Object
{

    const SESSION_SECTION = 'Echo511\Dropbox';

    public function __construct($key, $secret)
    {
        $this->api = new \Dropbox(array(
            'key' => $key,
            'secret' => $secret,
        ));
    }


    /*********** Dependencies ***********/
    private $session;
    private $httpRequest;
    private $httpResponse;

    public function setSession(\Nette\Http\Session $session)
    {
        $this->session = $session->getSection(self::SESSION_SECTION);
        return $this;
    }

    public function setHttpRequest(\Nette\Http\Request $request)
    {
        $this->httpRequest = $request;
        return $this;
    }

    public function setHttpResponse(\Nette\Http\Response $response)
    {
        $this->httpResponse = $response;
        return $this;
    }


    /*********** Default root ***********/
    public function setDefaultRoot($root)
    {
        if(!in_array($root, array('dropbox', 'sandbox'))) {
            throw new \Exception('Dropbox::DEFAULT_ROOT supports only \'dropbox\' or \'sandbox\' values.');
        }
        \Dropbox::$defaultRoot = $root;
        return $this;
    }


    /*********** API ***********/
    private $api;

    // Is user authenticated?
    private $ready = false;

    private function isReady()
    {
        return $this->ready;
    }

    // Authenticate user
    public function getApi()
    {
        if(!$this->isReady()) {
            if(!$this->session->token_secret) {
                $this->request();
            }

            if(!$this->session->oauth_token_secret || !$this->session->oauth_token) {
                $this->access();
            }

            $this->api->set_oauth_access(array(
                'oauth_token' => $this->session->oauth_token,
                'oauth_token_secret' => $this->session->oauth_token_secret,
            ));

            $this->ready = true;
        }

        return $this->api;
    }


    /*********** Authentication handlers ***********/
    // When app's token is not present
    public function request()
    {
        $data = $this->api->get_request_token( (string) $this->httpRequest->getUrl() );

        $this->session->token_secret = $data['token_secret'];

        $this->httpResponse->redirect($data['redirect']);
        die;
    }

    // After user has been authenticated
    public function access()
    {
        if(!array_key_exists('oauth_token', $this->httpRequest->getQuery())) {
            $this->request();
        }

        $oauth = $this->api->get_access_token($this->session->token_secret);

        $this->session->oauth_token_secret = $oauth['oauth_token_secret'];
        $this->session->oauth_token = $oauth['oauth_token'];

        // Removing uid and oauth_token from url
        $url = $this->httpRequest->getUrl();

        $query = $this->httpRequest->getQuery();
        unset($query['uid']);
        unset($query['oauth_token']);

        $url = new \Nette\Http\Url((string) $url);
        $url->setQuery($query);

        $this->httpResponse->redirect((string) $url);
        die;
    }


    /*********** Call API's function ***********/
    public function call()
    {
        $args = func_get_args();

        $return = call_user_func_array(
            array($this->getApi(), $args[0]),
            array_diff($args, array($args[0]))
        );

        $test = (array) $return;

        if(array_key_exists('error', $test)) {
            if($test['error'] == 'Access token is disabled.' || $test['error'] == 'Unauthorized') {
                $this->session->remove();
                $this->request();
            }
        }

        if($this->hasPanel())
            $this->panel->addRequest($args[0], $args, $return);

        return $return;
    }


    /*********** Nette's panel ***********/
    private $panel;

    public function setPanelRenderer($panel)
    {
        $this->panel = $panel;
        return $this;
    }

    public function hasPanel()
    {
        if(is_object($this->panel))
            return true;

        return false;
    }

}