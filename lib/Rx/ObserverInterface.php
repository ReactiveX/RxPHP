<?php

namespace Rx;

use Exception;

interface ObserverInterface
{
    function onCompleted();
    function onError(Exception $error);
    function onNext($value);
}
