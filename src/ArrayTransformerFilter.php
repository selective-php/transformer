<?php

namespace Selective\Transformer;

final class ArrayTransformerFilter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array<mixed>
     */
    private $params;

    /**
     * The constructor.
     *
     * @param string $name
     * @param array<mixed> $params
     */
    public function __construct(string $name, array $params = [])
    {
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
