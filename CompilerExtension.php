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
        'key' => false,
        'secret' => false,
        'defaultRoot' => 'dropbox',
        'panel' => true,
    );

    public function loadConfiguration()
    {
            $container = $this->getContainerBuilder()->addDependency(__FILE__);
            $config = $this->getConfig($this->defaults);

            // Rooftop - main object
            $rooftop = $container->addDefinition($this->prefix('rooftop'))
                    ->setClass('Echo511\Dropbox\Rooftop', array(
                        $config['key'],
                        $config['secret'],
                    ))
                    ->addSetup('setSession', '@Nette\Http\Session')
                    ->addSetup('setHttpRequest', '@Nette\Http\Request')
                    ->addSetup('setHttpResponse', '@Nette\Http\Response')
                    ->addSetup('setDefaultRoot', $config['defaultRoot']);

            // Panel renderer
            $panelRenderer = $container->addDefinition($this->prefix('panelRenderer'))
                    ->setClass('Echo511\Dropbox\PanelRenderer');


            if($config['panel']) {
                $rooftop->addSetup('setPanelRenderer', $panelRenderer);

                $panelRenderer->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array($panelRenderer));
            }
    }

    public static function register(Configurator $config)
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) {
            $compiler->addExtension('dropbox', new CompilerExtension());
        };
    }

}