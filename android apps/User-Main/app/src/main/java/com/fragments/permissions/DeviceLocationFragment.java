package com.fragments.permissions;

import android.Manifest;
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
import androidx.annotation.Nullable;
import androidx.core.app.ActivityCompat;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.BaseFragment;
import com.general.PermissionHandlers;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentPermissionsInfoBinding;
import com.utils.DeviceSettings;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

public class DeviceLocationFragment extends BaseFragment {

    private View view;
    private ParentActivity act;
    private MButton continueBtn;
    private long notification_permission_launch_time = -1;
    private final ActivityResultLauncher<Intent> settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (DeviceSettings.isForegroundLocationEnabled() && DeviceSettings.isBackgroundLocationEnabled()) {
            PermissionHandlers.getInstance().openSuccessPermissionDialogView();
        }
    });
    private final ActivityResultLauncher<String[]> foregroundLocationLauncher = registerForActivityResult(new ActivityResultContracts.RequestMultiplePermissions(), result -> {
                if (!DeviceSettings.isForegroundLocationEnabled() || !DeviceSettings.isBackgroundLocationEnabled()) {

                    if (DeviceSettings.isForegroundLocationEnabled() && !DeviceSettings.isBackgroundLocationEnabled()) {
                        continueBtn.performClick();
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
    private final ActivityResultLauncher<String> backgroundLocationLauncher = registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
        if (isGranted) {
            PermissionHandlers.getInstance().openSuccessPermissionDialogView();
        } else {
            if ((System.currentTimeMillis() - notification_permission_launch_time) < 500) {
                openSetting();
            }
        }
    });

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof ParentActivity activity) {
            act = activity;
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        FragmentPermissionsInfoBinding binding = DataBindingUtil.inflate(inflater, R.layout.fragment_permissions_info, container, false);

        binding.permissionTitleTxt.setText(act.generalFunc.retrieveLangLBl("", "LBL_DEVICE_LOCATION_TITLE"));
        binding.permissionImg.setImageResource(R.drawable.ic_permission_location);
        binding.txtSubTitle.setHtml(act.generalFunc.retrieveLangLBl("", "LBL_LOC_PERMISSION_NOTE_USER_ANDROID"), 0);

        PermissionHandlers.getInstance().setShadowView(binding);

        continueBtn = ((MaterialRippleLayout) binding.continueBtn).getChildView();
        continueBtn.setId(Utils.generateViewId());
        continueBtn.setText(act.generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        addToClickHandler(continueBtn);

        view = binding.getRoot();
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
        MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct()).showGeneralMessage(act.generalFunc.retrieveLangLBl("", "LBL_BACKGROUND_LOC_PER_TXT"), act.generalFunc.retrieveLangLBl("",
                        "LBL_BG_LOC_ALLOW_NOTE_ANDROID"), act.generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), act.generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"),
                buttonId -> {
                    if (buttonId == 1) {
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                            notification_permission_launch_time = System.currentTimeMillis();
                            backgroundLocationLauncher.launch(Manifest.permission.ACCESS_BACKGROUND_LOCATION);
                        }
                    }
                });
    }

    public void onClickView(View view) {
        if (view.getId() == continueBtn.getId()) {
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
        }
    }
}