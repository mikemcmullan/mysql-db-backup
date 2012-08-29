<?php

    class Mysql_Backup {
        
        /**
         * config
         * 
         * (default value: array())
         * 
         * @var array
         * @access private
         */
        private $config         = array();
        
        /**
         * __construct
         *
         * Is called when the class is first called. The config may
         * be passed here, if so the initialize method will be called.
         * 
         * @access public
         * @param array $config (default: array())
         * @return void
         */
        public function __construct($config = array())
        {
            if(!empty($config))
                $this->initialize($config);
        }
        
        /**
         * Initialize
         *
         * Is used to setup the class instead of the __construct method.
         *
         * @access public
         * @param array $config (default: array())
         * @return void
         */
        public function initialize($config = array())
        {
            $default_config = array(
                'mysqldump_location'    => '/usr/bin/mysqldump',
                'timezone'              => 'America/Toronto',
                'dump_options'          => '--quote-names --quick --add-drop-table --add-locks --allow-keywords --disable-keys --extended-insert --single-transaction --create-options --comments --net_buffer_length=16384',
                'ignore_db'             => array('information_schema', 'performance_schema', 'mysql'),
                'local_path'            => dirname(__FILE__) . '/mysql-backups/'
            );
            
            // Merge the default config with the users config.
            $this->config = array_merge($default_config, $config);
            
            date_default_timezone_set($this->config['timezone']);
        }
        
        /**
         * Set Database Host
         *
         * Is used to setup a database host. This method may be called multiple times
         * to add multiple hosts.
         *
         * Config example:
         *
         * $config = array(
         *     'db_host' => 'localhost',
         *     'db_user' => 'root',
         *     'db_pass' => 'root',
         *     'db_name' => array('db_name1', 'db_name2') // May also be a string with one db name
         *     'ignore_db' => array('db_name3', 'db_name4') // May also be a string with one db name
         * );
         * 
         * @access public
         * @param array $config (default: array())
         * @return void
         */
        public function set_database_host($config = array())
        {
            // Make sure the required params are present.
            $this->check_required_params($config, array('db_host', 'db_user', 'db_pass'));
            
            // If db_name is set then type cast it to an array to ensure it in one.
            if(isset($config['db_name']))
                $config['db_name'] = (array) $config['db_name'];
            
            // If ignore_db is set then type cast it to an array and merge it with the global ignore_db array.
            if(isset($config['ignore_db']))
                $config['ignore_db'] = array_merge((array) $this->config['ignore_db'], (array) $config['ignore_db']);
            else
                $config['ignore_db'] = $this->config['ignore_db'];
            
            // If the local_path is not set for the host use the global config local_path.
            if(!isset($config['local_path']))
                $config['local_path'] = $this->config['local_path'];
            
            // If dump_options is not set for the host use the global config dump_options.
            if(!isset($config['dump_options']))
                $config['dump_options'] = $this->config['dump_options'];
                        
            $this->config['hosts'][] = $config; 
        }
               
        /**
         * Get Databases
         *
         * Create the list of databases that will be used when backing them
         * up. The database list will be added to the config hosts array under
         * the corresponding host. Ie. $this->config['hosts']['host_id_int']['databases']
         *
         * @access public
         * @return boolean
         */
        public function get_databases()
        {
            foreach($this->config['hosts'] as $key => $host)
            {
                // Try and connect to the database.
                $connection = @mysql_connect($host['db_host'], $host['db_user'], $host['db_pass']);
                
                if(!$connection)
                    throw new exception("Could not connect to {$host['db_host']} " . mysql_error());
                
                // If the db name isn't set or is an empty array get all the databases available to the user.
                if(!isset($host['db_name']) || empty($host['db_name']))
                {
                    // Get a list of of databases available to the current user.
                    $db_list = mysql_list_dbs($connection);
                                        
                    while($dbs = mysql_fetch_object($db_list))
                    {
                        // Check to see if the database is in the list of dbs to ignore.
                        if(isset($host['ignore_db']) && is_array($host['ignore_db']))
                            (!in_array($dbs->Database, $host['ignore_db'])) && $this->config['hosts'][$key]['databases'][] = $dbs->Database;
                        else
                           $this->config['hosts'][$key]['databases'][] = $dbs->Database;
                    }                    
                }
                else
                {
                    // Go through each db name to make sure the database exists.
                    foreach($host['db_name'] as $db_name)
                    {        
                        $check_db = @mysql_select_db($db_name, $connection);
                        
                        if($check_db)
                            $this->config['hosts'][$key]['databases'][] = $db_name;
                        else
                             throw new exception("Database '{$db_name}' does not exist.");
                    }
                }
            }
            
            return true;
        }
        
        /**
         * Backup Databases
         *
         * Do the actual work of backing up the database using the mysqldump command.
         * 
         * @access public
         * @return void
         */
        public function backup_databases()
        {
            if($this->get_databases())
            {            
                foreach($this->config['hosts'] as $host)
                {
                    //print_r($host);
                    $dir = $this->create_directory_structure($host['local_path']);
                    $filename_base = date('Y-m-d_h-i-s-');
                            
                    foreach($host['databases'] as $db)
                    {           
                        $mysql_dump = $this->config['mysqldump_location'];
                        $filename   = "{$filename_base}{$db}.sql.bz2";

                        $path       = $dir . $filename;
                        $esc_path   = escapeshellarg($path);
                        
                        if(file_exists($path))
                            `rm {$esc_path}`;
                        
                        `{$mysql_dump} {$host['dump_options']} --host={$host['db_host']} --user={$host['db_user']} --password='{$host['db_pass']}' {$db} | bzip2  > {$esc_path}`;
        
                        echo "{$path}\n";
                    }
                }
            }
        }
        
        /**
         * Create Directory Structure
         *
         * Create all the directories in the pattern of year/month/day
         * inside of the $local_path folder set in the config.
         * 
         * @access private
         * @param mixed $local_path
         * @return void
         */
        private function create_directory_structure($local_path)
        {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
            
            $local_path = rtrim($local_path, '/') . '/';
            
            (!is_dir($local_path)) ? mkdir($local_path) : '';
            (!is_dir($local_path . $year)) ? mkdir($local_path . $year) : '';
            (!is_dir($local_path . $year . '/' . $month)) ? mkdir($local_path . $year . '/' . $month) : '';
            (!is_dir($local_path . $year . '/' . $month . '/' . $day)) ? mkdir($local_path . $year . '/' . $month . '/' . $day) : '';
            
            return $local_path . $year . '/' . $month . '/' . $day . '/';
        }
        
        /**
         * Check Required Params
         *
         * Is used to make sure all the required params are present.
         *
         * @access private
         * @param mixed $params
         * @param mixed $required_params
         * @return void
         */
        private function check_required_params($params, $required_params)
        {
            $errors = 0;
            foreach($required_params as $param)
            {
                if(!isset($params[$param]))
                    $errors++;
            }
            
            if($errors > 0)
                throw new exception(sprintf('You\'re missing some required config params. You must provide %s.', implode(', ', $required_params)));
        }
    
    }