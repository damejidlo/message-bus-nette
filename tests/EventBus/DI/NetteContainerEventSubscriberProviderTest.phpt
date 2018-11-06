<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\EventBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\EventBus\DI\NetteContainerEventSubscriberProvider;
use Damejidlo\EventBus\EventSubscriberNotFoundException;
use Damejidlo\EventBus\IEventSubscriber;
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
		$subscriberType = get_class($subscriber);

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($subscriberType)->andReturn($subscriber);

		$provider = new NetteContainerEventSubscriberProvider($container);

		Assert::same($subscriber, $provider->getByType($subscriberType));
	}



	public function testFailWhenRegisteredSubscriberServiceNotFound() : void
	{
		$subscriberType = 'FooSubscriber';

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($subscriberType)->andThrow(MissingServiceException::class);

		$provider = new NetteContainerEventSubscriberProvider($container);

		Assert::exception(function () use ($provider, $subscriberType) : void {
			$provider->getByType($subscriberType);
		}, EventSubscriberNotFoundException::class);
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
