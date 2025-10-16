<?php
// A function that computes the time interval in seconds between PHP DateTime objects
function GetTimeInterval($A, $B) {
    $Yr = date_diff($A, $B)->y;
    $Mo = date_diff($A, $B)->m;
	$Dy = date_diff($A, $B)->d;
    $Hr = date_diff($A, $B)->h;
    $Mi = date_diff($A, $B)->i;
    $Sc = date_diff($A, $B)->s;
    $Interval = ($Yr*365.25*24*60*60) + ($Mo*30.4375*24*60*60) + ($Dy*24*60*60) + ($Hr*60*60) + ($Mi*60) + $Sc;
    if ($A > $B) {
        $Interval = -1 * $Interval;
    } else {
        $Interval =  1 * $Interval ;
    }
    return $Interval;
}
?>