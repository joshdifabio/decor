<?php
namespace Decor;

final class Invoker
{
    private $subject;
    private $arguments;

    public function __construct(callable $subject, array $arguments)
    {
        $this->subject = $subject;
        $this->arguments = $arguments;
    }

    public function __invoke(array $argsToOverride = [])
    {
        $arguments = array_merge($this->arguments, $argsToOverride);

        return call_user_func_array($this->subject, $arguments);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getArgumentValue($name)
    {
        if (!$this->hasArgument($name)) {
            throw new \LogicException("The subject has no argument named $name.");
        }

        return $this->arguments[$name];
    }
}
