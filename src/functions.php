<?php
namespace Decor;

/**
 * @return \Closure
 */
function before(callable $subject, callable $observer)
{
    $compiler = new \Decor\Before\Compiler($subject, $observer);
    return $compiler->compile();
}

/**
 * @return \Closure
 */
function around(callable $subject, callable $decorator)
{
    $compiler = new \Decor\Around\Compiler($subject, $decorator);
    return $compiler->compile();
}
