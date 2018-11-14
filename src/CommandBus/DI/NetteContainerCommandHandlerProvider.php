<?php
declare(strict_types = 1);

namespace Damejidlo\CommandBus\DI;

use Damejidlo\CommandBus\CommandHandlerNotFoundException;
use Damejidlo\CommandBus\ICommandHandler;
use Damejidlo\CommandBus\ICommandHandlerProvider;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;



final class NetteContainerCommandHandlerProvider implements ICommandHandlerProvider
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @inheritdoc
	 */
	public function getByType(string $handlerType) : ICommandHandler
	{
		try {
			/** @var ICommandHandler $handler */
			$handler = $this->container->getByType($handlerType);

			return $handler;

		} catch (MissingServiceException $e) {
			throw new CommandHandlerNotFoundException(sprintf('Command handler "%s" not found in DI container.', $handlerType), 0, $e);
		}
	}

}
