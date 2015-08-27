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
}
