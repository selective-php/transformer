<?php

namespace Selective\Transformer;

use DateTimeZone;

final class ArrayTransformerRule
{
    /**
     * @var string
     */
    private $destination = '';

    /**
     * @var string
     */
    private $source = '';

    /**
     * @var mixed
     */
    private $default = null;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var ArrayTransformerFilter[]
     */
    private $filters = [];

    public function destination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function source(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function required(): self
    {
        $this->required = true;

        return $this;
    }

    /**
     * @param mixed $default
     *
     * @return $this
     */
    public function default($default = null): self
    {
        $this->default = $default;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return mixed|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $name
     * @param mixed ...$parameters
     *
     * @return $this
     */
    public function filter(string $name, ...$parameters): self
    {
        $this->filters[] = new ArrayTransformerFilter($name, $parameters);

        return $this;
    }

    /**
     * @return ArrayTransformerFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function string(bool $blankToNull = true): self
    {
        $this->filter($blankToNull ? 'blank-to-null' : 'string');

        return $this;
    }

    public function boolean(): self
    {
        return $this->filter('boolean');
    }

    public function float(): self
    {
        return $this->filter('float');
    }

    public function integer(): self
    {
        return $this->filter('integer');
    }

    public function number(string $format): self
    {
        return $this->filter('number', $format);
    }

    public function date(string $format = 'Y-m-d H:i:s', DateTimeZone $dateTimeZone = null): self
    {
        return $this->filter('date', $format, $dateTimeZone);
    }

    public function array(): self
    {
        return $this->filter('array');
    }

    public function callback(callable $callback): self
    {
        return $this->filter('callback', $callback);
    }
}
