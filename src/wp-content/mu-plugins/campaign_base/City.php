<?php

class City {
    /**
     * Return all available cities from a specific state
     * 
     * @param int $stateId
     * @return array
     */
    public static function getAllByState($stateId) {
        global $wpdb;
        $cities = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `cities` WHERE state_id = %d ORDER BY `name` asc", $stateId));
            
        return $cities;
    }
    
    /**
     * Print the select box with all the cities from a state
     * 
     * @param int $stateId
     * @param int $selectedCity
     * @return null 
     */
    public static function printCitiesSelectBox($stateId, $selectedCity = false) {
        $cities = self::getAllByState($stateId);
        $output = '';
        
        if (isset($_POST['city'])) {
            $selectedCity = $_POST['city'];
        }
        
        if (is_array($cities) && !empty($cities)) {
            foreach ($cities as $city) {
                $output .= "<option value='{$city->id}' ";
                $output .= ($selectedCity == $city->id) ? ' selected="selected" ' : '';
                $output .= ">{$city->name}</option>";
            }
        }
        
        echo $output;
    }
}
