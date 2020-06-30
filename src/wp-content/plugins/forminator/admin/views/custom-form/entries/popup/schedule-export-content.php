<div class="wpmudev-box-gray">
    <form class="forminator_schedule_export" method="post">
        <div id="forminator-export-schedule-timeframe" class="wpmudev-row">

            <div class="wpmudev-col col-12 col-md-4">

                <label><?php esc_html_e( "Frequency", Forminator::DOMAIN ); ?></label>

                <select class="wpmudev-select">

                    <option><?php esc_html_e( "Daily", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Weekly", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Monthly", Forminator::DOMAIN ); ?></option>

                </select>

            </div>

            <div class="wpmudev-col col-12 col-md-4">

                <label><?php esc_html_e( "Day of the week", Forminator::DOMAIN ); ?></label>

                <select class="wpmudev-select">

                    <option><?php esc_html_e( "Monday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Tuesday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Wednesday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Thursday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Friday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Saturday", Forminator::DOMAIN ); ?></option>
                    <option><?php esc_html_e( "Sunday", Forminator::DOMAIN ); ?></option>

                </select>

            </div>

            <div class="wpmudev-col col-12 col-md-4">

                <label><?php esc_html_e( "Time of the day", Forminator::DOMAIN ); ?></label>

                <select class="wpmudev-select">

                    <option>12:00 AM</option>
                    <option>01:00 AM</option>
                    <option>02:00 AM</option>
                    <option>03:00 AM</option>
                    <option>04:00 AM</option>
                    <option>05:00 AM</option>
                    <option>06:00 AM</option>
                    <option>07:00 AM</option>
                    <option>08:00 AM</option>
                    <option>09:00 AM</option>
                    <option>10:00 AM</option>
                    <option>11:00 AM</option>
                    <option>12:00 PM</option>
                    <option>01:00 PM</option>
                    <option>02:00 PM</option>
                    <option>03:00 PM</option>
                    <option>04:00 PM</option>
                    <option>05:00 PM</option>
                    <option>06:00 PM</option>
                    <option>07:00 PM</option>
                    <option>08:00 PM</option>
                    <option>09:00 PM</option>
                    <option>10:00 PM</option>
                    <option>11:00 PM</option>

                </select>

            </div>

        </div>

        <div id="forminator-export-schedule-email" class="wpmudev-row">

            <div class="wpmudev-col col-12">

                <label><?php esc_html_e( "Email export data to", Forminator::DOMAIN ); ?></label>

                <input type="email" class="wpmudev-input"
                       placeholder="<?php esc_html_e( 'admin@website.com', Forminator::DOMAIN ); ?>">

                <label class="wpmudev-helper"><?php esc_html_e( "Leave blank if you don't want to receive exports via email.", Forminator::DOMAIN ); ?></label>

            </div>

        </div>
		<?php wp_nonce_field( 'forminator_export_data', '_forminator_nonce' ); ?>
        <button class="wpmudev-button wpmudev-button-blue" type="submit"><?php esc_html_e( "Save Changes", Forminator::DOMAIN ); ?></button>
    </form>
</div>
