<?php

namespace Nati\BuilderGenerator\Driver;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser as PhpStanDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

final class PhpDocParser
{
    private $parser;

    private $lexer;

    public function __construct()
    {
        $this->parser = new PhpStanDocParser(new TypeParser(), new ConstExprParser());
        $this->lexer  = new Lexer();
    }

    public function getType(string $phpDoc)
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
        }

        return null;
    }
}
