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

use obregonco\B2\Client;
use obregonco\B2\Bucket;
class B2{

    private $client;

    public function __construct($version = 2, $domainAliases = [], $largeFileLimit = 3000000000000000)
    {
        $this->client = new Client('a6ad8ef03fd8', [
            'keyId' => '',
            'applicationKey' => '005702a65a8350c9161dab30d79faec4bb8e58a4ae',
        ]);
        $this->client->version = $version;
        $this->client->domainAliases = $domainAliases;
        $this->client->largeFileLimit = $largeFileLimit;
    }

    public function createBucket()
    {
        $bucket = $this->client->createBucket([
            'BucketName' => 'my-special-bucket',
            'BucketType' => Bucket::TYPE_PRIVATE // or TYPE_PUBLIC
        ]);
        return $bucket;
    }

    public function UpdateBucket($bucketId)
    {
        $updatedBucket = $this->client->updateBucket([
            'BucketId' => $bucketId,
            'BucketType' => Bucket::TYPE_PUBLIC
        ]);
        return $updatedBucket;
    }
    public function listBuckets()
    {
        $buckets = $this->client->listBuckets();
        return $buckets;
    }
    public function deleteBuckets($idBucket)
    {
        $buckets = $this->client->
        deleteBucket([
            'BucketId' => $idBucket
        ]);
        return $buckets;
    }

    public function uploadFile($fileName, $url)
    {

        $body = file_get_contents($url);
        $file = $this->client->upload([
            'BucketName' => 'servertoidayhoc',
            'FileName' => $fileName,
            'Body' => $body
        ]);
        return $file;
    }

    public function downloadFile($fileId, $saveAs = null)
    {
        $fileContent = $this->client->download([
            'FileId' => $fileId,
            'SaveAs' => $saveAs
        ]);

        return $fileContent;
    }

    public function deleteFile($FileName)
    {
        $fileDelete = $this->client->deleteFile([
            'FileName' => $FileName,
            'BucketName' => 'servertoidayhoc'
        ]);

        return $fileDelete;
    }

    public function listFiles($bucketId)
    {
        $fileList = $this->client->listFiles([
            'BucketId' => $bucketId
        ]);

        return $fileList;
    }
}