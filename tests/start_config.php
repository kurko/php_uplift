<?php



define('THIS_TO_BASE_DIR', '');
define('UPLIFT_CORE_DIR', THIS_TO_BASE_DIR.'upliftcore/');
define('DIRECTORIES_CONFIG_FILE', UPLIFT_CORE_DIR.'config/directories.php');
include_once(DIRECTORIES_CONFIG_FILE);

foreach( glob( FUNCTIONS_DIR."*.php") as $function ){
    include_once $function;
}

function __autoload($class){
    if( COMMANDS_DIR.'lib/class/'.$class.".php")
        include_once(UPLIFT_CORE_DIR.'lib/class/'.$class.".php");
    else if( CLASS_DIR.$class.".php")
        include_once(UPLIFT_CORE_DIR.'lib/class/'.$class.".php");
}

?>