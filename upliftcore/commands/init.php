<?php
/**
 * Description of init
 *
 * @author kurko
 */
class init extends Uplift {

    function __run(){

        $justStartSetup = false;
        if( $this->loadConfig() ){
            write("A configuration file has been found.\n\nDo you wish to setup ".
                   "your environment again anyway? [yes, no]: ", false);
            $overwriteConfig = inputText();
            if( in_array($overwriteConfig, array("no","n") ) ){
                exit();
            }
            $justStartSetup = true;
            breakLine();
        }
        write("Welcome to Uplift. I (Uplift) will change the way you ".
              "manage remote servers. It's intended to make FTP absolete on ".
              "your daily work.");

        breakLine();
        write("I will guide you through the initialization process, asking some ".
               "questions that will allow me to setup your environment.");

        breakLine();
        $hasInitPermission = false;

        /*
         * Start?
         */
        while( !$hasInitPermission OR !$justStartSetup ){
            write("Do you wish to start the setup now? [yes, no]: ", false);
            $initPermission = inputText();
            if( in_array($initPermission, array("yes","y") ) )
                $hasInitPermission = true;
            else if( in_array($initPermission, array("no","n") ) ){
                $hasInitPermission = false;
                breakLine();
                exitln("Exiting. See you later.\n");
            }
        }

        $this->setup();

        exitln('Connected successfully! The setup is now complete.');
        breakLine();

    }

    function setup(){
        $hasConnected = false;

        while( !$hasConnected ){

            write("Type in the SSH host address (e.g. ftp.host.com or 192.168.1.100): ", false);
            $this->config['ssh']['server'] = inputText();
            write("SSH username: ", false);
            $this->config['ssh']['username'] = inputText();
            write("SSH password: ", false);
            $this->config['ssh']['password'] = inputPassword();

            $ssh = new Net_SSH2($this->config['ssh']['server']);
            breakLine();
            write("\tTrying to stablish connection...");
            breakLine();
            if (!$ssh->login($this->config['ssh']['username'], $this->config['ssh']['password'])) {
                write('Login Failed. One of the following problems has ocurred:');
                breakLine();
                    write("\t- host address doesn't exists;");
                    write("\t- username is incorrect;");
                    write("\t- password is incorrect.");

                breakLine();
                write('Try again.');
                breakLine();

            } else {
                write('Logged in successfully.');
                $hasConnected = true;
            }

        }

        write("Remote root folder (e.g: public_html, www/public_html): ", false);
        $this->config['ssh']['root_dir'] = inputText();
        $this->saveConfig();
    }

    function saveConfig(){
        $configFile = fopen("./".CONFIG_FILE, "w");

        $config = "<?php\n";
        $config.= "class CONFIG { \n";
        $config.= "\t"."static \$default = array(\n";

        foreach( $this->config as $key=>$value ){

            $config.= $this->generateConfigSyntax($key, $value);
        }
        
        $config.= "\t);\n";
        $config.= "}\n";
        $config.= "?>";
        fwrite($configFile, $config);
        fclose($configFile);
    }

    function generateConfigSyntax($key, $value = "", $recursive = 1){
        $config = "";
        if( is_array($key) ){
            foreach( $key as $subKey=>$subValue ){
                $config.= $this->generateConfigSyntax($subKey, $subValue, $recursive);
            }
        }

        for($i=0; $i<$recursive; $i++){
            $config.= "\t";
        }
        if( is_string($key) ){
            if( is_array($value) ){
                $config.= "'$key' => array(\n";
                $config.= $this->generateConfigSyntax($value, "", $recursive+1);

                $config.= "),\n";
            } else {
                $config.= "'$key' => '$value',\n";
            }

        } else if( is_numeric($key) ){
            $config.= "$key => '$value',\n";
        }

        return $config;
    }

}
?>
