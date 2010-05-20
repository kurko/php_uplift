<?php
/**
 * Description of Uplift
 *
 * @author kurko
 */
class Uplift extends Shell
{

    public $localFiles = array();

    /**
     * All configuration about the project is here
     *
     * @var <array>
     */
    public $config = array();

    /**
     * Depending on the sendMode set up, the engine will work differently.
     *
     * mode=all (default)
     *      When the sendMode is set to all, uplift will create a new folder
     *      each time it pushes files to the server.
     *
     * mode=specific
     *      When you to push only specific files (not all the project), change the
     *      sendMode to 'specific'.
     *
     *      Whenever you specify an interval (e.g. uplift push today), mode will
     *      be automatically set to 'specific'.
     *
     * @var <string>
     */
    public $sendMode = "all"; // 'specific'

    /**
     *
     * @var <ssh object> Instance of SSH
     */
    public $ssh;
    /**
     *
     * @var <sftp object> Instance of SFTP
     */
    public $sftp;

    /**
     *
     * @var <string> 
     */
    public $rootDir;

    function  __construct() {

        parent::__construct();
        /*
         * Load Configurations
         */
        $this->loadConfig();

        if( !empty($this->config['ssh']['root_dir']) )
            $this->rootDir = $this->config['ssh']['root_dir'];

        if( in_array("help", $this->options) OR
            in_array("h", $this->options) )
        {
            $this->help();
            return true;
        }
        
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

    /**
     * connectSSH()
     *
     * @return <ssh object>
     */
    function connectSSH(){
        if( is_object($this->ssh) )
            return $this->ssh;    

        $ssh = new Net_SSH2($this->config['ssh']['server']);
        $ssh->login($this->config['ssh']['username'], $this->config['ssh']['password']);

        $this->ssh = $ssh;
        return $ssh;
    } // end connectSSH()

    /**
     * connectSFTP()
     *
     * @return <sftp object>
     */
    function connectSFTP(){
        if( is_object($this->sftp) )
            return $this->sftp;

        $sftp = new Net_SFTP($this->config['ssh']['server']);
        $sftp->login($this->config['ssh']['username'], $this->config['ssh']['password']);
        /*
         * Changes dir automatically.
         */
        $sftp->chdir($this->config['ssh']['root_dir']);

        $this->sftp = $sftp;
        return $sftp;
    } // end connectSFTP()

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

            if( class_exists('CONFIG') )
                $this->config = CONFIG::$default;

            if( !empty($this->config) )
                return true;

        }

        return false;
    }

    /**
     * getFiles()
     *
     * Lists all local files.
     *
     * @param <string> $dir
     * @return <array>
     */
    function getFiles($dir = "", $recursive = 0){

        $localFiles = array();

        foreach( glob($dir."*", GLOB_MARK) as $filename){

            if( $recursive == 0 ){
                $rootFile = $filename;

                if( substr($rootFile, strlen($rootFile)-1, strlen($rootFile)) == "/" ){
                    $rootFile = substr($rootFile, 0, strlen($rootFile)-1);
                }

                $this->rootItems[] = $rootFile;
            }

            if( is_dir($filename)){
                $file = false;
                $localFiles = array_merge($this->getFiles($filename, 1), $localFiles);
            } else if( is_file($filename) ){
                /*
                 * Verifies if has intervals
                 */
                if( $this->isOutOfInterval($filename) )
                    continue;
                
                $file = $filename;
                $localFiles[] = $filename;
            }

            if( !empty($file) AND
                $this->hasOption("list") )
            {
                print $file."\n";
            }

        }

        $this->localFiles = $localFiles;
        return $localFiles;

    } // end returnFiles();

    /**
     * isOutOfIntervavel()
     * 
     * Verifies how long ago a file has been modified, gets the wanted interval
     * and return true when the file is out of this given interval.
     * 
     * @param <string> $filename
     * @return <bool>
     */
    function isOutOfInterval($filename){
        $interval = false;

//        write(basename($filename)." - ". date("d/m/Y H:i:s", filemtime($filename))." - ". date("d/m/Y H:i:s", filectime($filename)) );
        $fileLastChange = filectime($filename);
        /*
         * Today modified files
         */
        if( $this->hasCommand("today") ){
            if( date("d/m/Y") != date("d/m/Y", $fileLastChange) )
                $interval = true;
        }
        /*
         * Yesterday modified files
         */
        else if( $this->hasCommand("yesterday") ){
            if( date("d/m/Y", mktime(-24, 0, 0)) != date("d/m/Y", $fileLastChange) )
                $interval = true;
        }
        /*
         * Hours given (1h, 2h, ...h, 10h, ...h)
         */
        else if( preg_match("/\s([0-9]+)h\s/", " ".implode(" ", $this->commands)." ", $time ) ){
            $givenInterval = mktime() - mktime(date("H")-$time[1]);
            $modifiedInterval = mktime() - $fileLastChange;
            if( $modifiedInterval > $givenInterval )
                $interval = true;
        }

        if( $interval )
            $this->sendMode = "specific";

        return $interval;
    } // end isOutOfInterval()

    /**
     * getLastVersion()
     *
     * Gets into the server and finds out what's the last version.
     *
     * @return <string>
     */
    function getLastVersion(){
        $sftp = $this->connectSFTP();

        $rootDir = $this->config['ssh']['root_dir'];
        if( substr($rootDir, strlen($rootDir)-1, strlen($rootDir)) != "/" ){
            $rootDir.= "/";
        }

        $pwd = $sftp->pwd();
        $sftp->chdir($rootDir.LIFTS_DIR);
        $versions = $sftp->nlist();

        /*
         * Find out what's is the last version
         */
        $treatedVersions = array();
        foreach( $versions as $version ){
            if( preg_match("/([0-9]{4})_([0-9]{2})_([0-9]{2})_([0-9]{2})/", $version, $time ) ){
                $treatedVersions[] = $version;
            }
        }
        sort( $treatedVersions );
        $treatedVersions = array_reverse( $treatedVersions );
        $sftp->chdir($pwd);

        if( !empty($treatedVersions) )
            return $treatedVersions[0];

        return false;
    }

    /*
     *
     * HELP
     *
     */

    
}
?>
