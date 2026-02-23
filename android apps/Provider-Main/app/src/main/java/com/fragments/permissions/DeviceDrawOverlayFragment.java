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
import com.utils.Utils;
import com.view.HTextView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class DeviceDrawOverlayFragment extends Fragment {

    private View view;
    private ActivityResultLauncher<Intent> settingLauncher;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_draw_overlay, container, false);
        GeneralFunctions generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DRAW_OVER_APPS_TITLE"));

        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_DRAW_OVER_APPS_INFO"), 0);

        PermissionHandlers.getInstance().setShadowView(view);

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));


        settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (DeviceSettings.isDrawOverlayEnable()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
            }
        });

        btn_type2_location.setOnClickListener(v -> {
            if (DeviceSettings.isDrawOverlayEnable()) {
                PermissionHandlers.getInstance().openSuccessPermissionDialogView();
                return;
            }
            openSetting();
        });

        return view;
    }


    private void openSetting() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            settingLauncher.launch(new Intent(Settings.ACTION_MANAGE_OVERLAY_PERMISSION, Uri.parse("package:" + MyApp.getInstance().getCurrentAct().getPackageName())));
        }
    }
}