<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 9/15/15
 * Time: 5:22 PM
 */

namespace Rx\Disposable;

use Rx\DisposableInterface;

class SerialDisposable implements DisposableInterface
{
    private $isDisposed = false;

    /** @var DisposableInterface */
    private $disposable = null;

    function dispose()
    {
        if (!$this->isDisposed) {
            $this->isDisposed = true;
            $old = $this->disposable;
            $this->disposable = null;

            if ($old instanceof DisposableInterface) {
                $old->dispose();
            }
        }
    }

    /**
     * @return DisposableInterface
     */
    public function getDisposable()
    {
        return $this->disposable;
    }

    /**
     * @param DisposableInterface $disposable
     */
    public function setDisposable($disposable)
    {
        $shouldDispose = $this->isDisposed;
        if (!$shouldDispose) {
            $old = $this->disposable;
            $this->disposable = $disposable;
            if ($old instanceof DisposableInterface) {
                $old->dispose();
            }
        }

        if ($shouldDispose && $disposable instanceof DisposableInterface) {
            $disposable->dispose();
        }
    }
}