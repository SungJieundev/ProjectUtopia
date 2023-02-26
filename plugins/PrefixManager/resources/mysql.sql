-- #!sqlite
-- #  { prefixmanager
-- #    { init
CREATE TABLE IF NOT EXISTS prefix (name VARCHAR(30) NOT NULL PRIMARY KEY, nickname VARCHAR(8) NOT NULL, selectedPrefix INT NOT NULL, prefixes TEXT NOT NULL, syncBlocked TINYINT NOT NULL);
-- #    }
-- #    { create
-- #      :name string
-- #      :nickname string
-- #      :selectedPrefix int
-- #      :prefixes string
-- #      :syncBlocked int
INSERT INTO prefix (name, nickname, selectedPrefix, prefixes, syncBlocked) VALUES (:name, :nickname, :selectedPrefix, :prefixes, :syncBlocked)
-- #    }
-- #    { update
-- #      :name string
-- #      :nickname string
-- #      :selectedPrefix int
-- #      :prefixes string
-- #      :syncBlocked int
UPDATE prefix SET nickname = :nickname, selectedPrefix = :selectedPrefix, prefixes = :prefixes, syncBlocked = :syncBlocked WHERE name = :name
-- #    }
-- #    { get
-- #      :name string
SELECT * FROM prefix WHERE name = :name
-- #    }
-- #    { update_state
-- #      :name string
-- #      :syncBlocked int
UPDATE prefix SET syncBlocked = :syncBlocked WHERE name = :name
-- #    }
-- #  }