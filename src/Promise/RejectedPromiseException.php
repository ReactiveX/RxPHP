<?php

namespace Rx\Promise;

class RejectedPromiseException extends \Exception
{
    private $rejectValue;

    /**
     * RejectedPromiseException constructor.
     */
    public function __construct($rejectValue)
    {
        $this->rejectValue = $rejectValue;

        parent::__construct("Promise rejected with non-exception");
    }

    /**
     * @return string
     */
    public function getRejectValue()
    {
        return $this->rejectValue;
    }
}
