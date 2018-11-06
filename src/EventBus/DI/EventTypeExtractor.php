<?php
declare(strict_types = 1);

namespace Damejidlo\EventBus\DI;

use Nette\SmartObject;



class EventTypeExtractor
{

	use SmartObject;



	/**
	 * @param string $subscriberServiceClass
	 * @return string event type
	 */
	public function extract(string $subscriberServiceClass) : string
	{
		$reflection = new \ReflectionClass($subscriberServiceClass);
		$handleMethod = $reflection->getMethod('handle');

		$handleMethodParameters = $handleMethod->getParameters();
		$handleMethodParameter = reset($handleMethodParameters);

		return $handleMethodParameter->getType()->__toString();
	}

}
