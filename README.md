# selective/transformer

A strictly typed array transformer with dot access and fluent interface. 
The mapped result can be used for JSON responses and many other things.

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/transformer.svg)](https://packagist.org/packages/selective/transformer)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/selective-php/transformer/workflows/build/badge.svg)](https://github.com/selective-php/transformer/actions)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/selective-php/transformer.svg)](https://scrutinizer-ci.com/g/selective-php/transformer/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/selective-php/transformer.svg)](https://scrutinizer-ci.com/g/selective-php/transformer/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/transformer.svg)](https://packagist.org/packages/selective/transformer/stats)

## Requirements

* PHP 7.2+ or 8.0+

## Installation

```bash
composer require selective/transformer
```

## Usage

Sample data:

```php
$data = [
    'first_name' => 'Sally',
    'last_name' => '',
    'email' => 'sally@example.com',
];
```

### Minimal example

```php
<?php

use Selective\Transformer\ArrayTransformer;

$transformer = new ArrayTransformer();

$transformer->map('firstName', 'first_name')
    ->map('lastName', 'last_name')
    ->map('email', 'email');
    
$result = $transformer->toArray($data);
```

The result:

```php
[
    'firstName' => 'Sally',
    'email' => 'mail@example.com',
];
```

### Dot access

You can copy any data from the source array to any sub-element of the destination array
using the dot-syntax.

```php
<?php

$transformer->map('firstName', 'address.first_name')
    ->map('lastName', 'address.last_name')
    ->map('invoice.items', 'root.sub1.sub2.items');
```

### Simple mapping rules

Using strings, separated by `|`, to define a filter chain:

```php
<?php

use Selective\Transformer\ArrayTransformer;

$transformer = new ArrayTransformer();

$transformer->map('firstName', 'first_name', 'string|required')
    ->map('lastName', 'last_name', 'string|required')
    ->map('email', 'email', 'string|required');
    
$result = $transformer->toArray($data);
```

Because `lastName` is blank but required the result looks like this:

```php
[
    'firstName' => 'Sally',
    'lastName' => '',
    'email' => 'mail@example.com',
];
```

### Complex mapping rules

For complexer mapping rules there is a fluent interface available. To create a new rule use the `rule` method and pass
it as 3rd. parameter to the `map` method.

```php
<?php

use Selective\Transformer\ArrayTransformer;

$transformer = new ArrayTransformer();

$transformer->map('firstName', 'first_name', $transformer->rule()->string()->required())
    ->map('lastName', 'last_name', $transformer->rule()->string()->required())
    ->map('email', 'email', $transformer->rule()->string()->required());
    
$result = $transformer->toArray($data);
```

Because `lastName` is blank but required the result looks like this:

```php
[
    'firstName' => 'Sally',
    'lastName' => '',
    'email' => 'mail@example.com',
];
```

## Filter

Most filters are directly available as rule method.

```php
// Cast value to string, convert blank to null
$transformer->rule()->string();

// Cast value to string, disable blank to null
$transformer->rule()->string(false);

// Cast value to int
$transformer->rule()->integer();

// Cast value to float
$transformer->rule()->float();

// Cast value to bool
$transformer->rule()->boolean();

// Cast value to datetime string, default: Y-m-d H:i:s
$transformer->rule()->date();

// Cast value to date string
$transformer->rule()->date('Y-m-d');

// Format value to number using the number_format function
$transformer->rule()->number();

// Format value to number with a custom number format
$transformer->rule()->number(2, '.', ',');

// Cast value to array
$transformer->rule()->array();

// Cast value using a custom callback function
$transformer->rule()->callback(
    function ($value) {
        return 'My custom value: ' . $value;
    }
);
```

## Custom filters

You can also add your own custom filter:

```php
$transformer = new ArrayTransformer();

// Add a trim filter using the native trim function
$transformer->registerFilter('trim', 'trim');

// Add a custom filter using a callback
$transformer->registerFilter(
    'custom1',
    function ($value) {
        return '  Custom1 value: ' . $value;
    }
);

// Usage
$transformer->map(
    'destination',
    'source',
    $transformer->rule()->filter('trim')
);

// It is possible to chain multiple filters
$transformer->map(
    'destination',
    'source',
    $transformer->rule()->filter('custom1')->filter('trim')->required()->default('example')
);
```

Define a custom filter using a mapping specific callback:

```php
$transformer = new ArrayTransformer();

$transformer->map(
    'destination',
    'source',
    $transformer->rule()->callback(
        function ($value) {
            return 'Callback value: ' . $value;
        }
    )
);
```

There are even more filter classes available that can be registered manually:

```php
use Selective\Transformer\Filter;

$transformer = new ArrayTransformer();

// Convert the value using the sprintf function
$transformer->registerFilter('sprintf', SprintfFilter::class);

// Usage
$transformer->map('destination', 'source', 'sprintf');

// or

$transformer->map('destination', 'source', $transformer->rule()->filter('sprintf', 'Count: %d'));
```

You can also implement and register your own filter classes as well.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
