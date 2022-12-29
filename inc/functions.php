<?php
function readableTimestamps($date)
{
    if ($date === null || strlen($date) === 0) return;

    $yyyy = substr($date, 0, 4);
    $mm = substr($date, 4, 2);
    $dd = substr($date, 6, 2);
    $hh = substr($date, 8, 2);
    $ii = substr($date, 10, 2);
    $ss = substr($date, 12, 2);


    return "$yyyy-$mm-$dd" . " " . "$hh:$ii:$ss";
}

function getTokenFromRequestHeader(): string|false {
    $authHeader = apache_request_headers()['Authorization'] ?? false;

    if($authHeader == false) { 
        return false;
    }
    
    return substr($authHeader, strlen('Bearer '));
}
function onlyFirstCharacterIsCapital(string $str) {
    return strtoupper(substr($str, 0, 1)) . (strlen($str) > 1  ? substr($str, 1) : '');
}