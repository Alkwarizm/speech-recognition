<?php

use App\Services\AssemblyAI;

test('can be instantiated', function () {
    expect(resolve(AssemblyAI::class))
        ->toBeInstanceOf(AssemblyAI::class);
});

test('transcribes an m4a audio file', function () {
    $audioFile = 'https://uagniymqzr.sharedwithexpose.com/audio/quotes.m4a';

    $transcription = resolve(AssemblyAI::class)->transcribe($audioFile);

    dump($transcription);

    expect($transcription)
        ->toBeArray()
        ->toHaveKeys(['id', 'status', 'text']);
});

test('polls transcription', function () {
    $transcriptionId = 'da055b46-c2d5-49bf-b056-7fbb1bdfc792';

    $transcription = resolve(AssemblyAI::class)->poll($transcriptionId);

    dump($transcription);

    expect($transcription)
        ->toBeArray()
        ->toHaveKeys(['id', 'status', 'text']);
});
