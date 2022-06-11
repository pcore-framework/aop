<?php

declare(strict_types=1);

namespace PCore\Aop;

use PCore\Aop\Collectors\AspectCollector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\{Closure, FuncCall, MethodCall, Variable};
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_;
use PhpParser\Node\Stmt\{Class_, ClassMethod, Expression, Return_, TraitUse};
use PhpParser\NodeVisitorAbstract;

/**
 * Class ProxyHandlerVisitor
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
class ProxyHandlerVisitor extends NodeVisitorAbstract
{

    public function __construct(protected Metadata $metadata)
    {
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $node->stmts = array_merge(
                [new TraitUse([new Name('\PCore\Aop\ProxyHandler')])],
                $node->stmts
            );
        }
        if ($node instanceof ClassMethod) {
            $methodName = $node->name->toString();
            if ($methodName === '__construct') {
                $this->metadata->hasConstructor = true;
                return;
            }
            if (AspectCollector::getMethodAspects($this->metadata->className, $methodName)) {
                $methodCall = new MethodCall(
                    new Variable(new Name('this')),
                    '__callViaProxy',
                    [
                        new Arg(new Function_()),
                        new Arg(new Closure([
                            'params' => $node->getParams(),
                            'stmts' => $node->stmts,
                        ])),
                        new Arg(new FuncCall(new Name('func_get_args')))
                    ]
                );
                $returnType = $node->getReturnType();
                if ($returnType instanceof Identifier && $returnType->name === 'void') {
                    $node->stmts = [new Expression($methodCall)];
                } else {
                    $node->stmts = [new Return_($methodCall)];
                }
            }
        }
    }

}