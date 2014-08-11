<?php
/**
 * User: dongww
 * Date: 14-8-1
 * Time: 下午2:49
 */

namespace Dongww\SilexBase;

class TwigEnvironment extends \Twig_Environment
{
    public function loadTemplate($name, $index = null)
    {
        return parent::loadTemplate($this->parseViewPath($name), $index);
    }

    protected function parseViewPath($view)
    {
        if (stripos($view, ':')) {
            $viewPathParts = explode(':', $view);
            $view          = $viewPathParts[0] . '/_resources/views/' . $viewPathParts[1];
        }

        return $view;
    }
}
