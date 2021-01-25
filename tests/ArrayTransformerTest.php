<?php

namespace Selective\Transformer\Test;

use PHPUnit\Framework\TestCase;
use Selective\Transformer\ArrayTransformer;

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
    public function test(): void
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
    public function test2(): void
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
            'sub1' => [
                'sub2' => 'sub2value',
            ],
        ];

        $transformer = new ArrayTransformer();

        $transformer->registerFilter('trim', 'trim');
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
            ->map('user_role_id', 'user_role_id', $transformer->rule()->integer())
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
        //$actual = $transformer->transformArrays($data);

        $expected = [
            'username' => 'admin',
            'password' => '12345678',
            'email' => 'mail@example.com',
            'first_name' => 'Sally',
            'date_of_birth' => '1982-01-01',
            'user_role_id' => 2,
            'enabled' => true,
            'items' => [],
            'from-sub2' => 'sub2value',
            'custom1' => 'Custom1 value: test',
            'username2' => 'Callback value: admin',
        ];

        $this->assertSame($expected, $actual);
    }
}
