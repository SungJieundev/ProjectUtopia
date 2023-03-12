<?php

/*
 * Auto-generated by libasynql-fx
 * Created from mysql.sql
 */

declare(strict_types=1);

namespace alvin0319\Warn;

use Generator;
use poggit\libasynql\DataConnector;
use SOFe\AwaitGenerator\Await;

final readonly class Database{
	public function __construct(private DataConnector $conn){ }

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:18
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, int>
	 */
	public function addWarn(string $name, string $reason, string $time, int $amount,) : Generator{
		$this->conn->executeInsert("add_warn", ["name" => $name, "reason" => $reason, "time" => $time, "amount" => $amount,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:27
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, list<array<string, mixed>>>
	 */
	public function getWarn(string $name, int $index,) : Generator{
		$this->conn->executeSelect("get_warn", ["name" => $name, "index" => $index,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:32
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, list<array<string, mixed>>>
	 */
	public function getWarnByTime(string $name, string $time,) : Generator{
		$this->conn->executeSelect("get_warn_by_time", ["name" => $name, "time" => $time,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:22
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, list<array<string, mixed>>>
	 */
	public function getWarns(string $name,) : Generator{
		$this->conn->executeSelect("get_warns", ["name" => $name,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:11
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, int>
	 */
	public function init() : Generator{
		$this->conn->executeChange("init", [], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:37
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, int>
	 */
	public function removeWarn(string $name, int $index,) : Generator{
		$this->conn->executeChange("remove_warn", ["name" => $name, "index" => $index,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:41
	 *
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, int>
	 */
	public function removeWarns(string $name,) : Generator{
		$this->conn->executeChange("remove_warns", ["name" => $name,], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}

	/**
	 * <h4>Declared in:</h4>
	 * - resources/mysql.sql:44
	 * @return Generator<mixed, 'all'|'once'|'race'|'reject'|'resolve'|array{'resolve'}|Generator<mixed, mixed, mixed, mixed>|null, mixed, list<array<string, mixed>>>
	 */
	public function warns() : Generator{
		$this->conn->executeSelect("warns", [], yield Await::RESOLVE, yield Await::REJECT);
		return yield Await::ONCE;
	}
}
