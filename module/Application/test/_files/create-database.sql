create database autowp_test default character set utf8;
GRANT ALL PRIVILEGES ON autowp_test.* TO autowp_test@localhost IDENTIFIED BY "test";
flush privileges;
\. module/Application/test/_files/dump.sql

mysqldump -u autowp_test -p autowp_test --complete-insert --skip-add-locks --hex-blob --default-character-set=utf8 -r dump.sql