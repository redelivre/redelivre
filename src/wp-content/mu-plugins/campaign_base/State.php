<?php

class State {
    /**
     * Return all available states
     * 
     * @return array
     */
    public static function getAll() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM `states` ORDER BY `name` asc");
    }
}
