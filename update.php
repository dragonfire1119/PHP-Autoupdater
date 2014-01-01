<?php

use VIPSoft\Unzip\Unzip;

/**
 * A update class to help update php software
 *
 * You will need to download https://github.com/vipsoft/Unzip to get this to work.
 */
class Update {

    /**
     * This Gets the remote url
     * 
     * @param  [type] $url     This remote url
     * @param  array  $methods 
     * @return mixed          
     */
    public function get($url, $methods = array('fopen', 'curl', 'socket')) {
        if(gettype($methods) == 'string')
            $methods = array($methods);
        elseif(!is_array($methods))
            return false;
        foreach($methods as $method)
        {
            switch($method)
            {
            case 'fopen':
                //uses file_get_contents in place of fopen
                //allow_url_fopen must still be enabled
                if(ini_get('allow_url_fopen'))
                {
                    $contents = file_get_contents($url);
                    if($contents !== false)
                        return $contents;
                }
            break;
            case 'curl':
                if(function_exists('curl_init'))
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    return curl_exec($ch);
                }
            break;
            case 'socket':
                //make sure the url contains a protocol, otherwise $parts['host'] won't be set
                if(strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
                    $url = 'http://' . $url;
                $parts = parse_url($url);
                if($parts['scheme'] == 'https')
                {
                    $target = 'ssl://' . $parts['host'];
                    $port = isset($parts['port']) ? $parts['port'] : 443;
                }
                else
                {
                    $target = $parts['host'];
                    $port = isset($parts['port']) ? $parts['port'] : 80;
                }
                $page    = isset($parts['path'])        ? $parts['path']            : '';
                $page   .= isset($parts['query'])       ? '?' . $parts['query']     : '';
                $page   .= isset($parts['fragment'])    ? '#' . $parts['fragment']  : '';
                $page    = ($page == '')                ? '/'                       : $page;
                if($fp = fsockopen($target, $port, $errno, $errstr, 15))
                {
                    $headers  = "GET $page HTTP/1.1\r\n";
                    $headers .= "Host: {$parts['host']}\r\n";
                    $headers .= "Connection: Close\r\n\r\n";
                    if(fwrite($fp, $headers))
                    {
                        $resp = '';
                        //while not eof and an error does not occur when calling fgets
                        while(!feof($fp) && ($curr = fgets($fp, 128)) !== false)
                            $resp .= $curr;
                        if(isset($curr) && $curr !== false)
                            return substr(strstr($resp, "\r\n\r\n"), 3);
                    }
                    fclose($fp);
                }
            break;
            }
        }
        return false;
    }

    /**
     * Checks for new update
     * @return mixed 
     */
    public function check() {

        // Get the update url data
        $getRemoteUrl = $this->get('http://remote.dev/check.php');

        $get = json_decode($getRemoteUrl);

        $old_version = '0.1';

        $version = $get->version;
        $pathToZip = $get->path;

        // Compare the old version with the new version
        if($old_version == $version) {
            // There is no updates
        } else {

            // Download the remote url zip that you have on another server or url
            $download = $this->get($pathToZip);

            // This is the destination path that you want to store the new downloaded zip file.
            $destinationPath    = 'updates/' . $version . '.zip';

            // Move the zip file to destination and store the zip data in the new created zip
            file_put_contents($destinationPath, $download);

            // Just data so you can serialize the data and store it in a database table.
            $update = [
                'version' => $version,
                'path' => $destinationPath,
            ];

            // I would go ahead and store the $update in a database table so you don't always have to fetch the remote url.
            // And you could do a if and show it on the updates page that there's a new update available
        }
    }

    /**
     * Installs the update
     * @return mixed
     */
    public function install($destinationPath) {

        // If you store the remote data in a database table you would fetch it instead of the remote url
        $remoteInfo = $this->get('http://remote.dev/check.php');
        $undoRemoteInfo = json_decode($remoteInfo);

        // Check if the update is in the database table
        if($undoRemoteInfo == false) {
            // There is no updates so do something
        }

        // You can use another unzip library to I just like this one :)
        // Where going to use the https://github.com/vipsoft/Unzip by vipsoft
        $unzipper  = new Unzip();

        // The ./ is for if you want to install the update in the root directory. Change this as you see fit.
        $filenames = $unzipper->extract($destinationPath, './');

        // Go ahead and store the new version in the database

        // Delete the info of the remote url that you stored in the database
        
        // There we go the php software should now be updated
    }

}
