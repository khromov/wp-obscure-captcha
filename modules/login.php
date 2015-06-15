<?php
/**
 * Loosely based on:
 * http://www.sitepoint.com/integrating-a-captcha-with-the-wordpress-login-form/
 */

// adds the captcha to the login form
add_action( 'login_form', function(){
	$question_prefix = apply_filters('signup_captcha_questions_prefix', '');
	$questions = apply_filters('signup_captcha_questions', array());
	oc_user_registration_captcha_render_form($question_prefix, array_rand($questions));
});

// authenticate the captcha answer
add_action( 'wp_authenticate_user', function($user, $password) {

	$postdata = oc_default_registration_postdata($_POST);


	/* If users magically filled out the hidden field, boot them */
	if($postdata['signup_captcha_hp'] !== '') {
		/* http://wordpress.stackexchange.com/questions/117012/validate-custom-login-field */
		//remove_action('authenticate', 'wp_authenticate_username_password', 20);
		return new WP_Error( 'signup_captcha_bot_error', __('<strong>FEL</strong>: Du råkade fylla i ett osynligt fält! Säker på att du inte är en robot?'));
	}


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
	if($answer === NULL || $strtolower_func($postdata['signup_captcha_answer']) !== $strtolower_func($answer)) {
		/* http://wordpress.stackexchange.com/questions/117012/validate-custom-login-field */
		//remove_action('authenticate', 'wp_authenticate_username_password', 20);
		$user = new WP_Error('signup_captcha_wrong_answer',__('<strong>FEL</strong>: Svaret på kontrollfrågan var felaktigt.'));
	}

	/* All good, return user! */
	return $user;
}, 10, 2 );