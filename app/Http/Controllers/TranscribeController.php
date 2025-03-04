<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAudioRequest;
use App\Services\AssemblyAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class TranscribeController extends Controller
{
    public function __construct(protected AssemblyAI $assemblyAI)
    {
    }

    public function __invoke(StoreAudioRequest $request)
    {
        Storage::disk('public')->put('audio.mp4', $request->file('file')->get());

        $data = $this->assemblyAI->transcribe(Storage::disk('public')->url('audio.mp4'));

        Cache::set('assemblyAI', ['id' => $data['id'], 'status' => 'queued']);

        return response()->json([
            'transcription_id' => $data['id'],
        ]);
    }

    public function show()
    {
        return response()->json([
            'transcription' => $this->assemblyAI->poll(
                Cache::get('assemblyAI')['id']
            )['text'],
        ]);
    }
}
