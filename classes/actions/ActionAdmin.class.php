<?php

class PluginWpimport_ActionAdmin extends ActionPlugin
{

    protected $oUserCurrent = null;

    /**
     * Инициализация
     *
     */
    public function Init()
    {
    $this->oUserCurrent = $this->User_GetUserCurrent();
    if (!$this->oUserCurrent OR !$this->oUserCurrent->isAdministrator()) {
        return Router::Action('error');
    }
    $this->SetDefaultEvent('admin');
    }

    /**
     * Регистрируем необходимые евенты
     *
     */
    protected function RegisterEvent()
    {
        $this->AddEvent('admin', 'EventAdmin');
        $this->AddEvent('users','EventUsers');
        $this->AddEventPreg('/^pusers$/i','/^$/i','EventPUsers');
        $this->AddEventPreg('/^pusers$/i','/^page(\d+?)$/i','EventPUsers');

        $this->AddEventPreg('/^pages$/i','/^$/i','EventPages');
        $this->AddEventPreg('/^pages$/i','/^page(\d+?)$/i','EventPages');
        $this->AddEventPreg('/^pages$/i','/^(\d+?)$/i','EventImportPage');

        $this->AddEvent('categories', 'EventCategories');
        $this->AddEvent('comments', 'EventComments');
        $this->AddEventPreg('/^posts$/i','/^(page(\d+))?$/i','EventPosts');
        $this->AddEventPreg('/^posts$/i','/^(item(\d+))?$/i', 'EventPosts');
        $this->AddEventPreg('/^posts$/i','/^(comment(\d+))?$/i', 'EventPosts');
    }

    private function getUsers(){
        return $this->PluginWpimport_wpimport_GetUsers();
    }
    private function addUser($uid) {
        return $this->PluginWpimport_wpimport_addUser($uid);
    }

    private function getCats(){
        return $this->PluginWpimport_wpimport_GetCats();
    }
    private function addCat($cid) {
        return $this->PluginWpimport_wpimport_addCat($cid);
    }

    private function addPost($tid) {
        return $this->PluginWpimport_wpimport_addPost($tid);
    }
    private function addComments($cid) {
        return $this->PluginWpimport_wpimport_addComments($cid);
    }

    protected function EventAdmin()
    {
        $this->SetTemplateAction('admin');
    }

    protected function EventPages(){
        $iPerPage = Config::Get('plugin.wpimport.per_page');
        $iPage = $this->getParamEventMatch(0,1) ? $this->getParamEventMatch(0,1) : 1;
        $aResult = $this->PluginWpimport_wpimport_GetPages($iPage,$iPerPage);
        $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,$iPerPage,Config::Get('pagination.pages.count'),Router::GetPath('wpimport')."pages/");
        $this->Viewer_Assign('aPages',$aResult['collection']);
        $this->Viewer_Assign('aPaging',$aPaging);
    }

    protected function EventImportPage(){
        $this->Viewer_SetResponseAjax('json');
        if(!$iPageId = $this->getParamEventMatch(0,1)){
            $this->Message_AddError('No pageid:'.$iPageId);
            return;
        };
        $this->PluginWpimport_wpimport_addPage($id);
    }

    protected function EventUsers()
    {
        //print_r($this);
        //die();
        //$iPage = $this->getParamEventMatch(0,1) ? $this->getParamEventMatch(0,1) : 1;
        $params = $this->getParams();
        $this->Viewer_Assign('sTemplateWebPathPlugin',Plugin::GetTemplateWebPath(get_class($this)));
        if ($params) {
            $uId = $params[0];          
            $this->Viewer_SetResponseAjax('json');
            $status = $this->addUser($uId);
            $this->Viewer_AssignAjax('id',$uId);
            $this->Viewer_AssignAjax('status',$status);
        } else {
            //$users = $this->PluginWpimport_wpimport_ImportUsers();
            $this->Viewer_Assign('aUsers',$this->getUsers());
        }
    }

    protected function EventPUsers(){
        $iPerPage = Config::Get('plugin.wpimport.per_page');
        $iPage = $this->getParamEventMatch(0,1) ? $this->getParamEventMatch(0,1) : 1;
        $aResult = $this->PluginWpimport_wpimport_GetPUsers($iPage,$iPerPage);
        $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,$iPerPage,Config::Get('pagination.pages.count'),Router::GetPath('wpimport')."pusers/");
        $this->Viewer_Assign('aUsers',$aResult['collection']);
        $this->Viewer_Assign('aPaging',$aPaging);
    }

    protected function EventCategories()
    {
        $params = $this->getParams();
        $this->Viewer_Assign('sTemplateWebPathPlugin',Plugin::GetTemplateWebPath(get_class($this)));
        if ($params) {
            $cId = $params[0];          
            $this->Viewer_SetResponseAjax('json');
            $status = $this->addCat($cId);
            $this->Viewer_AssignAjax('id',$cId);
            $this->Viewer_AssignAjax('status',$status);
        } else {
            $users = $this->getCats();
            $this->Viewer_Assign('aCats',$this->getCats());         
        }
    }

    protected function EventPosts()
    {
        if ($this->GetParamEventMatch(0,0)){
            $tid = preg_match("/^(item(\d+))?$/i",$this->GetParamEventMatch(0,0)) ? $this->GetParamEventMatch(0,2) : null;
            $iPage = preg_match("/^(page(\d+))?$/i",$this->GetParamEventMatch(0,0)) ? $this->GetParamEventMatch(0,2) : 1;
            $cid = preg_match("/^(comment(\d+))?$/i",$this->GetParamEventMatch(0,0)) ? $this->GetParamEventMatch(0,2) : null;
        } else {
            $tid = null;
            $cid = null;
            $iPage = 1;
        }
        $this->Viewer_Assign('sTemplateWebPathPlugin',Plugin::GetTemplateWebPath(get_class($this)));
        if ($tid) {
            $this->Viewer_SetResponseAjax('json');
            $status = $this->addPost($tid);
            //print "tid: {$tid} status: {$status}";
            $this->Viewer_AssignAjax('id',$tid);
            $this->Viewer_AssignAjax('status',$status);
        } elseif ($cid){
            $this->Viewer_SetResponseAjax('json');
            $status = $this->addComments($cid);
            $this->Viewer_AssignAjax('cid',$cid);
            $this->Viewer_AssignAjax('status',$status);
        } else {
            $aResult = $this->PluginWpimport_wpimport_getPosts($iPage,Config::Get('plugin.wpimport.per_page'));
            $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('plugin.wpimport.per_page'),Config::Get('pagination.pages.count'),Router::GetPath('wpimport/posts'));
            $this->Viewer_Assign('aPaging',$aPaging);
            $this->Viewer_Assign('aPosts',$aResult['collection']);
        }
    }

    protected function EventComments()
    {
        $this->SetTemplateAction('comments');
    }


    public function EventShutdown()
    {
        /*$this->Viewer_Assign('sMenuItemSelect', $this->sMenuItemSelect);*/
    }

}

?>
