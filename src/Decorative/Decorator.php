<?php

namespace Decorative;

use BadMethodCallException;
use Closure;

/**
 *
 * @author msaladino
 */
class Decorator
{

    /**
     * @var object
     */
    private $instance;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var Closure
     */
    private $before;

    /**
     * @var Closure
     */
    private $around;

    /**
     * @var Closure
     */
    private $after;

    /**
     *
     * @param object $instance
     * @param array $methods
     * @param Closure $before
     * @param Closure $around
     * @param Closure $after
     */
    public function __construct(object $instance, array $methods, Closure $before, Closure $around, Closure $after)
    {
        $this->instance = $instance;
        $this->methods = $methods;
        $this->before = $before;
        $this->around = $around;
        $this->after = $after;
    }

    /**
     * @param object $instance
     * @param string $method
     * @throws BadMethodCallException
     */
    private function checkMethodExists(object $instance, string $method): void
    {
        if (!method_exists($instance, $method)) {
            throw new BadMethodCallException();
        }
    }

    /**
     * @param \Decorative\MethodCall $methodCall
     * @return bool
     */
    private function match(MethodCall $call): bool
    {
        return in_array($call->getMethod(), $this->methods) || empty($this->methods);
    }

    /**
     * @param string $method
     * @param array|null $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $method, ?array $arguments)
    {
        $this->checkMethodExists($this->instance, $method);
        $call = new MethodCall($this->instance, $method, $arguments);
        if ($this->match($call)) {
            $this->before->__invoke($call);
            $this->around->__invoke($call);
            $this->after->__invoke($call);
        } else {
            $call->execute();
        }
        return $call->getResult();
    }
}
