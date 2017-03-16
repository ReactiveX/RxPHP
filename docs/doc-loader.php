<?php

class DocumentedMethod
{
    public $methodName;
    public $reactivexId;
    public $description;
    public $demos;
    public $isObservable;
    public $isDeprecated;

    private function __construct(
        $methodName,
        $reactivexId,
        $description,
        $demos,
        $isObservable,
        $isDeprecated
    ) {
        $this->methodName = $methodName;
        $this->reactivexId = $reactivexId;
        $this->description = $description;
        $this->demos = $demos;
        $this->isObservable = $isObservable;
        $this->isDeprecated = $isDeprecated;
    }

    public static function fromReflectionMethod(
        \ReflectionMethod $method
    ) {
        $methodName = $method->getName();
        $docComment = $method->getDocComment();

        $reactivexId = extract_doc_annotation($docComment, '@reactivex');
        $demoFiles = extract_doc_annotations($docComment, '@demo');

        $description = extract_doc_description($docComment);

        $isObservable = Str::contains($docComment, '@observable');
        $isDeprecated = Str::contains($docComment, '@deprecated');

        $demos = array_map(
            function($path) { return Demo::fromPath($path); },
            $demoFiles
        );

        return new DocumentedMethod(
            $methodName,
            $reactivexId,
            $description,
            $demos,
            $isObservable,
            $isDeprecated
        );
    }
}

class Demo
{
    const BASE_DEMO_URL = 'https://github.com/ReactiveX/RxPHP/blob/master/demo';
    public $demoCode;
    public $demoOutput;
    public $path;

    private function __construct($path, $demoCode, $demoOutput) {
        $this->path = $path;
        $this->demoCode = $demoCode;
        $this->demoOutput = $demoOutput;
    }

    public function getURL() {
        return self::BASE_DEMO_URL . '/' . $this->path;
    }

    public static function fromPath($path) {
        $codePath = __DIR__ . '/../demo/' . $path;
        $outputPath = __DIR__ . '/../demo/' . $path . '.expect';

        assert(file_exists($codePath), "code does not exist $codePath");
        assert(file_exists($outputPath), "output does not exist $outputPath");

        return new Demo(
            $path,
            file_get_contents($codePath),
            trim(file_get_contents($outputPath))
        );
    }
}

function extract_doc_annotation($comment, $annotation) {
    $values = extract_doc_annotations($comment, $annotation);
    assert(count($values) === 1, "Expected only one value for $annotation");
    return $values[0];
}

function extract_doc_annotations($comment, $annotation) {
    $values = [];
    foreach(explode("\n", $comment) as $line) {
        if (!Str::startsWith(trim($line), '* ' . $annotation)) {
            continue;
        }

        $value = Str::substringAfter($line, $annotation);
        if ($value !== '') {
            $values[] = trim($value);
        }
    }
    return $values;
}

function extract_doc_description($docComment) {
    $rawDescription = Str::substringUntil(
        Str::substringUntil($docComment, '@return'),
        '@param'
    );

    $lines = explode("\n", $rawDescription);
    array_shift($lines); // drop /*

    $cleaned = array_map(
        function($line) { return trim(str_replace('*', '', $line)); },
        $lines
    );

    return trim(implode(' ', $cleaned));
}

function has_documentation_tag(\ReflectionMethod $method) {
    return Str::contains($method->getDocComment(), '@operator')
        || Str::contains($method->getDocComment(), '@observable');
}

function load_all_docs() {
    $observable = new \ReflectionClass('Rx\Observable');

    $possibleMethods = $observable
        ->getMethods(ReflectionMethod::IS_STATIC|ReflectionMethod::IS_PUBLIC);

    $taggedMethods = array_filter($possibleMethods, 'has_documentation_tag');

    $documentedMethods = [];
    foreach ($taggedMethods as $taggedMethod) {
        $documentedMethods[] = DocumentedMethod::fromReflectionMethod(
            $taggedMethod
        );
    }

    return $documentedMethods;
}
