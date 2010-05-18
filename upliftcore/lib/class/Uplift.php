<?php
/**
 * Description of Uplift
 *
 * @author kurko
 */
class Uplift extends Shell
{

    public $localFiles = array();
    public $config = array();

    function  __construct() {

        parent::__construct();
        /*
         * Load Configurations
         */
        $this->loadConfig();
        
        /*
         * Loads phpseclib
         */
        $originPath = getcwd();
        chdir(LIB_DIR.'phpseclib/');
        include('Net/SSH2.php');
        include('Net/SFTP.php');
        chdir($originPath);

        if( !empty($config['ssh']['server']) AND
            !empty($config['ssh']['server']) AND
            !empty($config['ssh']['server']) )
        {
        }

        $this->__run();
    }

    function connectSSH(){
        $ssh = new Net_SSH2($this->config['ssh']['server']);
        $ssh->login($this->config['ssh']['username'], $this->config['ssh']['password']);
        
        return $ssh;
    }

    function connectSFTP(){
        $sftp = new Net_SFTP($this->config['ssh']['server']);
        $sftp->login($this->config['ssh']['username'], $this->config['ssh']['password']);
        $sftp->chdir($this->config['ssh']['root_dir']);

        return $sftp;
    }
    /**
     * loadConfig()
     *
     * Loads the entire configuration file and puts it into $this->config as
     * array.
     *
     * @return <bool>
     */
    function loadConfig(){
        if( is_file(CONFIG_FILE) ){
            include_once(CONFIG_FILE);
            $this->config = CONFIG::$default;

            if( !empty($this->config) )
                return true;

        }

        return false;
    }


    
}
?>
