<?php

namespace Echo511\Dropbox;

interface ITokenStorage {

    public function setRooftop(Rooftop $rooftop);

    public function getTokenSecret();

    public function getOauthTokenSecret();

    public function getOauthToken();

}