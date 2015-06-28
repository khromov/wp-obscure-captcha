<?php
return apply_filters('obscure_captcha_settings', array(
	'enabled_modules' => array(
		//'registration',
		//'disable-xmlrpc',
		'login'
	),
	'captcha_questions' => array(
		'Vilken färg har himlen?' => 'blå',
		'Vilken färg har sveriges flagga, förutom blå?' => 'gul'
	)
));