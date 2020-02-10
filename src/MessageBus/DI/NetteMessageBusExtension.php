<?php
declare(strict_types = 1);

namespace Damejidlo\MessageBus\DI;

use Damejidlo\MessageBus\Handling\HandlerType;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Damejidlo\MessageBus\IMessageHandler;
use Damejidlo\MessageBus\StaticAnalysis\MessageHandlerValidatorFactory;
use Damejidlo\MessageBus\StaticAnalysis\MessageTypeExtractor;
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

		$handlerTypesByMessageType = $this->findAndValidateHandlers();

		$handlerResolverDefinition->setArguments([
			'handlerTypesByMessageType' => $handlerTypesByMessageType,
		]);
	}



	/**
	 * @return string[][]
	 */
	private function findAndValidateHandlers() : array
	{
		$containerBuilder = $this->getContainerBuilder();

		$handlerValidator = MessageHandlerValidatorFactory::createDefault();
		$messageTypeExtractor = new MessageTypeExtractor();

		$handlerTypesByCommandType = [];

		foreach ($containerBuilder->findByType(IMessageHandler::class) as $handlerServiceDefinition) {
			$handlerTypeString = $handlerServiceDefinition->getType();
			if ($handlerTypeString === NULL) {
				throw new \LogicException('Type of handler service type must be defined in this context.');
			}

			$handlerType = HandlerType::fromString($handlerTypeString);
			$handlerValidator->validate($handlerType);

			$commandType = $messageTypeExtractor->extract($handlerType, 'handle');

			$handlerTypesByCommandType[$commandType->toString()][] = $handlerType->toString();
		}

		return $handlerTypesByCommandType;
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
