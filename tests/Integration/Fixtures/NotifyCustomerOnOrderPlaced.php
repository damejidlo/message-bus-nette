<?php declare(strict_types = 1);

namespace DamejidloTests\Integration\Fixtures;

use Damejidlo\MessageBus\Events\IEventSubscriber;



final class NotifyCustomerOnOrderPlaced implements IEventSubscriber
{

	public function handle(OrderPlacedEvent $event) : void
	{
	}

}
