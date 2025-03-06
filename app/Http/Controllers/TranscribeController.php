<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAudioRequest;
use App\Services\AssemblyAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscribeController extends Controller
{
    public function __construct(protected AssemblyAI $assemblyAI)
    {
    }

    public function __invoke(StoreAudioRequest $request)
    {
        $filename = str()->random(15) . '.mp4';

        Storage::disk('public')->put($filename, $request->file('file')->get());

        Log::debug('path', ['path' => Storage::disk('public')->url($filename)]);

        $data = rescue(
            callback: function () use ($filename) {
                return $this->assemblyAI->transcribe(
                    Storage::disk('public')->url($filename)
                );
            },
            rescue: function (\Exception $e) {
                Log::error('Transcription failed', ['error' => $e->getMessage()]);
            }
        );

        return response()->json([
            'transcriptionId' => $data['id'],
        ]);
    }

    public function show(string $transcriptionId)
    {
        return response()->json([
            'transcription' => $this->assemblyAI->poll(
                $transcriptionId
            )['text'],
        ]);
    }
}
