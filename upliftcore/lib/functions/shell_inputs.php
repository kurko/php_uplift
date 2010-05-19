<?php
function inputText($lengthToRead = ""){

    $var_stdin = fopen('php://stdin', 'r');

    $text = fgets($var_stdin, 100);
    $text = str_replace( array("\n","\r"), "", $text);
    return $text;
    //if( !empty($lengthToRead) )
        return fgets($var_stdin, 100);
    //else
        return fgets($var_stdin, 100);
}

function inputYesNo($question, $lengthToRead = ""){


    $hasAnswer = false;
    while( !$hasAnswer ){
        if( !empty($question) )
            write($question, false);
        
        $result = inputText($lengthToRead);
        if( in_array($result, array("yes","y") ) ){
            $hasAnswer = true;
            $hasYes = true;
        } else if( in_array($result, array("no","n") ) ){
            $hasAnswer = true;
            $hasYes = false;
        }
    }

    return $hasYes;

}

function inputPassword($stars = false){
    // Get current style
    $oldStyle = shell_exec('stty -g');

    if ($stars === false) {
        shell_exec('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
    } else {
        shell_exec('stty -icanon -echo min 1 time 0');

        $password = '';
        while (true) {
            $char = fgetc(STDIN);

            if ($char === "\n") {
                break;
            } else if (ord($char) === 127) {
                if (strlen($password) > 0) {
                    fwrite(STDOUT, "\x08 \x08");
                    $password = substr($password, 0, -1);
                }
            } else {
                fwrite(STDOUT, "*");
                $password .= $char;
            }
        }
    }

    // Reset old style
    shell_exec('stty ' . $oldStyle);

    // Return the password
    return $password;
}
?>