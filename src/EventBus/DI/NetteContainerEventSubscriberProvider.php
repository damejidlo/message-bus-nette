<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Damejidlo\MessageBus\Handling\HandlerCannotBeProvidedException;
use Damejidlo\MessageBus\Handling\HandlerType;
use Damejidlo\MessageBus\Handling\IHandlerProvider;
use Damejidlo\MessageBus\IMessageHandler;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;



final class NetteContainerEventSubscriberProvider implements IHandlerProvider
{

	/**
	 * @var Container
	 */
	private $container;



	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	public function get(HandlerType $type) : IMessageHandler
	{
		$typeAsString = $type->toString();

		try {
			/** @var IMessageHandler $subscriber */
			$subscriber = $this->container->getByType($typeAsString);

			return $subscriber;

		} catch (MissingServiceException $e) {
			throw new HandlerCannotBeProvidedException(sprintf('Event subscriber "%s" not found in DI container.', $typeAsString), 0, $e);
		}
	}

}
