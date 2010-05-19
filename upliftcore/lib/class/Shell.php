<?php
class Shell
{

    /**
     * Every argument.
     *
     * @var <array>
     */
    public $args;

    public $run;
    /**
     * Everything typed in the command line, except words starting with - or --.
     *
     * @var <array>
     */
    public $commands = array();

    /**
     * Contains only arguments starting with - or --.
     *
     * @var <array>
     */
    public $options = array();

    function __construct(){

        global $argv;
        $actualArgv = $argv;
        if( is_array($actualArgv) ){
            foreach( $actualArgv as $index => $value ){

                if( strpos($value,"-") === 0 ){
                    /*
                     * Options (words starting with - or --)
                     */
                    if( strpos($value,"--") === 0 )
                        $value = substr($value, 2);
                    else
                        $value = substr($value, 1);

                    $this->options[] = $value;
                } else {
                    /*
                     * Commands.
                     */
                    $this->commands[] = $value;
                }
                $this->args[] = $value;
            }
        }
        unset($this->commands[0]);
        sort($this->commands);

        //vd($this->commands);
        if( !empty($this->commands[1]) )
            $this->run = $this->commands[1];
        
    }

    /**
     * hasOption()
     *
     * If $params
     *
     * @param <mixed> $params
     * @return <bool>
     */
    public function hasOption($params){
        if( is_string($params) ){
            return in_array($params, $this->options);
        }
        /*
         * Makes sure every word is containned in arguments
         */
        else if( is_array($params) ){
            return $this->_allInArray($params, $this->options);
        }
    }

    /**
     * hasCommands()
     *
     * If $params
     *
     * @param <mixed> $params
     * @return <bool>
     */
    public function hasCommand($params){
        if( is_string($params) ){
            return in_array($params, $this->commands);
        }
        /*
         * Makes sure every word is containned in arguments
         */
        else if( is_array($params) ){
            return $this->_allInArray($params, $this->commands);
        }
    }

    /**
     * _allInArray()
     *
     * Checks if all given values (needle) exists in $haystack.
     *
     * @param <array> $needles
     * @param <array> $haystack
     * @return <bool>
     */
    public function _allInArray($needles, $haystack){
        if( !is_array($haystack) )
            return false;

        $has = true;
        foreach( $needles as $word ){
            if( !in_array($word, $haystack) ){
                $has = false;
            }
        }

        return $has;
    }

}
?>
