CREATE TABLE debits (
  id int(11) unsigned NOT NULL auto_increment,
  amount int(11) NOT NULL default '0',
  description varchar(255) NOT NULL default '',
  `date` bigint(14) default NULL,
  notes varchar(255) default NULL,
  tags varchar(255) default NULL,
  currency varchar(3) default NULL,
  PRIMARY KEY  (id),
  FULLTEXT KEY description (description),
  FULLTEXT KEY notes (notes),
  FULLTEXT KEY tags (tags)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE splits (
  id int(11) unsigned NOT NULL auto_increment,
  debits_id int(11) unsigned NOT NULL,
  amount int(11) NOT NULL default '0',
  notes varchar(255) default NULL,
  tags varchar(255) default NULL,
  PRIMARY KEY  (id),
  FULLTEXT KEY notes (notes),
  FULLTEXT KEY tags (tags)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

