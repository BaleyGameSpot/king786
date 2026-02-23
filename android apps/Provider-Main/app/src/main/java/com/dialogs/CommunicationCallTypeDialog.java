package com.dialogs;

import android.content.Context;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.app.ActivityCompat;

import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.providers.R;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;

public class CommunicationCallTypeDialog implements ActivityCompat.OnRequestPermissionsResultCallback {

    @Nullable
    public BottomSheetDialog sheetDialog;
    private GeneralFunctions generalFunc;
    private CommunicationCallTypeList listener;

    public static final int COMMUNICATION_CAMERA_MIC_PERMISSIONS_REQUEST = 911;

    private GenerateAlertBox currentAlertBox;

    private Context mContext;
    private CommunicationManager.TYPE mCommType;

    private MediaDataProvider dataProvider;
    public boolean btnClick = false;

    public void setListener(CommunicationCallTypeList listener) {
        this.listener = listener;
    }

    public void showPreferenceDialog(Context mContext, MediaDataProvider dataProvider) {
        if (sheetDialog != null && sheetDialog.isShowing()) {
            return;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        generalFunc = MyApp.getInstance().getGeneralFun(mContext);

        View dialogView = View.inflate(mContext, R.layout.design_communication_call_type, null);
        builder.setView(dialogView);

        ImageView closeImg = dialogView.findViewById(R.id.closeImg);
        closeImg.setOnClickListener(v -> {
            if (sheetDialog != null) {
                sheetDialog.dismiss();
            }
        });

        MTextView txtTitle = dialogView.findViewById(R.id.txtTitle);
        MTextView txtAudio = dialogView.findViewById(R.id.txtAudio);
        MTextView txtVideo = dialogView.findViewById(R.id.txtVideo);
        txtTitle.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_CALL_TYPE_TXT"));
        txtAudio.setText(generalFunc.retrieveLangLBl("", "LBL_AUDIO_CALL"));
        txtVideo.setText(generalFunc.retrieveLangLBl("", "LBL_VIDEO_CALL"));

        LinearLayout llVideoArea = dialogView.findViewById(R.id.llVideoArea);
        this.mContext = mContext;
        this.dataProvider = dataProvider;
        llVideoArea.setOnClickListener(view -> {
            // Video Call Click
            btnClick = true;
            checkPermissions(mContext, CommunicationManager.TYPE.VIDEO_CALL, dataProvider);
        });

        LinearLayout llAudioArea = dialogView.findViewById(R.id.llAudioArea);
        llAudioArea.setOnClickListener(view -> {
            // Audio Call Click
            btnClick = true;
            checkPermissions(mContext, CommunicationManager.TYPE.VOIP_CALL, dataProvider);
        });

        sheetDialog = new BottomSheetDialog(mContext);
        sheetDialog.setContentView(dialogView);
        View bottomSheetView = sheetDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(mContext.getResources().getColor(android.R.color.transparent));

        (BottomSheetBehavior.from((View) dialogView.getParent())).setPeekHeight(Utils.dpToPx(600, mContext));

        sheetDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(sheetDialog);
        Animation a = AnimationUtils.loadAnimation(mContext, R.anim.bottom_up);
        a.reset();
        bottomSheetView.clearAnimation();
        bottomSheetView.startAnimation(a);
        sheetDialog.show();
    }

    private void showNoPermission() {
        currentAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application requires some permission to be granted to work. Please allow it.", "LBL_ALLOW_PERMISSIONS_APP"),
                generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS"),
                buttonId -> {
                    if (buttonId == 0) {
                        currentAlertBox.closeAlertBox();
                    } else {
                        generalFunc.openSettings();
                    }
                });
    }

    public void checkPermissions(Context mContext, CommunicationManager.TYPE commType, MediaDataProvider dataProvider) {
        this.generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        this.mCommType = commType;
        this.mContext = mContext;
        this.dataProvider = dataProvider;

        ArrayList<String> requestPermissions = MyApp.getInstance().checkCameraWithMicPermission(commType == CommunicationManager.TYPE.VIDEO_CALL, CommunicationManager.MEDIA_TYPE == CommunicationManager.MEDIA.TWILIO);

        if (generalFunc.isAllPermissionGranted(true, requestPermissions, COMMUNICATION_CAMERA_MIC_PERMISSIONS_REQUEST) && btnClick) {
            btnClick = false;
            onListener(mContext, commType, dataProvider);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull @NotNull int[] grantResults) {

        ArrayList<String> requestPermissions = MyApp.getInstance().checkCameraWithMicPermission(mCommType == CommunicationManager.TYPE.VIDEO_CALL, CommunicationManager.MEDIA_TYPE == CommunicationManager.MEDIA.TWILIO);

        if (generalFunc.isAllPermissionGranted(false, requestPermissions, COMMUNICATION_CAMERA_MIC_PERMISSIONS_REQUEST)) {
            onListener(mContext, mCommType, dataProvider);
        } else {
            showNoPermission();
        }
    }

    private void onListener(Context mContext, CommunicationManager.TYPE type, MediaDataProvider dataProvider) {
        listener.onCallTypeSelected(mContext, type, dataProvider);
        if (sheetDialog != null) {
            sheetDialog.dismiss();
        }
        if (currentAlertBox != null && currentAlertBox.alertDialog.isShowing()) {
            currentAlertBox.closeAlertBox();
        }
    }

    public interface CommunicationCallTypeList {
        void onCallTypeSelected(Context mContext, CommunicationManager.TYPE type, MediaDataProvider dataProvider);
    }
}