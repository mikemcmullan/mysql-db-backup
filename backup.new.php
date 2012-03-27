<?php
	
	set_time_limit(0);
	
	require "backup.class.new.php";
    
    try
    {
        $backup = new Mysql_Backup;
        
        $backup->initialize(array(
            'mysqldump_location' => '/Applications/MAMP/Library/bin/mysqldump'
        ));
        
        $backup->set_database_host(array(
            'db_host' => ':/Applications/MAMP/tmp/mysql/mysql.sock',
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