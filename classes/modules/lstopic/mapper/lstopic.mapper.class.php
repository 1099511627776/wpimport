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

    public function setTopicWPId($topic_id,$id) {
        $sql = "UPDATE ".Config::Get('db.table.topic')." SET wp_id = ?d WHERE topic_id = ?d";
        if($this->oDb->query($sql,$id,$topic_id)){
            return true;
        }
        return false;
    }

    public function UpdateTopic($oTopic){
        $sql = "UPDATE ".Config::Get('db.table.topic')." 
            SET 
                blog_id= ?d,
                user_id= ?d,
                topic_title= ?,
                topic_tags= ?,
                topic_date_add = ?,
                topic_date_edit = ?,
                topic_user_ip= ?,
                topic_publish= ?d ,
                topic_publish_draft= ?d ,
                topic_publish_index= ?d,
                topic_rating= ?f,
                topic_count_vote= ?d,
                topic_count_vote_up= ?d,
                topic_count_vote_down= ?d,
                topic_count_vote_abstain= ?d,
                topic_count_read= ?d,
                topic_count_comment= ?d, 
                topic_count_favourite= ?d,
                topic_cut_text = ? ,
                topic_forbid_comment = ? ,
                topic_text_hash = ? 
            WHERE
                topic_id = ?d
        ";
        if ($this->oDb->query($sql,
                $oTopic->getBlogId(),
                $oTopic->getUserId(),
                $oTopic->getTitle(),
                $oTopic->getTags(),
                $oTopic->getDateAdd(),
                $oTopic->getDateEdit(),
                $oTopic->getUserIp(),
                $oTopic->getPublish(),
                $oTopic->getPublishDraft(),
                $oTopic->getPublishIndex(),
                $oTopic->getRating(),
                $oTopic->getCountVote(),
                $oTopic->getCountVoteUp(),
                $oTopic->getCountVoteDown(),
                $oTopic->getCountVoteAbstain(),
                $oTopic->getCountRead(),
                $oTopic->getCountComment(),
                $oTopic->getCountFavourite(),
                $oTopic->getCutText(),
                $oTopic->getForbidComment(),
                $oTopic->getTextHash(),
                $oTopic->getId())
        ) {
            return true;
        }
        return false;
    }

}
?>