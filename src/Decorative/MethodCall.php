<?php

namespace Decorative;

/**
 *
 * @author msaladino
 */
class MethodCall
{

    /**
     * @var object
     */
    private $instance;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array|null
     */
    private $arguments;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @param object $instance
     * @param string $method
     * @param array|null $arguments
     */
    public function __construct(object $instance, string $method, ?array $arguments)
    {
        $this->instance = $instance;
        $this->method = $method;
        $this->arguments = $arguments;
        $this->context = null;
        $this->enabled = true;
        $this->result = null;
    }

    /**
     * @return object
     */
    public function getInstance(): object
    {
        return $this->instance;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array|null
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $context
     * @return \Decorative\MethodCall
     */
    public function setContext($context): MethodCall
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param bool $enabled
     * @return \Decorative\MethodCall
     */
    public function setEnabled(bool $enabled): MethodCall
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param mixed $result
     * @return \Decorative\MethodCall
     */
    public function setResult($result): MethodCall
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return mixed
     */
    public function execute(): void
    {
        if ($this->enabled) {
            $this->result = call_user_func_array(
                [$this->instance, $this->method],
                $this->arguments
            );
        }
    }
}
