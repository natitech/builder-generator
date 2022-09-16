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

        $content = file_get_contents($filePath);

        if (!$content) {
            throw new \InvalidArgumentException('Cant read ' . $filePath);
        }

        return $content;
    }

    public function writeNear(string $filePath, string $suffix, string $content): string
    {
        $newFilePath = $this->makeNewFilePath($this->guardExists($filePath), $suffix);

        if (!file_put_contents($newFilePath, $content)) {
            throw new \InvalidArgumentException('Cant write to ' . $newFilePath);
        }

        return $newFilePath;
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
