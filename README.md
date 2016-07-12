An offline tester to validate your custom PHP RedLine13 Load Test.

# Quick Start
### Specify test on Command Line
* php runLoadTest.php ExampleTest

### Specify test in loadtest.ini
* php runLoadTest.php 

# Simulating inputs for test
Modify loadtest.ini with parameters, in your custom test you can access settings via

```php
<?php
require_once( 'LoadTestingTest.class.php' );

class ExampleTest extends LoadTestingTest
{
	/**
	 * Start test, don't forget we have access to
	 * $this->iniSettings = all config data for test
	 * $this->testNum = test #
	 * $this->session = Run test with RedLine13 CURL wrapper and maintain cookies for user session.
	 */
	public function startTest()
	{
		$config = $this->iniSettings;
		
		$myThing = $config['myThing'];
```

# Output
The load test will generate local information on performance results and errors.

# More on Custom Performance Tests
https://www.redline13.com/blog/writing-a-custom-load-test/
