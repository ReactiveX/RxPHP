RxPHP
======

## This is the development branch for RxPHP v2 and is not stable.  For production, use [v1](https://github.com/reactivex/rxphp) instead.

Reactive extensions for PHP. The reactive extensions for PHP are a set of
libraries to compose asynchronous and event-based programs using observable
collections and LINQ-style query operators in PHP.

[![Build Status](https://secure.travis-ci.org/ReactiveX/RxPHP.png?branch=master)](https://travis-ci.org/ReactiveX/RxPHP)
[![Coverage Status](https://coveralls.io/repos/github/ReactiveX/RxPHP/badge.svg?branch=master)](https://coveralls.io/github/ReactiveX/RxPHP?branch=master)

## Example

```php
$source = \Rx\Observable::fromArray([1, 2, 3, 4]);

$source->subscribe(
    function ($x) {
        echo 'Next: ', $x, PHP_EOL;
    },
    function (Exception $ex) {
        echo 'Error: ', $ex->getMessage(), PHP_EOL;
    },
    function () {
        echo 'Completed', PHP_EOL;
    }
);

//Next: 1
//Next: 2
//Next: 3
//Next: 4
//Completed

```

## Try out the demos

```bash
$ git clone git@github.com:reactivex/RxPHP.git -b 2.x
$ cd RxPHP
$ composer install
$ php demo/interval/interval.php
```

Have fun running the demos in `/demo`.

note:  The demos are automatically run within `Loop::execute`.  When using RxPHP within your own project, you'll need to install a [loop implementation](https://packagist.org/providers/async-interop/event-loop-implementation).  

## Installation
1. Install one [async-interop event loop](https://packagist.org/providers/async-interop/event-loop-implementation) implementation.

With ReactPHP:
```bash
$ composer require wyrihaximus/react-async-interop-loop
```
With amphp:
```bash
$ composer require amphp/loop:dev-master
```
With KoolKode:
```bash
$ composer require koolkode/async
```

2. Install RxPHP using [composer](https://getcomposer.org).

```bash
$ composer require reactivex/rxphp:2.x-dev
```

3. Write some code

```PHP
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Rx\Observable;
use Interop\Async\Loop;

Loop::execute(function () {

    Observable::interval(1000)
        ->take(5)
        ->flatMap(function ($i) {
            return Observable::of($i + 1);
        })
        ->subscribe(function ($e) {
            echo $e, PHP_EOL;
        });

});
```
## Working with Promises

Some async PHP frameworks have yet to fully embrace the awesome power of observables.  To help ease the transition, RxPHP has support for promise libraries that implement the async-interop promise [specification](https://github.com/async-interop/promise).

Mixing a promise into an observable stream:

```PHP
Observable::interval(1000)
    ->flatMap(function ($i) {
        return Observable::fromPromise(new Resolved($i));
    })
    ->subscribe(function ($v) {
        echo $v . PHP_EOL;
    });
```

Converting an Observable into a promise. (This is useful for libraries that use generators and coroutines):
```PHP
$observable = Observable::interval(1000)
    ->take(10)
    ->toArray()
    ->map('json_encode');

$promise = $observable->toPromise();
```

## License

RxPHP is licensed under the MIT License - see the LICENSE file for details
