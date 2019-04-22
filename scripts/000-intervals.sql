create table intervals
(
	interval_id int auto_increment
		primary key,
	date_start date not null,
	date_end date not null,
	price float not null
);

