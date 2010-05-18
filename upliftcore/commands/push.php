<?php
/**
 * Description of push
 *
 * @author kurko
 */
class push extends Uplift {

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

    function sendFiles(){

        $sftp = $this->connectSFTP();
        if( !$sftp->chdir(".lifts") ){
            $sftp->mkdir(".lifts");
        }
        
        $version = 'test';//date("Y_m_d_H_i_s");
        $sftp->chdir(".lifts");
        $sftp->mkdir($version);
        $sftp->chdir($version);

        print "Copying: ";
        foreach( $this->localFiles as $filename ){
            //print $filename."\n";
            $content = file_get_contents($filename);

            $sftp->put($filename, $content);
            print ".";

        }
        print "\n";

    }

    /**
     * returnFiles()
     * 
     * Lists all local files.
     * 
     * @param <string> $dir
     * @return <array> 
     */
    function returnFiles($dir = ""){

        $localFiles = array();

        foreach( glob($dir."*", GLOB_MARK) as $filename){

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
                $localFiles = array_merge($this->returnFiles($filename), $localFiles);
            } else if( is_file ){
                $file = $filename;
                $localFiles[] = $filename;
            }
            
            if( !empty($file) AND
                $this->hasOption("list") )
            {
                print $file." - ".date("d/m/Y", filemtime($filename))."\n";
            }

        }

        $this->localFiles = $localFiles;
        return $localFiles;

    } // end returnFiles();


}
?>
