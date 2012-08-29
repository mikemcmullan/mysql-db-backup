<?php
	
	set_time_limit(0);
	
	require "backup.class.new.php";
    
    try
    {
        $backup = new Mysql_Backup;
        
        $backup->initialize(array(
            'mysqldump_location' => '/usr/local/mysql-5.5.25a-osx10.6-x86_64/bin/mysqldump',
            'local_path' => '/Users/wiseass911/mysql-backups/'
        ));
        
        $backup->set_database_host(array(
            'db_host' => 'localhost',
            'db_user' => 'root',
            'db_pass' => 'root',
            //'db_name' => array('shadowlines', 'tndl'),
            //'ignore_db' => 'shadowlines'
        ));
        
        $backup->backup_databases();
    }
    catch(Exception $e)
    {
        print_r($e->getMessage());
    }