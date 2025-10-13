<?php
require __DIR__ . '/GetTargetUrl.php';
require __DIR__ . '/Credentials.php';
require __DIR__ . '/Encrypt_details.php';

// so the reason we're using this instead of a window.location.. method inside the html 
//is because the built in form methods dont facilliatate you to recieve a result of the
//post you sent out 
function RedirectToUrl($Url) {
    header('Location: '.$Url);
    exit();
}
// Preallocate the result (which will contain the direction):
$Result = array();

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

if (isset($_POST['checkbox']) && $_POST['checkbox'] =='check') {
	// Get SubjectId and Initials:
    $SubjectId = $_POST['SubjectId'];
    $Initials = $_POST['Initials'];   
    $SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
    $Initials = mysqli_real_escape_string($Conn,$Initials);
    $Initials = openssl_encrypt($Initials, $EncrypMethod, $Hash, false, $IV);
    $Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
    $DateTime_Consent = $Now->format('Y-m-d\TH:i:s');
    
    // Update Register:
    $Sql0 = "UPDATE Register SET `State` = 1, DateTime_Consent = '$DateTime_Consent' WHERE SubjectId = '$SubjectId'";
    if ($Conn->query($Sql0) == false) {
        die("Query Sql0 failed to execute successfully!");
    }
	
	// Add to ConsentLog:
	$Sql1 = "CALL RecordConsentLog('$SubjectId', '$DateTime_Consent', '$Initials')";
	if ($Conn->query($Sql1) === true) {
		// Redirect:
		$Url = GetTargetUrl($Conn, $SubjectId);
		$Conn->close();
        RedirectToUrl($Url);
	} else {
	    $Conn->close();
		die("Query Sql1 failed to execute successfully!");
	}
}
?>


