# SingleJobQueue

These are codes of single job queue for PHP. We could execute php or other language of script by scheduling in Linux. I make this privately and use it in company`s php framework. Currently I have not extract clearly from the framework so it needs to do some modify when you want to use it.

##History
We have requirement when we want to schedule some script in our server code. Cron is a good choice but it could not work when you do not know what exactly script you want to run or you just want to change the schedule dynamically. So I made this to run the scheduled script by the queue table in database.

You could do something like this.
1. Make a php script to change a value in database
2. Add the scipt to queue table and make it runs at 10:00:00

This has been verified on real system and runs well

##Install
