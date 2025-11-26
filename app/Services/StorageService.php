<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Upload audio file to storage
     */
    public function uploadAudio($file, $userId)
    {
        $path = $file->store('lectures/' . $userId, 'public');
        return Storage::url($path);
    }

    /**
     * Delete audio file from storage
     */
    public function deleteAudio($url)
    {
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        return Storage::disk('public')->delete($path);
    }

    /**
     * Get file size
     */
    public function getFileSize($url)
    {
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        return Storage::disk('public')->size($path);
    }

    /**
     * Check if file exists
     */
    public function fileExists($url)
    {
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        return Storage::disk('public')->exists($path);
    }
}
