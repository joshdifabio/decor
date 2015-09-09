<?php
namespace Decor;

final class Invoker
{
    private $subject;
    private $params;
    private $arguments;

    /**
     * @param \ReflectionParameter[] $params
     */
    public function __construct(callable $subject, array $params, array $arguments)
    {
        $this->subject = $subject;
        $this->params = $params;
        $this->arguments = $arguments;
    }

    public function __invoke(array $argsToOverride = [])
    {
        $arguments = array_merge($this->arguments, $argsToOverride);

        return call_user_func_array($this->subject, $arguments);
    }

    /**
     * Checks whether the subject has a parameter with the specified name
     *
     * @param string $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * @param $name
     * @return \ReflectionParameter
     */
    public function getParameter($name)
    {
        foreach ($this->params as $param) {
            if ($name === $param->getName()) {
                return $param;
            }
        }

        throw new \LogicException("The subject has no parameter named $name.");
    }

    /**
     * Returns the argument provided for the specified parameter
     *
     * @param string $paramName
     * @return mixed
     */
    public function getArgument($paramName)
    {
        if (!$this->hasParameter($paramName)) {
            throw new \LogicException("The subject has no parameter named $paramName.");
        }

        return $this->arguments[$paramName];
    }
}
