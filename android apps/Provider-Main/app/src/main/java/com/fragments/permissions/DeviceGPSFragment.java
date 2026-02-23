package com.fragments.permissions;

import android.annotation.SuppressLint;
import android.content.Intent;
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
import com.utils.Utils;
import com.view.HTextView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class DeviceGPSFragment extends Fragment {

    private View view;
    private ActivityResultLauncher<Intent> gpsLauncher;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_gps, container, false);
        GeneralFunctions generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GPS_SERVICE_TITLE"));

        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_DEVICE_GPS_INFO"), 0);

        PermissionHandlers.getInstance().setShadowView(view);

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));

        gpsLauncher = registerForActivityResult(
                new ActivityResultContracts.StartActivityForResult(), result -> {
                    if (DeviceSettings.isDeviceGPSEnabled()) {
                        PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                    }
                });

        btn_type2_location.setOnClickListener(v -> {
            if (DeviceSettings.isDeviceGPSEnabled()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                return;
            }
            gpsLauncher.launch(new Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS));
        });

        return view;
    }
}