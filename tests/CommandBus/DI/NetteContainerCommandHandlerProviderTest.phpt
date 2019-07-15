<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\CommandBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\CommandBus\DI\NetteContainerCommandHandlerProvider;
use Damejidlo\CommandBus\ICommandHandler;
use Damejidlo\MessageBus\Handling\HandlerCannotBeProvidedException;
use Damejidlo\MessageBus\Handling\HandlerType;
use DamejidloTests\DjTestCase;
use Mockery;
use Mockery\MockInterface;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Tester\Assert;



class NetteContainerCommandHandlerProviderTest extends DjTestCase
{

	public function testSuccess() : void
	{
		$handler = $this->mockCommandHandler();
		$handlerType = HandlerType::fromHandler($handler);

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($handlerType->toString())->andReturn($handler);

		$provider = new NetteContainerCommandHandlerProvider($container);

		Assert::same($handler, $provider->get($handlerType));
	}



	public function testFailWhenRegisteredHandlerServiceNotFound() : void
	{
		$handlerType = HandlerType::fromString('FooHandler');

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($handlerType->toString())->andThrow(MissingServiceException::class);

		$provider = new NetteContainerCommandHandlerProvider($container);

		Assert::exception(function () use ($provider, $handlerType) : void {
			$provider->get($handlerType);
		}, HandlerCannotBeProvidedException::class);
	}



	/**
	 * @return ICommandHandler|MockInterface
	 */
	private function mockCommandHandler() : ICommandHandler
	{
		$mock = Mockery::mock(ICommandHandler::class);

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

(new NetteContainerCommandHandlerProviderTest())->run();
