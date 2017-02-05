<?php

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
    $autoload->addPsr4('Vendor\\Rx\\Operator\\', __DIR__ . '/custom-operator');
} else {
    throw new RuntimeException('Install dependencies to run benchmark suite.');
}

use Rx\Observable;
use Rx\Observer\CallbackObserver;

define('MIN_TOTAL_DURATION', 5);
$start = microtime(true);

if ($_SERVER['argc'] == 1) {
    $files = glob(__DIR__ . '/**/*.php');
} else {
    // Force absolute path
    $files = array_map(function($file) {
        return $file[0] == DIRECTORY_SEPARATOR ? $file : $_SERVER['PWD'] . DIRECTORY_SEPARATOR . $file;
    }, array_slice($_SERVER['argv'], 1));
}

Observable::just($files)
    ->doOnNext(function(array $files) {
        printf("Benchmarking %d file/s (min %ds each)\n", count($files), MIN_TOTAL_DURATION);
        printf("script_name - total_runs (single_run_mean ±standard_variant)\n");
        printf("============================================================\n");
    })
    ->concatMap(function($files) { // flatten the array
        return Observable::fromArray($files);
    })
    ->doOnNext(function($file) {
        printf('%s', pathinfo($file, PATHINFO_FILENAME));
    })
    ->map(function($file) { // run benchmark
        $totalDuration = 0.0;
        $durations = [];

        ob_start();

        $dummyObserver = new Rx\Observer\CallbackObserver(
            function ($value) { },
            function ($error) { },
            function () { }
        );

        $testClosure = @include $file;
        if (!$testClosure) {
            throw new Exception("Unable to load file \"$file\"");
        }

        while ($totalDuration < MIN_TOTAL_DURATION) {
            $start = microtime(true);

            $testClosure();

            $duration = microtime(true) - $start;

            $durations[] = $duration * 1000;
            $totalDuration += $duration;
        }

        ob_end_clean();

        return [
            'file' => $file,
            'durations' => $durations,
        ];
    })
    ->doOnNext(function($result) { // Print the number of successful runs
        printf(' - %d', count($result['durations']));
    })
    ->map(function($result) { // Calculate the standard deviance
        $count = count($result['durations']);
        $mean = array_sum($result['durations']) / $count;

        $variance = array_sum(array_map(function($duration) use ($mean) {
            return pow($mean - $duration, 2);
        }, $result['durations']));

        return [
            'file' => $result['file'],
            'mean' => $mean,
            'standard_variance' => pow($variance / $count, 0.5),
        ];
    })
    ->subscribe(new CallbackObserver(
        function($result) {
            printf(" (%.2fms ±%.2fms)\n", $result['mean'], $result['standard_variance']);
        },
        function(\Exception $error) {
            printf("\nError: %s\n", $error->getMessage());
        },
        function() use ($start) {
            printf("============================================================\n");
            printf("total duration: %.2fs\n", microtime(true) - $start);
        }
    ));


//$dirIter = new RecursiveDirectoryIterator($dir);
//$iter = new RecursiveIteratorIterator($dirIter);
//$iter = new DirectoryIterator($dir);
//$iter = new RegexIterator($iter, $pattern, RegexIterator::GET_MATCH);