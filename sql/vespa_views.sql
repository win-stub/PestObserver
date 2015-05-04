USE `vespa`;

CREATE OR REPLACE ALGORITHM=TEMPTABLE DEFINER=`vespa`@`localhost` SQL SECURITY DEFINER VIEW `log_formatted` AS SELECT FROM_UNIXTIME(`time`) AS `date`,`user`,SUBSTR(`session`,1,6) AS `session`,`type`,`route`,`parameters`,`status`,`response`,`duration`,`ip`,`msg`,`watchpoint` FROM `log` ORDER BY `time` DESC;
