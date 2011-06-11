<?php

    /**************************************************
    * This file gets included as part of the the object 
    * instantiation (as long as the conf.php file calls $this->loadUserConf();)
    *
    * Just specify additional variables that you want to associate with this package like:
    *
    *   $this->foo = "bar";
    *   $this->blah = array('red', 'green', 'blue');
    *
    **************************************************/
    
	$this->bm_BlankEmail = 'none';
	$this->bm_importAllowBlankRequired = true;
	$this->bm_adminAllowBlankRequired = true;
	$this->bm_exportWithSubscriberID = true;
	$this->bm_PasswordField = 'Password';
	$this->bm_ParentEmailField = 'Parent Email';

?>