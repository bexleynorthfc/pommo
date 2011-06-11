<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class poMMoPackage extends Package{
        
        function poMMoPackage(){
            $this->Package();

            $this->is_active = true;
			
			include_once('poMMoContactContainer.php');
            include_once(dirname(__FILE__)."/poMMoImporter.php");
            include_once("poMMoExporter.php");
            
            if ($this->is_active){
        
                $this->package_name = 'poMMo';
                $this->package_title = 'poMMo';
                $this->package_description = 'Manage Subscribers';
                $this->auth_level = USER_AUTH_EVERYONE;
            
				$this->db_prefix = DATABASE_PREFIX.'pommo_';
            
                $this->admin_pages = array();
                $this->admin_pages['main'] = array('url' => 'admin/pommo_frame.php', 'title' => $this->package_title);
                $this->admin_pages['entry'] = array('url' => 'admin/admin_entry.php', 'title' => $this->package_title);
            
                $this->main_menu_page = 'entry';
            
				$this->isImportable = true;
				$this->isExportable = true;
                
                $this->loadUserConf();
            }

			/*
            global $poMMo;
            if (!is_a($poMMo,'Common')){
                // Load in poMMo.  Because fireup() starts a session, this needs to be called 
                // before anything is output.
                define('_IS_VALID', TRUE);

                global $bmdb;
                include_once(dirname(__FILE__).'/bootstrap.php');
                $this->poMMo = & fireup("loadConfig");
            }
            else{
                $this->poMMo = $poMMo;
            }
            $this->logger = & $this->poMMo->_logger;
            $this->dbo = & $this->poMMo->_dbo;
			*/
        }
        
		function getInstallSQL(){
			return dirname(__FILE__).'/install/sql.schema.default_fields.php';
		}
    }
    
    
?>