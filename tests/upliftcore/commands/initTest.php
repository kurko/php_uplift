<?php
require_once 'PHPUnit/Framework.php';
require_once 'tests/start_config.php';

class initTest extends PHPUnit_Framework_TestCase
{
    public $lastSaveId;

    public function setUp(){
        /*
         * current command
         */
        $c = 'init';

        $this->obj = new $c;//new $modInfo['className']();
    }


}
?>