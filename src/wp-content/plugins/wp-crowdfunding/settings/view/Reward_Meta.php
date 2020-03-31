<?php
global $post;
$reward = get_post_meta($post->ID, 'wpneo_selected_reward', true);
if ( ! empty($reward) && is_array($reward) ){
    ?>
    <ul class="order_notes">
        <li class="note system-note">
            <div class="note_content">
                <?php
                if ( ! empty($reward['wpneo_rewards_description']) ){
                    echo "<div>{$reward['wpneo_rewards_description']}</div>";
                }
                ?>
            </div>
            <?php if( ! empty($reward['wpneo_rewards_pladge_amount']) ){ ?>
                <div class="meta">
                    <abbr>
                        <?php echo sprintf('Amount : %s, Delivery : %s', wc_price($reward['wpneo_rewards_pladge_amount']), $reward['wpneo_rewards_endmonth'].', '.$reward['wpneo_rewards_endyear'] ); ?>
                    </abbr>
                </div>
            <?php } ?>
        </li>
    </ul>
<?php } ?>