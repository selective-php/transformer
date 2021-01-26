<?php

namespace Selective\Transformer;

use Dflydev\DotAccessData\Data;
use Selective\Transformer\Filter\ArrayFilter;
use Selective\Transformer\Filter\BlankToNullFilter;
use Selective\Transformer\Filter\BooleanFilter;
use Selective\Transformer\Filter\CallbackFilter;
use Selective\Transformer\Filter\DateTimeFilter;
use Selective\Transformer\Filter\FloatFilter;
use Selective\Transformer\Filter\IntegerFilter;
use Selective\Transformer\Filter\NumberFormatFilter;
use Selective\Transformer\Filter\StringFilter;

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

    public function __construct()
    {
        $this->converter = new ArrayValueConverter();
        $this->registerFilter('string', StringFilter::class);
        $this->registerFilter('blank-to-null', BlankToNullFilter::class);
        $this->registerFilter('boolean', BooleanFilter::class);
        $this->registerFilter('integer', IntegerFilter::class);
        $this->registerFilter('float', FloatFilter::class);
        $this->registerFilter('number', NumberFormatFilter::class);
        $this->registerFilter('date', DateTimeFilter::class);
        $this->registerFilter('array', ArrayFilter::class);
        $this->registerFilter('callback', CallbackFilter::class);
    }

    /**
     * @param string $string
     * @param callable|string $filter
     */
    public function registerFilter(string $string, $filter): void
    {
        $this->converter->registerFilter($string, $filter);
    }

    /**
     * @param string $destination
     * @param string $source
     * @param ArrayTransformerRule|string|null $rule
     *
     * @return $this
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

    public function rule(): ArrayTransformerRule
    {
        return new ArrayTransformerRule();
    }

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
     * @param array<mixed> $source
     * @param array<mixed> $target
     *
     * @return array<mixed>
     */
    public function toArray(array $source, array $target = []): array
    {
        $sourceData = new Data($source);
        $targetData = new Data($target);

        foreach ($this->rules as $rule) {
            $value = $sourceData->get($rule->getSource(), $rule->getDefault());
            $value = $this->converter->convert($value, $rule);

            if ($value === null && !$rule->isRequired()) {
                // Don't add item to result
                continue;
            }

            $targetData->set($rule->getDestination(), $value);
        }

        return $targetData->export();
    }
}
