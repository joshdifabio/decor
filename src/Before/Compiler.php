<?php
namespace Decor\Before;

final class Compiler
{
    private $subject;
    private $decoration;
    private $subjectParams;
    private $decorationParams;
    private $params;

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
        $subjectParamsCode = $this->getParamsCodeForInvokation($this->subjectParams) ?: 'null';
        $decorationParamsCode = $this->getParamsCodeForInvokation($this->decorationParams) ?: 'null';

        $code = <<<PHP_CODE
\$decorated = function ({$this->getParamsCodeForDeclaration()}) {
    call_user_func(\$this->decoration, $decorationParamsCode);
    return call_user_func(\$this->subject, $subjectParamsCode);
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
        $paramStrings = array_map(function (Param $param) {
            return $param->getPhpCode();
        }, $this->params);

        return implode(', ', $paramStrings);
    }

    /**
     * @param \ReflectionParameter[]
     * @return string
     */
    private function getParamsCodeForInvokation($params)
    {
        $paramVarNames = array_map(
            function (\ReflectionParameter $param) {
                return '$' . $param->getName();
            },
            $params
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
