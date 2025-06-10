<?php
declare(strict_types=1);

namespace Beauty\Core\Config;

class ConfigRepository
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key]
            ?? $this->getNested($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $ref = &$this->items;

        foreach ($keys as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }

        $ref = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getNested(string $key, mixed $default): mixed
    {
        $segments = explode('.', $key);
        $ref = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($ref) || !array_key_exists($segment, $ref)) {
                return $default;
            }
            $ref = $ref[$segment];
        }

        return $ref;
    }
}