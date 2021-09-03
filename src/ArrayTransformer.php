<?php

namespace Selective\Transformer;

use Selective\Transformer\Filter\ArrayFilter;
use Selective\Transformer\Filter\BooleanFilter;
use Selective\Transformer\Filter\CallbackFilter;
use Selective\Transformer\Filter\DateTimeFilter;
use Selective\Transformer\Filter\FloatFilter;
use Selective\Transformer\Filter\IntegerFilter;
use Selective\Transformer\Filter\NumberFormatFilter;
use Selective\Transformer\Filter\StringFilter;
use Selective\Transformer\Filter\StringWithBlankFilter;
use Selective\Transformer\Filter\TransformFilter;
use Selective\Transformer\Filter\TransformListFilter;
use Selective\Transformer\Interfaces\TransformerInterface;

/**
 * Transformer.
 */
final class ArrayTransformer implements TransformerInterface
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
        'bool' => BooleanFilter::class,
        'integer' => IntegerFilter::class,
        'int' => IntegerFilter::class,
        'float' => FloatFilter::class,
        'number' => NumberFormatFilter::class,
        'date' => DateTimeFilter::class,
        'array' => ArrayFilter::class,
        'callback' => CallbackFilter::class,
        'transform' => TransformFilter::class,
        'transform-list' => TransformListFilter::class,
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
     * Add mapping rule with default value only.
     *
     * @param string $destination The destination element
     * @param mixed $value The value
     *
     * @return $this The transformer
     */
    public function set(string $destination, $value): self
    {
        $this->rules[] = $this->rule()->destination($destination)->default($value);

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
     * @param array $source The source
     * @param array $target The target (optional)
     *
     * @return array The result
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
     * @param array $source The source
     * @param array $target The target (optional)
     *
     * @return array The result
     */
    public function toArray(array $source, array $target = []): array
    {
        $sourceData = new ArrayData($source);
        $targetData = new ArrayData($target);

        foreach ($this->rules as $rule) {
            $value = $sourceData->get($rule->getSource(), $rule->getDefault());
            $value = $this->converter->convert($value, $rule);

            if ($value === null && !$rule->isRequired()) {
                // Skip item
                continue;
            }

            $targetData->set($rule->getDestination(), $value);
        }

        return $targetData->all();
    }
}
