<?php

declare(strict_types = 1);

namespace Rx;

interface ObserverInterface
{
    public function onCompleted();

    public function onError(\Throwable $error);

    public function onNext($value);
}
