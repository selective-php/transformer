<?php

namespace Selective\Transformer\Test;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Selective\Transformer\ArrayTransformer;
use Selective\Transformer\Exceptions\ArrayTransformerException;
use Selective\Transformer\Filter\SprintfFilter;
use stdClass;

/**
 * Test.
 */
class ArrayTransformerTransformTest extends TestCase
{
    /**
     * Test.
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
            ->map('items', '', $transformer->rule()->array()->required()->default([]))
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
    public function testObject()
    {
        $transformer = new ArrayTransformer();

        $transformer->map('bar1', 'foo.bar', 'string')
            ->map('bar2', 'foo.bar2.0', 'string')
            ->map('sub.sub2.sub3', 'foo.bar2.1', 'string');

        $user = new stdClass();
        $user->foo = new stdClass();
        $user->foo->bar = 'Hello Bar';
        $user->foo->bar2 = [
            0 => 'Test 0',
            1 => 'Test 1',
        ];

        $actual = $transformer->toArray((array)$user);

        // Items are null because the source is empty and the source is required
        $this->assertSame(
            [
                'bar1' => 'Hello Bar',
                'bar2' => 'Test 0',
                'sub' => [
                    'sub2' => [
                        'sub3' => 'Test 1',
                    ],
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
    public function testEmptyKey()
    {
        $transformer = new ArrayTransformer();
        $transformer->map('bar', '', 'string');
        $actual = $transformer->toArray([]);

        $this->assertSame([], $actual);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSetDefault()
    {
        $transformer = new ArrayTransformer();

        $transformer->set('bar.0.item', 'default-value');

        // The same
        $transformer->map('bar.0.item2', '', $transformer->rule()->default('default-value2'));

        $actual = $transformer->toArray([]);

        $this->assertSame(
            [
                'bar' => [
                    [
                        'item' => 'default-value',
                        'item2' => 'default-value2',
                    ],
                ],
            ],
            $actual
        );
    }

    /**
     * Test.
     */
    public function testCallback(): void
    {
        $transformer = new ArrayTransformer();
        $transformer->registerFilter('lower', 'strtolower');

        $transformer->map(
            'destination',
            'source',
            $transformer->rule()->transform(
                function (ArrayTransformer $transformer) {
                    $transformer->map('test1', 'xxx', 'integer')
                        ->map('test2', 'yyy', 'lower');
                }
            )
        );

        $actual = $transformer->toArray(
            [
                'source' => [
                    'xxx' => 123,
                    'yyy' => 'ABC',
                ],
            ]
        );

        $expected = [
            'destination' => [
                'test1' => 123,
                'test2' => 'abc',
            ],
        ];

        $this->assertSame($expected, $actual);
    }
}
