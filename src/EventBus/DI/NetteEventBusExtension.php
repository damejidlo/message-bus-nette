<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\MessageBus\DI\NetteContainerHandlerProvider;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteEventBusExtension extends CompilerExtension
{

	private const RESOLVER_SERVICE_NAME = 'eventSubscribersResolver';
	private const PROVIDER_SERVICE_NAME = 'eventSubscriberProvider';



	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix(self::RESOLVER_SERVICE_NAME))
			->setType(ArrayMapHandlerTypesResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix(self::PROVIDER_SERVICE_NAME))
			->setType(NetteContainerHandlerProvider::class);
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
		return $this->getContainerBuilder()->getDefinition($this->prefix(self::RESOLVER_SERVICE_NAME));
	}

}
