<?php
declare(strict_types=1);

namespace Beauty\Core\Config;

class ConfigLoader
{
    /**
     * @param string $configPath
     */
    public function __construct(
        protected string $configPath,
    )
    {
    }

    /**
     * @return array
     */
    public function load(): array
    {
        $configs = [];

        foreach (glob($this->configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $configs[$key] = require $file;
        }

        return $configs;
    }

    /**
     * @param string $cacheFile
     * @return void
     */
    public function cache(string $cacheFile): void
    {
        $content = '<?php return ' . var_export($this->load(), true) . ';';
        file_put_contents($cacheFile, $content);
    }

    /**
     * @param string $cacheFile
     * @return array
     */
    public function loadFromCache(string $cacheFile): array
    {
        return file_exists($cacheFile) ? require $cacheFile : [];
    }
}