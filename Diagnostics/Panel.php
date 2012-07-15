<?php

namespace Echo511\Dropbox\Diagnostics;

use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\IBarPanel;
use Nette\Object;
use Nette\Utils\Html;
use stdClass;

/**
 * This file is a part of Dropbox API handler for Nette Framework.
 *
 * @author     Nikolas Tsiongas
 * @package    Dropbox API handler
 * @license    New BSD License
 */
class Panel extends Object implements IBarPanel
{

    /**
     * array(
     *   'n' => array(
     *       'action' => 'add|remove|get|...',
     *       'args' => array(...) // What was passed to Dropbox API
     *       'response' => 'dumped response'
     *    )
     * );
     */
    private $requests = array();

    /**
     * Add request to panel
     * 
     * @param string $action
     * @param array $args
     * @param object|stdClass $response
     */
    public function addRequest($action, $args, $response)
    {
        $key = count($this->requests);
        $this->requests[$key]['action'] = $action;
        $this->requests[$key]['args'] = Debugger::dump($args, true);
        $this->requests[$key]['response'] = Debugger::dump($response, true);
    }

    /**
     * Renders tab
     * 
     * @return string
     */
    public function getTab()
    {
        return '<span title="Dropbox">'
              . '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAADP0lEQVR4nG2Te2iVdRjHv8/v8r7nfsPteNncYnqOZImYrHTFRJYkXTTGEqKodqaLKS4iMIqKIWqrCV1QSlg1UCjUjXAomHYhy6XhxNZFt9LMuc6O29l2cjvnPe/v/fXP0az2+esLz/d5Hnh4vkABYozhFhjjHLcU/5Gcg4jwLzPnAgAEgMrVdXXRufNiAMCFlFQYFKtcWRNfel/1dAsIAEoq4gsa3j1ydPfPWrd+dmnwzqqaVQDAAdzf8PJrL57QetvJnH70ue2t0jBdhVYiyRlbunZD05Jntu3wB8I+vz2an1vsl4wcveulxoSn4u57y1Y31btyaVXkISqdFWKDP31/tmPr5o0Xz/WcpNqtH3e5lq1by8dHnWIP6ahfcp+0nVkBycqiXuz/0UHf5bRTGuRspo8hKG27dIZfmH6Jts3161nvgbfb1MDpc95whClNbDLvoMhnsMUzDV1u5lXD7Uol7vIyr8GQsTRMl0soKdHVvufDnqOfdgIAAsFg8MEt77e3nJiyv740pa6M23pk0tbJv2x9PmXroYzSv4/lne4Lk6r1q+Gx6rr1z948JgBkr2cy1kRquH+M6M/rGh5JIAAagFMwugXB5BoErdLJPy4XTkg0+7ZYvHJD21uzlz/8wNmBEWc0B1a30IXEIhNRL8NoFugbzuNwfw5K2XrxHDeFAi7s2/lqS3d7Wys9tuPAIU9V7UNWcsROThEft0ABF6E0yPH4QhPHf83j2G9ZVIQ55kcIJT5Hlc/wczMs8camxiZ2eHv9E0OfvL4z4HeTIkHa0drgBJMDvwxb+O6KBY9kEAyw8kpzl49fvDp0tSXx5NNfHOz44OZLxpcsr4o933lkKGu454U1rYkZXBLQPaDgM4DURC6/qCwkM/09Pbuaax/JpK+lAIARETEh5fkz335j9R7cv2xBWNxRZHDm2CqnAMvWOiCUvSIekfFioU917Xkvk76WEtIwAYBprbVj2zbjQnz5TnNj3+5NzdnJiZQ7FOIcjgMmiPsjYvBC75lX1lWv+LxrXwdjnNt5K4dpIACIROeUbHyzY+9HP+R0ojM5vvKpF7bwQlr/m9r/wbgQN3TsnppV0fL58ekSeIO/AcMmQgevMxIqAAAAAElFTkSuQmCC" />'
              . 'Dropbox</span>';
    }
    
    /**
     * Renders panel
     * 
     * @return string
     */
    public function getPanel()
    {
        $h1 = '<h1>Dropbox</h1>';
        $table = Html::el('table');

        $tr = $table->create('thead')->create('tr');
        $tr->create('th')->setHtml('Action');
        $tr->create('th')->setHtml('Args');
        $tr->create('th')->setHtml('Response');

        $tbody = $table->create('tbody');
        foreach($this->requests as $request)
        {
            $tr = $tbody->create('tr');
            $tr->create('td')->setHtml($request['action']);
            $tr->create('td')->setHtml($request['args']);
            $tr->create('td')->setHtml($request['response']);
        }

        return $h1. (string) $table;
    }

}