<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\EventBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\EventBus\DI\NetteContainerEventSubscriberProvider;
use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\MessageBus\Handling\HandlerCannotBeProvidedException;
use Damejidlo\MessageBus\Handling\HandlerType;
use DamejidloTests\DjTestCase;
use Mockery;
use Mockery\MockInterface;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Tester\Assert;



class NetteContainerEventSubscriberProviderTest extends DjTestCase
{

	public function testSuccess() : void
	{
		$subscriber = $this->mockEventHandler();
		$subscriberType = HandlerType::fromHandler($subscriber);

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($subscriberType->toString())->andReturn($subscriber);

		$provider = new NetteContainerEventSubscriberProvider($container);

		Assert::same($subscriber, $provider->get($subscriberType));
	}



	public function testFailWhenRegisteredSubscriberServiceNotFound() : void
	{
		$subscriberType = HandlerType::fromString('FooSubscriber');

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($subscriberType->toString())->andThrow(MissingServiceException::class);

		$provider = new NetteContainerEventSubscriberProvider($container);

		Assert::exception(function () use ($provider, $subscriberType) : void {
			$provider->get($subscriberType);
		}, HandlerCannotBeProvidedException::class);
	}



	/**
	 * @return IEventSubscriber|MockInterface
	 */
	private function mockEventHandler() : IEventSubscriber
	{
		$mock = Mockery::mock(IEventSubscriber::class);

		return $mock;
	}



	/**
	 * @return Container|MockInterface
	 */
	private function mockContainer() : Container
	{
		$mock = Mockery::mock(Container::class);

		return $mock;
	}

}

(new NetteContainerEventSubscriberProviderTest())->run();
