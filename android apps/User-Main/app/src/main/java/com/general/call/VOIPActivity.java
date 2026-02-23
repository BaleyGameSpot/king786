package com.general.call;

import android.graphics.Color;
import android.os.Bundle;
import android.view.View;

import androidx.core.content.res.ResourcesCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.VoipActivityBinding;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import java.util.ArrayList;

public class VOIPActivity extends ParentActivity {

    private VoipActivityBinding binding;
    public VOIPViewModel voipViewModel;

    private MediaDataProvider dataProvider;
    private ArrayList<String> requestPermissions = new ArrayList<>();
    private GenerateAlertBox alertBox;
    private boolean isPermissionsDone = false;
    private boolean isSpeaker = false, isMute = false, isFrontCamera = true, isCamera = true;
    private int whiteColor, blackColor;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getWindow().getDecorView().setSystemUiVisibility(View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN);
        getWindow().setStatusBarColor(Color.TRANSPARENT);
        binding = DataBindingUtil.setContentView(this, R.layout.voip_activity);

        dataProvider = (MediaDataProvider) getIntent().getSerializableExtra(CommunicationManager.MY_DATA);
        if (dataProvider == null) {
            generalFunc.showError(true);
            return;
        }

        voipViewModel = new VOIPViewModel(this, binding, dataProvider, getIntent().getBooleanExtra(CommunicationManager.IS_INCOMING_VIEW, false));

        initialization();
        if (voipViewModel.isIncomingView) {
            intiIncomingView();
        } else {
            intiCallingView();
        }

        requestPermissions = MyApp.getInstance().checkCameraWithMicPermission(dataProvider.isVideoCall, false);
        generalFunc.isAllPermissionGranted(true, requestPermissions);
    }

    private void initialization() {
        String toMemberImage = dataProvider.toMemberType;
        String toMemberId = dataProvider.toMemberId;
        if (voipViewModel.isIncomingView) {
            if (dataProvider.fromMemberType != null && dataProvider.fromMemberId != null) {
                toMemberImage = dataProvider.fromMemberType;
                toMemberId = dataProvider.fromMemberId;
            }
        }
        if (Utils.checkText(dataProvider.toMemberImage)) {
            if (toMemberImage.equalsIgnoreCase(Utils.CALLTOPASSENGER)) {
                toMemberImage = CommonUtilities.USER_PHOTO_PATH + toMemberId + "/" + dataProvider.toMemberImage;
            } else if (toMemberImage.equalsIgnoreCase(Utils.CALLTODRIVER)) {
                toMemberImage = CommonUtilities.PROVIDER_PHOTO_PATH + toMemberId + "/" + dataProvider.toMemberImage;
            } else if (toMemberImage.equalsIgnoreCase(Utils.CALLTOSTORE)) {
                toMemberImage = CommonUtilities.COMPANY_PHOTO_PATH + toMemberId + "/" + dataProvider.toMemberImage;
            }
        }
        binding.ivAvatar.setImageDrawable(ResourcesCompat.getDrawable(getResources(), R.mipmap.ic_no_pic_user, null));
        if (Utils.checkText(toMemberImage)) {
            new LoadImage.builder(LoadImage.bind(toMemberImage), binding.ivAvatar).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
        }

        binding.nameTxt.setText(dataProvider.toMemberName);
        binding.stateTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALLING"));
        binding.callTimeTxt.setText("");

        //
        addToClickHandler(binding.speakerArea);
        addToClickHandler(binding.cameraArea);
        addToClickHandler(binding.switchArea);
        addToClickHandler(binding.muteArea);
        addToClickHandler(binding.declineArea);
        addToClickHandler(binding.acceptArea);
        binding.speakerTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_SPEAKER_TXT"));
        binding.cameraTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CAMERA_OFF_TXT"));
        binding.switchTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_SWITCH_TXT"));
        binding.muteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_MUTE_TXT"));
        binding.declineTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_DECLINE_TXT"));
        binding.acceptTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_ACCEPT_TXT"));

        //
        binding.ivAvatar.setVisibility(dataProvider.isVideoCall ? View.GONE : View.VISIBLE);
        binding.sfLocalView.setVisibility(dataProvider.isVideoCall ? View.VISIBLE : View.GONE);

        whiteColor = getResources().getColor(R.color.white);
        blackColor = getResources().getColor(R.color.black);
    }

    private void intiIncomingView() {
        binding.callTimeTxt.setVisibility(View.GONE);
        binding.speakerArea.setVisibility(View.GONE);
        binding.cameraArea.setVisibility(View.GONE);
        binding.switchArea.setVisibility(View.GONE);
        binding.muteArea.setVisibility(View.GONE);
    }

    public void intiCallingView() {
        binding.stateTxt.setVisibility(voipViewModel.isCallStart ? View.GONE : View.VISIBLE);
        binding.callTimeTxt.setVisibility(voipViewModel.isCallStart ? View.VISIBLE : View.GONE);
        binding.acceptArea.setVisibility(View.GONE);

        binding.speakerArea.setVisibility(dataProvider.isVideoCall ? View.GONE : View.VISIBLE);
        binding.cameraArea.setVisibility(dataProvider.isVideoCall ? View.VISIBLE : View.GONE);
        binding.switchArea.setVisibility(dataProvider.isVideoCall ? View.VISIBLE : View.GONE);
        binding.muteArea.setVisibility(View.VISIBLE);
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (!isPermissionsDone && !requestPermissions.isEmpty()) {
            if (generalFunc.isAllPermissionGranted(false, requestPermissions)) {
                closeNoPermissionV();
                isPermissionsDone = true;

                voipViewModel.initCall();

                // innit Button view handle
                isSpeaker = !dataProvider.isVideoCall;
                binding.speakerArea.performClick();
                binding.muteArea.performClick();
                binding.cameraImgBg.setColorFilter(isCamera ? blackColor : whiteColor);
                binding.cameraImg.setColorFilter(isCamera ? whiteColor : blackColor);
                binding.switchImgBg.setColorFilter(isFrontCamera ? blackColor : whiteColor);
                binding.switchImg.setColorFilter(isFrontCamera ? whiteColor : blackColor);
            } else {
                isPermissionsDone = false;
                showNoPermissionV();
            }
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        voipViewModel.onDestroy();
    }

    private void closeNoPermissionV() {
        if (alertBox != null && alertBox.alertDialog.isShowing()) {
            alertBox.closeAlertBox();
            alertBox = null;
        }
    }

    private void showNoPermissionV() {
        closeNoPermissionV();
        alertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application requires some permission to be granted to work. Please allow it.", "LBL_ALLOW_PERMISSIONS_APP"),
                generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS"),
                buttonId -> {
                    if (buttonId == 0) {
                        binding.declineArea.performClick();
                    } else {
                        generalFunc.openSettings();
                    }
                });
    }

    public void onCameraSwitchDone(boolean b) {
        isFrontCamera = b;
        binding.sfLocalView.setMirror(b);
        binding.switchImgBg.setColorFilter(isFrontCamera ? blackColor : whiteColor);
        binding.switchImg.setColorFilter(isFrontCamera ? whiteColor : blackColor);
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.speakerArea.getId()) {
            isSpeaker = !isSpeaker;
            voipViewModel.speakerEvent(isSpeaker);
            binding.speakerImgBg.setColorFilter(isSpeaker ? whiteColor : blackColor);
            binding.speakerImg.setColorFilter(isSpeaker ? blackColor : whiteColor);

        } else if (i == binding.cameraArea.getId()) {
            isCamera = !isCamera;
            voipViewModel.cameraEvent(isCamera);
            binding.cameraImgBg.setColorFilter(isCamera ? blackColor : whiteColor);
            binding.cameraImg.setColorFilter(isCamera ? whiteColor : blackColor);

        } else if (i == binding.switchArea.getId()) {
            voipViewModel.switchCameraEvent();

        } else if (i == binding.muteArea.getId()) {
            isMute = !isMute;
            voipViewModel.muteEvent(isMute);
            binding.muteImgBg.setColorFilter(isMute ? blackColor : whiteColor);
            binding.muteImg.setColorFilter(isMute ? whiteColor : blackColor);

        } else if (i == binding.declineArea.getId()) {
            voipViewModel.sendRTCData(dataProvider, "CALL_END", 0);
            finish();

        } else if (i == binding.acceptArea.getId()) {
            voipViewModel.isAnswerBtnClick = true;
            if (generalFunc.isAllPermissionGranted(true, requestPermissions)) {
                if (isPermissionsDone) {
                    voipViewModel.doAnswer();
                }
            }
        }
    }
}