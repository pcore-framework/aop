<?php

namespace PCore\Aop;

use PhpParser\Node\Stmt\{Class_, Namespace_};
use PhpParser\{NodeTraverser, Parser, ParserFactory};

/**
 * Class AstManager
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
class AstManager
{

    protected Parser $parser;
    protected array $container = [];

    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    public function getNodes(string $realpath)
    {
        if (!isset($this->container[$realpath])) {
            $this->container[$realpath] = $this->parser->parse(file_get_contents($realpath));
        }
        return $this->container[$realpath];
    }

    public function getClassesByRealPath(string $realpath): array
    {
        $classes = [];
        foreach ($this->getNodes($realpath) as $stmt) {
            if ($stmt instanceof Namespace_) {
                $namespace = $stmt->name->toCodeString();
                foreach ($stmt->stmts as $subStmt) {
                    if ($subStmt instanceof Class_) {
                        $classes[] = $namespace . '\\' . $subStmt->name->toString();
                    }
                }
            }
        }
        return $classes;
    }

}