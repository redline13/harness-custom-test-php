<?php

/**
 * used to capture to look for caught errors on exit.
 * @internal
 */
register_shutdown_function( "fatal_handler" );
function fatal_handler() {
  $error = error_get_last();
	if ($error != null && $error['type'] === E_ERROR)
		echo  "recordFatalError: {$error['message']}\n";
}

/**
 * Record the load time for a USER, this is typically across multiple iterations within a user.
 * @param int $ts timestamp of request end
 * @param int $time elapsed time request took
 * @param boolean $error was this request an error
 * @param int $kb size of request
 */
function recordPageTime($ts, $time, $error = false, $kb = 0) {
	echo "recordPageTime($ts,$time,$error,$kb)\n";
}

/**
 * Record a user thread starting.
 * @param int $userid
 * @param int $ts time user/thread was strarted
 */
function recordUserStart( $userid, $ts ) {
	echo "recordUserStart($userid,$ts)\n";
}

/**
 * Record a user thread stopping
 * @param int $userid 
 * @param int $ts
 * @param int $time elapsed time
 * @param boolean $err if this user stopped because of an error.
 */
function recordUserStop( $userid, $ts, $time, $err = false ) {
	echo "recordUserStop($userid,$ts,$time,$err)\n";
}

/**
 * Record URL load
 * @param string $url Naming the request, can be URL or just NAME
 * @param int $ts timestamp of request end
 * @param int $time elapsed time request took
 * @param boolean $error was this request an error
 * @param int $kb size of request
 */
function recordURLPageLoad($url, $ts, $time, $error = false, $kb = 0) {
	echo "recordURLPageLoad($url,$ts,$time,$error,$kb)\n";
}

/**
 * Record generic error message, not specific to a request or user.
 * @param String $error error string
 */
function recordError($error) {
	echo "recordError: $error\n";
}

/** 
 * Record download size, not specific to a request.  
 * You can now use @see recordPageTime or @see recordURLPageLoad and include download size.
 */
function recordDownloadSize($kb) {
	echo "recordDownloadSize($kb)\n";
}

/** 
 * Record progress of the test (0-100) 
 * @param int $testNum Test Number available $this->testNum
 * @param int $percent should reflect test completeness.
 */
function recordProgress($testNum, $percent) {
	echo "recordProgress($testNum,$percent)\n";
}

try {
	// Parse ini file
	$config = @parse_ini_file('loadtest.ini');

	// Update running count
	echo "LoadAgentRunning\n";

	// Register shutdown function
	register_shutdown_function(function() {
			$error = error_get_last();
			if ($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR)
				echo 'PHP Fatal Error in ' . basename($error['file']) . '[' . $error['line'] . ']: ' . $error['message'] . "\n";
			echo "Completed Test\n";
	});

	// Get classname
	if ( !empty($argv[1] ) )
		$classname = $argv[1];
	else if (empty($config['classname']))
		throw new LogicException('Classname not specified.');
	else 
		$classname = $config['classname'];

	// Set up object
	require_once($classname.'.php');
	$test = new $classname( 1, null );
	if (method_exists($test, 'setIniSettings'))
		$test->setIniSettings($config);
	
	// Check for delay
	if (isset($config['min_delay_ms']) && isset($config['max_delay_ms']))
	{
		if (method_exists($test, 'setDelay'))
			$test->setDelay($config['min_delay_ms'], $config['max_delay_ms']);
	}
	
	// Start test
	if (!method_exists($test, 'startTest'))
		throw new LogicException('Invalid test script.');
	$test->startTest();
} catch (Exception $e) {
	$msg = $e->getMessage();
	echo "recordException: $msg\n";
}
