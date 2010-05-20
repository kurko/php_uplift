<?php

function write($string, $automaticEndOfLine = true){
    print($string);
    /*
     * Prints \n
     */
    if( $automaticEndOfLine )
        print "\n";
}

function breakLine($repeat = 1){
    for( $i = 0; $i < $repeat; $i++ )
        print "\n";
}

function division(){
    print "--------------------------------------------------------\n";
}

function arrayCleanEmptyValues($array){
    $empty_elements = array_keys($array,"");
    foreach ($empty_elements as $e)
        unset($array[$e]);

    return $array;
}

function questionTillAnswer(){

}

/*
 * ALIASES
 */
function wr($string, $automaticEndOfLine = true){ return write($string, $automaticEndOfLine); }
function br($repeat = 1){ return breakLine($repeat); }

function vd($str){
    var_dump($str);
}


?>