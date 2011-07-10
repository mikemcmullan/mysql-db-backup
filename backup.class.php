<?php

	class backup {
	
		public $config = array();
		public $db_list = array();
		public $errors = array();
		
		private $dump_options = "--quote-names --quick --add-drop-table --add-locks --allow-keywords --disable-keys --extended-insert --single-transaction --create-options --comments --net_buffer_length=16384";
		
		private $mysqldump_location = "/usr/bin/mysqldump";
	
		public function __construct($config = false)
		{
			if($config) $this->initialize($config);
		}
		
		public function initialize($user_config)
		{
			$sys_config = array(
				'timezone' => 'America/Toronto',
			);
			$config = array_merge($sys_config, $user_config);
			$this->config = $config;
			ini_set('date.timezone', $this->config['timezone']);
		}
				
		public function get_db_list()
		{
			$i = 0;
			
			foreach($this->config['hosts'] as $host):
				
				$link = mysql_connect($host['db_host'], $host['db_user'], $host['db_pass']);
				
				if(!$link) {
					$this->errors[] = "Could not connect to {$host['db_host']}";
					return false;
				}
							
				if(!isset($host['db_name'])) {

					$list = mysql_list_dbs($link);
					
					while($dbs = mysql_fetch_object($list)) {
					
						if(isset($host['ignore_db']) && is_array($host['ignore_db']))
							(!in_array($dbs->Database, $host['ignore_db'])) ? $this->db_list[$i]['host'][] = $dbs->Database : '';
						else
							$this->db_list[$i]['host'][] = $dbs->Database;
						
					}
					
					unset($list, $dbs, $host['db_name']);
					
					$this->db_list[$i]['config'] = $host;
					
				} else {
				
					if(!is_array($host['db_name'])) {
					
						$check_db = mysql_select_db($host['db_name'], $link);
						
						if($check_db)
							$this->db_list[$i]['host'][] = $host['db_name'];
						else {
							 $this->errors[] = "{$host['db_name']} does not exist.";
							 return false;	 
						}
						
					} else {
						
						foreach($host['db_name'] as $db_name) {
							
							$check_db = mysql_select_db($db_name, $link);
						
							if($check_db)
								$this->db_list[$i]['host'][] = $db_name;
							else {
								 $this->errors[] = "{$db_name} does not exist.";
								 return false;
							}
							
						}
						
					}
						
					unset($host['db_name']);
					$this->db_list[$i]['config'] = $host;
					
				}
				
				mysql_close($link);
				
				$i++;
				
			endforeach;
			
			return $this->db_list;
		}
		
		public function databases()
		{
			$this->get_db_list();
			
			if(!empty($this->errors)) {
				print_r($this->errors);
				return false;
			}
			
			/*
			echo "<pre>";
			print_r($this->config);
			echo "</pre>";
			
			return false;
			*/
			
			foreach($this->db_list as $host):
				
				foreach($host['host'] as $db) {
				
					$year = date('Y');
					$month = date('m');
					$day = date('d');

					(!is_dir($host['config']['local_path'])) ? mkdir($host['config']['local_path']) : '';
					(!is_dir($host['config']['local_path'] . $year)) ? mkdir($host['config']['local_path'] . $year) : '';
					(!is_dir($host['config']['local_path'] . $year . '/' . $month)) ? mkdir($host['config']['local_path'] . $year . '/' . $month) : '';
					(!is_dir($host['config']['local_path'] . $year . '/' . $month . '/' . $day)) ? mkdir($host['config']['local_path'] . $year . '/' . $month . '/' . $day) : '';
					
					$new_path = $host['config']['local_path'] . $year . '/' . $month . '/' . $day . '/';
					
					$mysql_dump = (isset($host['config']['mysqldump_location']) && $host['config']['mysqldump_location']) ? 
									$host['config']['mysqldump_location'] : $this->mysqldump_location;
					
					if(file_exists("{$new_path}{$db}.sql.bz2")) `rm {$new_path}{$db}.sql.bz2`;
					
					`{$mysql_dump} {$this->dump_options} --host={$host['config']['db_host']} --user={$host['config']['db_user']} --password='{$host['config']['db_pass']}' {$db} | bzip2  > {$new_path}{$db}.sql.bz2`;

					
					echo "{$new_path}{$db}.sql.bz2\n";
				
				}
			
			endforeach;
		}

	}