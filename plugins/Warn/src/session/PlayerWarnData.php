<?php

declare(strict_types=1);

namespace alvin0319\Warn\session;

final readonly class PlayerWarnData{

	public function __construct(
		public int $index,
		public string $name,
		public string $reason,
		public string $time,
		public int $amount
	){
	}
}
