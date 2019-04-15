<?php
declare(strict_types = 1);

/**
 * @testCase
 */

namespace DamejidloTests\CommandBus\DI;

require_once __DIR__ . '/../../bootstrap.php';

use Damejidlo\CommandBus\DI\CommandTypeExtractor;
use Damejidlo\CommandBus\ICommand;
use Damejidlo\CommandBus\ICommandHandler;
use DamejidloTests\DjTestCase;
use DamejidloTests\EmptyToArrayTrait;
use Tester\Assert;



class CommandTypeExtractorTest extends DjTestCase
{

	public function testExtract() : void
	{
		$extractor = new CommandTypeExtractor();

		Assert::same(SomeCommand::class, $extractor->extract(SomeHandler::class));
	}

}



class SomeCommand implements ICommand
{

	use EmptyToArrayTrait;

}



class SomeHandler implements ICommandHandler
{

	/**
	 * @param SomeCommand $command
	 */
	public function handle(SomeCommand $command) : void
	{
	}

}


(new CommandTypeExtractorTest())->run();
