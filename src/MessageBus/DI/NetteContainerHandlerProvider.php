<?php
declare(strict_types = 1);

namespace Damejidlo\MessageBus\DI;

use Damejidlo\MessageBus\Handling\HandlerCannotBeProvidedException;
use Damejidlo\MessageBus\Handling\HandlerType;
use Damejidlo\MessageBus\Handling\IHandlerProvider;
use Damejidlo\MessageBus\IMessageHandler;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;



final class NetteContainerHandlerProvider implements IHandlerProvider
{

	use SmartObject;

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



	public function get(HandlerType $type) : IMessageHandler
	{
		$typeAsString = $type->toString();

		try {
			/** @var IMessageHandler $handler */
			$handler = $this->container->getByType($typeAsString);

			return $handler;

		} catch (MissingServiceException $e) {
			throw new HandlerCannotBeProvidedException(sprintf('Message handler "%s" not found in DI container.', $typeAsString), 0, $e);
		}
	}

}
