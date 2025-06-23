<?php
// Lokasi: app/Libraries/TobaUploader.php

namespace App\Libraries;

use CodeIgniter\Files\File;

class TobaUploader
{
    protected $serverUrl;
    protected $apiKey;
    protected $modulName;
    protected $downloadUrl;

    public function __construct()
    {
        $this->serverUrl = getenv('toba.serverUrl');
        $this->apiKey = getenv('toba.apiKey');
        $this->modulName = getenv('toba.modulName');
        $this->downloadUrl = 'https://toba.beacukai.go.id/file-hosting/download/';

        if (empty($this->serverUrl) || empty($this->apiKey) || empty($this->modulName)) {
            throw new \Exception('Konfigurasi Toba (serverUrl, apiKey, modulName) tidak lengkap di file .env');
        }
    }

    public function upload(File $fileObject)
    {
        $filePath = $fileObject->getRealPath();
        $fileType = $fileObject->getMimeType();
        $fileName = $fileObject->getName();

        $data = [
            "txtModulName" => $this->modulName,
            "file" => new \CURLFile($filePath, $fileType, $fileName),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->serverUrl,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Api-Key: ' . $this->apiKey],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            log_message('error', 'cURL Error for Toba Upload: ' . $error);
            return false;
        }
        
        return json_decode($response, true);
    }

    public function download(string $keyFile)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->downloadUrl . $keyFile,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => ['Api-Key: ' . $this->apiKey],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            log_message('error', 'cURL Error for Toba Download: ' . $error);
            return false;
        }
        
        return json_decode($response);
    }
}