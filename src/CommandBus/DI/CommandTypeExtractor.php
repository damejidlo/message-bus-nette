<?php
declare(strict_types = 1);

namespace Damejidlo\CommandBus\DI;

use Nette\SmartObject;



class CommandTypeExtractor
{

	use SmartObject;



	/**
	 * @param string $handlerServiceClass
	 * @return string command type
	 */
	public function extract(string $handlerServiceClass) : string
	{
		$reflection = new \ReflectionClass($handlerServiceClass);
		$handleMethod = $reflection->getMethod('handle');

		$handleMethodParameters = $handleMethod->getParameters();
		$handleMethodParameter = reset($handleMethodParameters);

		return $handleMethodParameter->getType()->getName();
	}

}
