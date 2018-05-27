CREATE TABLE IF NOT EXISTS `pages` (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `keyword` varchar(255),
  `url` varchar(1000),
  `parsed` INTEGER NOT NULL DEFAULT 0,
  `created_at` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `settings` (
  `ext` VARCHAR(10)
);