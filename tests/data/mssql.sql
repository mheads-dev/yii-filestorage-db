IF OBJECT_ID('[dbo].[product]', 'U') IS NOT NULL DROP TABLE [dbo].[product];

CREATE TABLE [dbo].[product]
(
	[id]         int IDENTITY(1,1) NOT NULL,
	[name]       varchar(255) NOT NULL,
	[picture_id] bigint NULL,
	[manual_id]  bigint NULL,
	CONSTRAINT [PK_product] PRIMARY KEY CLUSTERED ([id] ASC)
);

CREATE INDEX [idx_product_picture_id] ON [dbo].[product] ([picture_id]);
CREATE INDEX [idx_product_manual_id] ON [dbo].[product] ([manual_id]);
