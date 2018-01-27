DROP TABLE IF EXISTS `civicrm_membership_period`;


-- /*******************************************************
-- *
-- * civicrm_membership_period
-- *
-- * Record Membership Period
-- *
-- *******************************************************/
CREATE TABLE `civicrm_membership_period` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Membership Period ID',
     `start_date` date NOT NULL   COMMENT 'Membership Period start date',
     `end_date` date NULL   COMMENT 'Membership Period start date',
     `contact_id` int unsigned NOT NULL   COMMENT 'FK to Contact Entity',
     `membership_id` int unsigned NOT NULL   COMMENT 'FK to Membership Entity',
     `membership_type_id` int unsigned NOT NULL   COMMENT 'FK to Membership Type',
     `contribution_id` int unsigned    COMMENT 'FK to Contribution Entity'
,
        PRIMARY KEY (`id`)


,          CONSTRAINT FK_civicrm_membership_period_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_membership_period_membership_id FOREIGN KEY (`membership_id`) REFERENCES `civicrm_membership`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_membership_period_membership_type_id FOREIGN KEY (`membership_type_id`) REFERENCES `civicrm_membership_type`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_membership_period_contribution_id FOREIGN KEY (`contribution_id`) REFERENCES `civicrm_contribution`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;