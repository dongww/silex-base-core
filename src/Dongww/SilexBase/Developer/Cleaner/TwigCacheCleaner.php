<?php
/**
 * User: dongww
 * Date: 14-6-28
 * Time: 上午11:14
 */

namespace Dongww\SilexBase\Developer\Cleaner;

use Symfony\Component\Filesystem\Filesystem;

/**
 * 清除 Twig 的缓存文件。
 *
 * Class TwigCacheCleaner
 * @package Dongww\SilexBase\Developer\Cleaner
 */
class TwigCacheCleaner implements CleanerInterface
{
    protected $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function clean()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }
}
