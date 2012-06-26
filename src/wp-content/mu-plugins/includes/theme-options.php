<?php
add_action('admin_init', 'theme_options_init');
add_action('admin_menu', 'theme_options_add_page');

/**
 * Init plugin options to white list our options
 */
function theme_options_init() {
    register_setting('campanha_options', 'campanha_theme_options', 'theme_options_validate');
}

/**
 * Load up the menu page
 */
function theme_options_add_page() {
    add_theme_page('Opções do Tema', 'Opções do Tema', 'edit_theme_options', 'theme_options', 'theme_options_do_page');
}

global $select_options, $radio_options;
/**
 * Create arrays for our select and radio options
 */
$radio_options = array(
    'direita' => array(
        'value' => 'direita',
        'label' => 'À direita'
    ),
    'esquerda' => array(
        'value' => 'esquerda',
        'label' => 'À esquerda'
    ),
);

/**
 * Create the options page
 */
function theme_options_do_page() {
    global $select_options, $radio_options;

    if (!isset($_REQUEST['settings-updated']))
        $_REQUEST['settings-updated'] = false;
    ?>
    <div class="wrap">
        <h2>Opções do Tema</h2>

        <?php if (false !== $_REQUEST['settings-updated']) : ?>
            <div class="updated fade"><p><strong>Opções salvas</strong></p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('campanha_options'); ?>
            <?php $options = get_option('campanha_theme_options'); ?>

            <table class="form-table">
                <tr valign="top"><th scope="row">Posição da Barra Lateral</th>
                    <td>
                        <fieldset>
                            <?php
                            if (!isset($checked))
                                $checked = '';
                            foreach ($radio_options as $option) {
                                $radio_setting = $options['sidebar_position'];

                                if ('' != $radio_setting) {
                                    if ($options['sidebar_position'] == $option['value']) {
                                        $checked = "checked=\"checked\"";
                                    } else {
                                        $checked = '';
                                    }
                                }
                                ?>
                                <label class="description"><input type="radio" name="campanha_theme_options[sidebar_position]" value="<?php esc_attr_e($option['value']); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?></label><br />
                                <?php
                            }
                            ?>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php do_action('campanha_theme_options'); ?>

            <p class="submit">
                <input type="submit" class="button-primary" value="Salvar opções" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function theme_options_validate($input) {
    global $select_options, $radio_options;

    // Our radio option must actually be in our array of radio options
    if (!isset($input['radioinput']))
        $input['radioinput'] = null;
    if (!array_key_exists($input['radioinput'], $radio_options))
        $input['radioinput'] = null;

    return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
