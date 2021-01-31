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
     * @param array<mixed> $source The source
     * @param array<mixed> $target The target (optional)
     *
     * @return array<mixed> The result
     */
    public function toArray(array $source, array $target = []): array;
}
