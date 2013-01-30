<?php

class PluginWpimport_ModuleLstopic_MapperLstopic extends Mapper
{

    public function getTopicByWPId($id)
    {
		$sql = "SELECT topic_id FROM ".Config::Get('db.table.topic')." WHERE wp_id = ?d";
		if ($aRows = $this->oDb->select($sql,$id)) {
		    foreach ($aRows as $aRow) {
				return $aRow['topic_id'];
		    }
		    return $aReturn;
		}
		return false;
	}

	public function setTopicK2Id($topic_id,$id) {
		$sql = "UPDATE ".Config::Get('db.table.topic')." SET wp_id = ?d WHERE topic_id = ?d";
		if($this->oDb->query($sql,$id,$topic_id)){
			return true;
		}
		return false;
	}

}
?>