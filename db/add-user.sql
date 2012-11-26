INSERT INTO `users` (
`uid` ,
`login` ,
`pass` ,
`email` ,
`enabled` ,
`admin`
)
VALUES (
NULL , 'admin', ENCRYPT( 'admin','aa' ) , 'admin@alternc.org', '1', '1'
);
