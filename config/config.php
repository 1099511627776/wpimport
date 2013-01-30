<?php

$config = array();

$config['wpdb'] = 
	array(
		'type'=>'mysql',
		'user'=>'',
		'pass'=>'',
		'host'=>'localhost',
		'port'=>'3306',
		'dbname'=>''
		);

$config['wp_prefix'] = 'wp';
$config['wpsite'] = 'http://koko.by/';
$config['wp_fileroot'] = '';

Config::Set('router.page.wpimport', 'PluginWpimport_ActionAdmin');
return $config;
?>
