<?php

namespace Echo511\Dropbox\OAuthStorages;

use Echo511\Dropbox\IOAuthStorage;
use Echo511\Dropbox\Rooftop;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Object;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class SingleUser extends Object implements IOAuthStorage
{

    public static $SESSION_SECTION = 'Echo511\Dropbox\OAuthStorages\SingleUser';


    /*********** Dependencies ***********/
    /**
     * @var Rooftop
     */
    private $rooftop;

    /**
     * @var SessionSection
     */
    private $session;

    /**
     * @param Rooftop $rooftop
     */
    public function setRooftop(Rooftop $rooftop) {
        $this->rooftop = $rooftop;
    }

    /**
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session->getSection(self::$SESSION_SECTION);
        return $this;
    }


    /*********** Storing OAuth ***********/
    /**
     * Store OAuth tokens into session
     *
     * @param string $token
     * @param string $token_secret
     */
    public function storeOAuthAccess($token, $token_secret)
    {
        $this->session->token = $token;
        $this->session->token_secret = $token_secret;
    }


    /*********** Authorizes user | Provides his tokens  ***********/
    /**
     * Calls $rooftop->setOAuthAccess();
     *
     * @return Rooftop
     */
    public function authorize()
    {
        if(!$this->session->token || !$this->session->token_secret) {
            $this->rooftop->getOAuthAccess();
        }

        return $this->rooftop->setOAuthAccess($this->session->token, $this->session->token_secret);
    }

}