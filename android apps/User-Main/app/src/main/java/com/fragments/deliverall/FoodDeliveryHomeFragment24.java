package com.fragments.deliverall;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.act.deliverAll.ServiceHomeActivity;
import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityFoodDeliveryHome24Binding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

public class FoodDeliveryHomeFragment24 extends Fragment {

    private ActivityFoodDeliveryHome24Binding binding;
    private final ServiceHomeActivity act;

    private FoodHomeScreen mFoodHomeScreen;

    public FoodDeliveryHomeFragment24(@NonNull ServiceHomeActivity serviceHomeActivity) {
        act = serviceHomeActivity;
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (binding == null) {
            mFoodHomeScreen = new FoodHomeScreen(act, act.generalFunc.retrieveValue(Utils.CURRENT_ADDRESSS), act.latitude, act.longitude);
            mFoodHomeScreen.setListener((address, latitude, longitude) -> {
                act.latitude = latitude;
                act.longitude = longitude;
            });
        }
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.activity_food_delivery_home_24, container, false);

        if (MyApp.getInstance().isGetDetailCall) {
            return binding.getRoot();
        }
        mFoodHomeScreen.onCreate(binding, getArguments() != null && getArguments().getBoolean("isBackHide", false));

        return binding.getRoot();
    }


    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        OpenNoLocationView.getInstance(act, binding.rootLayout).configView(false);
    }

    @Override
    public void onResume() {
        super.onResume();
        mFoodHomeScreen.onResume();
    }

    @Override
    public void onPause() {
        super.onPause();
        mFoodHomeScreen.onPause();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        mFoodHomeScreen.onDestroy();
    }

    public void onFragmentBackPressed() {
        mFoodHomeScreen.onBackPressed();
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(act);
        mFoodHomeScreen.onClickView(view.getId());
    }
}