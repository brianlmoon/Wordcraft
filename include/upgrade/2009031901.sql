--
-- Update posts table with ints for datetime
--

alter table {PREFIX}_posts add post_date_old datetime;
update {PREFIX}_posts set post_date_old=post_date;
alter table {PREFIX}_posts modify post_date bigint unsigned not null default 0;
update {PREFIX}_posts set post_date=unix_timestamp(post_date_old);
alter table {PREFIX}_posts drop post_date_old;

--
-- Update comments table with ints for datetime
--

alter table {PREFIX}_comments add comment_date_old datetime;
update {PREFIX}_comments set comment_date_old=comment_date;
alter table {PREFIX}_comments modify comment_date bigint unsigned not null default 0;
update {PREFIX}_comments set comment_date=unix_timestamp(comment_date_old);
alter table {PREFIX}_comments drop comment_date_old;

