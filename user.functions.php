<?php
    include_once(PACKAGE_DIRECTORY.'Common/UserFunction.php');
    
    class poMMo__UserFunctions extends UserFunction{
        
        function poMMo__UserFunctions(){
            global $Bootstrap;
            $this->setPackage('poMMo');
            $Bootstrap->usePackage('poMMo');
            
            $this->setFunctionParameters(); // See below
        }
    
        function poMMo__UserPaint($parms,$package,&$smarty){
            
            switch ($parms['subject']){
            case 'mini_subscribe':
                $url = array('base' => $package->getPackageURL());
                $smarty->assign('url',$url);
                $return = $smarty->fetch(dirname(__FILE__).'/themes/default/subscribe/form.mini.tpl');
                break;
            case 'subscribe':
                $url = array('base' => $package->getPackageURL());
        		global $bmdb;
				define('_IS_VALID',true);
		        require_once(dirname(__FILE__).'/bootstrap.php');
				require_once('inc/db_fields.php');
		
		        $poMMo = & fireup();
		        $dbo = & $poMMo->_dbo;
				$fields = dbGetFields($dbo,'active');
				if (!empty($fields))
					$smarty->assign('fields', $fields);
		
				// process.php appends serialized values to _GET['input']
				if ($poMMo->get('pommo_input')) {
					$smarty->assign(unserialize($poMMo->get('pommo_input')));
				}
		
				if (defined('bm_PasswordField')){
				    $smarty->assign('usingPassword',true);
				}
				if (defined('bm_ParentEmailField')){
				    $smarty->assign('usingParentEmail',true);
				}
				$smarty->assign($_POST);
                $smarty->assign('url',$url);
				$smarty->plugins_dir[] = bm_baseDir.'/inc/smarty-plugins/gettext';
				$return = '<style type="text/css">#subscribeForm .required { font-weight: bold; }</script>'."\n";
                $return.= $smarty->fetch(dirname(__FILE__).'/themes/default/subscribe/form.subscribe.tpl');
                break;
			case 'archives':
                $per_page = 25;
                
        		global $bmdb;
				define('_IS_VALID',true);
		        require_once(dirname(__FILE__).'/bootstrap.php');
                require_once (dirname(__FILE__).'/inc/db_history.php'); // Mailing History Database Handling     

				$NewsletterGroupsToDisplay = array('All Subscribers');
				if (function_exists('apply_filters')){
					$NewsletterGroupsToDisplay = apply_filters('pommo_groups_for_archives',$NewsletterGroupsToDisplay);
				}
                
		        $poMMo = & fireup();
		        $dbo = & $poMMo->_dbo;

                // Show all mailings
                $start = 0;
                $limit = 99999999; // a very large number, to ensure we get all rows
                $sortBy = 'started';
                $sortOrder = 'desc';      
                $mailings = & dbGetMailingHistory($dbo, $start, $limit, $sortBy, $sortOrder); // func in inc/db_history.php

                $PreviousMailingID = null;
                $NextMailingID = null;
                $FoundIt = false;
                foreach ($mailings as $key => $mailing){                    
                    if (!in_array($mailing['mailgroup'],$NewsletterGroupsToDisplay)){
                        unset($mailings[$key]);
                    }
                    else{
                        if (isset($_GET['mailing']) and $mailing['mailid'] == $_GET['mailing']){
                            $tmp = & dbGetMailingInfo($dbo,$mailing['mailid']);
                            if (is_array($tmp)){
                                $MailingToShow = current($tmp);
                                $MailingToShow['started'] = $mailing['started'];
                                $FoundIt = true;
                            }
                        }
                        elseif(!$FoundIt){
                            $PreviousMailingID = $mailings[$key]['mailid'];
                        }
                        elseif(!$NextMailingID){
                            $NextMailingID = $mailing['mailid'];
                        }
                    }                        
                }
                
				if (function_exists('apply_filters')){
					// We're in WordPress
					$SelfURL = apply_filters('pommo_archives_url','http://'.$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL']);
				}
				else{
					$SelfURL = $_SERVER['SCRIPT_NAME'];
				}
                $smarty->assign('SelfURL',$SelfURL);

                if (!isset($MailingToShow)){
                    $CurrentPage = (isset($_GET['page']) ? $_GET['page'] : 1);
                    $TotalPages = ceil(count($mailings) / $per_page);
                    $smarty->assign('TotalPages',$TotalPages);
                    $smarty->assign('CurrentPage',$CurrentPage);
                    $smarty->assign('Mailings',array_slice($mailings,$per_page * ($CurrentPage - 1),$per_page));
                    $Template = "pommo.history.tpl";
                }
                else{
                    $smarty->assign('Mailing',$MailingToShow);
                    $smarty->assign('PreviousMailingID',$PreviousMailingID);
                    $smarty->assign('NextMailingID',$NextMailingID);
                    
                    $Template = "pommo.mailing.tpl";
                }                
                $smarty->assign('parms',$parms);
                $return = $smarty->fetch(dirname(__FILE__).'/smarty/'.$Template);
				break;
            default:
                break;
            }
            
            return $return;
            
        }
        
        function setFunctionParameters(){
            $Subject = new FunctionParameter('paint','subject');
            $Subject->setParameterName('Subject');
            $Subject->setParameterDescription('The item you wish to paint');
            $Subject->addParameterValues(array('mini_subscribe' => 'Mini Subscribe Form'));
            $Subject->setParameterDefaultValue('mini_subscribe');

            $this->addFunctionParameter($Subject);
        }
        
    }
    
	if (!function_exists('do_translate')){
		function do_translate ($params, $content, &$smarty, &$repeat)
		{
			return __($content);
		}	
	}
?>