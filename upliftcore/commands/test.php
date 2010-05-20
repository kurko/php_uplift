<?php
/**
 * Description of test
 *
 * @author kurko
 */
class test extends Uplift {

    function __run(){

        $testIsOk = true;

        try {
            write("Testing (this may take a few moments):");
            breakLine();
            /*
             * CONFIGURATION EXITS
             */
            $config = $this->loadConfig();
            if( !$config ){
                write("\tUplift Initialized: Failed.");
                write("\tSolution: type 'uplift init' in your project's root folder.");
                $testIsOk = false;
                breakLine();
                write("Stop testing.");
                breakLine();
                exit();
            } else {
                write("\tUplift Initialized: Ok.");
            }

            /*
             * SSH CONNECTION IS OK
             */
            $ssh = new Net_SSH2($this->config['ssh']['server']);
            if (!$ssh->login($this->config['ssh']['username'], $this->config['ssh']['password'])) {
                write("\tSSH Connection: Failed.");
                $testIsOk = false;
            } else {
                write("\tSSH Connection: Ok.");
                $sftp = new Net_SFTP($this->config['ssh']['server']);
                $sftp->login($this->config['ssh']['username'], $this->config['ssh']['password']);
            }

            /*
             * ROOT EXISTS
             */

            if( !$sftp->chdir($this->config['ssh']['root_dir']) ){
                write("\tRemote root folder exists: Failed.");
                $testIsOk = false;
            } else {
                write("\tRemote root folder exists: Ok.");
            }
            //$ssh->exec("ls ".$this->config['ssh']['root_dir']." -la");

        } catch(Exception $e){
            
        }


        if( $testIsOk ){
            breakLine();
            write("Everything's ok.");
        } else {
            breakLine();
            write("Some tests failed.");
        }
    }
    
    /*
     *
     * HELP
     *
     */
    /**
     * help()
     *
     * Shows help.
     */
    public function help(){
        wr("Tests if it's alright at the environment.",false);
    }

}
?>