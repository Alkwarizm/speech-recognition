<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-white p-6 w-96">
                    <h2 class="text-xl font-semibold mb-4">Record Audio</h2>

                    <!-- Audio Recording Controls -->
                    <div class="mb-4">
                        <button id="startRecord" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Start Recording
                        </button>
                        <button id="stopRecord" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 ml-2 hidden">
                            Stop Recording
                        </button>
                    </div>

                    <!-- Audio Playback -->
                    <div class="mt-4 hidden" id="audio-section">
                        <audio id="audioPlayback" controls class="w-full"></audio>
                    </div>

                    <!-- Status Message -->
                    <p id="status" class="text-sm text-gray-600 mt-2"></p>

                    <!-- Transcription -->
                    <div class="mt-4 hidden" id="transcribe-section">
                        <h2 class="text-xl font-semibold mb-4">Transcription</h2>
                        <p id="transcription" class="text-sm text-gray-600">Loading</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        };
        const storeAudio =  (audioBlob) => {
            const formData = new FormData();

            formData.append('file', audioBlob, 'audio.mp4');

            try {
                const response = fetch('transcribe', {
                    method: 'POST',
                    body: formData,
                    headers,
                });

                if (response.ok) {
                    let data = response.json();
                    console.log(data, "Audio uploaded successfully");
                    transcriptionId = data.transcriptionId;
                    console.log(transcriptionId)
                } else {
                    console.error("Failed to upload audio");
                }
            } catch (error) {
                console.error("Error uploading audio:", error);
            }
        };

        const getTranscription = (transcriptionId) => {
            fetch(`transcribe/${transcriptionId}`, {
                method: 'GET',
                headers,
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    document.getElementById('transcription').textContent = data.transcription;
                })
                .catch(error => {
                    console.error("Error fetching transcription:", error);
                });
        };

        let mediaRecorder;
        let audioChunks = [];
        let transcriptionId = "";

        const startRecordButton = document.getElementById('startRecord');
        const stopRecordButton = document.getElementById('stopRecord');
        const audioPlayback = document.getElementById('audioPlayback');
        const statusText = document.getElementById('status');
        const audioSection = document.getElementById('audio-section');
        const transcribeSection = document.getElementById('transcribe-section');

        // Check for browser support
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusText.textContent = "Your browser does not support audio recording.";
            startRecordButton.disabled = true;
        }

        // Start Recording
        startRecordButton.addEventListener('click', async () => {
            try {
                startRecordButton.classList.add('hidden');
                stopRecordButton.classList.remove('hidden');
                statusText.textContent = "Recording...";
                startRecordButton.disabled = true;
                stopRecordButton.disabled = false;

                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

                // Use 'audio/webm' as the MIME type (widely supported)
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/mp4' });

                mediaRecorder.ondataavailable = (event) => {
                    console.log(event); // Debugging: Log the BlobEvent
                    audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/mp4' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    audioPlayback.src = audioUrl;
                    audioChunks = []; // Reset chunks for the next recording
                    statusText.textContent = "Recording stopped. Click play to listen.";
                    storeAudio(audioBlob);
                };

                mediaRecorder.start();
            } catch (error) {
                console.error("Error accessing microphone:", error);
                statusText.textContent = "Error accessing microphone. Please ensure permissions are granted.";
                startRecordButton.disabled = false;
                stopRecordButton.disabled = true;
            }
        });

        // Stop Recording
        stopRecordButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state === "recording") {
                mediaRecorder.stop();
                startRecordButton.disabled = false;
                stopRecordButton.disabled = true;
                startRecordButton.classList.remove('hidden');
                stopRecordButton.classList.add('hidden');
                audioSection.classList.remove('hidden');
                transcribeSection.classList.remove('hidden');
            }
        });

        // Fetch transcription when the user clicks on the play button
        audioPlayback.addEventListener('play', () => getTranscription(transcriptionId));
    </script>

</x-app-layout>
