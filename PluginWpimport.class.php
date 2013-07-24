<?php

/* -------------------------------------------------------
 *
 *   LiveStreet (v1.0)
 *   Plugin Conversion of the WordPress (v.0.1)
 *   Copyright © 2011 Bishovec Nikolay
 *
 * --------------------------------------------------------
 *
 *   Plugin Page: http://netlanc.net
 *   Contact e-mail: netlanc@yandex.ru
 *
  ---------------------------------------------------------
 */

if (!class_exists('Plugin')) {
    die('Hacking attemp!');
}

class PluginWpimport extends Plugin
{

    public $aDelegates = array(
            'action' => array(
                'ActionError' => '_ActionRouteWp',
            ),
    );

    public function Activate() {
        
        $this->ExportSQL(dirname(__FILE__).'/install.sql'); // Если нам надо изменить БД, делаем это здесь.
        return true;
    }

    public function Deactivate(){       
        $this->ExportSQL(dirname(__FILE__).'/deinstall.sql'); // Выполнить деактивационный sql, если надо.
        return true;
    }

    public function Init() {
        $this->Viewer_AppendScript(Plugin::GetTemplatePath(__CLASS__)."/js/wpimport.js");
        $this->Viewer_AppendStyle(Plugin::GetTemplatePath(__CLASS__)."/css/styles.css");
    }
}

?>
