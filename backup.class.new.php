<?php

    class Mysql_Backup {
    
        private $config         = array();
        
        private $db_list        = array();
        private $dump_options   = array();
        
        public function __get($name)
        {
        
        }
        
        public function __construct($config = array())
        {
            if(!empty($config))
                $this->initialize($config);
        }
        
        public function initialize($config = array())
        {
            $default_config = array(
                'mysqldump_location'    => '/Applications/MAMP/Library/bin/mysqldump',
                'timezone'              => 'America/Toronto',
                'dump_options'          => '--quote-names --quick --add-drop-table --add-locks --allow-keywords --disable-keys --extended-insert --single-transaction --create-options --comments --net_buffer_length=16384'
            );
            
            $this->config = array_merge($default_config, $config);
            
            date_default_timezone_set($this->config['timezone']);
        }
        
        public function set_database_config($config = array())
        {
            $this->check_required_params($config, array('host', 'user', 'pass'));
            
            $this->config['database_configs'][] = $config; 
        }
        
        public function get_databases()
        {
            foreach($this->config['database_configs'] as $host)
            {
                
            }
        }
        
        public function backup_databases()
        {
        
        }
        
        public function get_config()
        {
            return $this->config;
        }
    
        private function check_required_params($params, $required_params)
        {
            $errors = 0;
            foreach($required_params as $param)
            {
                if(!isset($params[$param]))
                    $error++;
            }
            
            if($error > 0)
                throw new exception(sprintf('You\'re missing some required config params. You must provide %s.', implode(', ', $required_params)));
        }
    
    }