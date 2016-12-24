<?php

require_once __DIR__ . '/../bootstrap.php';

$observable = \Rx\Observable::fromArray(['a', 'b', 'c'])->_Vendor_rot13();

$observable->subscribe($stdoutObserver);
