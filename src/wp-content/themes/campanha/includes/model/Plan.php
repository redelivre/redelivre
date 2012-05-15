<?php

class Plan {
    /**
     * Return all available plans
     * 
     * @return array
     */
    public static function getAll() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM `plans`");
    }
    
    /**
     * Return all available plan's ids
     * 
     * @return array
     */
    public static function getAllIds() {
        global $wpdb;
        return $wpdb->get_col("SELECT id FROM `plans`");
    }
    
    /**
     * Return plan name
     * @param int $planId
     * @return string
     */
    public static function getName($planId) {
        global $wpdb;
        $name = $wpdb->get_var(
            $wpdb->prepare("SELECT `name` FROM `plans` WHERE `id` = %d", $planId));
            
        return $name;
    }
}
