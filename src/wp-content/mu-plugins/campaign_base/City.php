<?php

class City {
    /**
     * Return all available cities from a specific state
     * 
     * @param int $state_id
     * @return array
     */
    public static function getAllByState($state_id) {
        global $wpdb;
        $cities = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `cities` WHERE state_id = %d ORDER BY `name` asc", $state_id));
            
        return $cities;
    }
    
    /**
     * Print the select box with all the cities from a state
     * 
     * @param int $state_id
     * @return null 
     */
    public static function printCitiesSelectBox($state_id) {
        $cities = self::getAllByState($state_id);
        $output = '';
        
        if (is_array($cities) && !empty($cities)) {
            foreach ($cities as $city) {
                $output .= "<option value='{$city->id}' ";
                $output .= (isset($_POST['city']) && $_POST['city'] == $city->id) ? ' selected="selected" ' : '';
                $output .= ">{$city->name}</option>";
            }
        }
        
        echo $output;
    }
}
