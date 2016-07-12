<?php

/* Copyright: Bickel Advisory Services, LLC. */

/* Example INI file
classname = LoadTestingSinglePage
url = "http://mrc.localhost/"
*/

require_once('LoadTestingTest.class.php');

/** Single Page Load Testing */
class LoadTestingSinglePage extends LoadTestingTest
{
	/** Parameters */
	private $parameters = array(
		'get' => array(),
		'post' => array()
	);

	/**
	 * Constructor
	 * @param int $testNum Test number
	 * @param string $rand Random token for test
	 */
	public function __construct($testNum, $rand)
	{
		// Call parent constructor
		parent::__construct($testNum, $rand);
	}

	/**
	 * Set ini settings
	 * @param array $iniSettings INI settings
	 */
	public function setIniSettings($iniSettings)
	{
		parent::setIniSettings($iniSettings);

		// Check if we should load external resources
		if (!empty($this->iniSettings['load_resources']))
		{
			$this->session->loadableResourceBaseUrl = 'http';	// TODO: Should we limit which external resources are loaded?
			$this->enableResourceLoading();
		}

		// Set up parameters
		if (!empty($this->iniSettings['parameter_file']) && file_exists($this->iniSettings['parameter_file']))
		{
			if (($json = json_decode(file_get_contents($this->iniSettings['parameter_file']), true)))
			{
				if (!empty($json['get']))
					$this->parameters['get'] = $json['get'];
				if (!empty($json['post'])) {
					$this->parameters['post'] = array();
					foreach( $json['post'] as $parameter ) {
						$this->parameters['post'][$parameter['name']] = $parameter['val'];
					}
				}
			}
		}
	}

	/**
	 * Start the test
	 * @throws LoadTestingTestException
	 */
	public function startTest()
	{
		try {
			// Sleep for ramp up time
			if (isset($this->iniSettings['ramp_up_time_sec']) && $this->iniSettings['ramp_up_time_sec'] > 0 && isset($this->iniSettings['num_users']))
			{
				$sleep = (int)round($this->testNum/$this->iniSettings['num_users']*$this->iniSettings['ramp_up_time_sec']);
				sleep($sleep);
			}
			
			// Load page
			$this->loadPage();

			// Clean up session file
			$this->session->cleanup();

		} catch (Exception $e) {
			echo "Test failed.\n";

			// Throw exception
			throw $e;
		}
	}

	/**
	 * Load page
	 * @return LoadTestingPageResponse Page
	 * @throws LoadTestingTestException
	 */
	public function loadPage()
	{
		// Check for URL
		if (empty($this->iniSettings['url']))
			throw new LoadTestingTestException('URL not specified.');

		// Check for number of iterations
		$iterations = 1;
		if (isset($this->iniSettings['num_iterations']))
			$iterations = $this->iniSettings['num_iterations'];

		// Iterate
		for ($i = 0; $i < $iterations; $i++)
		{
			// Build URL
			$url = $this->iniSettings['url'];
			if (!empty($this->parameters['get']))
			{
				$paramStr = array();
				foreach ($this->parameters['get'] as &$tmp)
					$paramStr[] = urlencode($tmp['name']) . '=' . urlencode($tmp['val']);
				unset($tmp);
				$paramStr = implode('&', $paramStr);

				$questionPos = strpos($url, '?');
				$hashPos = strpos($url, '#');
				if ($questionPos !== false)
				{
					if ($hashPos !== false)
						$url = substr($url, 0, $hashPos) . '&' . $paramStr . substr($url, $hashPos);
					else
						$url .= '&' . $paramStr;
				}
				else
				{
					if ($hashPos !== false)
						$url = substr($url, 0, $hashPos) . '?' . $paramStr . substr($url, $hashPos);
					else
						$url .= '?' . $paramStr;
				}
			}

			// Should we store output as we test.
			$storeOutput = !empty( $this->iniSettings['store_output'] );

			// Load page
			$this->session->goToUrl($url, $this->parameters['post'], array(), $storeOutput );

			// Record progress
			recordProgress($this->testNum, ($i+1)/$iterations * 100);
		}
	}
}
