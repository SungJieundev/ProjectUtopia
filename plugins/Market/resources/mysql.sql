-- #!mysql
-- # { init
CREATE TABLE IF NOT EXISTS market (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    item MEDIUMTEXT NOT NULL,
    buyPrice INT NOT NULL,
    sellPrice INT NOT NULL,
    currency VARCHAR(30) NOT NULL
)
-- # }
-- # { create_market
-- #   item string
-- #   buyPrice int
-- #   sellPrice int
-- #   currency string
INSERT INTO market (item, buyPrice, sellPrice, currency) VALUES (:item, :buyPrice, :sellPrice, :currency)
-- # }
-- # { remove_market
-- #   id int
DELETE FROM market WHERE id = :id
-- # }
-- # { get_market
-- #   id int
SELECT * FROM market WHERE id = :id
-- # }
-- # { get_market_by_item
-- #   item string
SELECT * FROM market WHERE item = :item
-- # }
-- # { get_markets
SELECT * FROM market
-- # }
-- # { update_market
-- #   id int
-- #   item string
-- #   buyPrice int
-- #   sellPrice int
-- #   currency string
UPDATE market SET item = :item, buyPrice = :buyPrice, sellPrice = :sellPrice, currency = :currency WHERE id = :id
-- # }