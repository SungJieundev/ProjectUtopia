-- #!mysql
-- # { init
CREATE TABLE IF NOT EXISTS prefix (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    prefix VARCHAR(255) NOT NULL
)
-- # }
-- # { init_session
CREATE TABLE IF NOT EXISTS prefix_session (
    name VARCHAR(30) NOT NULL PRIMARY KEY,
    customName VARCHAR(30) NOT NULL,
    prefixes TEXT NOT NULL,
    syncBlocked TINYINT(1) NOT NULL
)
-- # }
-- # { create_prefix
-- #   :prefix string
INSERT INTO prefix (prefix) VALUES (:prefix)
-- # }
-- # { delete_prefix
-- #   :prefix string
DELETE FROM prefix WHERE prefix = :prefix
-- # }
-- # { get_prefix
-- #   :prefix string
SELECT * FROM prefix WHERE prefix = :prefix
-- # }
-- # { get_prefix_by_id
-- #   :id int
SELECT * FROM prefix WHERE id = :id
-- # }
-- # { get_prefixes
SELECT * FROM prefix
-- # }
-- # { create_session
-- #   :name string
-- #   :customName string
-- #   :prefixes string
-- #   :syncBlocked int
    INSERT INTO prefix_session (name, customName, prefixes, syncBlocked) VALUES (:name, :customName, :prefixes, :syncBlocked)
-- # }
-- # { get_session
-- #   :name string
SELECT * FROM prefix_session WHERE name = :name
-- # }
-- # { update_session
-- #   :name string
-- #   :customName string
-- #   :prefixes string
-- #   :syncBlocked int
UPDATE prefix_session SET customName = :customName, prefixes = :prefixes, syncBlocked = :syncBlocked WHERE name = :name
-- # }
-- # { delete_session
-- #   :name string
DELETE FROM prefix_session WHERE name = :name
-- # }
-- # { get_sessions
SELECT * FROM prefix_session
-- # }