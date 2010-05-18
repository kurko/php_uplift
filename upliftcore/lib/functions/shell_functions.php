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

function vd($str){
    var_dump($str);
}


?>