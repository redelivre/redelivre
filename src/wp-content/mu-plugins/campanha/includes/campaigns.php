<?php

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    // edit campaign
    require_once('campaigns_edit.php');
} else {
    // list or delete campaigns
    require_once('campaigns_list.php');
} 
