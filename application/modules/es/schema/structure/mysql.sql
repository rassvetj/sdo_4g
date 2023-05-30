DROP TABLE IF EXISTS `es_events`;
CREATE TABLE `es_events` (
	event_id INT(11) NOT NULL AUTO_INCREMENT,
	event_type_id INT(8) NOT NULL,
	event_trigger_id INT(11) NOT NULL,
	event_group_id INT(8) DEFAULT NULL,
	description TEXT NOT NULL DEFAULT '',
	create_time DOUBLE(25,10) NOT NULL,
	PRIMARY KEY (event_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_event_users`;
CREATE TABLE `es_event_users` (
	event_id INT(11) NOT NULL,
	user_id INT(11) NOT NULL,
	views TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (event_id,user_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_event_group_types`;
CREATE TABLE `es_event_group_types` (
        event_group_type_id INT(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (event_group_type_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_event_types`;
CREATE TABLE `es_event_types` (
	event_type_id INT(8) NOT NULL,
	name VARCHAR(255) NOT NULL,
        event_group_type_id INT(8) NOT NULL,
	PRIMARY KEY (event_type_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_event_groups`;
CREATE TABLE `es_event_groups` (
	event_group_id INT(8) NOT NULL AUTO_INCREMENT,
	trigger_instance_id INT(11) NOT NULL,
	type VARCHAR(255) NOT NULL,
	data TEXT NOT NULL,
	UNIQUE KEY group_name (trigger_instance_id,type),
	PRIMARY KEY (event_group_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_notify_types`;
CREATE TABLE `es_notify_types` (
        notify_type_id INT(8) NOT NULL,
        name VARCHAR(255) NOT NULL,
        PRIMARY KEY (notify_type_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `es_user_notifies`;
CREATE TABLE `es_user_notifies` (
        user_id INT(11) NOT NULL,
        notify_type_id INT(8) NOT NULL,
        event_type_id INT(8) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id,notify_type_id, event_type_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX event_type_id ON es_events (event_type_id);
