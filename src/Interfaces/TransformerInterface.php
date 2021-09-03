<?php

namespace Selective\Transformer\Interfaces;

/**
 * Transformer Interface.
 */
interface TransformerInterface
{
    /**
     * Transform array to array.
     *
     * @param array $source The source
     * @param array $target The target (optional)
     *
     * @return array The result
     */
    public function toArray(array $source, array $target = []): array;
}
