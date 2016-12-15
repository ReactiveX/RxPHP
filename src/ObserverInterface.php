<?php

namespace Rx;

use Exception;

interface ObserverInterface
{
    public function onCompleted();

    public function onError(Exception $error);

    public function onNext($value);
}
