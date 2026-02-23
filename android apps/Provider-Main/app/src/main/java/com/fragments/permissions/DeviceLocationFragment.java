package com.fragments.permissions;

import android.Manifest;
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
import androidx.core.app.ActivityCompat;
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

public class DeviceLocationFragment extends Fragment {

    private View view;
    private GeneralFunctions generalFunc;
    private GenerateAlertBox currentAlertBox;
    private long notification_permission_launch_time = -1;
    private MButton btn_type2_location;
    private ActivityResultLauncher<Intent> settingLauncher;
    private ActivityResultLauncher<String[]> foregroundLocationLauncher;
    private ActivityResultLauncher<String> backgroundLocationLauncher;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_location, container, false);
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEVICE_LOCATION_TITLE"));

        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_LOC_PERMISSION_NOTE_PROVIDER_ANDROID"), 0);

        PermissionHandlers.getInstance().setShadowView(view);

        btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));

        settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (DeviceSettings.isForegroundLocationEnabled() && DeviceSettings.isBackgroundLocationEnabled()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            }
        });

        backgroundLocationLauncher = registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
            if (isGranted) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            } else {
                if ((System.currentTimeMillis() - notification_permission_launch_time) < 500) {
                    openSetting();
                }
            }
        });

        foregroundLocationLauncher = registerForActivityResult(new ActivityResultContracts.RequestMultiplePermissions(),
                result -> {
                    if (!DeviceSettings.isForegroundLocationEnabled() || !DeviceSettings.isBackgroundLocationEnabled()) {

                        if (DeviceSettings.isForegroundLocationEnabled() && !DeviceSettings.isBackgroundLocationEnabled()) {
                            btn_type2_location.performClick();
                            return;
                        }

                        if (!DeviceSettings.isForegroundLocationEnabled()) {
                            if ((System.currentTimeMillis() - notification_permission_launch_time) < 500) {
                                int myCount = 0;
                                for (String permission : result.keySet()) {
                                    if (!ActivityCompat.shouldShowRequestPermissionRationale(requireActivity(), permission)) {
                                        myCount++;
                                    }
                                }
                                if (result.size() == myCount) {
                                    openSetting();
                                }
                            }
                        }
                        return;
                    }
                    PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                }
        );


        btn_type2_location.setOnClickListener(v -> {

            if (!DeviceSettings.isForegroundLocationEnabled()) {
                String[] permissionList;
                if (Build.VERSION.SDK_INT == Build.VERSION_CODES.Q) {
                    permissionList = new String[]{Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION, Manifest.permission.ACCESS_BACKGROUND_LOCATION};
                } else {
                    permissionList = new String[]{Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION};
                }
                notification_permission_launch_time = System.currentTimeMillis();
                foregroundLocationLauncher.launch(permissionList);
                return;
            }

            if (!DeviceSettings.isBackgroundLocationEnabled()) {
                showBackGroundLocationPermission();
                return;
            }

            if (DeviceSettings.isForegroundLocationEnabled() && DeviceSettings.isBackgroundLocationEnabled()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            }
        });

        return view;
    }


    private void openSetting() {
        Intent intent = new Intent();
        intent.setAction(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
        Uri uri = Uri.fromParts("package", MyApp.getInstance().getApplicationContext().getPackageName(), null);
        intent.setData(uri);
        settingLauncher.launch(intent);
    }

    private void showBackGroundLocationPermission() {
        currentAlertBox = MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct()).showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_BACKGROUND_LOC_PER_TXT"), generalFunc.retrieveLangLBl("",
                        "LBL_BG_LOC_ALLOW_NOTE_ANDROID"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"),
                buttonId -> {
                    if (buttonId == 0) {
                        currentAlertBox.closeAlertBox();
                    } else {
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                            notification_permission_launch_time = System.currentTimeMillis();
                            backgroundLocationLauncher.launch(Manifest.permission.ACCESS_BACKGROUND_LOCATION);
                        }
                    }
                });
    }
}