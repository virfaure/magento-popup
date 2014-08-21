<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('popup')};
CREATE TABLE IF NOT EXISTS {$this->getTable('popup')} (
  `popup_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `popup_title` varchar(255) NOT NULL DEFAULT '',
  `popup_description` varchar(255) DEFAULT '',
  `popup_content_html` text,
  `popup_newsletter` tinyint(4) DEFAULT NULL,
  `popup_url` varchar(255) DEFAULT NULL,
  `popup_image` varchar(255) NOT NULL,
  `popup_date_start` date NOT NULL,
  `popup_date_end` date DEFAULT NULL,
  `popup_coupon` tinyint(1) DEFAULT NULL,
  `popup_email_template` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`popup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `popup_store` (
  `popup_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `popup_id` (`popup_id`,`store_id`),
  KEY `popup_id_2` (`popup_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `popup_store` ADD CONSTRAINT `fk_popup_store_1`  FOREIGN KEY (`store_id` )  REFERENCES `core_store` (`store_id` )  ON DELETE CASCADE  ON UPDATE CASCADE, ADD INDEX `fk_popup_store_1` (`store_id` ASC) ;
ALTER TABLE `popup_store` ADD CONSTRAINT `fk_popup_store_2`  FOREIGN KEY (`popup_id` )  REFERENCES `popup` (`popup_id` )  ON DELETE CASCADE  ON UPDATE CASCADE, ADD INDEX `fk_popup_store_2` (`popup_id` ASC) ;


CREATE TABLE IF NOT EXISTS `newsletter_popup_subcriber` (
  `newsletter_popup_subcriber_id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(10) unsigned NOT NULL,
  `last_popup_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`newsletter_popup_subcriber_id`),
  KEY `subcriber_id` (`subscriber_id`),
  KEY `last_popup_id` (`last_popup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `newsletter_popup_subcriber` ADD CONSTRAINT `fk_newsletter_popup_subcriber_1` FOREIGN KEY (`subscriber_id` )  REFERENCES `newsletter_subscriber` (`subscriber_id` )  ON DELETE CASCADE  ON UPDATE CASCADE, ADD INDEX `fk_newsletter_popup_subcriber_1` (`subscriber_id` ASC) ; 
ALTER TABLE `newsletter_popup_subcriber` ADD CONSTRAINT `fk_newsletter_popup_subcriber_2` FOREIGN KEY (`last_popup_id` )  REFERENCES `popup` (`popup_id` )  ON DELETE CASCADE  ON UPDATE CASCADE, ADD INDEX `fk_newsletter_popup_subcriber_2` (`last_popup_id` ASC) ;

    ");

$installer->endSetup(); 
