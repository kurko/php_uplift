<?php
/**
 * Description of push
 *
 * @author kurko
 */

class push extends Uplift {

    public $rootItems;

    function __run(){

        $this->getFiles();

        if( empty($this->localFiles) ){
            write("No files matched.", false);
            return false;
        }

        if( count($this->localFiles) == 1 )
            $fileWord = "file";
        else
            $fileWord = "files";

        if( inputYesNo("Push ".count($this->localFiles)." ".$fileWord." to the server? [yes, no]: ") ){
            $this->sendFiles();
        } else {
            exit();
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

        $sftp = $this->connectSFTP();
        $ssh = $this->connectSSH();
        if( !$sftp->chdir(".lifts") ){
            $sftp->mkdir(".lifts");
        }

        //if(  )

        $version = date("Y_m_d_H_i_s")."/";
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

        if( $this->sendMode == "all" ){
            print "\n";
            print "Creating links: ";

            foreach( glob("*") as $filename){
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
        wr("Updates server's files.");
        br();
        wr("usage: uplift push <options>");
        br();
        wr("Available options:");

        wr("\ttoday\t\twill push only files modified today.");
        wr("\tyesterday\twill push only files modified yesterday.");
        wr("\tlast\t\twill push the last modified file.");

        wr("\t1h\t\twill push files that were modified less than 60 minutes ago (1 hour).");
        wr("\t\t\tThe similar options are also available: 2h (2 hours), 3h, 4h, and so on.");

    }

}
?>
