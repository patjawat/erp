<?php

$packageDir = __DIR__ . '/../vendor/asyou99/yii2-cart';

if (!is_dir($packageDir)) {
    exit(0);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($packageDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    $contents = file_get_contents($path);

    if ($contents === false) {
        continue;
    }

    $patched = str_replace('yii\\base\\Object', 'yii\\base\\BaseObject', $contents);

    if ($patched !== $contents) {
        file_put_contents($path, $patched);
    }
}
