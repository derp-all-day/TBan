<?php
/* * * * * * * * * * * * * * * * * *
 * TITLE: TBan v1.0                *
 * AUTHOR: Andrew B.               *
 * DESCRIPTION: Automatically ban  *
 * clients making to many requests *
 * in to short of a time.          *
 * * * * * * * * * * * * * * * * * */

/* * * * * * * * * *
 * SCRIPT SETTINGS *
 * * * * * * * * * */

# The rate (seconds) at witch to refresh the session unless user is temporarily banned
# then it will refresh when the ban is lifted
$refreshRate = 60;

# The initial limit of requests for ever refresh period
$requestLimit = 60;

# The request limit for if the user is already banned and then exceeds this limit it will
# add on the extreme ban time to the current ban time
$extremeRequestLimit = 100;

# The initial amount of time to ban a user when the user exceeds the request limit
$banTime = 30;

# The limit (seconds) to add on to the temporary ban time if the user exceeds the
# extreme request limit
$extremeBanTime = 560;


/* * * * * * * *
 * THE SCRIPT  *
 * * * * * * * */
 
# Make sure session variables are set
if(empty($_SESSION['lastaccess'])) {
	$_SESSION['lastaccess'] = time();
	$_SESSION['tempban']    = false;
	$_SESSION['banlift']    = time();
	$_SESSION['requests']   = 0;
}

# Increase the request count by 1
$_SESSION['requests'] = $_SESSION['requests'] + 1;

# If user is temporarily banned and requests have increased to the extreme request limit
# then add $extremeBanTime to the ban time limit and throw HTTP/1.1 500 from now on until
# ban is up.
if($_SESSION['requests'] === $extremeRequestLimit) {
	$_SESSION['banlift'] = time() + $extremeBanTime;
	die();
} elseif($_SESSION['tempban'] && $_SESSION['banlift'] > time() && $_SESSION['requests'] > $extremeRequestLimit) {
	header('HTTP/1.1 500 Internal Server Error');
	die();
}

# If user is temporarily banned and the ban time has not passed then die()
if($_SESSION['tempban'] && $_SESSION['banlift'] > time()) {
	die(
		'<p>Woops! Seems you have sent too many requests in too short of a time. Please wait 30 seconds until '.
		@date("F j, Y, g:i:s a", $_SESSION['banlift']).
		' to access VoidRS Again.</p> '
	);
}

# If user is temporarily banned but the ban time is up then unban them and reset request count back to 0
if($_SESSION['tempban'] && $_SESSION['banlift'] < time()) {
	$_SESSION['requests'] = 0;
	$_SESSION['tempban']  = false;
}

# Reset the session every 60 seconds unless user is banned then waits
if(($_SESSION['lastaccess'] - $refreshRate) > time()  && !$_SESSION['tempban']) {
	$_SESSION['banlift']    = time();
	$_SESSION['lastaccess'] = time();
	$_SESSION['requests']   = 0;
}

# if user exceeds the limit of requests for the given session refresh rate then ban them
if($_SESSION['requests'] > $requestLimit && !$_SESSION['tempban']) {
	$_SESSION['banlift'] = time() + $banTime;
	$_SESSION['tempban'] = true;
}
?>
