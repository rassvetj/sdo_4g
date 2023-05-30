IF OBJECT_ID('dbo.es_events', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_events];
go

create table [dbo].[es_events] (
    [event_id] int not null identity primary key,
    [event_type_id] int not null,
    [event_trigger_id] int not null,
    [event_group_id] int not null default(0),
    [description] text not null default(''),
    [create_time] float(53) not null
);
go

IF OBJECT_ID('dbo.es_event_users', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_users];
go

create table [dbo].[es_event_users] (
    [event_id] int not null,
    [user_id] int not null,
    [views] int not null default 0,
    primary key (event_id,user_id)
);
go

IF OBJECT_ID('dbo.es_event_group_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_group_types];
go
create table [dbo].[es_event_group_types] (
    event_group_type_id int not null,
    name varchar(255) not null,
    PRIMARY KEY (event_group_type_id)
);
go

IF OBJECT_ID('dbo.es_event_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_types];
go
create table [dbo].[es_event_types] (
    event_type_id int not null identity primary key,
    name varchar(255) not null,
    event_group_type_id int not null
);
go

IF OBJECT_ID('dbo.es_notify_types', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_notify_types];
go
create table [dbo].[es_notify_types] (
    notify_type_id int not null,
    name varchar(255) not null,
    PRIMARY KEY (notify_type_id)
);
go

IF OBJECT_ID('dbo.es_user_notifies', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_user_notifies];
go
create table [dbo].[es_user_notifies] (
    user_id int not null,
    notify_type_id int not null,
    event_type_id int not null,
    is_active tinyint not null default 0,
    PRIMARY KEY (user_id, notify_type_id, event_type_id)
);
go

IF OBJECT_ID('dbo.es_event_groups', 'U') IS NOT NULL
      DROP TABLE [dbo].[es_event_groups];
go
create table [dbo].[es_event_groups] (
    event_group_id int not null identity primary key,
    trigger_instance_id int not null,
    type varchar(255) not null,
    data text not null,
    CONSTRAINT group_name UNIQUE(trigger_instance_id,type)
);
go

SET ANSI_NULLS ON
go
set quoted_identifier on
go
create function [dbo].[ranker] (@evGroupId int, @group int, @rank int)
returns int
as
begin
return case when @evGroupId = @group then @rank+1 else 1 end
end
go
