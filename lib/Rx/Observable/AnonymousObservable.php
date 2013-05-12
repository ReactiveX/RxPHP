<?php

namespace Rx\Observable;

use Rx\ObserverInterface;

class AnonymousObservable extends BaseObservable
{
    private $subscribeAction;

    public function __construct($subscribeAction)
    {
        if ( ! is_callable($subscribeAction)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $this->subscribeAction = $subscribeAction;
    }

    /**
     * @override
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        // todo: add scheduler
        $subscribeAction = $this->subscribeAction;

        return $subscribeAction($observer, $scheduler);
    }

    protected function doStart($scheduler)
    {
        // todo: remove from base
    }
}
