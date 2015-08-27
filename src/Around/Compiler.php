<?php
namespace Decor\Around;

use Decor\Invoker;

final class Compiler
{
    private $subject;
    private $decoration;
    private $subjectParams;
    private $decorationParams;
    private $params;
    private $invoker;

    public function __construct(callable $subject, callable $decoration)
    {
        $this->subject = $subject;
        $this->decoration = $decoration;
        $this->subjectParams = $this->getFunctionParams($subject);
        $this->decorationParams = $this->getFunctionParams($decoration);
        $this->params = Param::createFromArrays($this->subjectParams, $this->decorationParams);
    }

    public function compile()
    {
        $invokerClassName = '\\' . Invoker::class;
        $subjectParamsArrayCode = $this->getSubjectParamsArrayCode();
        $decorationParamsCode = $this->getParamsCodeForDecoratorInvokation() ?: 'null';

        $code = <<<PHP_CODE
\$decorated = function ({$this->getParamsCodeForDeclaration()}) {
    \$this->invoker = new $invokerClassName(\$this->subject, $subjectParamsArrayCode);
    return call_user_func(\$this->decoration, $decorationParamsCode);
};
PHP_CODE;
        eval($code);

        return $decorated;
    }

    /**
     * @return string
     */
    private function getParamsCodeForDeclaration()
    {
        $params = array_filter($this->params, function (Param $param) {
            return !$param->isInvokerPlaceholder();
        });

        $paramStrings = array_map(function (Param $param) {
            return $param->getPhpCode();
        }, $params);

        return implode(', ', $paramStrings);
    }

    /**
     * @param \ReflectionParameter[]
     * @return string
     */
    private function getSubjectParamsArrayCode()
    {
        $lines = array_map(
            function (\ReflectionParameter $param) {
                $name = $param->getName();
                return "'$name' => \$$name,\n";
            },
            $this->subjectParams
        );

        return '[' . implode('', $lines) . ']';
    }

    private function getParamsCodeForDecoratorInvokation()
    {
        $paramVarNames = array_map(
            function (\ReflectionParameter $reflection) {
                $param = $this->params[$reflection->getName()];
                if (!$param->isInvokerPlaceholder()) {
                    return '$' . $param->getName();
                }
                return '$this->invoker';
            },
            $this->decorationParams
        );

        return implode(', ', $paramVarNames);
    }

    /**
     * @return \ReflectionParameter[]
     */
    private function getFunctionParams(callable $callback)
    {
        if (is_array($callback)) {
            $callbackReflection = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (is_object($callback) && !$callback instanceof \Closure) {
            $callbackReflection = new \ReflectionObject($callback);
            $callbackReflection = $callbackReflection->getMethod('__invoke');
        } else {
            $callbackReflection = new \ReflectionFunction($callback);
        }

        return $callbackReflection->getParameters();
    }
}
