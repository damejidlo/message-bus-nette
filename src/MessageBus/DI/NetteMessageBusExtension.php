<?php
declare(strict_types = 1);

namespace Damejidlo\MessageBus\DI;

use Damejidlo\CommandBus\DI\CommandHandlerValidator;
use Damejidlo\CommandBus\DI\CommandTypeExtractor;
use Damejidlo\CommandBus\ICommandHandler;
use Damejidlo\EventBus\DI\EventSubscriberValidator;
use Damejidlo\EventBus\DI\EventTypeExtractor;
use Damejidlo\EventBus\IEventSubscriber;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteMessageBusExtension extends CompilerExtension
{

	private const RESOLVER_SERVICE_NAME = 'handlerResolver';
	private const PROVIDER_SERVICE_NAME = 'handlerProvider';



	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix(self::RESOLVER_SERVICE_NAME))
			->setClass(ArrayMapHandlerTypesResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix(self::PROVIDER_SERVICE_NAME))
			->setType(NetteContainerHandlerProvider::class);
	}



	public function beforeCompile() : void
	{
		$handlerResolverDefinition = $this->getHandlerResolverDefinition();

		$handlerTypesByMessageType = $this->findAndValidateCommandHandlers() + $this->findAndValidateEventSubscribers();

		$handlerResolverDefinition->setArguments([
			'handlerTypesByMessageType' => $handlerTypesByMessageType,
		]);
	}



	/**
	 * @return string[][]
	 */
	private function findAndValidateCommandHandlers() : array
	{
		$containerBuilder = $this->getContainerBuilder();

		$handlerValidator = new CommandHandlerValidator();
		$commandTypeExtractor = new CommandTypeExtractor();

		$handlerTypesByCommandType = [];

		foreach ($containerBuilder->findByType(ICommandHandler::class) as $handlerServiceDefinition) {
			$handlerType = $handlerServiceDefinition->getType();
			if ($handlerType === NULL) {
				throw new \LogicException('Type of handler service type must be defined in this context.');
			}

			$handlerValidator->validate($handlerType);

			$commandType = $commandTypeExtractor->extract($handlerType);

			$handlerTypesByCommandType[$commandType][] = $handlerType;
		}

		return $handlerTypesByCommandType;
	}



	/**
	 * @return string[][]
	 */
	private function findAndValidateEventSubscribers() : array
	{
		$containerBuilder = $this->getContainerBuilder();

		$subscriberValidator = new EventSubscriberValidator();
		$eventTypeExtractor = new EventTypeExtractor();

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

		return $subscriberTypesByEventType;
	}



	/**
	 * @return ServiceDefinition
	 *
	 * @throws MissingServiceException
	 * @throws ServiceCreationException
	 */
	private function getHandlerResolverDefinition() : ServiceDefinition
	{
		return $this->getContainerBuilder()->getDefinition($this->prefix(self::RESOLVER_SERVICE_NAME));
	}

}
