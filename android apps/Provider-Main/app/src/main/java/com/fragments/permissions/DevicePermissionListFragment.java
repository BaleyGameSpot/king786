package com.fragments.permissions;

import android.annotation.SuppressLint;
import android.os.Build;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.RecyclerView;

import com.adapter.files.permissions.PermissionListAdapter;
import com.general.PermissionHandlers;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.DeviceSettings;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.ArrayList;
import java.util.HashMap;

public class DevicePermissionListFragment extends Fragment {

    private View view;
    private GeneralFunctions generalFunc;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_permission_list, container, false);
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        ArrayList<HashMap<String, String>> permissionList = getPermissionList();

        MTextView permissionTitleTxt = view.findViewById(R.id.permissionTitleTxt);
        permissionTitleTxt.setText(getString(R.string.app_name) + " " + generalFunc.retrieveLangLBl("", "LBL_NEED_PERMISSION_TXT"));
        RecyclerView rvPermissionList = view.findViewById(R.id.rvPermissionList);
        rvPermissionList.setAdapter(new PermissionListAdapter(permissionList));

        setShadowView(rvPermissionList);

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_LET_DO_IT_TXT"));
        btn_type2_location.setOnClickListener(v -> PermissionHandlers.getInstance().setPageNext());

        return view;
    }

    private void setShadowView(RecyclerView rvPermissionList) {
        View shadowHeaderViewTop = view.findViewById(R.id.shadowHeaderViewTop);
        View shadowHeaderViewBottom = view.findViewById(R.id.shadowHeaderViewBottom);
        shadowHeaderViewTop.setVisibility(View.INVISIBLE);
        shadowHeaderViewBottom.setVisibility(View.INVISIBLE);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            rvPermissionList.setOnScrollChangeListener((v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
                if (!v.canScrollVertically(-1)) {
                    shadowHeaderViewTop.setVisibility(View.INVISIBLE);
                } else {
                    shadowHeaderViewTop.setVisibility(View.VISIBLE);
                }

                if (!v.canScrollVertically(1)) {
                    shadowHeaderViewBottom.setVisibility(View.INVISIBLE);
                } else {
                    shadowHeaderViewBottom.setVisibility(View.VISIBLE);
                }
            });
        }
    }

    private ArrayList<HashMap<String, String>> getPermissionList() {
        ArrayList<HashMap<String, String>> list = new ArrayList<>();

        if (!DeviceSettings.isDeviceGPSEnabled()) {
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("icon", String.valueOf(R.drawable.ic_permission_gps));
            hashMap.put("title", generalFunc.retrieveLangLBl("", "LBL_GPS_SERVICE_TITLE"));
            hashMap.put("subTitle", generalFunc.retrieveLangLBl("", "LBL_GPS_SERVICE_DESC"));
            list.add(hashMap);
        }

        if (!DeviceSettings.isForegroundLocationEnabled() || !DeviceSettings.isBackgroundLocationEnabled()) {
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("icon", String.valueOf(R.drawable.ic_permission_locations));
            hashMap.put("title", generalFunc.retrieveLangLBl("", "LBL_DEVICE_LOCATION_TITLE"));
            hashMap.put("subTitle", generalFunc.retrieveLangLBl("", "LBL_DEVICE_LOCATION_DESC"));
            list.add(hashMap);
        }

        if (!DeviceSettings.isNotificationEnable()) {
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("icon", String.valueOf(R.drawable.ic_permission_notification));
            hashMap.put("title", generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS_TITLE"));
            hashMap.put("subTitle", generalFunc.retrieveLangLBl("", "LBL_NOTIFICATIONS_DESC"));
            list.add(hashMap);
        }

        if (!DeviceSettings.isDrawOverlayEnable()) {
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("icon", String.valueOf(R.drawable.ic_permission_draw_over_apps));
            hashMap.put("title", generalFunc.retrieveLangLBl("", "LBL_DRAW_OVER_APPS_TITLE"));
            hashMap.put("subTitle", generalFunc.retrieveLangLBl("", "LBL_DRAW_OVER_APPS_DESC"));
            list.add(hashMap);
        }

        if (!DeviceSettings.isBatterySaverDisabled()) {
            HashMap<String, String> hashMap = new HashMap<>();
            hashMap.put("icon", String.valueOf(R.drawable.ic_permission_battery));
            hashMap.put("title", generalFunc.retrieveLangLBl("", "LBL_NO_BACKGROUND_RESTRICTIONS_TITLE"));
            hashMap.put("subTitle", generalFunc.retrieveLangLBl("", "LBL_NO_BACKGROUND_RESTRICTIONS_DESC"));
            list.add(hashMap);
        }

        return list;
    }
}