<?php
defined( 'ABSPATH' ) || exit;

global $post;
$campaign_rewards = get_post_meta($post->ID, 'wpneo_reward', true);
$campaign_rewards = stripslashes($campaign_rewards);
$campaign_rewards_a = json_decode($campaign_rewards, true);
$permalink = get_the_permalink();
$html = '';
$html .="<div class='campaign_rewards_wrapper'>";

if (is_array($campaign_rewards_a)) {
    if (count($campaign_rewards_a) > 0) {
        $html .= '<table class="table table-border">';
        $html .= '<tr>';
        foreach ($campaign_rewards_a[0] as $k => $v) {
            $html .= '<th>';
            $html .= ucfirst(str_replace('_', ' ', str_replace(strtolower('Wpneo_rewards_'),'',strtolower($k))));
            $html .= '</th>';
        }
        $html .= '</tr>';
        $i = 0;
        foreach ($campaign_rewards_a as $key => $value) {
            $i++;
            $html .= '<tr>';
            foreach ($value as $k => $v) {
                $html .= '<td>';
                if ($k == 'wpneo_rewards_pladge_amount') {
                    $to_amount = '+';
                    if ( ! empty($campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'])){
                        $raw_to_amount = (int) $campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'];
                        if ($v < $raw_to_amount) {
                            $to_amount = ' - ' . ((int)$campaign_rewards_a[$i]['wpneo_rewards_pladge_amount'] - 1);
                        }
                    }
                    $amount_group = $v.$to_amount;
                    $html .= '<a href="'.$permalink.'?reward_min_amount='.$v.'">'.$v.$to_amount.'</a>';
                } else {
                    $html .= $v;
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
}
$html .= "</div>";

echo $html;