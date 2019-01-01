<?php

use BadMethodCallException;
use Decorative\DecoratorBuilder;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 *
 * @author msaladino
 */
class DecoratorTest extends TestCase
{

    /*
     * @var Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    private function repository()
    {
        $mock = $this
            ->getMockBuilder(stdClass::class)
            ->setMethods(['findOne', 'findAll', 'get'])
            ->getMock();
        $mock
            ->expects($this->any())
            ->method('findOne')
            ->will($this->returnArgument(0));
        $mock
            ->expects($this->any())
            ->method('findAll')
            ->will($this->returnArgument(0));
        $mock->expects($this->any())
            ->method('get')
            ->will(
                $this->throwException(
                    new Exception('Expected exception was thrown')
                )
            );
        return $mock;
    }

    private function cache(): CacheInterface
    {
        return $this
            ->getMockBuilder(CacheInterface::class)
            ->setMethods(
                ['get', 'set', 'delete', 'clear', 'getMultiple', 'setMultiple', 'deleteMultiple', 'has']
            )
            ->getMock();
    }

    private function cacheAlwaysHit(): CacheInterface
    {
        $this->cache
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('Entity'));
        return $this->cache;
    }

    private function cacheAlwaysMiss(): CacheInterface
    {
        $this->cache
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->will($this->returnValue(true));
        return $this->cache;
    }

    /**
     * @before
     */
    public function setUp()
    {
        $this->cache = $this->cache();
        $this->repository = $this->repository();
    }

    /**
     * @test
     */
    public function decoratorShouldInvokeDecoratedMethod()
    {
        $builder = new DecoratorBuilder();
        $decorator = $builder->decorate($this->repository)->build();
        $this->assertSame('Entity', $decorator->findOne('Entity'));
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     */
    public function decoratorShouldThrowExeptionIfMethodDesNotExistInDecorated()
    {
        $builder = new DecoratorBuilder();
        $decorator = $builder->decorate($this->repository)->build();
        $decorator->nonExistingMethod();
    }

    /**
     * @test
     */
    public function decoratorShouldDecorateAllMethods()
    {
        $builder = new DecoratorBuilder();
        $decorator = $builder
            ->decorate($this->repository)
            ->after(
                function ($call) {
                    $call->setResult('Changed');
                }
            )
            ->build();
        $this->assertSame('Changed', $decorator->findOne('NotChanged'));
        $this->assertSame('Changed', $decorator->findAll('NotChanged'));
    }

    /**
     * @test
     */
    public function decoratorShouldDecorateOnlyListedMethods()
    {
        $builder = new DecoratorBuilder();
        $decorator = $builder
            ->decorate($this->repository)
            ->methods(['findOne'])
            ->after(
                function ($call) {
                    $call->setResult('Changed');
                }
            )
            ->build();
        $this->assertSame('Changed', $decorator->findOne('NotChanged'));
        $this->assertSame('NotChanged', $decorator->findAll('NotChanged'));
    }

    /**
     * @test
     */
    public function decoratorShouldCallCacheGetOnCacheHit()
    {
        $cache = $this->cacheAlwaysHit();
        $builder = new DecoratorBuilder();
        $decorator = $builder
            ->decorate($this->repository)
            ->before(
                function ($call) use ($cache) {
                    $call->setEnabled(!$cache->has('key'));
                }
            )
            ->after(
                function ($call) use ($cache) {
                    $call->setResult($cache->get('key'));
                }
            )
            ->build();
        $this->assertSame('Entity', $decorator->findOne('Entity'));
    }

    /**
     * @test
     */
    public function decoratorShouldCallCacheSetOnCacheMiss()
    {
        $cache = $this->cacheAlwaysMiss();
        $builder = new DecoratorBuilder();
        $decorator = $builder
            ->decorate($this->repository)
            ->before(
                function ($call) use ($cache) {
                    $call->setEnabled(!$cache->has('key'));
                }
            )
            ->after(
                function ($call) use ($cache) {
                    $cache->set('key', $call->getResult());
                }
            )
            ->build();
        $this->assertSame('Entity', $decorator->findOne('Entity'));
    }

    /**
     * @test
     */
    public function decoratorShouldCatchExeption()
    {
        $builder = new DecoratorBuilder();
        $decorator = $builder
            ->decorate($this->repository)
            ->methods(['get'])
            ->around(
                function ($call) {
                    try {
                        $call->execute();
                    } catch (Exception $e) {
                    }
                }
            )
            ->build();
        $thrown = false;
        try {
            $decorator->get('identifier');
        } catch (Exception $e) {
            $thrown = !empty($e);
        }
        $this->assertFalse($thrown);
    }
}
