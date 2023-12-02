<?php

declare(strict_types = 1);

namespace Rx;

interface DisposableInterface
{
    /**
     * @return void
     */
    public function dispose();
}
