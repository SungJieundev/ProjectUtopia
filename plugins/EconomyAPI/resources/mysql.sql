-- # !mysql
-- # { economyapi
-- #    { init
CREATE TABLE IF NOT EXISTS money (name VARCHAR(30) NOT NULL, currency VARCHAR(30) NOT NULL, money INT NOT NULL, transactionBlocked TINYINT(1) NOT NULL, PRIMARY KEY (name, currency))
-- #    }
-- #    { create
-- #      :name string
-- #      :currency string
-- #      :defaultMoney int
INSERT IGNORE INTO money (name, currency, money, transactionBlocked) VALUES (:name, :currency, :defaultMoney, 0)
-- #    }
-- #    { get
-- #      :name string
-- #      :currency string
SELECT * FROM money WHERE name = :name AND currency = :currency
-- #    }
-- #    { update
-- #      :name string
-- #      :currency string
-- #      :money int
-- #      :transactionBlocked int
UPDATE money SET transactionBlocked = :transactionBlocked, money = :money WHERE name = :name AND currency = :currency
-- #    }
-- #    { get_all
-- #      :currency string
SELECT * FROM money WHERE currency = :currency
-- #    }
-- #    { update_state
-- #      :name string
-- #      :currency string
-- #      :transactionBlocked int
UPDATE money SET transactionBlocked = :transactionBlocked WHERE name = :name and currency = :currency
-- #    }
-- #    { top
-- #     :currency string
-- #     :page int
SELECT * FROM money WHERE currency = :currency ORDER BY money DESC LIMIT 5 OFFSET :page
-- #    }
-- #    { getRows
-- #     :currency string
SELECT COUNT(*) AS columns FROM money WHERE currency = :currency
-- #    }
-- # }