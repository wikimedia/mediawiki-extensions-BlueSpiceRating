-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/BlueSpiceRating/maintenance/db/sql/rating.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/bs_rating (
  rat_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  rat_reftype VARCHAR(13) DEFAULT 'article' NOT NULL,
  rat_ref VARCHAR(33) DEFAULT '0' NOT NULL,
  rat_userid SMALLINT UNSIGNED DEFAULT NULL,
  rat_userip VARCHAR(15) DEFAULT NULL,
  rat_value SMALLINT DEFAULT 0 NOT NULL,
  rat_created VARCHAR(14) DEFAULT '' NOT NULL,
  rat_touched VARCHAR(14) DEFAULT '' NOT NULL,
  rat_archived TINYINT(1) DEFAULT '0' NOT NULL,
  rat_subtype VARCHAR(33) DEFAULT '' NOT NULL,
  INDEX rat_userid (rat_userid),
  INDEX rat_userip (rat_userip),
  INDEX rat_ref (rat_ref),
  INDEX rat_reftype (rat_reftype),
  PRIMARY KEY(rat_id)
) /*$wgDBTableOptions*/;
