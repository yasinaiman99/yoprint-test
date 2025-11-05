<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $upload;
    public $path;

    public function __construct(Upload $upload, $path)
    {
        $this->upload = $upload;
        $this->path = $path;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '512M'); // Prevent memory exhaustion

        $this->upload->update(['status' => 'processing']);

        $filePath = Storage::path($this->path);

        if (!file_exists($filePath)) {
            $this->upload->update(['status' => 'failed']);
            throw new \Exception("File not found at {$filePath}");
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->upload->update(['status' => 'failed']);
            throw new \Exception("Failed to open file for reading.");
        }

        $header = fgetcsv($handle); // Get header row

        while (($row = fgetcsv($handle)) !== false) {
            // Skip invalid rows
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            // Clean UTF-8 and trim values
            $data = array_map(fn($v) => mb_convert_encoding(trim($v), 'UTF-8', 'UTF-8'), $data);

            // UPSERT based on UNIQUE_KEY
            if (!empty($data['UNIQUE_KEY'])) {
                Product::updateOrCreate(
                    ['UNIQUE_KEY' => $data['UNIQUE_KEY']],
                    [
                        'PRODUCT_TITLE' => $data['PRODUCT_TITLE'] ?? null,
                        'PRODUCT_DESCRIPTION' => $data['PRODUCT_DESCRIPTION'] ?? null,
                        'STYLE' => $data['STYLE#'] ?? null,
                        'SANMAR_MAINFRAME_COLOR' => $data['SANMAR_MAINFRAME_COLOR'] ?? null,
                        'SIZE' => $data['SIZE'] ?? null,
                        'COLOR_NAME' => $data['COLOR_NAME'] ?? null,
                        'PIECE_PRICE' => $data['PIECE_PRICE'] ?? null,
                    ]
                );
            }
        }

        fclose($handle);

        $this->upload->update(['status' => 'completed']);
    }
}
