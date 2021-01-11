-- Exported from QuickDBD: https://www.quickdatabasediagrams.com/
-- Link to schema: https://app.quickdatabasediagrams.com/#/d/NTnf5P
-- NOTE! If you have used non-SQL datatypes in your design, you will have to change these here.


CREATE TABLE `mybb_consensus_polls` (
                                        `poll_id` int AUTO_INCREMENT NOT NULL ,
                                        `title` varchar  NOT NULL ,
                                        `description` text  NOT NULL ,
                                        `expires` dateTime  NOT NULL ,
                                        `created_at` dateTime  NOT NULL DEFAULT getutcdate(),
                                        `created_by_user_id` int  NOT NULL ,
                                        `status` inr  NOT NULL ,
                                        PRIMARY KEY (
                                                     `poll_id`
                                            )
);

CREATE TABLE `mybb_consensus_choices` (
                                          `choice_id` int AUTO_INCREMENT NOT NULL ,
                                          `poll_id` int  NOT NULL ,
                                          `choice` varchar  NOT NULL ,
                                          PRIMARY KEY (
                                                       `choice_id`
                                              )
);

CREATE TABLE `mybb_consensus_votes` (
                                        `vote_id` int AUTO_INCREMENT NOT NULL ,
                                        `choice_id` int  NOT NULL ,
                                        `vote_by_user_id` int  NOT NULL ,
                                        `consensus_points` tinyInt  NOT NULL ,
                                        PRIMARY KEY (
                                                     `vote_id`
                                            )
);

CREATE TABLE `mybb_consensus_status` (
                                         `status_id` int AUTO_INCREMENT NOT NULL ,
                                         `status` varchar  NOT NULL ,
                                         PRIMARY KEY (
                                                      `status_id`
                                             )
);

ALTER TABLE `mybb_consensus_polls` ADD CONSTRAINT `fk_mybb_consensus_polls_created_by_user_id` FOREIGN KEY(`created_by_user_id`)
    REFERENCES `mybb_users` (`user_id`);

ALTER TABLE `mybb_consensus_polls` ADD CONSTRAINT `fk_mybb_consensus_polls_status` FOREIGN KEY(`status`)
    REFERENCES `mybb_consensus_status` (`status_id`);

ALTER TABLE `mybb_consensus_choices` ADD CONSTRAINT `fk_mybb_consensus_choices_poll_id` FOREIGN KEY(`poll_id`)
    REFERENCES `mybb_consensus_polls` (`poll_id`);

ALTER TABLE `mybb_consensus_votes` ADD CONSTRAINT `fk_mybb_consensus_votes_choice_id` FOREIGN KEY(`choice_id`)
    REFERENCES `mybb_consensus_choices` (`choice_id`);

ALTER TABLE `mybb_consensus_votes` ADD CONSTRAINT `fk_mybb_consensus_votes_vote_by_user_id` FOREIGN KEY(`vote_by_user_id`)
    REFERENCES `mybb_users` (`user_id`);
