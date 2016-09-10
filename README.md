RxPHP
======

Reactive extensions for PHP. The reactive extensions for PHP are a set of
libraries to compose asynchronous and event-based programs using observable
collections and LINQ-style query operators in PHP.

[![Build Status](https://secure.travis-ci.org/ReactiveX/RxPHP.png?branch=master)](https://travis-ci.org/ReactiveX/RxPHP)
[![Coverage Status](https://coveralls.io/repos/github/ReactiveX/RxPHP/badge.svg?branch=master)](https://coveralls.io/github/ReactiveX/RxPHP?branch=master)

## Installation
Install dependencies using [composer](https://getcomposer.org).

```bash
$ composer.phar require reactivex/rxphp
```

## Example

```php
$source = \Rx\Observable::fromArray([1, 2, 3, 4]);

$subscription = $source->subscribe(new \Rx\Observer\CallbackObserver(
    function ($x) {
        echo 'Next: ', $x, PHP_EOL;
    },
    function (Exception $ex) {
        echo 'Error: ', $ex->getMessage(), PHP_EOL;
    },
    function () {
        echo 'Completed', PHP_EOL;
    }
));

//Next: 1
//Next: 2
//Next: 3
//Next: 4
//Completed

```

## Quick start for demos


```bash
$ composer.phar install
```

Have fun running the demos in `/demo`.


## License

RxPHP is licensed under the MIT License - see the LICENSE file for details
