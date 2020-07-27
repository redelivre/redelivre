<?php

class class_pisol_ewcl_email{

    private $frequency = 'daily';

    function __construct($email, $subject, $message, $file){

        $this->email = $email;

        $this->subject = $subject;

        $this->message = $message;

        $this->file = $file;
    }

    function send(){
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(wp_mail($this->email, $this->subject, $this->message, $headers, array($this->file))){
           return true;
        }
        return false;
    }
    
}

