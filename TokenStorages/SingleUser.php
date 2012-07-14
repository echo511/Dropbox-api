<?php

namespace Echo511\Dropbox\TokenStorages;

use Echo511\Dropbox;

class SingleUser extends \Nette\Object implements Dropbox\ITokenStorage
{

    public static $SESSION_SECTION = 'Echo511\Dropbox\TokenStorages\SingleUser';


    /*********** Dependencies ***********/
    private $session;

    public function setSession(\Nette\Http\Session $session)
    {
        $this->session = $session->getSection(self::$SESSION_SECTION);
        return $this;
    }

    public function setRooftop(Dropbox\Rooftop $rooftop)
    {
        $this->rooftop = $rooftop;
        $this->rooftop->onRequest[] = callback($this, 'saveTokenSecret');
        $this->rooftop->onAccess[] = callback($this, 'saveOauth');
        return $this;
    }


    /*********** Storing tokens ***********/
    public function saveTokenSecret($token)
    {
        $this->session->token_secret = $token;
    }

    public function saveOauth($oauth)
    {
        $this->session->oauth_token = $oauth['oauth_token'];
        $this->session->oauth_token_secret = $oauth['oauth_token_secret'];
    }

    private function restore()
    {
        $this->token_secret = $this->session->token_secret;
        $this->oauth_token = $this->session->oauth_token;
        $this->oauth_token_secret = $this->session->oauth_token_secret;
    }


    /*********** Getting tokens ***********/
    private $token_secret = false;
    private $oauth_token = false;
    private $oauth_token_secret = false;

    public function getTokenSecret()
    {
        if(!$this->token_secret)
            $this->restore();

        return $this->token_secret;
    }

    public function getOauthToken()
    {
        if(!$this->oauth_token)
            $this->restore();

        return $this->oauth_token;
    }

    public function getOauthTokenSecret()
    {
        if(!$this->oauth_token_secret)
            $this->restore();

        return $this->oauth_token_secret;
    }

}