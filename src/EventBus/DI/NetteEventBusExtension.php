<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteEventBusExtension extends CompilerExtension
{

	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('eventSubscribersResolver'))
			->setType(ArrayMapHandlerTypesResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix('eventSubscriberProvider'))
			->setType(NetteContainerEventSubscriberProvider::class);
	}



	public function beforeCompile() : void
	{
		$containerBuilder = $this->getContainerBuilder();

		$subscriberValidator = new EventSubscriberValidator();
		$eventTypeExtractor = new EventTypeExtractor();

		$eventSubscribersResolverDefinition = $this->getEventSubscribersResolverDefinition();

		$subscriberTypesByEventType = [];

		foreach ($containerBuilder->findByType(IEventSubscriber::class) as $subscriberServiceDefinition) {
			$subscriberType = $subscriberServiceDefinition->getType();
			if ($subscriberType === NULL) {
				throw new \LogicException('Type of subscriber service type must be defined in this context.');
			}
			$subscriberValidator->validate($subscriberType);

			$eventType = $eventTypeExtractor->extract($subscriberType);

			$subscriberTypesByEventType[$eventType][] = $subscriberType;
		}

		$eventSubscribersResolverDefinition->setArguments([
			'handlerTypesByMessageType' => $subscriberTypesByEventType,
		]);
	}



	/**
	 * @return ServiceDefinition
	 *
	 * @throws MissingServiceException
	 * @throws ServiceCreationException
	 */
	private function getEventSubscribersResolverDefinition() : ServiceDefinition
	{
		return $this->getContainerBuilder()->getDefinitionByType(ArrayMapHandlerTypesResolver::class);
	}

}
