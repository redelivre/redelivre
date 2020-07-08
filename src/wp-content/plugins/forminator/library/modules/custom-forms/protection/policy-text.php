<div class="wp-suggested-text">
	<h2><?php esc_html_e( 'Which forms collect personal data?', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e( 'If you use Forminator PRO to create and embed any forms on your website, you may need to mention it here to properly distinguish it from other forms.',
		                  Forminator::DOMAIN ); ?>
	</p>

	<h2><?php esc_html_e( 'What personal data do we collect and why?', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php _e( 'By default Forminator captures the <strong>IP Address</strong> for each submission to a Form. Other personal data such as your <strong>name</strong> and <strong>email address</strong> may also be captured,
depending on the Form
Fields.',
		          Forminator::DOMAIN );//wpcs : xss ok ?>
	</p>
	<p class="privacy-policy-tutorial">
		<i>
			<?php esc_html_e( 'Note: In this section you should include any personal data you collected and which form captures personal data to give users more relevant information. You should also include an explanation of why this data is needed. The explanation must note either the legal basis for your data collection and retention of the active consent the user has given.',
			                  Forminator::DOMAIN ); ?></i>
	</p>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php _e( 'When visitors or users submit a form, we capture the <strong>IP Address</strong> for spam protection. We also capture the <strong>email address</strong> and might capture other personal data included in the Form fields.',
		          Forminator::DOMAIN );//wpcs : xss ok ?>
	</p>

	<h2><?php esc_html_e( 'How long we retain your data', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php _e( 'By default Forminator retains all form submissions <strong>forever</strong>. You can change this setting in <strong>Forminator</strong> &raquo; <strong>Settings</strong> &raquo;
		<strong>Privacy Settings</strong>',
		          Forminator::DOMAIN );//wpcs : xss ok ?>
	</p>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php esc_html_e( 'When visitors or users submit a form we retain the data for 30 days.', Forminator::DOMAIN ); ?>
	</p>
	<h2><?php esc_html_e( 'Where we send your data', Forminator::DOMAIN ); ?></h2>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
		<?php esc_html_e( 'All collected data might be shown publicly and we send it to our workers or contractors to perform necessary actions based on the form submission.', Forminator::DOMAIN ); ?>
	</p>
	<h2><?php esc_html_e( 'Third Parties', Forminator::DOMAIN ); ?></h2>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e( 'If your forms utilize either built-in or external third party services, in this section you should mention any third parties and its privacy policy.',
		                  Forminator::DOMAIN ); ?>
	</p>
	<p class="privacy-policy-tutorial">
		<?php esc_html_e( 'By default Forminator Forms can be configured to connect with these third parties:' ); ?>
	</p>
	<ul class="privacy-policy-tutorial">
		<li><?php esc_html_e( 'Akismet. Enabled when you installed and configured Akismet on your site.' ); ?></li>
		<li><?php esc_html_e( 'Google reCAPTCHA. Enabled when you added reCAPTCHA on your forms.' ); ?></li>
		<li><?php esc_html_e( 'Mailchimp. Enabled when you activated and setup Mailchimp on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Zapier. Enabled when you activated and setup Zapier on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'ActiveCampaign. Enabled when you activated and setup ActiveCampaign on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Aweber. Enabled when you activated and setup Aweber on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Campaign Monitor. Enabled when you activated and setup Campaign Monitor on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Google Drive. Enabled when you activated and setup Google Drive on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Trello. Enabled when you activated and setup Trello on Integrations settings.' ); ?></li>
		<li><?php esc_html_e( 'Slack. Enabled when you activated and setup Slack on Integrations settings.' ); ?></li>
	</ul>
	<p>
		<strong class="privacy-policy-tutorial"><?php esc_html_e( 'Suggested text: ', Forminator::DOMAIN ); ?></strong>
	<p><?php esc_html_e( 'We use Google reCAPTCHA for spam protection. Their privacy policy can be found here : https://policies.google.com/privacy?hl=en.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Akismet Spam for spam protection. Their privacy policy can be found here : https://automattic.com/privacy/.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Mailchimp to manage our subscriber list. Their privacy policy can be found here : https://mailchimp.com/legal/privacy/.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Zapier to manage our integration data. Their privacy policy can be found here : https://zapier.com/privacy/.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use ActiveCampaign to manage our subscriber. Their privacy policy can be found here : https://www.activecampaign.com/privacy-policy/.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Aweber to manage our subscriber. Their privacy policy can be found here : https://www.aweber.com/privacy.htm.', Forminator::DOMAIN ); ?></p>
	<p>
		<?php esc_html_e( 'We use Campaign Monitor to manage our subscriber. Their privacy policy can be found here : https://www.campaignmonitor.com/policies/#privacy-policy.',
		                  Forminator::DOMAIN ); ?>
	</p>
	<p>
		<?php esc_html_e( 'We use Google Drive and Google Sheets to manage our integration data. Their privacy policy can be found here : https://policies.google.com/privacy?hl=en.',
		                  Forminator::DOMAIN ); ?>
	</p>
	<p><?php esc_html_e( 'We use Trello to manage our integration data. Their privacy policy can be found here : https://trello.com/privacy.', Forminator::DOMAIN ); ?></p>
	<p><?php esc_html_e( 'We use Slack to manage our integration data. Their privacy policy can be found here : https://slack.com/privacy-policy.', Forminator::DOMAIN ); ?></p>
	</p>


</div>
