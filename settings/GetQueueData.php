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

use React\EventLoop\Loop;
use React\Promise\Promise;
use Model\UserModel;
use Settings\Treatment;


class GetQueueData
{
    public static function uploadQueue($filename, $url)
    {   
        return new Promise(function ($resolve, $reject) use ($filename, $url) {
            try {

                $file_info = Treatment::replace_default_handle_upload($filename, $url);
            } catch (\Exception $e) {
                    print "Error upload: " . $e->getMessage() . "\n";
            }
            
            // Kiểm tra kết quả và giải quyết Promise
            if ($file_info !== null) {
                $resolve($file_info); // Giải quyết Promise với kết quả
            } else {
                $reject(new \Exception("Lỗi trong quá trình xử lý tải lên")); // Giải quyết Promise với lỗi
            }
        });
    }
}
