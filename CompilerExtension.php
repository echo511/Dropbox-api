<?php

namespace Echo511\Dropbox;

use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\Diagnostics\Debugger;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class CompilerExtension extends \Nette\Config\CompilerExtension
{

    public $defaults = array(
        // Identity
        'key' => false,
        'secret' => false,

        // TokenStorage
        'tokenStorage' => 'singleUsers',

        // Default dropbox root (dropbox=fullAccess|sandbox=appFolderAccess)
        'defaultRoot' => 'dropbox',

        // Show Bar Panel
        'panel' => true,
    );

    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder()->addDependency(__FILE__);
        $config = $this->getConfig($this->defaults);


        // Rooftop - main object
        $container->addDefinition($this->prefix('rooftop'))
                  ->setClass('Echo511\Dropbox\Rooftop', array(
                      $config['key'],
                      $config['secret'],
                  ))
                  ->addSetup('setHttpRequest', '@Nette\Http\Request')
                  ->addSetup('setHttpResponse', '@Nette\Http\Response')
                  ->addSetup('setDefaultRoot', $config['defaultRoot']);

        // Built in storages
        // SingleUserStorage
        $container->addDefinition($this->prefix('singleUser'))
                  ->setClass('Echo511\Dropbox\TokenStorages\SingleUser')
                  ->addSetup('setSession', '@Nette\Http\Session');

        // Panel renderer
        $container->addDefinition($this->prefix('panelRenderer'))
                  ->setClass('Echo511\Dropbox\PanelRenderer');
    }

    public function beforeCompile()
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Wiring TokenStorage and Rooftop
        if($config['tokenStorage'] == 'singleUser') {
            $container->getDefinition($this->prefix('rooftop'))
                      ->addSetup('setTokenStorage', $container->getDefinition($this->prefix('singleUser')));

        } else {
            foreach($container->findByTag($config['tokenStorage']) as $service => $tag) {
                $container->getDefinition($this->prefix('rooftop'))
                          ->addSetup('setTokenStorage', $container->getDefinition($service));
                break;
            }
        }

        // Wiring panel
        if($config['panel']) {
            $panelRenderer = $container->getDefinition($this->prefix('panelRenderer'));

            $container->getDefinition($this->prefix('rooftop'))
                      ->addSetup('setPanelRenderer', $panelRenderer);

            $container->getDefinition($this->prefix('panelRenderer'))
                      ->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array($panelRenderer));
        }
    }

    public static function register(Configurator $config)
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) {
            $compiler->addExtension('dropbox', new CompilerExtension());
        };
    }

}