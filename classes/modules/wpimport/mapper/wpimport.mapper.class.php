<?php
class PluginWpimport_ModuleWpimport_MapperWpimport extends Mapper
{

/*    public function getUserList($prefix)
    {
        $sql = "SELECT 
            id,
            user_nicename as 'username',
            user_login as 'login',
            display_name as 'name',
            user_email as 'email',
            user_url as 'url'
            FROM ".$prefix."_users";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
            $aReturn[$aRow['id']] = $aRow;
            }
            return $aReturn;
        }
        return false;
    }*/

    public function getUserList($prefix,&$iCount,$iPage,$iPerPage)
    {
        $sql = "SELECT 
            id,
            user_nicename as 'username',
            user_login as 'login',
            display_name as 'name',
            user_email as 'email',
            user_url as 'url'
            FROM ".$prefix."_users";
        $aReturn = array();
        if ($aRows = $this->oDb->selectPage($iCount,$sql,($iPage-1)*$iPerPage,$iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['id']] = $aRow;
            }
            return $aReturn;
        }
        return false;
    }

    public function getUser($prefix,$uid)
    {
    $sql = "SELECT 
        id,
        user_nicename as 'username',
        user_url as 'url',
        user_login as 'login',
        display_name as 'name',
        user_email as 'email',
        'other' as 'gender',
        user_registered as 'registerDate'
        FROM ".$prefix."_users
        where id = ".intval($uid);

    if ($aRows = $this->oDb->select($sql)) {
        foreach ($aRows as $aRow) {
            $aReturn[$aRow['id']] = $aRow;
        }
        return $aReturn;
    }

    return false;
    }

    public function getCat($prefix,$cid) {
        $sql = "SELECT 
                    tt.term_taxonomy_id as 'id',
                    t.name as 'name',
                    t.slug as 'alias',
                    tt.description as 'description'
                FROM ".$prefix."_term_taxonomy tt
                    left outer join ".$prefix."_terms t ON tt.term_id = t.term_id
                where tt.taxonomy = 'category' and tt.term_taxonomy_id = ".intval($cid);
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['id']] = $aRow;
                $aReturn[$aRow['id']]['alias'] = htmlspecialchars($aReturn[$aRow['id']]['alias']);
                $aReturn[$aRow['id']]['name'] = htmlspecialchars($aReturn[$aRow['id']]['name']);
                $aReturn[$aRow['id']]['description'] = htmlspecialchars($aReturn[$aRow['id']]['description']);              
            }
            return $aReturn;
        }

        return false;

    }
    public function getCatList($prefix) {
        $sql = "SELECT 
                    tt.term_taxonomy_id as 'id',
                    t.name,
                    t.slug as 'alias',
                    tt.description as 'description'                     
                FROM ".$prefix."_term_taxonomy tt
                    left outer join ".$prefix."_terms t ON tt.term_id = t.term_id
                WHERE tt.taxonomy = 'category'";
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['id']] = $aRow;
                $aReturn[$aRow['id']]['alias'] = htmlspecialchars($aReturn[$aRow['id']]['alias']);
                $aReturn[$aRow['id']]['name'] = htmlspecialchars($aReturn[$aRow['id']]['name']);
                $aReturn[$aRow['id']]['description'] = htmlspecialchars($aReturn[$aRow['id']]['description']);              
            }
            return $aReturn;
        }

        return false;

    }

    public function getTopicCount($prefix) {
        $sql = "SELECT 
                 count(p.id) as 'count'
                FROM ".$prefix."_posts p
                 left outer join ".$prefix."_term_relationships tr on p.id = tr.object_id
                 left outer join ".$prefix."_term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
                 left outer join ".$prefix."_terms t on t.term_id = tt.term_id
                where tt.taxonomy = 'category'";
        if($aRow = $this->oDb->select($sql)) {          
            return $aRow[0]['count'];
        }
        return 1;
    }

    public function getTopicList($iPage,$iPerPage) {
        $sql = "SELECT 
                     p.id, 
                     post_title as 'title', 
                     usr.user_login as 'created_by',
                     '' as 'hash'
                    FROM ".Config::Get('plugin.wpimport.wp_prefix')."_posts p
                     left outer join ".Config::Get('plugin.wpimport.wp_prefix')."_users usr on usr.id = p.post_author
                     left outer join ".Config::Get('plugin.wpimport.wp_prefix')."_term_relationships tr on p.id = tr.object_id
                     left outer join ".Config::Get('plugin.wpimport.wp_prefix')."_term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
                     left outer join ".Config::Get('plugin.wpimport.wp_prefix')."_terms t on t.term_id = tt.term_id
                    where p.post_type <> 'revision' and tt.taxonomy = 'category' group by p.id order by p.id asc LIMIT ?d, ?d";
        /*if(isset($page) && isset($pagesize)) {
            $sql .= ' limit '.intval($page-1)*intval($pagesize).",".intval($pagesize);
        }*/
        $iCount = 0;
        if ($aRows = $this->oDb->selectPage($iCount,$sql,($iPage-1)*$iPerPage,$iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['id']] = $aRow;
                $aReturn[$aRow['id']]['title'] = htmlspecialchars($aReturn[$aRow['id']]['title']);
            }
            return array('count'=>$iCount,'collection'=>$aReturn);
        }

        return false;
    }

    public function getPages(&$iCount,$iPage,$iPerPage) {
        $sql = "SELECT 
                     p.id, 
                     post_title as 'title', 
                     post_author as 'created_by',
                     '' as 'hash'
                    FROM ".Config::Get('plugin.wpimport.wp_prefix')."_posts p
                    where p.post_type = 'page' order by p.id asc LIMIT ?d, ?d";
        if ($aRows = $this->oDb->selectPage($iCount,$sql,($iPage-1)*$iPerPage,$iPerPage)) {
            return $aRows;
        }
        return false;
    }

    public function getComments($prefix,$cid) {
        $sql = "
            SELECT 
                comment_id as 'id',
                u.user_login as 'login',
                comment_parent as 'parent',
                user_id as 'userid',
                comment_author as 'name',
                comment_author_email as 'email',
                comment_author_ip as 'ip',
                comment_date as 'date',
                comment_content as 'comment' 
            FROM `".$prefix."_comments` c
                left outer join ".$prefix."_users u on u.id = c.user_id
            WHERE comment_post_ID = ?d ORDER BY comment_id ASC, comment_parent ASC";
        if ($aRows = $this->oDb->select($sql,$cid)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['parent']][$aRow['id']] = $aRow;
            }
            return $aReturn;
        }

        return false;
    }

    public function getTopic($prefix,$tid) {
        $sql = "SELECT 
                     p.id, 
                     post_title as 'title', 
                     t.name as 'cat',
                     post_date as 'date',
                     post_content as 'introtext',
                     '' as'fulltext',
                     '' as 'gallery',
                     '' as 'video',
                     usr.user_login as 'created_by',
                     (select coalesce(group_concat(t1.name separator ','),'') from ".$prefix."_terms t1
                       left outer join ".$prefix."_term_taxonomy tt1 on tt1.term_id = t1.term_id
                       left outer join ".$prefix."_term_relationships tr1 on tr1.term_taxonomy_id = tt1.term_taxonomy_id     
                       where tr1.object_id = p.id and tt1.taxonomy = 'post_tag') as 'tags'
                    FROM ".$prefix."_posts p
                     left outer join ".$prefix."_users usr on p.post_author = usr.id
                     left outer join ".$prefix."_term_relationships tr on p.id = tr.object_id
                     left outer join ".$prefix."_term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
                     left outer join ".$prefix."_terms t on t.term_id = tt.term_id
                    where tt.taxonomy = 'category' and p.id = ?d group by p.id";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql,$tid)) {
            foreach ($aRows as $aRow) {
                $aReturn[$aRow['id']] = $aRow;
                $aReturn[$aRow['id']]['title'] = htmlspecialchars($aReturn[$aRow['id']]['title']);
            }
            return $aReturn;
        }

        return false;
    }
    public function getTopicIdByAlias($sAlias,$prefix){
        $sql = "SELECT id FROM ".$prefix."_posts WHERE post_name = ?";
        if($aRows = $this->oDb->select($sql,$sAlias)){
            return $aRows[0]['id'];
        } else {
            return false;
        }
    }

    public function GetUserParam($iUserId,$sParam,$prefix){
        $sql = "SELECT meta_value FROM ".$prefix."_usermeta WHERE user_id = ?d and meta_key = ?";
        if($aRow = $this->oDb->selectRow($sql,$iUserId,$sParam)){
            return $aRow['meta_value'];
        }
        return false;
    }

    public function GetTopicParam($iTopicId,$sParam,$prefix){
        $sql = "SELECT meta_value FROM ".$prefix."_postmeta WHERE post_id = ?d and meta_key = ?";
        if($aRow = $this->oDb->selectRow($sql,$iTopicId,$sParam)){
            return $aRow['meta_value'];
        }
        return false;
    }
}
?>