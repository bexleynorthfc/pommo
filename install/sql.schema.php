-- CONFIG <?php defined('_IS_VALID') or die('Move along...'); ?>

CREATE TABLE :::config::: (
  `config_name` varchar(64) NOT NULL default '',
  `config_value` text NOT NULL,
  `config_description` tinytext NOT NULL,
  `autoload` enum('on','off') NOT NULL default 'on',
  `user_change` enum('on','off') NOT NULL default 'on',
  PRIMARY KEY  (`config_name`)
);

INSERT INTO :::config::: VALUES ('admin_username', 'admin', 'Username', 'off', 'on');
INSERT INTO :::config::: VALUES ('admin_password', 'c4027s86dd2bfe4ga8fh2dda7sa3d727', 'Password', 'off', 'on');
INSERT INTO :::config::: VALUES ('site_name', '', 'Website Name', 'on', 'on');
INSERT INTO :::config::: VALUES ('site_url', '', 'Website URL', 'on', 'on');
INSERT INTO :::config::: VALUES ('site_success', '', 'Signup Success URL', 'off', 'on');
INSERT INTO :::config::: VALUES ('list_name', ' Email List', 'List Name', 'on', 'on');
INSERT INTO :::config::: VALUES ('admin_email', '', 'Administrator Email', 'on', 'on');
INSERT INTO :::config::: VALUES ('list_fromname', '', 'From Name', 'off', 'on');
INSERT INTO :::config::: VALUES ('list_fromemail', '', 'From Email', 'off', 'on');
INSERT INTO :::config::: VALUES ('list_frombounce', '', 'Bounces', 'off', 'on');
INSERT INTO :::config::: VALUES ('list_exchanger', 'sendmail', 'List Exchanger', 'off', 'on');
INSERT INTO :::config::: VALUES ('list_confirm', 'on', 'Confirmation Messages', 'off', 'on');
INSERT INTO :::config::: VALUES ('demo_mode', 'on', 'Demonstration Mode', 'on', 'on');
INSERT INTO :::config::: VALUES ('site_confirm', '', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('smtp_1', '', '', 'off', 'off');
INSERT INTO :::config::: VALUES ('smtp_2', '', '', 'off', 'off');
INSERT INTO :::config::: VALUES ('smtp_3', '', '', 'off', 'off');
INSERT INTO :::config::: VALUES ('smtp_4', '', '', 'off', 'off');
INSERT INTO :::config::: VALUES ('throttle_DBPP', '0', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('throttle_DP', '10', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('throttle_DMPP', '0', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('throttle_BPS', '0', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('throttle_MPS', '3', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('throttle_SMTP', 'individual', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('dos_processors', '0', '', 'on', 'off');
INSERT INTO :::config::: VALUES ('messages', '', '', 'off', 'off');
INSERT INTO :::config::: VALUES ('list_charset', 'UTF-8', '', 'off', 'on');
INSERT INTO :::config::: VALUES ('version', 'Lloyd 1.0', 'poMMo Version', 'on', 'off');
INSERT INTO :::config::: VALUES ('revision', '26', 'Internal Revision', 'on', 'off');

-- DEMOGRAPHICS

CREATE TABLE :::fields::: (
  `field_id` smallint(5) unsigned NOT NULL auto_increment,
  `field_active` enum('on','off') NOT NULL default 'off',
  `field_ordering` smallint(5) unsigned NOT NULL default '0',
  `field_name` varchar(60) default NULL,
  `field_prompt` varchar(60) default NULL,
  `field_normally` varchar(60) default NULL,
  `field_options` text,
  `field_required` enum('on','off') NOT NULL default 'off',
  `field_type` enum('checkbox','multiple','text','date','number','multiplemultiple','bigtext') default NULL,
  PRIMARY KEY  (`field_id`),
  KEY `active` (`field_active`,`field_ordering`)
);

-- GROUPS

CREATE TABLE :::groups::: (
  `group_id` smallint(5) unsigned NOT NULL auto_increment,
  `group_name` tinytext  NOT NULL,
  `group_cacheTally` int(10) unsigned NOT NULL,
  `group_cacheTime` timestamp NULL,
  `group_filter_logic` enum('all', 'any') NOT NULL default 'all',
  PRIMARY KEY  (`group_id`)
);

-- GROUPS_CRITERIA

CREATE TABLE :::groups_criteria::: (
  `criteria_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `field_id` tinyint(3) unsigned NOT NULL default '0',
  `logic` enum('is_in','not_in','is_equal','not_equal','is_more','is_less','is_true','not_true', 'contains', 'does_not_contain', 'contains_multiple', 'does_not_contain_multiple', 'starts_with') NOT NULL default 'is_in',
  `value` text,
  PRIMARY KEY  (`criteria_id`),
  KEY `group_id` (`group_id`)
);


-- MAILING_CURRENT

CREATE TABLE :::mailing_current::: (
  `id` enum('1') NOT NULL default '1',
  `fromname` varchar(60) NOT NULL default '',
  `fromemail` varchar(60) NOT NULL default '',
  `frombounce` varchar(60) NOT NULL default '',
  `subject` varchar(60) NOT NULL default '',
  `body` mediumtext NOT NULL,
  `altbody` mediumtext default NULL,
  `ishtml` enum('on','off') NOT NULL default 'off',
  `mailgroup` varchar(60) NOT NULL default 'Unknown',
  `subscriberCount` int(10) unsigned NOT NULL default '0',
  `started` datetime default NULL,
  `finished` datetime default NULL,
  `sent` int(10) unsigned NOT NULL default '0',
  `status` enum('started','stopped') NOT NULL default 'stopped',
  `command` enum('none','restart','stop') NOT NULL default 'none',
  `serial` varchar(20) default NULL,
  `securityCode` varchar(35) default NULL,
  `notices` longtext default NULL,
  `charset` varchar(15) NOT NULL default 'UTF-8',
  PRIMARY KEY  (`id`)
);

-- MAILING_HISTORY

CREATE TABLE :::mailing_history::: (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `fromname` varchar(60) NOT NULL default '',
  `fromemail` varchar(60) NOT NULL default '',
  `frombounce` varchar(60) NOT NULL default '',
  `subject` varchar(60) NOT NULL default '',
  `body` mediumtext NOT NULL,
  `altbody` mediumtext default NULL,
  `ishtml` enum('on','off') NOT NULL default 'off',
  `mailgroup` varchar(60) NOT NULL default 'Unknown',
  `subscriberCount` int(10) unsigned NOT NULL default '0',
  `started` datetime NOT NULL,
  `finished` datetime NOT NULL,
  `sent` int(10) unsigned NOT NULL default '0',
  `charset` varchar(15) NOT NULL default 'UTF-8',
  `notices` longtext default NULL,
  PRIMARY KEY  (`id`)
);

-- MAILING_TEMPLATES

CREATE TABLE :::mailing_templates::: (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `body` mediumtext NOT NULL default '',
  `charset` varchar(15) NOT NULL default 'UTF-8',
  PRIMARY KEY  (`id`)
);

-- MAILING_TEMPLATES

INSERT INTO :::mailing_templates::: (`name`, `body`, `charset`) VALUES 
('Default (Blank)', '', 'UTF-8');

-- QUEUE

CREATE TABLE :::queue::: (
  `email` varchar(60) NOT NULL default '',
  `smtp_id` enum('0','1','2','3','4') NOT NULL default '0',
  UNIQUE KEY `email` (`email`),
  KEY `smtp_id` (`smtp_id`)
);


-- PENDING

CREATE TABLE :::pending::: (
  `pending_id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(35) NOT NULL default '',
  `type` enum('add','del','change','password') default NULL,
  `email` varchar(60) NOT NULL default '',
  `newEmail` varchar(60) NULL default NULL,
  `date` date NOT NULL default '0000-00-00',
  `lastModified` timestamp NOT NULL default NOW(),
  PRIMARY KEY  (`pending_id`),
  KEY `code` (`code`),
  KEY `type` (`type`),
  KEY `email` (`email`)
);

-- PENDING_DATA

CREATE TABLE :::pending_data::: (
  `data_id` bigint(20) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `pending_id` int(10) unsigned NOT NULL default '0',
  `value` tinytext,
  PRIMARY KEY  (`data_id`),
  UNIQUE KEY `field_id` (`field_id`,`pending_id`)
);

CREATE TABLE :::pending_bigdata::: (
  `data_id` bigint(20) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `pending_id` int(10) unsigned NOT NULL default '0',
  `value` text,
  PRIMARY KEY  (`data_id`),
  UNIQUE KEY `field_id` (`field_id`,`pending_id`)
);

-- SUBSCRIBERS

CREATE TABLE IF NOT EXISTS :::subscribers::: (
  `subscribers_id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(60) NOT NULL default '',
  `date` date NOT NULL default '0000-00-00',
  `lastModified` timestamp NOT NULL default NOW(),
  PRIMARY KEY  (`subscribers_id`),
  KEY `email` (`email`(30))
);

-- SUBSCRIBERS_FLAGGED

CREATE TABLE :::subscribers_flagged::: (
  `flagged_id` int(10) unsigned NOT NULL auto_increment,
  `subscribers_id` int(10) unsigned NOT NULL default '0',
  `flagged_type` enum('update') default NULL,
  PRIMARY KEY  (`flagged_id`),
  KEY `subscribers_id` (`subscribers_id`,`flagged_type`)
);

-- SUBSCRIBERS_DATA

CREATE TABLE :::subscribers_data::: (
  `data_id` bigint(20) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `subscribers_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`data_id`),
  UNIQUE KEY `s_plus_demo_id` (`field_id`,`subscribers_id`),
  KEY `val_plus_demo` (`value`,`field_id`),
  KEY `subscribers_id` (`subscribers_id`),
  KEY `subscribers_id_2` (`subscribers_id`,`value`)
);

CREATE TABLE :::subscribers_bigdata::: (
  `data_id` bigint(20) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `subscribers_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL default '',
  PRIMARY KEY  (`data_id`),
  UNIQUE KEY `s_plus_demo_id` (`field_id`,`subscribers_id`),
  KEY `val_plus_demo` (`value`(255),`field_id`),
  KEY `subscribers_id` (`subscribers_id`),
  KEY `subscribers_id_2` (`subscribers_id`,`value`(255))
);

-- UPDATES

CREATE TABLE :::updates::: (
  `update_id` int(10) unsigned NOT NULL auto_increment,
  `update_serial` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`update_id`),
  KEY `update_serial` (`update_serial`)
);
