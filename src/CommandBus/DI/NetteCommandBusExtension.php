<?php
declare(strict_types = 1);

namespace Damejidlo\CommandBus\DI;

use Damejidlo\CommandBus\ICommandHandler;
use Damejidlo\CommandBus\Implementation\CommandHandlerResolver;
use Nette\DI\CompilerExtension;
use Nette\DI\MissingServiceException;
use Nette\DI\ServiceCreationException;
use Nette\DI\ServiceDefinition;



class NetteCommandBusExtension extends CompilerExtension
{

	public function loadConfiguration() : void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('commandHandlerResolver'))
			->setClass(CommandHandlerResolver::class);

		$this->getContainerBuilder()->addDefinition($this->prefix('commandHandlerProvider'))
			->setType(NetteContainerCommandHandlerProvider::class);
	}



	public function beforeCompile() : void
	{
		$containerBuilder = $this->getContainerBuilder();

		$handlerValidator = new CommandHandlerValidator();
		$commandTypeExtractor = new CommandTypeExtractor();

		$commandHandlerResolverDefinition = $this->getCommandHandlerResolverDefinition();

		foreach ($containerBuilder->findByType(ICommandHandler::class) as $handlerServiceDefinition) {
			$handlerType = $handlerServiceDefinition->getType();
			if ($handlerType === NULL) {
				throw new \LogicException('Type of handler service type must be defined in this context.');
			}

			$handlerValidator->validate($handlerType);

			$commandType = $commandTypeExtractor->extract($handlerType);

			$commandHandlerResolverDefinition->addSetup('registerHandler', [
				'commandType' => $commandType,
				'handlerType' => $handlerType,
			]);
		}
	}



	/**
	 * @return ServiceDefinition
	 *
	 * @throws MissingServiceException
	 * @throws ServiceCreationException
	 */
	private function getCommandHandlerResolverDefinition() : ServiceDefinition
	{
		return $this->getContainerBuilder()->getDefinitionByType(CommandHandlerResolver::class);
	}

}
