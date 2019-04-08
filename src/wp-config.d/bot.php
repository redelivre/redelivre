<?php

function isBot(){
    if(isset($_GET['s']) && strpos($_GET['s'], 'o34.c') > 0){
        return true;
    } else {
        return false;
    }
}

if(isBot()){
    http_response_code(404);
    exit;
}

