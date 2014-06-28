<?php
/**
 * User: dongww
 * Date: 14-6-27
 * Time: 下午4:36
 */

namespace Dongww\SilexBase\Developer\Cleaner;

use Dongww\SilexBase\Core\Application;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Loader\YamlFileLoader;

class RoutesCleaner implements CleanerInterface
{
    protected $app;
    protected $cachePath;
    protected $metadata;
    protected $routesPath;
    protected $finder;
    protected $routesCache;

    public function __construct(Application $app, $cachePath, $routesPath)
    {
        $this->app         = $app;
        $this->cachePath   = $cachePath;
        $this->metadata    = $cachePath . '.meta';
        $this->routesPath  = $routesPath;
        $this->routesCache = new ConfigCache($cachePath, $app['debug']);

        $this->finder = new Finder();
        $this->finder->files()->in($this->routesPath);
    }

    /**
     * @return \Symfony\Component\Config\ConfigCache
     */
    public function getRoutesCache()
    {
        return $this->routesCache;
    }

    public function clean()
    {
        $locator = new FileLocator($this->app['config_path']);
        $loader  = new YamlFileLoader($locator);

        $resources = [];

        foreach ($this->finder as $file) {
            $resources[] = new FileResource($file->getRealpath());
            $this->app['routes']->addCollection($loader->load($file->getRealpath()));
        }

        $this->routesCache->write(\serialize($this->app['routes']), $resources);
    }

    /**
     * 未被缓存，返回true，否则返回false
     *
     * @return bool
     */
    public function noCache()
    {
        if (!is_file($this->metadata)) {
            return true;
        }

        return false;
    }

    /**
     * 配置文件数目有变化则返回true，否则返回false
     *
     * @return bool
     */
    public function countFilesChanged()
    {
        $meta = unserialize(file_get_contents($this->metadata));

        if (count($this->finder) != count($meta)) {
            return true;
        }

        return false;
    }
}
