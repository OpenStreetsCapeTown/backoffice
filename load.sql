SET NAMES utf8;
SET time_zone = '+00:00';

CREATE TABLE `ages` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `age` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `areas` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `communication_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `donations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `details` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `donation_event` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `donation_event` (`donation_event`),
  CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`),
  CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`donation_event`) REFERENCES `donation_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `donation_events` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `events` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `place` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `parent_event` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `parent_event` (`parent_event`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`type`) REFERENCES `event_types` (`id`),
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`parent_event`) REFERENCES `events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `event_attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `event` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `event` (`event`),
  CONSTRAINT `event_attendance_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_attendance_ibfk_4` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `event_relationships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `event_types` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `identity_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `income_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `interaction_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `involvement_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `language_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_action` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `people` mediumint(8) unsigned DEFAULT NULL,
  `details` text COLLATE utf8_unicode_ci,
  `user` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `log_action` (`log_action`),
  KEY `user` (`user`),
  CONSTRAINT `log_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `log_ibfk_4` FOREIGN KEY (`log_action`) REFERENCES `log_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `log_ibfk_5` FOREIGN KEY (`user`) REFERENCES `openid_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `log_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `mailinglist_options` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mailchimp_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `openid_identities` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `userid` (`userid`),
  CONSTRAINT `openid_identities_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `openid_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `openid_permissions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `openid_user` mediumint(8) unsigned NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user` (`openid_user`,`status`),
  CONSTRAINT `openid_permissions_ibfk_3` FOREIGN KEY (`openid_user`) REFERENCES `openid_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `openid_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `screenname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `organization_main_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `organization_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `main_organization` int(10) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `main_organization` (`main_organization`),
  CONSTRAINT `organization_types_ibfk_1` FOREIGN KEY (`main_organization`) REFERENCES `organization_main_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `os_communication_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `organization` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `organization_type` int(10) unsigned DEFAULT NULL,
  `affiliation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthyear` year(4) NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_additional` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `suburb` smallint(5) unsigned DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `twitter` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cell` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `referral` tinyint(3) unsigned DEFAULT NULL,
  `volunteer` tinyint(1) unsigned NOT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `active_mailings` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `suburb` (`suburb`),
  KEY `referral` (`referral`),
  KEY `age` (`age`),
  KEY `organization_type` (`organization_type`),
  CONSTRAINT `people_ibfk_1` FOREIGN KEY (`suburb`) REFERENCES `suburbs` (`id`),
  CONSTRAINT `people_ibfk_2` FOREIGN KEY (`referral`) REFERENCES `referral_sources` (`id`),
  CONSTRAINT `people_ibfk_3` FOREIGN KEY (`age`) REFERENCES `ages` (`id`),
  CONSTRAINT `people_ibfk_4` FOREIGN KEY (`organization_type`) REFERENCES `organization_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `event` mediumint(8) unsigned NOT NULL,
  `relationship` int(10) unsigned NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `event` (`event`),
  KEY `relationship` (`relationship`),
  CONSTRAINT `people_events_ibfk_4` FOREIGN KEY (`relationship`) REFERENCES `event_relationships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_events_ibfk_5` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_events_ibfk_6` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_mailinglists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `mailinglist` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `mailinglist` (`mailinglist`),
  CONSTRAINT `people_mailinglists_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_mailinglists_ibfk_4` FOREIGN KEY (`mailinglist`) REFERENCES `mailinglist_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `preference` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `preference` (`preference`),
  CONSTRAINT `people_preferences_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_preferences_ibfk_5` FOREIGN KEY (`preference`) REFERENCES `preference_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_skills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `skill` tinyint(3) unsigned NOT NULL,
  `people` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `skill` (`skill`),
  KEY `people` (`people`),
  CONSTRAINT `people_skills_ibfk_3` FOREIGN KEY (`skill`) REFERENCES `skills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_skills_ibfk_4` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `tag` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `tag` (`tag`),
  CONSTRAINT `people_tags_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `people_tags_ibfk_4` FOREIGN KEY (`tag`) REFERENCES `tags_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `people_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `people` mediumint(8) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `type` (`type`),
  CONSTRAINT `people_types_ibfk_2` FOREIGN KEY (`type`) REFERENCES `types` (`id`),
  CONSTRAINT `people_types_ibfk_3` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `planning_categories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `planning_checklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `type` enum('regular','provider') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'regular',
  `event` mediumint(8) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `finished_user` mediumint(8) unsigned DEFAULT NULL,
  `finished_date` date DEFAULT NULL,
  `comments` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `event` (`event`),
  KEY `status` (`active`),
  KEY `finished_user` (`finished_user`),
  CONSTRAINT `planning_checklist_ibfk_2` FOREIGN KEY (`category`) REFERENCES `planning_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `planning_checklist_ibfk_4` FOREIGN KEY (`event`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `planning_checklist_ibfk_5` FOREIGN KEY (`finished_user`) REFERENCES `openid_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `planning_checklist_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` mediumint(8) unsigned NOT NULL DEFAULT '4',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  `type` enum('regular','provider') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'regular',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  CONSTRAINT `planning_checklist_template_ibfk_2` FOREIGN KEY (`category`) REFERENCES `planning_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `planning_providers` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `checklist` int(10) unsigned NOT NULL,
  `quotes` tinyint(1) unsigned NOT NULL,
  `invoice` tinyint(1) unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist` (`checklist`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `preference_options` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  CONSTRAINT `preference_options_ibfk_3` FOREIGN KEY (`type`) REFERENCES `preference_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `preference_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `answers` enum('single','multiple') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'single',
  `applicable_to` enum('volunteers','nonvolunteers','all') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'volunteers',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `referral_sources` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `segments` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mailchimp_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post_array` text COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `people` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `skills` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `suburbs` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `area` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `area` (`area`),
  CONSTRAINT `suburbs_ibfk_1` FOREIGN KEY (`area`) REFERENCES `areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `surveys` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `post` text COLLATE utf8_unicode_ci NOT NULL,
  `people` mediumint(8) unsigned DEFAULT NULL,
  `survey` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `people` (`people`),
  KEY `survey` (`survey`),
  CONSTRAINT `surveys_ibfk_1` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `surveys_ibfk_2` FOREIGN KEY (`survey`) REFERENCES `survey_list` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `survey_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `survey` mediumint(8) unsigned NOT NULL,
  `question` tinyint(3) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `checklist` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `survey` (`survey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `survey_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `tags_options` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `transport_options` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `whiteboard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_message` int(10) unsigned DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `parent_message` (`parent_message`),
  CONSTRAINT `whiteboard_ibfk_1` FOREIGN KEY (`category`) REFERENCES `whiteboard_options` (`id`),
  CONSTRAINT `whiteboard_ibfk_2` FOREIGN KEY (`parent_message`) REFERENCES `whiteboard` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `whiteboard_options` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_generated` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2015-08-29 09:27:57

ALTER TABLE `types`
ADD `description` text NULL;

ALTER TABLE `tags_options`
ADD `description` text NULL;

ALTER TABLE `event_relationships`
ADD `description` text NULL;

CREATE TABLE `locations` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

ALTER TABLE `locations`
ADD INDEX `active` (`active`);

ALTER TABLE `locations`
CHANGE `id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;


ALTER TABLE `locations`
CHANGE `active` `active` int(1) unsigned NOT NULL DEFAULT '1' AFTER `name`;


ALTER TABLE `events`
ADD `location` int(10) unsigned NULL,
ADD FOREIGN KEY (`location`) REFERENCES `locations` (`id`) ON DELETE CASCADE;
