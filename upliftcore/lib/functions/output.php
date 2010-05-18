<?php


function exitOnUknownCommand($command){
    echo("Unknown command: ".$command.".");
    echo("\n");
    exit();
}

function exitln($str = ""){
    exit("$str\n");
}
?>