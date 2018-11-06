<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Damejidlo\EventBus\EventSubscriberNotFoundException;
use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\EventBus\IEventSubscriberProvider;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;



final class NetteContainerEventSubscriberProvider implements IEventSubscriberProvider
{

	/**
	 * @var Container
	 */
	private $container;



	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	public function getByType(string $subscriberType) : IEventSubscriber
	{
		try {
			/** @var IEventSubscriber $subscriber */
			$subscriber = $this->container->getByType($subscriberType);

			return $subscriber;

		} catch (MissingServiceException $e) {
			throw new EventSubscriberNotFoundException(sprintf('Event subscriber "%s" not found in DI container.', $subscriberType), 0, $e);
		}
	}

}
