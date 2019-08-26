<?php

namespace Nati\BuilderGenerator;

final class Classloader
{
    public function getFQN($fileContent): string
    {
        $namespace            = null;
        $class                = null;
        $nextTokenIsNamespace = false;
        $nextTokenIsClass     = false;

        foreach (token_get_all($fileContent) as $token) {
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $nextTokenIsNamespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $nextTokenIsClass = true;
            }

            if ($nextTokenIsNamespace) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $nextTokenIsNamespace = false;
                }
            }

            if ($nextTokenIsClass && is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];

                break;
            }
        }

        if ($class === null) {
            throw new \InvalidArgumentException('Not a class');
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }

    public function loadClass($classFilePath): void
    {
        /** @noinspection PhpIncludeInspection */
        require_once $classFilePath;
    }
}
