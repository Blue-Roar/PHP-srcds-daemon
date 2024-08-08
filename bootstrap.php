<?php
function output($text, $sub = null) {
    echo "[" . date('Y-m-d H:i:s', time()) . "] ";
    if ($sub) echo $sub . " | ";
    echo $text;
    echo PHP_EOL;
}

error_reporting(0);
set_time_limit(0);
ob_end_clean();
ob_implicit_flush();

require_once __DIR__.'/config.php';
require_once __DIR__.'/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;

$retry_counters = [];
if (file_exists(RETRY_FILE)) $retry_counters = json_decode(file_get_contents(RETRY_FILE),true);
foreach (array_keys(SERVERS) as $server) {
    $restart = false;
    $schauth = "";
    if (isset(SERVERS[$server]['schauth'])) {
        $schauth .= ' /s '.SERVERS[$server]['schauth']['server'];
        $schauth .= ' /u '.SERVERS[$server]['schauth']['user'];
        $schauth .= ' /p '.SERVERS[$server]['schauth']['password'];
    }
    if (!isset($retry_counters[$server])) $retry_counters[$server] = 0;
    output(SERVERS[$server]['name']." ",$server);

    $shell_result = shell_exec('schtasks /query /tn "'.SERVERS[$server]['schtask'].'" /fo CSV'.$schauth);
    $shell_result = str_replace("\n",'',$shell_result);
    $shell_result = str_replace('"','',$shell_result);
    $shell_result = array_filter(explode(",",$shell_result));
    if (end($shell_result) == "Running") {
        output("Schedule task is running",$server);
        
        $Query = new SourceQuery();
        $Info = [];
        
        try {
            $Query->Connect(SERVERS[$server]['addr'], SERVERS[$server]['port'], 3, SourceQuery::SOURCE);		
            $Info = $Query->GetInfo();
            if (isset($Info[ONLINE_INDICATOR_FIELD])) {
                output("Server is online",$server);
                $retry_counters[$server] = 0;
            } else {
                output("Server may be offline - empty query result",$server);
                $retry_counters[$server]++;
            }
        } catch (Exception $e) {
            output("Server may be offline - query error or timed out",$server);
            $retry_counters[$server]++;
        }
        $Query->Disconnect( );
    } else {
        output("Schedule task is not running!",$server);
        $restart = true;
    }
    
    if ($retry_counters[$server] >= RETRY_COUNT) {
        output("Restart threshold reached!",$server);
        $restart = true;
    } elseif ($retry_counters[$server]>0) {
        output("Retry attempt ".$retry_counters[$server]." of ".RETRY_COUNT,$server);
    }
    if ($restart) {
        output("Restarting ".SERVERS[$server]['name']."...",$server);
        output(str_replace("\n",'',shell_exec('schtasks /end /tn "'.SERVERS[$server]['schtask'].'"'.$schauth)),$server);
        output(str_replace("\n",'',shell_exec('schtasks /run /tn "'.SERVERS[$server]['schtask'].'"'.$schauth)),$server);
    }
}

try {
    $fp = fopen(RETRY_FILE,'w');
    fwrite($fp,json_encode($retry_counters));
    fclose($fp);
    output("Retry attempts saved successfully");
} catch (Exception $e) {
    output("Failed to save retry attempts");
}
?>