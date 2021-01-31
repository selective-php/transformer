<?php

namespace Selective\Transformer;

use DateTimeZone;

/**
 * Rule.
 */
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
     * @var ArrayTransformerFilterItem[]
     */
    private $filters = [];

    /**
     * Add destination.
     *
     * @param string $destination The destination element name
     *
     * @return $this The rule
     */
    public function destination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Set source name.
     *
     * @param string $source The element name
     *
     * @return $this Self
     */
    public function source(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string The source name
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Get destination.
     *
     * @return string The destination name
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Set required.
     *
     * @return $this Self
     */
    public function required(): self
    {
        $this->required = true;

        return $this;
    }

    /**
     * Get required status.
     *
     * @return bool The status
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set default value.
     *
     * @param mixed $default The value
     *
     * @return $this Self
     */
    public function default($default = null): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default value.
     *
     * @return mixed|null The value
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get filters.
     *
     * @return ArrayTransformerFilterItem[] The filters
     */
    public function getFiltersItems(): array
    {
        return $this->filters;
    }

    /**
     * Add string filter.
     *
     * @param bool $allowBlank Convert blank to null by default. True allows the string to be blank.
     *
     * @return $this Self
     */
    public function string(bool $allowBlank = false): self
    {
        $this->filter($allowBlank ? 'string-with-blank' : 'string');

        return $this;
    }

    /**
     * Add filter.
     *
     * @param string $name The filter name
     * @param mixed ...$parameters The filter arguments
     *
     * @return $this Self
     */
    public function filter(string $name, ...$parameters): self
    {
        $this->filters[] = new ArrayTransformerFilterItem($name, $parameters);

        return $this;
    }

    /**
     * Add boolean filter.
     *
     * @return $this Self
     */
    public function boolean(): self
    {
        return $this->filter('boolean');
    }

    /**
     * Add float filter.
     *
     * @return $this Self
     */
    public function float(): self
    {
        return $this->filter('float');
    }

    /**
     * Add integer filter.
     *
     * @return $this Self
     */
    public function integer(): self
    {
        return $this->filter('integer');
    }

    /**
     * Add number format filter.
     *
     * @param int $decimals The number of decimals
     * @param string $decimalSeparator The  decimal separator
     * @param string $thousandsSeparator The Thousands separator
     *
     * @return $this Self
     */
    public function number(int $decimals = 0, string $decimalSeparator = '.', string $thousandsSeparator = ','): self
    {
        return $this->filter('number', $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Add date filter.
     *
     * @param string $format The date format
     * @param DateTimeZone|null $dateTimeZone The time zone
     *
     * @return $this Self
     */
    public function date(string $format = 'Y-m-d H:i:s', DateTimeZone $dateTimeZone = null): self
    {
        return $this->filter('date', $format, $dateTimeZone);
    }

    /**
     * Add array filter.
     *
     * @return $this Self
     */
    public function array(): self
    {
        return $this->filter('array');
    }

    /**
     * Add callback filter.
     *
     * @param callable $callback The callback
     *
     * @return $this Self
     */
    public function callback(callable $callback): self
    {
        return $this->filter('callback', $callback);
    }

    /**
     * Add transformer filter.
     *
     * @param callable $callback The callback
     *
     * @return $this Self
     */
    public function transform(callable $callback): self
    {
        return $this->filter('transform', $callback, new ArrayTransformer());
    }

    /**
     * Add transformer list filter.
     *
     * @param callable $callback The callback
     *
     * @return $this Self
     */
    public function transformList(callable $callback): self
    {
        return $this->filter('transform-list', $callback, new ArrayTransformer());
    }
}
