<?php

namespace Selective\Transformer;

use Dflydev\DotAccessData\Data;
use Selective\Transformer\Filter\ArrayFilter;
use Selective\Transformer\Filter\StringWithBlankFilter;
use Selective\Transformer\Filter\BooleanFilter;
use Selective\Transformer\Filter\CallbackFilter;
use Selective\Transformer\Filter\DateTimeFilter;
use Selective\Transformer\Filter\FloatFilter;
use Selective\Transformer\Filter\IntegerFilter;
use Selective\Transformer\Filter\NumberFormatFilter;
use Selective\Transformer\Filter\StringFilter;

/**
 * Transformer.
 */
final class ArrayTransformer
{
    /**
     * @var ArrayTransformerRule[]
     */
    private $rules;

    /**
     * @var ArrayValueConverter
     */
    private $converter;

    /**
     * @var string[]
     */
    private $internalFilters = [
        'string' => StringFilter::class,
        'string-with-blank' => StringWithBlankFilter::class,
        'boolean' => BooleanFilter::class,
        'integer' => IntegerFilter::class,
        'float' => FloatFilter::class,
        'number' => NumberFormatFilter::class,
        'date' => DateTimeFilter::class,
        'array' => ArrayFilter::class,
        'callback' => CallbackFilter::class,
    ];

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->converter = new ArrayValueConverter();

        foreach ($this->internalFilters as $name => $class) {
            $this->registerFilter($name, new $class());
        }
    }

    /**
     * Register custom filter.
     *
     * @param string $name The name
     * @param callable $filter The filter callback
     */
    public function registerFilter(string $name, callable $filter): void
    {
        $this->converter->registerFilter($name, $filter);
    }

    /**
     * Add mapping rule.
     *
     * @param string $destination The destination element
     * @param string $source The source element
     * @param ArrayTransformerRule|string|null $rule The rule
     *
     * @return $this The transformer
     */
    public function map(string $destination, string $source, $rule = null): self
    {
        if (is_string($rule)) {
            $rule = $this->ruleFromString($rule);
        }

        $rule = $rule ?? $this->rule();

        $this->rules[] = $rule->destination($destination)->source($source);

        return $this;
    }

    /**
     * Convert rule string to rule object.
     *
     * @param string $rules The rules, separated by '|'
     *
     * @return ArrayTransformerRule The rule object
     */
    private function ruleFromString(string $rules): ArrayTransformerRule
    {
        $rule = $this->rule();

        foreach (explode('|', $rules) as $name) {
            if ($name === 'required') {
                $rule->required();

                continue;
            }
            $rule->filter($name);
        }

        return $rule;
    }

    /**
     * Create transformer rule.
     *
     * @return ArrayTransformerRule The rule
     */
    public function rule(): ArrayTransformerRule
    {
        return new ArrayTransformerRule();
    }

    /**
     * Transform list of arrays to list of arrays.
     *
     * @param array<mixed> $source The source
     * @param array<mixed> $target The target (optional)
     *
     * @return array<mixed> The result
     */
    public function toArrays(array $source, array $target = []): array
    {
        foreach ($source as $item) {
            $target[] = $this->toArray($item);
        }

        return $target;
    }

    /**
     * Transform array to array.
     *
     * @param array<mixed> $source The source
     * @param array<mixed> $target The target (optional)
     *
     * @return array<mixed> The result
     */
    public function toArray(array $source, array $target = []): array
    {
        $sourceData = new Data($source);
        $targetData = new Data($target);

        foreach ($this->rules as $rule) {
            $value = $sourceData->get($rule->getSource(), $rule->getDefault());
            $value = $this->converter->convert($value, $rule);

            if ($value === null && !$rule->isRequired()) {
                // Skip item
                continue;
            }

            $targetData->set($rule->getDestination(), $value);
        }

        return $targetData->export();
    }
}
