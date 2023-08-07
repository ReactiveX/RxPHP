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
    public function __construct(int $timestampMillis, $value)
    {
        $this->timestampMillis = $timestampMillis;
        $this->value = $value;
    }

    /**
     * @param Timestamped|mixed $other
     * @return bool
     */
    public function equals($other): bool
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

    public function getTimestampMillis(): int
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