<div class="wp-suggested-text">
	<h2><?php esc_html_e( 'Which quizzes are collecting personal data?', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e(
			'If you use Forminator PRO to create and embed any quizzes on your website, you may need to mention it here to properly distinguish it from other quizzes.',
			Forminator::DOMAIN
		); ?>
	</p>

	<h2><?php esc_html_e( 'What personal data do we collect and why?', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php _e( 'By default Forminator captures <strong>NO Personally Identifiable Information</strong> for each Quiz submission.', Forminator::DOMAIN );// wpcs: xss ok. ?>
	</p>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e(
			'In this section you should note what personal data you collected including which quizzes are available. You should also explain why this data is needed. Include the legal basis for your data collection and note the active consent the user has given.',
			Forminator::DOMAIN
		); ?>
	</p>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php _e( 'When visitors or users submit a quiz\'s answer, we capture <strong>NO Personally Identifiable Information</strong>.', Forminator::DOMAIN );// wpcs: xss
		// ok. ?>
	</p>

	<h2><?php esc_html_e( 'How long we retain your data', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php _e(
			'By default Forminator retains all quizzes answers and <strong>forever</strong>. You can change this setting in <strong>Forminator</strong> &raquo; <strong>Settings</strong> &raquo;
		<strong>Data</strong>',
			Forminator::DOMAIN
		);// wpcs: xss ok. ?>
	</p>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php _e( 'When visitors or users answer a quiz we retain the <strong>answers</strong> data for 30 days and then remove it from our system.', Forminator::DOMAIN ); // wpcs: xss ok. ?>
	</p>
	<h2><?php esc_html_e( 'Where we send your data', Forminator::DOMAIN ); ?></h2>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php esc_html_e( 'All collected data might be shown publicly and we send it to our workers or contractors to perform necessary actions based on answers.', Forminator::DOMAIN ); ?>
	</p>
	<h2><?php esc_html_e( 'Third Parties', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e(
			'If your quizzes utilize either built-in or external third party services, in this section you should mention any third parties and its privacy policy.',
			Forminator::DOMAIN
		); ?>
	</p>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e( 'By default Forminator Quizzes can be configured to connect with these third parties:' ); ?>
	</p>
	<ul class="privacy-policy-tutorial">
		<li><?php esc_html_e( 'Zapier. Enabled when you activated and setup Zapier on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Google Drive. Enabled when you activated and setup Google Drive on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Trello. Enabled when you activated and setup Trello on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Slack. Enabled when you activated and setup Slack on Integrations settings.' ); ?></li>
	</ul>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
	<p><?php esc_html_e( 'We use Zapier to manage our integration data. Their privacy policy can be found here : https://zapier.com/privacy/.', Forminator::DOMAIN ); ?></p>

	<p>
		<?php esc_html_e(
			'We use Google Drive and Google Sheets to manage our integration data. Their privacy policy can be found here : https://policies.google.com/privacy?hl=en.',
			Forminator::DOMAIN
		); ?>
	</p>
	<p><?php esc_html_e( 'We use Trello to manage our integration data. Their privacy policy can be found here : https://trello.com/privacy.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Slack to manage our integration data. Their privacy policy can be found here : https://slack.com/privacy-policy.', Forminator::DOMAIN ); ?></p>
	</p>
</div>
