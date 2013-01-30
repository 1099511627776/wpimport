<?php

/* -------------------------------------------------------
 *
 *   LiveStreet (v1.0)
 *   Copyright © 2012 1099511627776@mail.ru
 *
 * --------------------------------------------------------
 *
 *   Contact e-mail: 1099511627776@mail.ru
 *
  ---------------------------------------------------------
*/

class PluginWpimport_ModuleLstopic extends Module
{
    protected $oMapper;

	public function Init(){
		$this->oMapper = Engine::GetMapper(__CLASS__, 'lstopic');
	}                       	

	public function getTopicByWpId($id){

		if($iId = $this->oMapper->getTopicByWPId($id)){
			return $this->Topic_getTopicById($iId);
		}
		return false;
	}

	public function setTopicWpId($oTopic,$id){
		return $this->oMapper->setTopicWpId($oTopic->getId(),$id);
	}

}

?>