<?php

namespace Echo511\Dropbox\DI;

use Nette\Config\Compiler;
use Nette\Config\CompilerExtension;
use Nette\Config\Configurator;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class DropboxExtension extends CompilerExtension
{

    /**
     * @var array
     */
    public $defaults = array(
        // Identity
        'key' => false,
        'secret' => false,

        // OAuthStorage
        'oauthStorage' => '@dropbox.singleUser',

        // Default dropbox root (dropbox=fullAccess|sandbox=appFolderAccess)
        'defaultRoot' => 'dropbox',

        // Show Bar Panel
        'panel' => true,
    );

    /**
     * Add definitions
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder()->addDependency(__FILE__);
        $config = $this->getConfig($this->defaults);


        // Rooftop - main object
        $builder->addDefinition($this->prefix('rooftop'))
                  ->setClass('Echo511\Dropbox\Rooftop', array(
                      $config['key'],
                      $config['secret'],
                  ))
                  ->addSetup('setSession', '@Nette\Http\Session')
                  ->addSetup('setHttpRequest', '@Nette\Http\Request')
                  ->addSetup('setHttpResponse', '@Nette\Http\Response')
                  ->addSetup('setDefaultRoot', $config['defaultRoot']);

        // Built in storages
        // SingleUserStorage
        $builder->addDefinition($this->prefix('singleUser'))
                  ->setClass('Echo511\Dropbox\OAuthStorages\SingleUser')
                  ->addSetup('setSession', '@Nette\Http\Session');

        // Panel renderer
        $builder->addDefinition($this->prefix('panel'))
                  ->setClass('Echo511\Dropbox\Diagnostics\Panel');
    }

    /**
     * Make relationships
     */
    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Wiring TokenStorage and Rooftop
            $service = $builder->getDefinition(str_replace('@', '', $config['oauthStorage']));
        $builder->getDefinition($this->prefix('rooftop'))
                  ->addSetup('setOAuthStorage', $service);

        // Wiring panel
        if($config['panel']) {
            $panel = $builder->getDefinition($this->prefix('panel'));

            $builder->getDefinition($this->prefix('rooftop'))
                      ->addSetup('setPanelRenderer', $panel);

            $builder->getDefinition($this->prefix('panel'))
                      ->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array($panel));
        }
    }


    /**
     * Register extension
     *
     * @param Configurator $config
     */
    public static function register(Configurator $config)
    {
        $self = new self;
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($self) {
            $compiler->addExtension('dropbox', $self);
        };
    }

}