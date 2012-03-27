<?php
	
	set_time_limit(0);
	
	require "backup.class.new.php";
		
    $backup = new Mysql_Backup;
    
    $backup->set_database_config(array(
        'host' => 'localhost',
        'user' => 'root',
        'pass' => 'root'
    ));
    
    print_r($backup->get_config());