ALTER TABLE /*$wgDBprefix*/bs_rating ADD `rat_subtype` VARCHAR(33) NOT NULL DEFAULT '';
CREATE INDEX /*i*/rat_subtype ON /*$wgDBprefix*/bs_rating (rat_subtype);