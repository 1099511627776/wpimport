<?php
/* -------------------------------------------------------
 *
 *   LiveStreet (v1.0)
 *   Plugin Events for liveStreet 1.0.1
 *   Copyright © 2012 1099511627776@mail.ru
 *
 * --------------------------------------------------------
 *
 *   Contact e-mail: 1099511627776@mail.ru
 *
  ---------------------------------------------------------
*/


class PluginWpimport_ActionRouteWp extends ActionPlugin_Inherit_ActionError {

	protected function EventError() {
		$url = $this->getParam(0);
		$fullurl = $_SERVER['REQUEST_URI'];
		if(preg_match('/\/(\S+)\.html.*?/i',$fullurl,$matches)){
			$alias = $matches[1];
			if($iid = $this->PluginWpimport_wpimport_getTopicIdByAlias($alias)){
				if(!($oTopic = $this->PluginWpimport_lstopic_getTopicByK2Id($iid))){					
					$this->PluginWpimport_wpimport_addPost($iid);
					$this->PluginWpimport_wpimport_addComments($iid);
					if($oTopic = $this->PluginWpimport_lstopic_getTopicByK2Id($iid)){
						header('HTTP/1.1 301 Moved Permanently');
						header('Location: '.$oTopic->getUrl());				
						dump("I'm routing to:".$oTopic->getUrl());
						return;
					} else {
						parent::EventError();
					}
				} else {
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.$oTopic->getUrl());
					dump("I'm routing to:".$oTopic->getUrl());
					return;
				}		
			}
		} 
		parent::EventError();
	}
}

?>
