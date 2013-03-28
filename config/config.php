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
$config['wpsite'] = '';
$config['wp_fileroot'] = '';

$config['anonymous_user'] = 1;

Config::Set('router.page.wpimport', 'PluginWpimport_ActionAdmin');
return $config;
?>
