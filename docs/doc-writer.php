<?php

function load_all_reactivex_docs($docsPath) {
    $documentationFiles = glob($docsPath . '/*.html');
    $docsPerId = [];
    foreach ($documentationFiles as $file) {
        $contents = file_get_contents($file);
        $lines = explode("\n", $contents);
        $id = Str::substringAfter($lines[3], 'id:');
        $docsPerId[$id] = $file;
    }
    return $docsPerId;
}

function update_documentation($path, $docs) {
    $htmlDocs = build_documentation($docs);
    $currentDocumentation = file_get_contents($path);
    $currentLines = explode("\n", $currentDocumentation);

    $newLines = explode("\n", $htmlDocs);

    if (!Str::contains($currentDocumentation, 'RxPHP')) {
        $insertPosition = get_insert_position($currentLines);
        $newLines[] = ""; // extra new line at the end
        array_splice($currentLines, $insertPosition, 0, $newLines);
    } else {
        list($position, $length) = get_replace_position($currentLines);
        array_splice($currentLines, $position, $length);
        array_splice($currentLines, $position, 0, $newLines);
    }

    file_put_contents($path, implode("\n", $currentLines));
}

function get_insert_position($lines) {
    $docPositions = array_filter(
        $lines,
        function($line) { return Str::contains($line, '{% lang_operator'); }
    );

    $documentedLibraries = array_map(
        function($line) {
            return Str::firstWord(Str::substringAfter($line, 'lang_operator'));
        },
        $docPositions
    );

    foreach ($documentedLibraries as $position => $library) {
        if (strcmp($library, 'RxPHP') <= 0) {
            continue;
        }
        return $position;
    }

    return count($lines) - 2; // at the end of the file by default
}

function get_replace_position($lines) {
    $start = -1;
    foreach ($lines as $position => $line) {
        if (Str::contains($line, '{% lang_operator RxPHP')) {
            $start = $position;
            continue;
        }
        if ($start != -1 && Str::contains($line, '{% endlang_operator')) {
            $end = $position;
            break;
        }
    }
    return array($start, $end - $start + 1);
}

function build_documentation($docs) {
//    usort(
//        $docs,
//        function($a, $b) { return strcmp($a->methodName, $b->methodName); }
//    );
    $names = array_map(
        function($doc) { return $doc->methodName; },
        $docs
    );

    $langOperatorNames = implode(' ', $names);

    $htmlDocs = '';
    for ($i = 0; $i < count($docs); $i++) {
        $htmlDocs .= build_variant_documentation($i, $docs[$i]);
    }

    $docs = <<<DOCUMENTATION
  {% lang_operator RxPHP $langOperatorNames %}
$htmlDocs
  {% endlang_operator %}
DOCUMENTATION;
    return $docs;
}

function build_variant_documentation($index, $doc) {
    $introduction = build_documentation_introduction($index, $doc);
    $description = $doc->description;

    $demos = array_map('build_code_samples', $doc->demos);

    $sampleCode = '';
    if (count($demos) > 0) {
        $sampleCode = '<h4>Sample Code</h4>'."\n";
        $sampleCode .= implode("\n\n", $demos);
    }
    return <<<DOCUMENTATION
<figure class="variant">
    <figcaption>
    <p>
    $introduction
    </p>
    <p>
    $description
    </p>
$sampleCode
    </figcaption>
</figure>
DOCUMENTATION;
}

function build_documentation_introduction($index, $doc) {
    $template = $index === 0
        ? 'RxPHP implements this operator as <code>%s</code>.'
        : 'RxPHP also has an operator <code>%s</code>.';
    return sprintf($template, $doc->methodName);
}

function build_code_samples(Demo $demo) {
    $lines = explode("\n", $demo->demoCode);
    $start = -1;
    foreach ($lines as $position => $line) {
        if (Str::contains($line, 'require_once')) {
            $start = $position;
            break;
        }
    }
    array_splice($lines, 0, $start + 2);
    $demoCode = implode("\n", $lines);
    $url = $demo->getURL();

    return <<<DEMO
<div class="code php">
    <pre>
//from $url

$demoCode
   </pre>
</div>
<div class="output">
    <pre>
$demo->demoOutput
    </pre>
</div>
DEMO;
}
