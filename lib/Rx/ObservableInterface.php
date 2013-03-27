<?php

namespace Rx;

interface ObservableInterface
{
    /**
     * @return DisposableInterface
     */
    function subscribe(ObserverInterface $observer);
}
