package com.fragments.permissions;

import android.Manifest;
import android.annotation.SuppressLint;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.net.Uri;
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
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.BaseFragment;
import com.general.PermissionHandlers;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentPermissionsInfoBinding;
import com.utils.DeviceSettings;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.Objects;

public class DeviceNotificationFragment extends BaseFragment {

    private View view;
    private ParentActivity act;
    private MButton continueBtn;
    private final ActivityResultLauncher<Intent> settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (DeviceSettings.isNotificationEnable()) {
            PermissionHandlers.getInstance().openSuccessPermissionDialogView();
        }
    });

    private long notification_permission_launch_time = -1;
    private final ActivityResultLauncher<String> notificationActivityResult = registerForActivityResult(
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

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof ParentActivity activity) {
            act = activity;
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        notification_permission_launch_time = -1;
        if (view != null) {
            return view;
        }
        FragmentPermissionsInfoBinding binding = DataBindingUtil.inflate(inflater, R.layout.fragment_permissions_info, container, false);

        binding.permissionTitleTxt.setText(act.generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS_TITLE"));
        binding.permissionImg.setImageResource(R.drawable.ic_permission_notifications);
        binding.txtSubTitle.setHtml(act.generalFunc.retrieveLangLBl("", "LBL_DEVICE_NOTIFICATION_INFO"), 0);

        PermissionHandlers.getInstance().setShadowView(binding);

        continueBtn = ((MaterialRippleLayout) binding.continueBtn).getChildView();
        continueBtn.setId(Utils.generateViewId());
        continueBtn.setText(act.generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        addToClickHandler(continueBtn);

        view = binding.getRoot();
        return view;
    }

    private void openSetting() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            Intent intent = new Intent();
            intent.setAction(Settings.ACTION_APP_NOTIFICATION_SETTINGS);
            intent.putExtra(Settings.EXTRA_APP_PACKAGE, MyApp.getInstance().getCurrentAct().getPackageName());
            settingLauncher.launch(intent);
        } else {
            Intent intent = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
            intent.setData(Uri.parse("package:" + MyApp.getInstance().getCurrentAct().getPackageName()));
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

        String sourceString = act.generalFunc.retrieveLangLBl("", "LBL_ALLOW_RUNTIME_NOTI_TXT").replace("#PROJECT_NAME#", "<b>" + getString(R.string.app_name) + "</b> ");
        titleTxt.setText(Html.fromHtml(sourceString));

        btnAccept.setText(act.generalFunc.retrieveLangLBl("", "LBL_ALLOW"));
        btnReject.setText(act.generalFunc.retrieveLangLBl("", "LBL_DONT_ALLOW_TXT"));

        btnAccept.setOnClickListener(v -> {
            openSetting();
            btnReject.performClick();
        });
        btnReject.setOnClickListener(v -> alert.closeAlertBox());

        alert.showAlertBox();
        Objects.requireNonNull(alert.alertDialog.getWindow()).setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
    }

    public void onClickView(View view) {
        if (view.getId() == continueBtn.getId()) {
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
        }
    }
}