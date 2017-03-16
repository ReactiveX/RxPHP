<?php

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
} else {
    throw new RuntimeException('Install dependencies to run doc builder.');
}

require_once __DIR__ . '/doc-loader.php';
require_once __DIR__ . '/doc-writer.php';
require_once __DIR__ . '/utils.php';

function println($line) {
    $args = func_get_args();
    echo call_user_func_array('sprintf', $args) . "\n";
}

function run_lint() {
    $docs = load_all_docs();
    println("Successfully loaded documentation for %d observables/operators\n",
        count($docs)
    );

    foreach ($docs as $doc) {
        println(
            "- %s with %d demo(s) as %s",
            $doc->methodName,
            count($doc->demos),
            $doc->isObservable ? 'observabe' : 'operator'
        );
    }

    exit(0);
}

function run_reactivex() {
    $allDocs = load_all_docs();

    $docs = array_filter($allDocs, function ($doc) {
        return !$doc->isDeprecated;
    });

    $repoRoot = __DIR__ . '/reactivex.github.io';
    $docsPath = $repoRoot . '/documentation/operators/';
    if (!is_dir($docsPath)) {
        println("Expecting a valid reactivex.io checkout at @ $docsPath");
        exit(1);
    }

    // Change to reactivex doc repository
    chdir($repoRoot);
    if (!is_clean_git_checkout(getcwd())) {
        println("ReactiveX docs checkout should be clean at:");
        println("  " . $repoRoot);
        exit(1);
    }

    $reactivexDocs = load_all_reactivex_docs($docsPath);

    $grouped_docs = group_by(
        $docs,
        function($item) { return $item->reactivexId; }
    );

    $diff = array_diff(array_keys($grouped_docs), array_keys($reactivexDocs));
    if (count($diff) != 0) {
        println("ReactiveX id(s) '%s' not found", implode(', ', $diff));
        exit(1);
    }

    foreach ($grouped_docs as $id => $docs) {
        update_documentation($reactivexDocs[$id], $docs);
    }
    exit(0);
}

function run_usage() {
    println("Usage: $argv[0] <command>");
    println("  lint      Verifies that documentation can be loaded");
    println("  reactivex Updates the reactivex documentation");
    exit(1);
}

function main($argv) {
    if (count($argv) !== 2) {
        run_usage();
    }

    switch ($argv[1]) {
        case 'lint':
            run_lint();
        case 'reactivex':
            run_reactivex();
        default:
            println("Unknown command $argv[1]");
            run_usage();
    }
}
main($argv);
