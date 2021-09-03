<?php

namespace Selective\Transformer;

use Selective\Transformer\Exceptions\ArrayDataException;

/**
 * Array data dot access.
 */
final class ArrayData
{
    /**
     * Internal representation of data data.
     *
     * @var array
     */
    private $data;

    /**
     * The constructor.
     *
     * @param array $data The data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Set value into array.
     *
     * @param string $key The key
     * @param mixed $value The value
     *
     * @throws ArrayDataException
     *
     * @return void
     */
    public function set(string $key, $value = null): void
    {
        $currentValue = &$this->data;
        $keyPath = $this->keyToPathArray($key);

        $endKey = array_pop($keyPath);

        foreach ($keyPath as $currentKey) {
            if (!isset($currentValue[$currentKey])) {
                $currentValue[$currentKey] = [];
            }
            $currentValue = &$currentValue[$currentKey];
        }

        $currentValue[$endKey] = $value;
    }

    /**
     * Key path to array.
     *
     * @param string $path The path
     *
     * @return string[] The key paths
     */
    private function keyToPathArray(string $path): array
    {
        return explode('.', $path);
    }

    /**
     * Get value from array.
     *
     * @param string $key The key
     * @param null $default The default value
     *
     * @return mixed The value
     */
    public function get(string $key, $default = null)
    {
        $currentValue = $this->data;
        $keyPath = $this->keyToPathArray($key);

        foreach ($keyPath as $currentKey) {
            if (isset($currentValue->$currentKey)) {
                $currentValue = $currentValue->$currentKey;
                continue;
            }
            if (isset($currentValue[$currentKey])) {
                $currentValue = $currentValue[$currentKey];
                continue;
            }

            return $default;
        }

        return $currentValue === null ? $default : $currentValue;
    }

    /**
     * Get all values.
     *
     * @return array The values
     */
    public function all(): array
    {
        return $this->data;
    }
}
