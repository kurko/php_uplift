<?php
/**
 * Description of push
 *
 * @author kurko
 */

class push extends Uplift {

    public $rootItems;

    function __run(){

        $this->returnFiles();

        if( empty($this->localFiles) )
            return false;

        if( count($this->localFiles) == 1 )
            $fileWord = "file";
        else
            $fileWord = "files";

        if( inputYesNo("Push ".count($this->localFiles)." ".$fileWord." to the server? [yes, no]: ") ){
            $this->sendFiles();
        } else {
            exitln();
        }

    }

    /**
     * sendFiles()
     *
     * Send all files to the server.
     *
     * @return <bool>
     */
    function sendFiles(){
        $mode = "all";
        if( $this->hasOption["diff"] )
            $mode = "diff";

        $sftp = $this->connectSFTP();
        $ssh = $this->connectSSH();
        if( !$sftp->chdir(".lifts") ){
            $sftp->mkdir(".lifts");
        }
        
        $version = "test_lift/";//date("Y_m_d_H_i_s")."/";
        $sftp->chdir(".lifts");
        $sftp->mkdir($version);
        $sftp->chdir($version);
        $pwd = $sftp->pwd();

        print "Copying files: ";

        $rootDir = $this->config['ssh']['root_dir'];
        if( substr($rootDir, strlen($rootDir)-1, strlen($rootDir)) != "/" ){
            $rootDir.= "/";
        }

        $thisVersionDir = $rootDir.LIFTS_DIR.$version;
        $createdDirs = array();
        foreach( $this->localFiles as $filename ){
            $content = file_get_contents($filename);
            //write($filename);
            //print $content."\n";
            $dir = dirname($filename);
            $file = basename($filename);
            $completePath = $thisVersionDir.$dir;

            //print $filename."\n";
            if( !in_array($completePath, $createdDirs) ){
                $ssh->exec("mkdir -p ".$thisVersionDir.$dir);
                $createdDirs[] = $thisVersionDir.$dir;
            }

            $sftp->chdir($thisVersionDir.$dir);
            $sftp->put($file, $content);
            $sftp->chdir($pwd);
            print ".";

        }

        //var_dump($this->rootItems);

        if( $mode == "all" ){
            print "\n";
            print "Creating links: ";

            foreach( glob("*") as $filename){
                //print($filename."\n");
                if( in_array($filename, $this->rootItems) ){
                    $linkExec = "ln -sf ".$thisVersionDir.$filename." ".$rootDir.$filename;
                    $ssh->exec($linkExec);
                    print ".";
                }
            }
        }

        print "\n";

        return true;

    }

    /**
     * returnFiles()
     * 
     * Lists all local files.
     * 
     * @param <string> $dir
     * @return <array> 
     */
    function returnFiles($dir = "", $recursive = 0){

        $localFiles = array();

        foreach( glob($dir."*", GLOB_MARK) as $filename){

            if( $recursive == 0 ){
                $rootFile = $filename;

                if( substr($rootFile, strlen($rootFile)-1, strlen($rootFile)) == "/" ){
                    $rootFile = substr($rootFile, 0, strlen($rootFile)-1);
                }

                $this->rootItems[] = $rootFile;
            }

            /*
             * SEARCH BY DAY
             */
            /*
             * Today modified files
             */
            if( $this->hasCommand("today") ){
                if( date("d/m/Y") != date("d/m/Y", filemtime($filename)) ){
                    continue;
                }
            }

            /*
             * Yesterday modified files
             */
            else if( $this->hasCommand("yesterday") ){
                if( date("d/m/Y", mktime(-24, 0, 0)) != date("d/m/Y", filemtime($filename)) ){
                    continue;
                }
            }

            if( is_dir($filename)){
                $file = false;
                $localFiles = array_merge($this->returnFiles($filename, 1), $localFiles);
            } else if( is_file ){
                $file = $filename;
                $localFiles[] = $filename;
            }
            
            if( !empty($file) AND
                $this->hasOption("list") )
            {
                print $file."\n";
                //." - ".date("d/m/Y", filemtime($filename))."\n";
            }

        }

        $this->localFiles = $localFiles;
        return $localFiles;

    } // end returnFiles();


}
?>
