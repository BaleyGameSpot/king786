package com.fragments.permissions;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;

import androidx.annotation.NonNull;
import androidx.fragment.app.Fragment;

import com.general.PermissionHandlers;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.HTextView;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

public class DeviceSetUpCompleteFragment extends Fragment {

    private View view;
    private ProgressBar mProgressBar;

    @SuppressLint("SetTextI18n")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        if (view != null) {
            return view;
        }
        view = inflater.inflate(R.layout.fragment_device_setup_complete, container, false);
        GeneralFunctions generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        mProgressBar = view.findViewById(R.id.mProgressBar);
        mProgressBar.getIndeterminateDrawable().setColorFilter(getResources().getColor(R.color.appThemeColor_2), android.graphics.PorterDuff.Mode.SRC_IN);
        mProgressBar.setIndeterminate(true);
        mProgressBar.setVisibility(View.GONE);

        MTextView txtTitle = view.findViewById(R.id.txtTitle);
        txtTitle.setText(generalFunc.retrieveLangLBl("", "LBL_SETUP_COMPLETE_TXT"));
        HTextView txtSubTitle = view.findViewById(R.id.txtSubTitle);
        txtSubTitle.setHtml(generalFunc.retrieveLangLBl("", "LBL_SETUP_COMPLETE_MSG_TXT"), 0);

        MButton btn_type2_location = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2_location)).getChildView();
        btn_type2_location.setId(Utils.generateViewId());
        btn_type2_location.setText(generalFunc.retrieveLangLBl("", "LBL_OK_GOT_IT_TXT"));

        btn_type2_location.setOnClickListener(v -> {
            mProgressBar.setVisibility(View.VISIBLE);
            PermissionHandlers.getInstance().setPageNext();
        });

        return view;
    }
}