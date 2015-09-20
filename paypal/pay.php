<?php
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require( '../src/start.php' );

if ( isset( $_GET[ 'approved' ] ) && $_GET[ 'approved' ] === 'true' ) {

	$approved = $_GET[ 'approved' ] === 'true';
	if ( $approved ) {
		$payerId   = $_GET[ 'PayerID' ];
		$paymentId = $db->prepare( "
			SELECT payment_id
			FROM transactions_paypal
			WHERE hash = :hash
		" );
		$paymentId->execute( [
			'hash' => $_SESSION[ 'paypal_hash' ]
		] );

		$paymentId = $paymentId->fetchObject()->payment_id;

		// Get the PayPal payment
		$payment   = Payment::get( $paymentId, $api );
		$execution = new PaymentExecution();
		$execution->setPayerId( $payerId );
		// Execute PayPal payment (charge)
		$payment->execute( $execution, $api );
		// Update transaction
		$updateTransaction = $db->prepare( "
			UPDATE transactions_paypal
			SET complete = 1
			WHERE payment_id = :payment_id
		" );
		$updateTransaction->execute( [
			'payment_id' => $paymentId
		] );
		// Set the user as a member
		$setMemeber = $db->prepare( "
			UPDATE users
			SET member = :member_status
			WHERE id = :user_id
		" );
		$setMemeber->execute( [ 'member_status' => 1, 'user_id' => $_SESSION[ 'user_id' ] ] );
		// Unset PayPal hash
		unset( $_SESSION[ 'paypal_hash' ] );

		header( 'Location: ../member/complete.php' );

	} else {
		header( 'Location: ../paypal/cancel.php' );
	}
}