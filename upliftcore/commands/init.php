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
            wr("A configuration file has been found.\n\nDo you wish to setup ".
                   "your environment again anyway? [yes, no]: ", false);
            $overwriteConfig = inputText();
            if( in_array($overwriteConfig, array("no","n") ) ){
                exit();
            }
            $justStartSetup = true;
            breakLine();
        }
        wr("Welcome to Uplift. I (Uplift) will change the way you ".
              "manage remote servers. It's intended to make FTP absolete on ".
              "your daily work.");

        breakLine();
        wr("I will guide you through the initialization process, asking some ".
               "questions that will allow me to setup your environment.");

        breakLine();
        $hasInitPermission = false;

        /*
         * Start?
         */
        while( !$hasInitPermission AND !$justStartSetup ){
            wr("Do you wish to start the setup now? [yes, no]: ", false);
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

            wr("Type in the SSH host address (e.g. ftp.host.com or 192.168.1.100): ", false);
            $this->config['ssh']['server'] = inputText();
            wr("SSH username: ", false);
            $this->config['ssh']['username'] = inputText();
            wr("SSH password: ", false);
            $this->config['ssh']['password'] = inputPassword();

            $ssh = new Net_SSH2($this->config['ssh']['server']);
            breakLine();
            wr("\tTrying to stablish connection...");
            breakLine();
            if (!$ssh->login($this->config['ssh']['username'], $this->config['ssh']['password'])) {
                wr('Login Failed. One of the following problems has ocurred:');
                breakLine();
                    wr("\t- host address doesn't exists;");
                    wr("\t- username is incorrect;");
                    wr("\t- password is incorrect.");

                breakLine();
                wr('Try again.');
                breakLine();

            } else {
                wr('Logged in successfully.');
                $hasConnected = true;
            }

        }

        wr("Remote path to root folder (e.g: /home/user, /home/user/public_html): ", false);
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
        wr("Initializes a project under the current directory.", false);
        br();
        br();
        wr("If the project has already been initialized, this will ", false);
        wr("reconfigure it.", false);
        br();
        br();
        wr("The console will ask you for ssh's connection info (host address, ", false);
        wr("username, password) and other related stuff.", false);
    }

}
?>
