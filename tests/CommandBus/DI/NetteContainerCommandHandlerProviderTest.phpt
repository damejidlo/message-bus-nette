<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\CommandBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\CommandBus\CommandHandlerNotFoundException;
use Damejidlo\CommandBus\DI\NetteContainerCommandHandlerProvider;
use Damejidlo\CommandBus\ICommandHandler;
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
		$handlerType = get_class($handler);

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($handlerType)->andReturn($handler);

		$provider = new NetteContainerCommandHandlerProvider($container);

		Assert::same($handler, $provider->getByType($handlerType));
	}



	public function testFailWhenRegisteredHandlerServiceNotFound() : void
	{
		$handlerType = 'FooHandler';

		$container = $this->mockContainer();
		$container->shouldReceive('getByType')->once()->with($handlerType)->andThrow(MissingServiceException::class);

		$provider = new NetteContainerCommandHandlerProvider($container);

		Assert::exception(function () use ($provider, $handlerType) : void {
			$provider->getByType($handlerType);
		}, CommandHandlerNotFoundException::class);
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
