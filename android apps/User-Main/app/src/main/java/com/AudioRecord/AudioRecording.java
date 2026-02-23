package com.AudioRecord;

import android.content.Context;
import android.media.MediaPlayer;
import android.media.MediaRecorder;

import androidx.annotation.Nullable;

import com.act.deliverAll.CheckOutActivity;
import com.general.features.SafetyTools;
import com.utils.Logger;

import java.io.File;
import java.io.IOException;

public class AudioRecording {

    private String mFileName;
    private final Context mContext;

    public MediaPlayer mMediaPlayer;
    private AudioListener audioListener;
    private final MediaRecorder mRecorder;
    private long mStartingTimeMillis = 0;
    @Nullable
    private CheckOutActivity checkoutAct;

    public AudioRecording(Context context) {
        mRecorder = new MediaRecorder();
        this.mContext = context;
        if (mContext instanceof CheckOutActivity activity) {
            checkoutAct = activity;
        }
    }

    public AudioRecording setNameFile(String nameFile) {
        this.mFileName = nameFile;
        return this;
    }

    public AudioRecording start(AudioListener audioListener) {
        this.audioListener = audioListener;

        try {

            mRecorder.reset();

            mRecorder.setAudioSource(MediaRecorder.AudioSource.MIC);
            mRecorder.setOutputFormat(MediaRecorder.OutputFormat.MPEG_4);
            mRecorder.setOutputFile(mContext.getCacheDir() + mFileName);
            mRecorder.setAudioEncoder(MediaRecorder.AudioEncoder.AAC);

            mRecorder.prepare();
            mRecorder.start();
            mStartingTimeMillis = System.currentTimeMillis();

        } catch (IOException e) {
            this.audioListener.onError(e);
        }
        return this;
    }

    public void stop(Boolean cancel) {
        try {
            mRecorder.stop();
        } catch (RuntimeException e) {
            deleteOutput();
        }
        mRecorder.release();
        long mElapsedMillis = (System.currentTimeMillis() - mStartingTimeMillis);

        if (mElapsedMillis < 1000) {
            deleteOutput();
            audioListener.onCancel();
            return;
        }

        RecordingItem recordingItem = new RecordingItem();
        recordingItem.setFilePath(mContext.getCacheDir() + mFileName);
        recordingItem.setName(mFileName);
        recordingItem.setLength((int) mElapsedMillis);
        recordingItem.setTime(System.currentTimeMillis());

        audioListener.onStop(recordingItem);
    }

    public void deleteOutput() {
        File file = new File(mContext.getCacheDir() + mFileName);
        if (file.exists() && file.delete()) {
            Logger.d("file.delete()", ":: File Deleted Successfully");
        } else {
            Logger.d("file.delete()", ":: Error Deleting File");
        }
    }

    public void play(RecordingItem recordingItem) {
        try {
            this.mMediaPlayer = new MediaPlayer();
            Logger.d("recordingItem.getFilePath()", "::" + recordingItem.getFilePath());
            this.mMediaPlayer.setDataSource(recordingItem.getFilePath());
            this.mMediaPlayer.prepare();
            this.mMediaPlayer.setLooping(false);
            this.mMediaPlayer.start();

            if (checkoutAct != null) {
                checkoutAct.seekbar.setMax(mMediaPlayer.getDuration());
            }
            if (SafetyTools.getInstance() != null) {
                SafetyTools.getInstance().setSeekbarMax(mMediaPlayer.getDuration());
            }

            new Thread(this::run).start();

        } catch (IOException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public void play() {
        if (mMediaPlayer != null) {
            if (checkoutAct != null) {
                checkoutAct.seekbar.setMax(mMediaPlayer.getDuration());
            }
            if (SafetyTools.getInstance() != null) {
                SafetyTools.getInstance().setSeekbarMax(mMediaPlayer.getDuration());
            }
            new Thread(this::run).start();
        }
    }

    public void run() {

        int currentPosition = mMediaPlayer.getCurrentPosition();
        int total = mMediaPlayer.getDuration();


        while (mMediaPlayer != null && mMediaPlayer.isPlaying() && currentPosition < total) {
            try {
                Thread.sleep(1000);
                currentPosition = mMediaPlayer.getCurrentPosition();
            } catch (InterruptedException e) {
                return;
            } catch (Exception e) {
                return;
            }
            if (checkoutAct != null) {
                checkoutAct.seekbar.setProgress(currentPosition);
            }
            if (SafetyTools.getInstance() != null) {
                SafetyTools.getInstance().setProgress(currentPosition);
            }
        }
    }

    public void pauseplay() {
        if (mMediaPlayer != null) {
            if (checkoutAct != null) {
                checkoutAct.seekbar.setMax(mMediaPlayer.getDuration());
            }
            if (SafetyTools.getInstance() != null) {
                SafetyTools.getInstance().setSeekbarMax(mMediaPlayer.getDuration());
            }
            new Thread(this::run).start();
        }
    }
}
