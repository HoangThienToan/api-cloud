<?php

/**
 *
 *      ___                       ___           ___           ___     
 *     /\  \          ___        /\  \         /\  \         /\__\    
 *     \:\  \        /\  \       \:\  \       /::\  \       /::|  |   
 *      \:\  \       \:\  \       \:\  \     /:/\:\  \     /:|:|  |   
 *      /::\  \      /::\__\      /::\  \   /::\~\:\  \   /:/|:|  |__ 
 *     /:/\:\__\  __/:/\/__/     /:/\:\__\ /:/\:\ \:\__\ /:/ |:| /\__\
 *    /:/  \/__/ /\/:/  /       /:/  \/__/ \/__\:\/:/  / \/__|:|/:/  /
 *   /:/  /      \::/__/       /:/  /           \::/  /      |:/:/  / 
 *   \/__/        \:\__\       \/__/            /:/  /       |::/  /  
 *                 \/__/                       /:/  /        /:/  /   
 *                                             \/__/         \/__/    
 *
 */

namespace Settings;

use Settings\SendMail;
use Settings\B2;
use Model\UserModel;
use Model\FilesModel;

class Treatment
{
    public static function permission($domain, $user)
    {
        $return = '';
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $currentDateTime = date('Y-m-d H:i:s');
        if ($user) {
            if ($user['type'] == 'user') {
                //if (strtotime($currentDateTime) < strtotime($user['outOfDate'])) {

                $return = (strpos($user['domain'], $domain) !== false) ? "ok" : "Domain is incorrect!";
                //} else {
                //	$return = "Please extend it for further use!";
                //	die;
                //}
            } else if ($user['type'] == 'admin') {
                $return = "ok";
            } else {
                $return = "This account is Invalid!";
            }
        } else {
            $return = "Key does not exist!";
        }
        return $return;
    }


    public static function emailTemplate($email, $name, $random = null, $domain, $emergency_code)
    {
        $currentUrl = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $currentUrl .= 's';
        }
        $currentUrl .= '://' . $_SERVER['HTTP_HOST'];
        
        function get_html_file($name, $random = null, $currentUrl, $emergency_code)
        {
            $GLOBALS['name'] = $name;
            $GLOBALS['random'] = $random;
            $GLOBALS['currentUrl'] = $currentUrl;
            $GLOBALS['emergency_code'] = $emergency_code;
            ob_start();
            include 'MailFormat.php';
            $content = ob_get_clean();
            return $content;
        }
        $MailBody = get_html_file($name, $random, $currentUrl, $emergency_code);
        // var_dump($MailBody);
        // die;
        $MailSubject = "Email Verification";

        $senmail = new SendMail();
        $senmail->SendMail($email, $MailSubject, $MailBody);
        return "Email sent successfully, check your inbox!";
    }

    // Function to handle media upload and replacement
    public static function replace_default_handle_upload($file_name, $url)
    {
        $return = true;
        $crod = self::connection_cloud();
            try {
                $result = $crod->uploadFile($file_name, $url);
                return $result;
            } catch (\Exception $e) {
                echo json_encode("Error upload: " . $e->getMessage() . "\n");die;
                    $return = false;
            }

        return $return;
    }

    public static function used_storage_capacity($domain)
    {
        $md_files = new FilesModel();   
        $files = $md_files->get("`domain` = '$domain' AND `datedTimeDel` IS NULL");
        $totalSize = 0;
        foreach ($files as $file) {
            $info = json_decode($file['info']);
            $totalSize += $info->size;
        }
        $md_files->close();
        return $totalSize;
    }

    public static function storage_limit($user_id)
    {
        $md_user = new UserModel();
        $users = $md_user->get("`id` = '$user_id'");
        $storage_limit = $users[0]['data_limit'];
        return $storage_limit;
    }

    public static function curl_filesize($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return $fileSize;
    }
    
    public static function checkFileExistence($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return $responseCode === 200;
    }
    // 
    public static function deleteData($file_name)
    {

        $crod = self::connection_cloud();
        $n = 0;
        $result = true;
        while ($n < 10) {
            try {
                $result = $crod->deleteFile($file_name);

                break;
            } catch (\Exception $e) {
                $n++;
                if ($n === 10) {
                    print "Error upload: " . $e->getMessage() . "\n";
                    $result = false;
                }
            }
        }
        return $result;
    }

    private static function connection_cloud()
    {
        $n = 0;
        $crod =  new B2();
        while ($n < 10) {
            try {
                $buckets = $crod->listBuckets();
                if ($buckets) {
                    return $crod;
                    foreach ($buckets as $bucket) {
                    }
                } else {
                }
                break;
            } catch (\Exception $e) {
                $n++;
                if ($n === 10) {
                    print "Error upload: " . $e->getMessage() . "\n";
                    return false;
                }
            }
        }
        return $crod;
    }
}
