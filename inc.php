<?php
$options = get_option('gboutique_options');


$plugin_dir_path = dirname(__FILE__);
$clientLibraryPath = $plugin_dir_path.'/library';
set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);
Zend_Loader::loadClass('Zend_Gdata'); //Verifier si on a besoin de charger les class...
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
Zend_Loader::loadClass('Zend_Gdata_App_AuthException');
Zend_Loader::loadClass('Zend_Http_Client');
$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
$client = Zend_Gdata_ClientLogin::getHttpClient($options['email'],$options['pass'], $service);
$spreadsheetService = new Zend_Gdata_Spreadsheets($client);
$query = new Zend_Gdata_Spreadsheets_ListQuery();
$query->setSpreadsheetKey($options['spreadsheetKey']);

// Fetch the worksheet id pf the Products sheet. Don't change the name of the tab in gdoc !
$query_worksheet = new Zend_Gdata_Spreadsheets_DocumentQuery();
$query_worksheet->setSpreadsheetKey($options['spreadsheetKey']);
$feed = $spreadsheetService->getWorksheetFeed($query_worksheet);
foreach($feed->entries as $entry){
	if ($entry->title->text=='Products'){$worksheetId_Products=basename($entry->id);}
	elseif ($entry->title->text=='Sales'){$worksheetId_Sales=basename($entry->id);}
	elseif ($entry->title->text=='Settings'){$worksheetId_Settings=basename($entry->id);}
}

// Fetch Settings
$query_Settings = new Zend_Gdata_Spreadsheets_ListQuery();
$query_Settings->setSpreadsheetKey($options['spreadsheetKey']);
$query_Settings->setWorksheetId($worksheetId_Settings);
$listFeed_Settings = $spreadsheetService->getListFeed($query_Settings);
foreach($listFeed_Settings->entries as $entry) {
		$rowData = $entry->getCustom();
		$Settings[$rowData[0]->getText()]=$rowData[1]->getText();
	}	
// End of Fetch Settings

// Create Products Array :
$query->setWorksheetId($worksheetId_Products);
$listFeed_Products = $spreadsheetService->getListFeed($query);
foreach($listFeed_Products->entries as $entry) {
	$rowData = $entry->getCustom();
	$Products[]=array('description'=>$rowData[0]->getText(),'amount'=>$rowData[1]->getText(),'img'=>$rowData[2]->getText());
}

// Fetch Sales
$query_Sales = new Zend_Gdata_Spreadsheets_ListQuery();
$query_Sales->setSpreadsheetKey($options['spreadsheetKey']);
$query_Sales->setWorksheetId($worksheetId_Sales);
$listFeed_Sales = $spreadsheetService->getListFeed($query_Sales);
$saleid=1;
foreach($listFeed_Sales->entries as $entry) {
	$rowData = $entry->getCustom();
	foreach($rowData as $customEntry) {
		$Sales[$saleid][$customEntry->getColumnName()]=$customEntry->getText();
	}
	$saleid++;
}
foreach ($Sales[1] as $key=>$Value){
	$Header_Sales[]=$key;
}
// End of Fetch Sales

if ($Settings['sandbox']){
	$paypalurl="https://www.sandbox.paypal.com/cgi-bin/webscr";
}
else{
	$paypalurl="https://www.paypal.com/cgi-bin/webscr";
}
?>