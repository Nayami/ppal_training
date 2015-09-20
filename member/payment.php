<?php
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;

require '../src/start.php';

$payer        = new Payer();
$amount       = new Amount();
$details      = new Details();
$payment      = new Payment();
$transaction  = new Transaction();
$redirectUrls = new RedirectUrls();

// Payer
$payer->setPaymentMethod( 'paypal' );

// Details
$details->setShipping( '2.00' )
        ->setTax( '0.00' )
        ->setSubtotal( '20.00' );

$total = number_format( $details->getSubtotal() + $details->getShipping() + $details->getTax(), 2 );

// Amount
$amount->setCurrency( 'GBP' )
       ->setTotal( $total )
       ->setDetails( $details );

// Transaction
$transaction->setAmount( $amount )
            ->setDescription( 'Membership' );

// Payment
$payment->setIntent( 'sale' )
        ->setPayer( $payer )
        ->setTransactions( [ $transaction ] );

// Redirect Urls
$redirectUrls->setReturnUrl( 'http://localhost/pp/paypal/pay.php?approved=true' )
             ->setCancelUrl( 'http://localhost/pp/paypal/pay.php?approved=false' );

$payment->setRedirectUrls( $redirectUrls );

try {
	$payment->create( $api );
	$hash                      = md5( $payment->getId() );
	$_SESSION[ 'paypal_hash' ] = $hash;
	$store                     = $db->prepare( "
		INSERT INTO transactions_paypal (user_id, payment_id, hash, complete)
		VALUES (:user_id, :payment_id, :hash, 0)
	" );
	$store->execute( [
		'user_id'    => $_SESSION[ 'user_id' ],
		'payment_id' => $payment->getId(),
		'hash'       => $hash
	] );

} catch ( PayPalConnectionException $e ) {
	header( 'Location: ../paypal/error.php' );
}

foreach ( $payment->getLinks() as $link ) {
	if ( $link->getRel() === 'approval_url' ) {
		$redirectUrl = $link->getHref();
	}
}

header( 'Location: ' . $redirectUrl );