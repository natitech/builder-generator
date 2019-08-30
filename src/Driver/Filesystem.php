<?php

namespace Nati\BuilderGenerator\Driver;

final class Filesystem
{
    public function read($filePath): string
    {
        $this->guardExists($filePath);

        return file_get_contents($filePath);
    }

    public function writeNear($filePath, $suffix, $content): void
    {
        $newFilePath = $this->guardWritable($this->makeNewFilePath($this->guardExists($filePath), $suffix));

        file_put_contents($newFilePath, $content);
    }

    private function guardExists($filePath): \SplFileInfo
    {
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found');
        }

        return new \SplFileInfo($filePath);
    }

    private function guardWritable(string $path): string
    {
        if (!is_writable($path)) {
//            throw new \InvalidArgumentException('Cant write new file');
        }

        return $path;
    }

    private function makeNewFilePath(\SplFileInfo $currentFilePath, $suffix): string
    {
        return $currentFilePath->getPath() . '/' . $currentFilePath->getBasename('.php') . $suffix . '.php';
    }
}
