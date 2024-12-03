<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Upload;
use App\Models\File;

class UploadController extends Controller
{
    public function upload(Request $request) {
        $request->validate([
            'files.*' => 'required|file|max:102400',
            'storage_period' => 'required|in:1h,12h,24h,72h,168h,336h,720h',
        ]);

        $expirationDate = match ($request -> storage_period) {
            '1h' => now()->addHours(1),
            '12h' => now()->addHours(12),
            '24h' => now()->addHours(24),
            '72h' => now()->addHours(72),
            '168h' => now()->addHours(168),
            '336h' => now()->addHours(336),
            '720h' => now()->addHours(720),
        };

        DB::beginTransaction();

        try {
            $upload = Upload::create([
                'user_id' => $request->user()->id,
                'expiration_date' => $expirationDate,
            ]);

            $folder = 'uploads/' . $upload->id;

            $filesData = [];

            foreach ($request->file('files') as $file) {
                $filePath = $file->store($folder);

                if (!$filePath) {
                    return response()->json(['message' => 'Ошибка сохранения файла!', 'file' => $filePath], 500);
                }

                $filesData[] = [
                    'upload_id' => $upload->id,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'file_name' => $file->getClientOriginalName(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            File::insert($filesData);

            DB::commit();

            return response()->json([
                'message' => 'Файлы успешно загружены!',
                'upload_id' => $upload->id,
                'files' => $filesData,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Хоба оба и сломалось!', 'error' => $e->getMessage()], 500);
        }
    }
}
