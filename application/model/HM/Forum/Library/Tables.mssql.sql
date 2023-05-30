SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_list](
    [forum_id] int NOT NULL IDENTITY PRIMARY KEY,
    [subject_id] int NOT NULL DEFAULT(0),
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [title] varchar(255) NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0)
);
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_sections](
    [section_id] int NOT NULL IDENTITY PRIMARY KEY,
    [lesson_id] int NOT NULL DEFAULT(0),
    [forum_id] int NOT NULL,
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [parent_id] int NOT NULL DEFAULT(0),
    [title] varchar(255) NOT NULL,
    [text] text NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [last_msg] datetime NOT NULL DEFAULT(0),
    [count_msg] int NOT NULL DEFAULT(0),
    [order] int NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0)
);
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_messages](
    [message_id] int NOT NULL IDENTITY PRIMARY KEY,
    [forum_id] int NOT NULL,
    [section_id] int NOT NULL DEFAULT(0),
    [user_id] int NOT NULL,
    [user_name] varchar(255) NOT NULL DEFAULT (''),
    [user_ip] varchar(16) NOT NULL DEFAULT ('127.0.0.1'),
    [answer_to] int NOT NULL DEFAULT(0),
    [title] varchar(255) NOT NULL,
    [text] text NOT NULL,
    [text_preview] varchar(255) NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    [updated] datetime NOT NULL DEFAULT(0),
    [rating] int NOT NULL DEFAULT(0),
    [flags] int NOT NULL DEFAULT(0)
);

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[forums_messages_showed](
    [user_id] int NOT NULL,
    [message_id] int NOT NULL,
    [created] datetime NOT NULL DEFAULT(0),
    PRIMARY KEY([user_id], [message_id])
);
GO