#!/usr/bin/php5
<?php
	
	set_time_limit(0);
	
	include "backup.class.php";

	/*
	* Available Configuration Options
	*
	* 'timezone' - The timezone where you wish the date to be based (default: 'America/Toronto')
	* 'hosts' - Contains all host information (required)
	* -- 'db_host' - database host (required)
	* -- 'db_user' - database user (required)
	* -- 'db_pass' - database password (required)
	* -- 'db_name' - the database you wish to backup, can be array of multiple database names.
	*				 If left blank all accessible to the user will be backedup.
	* -- 'ignore_db' - an array of databases you wish not to be backed up.
	* -- 'mysqldump_location' - the location of the mysqldump tool (defult: '/usr/bin/mysqldump')
	* -- 'local_path' - The path to the folder where the backups will be held. If the folder does not
	*					exis it will be created. (required)
	*/

	$config = array(
		'timezone' => 'America/Toronto',
		'hosts' => array(
			
			/*
			array(
				'db_host' 		=> 'localhost',
				'db_user' 		=> 'root',
				'db_pass' 		=> 'root',
				'mysqldump_location' => '/Applications/MAMP/Library/bin/mysqldump',
				'local_path'	=> dirname(__FILE__) . '/mysql-backups/',
				'ignore_db'		=> array('information_schema'),
				'send_s3'		=> false,
			)
			*/
			
			array(
				'db_host' 		=> 'internal-db.s62987.gridserver.com',
				'db_user' 		=> 'db62987',
				'db_pass' 		=> 'm1ke1990',
				
				'local_path'	=> dirname(__FILE__) . '/mysql-backups/',
				'ignore_db'		=> array('information_schema'),
				'send_s3'		=> false,
			)
		)
					
	);
	
	$backup = new backup();
	
	$backup->initialize($config);
	
	$backup->databases();