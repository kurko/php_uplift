<?php
/**
 * Description of init
 *
 * @author kurko
 */
class __empty extends Uplift {

    function __run(){

        write("These are the commands are at your disposal:");
        breakLine();
        foreach( glob(COMMANDS_DIR."*", GLOB_MARK) as $filename){
            $file = basename( $filename );
            $command = str_replace(".php", "", $file);

            if( strpos($file,"__") !== 0 )
                print "\t".$command."\n";
        }

    }

}
?>
