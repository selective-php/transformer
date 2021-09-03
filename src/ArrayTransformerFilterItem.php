<?php

namespace Selective\Transformer;

/**
 * Filter.
 */
final class ArrayTransformerFilterItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments;

    /**
     * The constructor.
     *
     * @param string $name The filter to apply
     * @param array $arguments The parameters for the filter
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * The filter to apply.
     *
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get filter parameters.
     *
     * @return array The params
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
