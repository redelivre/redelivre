<?php $url = forminator_plugin_url(); ?>

<div class="sui-row-with-sidenav forminator-integrations-wrapper">

	<div class="sui-sidenav">

		<ul class="sui-vertical-tabs sui-sidenav-hide-md">

			<li class="sui-vertical-tab forminator-integrations" data-tab-id="forminator-integrations">
				<a href="#forminator-integrations" role="button"><?php esc_html_e( 'Applications', Forminator::DOMAIN ); ?></a>
			</li>

			<li class="sui-vertical-tab forminator-api" data-tab-id="forminator-api">
				<a href="#forminator-api" role="button"><?php esc_html_e( 'API', Forminator::DOMAIN ); ?></a>
			</li>

		</ul>

		<select class="sui-mobile-nav sui-sidenav-hide-lg">
			<option value="forminator-integrations"><?php esc_html_e( 'Applications', Forminator::DOMAIN ); ?></option>
			<option value="forminator-api"><?php esc_html_e( 'API', Forminator::DOMAIN ); ?></option>
		</select>

	</div>

	<div id="forminator-integrations" class="wpmudev-settings--box" style="display: block;">

		<div class="sui-box">

			<div class="sui-box-header">

				<h2 class="sui-box-title"><?php esc_html_e( 'Applications', Forminator::DOMAIN ); ?></h2>

			</div>

			<div id="forminator-integrations-page" class="sui-box-body">

				<p><?php esc_html_e( 'Forminator integrates with your favorite third party apps. You can connect to the available apps via their API here and activate them to collect data in the Integrations tab of your forms, polls or quizzes.', Forminator::DOMAIN ); ?></p>

				<div id="forminator-integrations-display"></div>

			</div>

		</div>

	</div>

	<div id="forminator-api" class="wpmudev-settings--box" style="display: none;">

		<div class="sui-box">

			<div class="sui-box-header">

				<h2 class="sui-box-title"><?php esc_html_e( 'API', Forminator::DOMAIN ); ?></h2>

				<div class="sui-actions-left">
					<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'PRO', Forminator::DOMAIN ); ?></span>
				</div>

			</div>

			<div class="sui-box">

				<div class="sui-box-body sui-block-content-center">

					<?php if ( forminator_is_show_branding() ) : ?>
						<img src="<?php echo $url . 'assets/img/forminator-disabled.png'; // phpcs:ignore ?>"
							srcset="<?php echo $url . 'assets/img/forminator-disabled.png'; // phpcs:ignore ?> 1x,
							<?php echo $url . 'assets/img/forminator-disabled@2x.png'; // phpcs:ignore ?> 2x"
							alt="<?php esc_html_e( 'Forminator APIs', Forminator::DOMAIN ); ?>"
							class="sui-image sui-image-center fui-image"/>
					<?php endif; ?>

					<div class="fui-limit-block-600 fui-limit-block-center">

					<p>
						<?php
						esc_html_e( 'Build your own integrations and custom Forminator apps using our full featured API! Visit the Forminator API Docs to get started.', Forminator::DOMAIN );
						?>
					</p>
					<p>
						<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/forminator-api-docs/" target="_blank" class="sui-button sui-button-blue">Get Started</a>
					</p>
					</div>

				</div>

			</div>

		</div>

	</div>

</div>
