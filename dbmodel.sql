
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- RolledWest implementation : © Jonathan Moyett <someguy590@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):

ALTER TABLE `player` ADD `wood` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `copper` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `silver` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `gold` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `is_banking_during_turn` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `is_banking_in_between_turn` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `is_purchasing_office` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `is_purchasing_contract` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `is_building_claim` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `is_shipping` BIT(1) NOT NULL DEFAULT FALSE;
ALTER TABLE `player` ADD `copper_shipped` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `silver_shipped` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `gold_shipped` INT UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `exclusive` (
  `exclusive_id` int(10) unsigned NOT NULL,
  `exclusive_type` varchar(16) NOT NULL,
  `marked_by_player` int(10) unsigned NULL,
  PRIMARY KEY (`exclusive_id`, `exclusive_type`),
  FOREIGN KEY (`marked_by_player`) REFERENCES player(`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `claim` (
  `claim_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `terrain_type_id` int(11) NOT NULL,
  `space_id` int(11) NOT NULL,
  `claim_type` varchar(16) NULL,
  PRIMARY KEY (`claim_id`),
  FOREIGN KEY (`player_id`) REFERENCES player(`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;