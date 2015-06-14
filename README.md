# SingleJobQueue

These are codes of single job queue for PHP. We could execute php or other language of script by scheduling in Linux. I make this privately and use it in company`s php framework. Currently I have not extract clearly from the framework so it needs to do some modify when you want to use it.

## History
We have requirement when we want to schedule some script in our server code. Cron is a good choice but it could not work when you do not know what exactly script you want to run or you just want to change the schedule dynamically. So I made this to run the scheduled script by the queue table in database.

You could do something like this.

1. Make a php script to change a value in database
2. Add the scipt to queue table and make it runs at 10:00:00

This has been verified on real system and runs well.

I also made a management page to check the running result and get log.

The queue algorithm implements FIFO.

## Known issue
1. The script runner is poll by cron, so the min time slice is 1 minute

## Install
1. create database table
```
CREATE TABLE IF NOT EXISTS `admin_job_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '順番ID',
  `job_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'ジョブID',
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'ジョブ名前',
  `command` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'コマンド',
  `log_file` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'ログファイル',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状態 0:準備中 1: 実行中 2: 完了',
  `pid` int(10) unsigned DEFAULT NULL COMMENT 'プロセスID',
  `start_dt` datetime DEFAULT NULL COMMENT '開始日時',
  `create_dt` datetime NOT NULL COMMENT '作成日時',
  `update_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
  `delete_dt` datetime DEFAULT NULL COMMENT '削除日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;
```

2. Modify the code to fit your php framework, like model

3. Add cron as like following and specify the output log
```
#Single Queue Worker
*/1 * * * * /path/to/lib/SingleJobQueue/Worker.php >> /path/to/logs/single_queue_worker.log
```

4. If you want to re-use management page, you need to install the following js package
backbone.js
underscore.js

## System Screenshot
1. You could check how many jobs on the queue and get its running status
![alt text](https://github.com/markkong318/SingleJobQueue/blob/master/readme/screenshot/1.png)

2. In the job detail window, all the running parameter could be checked
![alt text](https://github.com/markkong318/SingleJobQueue/blob/master/readme/screenshot/2.png)

## Future work
1. It could not just be single job at one time, I wish one day it could running multiple jobs on the same time 
2. It could be better to have weight on some important jobs
