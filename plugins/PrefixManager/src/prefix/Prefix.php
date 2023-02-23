<?php

declare(strict_types=1);

namespace alvin0319\PrefixManager\prefix;

final readonly class Prefix{

	public function __construct(
		public int $id,
		public string $prefix
	){}
}
