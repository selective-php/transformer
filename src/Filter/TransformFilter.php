<?php

namespace Selective\Transformer\Filter;

use Selective\Transformer\ArrayTransformer;

/**
 * Filter.
 */
final class TransformFilter
{
    private ArrayTransformer $transformer;

    /**
     * The constructor.
     *
     * @param ArrayTransformer|null $transformer The parent transformer
     */
    public function __construct(ArrayTransformer $transformer = null)
    {
        $this->transformer = $transformer ?? new ArrayTransformer();
    }

    /**
     * Invoke.
     *
     * @param array<mixed> $value The values
     * @param callable $callback The callback
     *
     * @return array<mixed>|null The value
     */
    public function __invoke(array $value, callable $callback)
    {
        if (empty($value)) {
            // The item will be skipped if "required" is not set
            return null;
        }

        $callback($this->transformer);

        return $this->transformer->toArray($value);
    }
}
