package com.general.call;

import android.content.Context;
import android.media.AudioManager;

import com.utils.Logger;

public class AppRTCAudioManager {

    private static final String TAG = AppRTCAudioManager.class.getSimpleName();
    private boolean initialized = false;
    private final AudioManager audioManager;
    private int savedAudioMode = AudioManager.MODE_NORMAL;
    private boolean savedIsSpeakerPhoneOn = true;
    private boolean savedIsMicrophoneMute = false;

    public static AppRTCAudioManager create(Context context) {
        return new AppRTCAudioManager(context);
    }

    private AppRTCAudioManager(Context context) {
        Logger.d(TAG, "AppRTCAudioManager");
        audioManager = ((AudioManager) context.getSystemService(Context.AUDIO_SERVICE));
    }

    public void init() {
        Logger.d(TAG, "init");
        if (initialized) {
            return;
        }
        savedAudioMode = audioManager.getMode();
        savedIsSpeakerPhoneOn = audioManager.isSpeakerphoneOn();
        savedIsMicrophoneMute = audioManager.isMicrophoneMute();

        audioManager.setMode(AudioManager.MODE_IN_COMMUNICATION);
        initialized = true;
    }

    public void close() {
        Logger.d(TAG, "close");
        if (!initialized) {
            return;
        }
        setSpeakerphoneOn(savedIsSpeakerPhoneOn);
        setMicrophoneMute(savedIsMicrophoneMute);
        audioManager.setMode(savedAudioMode);
        initialized = false;
    }

    public void setSpeakerphoneOn(boolean on) {
        if (audioManager != null) {
            boolean wasOn = audioManager.isSpeakerphoneOn();
            Logger.d(TAG, "::setSpeakerphoneOn::" + wasOn);
            if (wasOn == on) {
                return;
            }
            audioManager.setSpeakerphoneOn(on);
        }
    }

    private void setMicrophoneMute(boolean on) {
        boolean wasMuted = audioManager.isMicrophoneMute();
        if (wasMuted == on) {
            return;
        }
        audioManager.setMicrophoneMute(on);
    }
}