package com.act.rideSharingPro;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.act.rideSharingPro.fragmentHome.RideSharingPublishFragment;
import com.act.rideSharingPro.fragmentHome.RideSharingRidesFragment;
import com.act.rideSharingPro.fragmentHome.RideSharingSearchFragment;
import com.act.rideSharingPro.model.RideProPublishData;
import com.activity.ParentActivity;
import com.dialogs.BottomInfoDialog;
import com.facebook.ads.AdSize;
import com.fragments.MyProfileFragment;
import com.general.files.FileSelector;
import com.general.files.MyApp;
import com.general.files.OpenAdvertisementDialog;
import com.google.android.gms.ads.AdRequest;
import com.google.android.gms.ads.AdView;
import com.google.android.gms.ads.MobileAds;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideSharingHomeBinding;
import com.model.ServiceModule;
import com.utils.Logger;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class RideSharingProHomeActivity extends ParentActivity {
    public ActivityRideSharingHomeBinding binding;
    private int bottomBtnPos = 1;
    private int selectedColor, deSelectedColor;

    public RideSharingSearchFragment rsSearchFragment;
    public RideSharingPublishFragment rsPublishFragment;
    public View publishFrgView;
    public RideSharingRidesFragment rsRidesFragment;
    private MyProfileFragment rsProfileFragment;
    private boolean isSearchFrg, isPublishFrg, isProfileFrg;
    public boolean isRidesFrg;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_sharing_home);

        initialization();
        manageBottomMenu(binding.searchTxt);

        if (!ServiceModule.OnlyRideSharingPro) {
            if (getIntent().getStringExtra("eCatType").equalsIgnoreCase("RIDESHAREBOOK")) {
                binding.searchArea.performClick();
            } else if (getIntent().getStringExtra("eCatType").equalsIgnoreCase("RIDESHAREPUBLISH")) {
                binding.publishArea.performClick();
            }
            binding.bottomArea.setVisibility(View.GONE);
        } else {
            binding.searchArea.performClick();
        }
        if (ServiceModule.OnlyRideSharingPro && !MyApp.getInstance().isGetDetailCall) {
            String advertise_banner_data = generalFunc.getJsonValueStr("advertise_banner_data", obj_userProfile);
            if (advertise_banner_data != null && !advertise_banner_data.equalsIgnoreCase("")) {

                if (generalFunc.getJsonValue("image_url", advertise_banner_data) != null && !generalFunc.getJsonValue("image_url", advertise_banner_data).equalsIgnoreCase("")) {
                    HashMap<String, String> map = new HashMap<>();
                    map.put("image_url", generalFunc.getJsonValue("image_url", advertise_banner_data));
                    map.put("tRedirectUrl", generalFunc.getJsonValue("tRedirectUrl", advertise_banner_data));
                    map.put("iAdvertBannerId", generalFunc.getJsonValue("iAdvertBannerId", advertise_banner_data));
                    map.put("vImageWidth", generalFunc.getJsonValue("vImageWidth", advertise_banner_data));
                    map.put("vImageHeight", generalFunc.getJsonValue("vImageHeight", advertise_banner_data));
                    new OpenAdvertisementDialog(getActContext(), map, generalFunc);
                }
            }
        }
    }

    private void initialization() {
        selectedColor = ContextCompat.getColor(getActContext(), R.color.appThemeColor_1);
        deSelectedColor = ContextCompat.getColor(getActContext(), R.color.homedeSelectColor);

        if (generalFunc.getJsonValueStr("ENABLE_GOOGLE_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            binding.googleBanner.setVisibility(View.VISIBLE);
            MobileAds.initialize(getActContext());
            AdView mAdView = new AdView(getActContext());
            mAdView.setAdSize(com.google.android.gms.ads.AdSize.FULL_BANNER);
            mAdView.setAdUnitId(generalFunc.getJsonValueStr("GOOGLE_ADMOB_ID", obj_userProfile));
            binding.googleBanner.addView(mAdView);
            mAdView.loadAd(new AdRequest.Builder().build());
        } else {
            binding.googleBanner.setVisibility(View.GONE);
        }
        if (generalFunc.getJsonValueStr("ENABLE_FACEBOOK_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            binding.fbBanner.setVisibility(View.VISIBLE);
            binding.fbBanner.addView(new com.facebook.ads.AdView(this, "IMG_16_9_APP_INSTALL#" + generalFunc.getJsonValueStr("FACEBOOK_PLACEMENT_ID", obj_userProfile), AdSize.BANNER_HEIGHT_50));
        } else {
            binding.fbBanner.setVisibility(View.GONE);
        }

        binding.searchTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_BOOK_TXT"));
        binding.publishTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PUBLISH_TITLE_TXT"));
        binding.ridesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_RIDES_RIDE_SHARE_PRO"));
        binding.profileTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PROFILE_RIDE_SHARE_PRO"));

        addToClickHandler(binding.searchArea);
        addToClickHandler(binding.publishArea);
        addToClickHandler(binding.ridesArea);
        addToClickHandler(binding.profileArea);

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") &&
                !generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equals("")) {

            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
            bottomInfoDialog.setListener(() -> {
                generalFunc.storeData("isFirstTimeSmartLoginView", "Yes");
                generalFunc.isLocationPermissionGranted(true);
                if (rsSearchFragment != null && isSearchFrg) {
                    rsSearchFragment.initializeLocationCheckDone();
                }
            });
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_TXT"),
                    R.raw.biometric, generalFunc.retrieveLangLBl("", "LBL_OK"), true);
        }
    }

    private Context getActContext() {
        return RideSharingProHomeActivity.this;
    }

    private void manageView(boolean isHome) {
        binding.googleBanner.setVisibility(isHome ? View.VISIBLE : View.GONE);
        binding.fbBanner.setVisibility(isHome ? View.VISIBLE : View.GONE);
        if (isHome) {
            getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_Full_Light));
        } else {
            getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
        }
    }

    private void openSearchFragment() {
        isSearchFrg = true;
        manageView(true);
        if (rsSearchFragment == null) {
            rsSearchFragment = new RideSharingSearchFragment();
        }
        openPageFrag(1, rsSearchFragment);
    }

    private void openPublishFragment() {
        isPublishFrg = true;
        if (bottomBtnPos == 2) {
            return;
        }
        manageView(false);
        if (rsPublishFragment == null) {
            rsPublishFragment = new RideSharingPublishFragment();
        }
        openPageFrag(2, rsPublishFragment);
    }

    public void setReturnRideFrag(ArrayList<RideProPublishData.MultiStopData> multiStopData) {
        bottomBtnPos = 0;
        publishFrgView = null;
        rsPublishFragment = null;
        binding.publishArea.performClick();
        if (rsPublishFragment != null) {
            rsPublishFragment.setReturnRideFrag(multiStopData);
        }
    }

    public void setFrag(int pos) {
        binding.ridesArea.performClick();
        if (rsRidesFragment != null) {
            rsRidesFragment.setFrag(pos);
        }
    }

    private void openRidesFragment() {
        isRidesFrg = true;
        if (bottomBtnPos == 3) {
            return;
        }
        manageView(false);
        rsRidesFragment = new RideSharingRidesFragment();
        openPageFrag(3, rsRidesFragment);
    }

    private void openProfileFragment() {
        isProfileFrg = true;
        manageView(false);
        if (rsProfileFragment == null) {
            rsProfileFragment = new MyProfileFragment();
        }
        openPageFrag(4, rsProfileFragment);
    }

    private void openPageFrag(int position, Fragment fragToOpen) {
        int leftAnim = bottomBtnPos > position ? R.anim.slide_from_left : R.anim.slide_from_right;
        int rightAnim = bottomBtnPos > position ? R.anim.slide_to_right : R.anim.slide_to_left;

        try {
            getSupportFragmentManager().beginTransaction().setCustomAnimations(leftAnim, rightAnim).replace(R.id.fragContainer, fragToOpen).commit();
        } catch (Exception e) {
            Logger.e("ExceptionFrag", "::" + e.getMessage());
        }
        bottomBtnPos = position;
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        super.onFileSelected(mFileUri, mFilePath, mFileType);
        if (rsPublishFragment != null && isPublishFrg) {
            rsPublishFragment.onFileSelected(mFileUri, mFilePath, mFileType);
        }
    }

    public void handleImgUploadResponse(String responseString) {
        if (rsPublishFragment != null && isPublishFrg) {
            rsPublishFragment.handleImgUploadResponse(responseString);
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (rsSearchFragment != null && isSearchFrg) {
            rsSearchFragment.onActivityResult(requestCode, resultCode, data);
        } else if (rsPublishFragment != null && isPublishFrg) {
            rsPublishFragment.onActivityResult(requestCode, resultCode, data);
        } else if (rsRidesFragment != null && isRidesFrg) {
            rsRidesFragment.onActivityResult(requestCode, resultCode, data);
        } else if (rsProfileFragment != null && isProfileFrg) {
            rsProfileFragment.onActivityResult(requestCode, resultCode, data);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull @NotNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (rsSearchFragment != null && isSearchFrg) {
            rsSearchFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (rsPublishFragment != null && isPublishFrg) {
            rsPublishFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (rsRidesFragment != null && isRidesFrg) {
            rsRidesFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (rsProfileFragment != null && isProfileFrg) {
            rsProfileFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        }
    }

    private void manageBottomMenu(MTextView selTextView) {
        if (selTextView.getId() == binding.searchTxt.getId()) {
            binding.searchTxt.setTextColor(selectedColor);
            binding.searchIcon.setColorFilter(selectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.searchIcon.setImageResource(R.drawable.ic_search_black_24dp);
        } else {
            binding.searchTxt.setTextColor(deSelectedColor);
            binding.searchIcon.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.searchIcon.setImageResource(R.drawable.ic_search_black_24dp);
        }

        if (selTextView.getId() == binding.publishTxt.getId()) {
            binding.publishTxt.setTextColor(selectedColor);
            binding.publishIcon.setColorFilter(selectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.publishIcon.setImageResource(R.drawable.ic_add_circle);
        } else {
            binding.publishTxt.setTextColor(deSelectedColor);
            binding.publishIcon.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.publishIcon.setImageResource(R.drawable.ic_add_circle);
        }

        if (selTextView.getId() == binding.ridesTxt.getId()) {
            binding.ridesTxt.setTextColor(selectedColor);
            binding.ridesIcon.setColorFilter(selectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.ridesIcon.setImageResource(R.drawable.ic_your_ride);
        } else {
            binding.ridesTxt.setTextColor(deSelectedColor);
            binding.ridesIcon.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.ridesIcon.setImageResource(R.drawable.ic_your_ride);
        }

        if (selTextView.getId() == binding.profileTxt.getId()) {
            binding.profileTxt.setTextColor(selectedColor);
            binding.profileIcon.setColorFilter(selectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.profileIcon.setImageResource(R.drawable.ic_profile_23);
        } else {
            binding.profileTxt.setTextColor(deSelectedColor);
            binding.profileIcon.setColorFilter(deSelectedColor, android.graphics.PorterDuff.Mode.SRC_IN);
            binding.profileIcon.setImageResource(R.drawable.ic_profile_23);
        }
    }

    public void onClick(View view) {

        isSearchFrg = false;
        isPublishFrg = false;
        isRidesFrg = false;
        isProfileFrg = false;

        int i = view.getId();
        if (i == binding.searchArea.getId()) {
            manageBottomMenu(binding.searchTxt);
            openSearchFragment();

        } else if (i == binding.publishArea.getId()) {
            manageBottomMenu(binding.publishTxt);
            openPublishFragment();

        } else if (i == binding.ridesArea.getId()) {
            manageBottomMenu(binding.ridesTxt);
            openRidesFragment();

        } else if (i == binding.profileArea.getId()) {
            manageBottomMenu(binding.profileTxt);
            openProfileFragment();
        }
    }

    @Override
    public void onBackPressed() {
        if (!ServiceModule.OnlyRideSharingPro) {
            if (getIntent().getStringExtra("eCatType").equalsIgnoreCase("RIDESHAREBOOK")) {
                super.onBackPressed();
                return;
            }
        }
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PUBLISHED_RIDE_EXIT_TXT"), generalFunc.retrieveLangLBl("", "LBL_NO"),
                generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                    if (buttonId == 1) {
                        super.onBackPressed();
                    }
                });
    }

}