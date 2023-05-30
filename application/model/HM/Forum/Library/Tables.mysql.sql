DROP TABLE IF EXISTS `forums_list`;

CREATE TABLE `forums_list`(
    `forum_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subject_id` int unsigned NOT NULL DEFAULT 0,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `title` varchar(255) NOT NULL,
    `created` datetime NOT NULL DEFAULT 0,
    `updated` datetime NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `forums_sections`;

CREATE TABLE `forums_sections`(
    `section_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `lesson_id` int unsigned NOT NULL DEFAULT 0,
    `forum_id` int unsigned NOT NULL,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `parent_id` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `text` mediumtext NOT NULL,
    `created` datetime NOT NULL DEFAULT 0,
    `updated` datetime NOT NULL DEFAULT 0,
    `last_msg` datetime NOT NULL DEFAULT 0,
    `count_msg` int unsigned NOT NULL DEFAULT 0,
    `order` int signed NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `forums_messages`;

CREATE TABLE `forums_messages`(
    `message_id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `forum_id` int unsigned NOT NULL,
    `section_id` int unsigned NOT NULL DEFAULT 0,
    `user_id` int unsigned NOT NULL,
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_ip` varchar(16) NOT NULL DEFAULT '127.0.0.1',
    `level` int unsigned NOT NULL,
    `answer_to` int unsigned NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `text` mediumtext NOT NULL,
    `text_preview` varchar(255) NOT NULL,
    `created` datetime NOT NULL DEFAULT 0,
    `updated` datetime NOT NULL DEFAULT 0,
    `rating` int signed NOT NULL DEFAULT 0,
    `flags` int unsigned NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `forums_messages_showed`;

CREATE TABLE `forums_messages_showed`(
    `user_id` int unsigned NOT NULL,
    `message_id` int unsigned NOT NULL,
    `created` datetime NOT NULL DEFAULT 0,
    PRIMARY KEY(`user_id`, `message_id`)
);