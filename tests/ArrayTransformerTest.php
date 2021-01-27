<?php

namespace Selective\Transformer\Test;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Selective\Transformer\ArrayTransformer;
use Selective\Transformer\Exceptions\ArrayTransformerException;
use Selective\Transformer\Filter\SprintfFilter;

/**
 * Test.
 */
class ArrayTransformerTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testSimpleStringMapping(): void
    {
        $data = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
            'first_name' => 'Sally',
            'last_name' => '',
            'date_of_birth' => '1982-01-01 15:45:30',
            'user_role_id' => '2',
            'locale' => 'de_DE',
            'enabled' => 1,
        ];

        $transformer = new ArrayTransformer();

        $transformer->map('username', 'username', 'string|required')
            ->map('password', 'password', 'string|required')
            ->map('email', 'email', 'string|required');

        $actual = $transformer->toArray($data);

        $expected = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testComplexMapping(): void
    {
        $data = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
            'first_name' => 'Sally',
            'last_name' => '',
            'date_of_birth' => '1982-01-01 15:45:30',
            'transaction_date' => new DateTimeImmutable('2021-01-24 15:45:30'),
            'user_role_id' => '2',
            'amount' => 3.14159,
            'number' => 3.14159,
            'locale' => 'de_DE',
            'enabled' => 1,
            'comment' => ' Test ',
            'sub1' => [
                'sub2' => 'sub2value',
            ],
        ];

        $transformer = new ArrayTransformer();

        $transformer->registerFilter('trim', 'trim');

        $transformer->registerFilter('sprintf', new SprintfFilter());

        $transformer->registerFilter(
            'custom1',
            function ($value) {
                return '  Custom1 value: ' . $value;
            }
        );

        $transformer->map('username', 'username', $transformer->rule()->string()->required())
            ->map('password', 'password')
            ->map('email', 'email')
            ->map('nada', 'nada')
            ->map('first_name', 'first_name', $transformer->rule()->string()->required())
            ->map('last_name', 'last_name', $transformer->rule()->string())
            ->map('user_role_id', 'user_role_id', $transformer->rule()->integer())
            ->map('amount', 'amount', $transformer->rule()->float())
            ->map('amount2', 'amount', $transformer->rule()->number(2)->float())
            ->map('amount3', 'amount', $transformer->rule()->filter('sprintf', '%02.3f')->string())
            ->map('comment', 'comment', $transformer->rule()->filter('trim'))
            ->map('enabled', 'enabled', $transformer->rule()->boolean())
            ->map('items', 'items', $transformer->rule()->array()->required()->default([]))
            ->map('from-sub2', 'sub1.sub2')
            ->map(
                'custom1',
                'custom1',
                $transformer->rule()->filter('custom1')->filter('trim')->required()->default('test')
            )
            ->map(
                'username2',
                'username',
                $transformer->rule()->required()->callback(
                    function ($value) {
                        return 'Callback value: ' . $value;
                    }
                )
            );

        $actual = $transformer->toArray($data);

        $expected = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
            'first_name' => 'Sally',
            'user_role_id' => 2,
            'amount' => 3.14159,
            'amount2' => 3.14,
            'amount3' => '3.142',
            'comment' => 'Test',
            'enabled' => true,
            'items' => [],
            'from-sub2' => 'sub2value',
            'custom1' => 'Custom1 value: test',
            'username2' => 'Callback value: admin',
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDateTimeFilter(): void
    {
        $transformer = new ArrayTransformer();

        $transformer->map('datetime', 'date', $transformer->rule()->date())
            ->map('date', 'date', $transformer->rule()->date('Y-m-d'))
            ->map('date2', 'date2', $transformer->rule()->date());

        $actual = $transformer->toArray(
            [
                'date' => '2021-01-01 00:00:00',
                'date2' => new DateTimeImmutable('2021-01-01 00:00:00'),
            ]
        );

        $this->assertSame(
            [
                'datetime' => '2021-01-01 00:00:00',
                'date' => '2021-01-01',
                'date2' => '2021-01-01 00:00:00',
            ],
            $actual
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDateTimeFilterAndTimeZone(): void
    {
        // Works only with UTC
        $defaultZone = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $transformer = new ArrayTransformer();

        $transformer->map('ymd', 'date', $transformer->rule()->date())
            ->map('atom', 'date', $transformer->rule()->date(DateTimeInterface::ATOM));

        $actual = $transformer->toArray(
            [
                'date' => '2021-01-01 00:00:00',
            ]
        );

        $this->assertSame(
            [
                'ymd' => '2021-01-01 00:00:00',
                'atom' => '2021-01-01T00:00:00+00:00',
            ],
            $actual
        );

        date_default_timezone_set($defaultZone);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDateTimParserError(): void
    {
        $this->expectException(ArrayTransformerException::class);
        $this->expectErrorMessageMatches('/Failed to parse time string/');

        $transformer = new ArrayTransformer();
        $transformer->map('date', 'date', $transformer->rule()->date('Y-m-d'));

        $transformer->toArray(
            [
                'date' => '2021-01-',
            ]
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDateTimeDateTimeZoneException(): void
    {
        $this->expectException(ArrayTransformerException::class);
        $this->expectErrorMessage(
            'Changing the DateTimeZone of an existing DateTimeImmutable object is not supported.'
        );

        $transformer = new ArrayTransformer();

        $transformer->map(
            'date',
            'date',
            $transformer->rule()->date('Y-m-d\TH:i:s.u0P', new DateTimeZone('+01:00'))
        );

        $transformer->toArray(
            [
                'date' => new DateTimeImmutable('2021-01-24 15:45:30'),
            ]
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testUndefinedFilterException(): void
    {
        $this->expectException(ArrayTransformerException::class);
        $this->expectErrorMessage('Filter not found: foo');

        $transformer = new ArrayTransformer();
        $transformer->map('field', 'field', $transformer->rule()->filter('foo'));

        $transformer->toArray(
            [
                'field' => 'value',
            ]
        );
    }

    /**
     * Test.
     *
     * @return void
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
     *
     * @return void
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
}
