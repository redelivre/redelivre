<?php
/**
 * Promotion 
 * Version 1.0.0
 */
if(!class_exists('pisol_promotion')){
 class pisol_promotion{

     function __construct($variable_name){
        $this->variable_name = $variable_name;
        $this->logic();
     }

     function logic(){
        $day_passed = $this->daysPassed();
        $today = date('d, M Y');
        switch($day_passed){
            case 0:
            $this->add("OFFER ENDS ON", $today, "6%", "OFF6" );
            break;
            
            case 1:
            $this->add("OFFER ENDS ON", $today, "4%", "OFF4" );
            break;

            case 2:
            $this->add("OFFER ENDS ON", $today, "2%", "OFF2" );
            break;

            case ($day_passed > 2):
                if($this->isWeekEnd()){
                    $this->add("WEEKEND OFFER", "---------------", "10%", "DISC10" );
                }
            break;
        } 
     }

     function getInstallationDate(){
        $install_date = get_option($this->variable_name,"");
        $today = date('Y-m-d');
        if($install_date == "" || $install_date == false || $install_date == "1"){
            update_option($this->variable_name, $today);
            return $today;
        }
        //return '2019-06-19';
        return $install_date == "" ? $today : $install_date;
     }

     function daysPassed(){
        $date = $this->getInstallationDate();
        $today = date('Y-m-d');
        $date_obj = date_create($date);
        $today_obj = date_create($today);
        $diff = date_diff($today_obj, $date_obj);
        return $diff->days;
     }

     function isWeekEnd(){
        $day = date('N', strtotime(date('Y/m/d')));
        if($day >= 6){
            return true;
        }
        return false;
     }

     function add($tagline, $date, $percent, $coupon_code){
        include 'banner-sample.php';
     }
 }

}
