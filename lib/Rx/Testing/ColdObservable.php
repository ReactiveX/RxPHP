<?php

namespace Rx\Testing;

use Rx\Observable\BaseObservable;

class ColdObservable extends BaseObservable
{
    private $scheduler;
    private $messages;
    private $subscriptions;

    public function __construct($scheduler, $messages = array())
    {
        $this->scheduler     = $scheduler;
        $this->messages      = $messages ;
        $this->subscriptions = $subscriptions;
    }
}

