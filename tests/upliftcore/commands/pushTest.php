<?php
require_once 'PHPUnit/Framework.php';
require_once 'tests/start_config.php';

class pushTest extends PHPUnit_Framework_TestCase
{
    var $obj;

    public function setUp(){
        /*
         * current command
         */
        $c = 'push';

        $this->obj = new $c;//new $modInfo['className']();
    }


}
?>