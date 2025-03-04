<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssemblyAI
{
    protected Factory|PendingRequest $client;

    public function __construct(
        protected string $baseUri,
        protected string $key
    )
    {
        $this->client = Http::withHeaders([
            'authorization' => $this->key,
            'content-type' => 'application/json',
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function transcribe(string $path): array
    {
        Log::debug('Transcribing audio file', ['path' => $path]);

        $url = "{$this->baseUri}/v2/transcript";

        $response =  $this->client
            ->post($url, [
                'audio_url' => $path,
            ]);

        return $response->json();
    }

    public function poll(string $transcriptionId): array
    {
        $url = "{$this->baseUri}/v2/transcript/{$transcriptionId}";

        $response = $this->client->get($url);

        Log::debug('Transcribing audio file', ['response' => $response->json()]);

        return $response->json();
    }
}
