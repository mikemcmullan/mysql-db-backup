# Simple MySQL Backup Tool

This simple mysql backup tool makes it easy to automatically backup all your mysql databases. This tool is best run from terminal or cron but can easily be modified to run in the browser (not recommended). To run from the browser, simple remove the first line in the backup.php file which tells the parser the following lines of code are php. The rest of the documentation about how to configure this tool is located in the backup.php file.  It is highly recommended this tool be run periodically for regular mysql backups.  This can easily be obtained via a cron job.  The setup may vary depending on your host, please consult your hosts documentation before proceeding.

# Running the tool

Once you have run this tool for the first time the folder which you have defined in the config under the 'local\_path' should have been created. Inside of the defined folder several more sub folders will have been created in the structure of year/month/day. If the tool is run more than once per day the newest backup will have overwritten the original backup for said day.  As the days pass a new folder will be created for each day / each month and so on. Inside of the folder of the current day each database will have its own sql file which has been compressed using bz2, this should save some space.  

# Compatibility

This tool has only been tested on php 5.2 on the Mediatemple Grid Service. This tool has been running perfectly for me for the last two months.