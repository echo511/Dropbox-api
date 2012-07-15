<?php

namespace Echo511\Dropbox;

use Dropbox;
use Echo511\Dropbox\Diagnostics\Panel;
use Echo511\Dropbox\IOAuthStorage;
use Echo511\Dropbox\Rooftop;
use Exception;
use LogicException;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Http\Url;
use Nette\Object;
use stdClass;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class Rooftop extends Object
{

    public static $SESSION_SECTION = 'Echo511\Dropbox\Rooftop';

    /**
     * Configures API for your app
     *
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->api = new Dropbox(array(
            'key' => $key,
            'secret' => $secret,
        ));
    }


    /*********** Dependencies ***********/
    /**
     * @var IOAuthStorage
     */
    private $oauthStorage;

    /**
     * @var SessionSection
     */
    private $session;

    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var Response
     */
    private $httpResponse;


    /**
     *
     * @param IOAuthStorage $oauthStorage
     * @return Rooftop
     */
    public function setOAuthStorage(IOAuthStorage $oauthStorage)
    {
        $this->oauthStorage = $oauthStorage;
        $this->oauthStorage->setRooftop($this);
        return $this;
    }

    /**
     * @param Session $session
     * @return Rooftop
     */
    public function setSession(Session $session)
    {
        $this->session = $session->getSection(self::$SESSION_SECTION);
        return $this;
    }

    /**
     *
     * @param Request $request
     * @return Rooftop
     */
    public function setHttpRequest(Request $request)
    {
        $this->httpRequest = $request;
        return $this;
    }

    /**
     *
     * @param Response $response
     * @return Rooftop
     */
    public function setHttpResponse(Response $response)
    {
        $this->httpResponse = $response;
        return $this;
    }

    
    /*********** Getter ***********/
    /**
     * @return IOAuthStorage
     */
    public function getOAuthStorage()
    {
        return $this->oauthStorage;
    }


    /*********** Default root ***********/
    /**
     * Sets default root sandbox|dropbox
     *
     * @param string $root
     * @return Rooftop
     * @throws Exception
     */
    public function setDefaultRoot($root)
    {
        if(!in_array($root, array('dropbox', 'sandbox'))) {
            throw new Exception('Dropbox::DEFAULT_ROOT supports only \'dropbox\' or \'sandbox\' values.');
        }
        Dropbox::$defaultRoot = $root;
        return $this;
    }

    
    /*********** Authentication handlers ***********/
    public function getOAuthAccess()
    {
        if(!$this->session->token_secret) {
            $this->request();
        } else {
            $this->access();
        }
    }

    /**
     * When app's token is not present (asking user for authorization)
     */
    public function request()
    {
        $data = $this->api->get_request_token( (string) $this->httpRequest->getUrl() );

        $this->session->token_secret = $data['token_secret'];

        $this->httpResponse->redirect($data['redirect']); die;
    }

    /**
     * After user has authorized your app
     */
    public function access()
    {
        if(!array_key_exists('oauth_token', $this->httpRequest->getQuery())) {
            $this->request();
        }

        $oauth = $this->api->get_access_token($this->session->token_secret);

        $this->oauthStorage->storeOAuthAccess($oauth['oauth_token'], $oauth['oauth_token_secret']);

        // Removing uid and oauth_token from url
        $url = $this->httpRequest->getUrl();

        $query = $this->httpRequest->getQuery();
        unset($query['uid']);
        unset($query['oauth_token']);

        $url = new Url((string) $url);
        $url->setQuery($query);

        $this->httpResponse->redirect((string) $url); die;
    }    
    

    /*********** API ***********/
    /**
     * @var Dropbox
     */
    private $api;

    // Is user authenticated?
    /**
     * @var boolean
     */
    private $ready = false;

    /**
     * Is API authorized?
     *
     * @return boolean
     */
    private function isReady()
    {
        return $this->ready;
    }

    /**
     * Gets API
     *
     * @return Dropbox
     */
    public function getApi()
    {
        if($this->isReady()) {
            return $this->api;
        }

        throw new LogicException('Dropbox OAuthAccess not setted.');
    }

    /**
     * Authenticate user
     * 
     * @param string $token
     * @param string $token_secret
     * @return Rooftop
     */
    public function setOAuthAccess($token, $token_secret)
    {
        $this->api->set_oauth_access(array(
            'oauth_token' => $token,
            'oauth_token_secret' => $token_secret,
        ));

        $this->ready = true;
        return $this;
    }


    /*********** Call API's function ***********/
    /**
     * Calls API's function and dumps response into panel
     *
     * @return object|stdClass
     */
    public function call()
    {
        $args = func_get_args();

        if($args[0] == 'synchronise') {
            $return = call_user_func_array(
                array($this, 'synchronise'),
                array_diff($args, array($args[0]))
            );

        } else {
            $return = call_user_func_array(
                array($this->getApi(), $args[0]),
                array_diff($args, array($args[0]))
            );
        }

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
    /**
     * @var Panel
     */
    private $panel;

    public function setPanelRenderer(Panel $panel)
    {
        $this->panel = $panel;
        return $this;
    }

    /**
     * Is panel wired?
     *
     * @return boolean
     */
    public function hasPanel()
    {
        if(is_object($this->panel))
            return true;

        return false;
    }


    /*********** Synchronise ***********/
    /**
     * Synchronises entire Dropbox folder with local storage based on delta entries
     *
     * @param string $destination
     * @param string $cursor
     */
    public function synchronise($destination, $cursor)
    {
        $delta = $this->call('delta', $cursor);

        foreach($delta->entries as $entry) {
            $localPath = $destination.$entry[0];
            $dropboxPath = $entry[0];

            $change = $entry[1];

            // New/Changed
            if(is_object($change)) {

                // New directory
                if($change->is_dir == 1) {
                    if(!is_dir($localPath))
                        mkdir($localPath);
                }

                // New or updated file
                else {
                    if(file_exists($localPath))
                        unlink($localPath);

                    $this->call('get', $localPath, $dropboxPath);
                }

            }

            // Removed
            else {
                if(is_dir($localPath)) {
                    rmdir($localPath);
                }

                if(file_exists($localPath)) {
                    unlink($localPath);
                }
            }
        }
    }

}