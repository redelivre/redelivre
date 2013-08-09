<?php

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && is_int((int) $_GET['id'])) {
    require 'campaigns_edit.php';
} 
else {
    require 'campaigns_list.php';
}