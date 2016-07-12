<?php

require_once( 'LoadTestingTest.class.php' );

class ExampleTest extends LoadTestingTest
{
  public function startTest()
  {
    $startUserTime = time();

    for ( $x = 1; $x <= 100; $x++ )
    {
        $startTime = time();
        sleep( mt_rand( 2, 5 ) );
        $diff = microtime( true ) - $startTime;
        recordURLPageLoad( $x, $startTime, $diff );
    }
    $endUserElapsed = microtime(true) - $startUserTime;
    recordPageTime( $startUserTime, $endUserElapsed );

    return true;
  }
}
