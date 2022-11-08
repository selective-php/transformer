<?php

namespace Selective\Transformer\Test;

use PHPUnit\Framework\TestCase;
use Selective\Transformer\ArrayTransformer;

/**
 * Test.
 */
class ArrayTransformerTransformListTest extends TestCase
{
    /**
     * Test.
     */
    public function testToArrays(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map('id', 'id', $transformer->rule()->integer())
            ->map('first_name', 'first_name', $transformer->rule()->string())
            ->map('last_name', 'last_name', $transformer->rule()->string())
            ->map('phone', 'phone', $transformer->rule()->string())
            ->map('enabled', 'enabled', $transformer->rule()->boolean());

        $rows = [];
        $rows[] = [
            'id' => '100',
            'first_name' => 'Sally',
            'last_name' => '',
            'phone' => null,
            'enabled' => '1',
        ];

        $rows[] = [
            'id' => '101',
            'first_name' => 'Max',
            'last_name' => 'Doe',
            'phone' => '+123456789',
            'enabled' => '0',
        ];

        $actual = $transformer->toArrays($rows);

        $this->assertSame(
            [
                [
                    'id' => 100,
                    'first_name' => 'Sally',
                    'enabled' => true,
                ],
                [
                    'id' => 101,
                    'first_name' => 'Max',
                    'last_name' => 'Doe',
                    'phone' => '+123456789',
                    'enabled' => false,
                ],
            ],
            $actual
        );
    }

    /**
     * Test.
     */
    public function testToArraysWithStrings(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map('id', 'id', 'integer')
            ->map('first_name', 'first_name', 'string')
            ->map('last_name', 'last_name', 'string-with-blank|required')
            ->map('phone', 'phone', 'string')
            ->map('enabled', 'enabled', 'boolean');

        $rows = [];
        $rows[] = [
            'id' => '100',
            'first_name' => 'Sally',
            'last_name' => '',
            'phone' => null,
            'enabled' => '1',
        ];

        $rows[] = [
            'id' => '101',
            'first_name' => 'Max',
            'last_name' => 'Doe',
            'phone' => '+123456789',
            'enabled' => '0',
        ];

        $actual = $transformer->toArrays($rows);

        $this->assertSame(
            [
                [
                    'id' => 100,
                    'first_name' => 'Sally',
                    'last_name' => '',
                    'enabled' => true,
                ],
                [
                    'id' => 101,
                    'first_name' => 'Max',
                    'last_name' => 'Doe',
                    'phone' => '+123456789',
                    'enabled' => false,
                ],
            ],
            $actual
        );
    }

    /**
     * Test.
     */
    public function testTransformWithListOfData(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map(
            'items',
            'source',
            $transformer->rule()->transformList(
                function (ArrayTransformer $transformer) {
                    $transformer->map('id', 'id', 'integer')
                        ->map('first_name', 'first_name', 'string')
                        ->map('last_name', 'last_name', 'string-with-blank|required')
                        ->map('phone', 'phone', 'string')
                        ->map('enabled', 'enabled', 'boolean');
                }
            )
        );

        $rows = [];
        $rows[] = [
            'id' => '100',
            'first_name' => 'Sally',
            'last_name' => '',
            'phone' => null,
            'enabled' => '1',
        ];

        $rows[] = [
            'id' => '101',
            'first_name' => 'Max',
            'last_name' => 'Doe',
            'phone' => '+123456789',
            'enabled' => '0',
        ];

        $data = [
            'source' => $rows,
        ];

        $actual = $transformer->toArray($data);

        $this->assertSame(
            [
                'items' => [
                    [
                        'id' => 100,
                        'first_name' => 'Sally',
                        'last_name' => '',
                        'enabled' => true,
                    ],
                    [
                        'id' => 101,
                        'first_name' => 'Max',
                        'last_name' => 'Doe',
                        'phone' => '+123456789',
                        'enabled' => false,
                    ],
                ],
            ],
            $actual
        );
    }

    /**
     * Test.
     */
    public function testTransformWithData(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map(
            'item',
            'source',
            $transformer->rule()->transform(
                function (ArrayTransformer $transformer) {
                    $transformer->map('id', 'id', 'integer');
                }
            )
        );

        $data = [
            'source' => [
                'id' => '100',
                'first_name' => 'Sally',
            ],
        ];

        $actual = $transformer->toArray($data);

        // Empty array because the source is empty and the item is not required
        $this->assertSame(
            [
                'item' => [
                    'id' => 100,
                ],
            ],
            $actual
        );
    }

    /**
     * Test.
     */
    public function testTransformEmpty(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map(
            'items',
            'source',
            $transformer->rule()->transform(
                function (ArrayTransformer $transformer) {
                    $transformer->map('id', 'id', 'integer');
                }
            )
        );

        $data = [
            'source' => [],
        ];

        $actual = $transformer->toArray($data);

        // Empty array because the source is empty and the item is not required
        $this->assertSame([], $actual);
    }

    /**
     * Test.
     */
    public function testTransformListEmpty(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map(
            'items',
            'source',
            $transformer->rule()->transformList(
                function (ArrayTransformer $transformer) {
                    $transformer->map('id', 'id', 'integer');
                }
            )
        );

        $data = [
            'source' => [],
        ];

        $actual = $transformer->toArray($data);

        // Empty array because the source is empty and the item is not required
        $this->assertSame([], $actual);
    }

    /**
     * Test.
     */
    public function testTransformListEmptyButRequired(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map(
            'items',
            'source',
            $transformer->rule()->transform(
                function (ArrayTransformer $transformer) {
                    $transformer->map('id', 'id', 'integer');
                }
            )->required()
        );

        $data = [
            'source' => [],
        ];

        $actual = $transformer->toArray($data);

        // Items is null because the source is empty and the source is required
        $this->assertSame(
            [
                'items' => null,
            ],
            $actual
        );
    }
}
