<?php

abstract class Str {
    public static function contains($haystack, $needle) {
        return str_contains($haystack, $needle);
    }

    public static function containsAny($haystack, $needles) {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function firstWord($haystack) {
        $words = explode(' ', $haystack);
        return $words[0];
    }

    public static function startsWith($haystack, $needle) {
        return str_starts_with($haystack, $needle);
    }

    public static function substringAfter($haystack, $needle) {
        $pos = strpos($haystack, $needle);
        if (false === $pos) {
            return $haystack;
        }
        return trim(substr($haystack, $pos + strlen($needle)));
    }

    public static function substringUntil($haystack, $needle) {
        $pos = strpos($haystack, $needle);
        if (false === $pos) {
            return $haystack;
        }
        return trim(substr($haystack, 0, $pos));
    }
}

function group_by($collection, callable $groupSelector) {
    $grouped = [];
    foreach ($collection as $item) {
        $group = $groupSelector($item);
        if (!isset($grouped[$group])) {
            $grouped[$group] = [];
        }
        $grouped[$group][] = $item;
    }
    return $grouped;
}

function is_clean_git_checkout($path) {
    list($exitCode, $output) = run_cmd("git status --porcelain $path");
    return $exitCode === 0
        && count($output) === 0;
}

function run_cmd($cmd) {
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);
    return [$exitCode, $output];
}
