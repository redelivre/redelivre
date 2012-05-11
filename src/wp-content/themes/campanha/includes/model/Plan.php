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
}
