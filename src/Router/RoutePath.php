<?php
declare(strict_types=1);

namespace Beauty\Core\Router;

final readonly class RoutePath
{
    /**
     * @var string
     */
    public string $pattern;
    /**
     * @var string
     */
    public string $regex;
    /**
     * @var array
     */
    public array $parameterNames;

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
        $this->regex = $this->compileRegex($pattern);
        $this->parameterNames = $this->extractParameterNames($pattern);
    }

    /**
     * @param string $path
     * @return array|null
     */
    public function match(string $path): ?array
    {
        if (!preg_match($this->regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($this->parameterNames as $name) {
            $params[$name] = $matches[$name] ?? null;
        }

        return $params;
    }

    /**
     * @param string $pattern
     * @return string
     */
    private function compileRegex(string $pattern): string
    {
        return '#^' . preg_replace(
                '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
                '(?P<$1>[^/]+)',
                $pattern
            ) . '$#';
    }

    /**
     * @param string $pattern
     * @return array
     */
    private function extractParameterNames(string $pattern): array
    {
        preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $pattern, $matches);
        return $matches[1] ?? [];
    }
}