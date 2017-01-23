<?php

namespace Rx\Observer;

use Exception;
use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\DisposableInterface;
use Rx\Disposable\SingleAssignmentDisposable;

class AutoDetachObserver extends AbstractObserver
{
    /** @var ObserverInterface */
    private $observer;

    /**
     * @param ObserverInterface $observer
     */
    public function __construct(ObserverInterface $observer)
    {
        $this->observer   = $observer;
        $this->disposable = new SingleAssignmentDisposable();
    }

    /**
     * @param DisposableInterface|null $disposable
     * @return void
     */
    public function setDisposable(DisposableInterface $disposable = null)
    {
        $disposable = $disposable ?: new EmptyDisposable();

        $this->disposable->setDisposable($disposable);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function completed()
    {
        try {
            $this->observer->onCompleted();
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function error(Exception $exception)
    {
        try {
            $this->observer->onError($exception);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function next($value)
    {
        try {
            $this->observer->onNext($value);
        } catch (Exception $e) {
            $this->dispose();
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function dispose()
    {
        $this->disposable->dispose();
    }
}
