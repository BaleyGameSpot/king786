package com.general;

import android.Manifest;
import android.app.Dialog;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.app.ActivityCompat;

import com.activity.ParentActivity;
import com.dialogs.BottomInfoDialog;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.LayoutDirection;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;

public class PermissionHandler {

    private static PermissionHandler instance;
    private ParentActivity activity;
    private int PERMISSION_REQ_CODE, SETTINGS_REQ_CODE;
    private String[] mPermissions;
    private Dialog mNoPermissionsDialog;
    private String mMessage;

    public static PermissionHandler getInstance() {
        if (instance == null) {
            instance = new PermissionHandler();
        }
        return instance;
    }

    public void initiateHandle(@NonNull ParentActivity activity, boolean openDialog, @NonNull String[] permissions, String message, int PERMISSION_REQ_CODE, int SETTINGS_REQ_CODE) {
        this.activity = activity;
        this.mPermissions = permissions;
        //this.mMessage = Utils.checkText(message) ? message : PermissionDeniedMsg(permissions);
        this.mMessage = PermissionDeniedMsg(permissions);
        this.PERMISSION_REQ_CODE = PERMISSION_REQ_CODE;
        this.SETTINGS_REQ_CODE = SETTINGS_REQ_CODE;

        if (checkAnyPermissions(activity.generalFunc, openDialog, permissions, PERMISSION_REQ_CODE)) {
            closeView();
            activity.onReqPermissionsResult();
        } else {
            execute();
        }
    }

    private void execute() {
        if (mNoPermissionsDialog != null && mNoPermissionsDialog.isShowing()) {
            ((MTextView) mNoPermissionsDialog.findViewById(R.id.txtMessage)).setText(mMessage);
            return;
        }
        mNoPermissionsDialog = new Dialog(activity, android.R.style.Theme_Light_NoTitleBar_Fullscreen);
        mNoPermissionsDialog.setContentView(R.layout.design_no_permission_dialog);

        ImageView backImgView = mNoPermissionsDialog.findViewById(R.id.backImgView);
        MButton btn_type2 = ((MaterialRippleLayout) mNoPermissionsDialog.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(activity.generalFunc.retrieveLangLBl("", "LBL_SETTINGS"));

        MTextView txtHowItWorks = mNoPermissionsDialog.findViewById(R.id.txtHowItWorks);

        MTextView txtMessage = mNoPermissionsDialog.findViewById(R.id.txtMessage);
        txtMessage.setText(mMessage);

        backImgView.setOnClickListener(v -> mNoPermissionsDialog.cancel());
        btn_type2.setOnClickListener(view -> {
            int myCount = 0;
            for (String permission : mPermissions) {
                if (!ActivityCompat.shouldShowRequestPermissionRationale(activity, permission)) {
                    myCount++;
                }
            }
            if (mPermissions.length == myCount) {
                activity.generalFunc.openSettings(true, SETTINGS_REQ_CODE);
            } else {
                checkAnyPermissions(activity.generalFunc, true, mPermissions, PERMISSION_REQ_CODE);
            }
        });

        txtHowItWorks.setText(activity.generalFunc.retrieveLangLBl("", "LBL_HOW_IT_WORKS_TXT"));
        txtHowItWorks.setOnClickListener(v -> {
            HashMap<Integer, Object> howItWorkHashMap = howItWorkMsg(mPermissions);
            if (howItWorkHashMap.get(1) == null) {
                return;
            }
            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(activity, activity.generalFunc);
            bottomInfoDialog.showPreferenceDialog(activity.generalFunc.retrieveLangLBl("", "LBL_ALLOW_PERMISSION_TXT"),
                    howItWorkHashMap.get(2).toString(), (Integer) howItWorkHashMap.get(1),
                    activity.generalFunc.retrieveLangLBl("", "LBL_OK"));
        });

        LayoutDirection.setLayoutDirection(mNoPermissionsDialog);
        mNoPermissionsDialog.show();
    }

    public void closeView() {
        if (mNoPermissionsDialog != null) {
            mNoPermissionsDialog.cancel();
            mNoPermissionsDialog = null;
        }
    }

    public boolean checkAnyPermissions(GeneralFunctions generalFunc, boolean openDialog, String[] permissions, int PERMISSION_REQ_CODE) {
        ArrayList<String> requestPermissions = new ArrayList<>();
        Collections.addAll(requestPermissions, permissions);
        return generalFunc.isAllPermissionGranted(openDialog, requestPermissions, PERMISSION_REQ_CODE);
    }

    private String PermissionDeniedMsg(String[] requestPermissions) {
        String msg = "";
        for (String permission : requestPermissions) {
            if (permission.equalsIgnoreCase(Manifest.permission.READ_CONTACTS)) {
                msg = activity.generalFunc.retrieveLangLBl("", "LBL_CONTACT_ALLOW_NOTE");
                break;
            } else if (permission.equalsIgnoreCase(Manifest.permission.RECORD_AUDIO)) {
                msg = activity.generalFunc.retrieveLangLBl("", "LBL_AUDIO_ALLOW_NOTE");
                break;
            } else if (permission.equalsIgnoreCase(Manifest.permission.CAMERA)
                    || permission.equalsIgnoreCase(Manifest.permission.READ_MEDIA_IMAGES)
                    || permission.equalsIgnoreCase(Manifest.permission.READ_MEDIA_VIDEO)
                    || permission.equalsIgnoreCase(Manifest.permission.READ_EXTERNAL_STORAGE)
                    || permission.equalsIgnoreCase(Manifest.permission.WRITE_EXTERNAL_STORAGE)) {
                msg = activity.generalFunc.retrieveLangLBl("", "LBL_CAMERA_STORAGE_REQUIRE_TXT");
                break;
            }
        }
        return msg;
    }

    private HashMap<Integer, Object> howItWorkMsg(String[] requestPermissions) {
        HashMap<Integer, Object> hashMap = new HashMap<>();
        for (String permission : requestPermissions) {
            if (permission.equalsIgnoreCase(Manifest.permission.READ_CONTACTS)) {
                hashMap.put(1, R.drawable.ic_permission_contacts);
                hashMap.put(2, activity.generalFunc.retrieveLangLBl("", "LBL_PERMISSION_CONTACTS_TXT"));
                break;
            } else if (permission.equalsIgnoreCase(Manifest.permission.RECORD_AUDIO)) {
                hashMap.put(1, R.drawable.ic_permission_audio);
                hashMap.put(2, activity.generalFunc.retrieveLangLBl("", "LBL_PERMISSION_AUDIO_TXT"));
                break;
            } else if (permission.equalsIgnoreCase(Manifest.permission.CAMERA) || permission.equalsIgnoreCase(Manifest.permission.READ_EXTERNAL_STORAGE) || permission.equalsIgnoreCase(Manifest.permission.WRITE_EXTERNAL_STORAGE) || permission.equalsIgnoreCase(Manifest.permission.READ_MEDIA_IMAGES) || permission.equalsIgnoreCase(Manifest.permission.READ_MEDIA_VIDEO)) {
                hashMap.clear();
                hashMap.put(1, R.drawable.ic_permission_camera_store);
                hashMap.put(2, activity.generalFunc.retrieveLangLBl("", "LBL_PERMISSION_CAMERA_STORAGE_TXT"));
                break;
            }
        }
        return hashMap;
    }
}