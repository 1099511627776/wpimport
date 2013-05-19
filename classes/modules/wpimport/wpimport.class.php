<?php

/* -------------------------------------------------------
 *
 *   LiveStreet (v1.0)
 *   Plugin Conversion of the Joomla K2 (v.0.1)
 *   Copyright © 2012 1099511627776@mail.ru
 *
 * --------------------------------------------------------
 *
 *   Contact e-mail: 1099511627776@mail.ru
 *
  ---------------------------------------------------------
 */

set_time_limit(120);

class PluginWpimport_Modulewpimport extends Module
{

    protected $oMapper;
    protected $aUsersK2;
    protected $aCategoryK2;
    protected $aCommentsK2;
    protected $aPostsK2;
    
    public function Init()
    {
        $conn = $this->Database_GetConnect(Config::Get('plugin.wpimport.wpdb'));
        $this->oMapper = Engine::GetMapper(__CLASS__, 'wpimport', $conn);
    }
    private function clearComments($cid) {
        $conn = $this->Database_GetConnect();
        $conn->query('UPDATE '.Config::Get('db.table.topic').' SET topic_count_comment = 0 WHERE topic_id ='.intval($cid));
    }
    private function UploadImageByUrl($sUrl){
        if(!@getimagesize($sUrl)) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_TYPE;
        }
        $oFile=fopen($sUrl,'r');
        if(!$oFile) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_READ;
        }
        $iMaxSizeKb=Config::Get('view.img_max_size_url');
        $iSizeKb=0;
        $sContent='';
        while (!feof($oFile) and $iSizeKb<$iMaxSizeKb) {
            $sContent.=fread($oFile ,1024*1);
            $iSizeKb++;
        }
        if(!feof($oFile)) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_SIZE;
        }
        fclose($oFile);
        $sFileTmp=Config::Get('sys.cache.dir').func_generator();

        $fp=fopen($sFileTmp,'w');
        fwrite($fp,$sContent);
        fclose($fp);

        return $sFileTmp;
    }
    private function makeUser($oUser,$aUser){
        $oUser->setLogin($aUser['login']);
        $oUser->setMail($aUser['email']);
        $oUser->setProfileAbout($aUser['url']);
        $oUser->setDateRegister($aUser['registerDate']);
        $oUser->setActivate(1);
        $sPassword = func_generator(6);
        $oUser->setPassword(func_encrypt($sPassword));
        $oUser->setIpRegister(func_getIp());
        $oUser->setActivateKey(null);
        $oUser->setProfileSite($aUser['url']);
        $oUser->setProfileName($aUser['name']);
        $g = (trim($aUser['gender']) == 'm') ? 'man' : ((trim($aUser['gender']) == 'f') ? 'woman': 'other');
        $oUser->setProfileSex($g);
        if(array_key_exists('image',$aUser)){
            $sPhotoUrl = Config::Get('plugin.wpimport.wpsite').'/'.Config::Get('plugin.wpimport.wp_avatars').$aUser['image'];
            $sPhotoPath = $this->UploadImageByUrl($sPhotoUrl);
            if($sFileWeb = $this->User_UploadAvatar($sPhotoPath,$oUser)) {
                if ($sFileWeb!=$oUser->getProfileAvatar()) {
                    $this->User_DeleteAvatar($oUser);
                }
                $oUser->setProfileAvatar($sFileWeb);                
            };
        }
        //print "before";
        $this->User_Update($oUser);
        //print "after";
    }
    private function makeBlog($oBlog,$aCat) {
        $oBlog->setTitle(htmlspecialchars($aCat['name']));
        $oBlog->setType('open');
        $oBlog->setUrl($aCat['alias']);
        $oBlog->setDateAdd(date("Y-m-d H:i:s"));
        $oBlog->setDescription(htmlspecialchars($aCat['description']));
        $oBlog->setOwnerId(1);
        $oBlog->setLimitRatingTopic(0);
        $this->Blog_UpdateBlog($oBlog);
    }
    private function bbCode($sText){
        $sTextfull = preg_replace('/\[b\](.*?)\[\/b\]/i','<strong>$1</strong>',$sText);
        $sTextfull = preg_replace('/\[center\](.*?)\[\/center\]/i','$1',$sTextfull);
        $sTextfull = preg_replace('/\[block\](.*?)\[\/block\]/i','$1',$sTextfull);
        $sTextfull = preg_replace('/\[i\](.*?)\[\/i\]/i','<em>$1</em>',$sTextfull);
        $sTextfull = preg_replace('/\[u\](.*?)\[\/u\]/i','<u>$1</u>',$sTextfull);
        $sTextfull = preg_replace('/\[s\](.*?)\[\/s\]/i','<s>$1</s>',$sTextfull);
        $sTextfull = preg_replace('/\[del\](.*?)\[\/del\]/i','<s>$1</s>',$sTextfull);
        $sTextfull = preg_replace('/\[b\](.*?)\[\/b\]/i','<strong>$1</strong>',$sTextfull);
        $sTextfull = preg_replace('/\[img src=(.*?)\]/i','<img src=$1></img>',$sTextfull);
        $sTextfull = preg_replace('/\[a href=(.*?)\](.*?)\[\/a\]/i','<a href=$1>$2</a>',$sTextfull);
        $sTextfull = preg_replace('/\[quote(.*?)\](.*?)\[\/quote\]/i','<blockquote>$2</blockquote>',$sTextfull);
        return $sTextfull;
    }

    public function getUsers($uid = null){
        if($uid) {
            return $this->oMapper->getUser(Config::Get('plugin.wpimport.wp_prefix'),$uid);
        } else {
            $users = $this->oMapper->getUserList(Config::Get('plugin.wpimport.wp_prefix'));
            foreach($users as $id => $user) {
                if($this->User_GetUserById($id) || $this->User_GetUserByLogin($user['login'])) {
                    $users[$id]['status'] = 'exists';
                }
            }
            return  $users;
        }
    }
    public function ImportUsers(){
        set_time_limit(0);
        if($aUsers = $this->getUsers()){
            foreach($aUsers as $oUser){                     
                if(array_key_exists('image',$oUser)){
                    $this->Message_AddNotice($oUser['image']);
                }       
                $this->addUser($oUser['id']);
                //print_r($oUser);
            }
        }
    }
    public function getPosts($tid = null,$page=null,$pagesize=null) {
        if($tid) {
            return $this->oMapper->getTopic(Config::Get('plugin.wpimport.wp_prefix'),$tid);
        } else {
            $topics = $this->oMapper->getTopicList(Config::Get('plugin.wpimport.wp_prefix'),$page,$pagesize);
            $tpc = $topics['collection'];
            foreach($tpc as $id => $topic) {            
                if($oTopic = $this->Pluginwpimport_Modulelstopic_getTopicByWPId($id)) {
                    $tpc[$id]['status'] = 'exists';                 
                }
                /*$sMD5Match = $topic['hash'];
                if(preg_match('/^<img(.*?)src=\"(.*?)\">(.*?)/is', $sMD5Match, $aData)) {
                    $aMD5Match = $aData['2'];
                } else {
                    $aMD5Match = $sMD5Match;
                }
                $oUser = $this->User_GetUserByLogin($topic['created_by']);
                $uid = ($oUser) ? $oUser->getId() : 1;
                if($this->Topic_GetTopicUnique($uid,md5($aMD5Match))){
                    $tpc[$id]['status'] = 'exists';
                }*/
            }
            $topics['collection'] = $tpc;
            return  $topics;
        }
    }
    public function getCats($cid = null){
        if($cid) {
            return $this->oMapper->getCat(Config::Get('plugin.wpimport.wp_prefix'),$cid);
        } else {
            $cats = $this->oMapper->getCatList(Config::Get('plugin.wpimport.wp_prefix'));
            foreach($cats as $id => $cat) {
                if($this->Blog_GetBlogById($id)) {
                    $cats[$id]['status'] = 'exists';
                }
            }
            return  $cats;
        }
    }

    public function addCat($cid) {
        $cat = $this->getCats($cid);
        foreach($cat as $aid => $aCat) {
            $oBlog = $this->Blog_GetBlogByTitle($aCat['name']);
            if ($oBlog) {
                $this->makeBlog($oBlog,$aCat);
                return "updated";
            } else {
                $oBlog = Engine::GetEntity('Blog');
                $oBlog->setTitle($aCat['name']);
                $this->makeBlog($oBlog,$aCat);
                if ($this->Blog_AddBlog($oBlog)) {
                    $oBlog = $this->Blog_GetBlogByTitle($oBlog->getName());
                }
                return "created";
            }
        }
    }
    public function addUser($uid) {
        $user = $this->getUsers($uid);
        foreach($user as $aid => $aUser) {
            //$oUser = $this->User_GetUserById($aUser['id']);
            if($oUser = $this->User_GetUserByLogin(mb_substr($aUser['login'],0,30))){
                    $this->makeUser($oUser,$aUser);
                    return "updated";
            }
            else 
            {
                if (($aUser['login'] != '') && ($aUser['login'] != 'admin')) {
                    $oUser = Engine::GetEntity('User');
                    $this->makeUser($oUser,$aUser);
                    if ($this->User_Add($oUser)) {
                        $oUser = $this->User_GetUserById($oUser->getId());
                    }
                    $this->User_Update($oUser);
                    return "created";
                } else {
                    return print_r($user,true);
                }           
            }
        }
    }


    private function UploadTopicPhotoUrl($aImageUrl) {
        if(!is_array($aImageUrl)) {
            return false;
        }

        $sPath = Config::Get('path.uploads.images').'/topic/'.date('Y/m/d').'/';

        if (!is_dir(Config::Get('path.root.server').$sPath)) {
            mkdir(Config::Get('path.root.server').$sPath, 0755, true);
        }

        $iMaxSizeKb=Config::Get('view.img_max_size_url');
        $aUploadedImages = array();
        $aParams=$this->Image_BuildParams('photoset');
        dump('start loop');
        dump($aImageUrl);
        foreach($aImageUrl as $sUrl){

            $sFileName = func_generator(10);
            $sFileTmp = Config::Get('path.root.server').$sPath.$sFileName;
            $sFileOrig = Config::Get('path.root.server').$sPath.$sUrl['fname'];

            if(!@getimagesize($sUrl['url'])) {
                dump('error getimagesize');
                $this->Message_AddError($sUrl['url']." - not an image",$this->Lang_Get('error'));
                continue;
            }

            $oFile=fopen($sUrl['url'],'r');

            if(!$oFile) {
                dump('error opening stream');
                $this->Message_AddError($sUrl['url']." - error opening stream",$this->Lang_Get('error'));               
                continue;
            }

            $iSizeKb=0;
            $sContent='';
            while (!feof($oFile) and $iSizeKb<$iMaxSizeKb) {
                $sContent.=fread($oFile ,1024*1);
                $iSizeKb++;
            }
    
            if(!feof($oFile)) {
                $this->Message_AddError($sUrl['url']." - bigger than: ".$iMaxSizeKb.'Kb',$this->Lang_Get('error'));
                continue;
            }

            fclose($oFile);

            dump('creating:'.$sFileTmp);
    
            $fp=fopen($sFileTmp,'w');
            fwrite($fp,$sContent);
            fclose($fp);

            $oImage =$this->Image_CreateImageObject($sFileTmp);

            if($sError=$oImage->get_last_error()) {
                dump($sUrl['url']." error:".$sError);
                // Вывод сообщения об ошибки, произошедшей при создании объекта изображения
                $this->Message_AddError($sError,$this->Lang_Get('error'));
                @unlink($sFileTmp);
                continue;
            }
            if (($oImage->get_image_params('width')>Config::Get('view.img_max_width')) or ($oImage->get_image_params('height')>Config::Get('view.img_max_height'))) {
                $this->Message_AddError($this->Lang_Get('topic_photoset_error_size'),$this->Lang_Get('error'));
                @unlink($sFileTmp);
                continue;
            }
            /**
             * Добавляем к загруженному файлу расширение
             */
            $sFile=$sFileTmp.'.'.$oImage->get_image_params('format');
            rename($sFileTmp,$sFile);

            $aSizes=Config::Get('module.topic.photoset.size');
            foreach ($aSizes as $aSize) {
                /**
                 * Для каждого указанного в конфиге размера генерируем картинку
                 */
                $sNewFileName = $sFileName.'_'.$aSize['w'];
                $oImage = $this->Image_CreateImageObject($sFile);
                if ($aSize['crop']) {
                    $this->Image_CropProportion($oImage, $aSize['w'], $aSize['h'], true);
                    $sNewFileName .= 'crop';
                }
                $this->Image_Resize($sFile,$sPath,$sNewFileName,Config::Get('view.img_max_width'),Config::Get('view.img_max_height'),$aSize['w'],$aSize['h'],true,$aParams,$oImage);
            }
            dump($this->Image_GetWebPath($sFile));
            $aUploadedImages[] = $this->Image_GetWebPath($sFile);
        }
        return $aUploadedImages;
    }

    private function makeGallery($oTopic,$aTopic){  
        preg_match('/{gallery}\s*(\d+)\s*{\/gallery}/i',$aTopic['gallery'],$matches);
        //print_r($matches);
        if($galid = $matches[1]){
            dump('galid:'.$galid);
            $aFiles = array();
            if($handle = opendir(Config::Get('plugin.wpimport.joomla_fileroot').Config::Get('plugin.wpimport.joomla_galleryroot').$galid)) {
                while (false !== ($entry = readdir($handle) )){
                    if(preg_match('/.*?[jpg|png|gif]/i',$entry)){
                        //print Config::Get('plugin.wpimport.joomlasite').'/'.Config::Get('plugin.wpimport.joomla_galleryroot').$galid."/".$entry;
                        $aFiles[] = array(
                            'url' => Config::Get('plugin.wpimport.joomlasite').'/'.Config::Get('plugin.wpimport.joomla_galleryroot').$galid."/".$entry,
                            'fname' => $entry
                        );
                    }
                }
                closedir($handle);
            }
            //print_r($aFiles);
            $aUploadedImages = $this->UploadTopicPhotoUrl($aFiles);
            //print_r($aUploadedImages);
            dump($aUploadedImages);
            $oTopic->setType('photoset');
            $isFirst = false;
            if($this->Topic_UpdateTopic($oTopic)){
                foreach($aUploadedImages as $sFile){
                    $oPhoto = Engine::GetEntity('Topic_TopicPhoto');
                    $oPhoto->setPath($sFile);
                    $oPhoto->setTopicId($oTopic->getId());
                    if ($oPhoto = $this->Topic_addTopicPhoto($oPhoto)) {
                        if(!$isFirst){
                            $isFirst = true;
                            $oTopic->setPhotosetMainPhotoId($oPhoto->getId());
                        }
                        if (isset($oTopic)) {
                            $oTopic->setPhotosetCount($oTopic->getPhotosetCount()+1);
                        }                   
                    }
                    dump('photo');
                    dump($oPhoto);              
                }
                $this->Topic_UpdateTopic($oTopic);
            }
            //die('kukuku');
        }
    }
    private function makeTopic($oTopic,$aTopic) {
        $sTitle = str_replace('&amp;quot;','"',htmlspecialchars($aTopic['title']));
        $oTopic->setTitle($sTitle);
        //$oTopic->setId($aTopic['id']);
        $oTopic->setDateAdd($aTopic['date']);
        if($oUser = $this->User_GetUserByLogin($aTopic['created_by'])){
            $userid = $oUser->getId();      
        } else {
            $userid = 1;            
            $oUser = $this->User_GetUserById(1);
        }
        $oTopic->setUserId($userid);

        if($aTopic['gallery']){ 
            $oTopic->setType('photoset');
        } else {
            $oTopic->setType('topic');
        }

        $oTopic->setTags($aTopic['tags']);
        $sTextfull = $aTopic['introtext'].$aTopic['fulltext'];

        //images
        preg_match_all('/<img(.*?)src=\"(.*?)\"(.*?)>/is', $sTextfull, $aData, PREG_PATTERN_ORDER);
        $aImg = $aData['2'];
        dump($aImg);
        if (!empty($aImg)) {
            foreach ($aImg as $key => $sPath) {
                if(strpos($sPath,'://')===false){
                 $sPPath = Config::Get('plugin.wpimport.joomlasite').$sPath;
                } else {
                 $sPPath = $sPath;
                }
                $sPathNew = $this->Topic_UploadTopicImageUrl($sPPath, $oUser);

                $sTextfull = str_replace($sPath, $sPathNew, $sTextfull);
                $sDump = "key:{$key} url:{$sPath} url_new:{$sPathNew}";
                //dump($sDump);
                //dump($sTextfull);
                //print "key:{$key} url:{$sPath} url_new:{$sPathNew}\n";                
            }
        }
        //print_r($aImg);
        //die();
        //logo
        /*$sLogo = Config::Get('plugin.wpimport.joomlasite').'/media/k2/items/cache/'.md5("Image".$aTopic['id']).'_XL.jpg';
        $sPathNew = $this->Topic_UploadTopicImageUrl($sLogo, $oUser);
        $sTextfull = str_replace('&amp;quot;','"',$sTextfull);
        $sTextfull = '<img src="'.$sPathNew.'" style="width:50%; float:left; padding-right:10px; "/>'.$sTextfull;
        if($aTopic['video']){
            $sTextfull = $sTextfull."<br />".$aTopic['video'];
        }*/

        $sTextfull = '<p>'.$sTextfull.'</p>';
        $sTextfull = preg_replace('/\n\s/is','</p><p>',$sTextfull);
        $sTextfull = preg_replace('/<p>\s*<\/p>/is','',$sTextfull);

        //topic
        $oTopic->setTextSource($sTextfull);
        $oTopic->setTextShort($sTextfull);
        $oTopic->setText($sTextfull);
        //print_r($aTopic);
        if($oBlog = $this->Blog_GetBlogByTitle($aTopic['cat'])){
            $blogId = $oBlog->getId();
        } else {
            $blogId = $this->Blog_GetPersonalBlogByUserId($oUser->getId())->getId();    
        }
        $oTopic->setBlogId($blogId);
        $oTopic->setUserIp(func_getIp());
        $oTopic->setDateAdd($aTopic['date']);
        $oTopic->setPublish(1);
        $oTopic->setPublishIndex(1);
        $oTopic->setPublishDraft(1);
        $oTopic->setForbidComment(0);
        $oTopic->setTextHash(md5($aTopic['introtext']));
        dump($oTopic->getText());
        //dump($aTopic);
        //die('kukuukuk');
        $this->Topic_UpdateTopic($oTopic);
    }

    private function JoomlaPost2Topic($cid) {
        $post = $this->getPosts($cid);
        foreach($post as $aid => $aTopic) {
            $sMD5Match = $aTopic['introtext'];
            if(preg_match('/^<img(.*?)src=\"(.*?)\"(.*?)>(.*?)/is', $sMD5Match, $aData)) {
                $aMD5Match = $aData['3'];
            } else {
                $aMD5Match = $sMD5Match;
            }
            if($oUser = $this->User_GetUserByLogin($aTopic['created_by'])){
                $userid = $oUser->getId();
            } else {
                $userid = 257;
            }   
            return $this->Topic_GetTopicUnique($userid,md5($aMD5Match));
        }
    }
    private function findPid($comments,$pid) {
        foreach($comments as $parentid=>$comment) {
            foreach($comment as $cid=>$commbody) {
                if($pid == $commbody['id']) {
                    return $commbody['newid'];
                }
            }
        }
    }
    public function addComments($cid) {
        $comments = $this->oMapper->getComments(Config::Get('plugin.wpimport.wp_prefix'),$cid);
        if(!$comments){
            return "no comment";
        }
        $oTopic = $this->Pluginwpimport_Modulelstopic_getTopicByWPId($cid);
/*      print $tid."<br />";
        print $cid;

        $oTopic = $this->Topic_GetTopicById($tid);*/
        if($oTopic) {
            if(!$this->Comment_DeleteCommentByTargetId($oTopic->getId(),$oTopic->getType())) {
                return false;
            } else {
                $this->clearComments($oTopic->getId());
            }
            foreach($comments as $parentid => $comment) {
                foreach($comment as $cid => $commbody) {
                    $pid = $parentid;
                    if($pid != 0) {
                        $pid = $this->findPid($comments,$pid);
                    }
                    $lscomm = Engine::GetEntity('Comment');
                    $lscomm->setTargetId($oTopic->getId());
                    $lscomm->setTargetType($oTopic->getType());
                    $lscomm->setTargetParentId($oTopic->getBlog()->getId());
                    $oUser = $this->User_GetUserByLogin($commbody['login']);
                    if($oUser) {
                        $uid = $oUser->getId();
                    } else {
                        $uid = Config::Get('plugin.wpimport.anonymous_user');
                    }
                    $lscomm->setUserId($uid);
                    $ctext = $this->bbCode($commbody['comment']);
                    $lscomm->setText($ctext);
                    $lscomm->setDate($commbody['date']);
                    $lscomm->setUserIp($commbody['ip']);
                    $lscomm->setTextHash(md5($ctext));
                    if($pid != 0) {
                        $lscomm->setPid($pid);
                    }
                    $lscomm->setPublish(1);
                    $this->Comment_AddComment($lscomm);
                    $comments[$parentid][$cid]['newid'] = $lscomm->getId();
                }
            }
            return 'recreated';
        }
        return false;
    }

    public function addPost($tid) {
        $post = $this->getPosts($tid);
        foreach($post as $aid => $aTopic) {
            $oTopic = $this->Pluginwpimport_Modulelstopic_getTopicByWpId($tid);
            //$oTopic = $this->JoomlaPost2Topic($tid);
            if ($oTopic) {
                $this->makeTopic($oTopic,$aTopic);
                $this->Hook_Run('topic_edit_after',array('oTopic'=>$oTopic,'oBlog'=>$oTopic->getBlog()));
                return "updated";
            } else {
                $oTopic = Engine::GetEntity('Topic');
                //$oTopic->setId($aid);
                $this->makeTopic($oTopic,$aTopic);
                if ($oTopic = $this->Topic_AddTopic($oTopic)) {
                    //$oTopic = $this->Topic_GetTopicById($oTopic->getId());
                    //print_r($oTopic->getId());
                    //die('kukuku');
                    if($aTopic['gallery']){ 
                        $this->makeGallery($oTopic,$aTopic);
                    }
                }
                $this->Pluginwpimport_ModuleLstopic_setTopicWpId($oTopic,$tid);
                $this->Hook_Run('topic_add_after',array('oTopic'=>$oTopic,'oBlog'=>$oTopic->getBlog()));
                return "created";
            }
        }
    }
    public function getTopicIdByAlias($sAlias){
        return $this->oMapper->getTopicIdByAlias($sAlias,Config::Get('plugin.wpimport.wp_prefix'));
    }
}
?>