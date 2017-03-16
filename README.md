RxPHP
======

Reactive extensions for PHP. The reactive extensions for PHP are a set of
libraries to compose asynchronous and event-based programs using observable
collections and LINQ-style query operators in PHP.

[![Build Status](https://secure.travis-ci.org/ReactiveX/RxPHP.png?branch=master)](https://travis-ci.org/ReactiveX/RxPHP)
[![Coverage Status](https://coveralls.io/repos/github/ReactiveX/RxPHP/badge.svg?branch=master)](https://coveralls.io/github/ReactiveX/RxPHP?branch=master)

note:  This repo is for v2.x, the latest version of RxPHP, not [v1.x](https://github.com/ReactiveX/RxPHP/tree/1.x).

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

note:  When running the demos, the scheduler is automatically bootstrapped.  When using RxPHP within your own project, you'll need to set the default scheduler. 

## Installation
1. Install an event loop.  Any event loop should work, but the ReactPHP event loop is recommended.

```bash
$ composer require react/event-loop
```

2. Install RxPHP using [composer](https://getcomposer.org).

```bash
$ composer require reactivex/rxphp
```

3. Write some code.

```PHP
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Rx\Observable;
use React\EventLoop\Factory;
use Rx\Scheduler;

$loop = Factory::create();

//You only need to set the default scheduler once
Scheduler::setDefaultFactory(function() use($loop){
    return new Scheduler\EventLoopScheduler($loop);
});

Observable::interval(1000)
    ->take(5)
    ->flatMap(function ($i) {
        return Observable::of($i + 1);
    })
    ->subscribe(function ($e) {
        echo $e, PHP_EOL;
    });

$loop->run();

```
## Working with Promises

Some async PHP frameworks have yet to fully embrace the awesome power of observables.  To help ease the transition, RxPHP has built in support for [ReactPHP promises](https://github.com/reactphp/promise).

Mixing a promise into an observable stream:

```PHP
Observable::interval(1000)
    ->flatMap(function ($i) {
        return Observable::fromPromise(\React\Promise\resolve(42 + $i));
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

## Working with [Amphp](https://github.com/amphp)

Since RxPHP has support for promises, it can play nicely with async frameworks like Amphp:

```PHP
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

//This only needs to be set once
\Rx\Scheduler::setDefaultFactory(function () {
    return new \Rx\Scheduler\EventLoopScheduler('Amp\Loop::delay');
});

Amp\Loop::run(function () {
    echo yield \Rx\Observable::interval(1000)
        ->take(1)
        ->toPromise();
});

```

Amp expects a promise to be yielded, so you need to pick a single value from the observable stream.  In this case we use the `take(1)` operator to take the first value.


## Additional Information
- [The Official Reactive Extensions Documentation](http://reactivex.io/documentation/observable.html)
- [A Simple Introduction to Observables](https://www.youtube.com/watch?v=uQ1zhJHclvs)(video)


## License

RxPHP is licensed under the MIT License - see the LICENSE file for details
