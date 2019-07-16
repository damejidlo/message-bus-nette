<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\Integration;

require_once __DIR__ . '/../bootstrap.php';

use Damejidlo\CommandBus\DI\NetteContainerCommandHandlerProvider;
use Damejidlo\EventBus\DI\NetteContainerEventSubscriberProvider;
use Damejidlo\MessageBus\Handling\HandlerType;
use Damejidlo\MessageBus\Handling\Implementation\ArrayMapHandlerTypesResolver;
use Damejidlo\MessageBus\Handling\MessageType;
use DamejidloTests\DjTestCase;
use DamejidloTests\Integration\Fixtures\CreateInvoiceOnOrderPlaced;
use DamejidloTests\Integration\Fixtures\NotifyCustomerOnOrderPlaced;
use DamejidloTests\Integration\Fixtures\OrderPlacedEvent;
use DamejidloTests\Integration\Fixtures\PlaceOrderCommand;
use DamejidloTests\Integration\Fixtures\PlaceOrderHandler;
use Nette\Configurator;
use Nette\DI\Container;
use Tester\Assert;



class IntegrationTest extends DjTestCase
{

	private const FIXTURES_DIRECTORY = __DIR__ . '/Fixtures';

	/**
	 * @var Container
	 */
	private $container;



	protected function setUp() : void
	{
		parent::setUp();

		$this->compileContainer();
	}



	private function compileContainer() : void
	{
		$configurator = new Configurator();
		$configurator->setTempDirectory(self::FIXTURES_DIRECTORY);
		$configurator->setDebugMode(TRUE);
		$configurator->addConfig(self::FIXTURES_DIRECTORY . '/config.neon');

		$this->container = $configurator->createContainer();
	}



	public function testThatExtensionBuildsCommandHandlerTypesResolver() : void
	{
		/** @var ArrayMapHandlerTypesResolver $resolver */
		$resolver = $this->container->getService('commandBus.commandHandlerResolver');

		$command = new PlaceOrderCommand();
		Assert::equal(
			HandlerType::fromString(PlaceOrderHandler::class),
			$resolver->resolve(MessageType::fromMessage($command))->getOne()
		);
	}



	public function testThatExtensionBuildsCommandHandlerProvider() : void
	{
		/** @var NetteContainerCommandHandlerProvider $provider */
		$provider = $this->container->getService('commandBus.commandHandlerProvider');
		$providedHandler = $provider->get(HandlerType::fromString(PlaceOrderHandler::class));

		Assert::type(PlaceOrderHandler::class, $providedHandler);
	}



	public function testThatExtensionBuildsEventSubscriberTypesResolver() : void
	{
		/** @var ArrayMapHandlerTypesResolver $resolver */
		$resolver = $this->container->getService('eventBus.eventSubscribersResolver');

		$event = new OrderPlacedEvent();

		$subscribers = $resolver->resolve(MessageType::fromMessage($event))->toArray();

		$subscribersAsString = array_map(
			function (HandlerType $handlerType) : string {
				return $handlerType->toString();
			},
			$subscribers
		);

		sort($subscribersAsString);

		Assert::equal(
			[
				CreateInvoiceOnOrderPlaced::class,
				NotifyCustomerOnOrderPlaced::class,
			],
			$subscribersAsString
		);
	}



	public function testThatExtensionBuildsEventSubscribersProvider() : void
	{
		/** @var NetteContainerEventSubscriberProvider $provider */
		$provider = $this->container->getService('eventBus.eventSubscriberProvider');
		$providedSubscriber = $provider->get(HandlerType::fromString(NotifyCustomerOnOrderPlaced::class));

		Assert::type(NotifyCustomerOnOrderPlaced::class, $providedSubscriber);
	}

}



(new IntegrationTest())->run();
