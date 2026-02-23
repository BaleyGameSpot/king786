package com.fragments.permissions;

import android.Manifest;
import android.annotation.SuppressLint;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
import android.text.Html;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;

import com.general.PermissionHandlers;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.DeviceSettings;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.HTextView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class DeviceNotificationFragment extends Fragment {

    private View view;
    private GeneralFunctions generalFunc;
    private ActivityResultLauncher<Intent> settingLauncher;
    private ActivityResultLauncher<String> notificationActivityResult;
    private long notification_permission_launch_time = -1;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        notification_permission_launch_time = -1;
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_notification, container, false);
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS_TITLE"));

        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_DEVICE_NOTIFICATION_INFO"), 0);

        PermissionHandlers.getInstance().setShadowView(view);

        settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (DeviceSettings.isNotificationEnable()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            }
        });
        notificationActivityResult = registerForActivityResult(
                new ActivityResultContracts.RequestPermission(), isGranted -> {
                    if (isGranted) {
                        PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                    } else {
                        if ((System.currentTimeMillis() - notification_permission_launch_time) < 500) {
                            openNotificationPermissionDialogView();
                        }
                    }
                }
        );

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        btn_type2_location.setOnClickListener(v -> {
            if (DeviceSettings.isNotificationEnable()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                return;
            }
            if (Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU) {
                openNotificationPermissionDialogView();
                return;
            }
            notification_permission_launch_time = System.currentTimeMillis();
            notificationActivityResult.launch(Manifest.permission.POST_NOTIFICATIONS);
        });

        return view;
    }


    private void openSetting() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            Intent intent = new Intent();
            intent.setAction(Settings.ACTION_APP_NOTIFICATION_SETTINGS);
            intent.putExtra(Settings.EXTRA_APP_PACKAGE, MyApp.getInstance().getCurrentAct().getPackageName());
            settingLauncher.launch(intent);
        }
    }

    @SuppressLint("InflateParams")
    private void openNotificationPermissionDialogView() {

        GenerateAlertBox alert = new GenerateAlertBox(MyApp.getInstance().getCurrentAct());
        alert.setCustomView(R.layout.notification_permission_layout);

        MTextView titleTxt = (MTextView) alert.getView(R.id.titleTxt);
        MTextView btnAccept = (MTextView) alert.getView(R.id.btnAccept);
        MTextView btnReject = (MTextView) alert.getView(R.id.btnReject);

        String sourceString = generalFunc.retrieveLangLBl("", "LBL_ALLOW_RUNTIME_NOTI_TXT").replace("#PROJECT_NAME#", "<b>" + getString(R.string.app_name) + "</b> ");
        titleTxt.setText(Html.fromHtml(sourceString));

        btnAccept.setText(generalFunc.retrieveLangLBl("", "LBL_ALLOW"));
        btnReject.setText(generalFunc.retrieveLangLBl("", "LBL_DONT_ALLOW_TXT"));

        btnAccept.setOnClickListener(v -> {
            openSetting();
            btnReject.performClick();
        });
        btnReject.setOnClickListener(v -> alert.closeAlertBox());

        alert.showAlertBox();
        alert.alertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
    }
}