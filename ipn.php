<?php
include('inc.php');
error_reporting(E_ALL ^ E_NOTICE); 
$header = "";$details = ""; 
$req = 'cmd=_notify-validate'; 
if(function_exists('get_magic_quotes_gpc')){$get_magic_quotes_exits = true;} 
foreach ($_POST as $key => $value){
	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){$value = urlencode(stripslashes($value));}
	else {$value = urlencode($value);}
	$req .= "&$key=$value";
}
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n"; 
$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n"; 
if ($Settings['sandbox']){
	$paypalurl='ssl://www.sandbox.paypal.com';
	}
else{
	$paypalurl='ssl://www.paypal.com';
}
$fp = fsockopen ($paypalurl, 443, $errno, $errstr, 30);
if ($Settings['debug']){mail($email,'ipn file found','ipn file found');}
if ($fp){
	fputs ($fp, $header . $req); 
	while (!feof($fp)){
		$res = fgets ($fp, 1024); 
		if (strcmp ($res, "VERIFIED") == 0){
			// Check the payment_status is Completed
			if ($Settings['debug']){mail($email,'verified','verified');}
			if ($_POST['payment_status']=='Completed'){
				if ($Settings['receiveemailnotification']){
					foreach ($_POST as $key => $value){$details .= $key . " = " .$value ."\n\n"; }				
					mail($email, "[gBoutique][log] Sales completed ", "\n"."Log :"."\n".$details."\n".$req);
				}
				if ($Settings['debug']){
					foreach ($_POST as $key => $value){$details .= $key . "=" .$value ."&"; }
				}else{
					$details="-";
				}
				//Add to Sales in google doc using Zend :
				Zend_Loader::loadClass('Zend_Gdata');
				Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
				Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
				Zend_Loader::loadClass('Zend_Gdata_App_AuthException');
				Zend_Loader::loadClass('Zend_Http_Client');
				$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
				$client = Zend_Gdata_ClientLogin::getHttpClient($options['email'],$options['pass'], $service);
				$spreadsheetService = new Zend_Gdata_Spreadsheets($client);

				$rowData=array(
					'paymentdate'=>$_POST['payment_date'],
					'payeremail'=>$_POST['payer_email'],
					'lastname'=>$_POST['last_name'],
					'firstname'=>$_POST['first_name'],
					'amount'=>$_POST['mc_gross'],
					'details'=>$details);
					// Insert row in google spreadsheet :
				$insertedListEntry = $spreadsheetService->insertRow($rowData,$spreadsheetKey,$worksheetId_Sales);
			}//End if completed
		}
		elseif (strcmp ($res, "INVALID") == 0){
			// If 'INVALID', send an email. TODO: Log for manual investigation. foreach ($_POST as $key => $value){$details .= $key . " = " .$value ."\n\n";}
		}  
	} // End of While
	fclose ($fp);
}
else{
	//
}
?>