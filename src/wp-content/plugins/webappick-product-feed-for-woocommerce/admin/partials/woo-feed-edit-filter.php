<?php
if ( ! defined( 'ABSPATH' ) ) {
	die(); // silence
}
?>
<table class="table tree widefat fixed woo-feed-filters">
	<tbody>
	<tr>
        <td colspan="2"><?php _e( 'Campaign URL Builder', 'woo-feed' ); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="table widefat fixed" id="wf_campaign_url_builder">
                <tbody>
                <tr>
                    <td>
                        <label class="screen-reader-text" for="utm_source"><?php esc_html_e( 'Campaign Source', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
                        <input type="text" name="campaign_parameters[utm_source]" id="utm_source" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Source', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_source'] ); ?>">
                        <label for="utm_source">
                            <span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'The referrer: (e.g. google, newsletter)', 'woo-feed' ); ?></span>
                        </label>
                    </td>
                    <td>
                        <label class="screen-reader-text" for="utm_medium"><?php esc_html_e( 'Campaign Medium', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
                        <input type="text" name="campaign_parameters[utm_medium]" id="utm_medium" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Medium', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_medium'] ); ?>">
                        <label for="utm_medium">
                            <span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Marketing medium: (e.g. cpc, banner, email)', 'woo-feed' ); ?></span>
                        </label>
                    </td>
                    <td>
                        <label class="screen-reader-text" for="utm_campaign"><?php esc_html_e( 'Campaign Name', 'woo-feed' ); ?> <span class="required" aria-label="<?php esc_attr_e( 'Required', 'woo-feed' ); ?>">*</span></label>
                        <input type="text" name="campaign_parameters[utm_campaign]" id="utm_campaign" class="regular-text" placeholder="*<?php esc_attr_e( 'Campaign Name', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_campaign'] ); ?>">
                        <label for="utm_campaign">
                            <span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Product, promo code, or slogan (e.g. spring_sale)', 'woo-feed' ); ?></span>
                        </label>
                    </td>
                    <td>
                        <label class="screen-reader-text" for="utm_term"><?php esc_html_e( 'Campaign Term', 'woo-feed' ); ?></label>
                        <input type="text" name="campaign_parameters[utm_term]" id="utm_term" class="regular-text" placeholder="<?php esc_attr_e( 'Campaign Term', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_term'] ); ?>">
                        <label for="utm_term">
                            <span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Identify the keywords', 'woo-feed' ); ?></span>
                        </label>
                    </td>
                    <td>
                        <label class="screen-reader-text" for="utm_content"><?php esc_html_e( 'Campaign Content', 'woo-feed' ); ?></label>
                        <input type="text" name="campaign_parameters[utm_content]" id="utm_content" class="regular-text" placeholder="<?php esc_attr_e( 'Campaign Content', 'woo-feed' ); ?>" value="<?php echo esc_attr( $feedRules['campaign_parameters']['utm_content'] ); ?>">
                        <label for="utm_content">
                            <span class="description" style="color:#8a8a8a;"><?php esc_html_e( 'Use to differentiate ads', 'woo-feed' ); ?></span>
                        </label>
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5">
                        <p>
                            <span class="description"><?php esc_html_e( 'Fill out the required fields (marked with *) in the form above, if any required field is empty, then the parameters will not be applied.', 'woo-feed' ); ?></span>
                            <a href="https://support.google.com/analytics/answer/1033863#parameters" target="_blank"><?php esc_html_e( 'Learn more about Campaign URL', 'woo-feed' ); ?></a>
                        </p>
                    </td>
                </tr>
                </tfoot>
            </table>
        </td>
    </tr>
	</tbody>
</table>
