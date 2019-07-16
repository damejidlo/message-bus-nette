<?php
declare(strict_types = 1);

namespace Damejidlo\CommandBus\DI;

use Damejidlo\CommandBus\ICommandHandler;
use Damejidlo\MessageBus\DI\NetteContainerHandlerProvider;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteCommandBusExtension extends CompilerExtension
{

	private const RESOLVER_SERVICE_NAME = 'commandHandlerResolver';
	private const PROVIDER_SERVICE_NAME = 'commandHandlerProvider';



	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix(self::RESOLVER_SERVICE_NAME))
			->setClass(ArrayMapHandlerTypesResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix(self::PROVIDER_SERVICE_NAME))
			->setType(NetteContainerHandlerProvider::class);
	}



	public function beforeCompile() : void
	{
		$containerBuilder = $this->getContainerBuilder();

		$handlerValidator = new CommandHandlerValidator();
		$commandTypeExtractor = new CommandTypeExtractor();

		$commandHandlerResolverDefinition = $this->getCommandHandlerResolverDefinition();

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

		$commandHandlerResolverDefinition->setArguments([
			'handlerTypesByMessageType' => $handlerTypesByCommandType,
		]);
	}



	/**
	 * @return ServiceDefinition
	 *
	 * @throws MissingServiceException
	 * @throws ServiceCreationException
	 */
	private function getCommandHandlerResolverDefinition() : ServiceDefinition
	{
		return $this->getContainerBuilder()->getDefinition($this->prefix(self::RESOLVER_SERVICE_NAME));
	}

}
