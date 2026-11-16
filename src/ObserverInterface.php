<?php

declare(strict_types = 1);

namespace Rx;

interface ObserverInterface
{
    public function onCompleted();

    public function onError(\Throwable $error);

    /** @template T */
    public function onNext($value);
}
