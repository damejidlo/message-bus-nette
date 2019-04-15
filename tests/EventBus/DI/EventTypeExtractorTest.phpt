<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\EventBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\EventBus\DI\EventTypeExtractor;
use Damejidlo\EventBus\IDomainEvent;
use Damejidlo\EventBus\IEventSubscriber;
use DamejidloTests\DjTestCase;
use DamejidloTests\EmptyToArrayTrait;
use Tester\Assert;



class EventTypeExtractorTest extends DjTestCase
{

	public function testExtract() : void
	{
		$extractor = new EventTypeExtractor();

		Assert::same(SomeEvent::class, $extractor->extract(SomeSubscriber::class));
	}

}



class SomeEvent implements IDomainEvent
{

	use EmptyToArrayTrait;

}



class SomeSubscriber implements IEventSubscriber
{

	/**
	 * @param SomeEvent $event
	 */
	public function handle(SomeEvent $event) : void
	{
	}

}


(new EventTypeExtractorTest())->run();
