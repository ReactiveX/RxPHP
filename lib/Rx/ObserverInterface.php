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
     * @param \Exception $error
     * @return void
     */
    public function onError(Exception $error);

    /**
     * @param mixed $value
     * @return void
     */
    public function onNext($value);
}
