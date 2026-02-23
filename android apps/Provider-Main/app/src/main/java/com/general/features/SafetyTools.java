package com.general.features;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.SystemClock;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.SeekBar;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;

import com.AudioRecord.AudioListener;
import com.AudioRecord.AudioRecording;
import com.AudioRecord.RecordingItem;
import com.act.EmergencyContactActivity;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.UploadProfileImage;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DialogSafetyToolsAudioRecordingAvailableBinding;
import com.buddyverse.providers.databinding.DialogSafetyToolsAudioRecordingBinding;
import com.buddyverse.providers.databinding.DialogSafetyToolsBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

import java.text.DecimalFormat;
import java.text.NumberFormat;
import java.util.HashMap;

public class SafetyTools {

    @Nullable
    @SuppressLint("StaticFieldLeak")
    private static SafetyTools instance;
    private Context mContext;
    private GeneralFunctions generalFunc;
    private String iTripId, eType;
    @Nullable
    private Activity mActivity;

    @Nullable
    private BottomSheetDialog safetyToolsDialog, audioRecordingDialog, audioRecordingAvailableDialog;
    @Nullable
    private DialogSafetyToolsAudioRecordingAvailableBinding bindingSTARA;
    private boolean isStartRecord = false;
    @Nullable
    private AudioRecording mAudioRecording;
    private JSONObject obj_userProfile;

    public static SafetyTools getInstance() {
        if (instance == null) {
            instance = new SafetyTools();
        }
        return instance;
    }

    public void dismissAllDialog() {
        if (safetyToolsDialog != null) {
            safetyToolsDialog.dismiss();
            safetyToolsDialog = null;
        }
        if (audioRecordingDialog != null) {
            audioRecordingDialog.dismiss();
            audioRecordingDialog = null;
        }
        if (audioRecordingAvailableDialog != null) {
            audioRecordingAvailableDialog.dismiss();
            audioRecordingAvailableDialog = null;
        }
    }

    public void initiate(@NonNull Context context, @NonNull GeneralFunctions generalFunc, String iTripId, String eType) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.iTripId = iTripId;
        this.eType = eType;

        this.mActivity = MyApp.getInstance().getCurrentAct();
        this.obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
    }

    @SuppressLint("SetTextI18n")
    public void safetyToolsDialog(boolean isRecordStartClick) {
        if (safetyToolsDialog != null) {
            if (safetyToolsDialog.isShowing()) {
                return;
            }
            if (isStartRecord && !isRecordStartClick) {
                safetyToolsDialog.show();
                return;
            }
        }
        safetyToolsDialog = new BottomSheetDialog(mContext, R.style.BottomSheetDialog);

        DialogSafetyToolsBinding bindingST = DialogSafetyToolsBinding.inflate(LayoutInflater.from(mContext));
        //
        bindingST.topTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SAFETY_TXT"));
        bindingST.headerTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SAFETY_TOOLS_TXT"));
        bindingST.callPoliceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT") + " " + generalFunc.getJsonValueStr("SITE_POLICE_CONTROL_NUMBER", obj_userProfile));
        bindingST.emeContactTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_SOS_MSG"));
        bindingST.recordAudioTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECORD_AUDIO_TXT"));
        bindingST.startRecordAudioTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECORD_AUDIO_TXT"));
        bindingST.shareTripTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SHARE_TRIP_STATUS_TXT"));
        bindingST.liveChatTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LIVE_CHAT"));

        bindingST.recordTimeTxt.setText("00:00");
        bindingST.stopTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STOP"));

        bindingST.callPoliceArea.setOnClickListener(v -> {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setPhoneNumber(generalFunc.getJsonValueStr("SITE_POLICE_CONTROL_NUMBER", obj_userProfile))
                    .setMedia(CommunicationManager.MEDIA.DEFAULT)
                    .build();
            CommunicationManager.getInstance().communicate(mActivity, mDataProvider, CommunicationManager.TYPE.OTHER);
            safetyToolsDialog.dismiss();
        });
        bindingST.emeContactArea.setOnClickListener(v -> {
            safetyToolsDialog.dismiss();
            emeContactClick();
        });

        bindingST.shareTripArea.setOnClickListener(v -> {
            safetyToolsDialog.dismiss();
            shareTripClick();
        });
        bindingST.liveChatArea.setOnClickListener(v -> {
            MyUtils.openLiveChatActivity(mContext, generalFunc, obj_userProfile);
            safetyToolsDialog.dismiss();
        });

        if (generalFunc.getJsonValueStr("ENABLE_LIVE_CHAT", obj_userProfile).equalsIgnoreCase("Yes")) {
            bindingST.liveChatArea.setVisibility(View.VISIBLE);
        } else {
            bindingST.liveChatArea.setVisibility(View.GONE);
        }

        if (isStartRecord) {
            bindingST.recordAudioArea.setVisibility(View.GONE);
            bindingST.startRecordAudioArea.setVisibility(View.VISIBLE);

            startRecord(bindingST);

            bindingST.stopArea.setOnClickListener(v -> {
                safetyToolsDialog.dismiss();
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_STOP_RECONDING_CONFIRM_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), button_Id -> {
                    if (button_Id == 1) {
                        isStartRecord = false;
                        if (mAudioRecording != null) {
                            mAudioRecording.stop(true);
                        }
                    } else {
                        safetyToolsDialog.show();
                    }
                });
            });
        } else {
            bindingST.recordAudioArea.setVisibility(View.VISIBLE);
            bindingST.startRecordAudioArea.setVisibility(View.GONE);
            bindingST.recordAudioArea.setOnClickListener(v -> {
                safetyToolsDialog.dismiss();
                audioRecordingDialog();
            });
        }

        bindingST.safetyToolkitArea.setVisibility(Utils.checkText(eType) && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) ? View.VISIBLE : View.GONE);
        if (ServiceModule.IsMedicalAll) {
            bindingST.safetyToolkitArea.setVisibility(View.VISIBLE);
            bindingST.dividerView.setVisibility(View.GONE);
            bindingST.startRecordAudioArea.setVisibility(View.GONE);
            bindingST.recordAudioArea.setVisibility(View.GONE);
        }

        //-------
        safetyToolsDialog.setContentView(bindingST.getRoot());
        LayoutDirection.setLayoutDirection(safetyToolsDialog);
        safetyToolsDialog.show();
    }

    private void emeContactClick() {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendAlertToEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iTripId", iTripId);
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {

            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null && !responseObj.toString().equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));

                } else {
                    if (generalFunc.getJsonValueStr(Utils.message_str_one, responseObj).equalsIgnoreCase("SmsError")) {
                        generalFunc.showGeneralMessage("", generalFunc.getJsonValueStr(Utils.message_str, responseObj));

                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)), "", generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), btn_id -> {
                            if (btn_id == 1) {
                                new ActUtils(mContext).startAct(EmergencyContactActivity.class);
                            }
                        });
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void shareTripClick() {
        Intent sharingIntent = new Intent(Intent.ACTION_SEND);
        sharingIntent.setType("text/plain");
        sharingIntent.putExtra(Intent.EXTRA_SUBJECT, "");
        String link_location = generalFunc.getJsonValueStr("liveTrackingUrl", obj_userProfile);
        if (Utils.checkText(link_location) && mActivity != null) {
            sharingIntent.putExtra(Intent.EXTRA_TEXT, generalFunc.retrieveLangLBl("", "LBL_SEND_STATUS_CONTENT_TXT") + " " + link_location);
            mActivity.startActivity(Intent.createChooser(sharingIntent, generalFunc.retrieveLangLBl("", "LBL_SHARE_USING")));
        }
    }

    private void startRecord(DialogSafetyToolsBinding bindingST) {
        AudioListener audioListener = new AudioListener() {
            @Override
            public void onStop(RecordingItem recordingItem) {
                Logger.d("audio_record_button", "onStop");
                if (mActivity != null && !mActivity.isFinishing()) {
                    audioRecordingAvailableDialog(recordingItem);
                }
            }

            @Override
            public void onCancel() {
                Logger.d("audio_record_button", "onCancel");
            }

            @Override
            public void onError(Exception e) {
                Logger.d("audio_record_button", "onError >> " + e.getMessage());
            }
        };
        String mFileName = "/safetyTools-" + iTripId + "-audio.wav";
        this.mAudioRecording = new AudioRecording(mContext).setNameFile(mFileName).start(audioListener);
        bindingST.recordTimeTxt.setBase(SystemClock.elapsedRealtime());
        bindingST.recordTimeTxt.start();
    }

    private void audioRecordingDialog() {
        if (audioRecordingDialog != null && audioRecordingDialog.isShowing()) {
            return;
        }
        audioRecordingDialog = new BottomSheetDialog(mContext, R.style.BottomSheetDialog);

        DialogSafetyToolsAudioRecordingBinding bindingSTAR = DialogSafetyToolsAudioRecordingBinding.inflate(LayoutInflater.from(mContext));
        //
        bindingSTAR.topTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_RECORDING_TXT"));
        bindingSTAR.headerTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SHARE_AUDIO_FOR_SAFETY_NOTE"));
        bindingSTAR.noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AVOID_BACKGROUND_AUDIO_RECORDING_NOTE"));

        MButton startAudioBtn = ((MaterialRippleLayout) bindingSTAR.startAudioBtn).getChildView();
        startAudioBtn.setText(generalFunc.retrieveLangLBl("", "LBL_START_AUDIO_RECORDING_TXT"));
        startAudioBtn.setOnClickListener(v -> {
            if (MyApp.getInstance().checkMicWithStorePermission(generalFunc, true)) {
                bindingSTAR.cancelBtnTxt.performClick();
                isStartRecord = true;
                safetyToolsDialog(true);
            }
        });
        bindingSTAR.cancelBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CLOSE_TXT"));
        bindingSTAR.cancelBtnTxt.setOnClickListener(v -> {
            audioRecordingDialog.dismiss();
            audioRecordingDialog = null;
        });

        //-------
        audioRecordingDialog.setCancelable(false);
        audioRecordingDialog.setContentView(bindingSTAR.getRoot());
        LayoutDirection.setLayoutDirection(audioRecordingDialog);
        audioRecordingDialog.show();
    }

    @SuppressLint("SetTextI18n")
    public void audioRecordingAvailableDialog(RecordingItem recordingItem) {
        if (audioRecordingAvailableDialog != null && audioRecordingAvailableDialog.isShowing()) {
            return;
        }
        audioRecordingAvailableDialog = new BottomSheetDialog(mContext, R.style.BottomSheetDialog);

        bindingSTARA = DialogSafetyToolsAudioRecordingAvailableBinding.inflate(LayoutInflater.from(mContext));
        //
        bindingSTARA.topTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_RECORDING_AVAILABLE_TXT"));
        bindingSTARA.noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_SAFETY_REPORT_NOTE"));
        bindingSTARA.whatWentMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REPORT_SAFETY_DESCRIPTION_TITLE"));
        bindingSTARA.messageBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_MESSAGE"));

        //
        bindingSTARA.audioTimeTxt.setText(formatDurationToSecondAndMinutes((int) Math.ceil(recordingItem.getLength() / 1000f)));
        bindingSTARA.audioSeekbar.setProgress(0);
        bindingSTARA.audioSeekbar.setOnSeekBarChangeListener(new SeekBar.OnSeekBarChangeListener() {
            @Override
            public void onProgressChanged(SeekBar seekBar, int progress, boolean fromUser) {
                int x = (int) Math.ceil((seekBar.getMax() - progress) / 1000f);

                bindingSTARA.audioTimeTxt.setText(formatDurationToSecondAndMinutes(x));

                if (seekBar.getMax() <= (progress + 200)) {
                    bindingSTARA.audioPlayBtn.setImageDrawable(ContextCompat.getDrawable(mContext, R.drawable.ic_baseline_play_arrow_24));
                    bindingSTARA.audioTimeTxt.setText(formatDurationToSecondAndMinutes((int) Math.ceil(recordingItem.getLength() / 1000f)));
                    bindingSTARA.audioSeekbar.setProgress(0);
                }
            }

            @Override
            public void onStartTrackingTouch(SeekBar seekBar) {

            }

            @Override
            public void onStopTrackingTouch(SeekBar seekBar) {
                if (mAudioRecording != null && mAudioRecording.mMediaPlayer != null && mAudioRecording.mMediaPlayer.isPlaying()) {
                    mAudioRecording.mMediaPlayer.seekTo(seekBar.getProgress());
                }
            }
        });

        //
        if (mAudioRecording == null) {
            mAudioRecording = new AudioRecording(mContext);
        }
        mAudioRecording.setNameFile(recordingItem.getName());
        mAudioRecording.play(recordingItem);
        mAudioRecording.mMediaPlayer.pause();

        bindingSTARA.audioPlayBtn.setOnClickListener(view -> {
            if (mAudioRecording.mMediaPlayer != null && mAudioRecording.mMediaPlayer.isPlaying()) {
                bindingSTARA.audioPlayBtn.setImageDrawable(ContextCompat.getDrawable(mContext, R.drawable.ic_baseline_play_arrow_24));
                mAudioRecording.mMediaPlayer.pause();
            } else {
                bindingSTARA.audioPlayBtn.setImageDrawable(ContextCompat.getDrawable(mContext, R.drawable.ic_baseline_pause_24));
                mAudioRecording.mMediaPlayer.start();
                mAudioRecording.play();
            }
        });

        MButton shareAudioBtn = ((MaterialRippleLayout) bindingSTARA.shareAudioBtn).getChildView();
        shareAudioBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SHARE_AUDIO_TXT"));
        shareAudioBtn.setOnClickListener(v -> {
            if (mAudioRecording.mMediaPlayer != null && mAudioRecording.mMediaPlayer.isPlaying()) {
                mAudioRecording.mMediaPlayer.stop();
            }
            shareAudio(recordingItem, Utils.getText(bindingSTARA.messageBox));
        });
        bindingSTARA.noNeededBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_RECORDING_NOT_REQUIRED_TXT"));
        bindingSTARA.noNeededBtnTxt.setOnClickListener(v -> {

            if (mAudioRecording.mMediaPlayer != null && mAudioRecording.mMediaPlayer.isPlaying()) {
                mAudioRecording.mMediaPlayer.stop();
            }

            mAudioRecording.deleteOutput();
            audioRecordingAvailableDialog.dismiss();
            audioRecordingAvailableDialog = null;
        });

        //-------
        audioRecordingAvailableDialog.setCancelable(false);
        audioRecordingAvailableDialog.setContentView(bindingSTARA.getRoot());
        LayoutDirection.setLayoutDirection(audioRecordingAvailableDialog);
        audioRecordingAvailableDialog.show();
    }

    private String formatDurationToSecondAndMinutes(int x) {

        if (x < 10) {
            return "00:0" + x;
        } else if (x >= 60) {
            long minutes = x / 60;
            long seconds = x % 60;
            NumberFormat f = new DecimalFormat("00");
            return f.format(minutes) + ":" + f.format(seconds);
        } else {
            return "00:" + x;
        }
    }

    private void shareAudio(RecordingItem recordingItem, String messageText) {
        HashMap<String, String> paramsList = new HashMap<String, String>() {{
            put("type", "UploadServiceSafetyMedia");
            put("iTripId", iTripId);
            put("vSafetyMessage", messageText);
        }};

        new UploadProfileImage(mActivity, recordingItem.getFilePath(), recordingItem.getName(), paramsList, "UploadServiceSafetyMedia").execute();
    }

    public void handleImgUploadResponse(String responseString) {
        if (audioRecordingAvailableDialog != null && audioRecordingAvailableDialog.isShowing()) {
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));

                    if (mAudioRecording != null) {
                        mAudioRecording.deleteOutput();
                    }

                    if (audioRecordingAvailableDialog != null) {
                        audioRecordingAvailableDialog.dismiss();
                        audioRecordingAvailableDialog = null;
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        }
    }

    public void setSeekbarMax(int duration) {
        if (audioRecordingAvailableDialog != null && audioRecordingAvailableDialog.isShowing()) {
            if (bindingSTARA != null) {
                bindingSTARA.audioSeekbar.setMax(duration);
            }
        }
    }

    public void setProgress(int currentPosition) {
        if (audioRecordingAvailableDialog != null && audioRecordingAvailableDialog.isShowing()) {
            if (bindingSTARA != null) {
                bindingSTARA.audioSeekbar.setProgress(currentPosition);
            }
        }
    }

    public void stopRecord() {
        if (mAudioRecording != null && isStartRecord) {
            mAudioRecording.stop(true);
        }
    }
}