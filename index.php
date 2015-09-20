<?php
	require( 'src/start.php' );
?>
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>PayPal test Transactions</title>
</head>
<body>

<?php if ( $user->member === '1') : ?>
	<p>You are memeber</p>
<?php else: ?>
	<p>You are not a member <a href="member/payment.php">Become a memeber</a></p>
<?php endif; ?>
</body>
</html>