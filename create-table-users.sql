create table users (
    id int NOT NULL auto_increment ,
    username varchar(255) ,
    `password` varchar(255),
    primary key(id)
);
-- passwordは予約文字なのでバッククォートで囲う。
-- NOT NULL ← 必須という意味