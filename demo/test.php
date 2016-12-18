<?php

function run_cmd($file) {
    $output = [];
    $exit_code = -1;
    exec(
        PHP_BINARY . ' ' . $file,
        $output,
        $exit_code
    );
    return array($exit_code, implode("\n", $output));
}

function run_demo($file) {
    list($exit_code, $output) = run_cmd($file);
    if ($exit_code != 0) {
        return false;
    }
    $expected = file_get_contents($file . '.expect');
    if (trim($output) != trim($expected)) {
        echo $file . " output does not match expected output:\n";
        echo "--- Actual output:\n";
        echo $output;
        echo "--- End actual output\n";
        echo "--- Expected output:\n";
        echo $expected;
        echo "--- End expected output\n";
    }
    return trim($output) == trim($expected);
}

function find_demos($dir) {
    return glob($dir . '/*/*.php');
}

function has_expect($file) {
    return file_exists($file . '.expect');
}

function strip_cwd($cwd, $absolute_path) {
    $is_prefix = 0 === strpos($absolute_path, $cwd);
    if (!$is_prefix) {
        return $absolute_path;
    }
    return substr($absolute_path, strlen($cwd) + 1 /* strip trailing / */);
}

function main($cwd) {
    $demos = array_filter(find_demos(__DIR__), 'has_expect');

    echo sprintf("Found %d demos with an expect file.\n", count($demos));

    $overall_success = true;
    foreach ($demos as $demo) {
        $result = run_demo($demo);
        $overall_success = $overall_success && $result;

        $readable = $result ? 'v' : 'x';
        echo sprintf("[%s] %s\n", $readable, strip_cwd($cwd, $demo));
    }

    return $overall_success ? 0 : 1;
}

exit(main(getcwd()));
