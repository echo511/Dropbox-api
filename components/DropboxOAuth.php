<?php

namespace Echo511\Dropbox\Components;

class DropboxOAuth extends \Nette\Application\UI\Control
{

    public function injectDropboxApi(\Dropbox $dropboxApi)
    {
        $this->dropboxApi = $dropboxApi;
    }

}