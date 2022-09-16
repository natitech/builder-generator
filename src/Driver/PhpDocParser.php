<?php

namespace Nati\BuilderGenerator\Driver;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser as PhpStanDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

final class PhpDocParser
{
    private PhpStanDocParser $parser;

    private Lexer $lexer;

    public function __construct()
    {
        $this->parser = new PhpStanDocParser(new TypeParser(), new ConstExprParser());
        $this->lexer  = new Lexer();
    }

    public function getType(string $phpDoc): ?string
    {
        try {
            $docNode = $this->parser->parse(new TokenIterator($this->lexer->tokenize($phpDoc)));
        } catch (\Exception $e) {
            return null;
        }

        foreach ($docNode->children as $child) {
            if (isset($child->value->type)) {
                return (string)$child->value->type;
            }

            if (isset($child->name) && strpos($child->name, '@ORM') !== false) {
                $ormTypePos = strpos($child->value, 'type="');
                if ($ormTypePos !== false) {
                    $ormTypePosStart = $ormTypePos + 6;

                    return $this->filterORMType(
                        substr(
                            $child->value,
                            $ormTypePosStart,
                            strpos($child->value, '"', $ormTypePosStart) - $ormTypePosStart
                        )
                    );
                }

                return 'string';
            }
        }

        return null;
    }

    private function filterORMType(string $ormType): ?string
    {
        return in_array($ormType, ['string', 'float', 'boolean', 'integer', 'datetime', 'date'], true) ?
            $ormType :
            null;
    }
}
