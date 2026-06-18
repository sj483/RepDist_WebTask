<?php

function GetRegisterRow($Conn, $SubjectId)
{
    $SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
    $Sql = "SELECT * FROM Register WHERE SubjectId = '$SubjectId' LIMIT 1";
    $QueryRes = mysqli_query($Conn, $Sql);
    if ($QueryRes === false) {
        die("Register query failed to execute successfully!");
    }

    $Row = mysqli_fetch_assoc($QueryRes);
    return $Row === null ? null : $Row;
}

function RespondWithJsonNotice($StatusCode, $Notice, $Extra = array())
{
    http_response_code($StatusCode);
    $Result = array('Notice' => $Notice);
    foreach ($Extra as $Key => $Value) {
        $Result[$Key] = $Value;
    }

    echo json_encode($Result);
    exit;
}
