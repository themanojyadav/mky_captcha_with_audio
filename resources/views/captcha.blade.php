<div class="mky-captcha-container" id="mky-captcha-{{ $id ?? 'default' }}">
    <div class="mky-captcha-image-wrapper">
        <img src="{{ $image }}" alt="CAPTCHA" class="mky-captcha-image" id="mky-captcha-img-{{ $id ?? 'default' }}">
        <button type="button" class="mky-captcha-refresh" onclick="refreshMkyCaptcha('{{ $id ?? 'default' }}')" title="Refresh CAPTCHA">
            ↻
        </button>
    </div>

    @if($audioEnabled ?? config('mky-captcha.audio_enabled', true))
    <div class="mky-captcha-audio-wrapper">
        <button type="button" class="mky-captcha-audio-btn" onclick="playMkyCaptchaAudio('{{ $id ?? 'default' }}')" title="Play Audio CAPTCHA">
            🔊 Play Audio
        </button>
    </div>
    @endif

    <input type="hidden" id="mky-captcha-audio-{{ $id ?? 'default' }}" value="{{ json_encode($audio ?? []) }}">
</div>

<style>
    .mky-captcha-container {
        display: inline-block;
        margin: 10px 0;
    }

    .mky-captcha-image-wrapper {
        position: relative;
        display: inline-block;
    }

    .mky-captcha-image {
        display: block;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .mky-captcha-refresh {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 3px;
        cursor: pointer;
        padding: 5px 10px;
        font-size: 18px;
        font-weight: bold;
        transition: background 0.2s;
    }

    .mky-captcha-refresh:hover {
        background: rgba(255, 255, 255, 1);
    }

    .mky-captcha-audio-wrapper {
        margin-top: 8px;
    }

    .mky-captcha-audio-btn {
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .mky-captcha-audio-btn:hover {
        background: #0056b3;
    }

    .mky-captcha-audio-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }
</style>

<script>
    let mkyCaptchaAudioPlaying = {};

    async function refreshMkyCaptcha(id = 'default') {
        try {
            const response = await fetch('{{ route("mky-captcha.refresh") }}');
            const data = await response.json();

            if (data.success) {
                document.getElementById(`mky-captcha-img-${id}`).src = data.image;
                document.getElementById(`mky-captcha-audio-${id}`).value = JSON.stringify(data.audio);
            }
        } catch (error) {
            console.error('Failed to refresh CAPTCHA:', error);
        }
    }

    async function playMkyCaptchaAudio(id = 'default') {
        if (mkyCaptchaAudioPlaying[id]) {
            return;
        }

        try {
            const audioData = JSON.parse(document.getElementById(`mky-captcha-audio-${id}`).value);

            if (!audioData || audioData.length === 0) {
                alert('Audio not available');
                return;
            }

            mkyCaptchaAudioPlaying[id] = true;
            const button = event.target;
            button.disabled = true;

            for (let i = 0; i < audioData.length; i++) {
                const audio = new Audio(audioData[i]);
                await new Promise((resolve, reject) => {
                    audio.onended = resolve;
                    audio.onerror = reject;
                    audio.play();
                });

                // Small pause between characters
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            button.disabled = false;
            mkyCaptchaAudioPlaying[id] = false;
        } catch (error) {
            console.error('Failed to play audio:', error);
            mkyCaptchaAudioPlaying[id] = false;
            event.target.disabled = false;
        }
    }
</script>