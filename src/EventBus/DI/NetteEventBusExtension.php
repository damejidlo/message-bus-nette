<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\EventBus\Implementation\EventSubscribersResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteEventBusExtension extends CompilerExtension
{

	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('eventSubscribersResolver'))
			->setType(EventSubscribersResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix('eventSubscriberProvider'))
			->setType(NetteContainerEventSubscriberProvider::class);
	}



	public function beforeCompile() : void
	{
		$containerBuilder = $this->getContainerBuilder();

		$subscriberValidator = new EventSubscriberValidator();
		$eventTypeExtractor = new EventTypeExtractor();

		$eventSubscribersResolverDefinition = $this->getEventSubscribersResolverDefinition();

		foreach ($containerBuilder->findByType(IEventSubscriber::class) as $subscriberServiceDefinition) {
			$subscriberType = $subscriberServiceDefinition->getType();
			$subscriberValidator->validate($subscriberType);

			$eventType = $eventTypeExtractor->extract($subscriberType);

			$eventSubscribersResolverDefinition->addSetup('registerSubscriber', [
				'eventType' => $eventType,
				'subscriberType' => $subscriberType,
			]);
		}
	}



	/**
	 * @return ServiceDefinition
	 *
	 * @throws MissingServiceException
	 * @throws ServiceCreationException
	 */
	private function getEventSubscribersResolverDefinition() : ServiceDefinition
	{
		return $this->getContainerBuilder()->getDefinitionByType(EventSubscribersResolver::class);
	}

}
