<?php

require_once __DIR__ . '/../bootstrap.php';

// Create subject
$subject = new \Rx\Subject\AsyncSubject();

// Send a value
$subject->onNext(42);
$subject->onCompleted();

// Hide its type
$source = $subject->asObservable();

$source->subscribe($stdoutObserver);