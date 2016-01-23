<?php

namespace Rx;

interface ObservableInterface
{
    /**
     * @param ObserverInterface $observer
     * @return DisposableInterface
     */
    public function subscribe(ObserverInterface $observer);
}
