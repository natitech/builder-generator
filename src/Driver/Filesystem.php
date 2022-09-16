<?php

namespace Nati\BuilderGenerator\Driver;

/**
 * @codeCoverageIgnore
 */
final class Filesystem
{
    public function read(string $filePath): string
    {
        $this->guardExists($filePath);

        return file_get_contents($filePath);
    }

    public function writeNear(string $filePath, string $suffix, string $content): void
    {
        file_put_contents($this->makeNewFilePath($this->guardExists($filePath), $suffix), $content);
    }

    private function guardExists(string $filePath): \SplFileInfo
    {
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found');
        }

        return new \SplFileInfo($filePath);
    }

    private function makeNewFilePath(\SplFileInfo $currentFilePath, string $suffix): string
    {
        $ext = $currentFilePath->getExtension();

        return $currentFilePath->getPath() . '/' . $currentFilePath->getBasename('.' . $ext) . $suffix . '.' . $ext;
    }
}
