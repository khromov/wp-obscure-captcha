<?php
/**
 * Renders the registration CAPTCHA form
 *
 * @param $question_prefix
 * @param $question
 * @param null $errors
 * @param string $answer
 */
function oc_user_registration_captcha_render_form($question_prefix, $question, $errors = null, $answer = '')
{
	?>
	<p>
		<!-- Print errors if needed -->
		<?php
			if(is_array($errors))
			{
				foreach($errors as $error)
					echo ($error) ? "<p class=\"error\">{$error}</p>" : '';
			}
		?>
		<label for="signup_captcha_answer">
			<span>
				<strong>
					<?=$question_prefix?>
				</strong>
				<?=oc_convert_to_htmlentities($question)?>
			</span>
			<br/>
			<input type="text" name="signup_captcha_answer" value="<?=$answer?>" class="input" value="" size="20"><br/>
			<input type="hidden" name="signup_captcha_question" value="<?=base64_encode($question)?>">
			<input type="hidden" name="signup_captcha_check" value="">
		</label>

		<label id="signup_captcha_id">HP<br/>
			<input type="text" name="signup_captcha_hp" value="" class="input" size="20"/>
		</label>

		<script>
			document.getElementById("signup_captcha_id").style.display = "none";
		</script>

		<!-- Technically necessary? Bah, who knows... -->
		<noscript>
			<style type="text/css">
				#signup_captcha_id
				{
					display: none;
				}
			</style>
		</noscript>
	</p>
	<?php
}

/**
 * Helper function to convert string to proper htmlentities (via UTF-32 detour)
 *
 * @param $in
 * @return string
 */
function oc_convert_to_htmlentities($in) {
	/* Convert all characters to HTML entities */
	if(function_exists('mb_convert_encoding')) {
		$text = mb_convert_encoding($in , 'UTF-32', 'UTF-8');
		$question_html_entities = unpack("N*", $text);
		$question_html_entities = array_map(function($n)
		{
			return "&#$n;";
		}, $question_html_entities);
	}
	else {
		$question_html_entities = htmlentities($in); //Not as good...
	}

	return implode("", $question_html_entities);
}

/**
 * Helper function to return default keys in postdata.
 *
 * @param $incoming_postdata - $_POST values.
 * @return array
 */
function oc_default_registration_postdata($incoming_postdata) {
		return array_merge(array(
			'signup_captcha_answer' => '',
			'signup_captcha_question' => '',
			'signup_captcha_check' => '',
			'signup_captcha_hp' => ''
	), $incoming_postdata);
}