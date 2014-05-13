<?php
    // include the class autoloader
    include(KA.'/library/Autoload.php');

	// include all output in the template after processing, this can be set to 0 later
	$GLOBALS['use_template']=1;
	$GLOBALS['use_test_db']=0;

	//-------------- Check for a test request
	$urlArray=explode('/',$_SERVER['REQUEST_URI']);
	array_shift($urlArray);
	if ($urlArray[0] == 'test')
	{   
		$GLOBALS['use_test_db']=1;
	}  
	//-------------
?>
