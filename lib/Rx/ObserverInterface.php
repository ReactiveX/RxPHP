<?php

namespace Rx;

use Exception;

interface ObserverInterface
{
    /**
     * @return void
     */
    public function onCompleted();

    /**
     * @return void
     */
    public function onError(Exception $error);

    /**
     * @return void
     */
    public function onNext($value);
}
