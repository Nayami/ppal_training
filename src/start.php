<?php
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

session_start();
require __DIR__ . '/../vendor/autoload.php';
$_SESSION[ 'user_id' ] = 1;
$api                   = new ApiContext(
	new OAuthTokenCredential(
		'AT1wFUoEwZNQyesYEGDONWfzuPknHgU5N7_UkrgJrfLuYUpCeEj8zT80qlDd2pNyWAr8iETNWmRjze56',
		'EMr67TozW1wIIq3bIQo5qkt0januXtyhFY3DkHFY9aRxHX8TT684NnKkQLKIP6IjKJy1aQ8njX4kYTMi'
	)
);
$api->setConfig( [
	'mode'                   => 'sandbox',
	'http.ConnectionTimeout' => 30,
	'log.LogEnabled'         => false,
	'log.FileName'           => '',
	'log.LogLevel'           => 'FINE',
	'validation.level'       => 'log'
] );

$db = new PDO( 'mysql:host=localhost;dbname=pp', 'root', '' );

$user = $db->prepare( "
	SELECT * FROM users
	WHERE id = :user_id
" );

$user->execute( [ 'user_id' => $_SESSION[ 'user_id' ] ] );
$user = $user->fetchObject();