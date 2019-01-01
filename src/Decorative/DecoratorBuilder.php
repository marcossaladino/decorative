<?php

namespace Decorative;

use Closure;

/**
 *
 * @author msaladino
 */
class DecoratorBuilder
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
     */
    public function __construct()
    {
        $this->instance = null;
        $this->methods = [];
        $this->before = function () {
        };
        $this->around = function ($call) {
            $call->execute();
        };
        $this->after = function () {
        };
    }

    /**
     * @param object $instance
     * @return Decorator
     */
    public function decorate(object $instance): DecoratorBuilder
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * @param array $methods
     * @return Decorator
     */
    public function methods(array $methods): DecoratorBuilder
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @param Closure $before
     * @return Decorator
     */
    public function before(Closure $before): DecoratorBuilder
    {
        $this->before = $before;
        return $this;
    }

    /**
     * @param Closure $around
     * @return Decorator
     */
    public function around(Closure $around): DecoratorBuilder
    {
        $this->around = $around;
        return $this;
    }

    /**
     * @param Closure $after
     * @return Decorator
     */
    public function after(Closure $after): DecoratorBuilder
    {
        $this->after = $after;
        return $this;
    }

    /**
     * @return \Decorative\Decorator
     */
    public function build(): Decorator
    {
        return new Decorator(
            $this->instance,
            $this->methods,
            $this->before,
            $this->around,
            $this->after
        );
    }
}
