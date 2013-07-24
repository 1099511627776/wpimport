<?php

$config = array();

$config['wpdb'] = 
    array(
        'type'=>'mysql',
        'user'=>'utmag',
        'pass'=>'8nVkE7Ci',
        'host'=>'localhost',
        'port'=>'3306',
        'dbname'=>'utmagaz'
        );

$config['per_page'] = 50;

$config['wp_prefix'] = 'utmag';
$config['wpsite'] = 'http://utmagazine.ru/';
$config['wp_fileroot'] = '';

$config['anonymous_user'] = 1;

Config::Set('router.page.wpimport', 'PluginWpimport_ActionAdmin');
return $config;
?>
