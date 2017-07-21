<?php
	$html='<ul>';
// Loop over Products :
foreach($Products as $Product) {
	$html.='<li>';
	$html.='<h2>'.$Product['description'].'</h2><p>'.$Product['amount'].'</p><img src="'.$Product['img'].'" height="100">';
	// Print paypal button
	$html.='<p>';
	$html.='<form name="_xclick" action="'.$paypalurl.'" method="post">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="'.$Settings['paypalemail'].'">
		<input type="hidden" name="currency_code" value="EUR">
		<input type="hidden" name="item_name" value="'.$Product['description'].'">
		<input type="hidden" name="amount" value="'.$Product['amount'].'">
		<input type="hidden" name="notify_url" value="'.plugins_url( 'ipn.php' , __FILE__ ).'">
		<input type="hidden" name="cancel_ return" value="'.$Settings['shopurl'].'">
		<input type="hidden" name="return" value="'.$Settings['shopurl'].'">';
	if ($Settings['buytext']){
		$html.= '<input type="submit" class="submitbutton" name="submit" value="'.$Settings['buytext'].'"/>';
	}	
	else{
		$html.= '<input type="image" src="http://www.paypal.com/fr_XC/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">';
	}
	$html.= '</form>';
	if ($Settings['paypalcart']){
	$html.= '<form name="_xclick" target="paypal" action="'.$paypalurl.'" method="post">
		<input type="hidden" name="cmd" value="_cart">
		<input type="hidden" name="business" value="'.$Settings['paypalemail'].'">
		<input type="hidden" name="currency_code" value="EUR">
		<input type="hidden" name="item_name" value="'.$Product['description'].'">
		<input type="hidden" name="amount" value="'.$Product['amount'].'">
		<input type="hidden" name="notify_url" value="'.plugins_url( 'ipn.php' , __FILE__ ).'">
		<input type="hidden" name="cancel_ return" value="'.$Settings['shopurl'].'">
		<input type="hidden" name="return" value="'.$Settings['shopurl'].'">
		<input type="image" src="http://www.paypal.com/fr_XC/i/btn/sc-but-01.gif" border="0" name="submit" alt="Effectuez vos paiements via PayPal : une solution rapide, gratuite et sécurisée">
		<input type="hidden" name="add" value="1">
		</form>';
	}
	$html.= '</p>';
	$html.= '</li>';
} // End Loop over Products
$html.= '</ul>';


if ($Settings['paypalcart']){
	// See Cart button :
	$html.= '<div><form name="_xclick" target="paypal" action="'.$paypalurl.'" method="post">
		<input type="hidden" name="cmd" value="_cart">
		<input type="hidden" name="business" value="'.$Settings['paypalemail'].'">
		<input type="hidden" name="notify_url" value="'.plugins_url( 'ipn.php' , __FILE__ ).'">
		<input type="hidden" name="cancel_ return" value="'.$Settings['shopurl'].'">
		<input type="hidden" name="return" value="'.$Settings['shopurl'].'">
		<input type="image" src="https://www.paypal.com/fr_XC/i/btn/view_cart.gif" border="0" name="submit" alt="Effectuez vos paiements via PayPal : une solution rapide, gratuite et sécurisée">
		<input type="hidden" name="display" value="1">
		</form></div>';
}
?>