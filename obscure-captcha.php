<?php
/*
Plugin Name: Obscure CAPTCHA
Plugin URI:
Description: Shh, it's a secret!
Version: 1.0
Author: khromov
Author URI: http://profiles.wordpress.org/khromov/
License: GPL2
*/
include 'obscure-functions.php';

/**
 * This will let us set a question prefix and register our questions.
 * These arent hooked anywhere because we want them to run as early as possible.
 */
add_filter('signup_captcha_questions_prefix', function($prefix)
{
	return __('Kontrollfråga: ', 'user_registration_captcha') . '<br/>';
});

add_filter('signup_captcha_questions', function($questions)
{
	$questions['Vilken färg har himlen?'] = 'blå';
	$questions['Vilken färg har sveriges flagga, förutom blå?'] = 'gul';

	return $questions;
});


add_action('plugins_loaded', function()
{
	$question_prefix = apply_filters('signup_captcha_questions_prefix', '');
	$questions = apply_filters('signup_captcha_questions', array());

	/**
	 * Add question in single sites
	 */
	add_action('register_form', function() use ($question_prefix, $questions)
	{
		oc_user_registration_captcha_render_form($question_prefix, array_rand($questions));
		?>
	<?php
	}, 100);

	/**
	 * Hook into registration on single sites and check answer
	 */
	add_action('register_post', function($login, $email, $errors)
	{
		/* Here we will set defaults for all the values we need */
		$postdata = oc_default_registration_postdata($_POST);

		/* @var $errors WP_Error */
		/** Check if the control value was empty and error out if it was **/

		/* If users magically filled out the hidden field, boot them */
		if($postdata['signup_captcha_hp'] !== '')
			$errors->add('signup_captcha_bot_error', __('<strong>FEL</strong>: Du råkade fylla i ett osynligt fält! Säker på att du inte är en robot?'));

		/* Grab questions here via the same filter as we use for building the form */
		$questions = apply_filters('signup_captcha_questions', array());

		/* Check if what was submitted is a proper question and in such case, grab the answer */
		$answer = (isset($questions[base64_decode($postdata['signup_captcha_question'])])) ? $questions[base64_decode($postdata['signup_captcha_question'])] : null;

		/* Gracefully handle non-existant mb_ function */
		$strtolower_func = function($in) {
			if(function_exists('mb_strtolower')) {
				return mb_strtolower($in);
			}
			else {
				return strtolower($in);
			}
		};

		/* If not a valid question or the answer is incorrect, add an error */
		if($answer === NULL || $strtolower_func($postdata['signup_captcha_answer']) !== $strtolower_func($answer))
			$errors->add('signup_captcha_wrong_answer',__('<strong>FEL</strong>: Svaret på kontrollfrågan var felaktigt.'));

	}, 10, 3);


	/**
	 * Multisite registration
	 */
	if(is_multisite())
	{
		/**
		 * Ask question
		 */
		add_action('signup_extra_fields', function($errors) use ($question_prefix, $questions)
		{
			$errors_new = array();
			$errors_new[] = $errors->get_error_message('signup_captcha_bot_error');
			$errors_new[] = $errors->get_error_message('signup_captcha_wrong_answer');

			oc_user_registration_captcha_render_form($question_prefix, array_rand($questions), $errors_new);
		});

		/**
		 * Check answer
		 */
		add_filter('wpmu_validate_user_signup', function($content)
		{
			/* Don't do the validation stuff if we're adding a user from the admin page and user has the permission to do it */
			if(is_admin() && current_user_can('create_users'))
				return $content;

			/* Here we will set defaults for all the values we need */
			$postdata = oc_default_registration_postdata($_POST);

			/* @var $content['errors'] WP_Error */
			/** Check if the control value was empty and error out if it was **/

			/* If users magically filled out the hidden field, boot them */
			if($postdata['signup_captcha_hp'] !== '')
				$content['errors']->add('signup_captcha_bot_error', __('<strong>FEL</strong>: Du råkade fylla i ett osynligt fält! Säker på att du inte är en robot?'));

			/* Grab questions here via the same filter as we use for building the form */
			$questions = apply_filters('signup_captcha_questions', array());

			/* Check if what was submitted is a proper question and in such case, grab the answer */
			$answer = (isset($questions[base64_decode($postdata['signup_captcha_question'])])) ? $questions[base64_decode($postdata['signup_captcha_question'])] : null;

			/* Gracefully handle non-existant mb_ function */
			$strtolower_func = function($in) {
				if(function_exists('mb_strtolower')) {
					return mb_strtolower($in);
				}
				else {
					return strtolower($in);
				}
			};

			/* If not a valid question or the answer is incorrect, add an error */
			if($answer === NULL || $strtolower_func($postdata['signup_captcha_answer']) !== $strtolower_func($answer))
				$content['errors']->add('signup_captcha_wrong_answer',__('<strong>FEL</strong>: Svaret på kontrollfrågan var felaktigt.'));

			return $content;
		});
	}

}, 11);