<?php

namespace Selective\Transformer\Test;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Selective\Transformer\ArrayTransformer;
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
    public function testSystemTimZone()
    {
        $date = new DateTimeImmutable('2021-01-01 00:00:00');
        $date = $date->setTimezone(new DateTimeZone('+01:00'));

        $this->assertSame('+01:00', $date->getTimezone()->getName());
        $this->assertSame('2021-01-01 00:00:00', $date->format('Y-m-d H:i:s'));
        $this->assertSame('2021-01-01T00:00:00.0000000+01:00', $date->format('Y-m-d\TH:i:s.u0P'));
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

        $transformer->registerFilter('sprintf', SprintfFilter::class);

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
            ->map('date_of_birth', 'date_of_birth', $transformer->rule()->date('Y-m-d'))
            ->map(
                'transaction_date',
                'transaction_date',
                $transformer->rule()->date('Y-m-d\TH:i:s.u0P', new DateTimeZone('+01:00'))
            )
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
        //$actual = $transformer->toArrays($data);

        $expected = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
            'first_name' => 'Sally',
            'date_of_birth' => '1982-01-01',
            'transaction_date' => '2021-01-24T15:45:30.0000000+01:00',
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
}
