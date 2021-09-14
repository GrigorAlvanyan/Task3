<?php
namespace PhpTelnet;

class Client
{

    var $uSleepTime = 250000;
    var $sleepTime = 250000;

    var $socketOpenTimeout = 5;
 
    var $loginSleepTime = 250000;

    var $connection = null;

    var $server = null;

    var $port = null;

    var $username = null;

    var $password = null;

    var $loginPrompt;

    var $connMessage1;

    var $connMessage2;

    const ERROR_0 = "success";

    CONST ERROR_1 = "couldn't open network connection";

    const ERROR_2 = "unknown host";

    const ERROR_3 = "login failed";

    private $users = [
        'admin@hgw',
        'root@hgw'
    ];


    public function __construct($server, $port, $username = null, $password = null)
    {
        // we need php 5 obviously
        if (version_compare(phpversion(), '5.0', '<')) {
            throw new \Exception('PhpTelnet\'s Client needs PHP 5+ to work.');
        } else {
            $this->server = $server;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;

        }
    }

    function connect()
    {
        if ($this->connection === NULL) {

            $errorNumber = 0;
            if ($this->connection = fsockopen($this->server, $this->port, $errno, $errstr, $this->socketOpenTimeout)) {


                if ($this->username !== null || $this->password !== null) {

                    $r = $this->getResponse();
                    $r = explode("\n", $r);

                    $this->loginPrompt = $r[count($r) - 1];


                    fputs($this->connection, $this->username . "\r");
                    $this->sleep();


                    fputs($this->connection, $this->password . "\r");
                    $this->sleep($this->loginSleepTime);

                    $r = $this->getResponse();
                    $r = explode("\n", $r);
                    if (($r[count($r) - 1] == '') || ($this->loginPrompt == $r[count($r) - 1])) {
                        $errorNumber = 3;
                        $this->disconnect();
                    }
                }
            } else {
                $errorNumber = 1;
            }

            if ($errorNumber != 0) {
                $this->throwConnectError($errorNumber);
            }
        } else {
            return true;
        }
    }

    function disconnect($exit = 'exit')
    {
        if ($this->connection) {
            if ($exit)
                $this->execute($exit);
            fclose($this->connection);
            $this->connection = NULL;
        }
    }


    public function execute($cmd, $asArray = true)
    {
        $this->connect();

        fwrite($this->connection, $cmd . "\r\n");

        $r = '';

        set_time_limit (5);

        while(!$this->strpos_arr($r, $this->users)){
            $r = $this->getResponses();
        }


        if ($asArray) {
            $result = explode(PHP_EOL, $r);
        } else {
            $result = $r;
        }
        return $result;
    }


    public function exec($cmd, $asArray = true)
    {
        $this->connect();

        fwrite($this->connection, $cmd . "\r\n");

        $this->sleep();
        $r = $this->getResponse();


        if ($asArray) {
            $result = explode(PHP_EOL, $r);
        } else {
            $result = $r;
        }
        return $result;
    }

    private function strpos_arr($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $what) {
            if(($pos = strpos($haystack, $what))!==false) return $pos;
        }
        return false;
    }

    private function removeNonPrintableCharacters($str)
    {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
    }

    function getResponse()
    {
        $r = '';
        do {
            $r .= fread($this->connection, 10000); //length 1000
            $s = stream_get_meta_data($this->connection);

        } while ($s['unread_bytes']) ;
        return $this->removeNonPrintableCharacters($r);
    }

    function getResponses()
    {
        $r = '';
        set_time_limit (5);

        do {
            $r .= fread($this->connection, 10000); //length 1000
//            $s = stream_get_meta_data($this->connection);

        } while (!$this->strpos_arr($r, $this->users)) ;

        return $this->removeNonPrintableCharacters($r);
    }


    function sleep($sleepTime = null)
    {
        if ($sleepTime === null) {
            usleep($this->uSleepTime);
        } else {
            usleep($this->sleepTime);
        }
    }

    function throwConnectError($num)
    {
        throw new \Exception(constant('ERROR_' . $num));
    }
}