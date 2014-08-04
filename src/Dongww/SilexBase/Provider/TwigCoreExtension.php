<?php
/**
 * User: dongww
 * Date: 14-4-4
 * Time: 下午1:22
 */

namespace Dongww\SilexBase\Provider;

/**
 * SilexBase 的核心 Twig 扩展
 *
 * Class TwigCoreExtension
 * @package Dongww\SilexBase\Provider
 */
class TwigCoreExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'asset' => new \Twig_Function_Method($this, 'asset', ['needs_environment' => true]),
        ];
    }

    /**
     * asset 方法，以解决网站处于子目录时的前台调用文件包含路径问题。
     *
     * @param \Twig_Environment $twig
     * @param $asset
     * @return string
     */
    public function asset(\Twig_Environment $twig, $asset)
    {
        $globals = $twig->getGlobals();
        $request = $globals['app']['request'];

        return sprintf('%s/%s', $request->getBasePath(), ltrim($asset, '/'));
    }

    public function getName()
    {
        return 'silexBase';
    }
}
