package com.fragments.permissions;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
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
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.HTextView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class DeviceBatterySaverFragment extends Fragment {

    private View view;
    private GeneralFunctions generalFunc;
    private GenerateAlertBox currentAlertBox;
    private boolean isBatteryOptimizationsShow = false;
    private ActivityResultLauncher<Intent> settingLauncher;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_battery_saver, container, false);
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_BACKGROUND_RESTRICTIONS_TITLE"));

        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_NO_BACKGROUND_RESTRICTIONS_INFO"), 0);

        PermissionHandlers.getInstance().setShadowView(view);

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));

        generalFunc.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "No");

        if (DeviceSettings.isBatterySaverDisabled()) {
            PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            return view;
        }
        generalFunc.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "Yes");
        isBatteryOptimizationsShow = false;
        btn_type2_location.setOnClickListener(v -> {

            if (DeviceSettings.isBatteryOptimizationsEnable() && !DeviceSettings.isPowerSaveMode()) {
                generalFunc.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "No");
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                return;
            }
            openSetting();
        });
        settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (DeviceSettings.isBatteryOptimizationsEnable() && !DeviceSettings.isPowerSaveMode()) {
                generalFunc.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "No");
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                return;
            }
            if (DeviceSettings.isBatteryOptimizationsEnable() && DeviceSettings.isPowerSaveMode() && isBatteryOptimizationsShow) {
                isBatteryOptimizationsShow = false;
                showBatterySave();
            }
        });
        return view;
    }


    @SuppressLint("BatteryLife")
    private void showBatterySave() {

        String msg = generalFunc.retrieveLangLBl("", "LBL_DISABLE_BATTERY_SAVE_NOTE");
        boolean isHuaweiXiaomi = false;
        if (Build.MANUFACTURER.equalsIgnoreCase("Xiaomi") || Build.MANUFACTURER.equalsIgnoreCase("Huawei")) {
            isHuaweiXiaomi = true;
            msg = generalFunc.retrieveLangLBl("Disable the Battery Saver to let application works normally. Please disable it from Device Settings.", "LBL_DISABLE_BATTERY_SAVE_MANUAL_NOTE");
        }

        boolean finalIsHuaweiXiaomi = isHuaweiXiaomi;
        currentAlertBox = MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct()).showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_DISABLE_BATTERY_SAVE_TXT"), msg,
                isHuaweiXiaomi ? "" : generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"),
                isHuaweiXiaomi ? generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT") : generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"),
                buttonId -> {
                    if (buttonId == 0) {
                        currentAlertBox.closeAlertBox();
                    } else {
                        currentAlertBox.closeAlertBox();
                        if (!finalIsHuaweiXiaomi) {
                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP_MR1) {
                                settingLauncher.launch(new Intent(Settings.ACTION_BATTERY_SAVER_SETTINGS));
                            }
                        }
                    }
                });
    }

    @SuppressLint("BatteryLife")
    private void openSetting() {
        try {
            if (Build.VERSION.SDK_INT > Build.VERSION_CODES.LOLLIPOP_MR1) {
                if (!DeviceSettings.isBatteryOptimizationsEnable()) {
                    Intent i = new Intent();
                    i.setAction(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS);
                    i.setData(Uri.parse("package:" + MyApp.getInstance().getApplicationContext().getPackageName()));
                    //i.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    isBatteryOptimizationsShow = true;
                    settingLauncher.launch(i);
                } else if (DeviceSettings.isPowerSaveMode()) {
                    showBatterySave();
                }
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

}