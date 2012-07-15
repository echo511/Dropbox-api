<?php

namespace Echo511\Dropbox;

use Echo511\Dropbox\Rooftop;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
interface IOAuthStorage {

    /**
     * Wiring Rooftop
     *
     * @param Rooftop $rooftop
     */
    public function setRooftop(Rooftop $rooftop);

    /**
     * Store Dropbox OAuthAccess for further use
     *
     * @param string $token
     * @param string $token_secret
     */
    public function storeOAuthAccess($token, $token_secret);

}
