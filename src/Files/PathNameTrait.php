<?php
declare(strict_types=1);

namespace Beauty\Core\Files;

trait PathNameTrait
{
    /**
     * @param string $pattern
     * @return string
     */
    protected function extractDir(string $pattern): string
    {
        $clean = str_replace('\\', '/', $pattern);
        $parts = explode('/', $clean);

        $path = [];

        foreach ($parts as $part) {
            if (str_contains($part, '*')) {
                break;
            }
            $path[] = $part;
        }

        $dir = implode('/', $path);

        if (!is_dir($dir)) {
            throw new \RuntimeException("Directory does not exist: $dir (from pattern: $pattern)");
        }

        return $dir;
    }

    /**
     * @param string $pattern
     * @return string
     */
    protected function extractMask(string $pattern): string
    {
        return basename($pattern);
    }
}