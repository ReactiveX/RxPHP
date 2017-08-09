<?php

namespace Rx\Promise;

use Amp\Promise as AmpPromise;
use React\Promise\ExtendedPromiseInterface as ExtendedReactPromise;

if (false) { // Declaration of PromiseInterface in an un-executed block to fool IDEs.
    interface PromiseInterface extends AmpPromise, ExtendedReactPromise {}
}

// Dynamically determine interfaces to extend and declare PromiseInterface using eval.
(function () {
    $interfaceName = 'PromiseInterface';
    $interfaces = [];

    if (\interface_exists(AmpPromise::class)) {
        $interfaces[] = '\\' . AmpPromise::class;
    }

    if (\interface_exists(ExtendedReactPromise::class)) {
        $interfaces[] = '\\' . ExtendedReactPromise::class;
    }

    if (empty($interfaces)) {
        eval(\sprintf('namespace %s; interface %s {}', __NAMESPACE__, $interfaceName));
        return;
    }

    eval(\sprintf(
        'namespace %s; interface %s extends %s {}',
        __NAMESPACE__,
        $interfaceName,
        \implode(', ', $interfaces)
    ));
})();
