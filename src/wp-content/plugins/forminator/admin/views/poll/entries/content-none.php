<?php if ( forminator_is_show_branding() ): ?>
    <img src="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions.png' ); // WPCS: XSS ok. ?>"
         srcset="<?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions.png' ); // WPCS: XSS ok. ?> 1x, <?php echo esc_url( forminator_plugin_url() . 'assets/img/forminator-submissions@2x.png' ); // WPCS: XSS ok. ?> 2x"
         alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
         class="sui-image"
         aria-hidden="true"/>
<?php endif; ?>

<div class="sui-message-content">

    <h2><?php echo forminator_get_form_name( $form_id, 'poll'); // WPCS: XSS ok. ?></h2>

    <p><?php esc_html_e( "You haven’t received any submissions for this poll yet. When you do, you’ll be able to view all the data here.", Forminator::DOMAIN ); ?></p>

</div>
