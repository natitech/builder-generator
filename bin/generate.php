<?php

require __DIR__ . '/../vendor/autoload.php';

use Nati\BuilderGenerator\FileBuilderGenerator;

try {
    FileBuilderGenerator::create()->generateFrom($argv[1] ?? null);
} catch (Exception $e) {
    echo 'Error while generating builder' . "\n";
    exit(1);
}

echo 'Done !' . "\n";
exit(0);
