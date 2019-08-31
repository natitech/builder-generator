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
        file_put_contents($this->makeNewFilePath($this->guardExists($filePath), $suffix), $content);
    }

    private function guardExists($filePath): \SplFileInfo
    {
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found');
        }

        return new \SplFileInfo($filePath);
    }

    private function makeNewFilePath(\SplFileInfo $currentFilePath, $suffix): string
    {
        $ext = $currentFilePath->getExtension();

        return $currentFilePath->getPath() . '/' . $currentFilePath->getBasename('.' . $ext) . $suffix . '.' . $ext;
    }
}
