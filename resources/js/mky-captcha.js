/**
 * MkyCaptchaWithAudio JavaScript
 * Handles CAPTCHA refresh and audio playback
 */

(function () {
  "use strict";

  let mkyCaptchaAudioPlaying = {};

  /**
   * Refresh CAPTCHA image and audio
   */
  async function refreshMkyCaptcha(id) {
    try {
      const refreshUrl = document.querySelector(
        "[data-mky-captcha-refresh-url]"
      )?.dataset.mkyCaptchaRefreshUrl;
      if (!refreshUrl) {
        console.error("Refresh URL not found");
        return;
      }

      const response = await fetch(refreshUrl);
      const data = await response.json();

      if (data.success) {
        const imgElement = document.getElementById("mky-captcha-img-" + id);
        const audioElement = document.getElementById("mky-captcha-audio-" + id);

        if (imgElement) {
          imgElement.src = data.image;
        }

        if (audioElement) {
          audioElement.value = JSON.stringify(data.audio);
        }
      }
    } catch (error) {
      console.error("Failed to refresh CAPTCHA:", error);
    }
  }

  /**
   * Play CAPTCHA audio
   */
  async function playMkyCaptchaAudio(id, button) {
    if (mkyCaptchaAudioPlaying[id]) {
      return;
    }

    try {
      const audioElement = document.getElementById("mky-captcha-audio-" + id);
      if (!audioElement) {
        console.error("Audio data not found");
        return;
      }

      const audioData = JSON.parse(audioElement.value);

      if (!audioData || audioData.length === 0) {
        alert("Audio not available");
        return;
      }

      mkyCaptchaAudioPlaying[id] = true;
      button.disabled = true;

      for (let i = 0; i < audioData.length; i++) {
        const audio = new Audio(audioData[i]);
        await new Promise((resolve, reject) => {
          audio.onended = resolve;
          audio.onerror = reject;
          audio.play();
        });

        // Small pause between characters
        await new Promise((resolve) => setTimeout(resolve, 300));
      }

      button.disabled = false;
      mkyCaptchaAudioPlaying[id] = false;
    } catch (error) {
      console.error("Failed to play audio:", error);
      mkyCaptchaAudioPlaying[id] = false;
      button.disabled = false;
    }
  }

  /**
   * Initialize event listeners when DOM is ready
   */
  function initMkyCaptcha() {
    // Handle refresh button clicks
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("mky-captcha-refresh")) {
        e.preventDefault();
        const captchaId = e.target.dataset.captchaId;
        if (captchaId) {
          refreshMkyCaptcha(captchaId);
        }
      }
    });

    // Handle audio button clicks
    document.addEventListener("click", function (e) {
      if (e.target.classList.contains("mky-captcha-audio-btn")) {
        e.preventDefault();
        const captchaId = e.target.dataset.captchaId;
        if (captchaId) {
          playMkyCaptchaAudio(captchaId, e.target);
        }
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMkyCaptcha);
  } else {
    initMkyCaptcha();
  }

  // Expose functions globally for programmatic access
  window.MkyCaptcha = {
    refresh: refreshMkyCaptcha,
    playAudio: function (id) {
      const button = document.querySelector(
        '[data-captcha-id="' + id + '"].mky-captcha-audio-btn'
      );
      if (button) {
        playMkyCaptchaAudio(id, button);
      }
    },
  };
})();
