<?php

/* Copyright: Bickel Advisory Services, LLC. */

require_once('LoadTestingSession.class.php');

/** Load Testing Test Exception */
class LoadTestingTestException extends Exception {};

/** Load Testing Test */
abstract class LoadTestingTest
{
	/** @var LoadTestingSession Access to object to invoke requests to pages and wrap CURL. */
	protected $session = null;
	
	/** Verbose mode */
	protected $verbose = false;
	
	/** Ini settings */
	protected $iniSettings = array();
	
	/** Test Number */
	protected $testNum = null;
	
	/**
	 * Constructor
	 * @param int $testNum Test Number
	 * @param string $rand Random token for test
	 * @param string $resourceUrl Optional URL specifying host that resources will
	 * 	be loaded for.  The hostname is grabbed from this URL.
	 */
	public function __construct($testNum, $rand, $resourceUrl = null)
	{
		// Set up session
		$this->session = new LoadTestingSession($testNum, $rand);
		
		// Save test number
		$this->testNum = $testNum;
		
		// Load resource only from base url
		if ($resourceUrl && preg_match('/^(https?:\\/\\/[^\\/]+)(?:\\/.*)?$/', $resourceUrl, $match))
			$this->session->loadableResourceBaseUrl = $match[1] . '/';
	}
	
	/** Enable resource loading */
	public function enableResourceLoading() {
		$this->session->enableResourceLoading();
	}
	
	/** Disable resource loading */
	public function disableResourceLoading() {
		$this->session->disableResourceLoading();
	}
	
	/**
	 * Set delay between page loads
	 * @param int $minDelayMs Minimum delay in ms
	 * @param int $maxDelayMs Maximum delay in ms
	 */
	public function setDelay($minDelayMs, $maxDelayMs) {
		$this->session->setDelay($minDelayMs, $maxDelayMs);
	}

	/**
	 * Gets most recently used delay.
	 * @return int
	 */
	public function getDelay(){
		return $this->session->getDelay();
	}

	/**
	 * Set ini settings
	 * @param array $iniSettings INI settings
	 */
	public function setIniSettings($iniSettings) {
		$this->iniSettings = $iniSettings;
	}

	/**
	 * Get the iniSettings (hashmap)
	 * @return HashMap Settings for test
	 */
	public function getIniSettings(){
		return $this->iniSettings;
	}

	/**
	 * Get the sesssion object, which wraps CURL for simple tests.
	 * @return LoadTestingSession Session object.
	 */
	public function getSession(){
		return $this->session;
	}

	/** Verbose */
	public function verbose() {
		$this->verbose = true;
		$this->session->verbose();
	}
	
	/** Non-verbose */
	public function nonVerbose() {
		$this->verbose = false;
		$this->session->nonVerbose();
	}
	
	/** Start the test */
	public abstract function startTest();
}
