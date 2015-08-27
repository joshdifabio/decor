<?php
namespace Decor\Around;

use Decor\Invoker;

final class Param
{
    private $name;
    private $subjectParam;
    private $decorationParam;
    private $primaryParam;

    /**
     * @param string $name
     */
    public function __construct(
        $name,
        \ReflectionParameter $subjectParam = null,
        \ReflectionParameter $decorationParam = null
    ) {
        if (null === $subjectParam && null === $decorationParam) {
            throw new \LogicException;
        }

        $this->name = $name;
        $this->subjectParam = $subjectParam;
        $this->decorationParam = $decorationParam;
        $this->primaryParam = $subjectParam ?: $decorationParam;
    }

    /**
     * @param \ReflectionParameter[] $subjectParams
     * @param \ReflectionParameter[] $decorationParams
     * @return self[]
     */
    public static function createFromArrays(array $subjectParams, array $decorationParams)
    {
        $reflections = [];

        foreach ($subjectParams as $subjectParam) {
            $reflections[$subjectParam->getName()] = [$subjectParam];
        }

        foreach ($decorationParams as $decorationParam) {
            $reflections[$decorationParam->getName()][1] = $decorationParam;
        }

        $paramNames = array_keys($reflections);
        $params = array_map(
            function ($name, array $reflections) {
                $subjectParam = isset($reflections[0]) ? $reflections[0] : null;
                $decorationParam = isset($reflections[1]) ? $reflections[1] : null;
                return new self($name, $subjectParam, $decorationParam);
            },
            $paramNames,
            $reflections
        );

        return array_combine($paramNames, $params);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhpCode()
    {
        $result = preg_match(
            '{^Parameter #\d+ \[ <(required|optional)> (?<php_code>.+) ]\s*$}',
            (string)$this->primaryParam,
            $matches
        );

        if (!$result) {
            throw new \RuntimeException('Failed to get param PHP code using reflection.');
        }

        return $matches['php_code'];
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->primaryParam->isOptional();
    }

    /**
     * @return bool
     */
    public function isInvokerPlaceholder()
    {
        if (null !== $this->subjectParam) {
            return false;
        }

        $paramClass = $this->decorationParam->getClass();

        if (null === $paramClass) {
            return false;
        }

        return Invoker::class === $paramClass->getName();
    }

    /**
     * @return null|\ReflectionParameter
     */
    public function getDecorationParam()
    {
        return $this->decorationParam;
    }
}
