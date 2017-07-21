<?php
include('../../../wp-load.php');
include('inc.php');
$post_data['emailtemplate']=$Settings['emailtemplate'];
$post_data['billtemplate']=$Settings['billtemplate'];
$post_data['apiKey']=$options['apiKey'];
$post_data['emailapiKey']=$options['emailapiKey'];


$response=wp_remote_post('http://wpcb.fr/api/gboutique/billing.php',array('body' =>$post_data));
$html=unserialize($response['body']);

if ($html['Bill']){
	$Bill_html=$html['Bill'];
	$Email_html=$html['Email'];

	foreach ($Header_Sales as $Keyword){
	$Bill_html =str_replace('{'.$Keyword.'}',$Sales[$_GET['id']][$Keyword],$Bill_html);
	$Email_html =str_replace('{'.$Keyword.'}',$Sales[$_GET['id']][$Keyword],$Email_html);
	}
// Rebuild the link structure :
$Bill_html =str_replace('pubimage','https://docs.google.com/document/pubimage',$Bill_html);
//$Bill_html =str_replace('#header, #footer {','#header, #footer { display:none;',$Bill_html);

$Email_html =str_replace('pubimage','https://docs.google.com/document/pubimage',$Email_html);

	
if ($_GET['action']=='browser'){
	echo $Bill_html;
}
elseif ($_GET['action']=='download'){
	require_once("library/dompdf/dompdf_config.inc.php");
	$dompdf = new DOMPDF();
	$dompdf->load_html($Bill_html);
	$dompdf->render();
	$dompdf->stream("Bill_".$_GET['id'].".pdf");	
	}
elseif($_GET['action']=='email'){
	$clientLibraryPath = dirname($_SERVER['SCRIPT_FILENAME']).'/library';
	$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);
	require_once 'Zend/Loader.php';
	require_once 'Zend/Mail.php';
	Zend_Loader::loadClass('Zend_Mail_Transport_Smtp');
	Zend_Loader::loadClass('Zend_Mail');
	//if ($Settings['gmailemail']){
		// Use gmail smtp
		$config = array('ssl' => 'ssl','auth'=>'login','port' => 465,'username' => $email,'password' => $pass);
		$tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
	//}
	//else{
		// Use custom smtp
	//	$config = array('ssl' => 'ssl','auth'=>'login','port' => 465,'username' => $smtp_username,'password' => $smtp_password);
	//	$tr = new Zend_Mail_Transport_Smtp($smtp_server,$config);
	//}


	$mail = new Zend_Mail();
	$mail->setBodyText($Email_html);
	$mail->setBodyHtml($Email_html);
	$mail->setFrom($email,$Settings['fromnamebillingemail']);
	$mail->addTo($Sales[$_GET['id']]['payeremail'],$Sales[$_GET['id']]['firstname'].' '.$Sales[$_GET['id']]['lastname']);
	$mail->addBcc($email); // A copy for your record
	$mail->setSubject($Settings['subjectbillingemail']);

	require_once("library/dompdf/dompdf_config.inc.php");
	$dompdf = new DOMPDF();
	$dompdf->load_html($Bill_html);
	$dompdf->render();
	$pdfoutput = $dompdf->output(); 
	$filename = "Bill_".$_GET['id'].".pdf"; 
	$fp = fopen('tmp/'.$filename, "w"); 
	fwrite($fp, $pdfoutput); 
	fclose($fp); 

	$myFile=file_get_contents('tmp/'.$filename);
	$at = new Zend_Mime_Part($myFile);
	$at->type = 'application/pdf';
	$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
	$at->encoding = Zend_Mime::ENCODING_BASE64;
	$at->filename = $filename;
	$mail->addAttachment($at);
	$mail->send($tr);
}
else{
	echo $Bill_html;
}

}
else {
// Api error or wrong key
	echo 'Please consider a donation to obtain your key and unlock the Billing options';
}
?>