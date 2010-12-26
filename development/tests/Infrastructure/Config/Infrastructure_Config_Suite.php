<?php 
require_once(ROOT_DIR . 'tests/AllTests.php');

class Infrastructure_Config_Suite
{
	public static function suite()
    {
    	return TestHelper::GetSuite('tests/Infrastructure/Config', array(__CLASS__, "IsIgnored"));
    }
    
    public static function IsIgnored($fileName)
    {
    	return false;
    }
}
?>