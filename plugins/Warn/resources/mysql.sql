-- #!mysql
-- # { init
CREATE TABLE IF NOT EXISTS warns (
    `index` BIGINT NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    time VARCHAR(10) NOT NULL,
    amount INT NOT NULL,
    PRIMARY KEY (`index`, name)
)
-- # }
-- # { add_warn
-- #   :name string
-- #   :reason string
-- #   :time string
-- #   :amount int
INSERT INTO warns (name, reason, time, amount) VALUES (:name, :reason, :time, :amount)
-- # }
-- # { get_warns
-- #   :name string
SELECT * FROM warns WHERE name = :name
-- # }
-- # { get_warn
-- #   :name string
-- #   :index int
SELECT * FROM warns WHERE name = :name AND `index` = :index
-- # }
-- # { get_warn_by_time
-- #   :name string
-- #   :time string
SELECT * FROM warns WHERE name = :name AND time = :time
-- # }
-- # { remove_warn
-- #   :name string
-- #   :index int
DELETE FROM warns WHERE name = :name AND `index` = :index
-- # }
-- # { remove_warns
-- #   :name string
DELETE FROM warns WHERE name = :name
-- # }
-- # { warns
SELECT * FROM warns
-- # }