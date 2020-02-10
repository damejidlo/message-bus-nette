<?php declare(strict_types = 1);

namespace DamejidloTests\Integration\Fixtures;

use Damejidlo\MessageBus\Commands\ICommandHandler;



final class PlaceOrderHandler implements ICommandHandler
{

	public function handle(PlaceOrderCommand $command) : void
	{
	}

}
