<?php

declare(strict_types = 1);

namespace Rx\Testing;

class Recorded
{
    public function __construct(int $time, $value, callable $comparer = null)
    {
        $this->time     = $time;
        $this->value    = $value;
        $this->comparer = $comparer ?: function ($a, $b) {
            if (method_exists($a, 'equals')) {
                return $a->equals($b);
            }

            return $a === $b;
        };
    }

    public function equals(Recorded $other)
    {
        $comparer = $this->comparer;

        return $this->time === $other->time
            && $comparer($this->value, $other->value);
    }

    public function __toString()
    {
        return $this->value . '@' . $this->time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getValue()
    {
        return $this->value;
    }
}
