<?
function DotProduct($a,$b) {
	$d = sizeof($a);
	if ($d != sizeof($b)) {
		die("Bad call to DotProduct: Incompatible sizes.");
	}
	$c = 0;
	for ($ii = 0; $ii < $d; $ii++) {
		$c = $c + $a[$ii]*$b[$ii];
	}
	return $c;
}