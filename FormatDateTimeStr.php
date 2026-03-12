<?php
function FormatDateTimeStr($Str) {
    $OutStr = substr($Str, 0, 4)
        . '-' . substr($Str, 4, 2)
        . '-' . substr($Str, 6, 2)
        . 'T' . substr($Str, 9, 2)
        . ':' . substr($Str, 11, 2)
        . ':' . substr($Str, 13, 2);
    return $OutStr;
}