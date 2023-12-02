<?php

declare(strict_types = 1);

namespace Rx\Testing;

class Recorded
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var callable
     */
    private $comparer;

    /**
     * @param int $time
     * @param mixed $value
     * @param callable|null $comparer
     */
    public function __construct(int $time, $value, callable $comparer = null)
    {
        $this->time     = $time;
        $this->value    = $value;
        $this->comparer = $comparer ?: function ($a, $b) {
            if (is_object($a) && method_exists($a, 'equals')) {
                return $a->equals($b);
            }

            return $a === $b;
        };
    }

    public function equals(Recorded $other): bool
    {
        $comparer = $this->comparer;

        return $this->time === $other->time
            && $comparer($this->value, $other->value);
    }

    public function __toString()
    {
        return $this->value . '@' . $this->time;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
