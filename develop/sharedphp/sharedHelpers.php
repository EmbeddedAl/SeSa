<?php


function sharedHelpers_getUserImageFile($userid)
{
    include 'config.php';

    $imgUserFileName = "users/" . $userid . ".jpg";
    $imgFileName = file_exists($imgUserFileName) ? $imgUserFileName : $ConfigNoImageForUser;

    return $imgFileName;
}




?>