<?php

declare(strict_types = 1);

namespace Rx;

class Timestamped
{
    /** @var int */
    private $timestampMillis;

    /** @var mixed */
    private $value;

    /**
     * Timestamped constructor.
     * @param int $timestampMillis
     * @param mixed $value
     */
    public function __construct($timestampMillis, $value)
    {
        $this->timestampMillis = $timestampMillis;
        $this->value = $value;
    }

    public function equals($other)
    {
        if ($this === $other) {
            return true;
        }

        if (!($other instanceof Timestamped)) {
            return false;
        }
        if ($this->getTimestampMillis() !== $other->getTimestampMillis()) {
            return false;
        }
        if (is_scalar($this->value) && $this->value == $other->value) {
            return true;
        }
        if ($this->value === $other->value) {
            return true;
        }
        
        return false;
    }

    /**
     * @return int
     */
    public function getTimestampMillis()
    {
        return $this->timestampMillis;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}