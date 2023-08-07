<?php

namespace Rx\React;

class RejectedPromiseException extends \Exception
{
    /**
     * @var mixed
     */
    private $rejectValue;

    /**
     * RejectedPromiseException constructor.
     * @param mixed $rejectValue
     */
    public function __construct($rejectValue)
    {
        $this->rejectValue = $rejectValue;

        parent::__construct("Promise rejected with non-exception");
    }

    /**
     * @return mixed
     */
    public function getRejectValue()
    {
        return $this->rejectValue;
    }
}
