<?php
/*
Plugin Name: Obscure CAPTCHA
Plugin URI:
Description: Shh, this plugin is a secret! :-)
Version: 1.0
Author: khromov
Author URI: http://profiles.wordpress.org/khromov/
GitHub Plugin URI: khromov/wp-obscure-captcha
License: GPL2
*/

define('OBSCURE_CAPTCHA_DIR', dirname(__FILE__));

/**
 * This will let us set a question prefix and register our questions.
 * These arent hooked anywhere because we want them to run as early as possible.
 */
add_action('plugins_loaded', function()
{
	/* Config */
	$config = include 'obscure-config.php';

	/* Shared functions */
	include 'obscure-functions.php';

	/* Set captcha prefix */
	add_filter('signup_captcha_questions_prefix', function($prefix)
	{
		return __('Kontrollfråga: ', 'user_registration_captcha') . '<br/>';
	});

	/* Add default questions */
	add_filter('signup_captcha_questions', function($questions) use ($config)
	{
		return array_merge($questions, $config['captcha_questions']);
	});

	/* Load CAPTCHA modules */
	foreach($config['enabled_modules'] as $module) {
		include trailingslashit(OBSCURE_CAPTCHA_DIR) . "modules/{$module}.php";
	}
}, 11);