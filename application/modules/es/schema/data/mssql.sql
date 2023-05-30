TRUNCATE TABLE es_event_group_types;
go
SET IDENTITY_INSERT es_event_types ON
INSERT INTO es_event_group_types (event_group_type_id, name) VALUES
(1, 'personalMessages'),
(2, 'discussions'),
(3, 'notifications');
SET IDENTITY_INSERT es_event_types OFF
go

TRUNCATE TABLE es_event_types;
go
SET IDENTITY_INSERT es_event_types ON
insert into es_event_types (event_type_id,name, event_group_type_id) values 
(1,'forumAddMessage', 2),
(2, 'blogAddMessage', 2),
(3, 'wikiAddPage', 2),
(4, 'wikiModifyPage', 2),
(5, 'forumInternalAddMessage', 2),
(6, 'blogInternalAddMessage', 2),
(7, 'wikiInternalAddPage', 2),
(8, 'wikiInternalModifyPage', 2),
(9, 'courseAddMaterial', 3),
(10, 'courseAttachLesson', 3),
(11, 'courseScoreTriggered', 3),
(12, 'courseTaskAction', 3),
(13, 'commentAdd', 2),
(14, 'commentInternalAdd', 2),
(15, 'courseTaskScoreTriggered', 3),
(16, 'personalMessageSend', 1);
SET IDENTITY_INSERT es_event_types OFF
go

TRUNCATE TABLE es_notify_types;
go
INSERT INTO es_notify_types (notify_type_id, name) VALUES
(1, 'Email notifications'),
(2, 'Weekly reports by email');
go
