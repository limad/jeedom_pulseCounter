<?php

trait pulseCounterTrait {

/////////////////////////////////////*********************///////////////////////////////////// 
    public function makeCmd($eqLogicId=NULL) {
		log::add('pulseCounter', 'info', '        '.__FUNCTION__ .' started ********* '.$this->getName());
		$eqLogicId= $this->getLogicalId();
		
		$cmds =[];
      	$eqType = $this->getConfiguration('type');
      	$typeCmds = $this->loadEqConfig($eqType);
      	$communCmds = $this->loadEqConfig('commun');
    	$commands = array_merge($typeCmds, $communCmds);
		
    
//  ************************************************* //
  		log::add('pulseCounter', 'debug','          >>> '. __FUNCTION__ .' cmds for: '.$this->getName()." : ".json_encode($commands));
   		if(!$commands || empty($commands) ){
          	log::add('pulseCounter', 'debug','          '. __FUNCTION__ .' !commands ');
          	return false;
        }
      	else{
          	$importCmds = $this->createOrUpdateCmds($commands);
          	log::add('pulseCounter', 'debug','          '. __FUNCTION__ .' fin import '.$importCmds);
        }
      	
	/* ********************* */
      	return "ok";
    }
/////////////////////////////////////*********************/////////////////////////////////////	
	private function loadEqConfig($eqtype=null,$what=NULL) {
        //log::add('pulseCounter', 'debug','		'. __FUNCTION__ .' started ***************** '.$eqtype);
  		if (!$eqtype) {
             $eqtype = $this->getConfiguration('type', '');//
        }
      	$configFile = __DIR__  . '/../config/pulseCounter_config.json';
        if (!file_exists($configFile)) {
            log::add('pulseCounter', 'error', __FUNCTION__ .' Fichier de configuration introuvable !');
        }

        if(self::$_eqConfig == null){
			$eqConfig = json_decode(file_get_contents($configFile), true);
          	$msg_err =  __FUNCTION__ .':: Fichier de configuration inexploitable ! ';
          	if(!$eqConfig) throw new Exception(__($msg_err, __FILE__));
            self::$_eqConfig = $eqConfig;
		}
        $eqConfig = self::$_eqConfig;
        
    	$commands = $eqConfig[$eqtype]['commands'];
		if(empty($commands)){
          	$msg_err =  __FUNCTION__ .':: No commands found in config for type : "'.$eqtype.'"';
          	log::add('pulseCounter', 'warning',$msg_err);
          	throw new Exception(__($msg_err, __FILE__));
          	return null;
        }
      	log::add('pulseCounter', 'debug', __FUNCTION__ ." commands ($eqtype) : ". json_encode($commands));
    	return $commands;
        
    }
/////////////////////////////////////*********************///////////////////////////////////// 
    private function createOrUpdateCmds(array $commands, $isUpdate = false) {
      //log::add(__CLASS__, 'debug', __FUNCTION__.' start for : '.$this->getName() );
		$cmd_order = 0;
		$link_cmds = array();
		$link_actions = array();
		$cmdsToRemove = [];
      	log::add(__CLASS__, 'debug', '          '.__FUNCTION__.' start commands: '.json_encode($commands));
		foreach ($commands as $cmdlogid => $command){
			$cmd = $this->getCmd(null, $command["logicalId"]);
			if (!is_object($cmd)) {
				//log::add(__CLASS__, 'debug', 'create: '.$this->getName()." ".$command["logicalId"].'/'.$command["name"]);
				$cmd = new cmd();
              	if(isset($command["order"])) {
					$cmd_order = $command["order"];
				}
              	$cmd->setOrder($cmd_order);
				$cmd->setLogicalId($command["logicalId"]);
				$cmd->setEqLogic_id($this->getId());
				$cmd->setName(__($command["name"], __FILE__));
				if(isset($command["isHistorized"])) {
					$cmd->setIsHistorized($command["isHistorized"]);
				}
				if(isset($command["isVisible"])) {
					$cmd->setIsVisible($command["isVisible"]);
				}
				if (isset($command['template'])) {
					foreach ($command['template'] as $key => $value) {
						$cmd->setTemplate($key, $value);
					}
				}
              	if (isset($command['display'])) {
                    foreach ($command['display'] as $key => $value) {
                        if ($key=='title_placeholder' || $key=='message_placeholder') {
                            $value = __($value, __FILE__);
                        }
                        $cmd->setDisplay($key, $value);
                    }
				}
			}//if (!is_object($cmd))
          
          
          
			$cmd->setType($command["type"]);
			$cmd->setSubType($command["subType"]);
			if(isset($command["generic_type"])) {
				$cmd->setGeneric_type($command["generic_type"]);
			}
			
			if(isset($command["unite"])) {
				$cmd->setUnite($command["unite"]);
			}

			if (isset($command['configuration'])) {
				foreach ($command['configuration'] as $key => $value) {
					$cmd->setConfiguration($key, $value);
                  	if ($key == 'updateCmdId'){
                      	$link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
                    }
                  	if ($key == 'valueLogicalId'){
                      	$link_cmds[$command["logicalId"]] = $value;
              		}
				}
			}

			if (isset($command['value'])) {
				//$link_cmds[$command["logicalId"]] = $command['value'];
              	//log::add(__CLASS__, 'warning', __FUNCTION__.' set link_cmd: '.$command["logicalId"].' to '.$command['value']);
			}
			$cmd->save();
          
			if (isset($command['initialValue'])) {
				$cmdValue = $cmd->execCmd();
				if ($cmdValue=='') {
					$this->checkAndUpdateCmd($command["logicalId"], $command['initialValue']);
				}
			}
          	$cmd_order++;
		}

		foreach ($link_cmds as $cmd_logicalId => $link_logicalId) {
			$cmd = $this->getCmd(null, $cmd_logicalId);
			$linkCmd = $this->getCmd(null, $link_logicalId);
			//log::add(__CLASS__, 'warning', __FUNCTION__.' link_cmd: '.$cmd_logicalId.'=>'.$link_logicalId);
			if (is_object($cmd) && is_object($linkCmd)) {
				$cmd->setValue($linkCmd->getId());
				$cmd->save();
			}
		} 
      	foreach ($link_actions as $cmd_logicalId => $link_action) {
			$cmd = $this->getCmd(null, $cmd_logicalId);//action: slider,color,select
			$linkActionCmd = $this->getCmd(null, $link_action);
			if (is_object($cmd) && is_object($linkActionCmd)) {
				$cmd->setConfiguration('updateCmdId', $linkActionCmd->getId());
				$cmd->save();
			}
		}
		if($isUpdate){
          	log::add(__CLASS__, 'debug', __FUNCTION__.' updating commands list: '.$this->getName() );
        	foreach (($this->getCmd()) as $eqLogic_cmd) {
                  $exists = 0;
                  $eqLogic_cmdId = $eqLogic_cmd->getLogicalId();
                  $keyp_search = array_search($eqLogic_cmdId, array_column($commands, 'logicalId'));
                  if(!$keyp_search){
                      log::add(__CLASS__, 'warning', __FUNCTION__.' cmd('.$eqLogic_cmdId.') No More exist removing...');
                      $arrayToRemove[] = $eqLogic_cmd;
                      try {
                          $eqLogic_cmd->remove();
                      } catch (Exception $e) {
                      }
                  }
            }
      	}
      	$this->setConfiguration('cmdsMaked', true);
        //$this->save();
	}


}