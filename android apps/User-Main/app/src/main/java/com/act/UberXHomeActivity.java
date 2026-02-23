package com.act;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.act.homescreen23.HomeDynamic_23_Fragment;
import com.act.homescreen23.ServicesFragment;
import com.act.homescreen24.HomeDynamic_24_Fragment;
import com.act.rentItem.fragment.RentItemListFragment;
import com.activity.ParentActivity;
import com.dialogs.BottomInfoDialog;
import com.facebook.ads.AdSize;
import com.fragments.HomeDaynamicFragment;
import com.fragments.HomeDaynamic_22_Fragment;
import com.fragments.HomeFragment;
import com.fragments.MyBookingFragment;
import com.fragments.MyProfileFragment;
import com.fragments.MyWalletFragment;
import com.general.PermissionHandlers;
import com.general.files.ActUtils;
import com.general.files.AddBottomBarNew;
import com.general.files.MyApp;
import com.general.files.OpenAdvertisementDialog;
import com.google.android.gms.ads.AdRequest;
import com.google.android.gms.ads.AdView;
import com.google.android.gms.ads.MobileAds;
import com.huawei.hms.adapter.AvailableAdapter;
import com.huawei.hms.adapter.internal.AvailableCode;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityUberXhome2Binding;
import com.model.ChatMsgHandler;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONObject;

import java.util.HashMap;

public class UberXHomeActivity extends ParentActivity {

    public ActivityUberXhome2Binding binding;
    public HomeFragment homeFragment;
    public HomeDaynamicFragment homeDaynamicFragment;
    public HomeDaynamic_22_Fragment homeDaynamic_22_fragment;
    public HomeDynamic_23_Fragment homeDynamic_23_fragment;
    public HomeDynamic_24_Fragment homeDynamic_24_fragment;

    public MyBookingFragment myBookingFragment;
    private ServicesFragment myServicesFragment;
    private MyWalletFragment myWalletFragment;
    private MyProfileFragment myProfileFragment;
    public RentItemListFragment myRentItemListFragment;

    public String mSelectedAddress, mSelectedLatitude = "", mSelectedLongitude = "";
    public int bottomBtnPos = 1;
    public String ENABLE_LOCATION_WISE_BANNER = "No";

    public boolean isHomeFrg = true, isRentItemListFrg = false;
    private boolean isWalletFrg = false, isProfileFrg = false, isBookingFrg = false, isServicesFrg = false;
    private boolean isNewHome, isNewHome_22, isNewHome_23, isNewHome_24;
    public AddBottomBarNew addBottomBarNew;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_uber_xhome2);

        if (generalFunc.getJsonValueStr("ENABLE_FACEBOOK_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            facebookAdds();
        }
        if (generalFunc.getJsonValueStr("ENABLE_GOOGLE_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            googleAdds();
        }
        ENABLE_LOCATION_WISE_BANNER = generalFunc.getJsonValueStr("ENABLE_LOCATION_WISE_BANNER", obj_userProfile);

        isNewHome = generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP", obj_userProfile).equalsIgnoreCase("Yes");

        isNewHome_22 = generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", obj_userProfile).equalsIgnoreCase("Yes");

        isNewHome_23 = generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equalsIgnoreCase("Yes");

        isNewHome_24 = generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equalsIgnoreCase("Yes");

        addBottomBarNew = new AddBottomBarNew(getActContext(), generalFunc, binding.rduTopArea, isNewHome_23, isNewHome_24);

        if (PermissionHandlers.getInstance().getVisibilityPager()) {
            return;
        }

        if (MyApp.getInstance().isHMSOnly()) {
            AvailableAdapter availableAdapter = new AvailableAdapter(60400312);
            int result = availableAdapter.isHuaweiMobileServicesAvailable(this);
            if (result != AvailableCode.SUCCESS) {
                availableAdapter.startResolution(this, result1 -> {
                    Logger.e("HMSResult", "onComplete before result: " + result);
                    Logger.e("HMSResult", "onComplete result: " + result1);
                });
            }
        }

        String advertise_banner_data = generalFunc.getJsonValueStr("advertise_banner_data", obj_userProfile);
        if (!MyApp.getInstance().isGetDetailCall && advertise_banner_data != null && !advertise_banner_data.equalsIgnoreCase("")) {

            String image_url = generalFunc.getJsonValue("image_url", advertise_banner_data);
            if (image_url != null && !image_url.equalsIgnoreCase("")) {
                HashMap<String, String> map = new HashMap<>();
                map.put("image_url", image_url);
                map.put("tRedirectUrl", generalFunc.getJsonValue("tRedirectUrl", advertise_banner_data));
                map.put("iAdvertBannerId", generalFunc.getJsonValue("iAdvertBannerId", advertise_banner_data));
                map.put("vImageWidth", generalFunc.getJsonValue("vImageWidth", advertise_banner_data));
                map.put("vImageHeight", generalFunc.getJsonValue("vImageHeight", advertise_banner_data));
                new OpenAdvertisementDialog(getActContext(), map, generalFunc);
            }
        }
        MyApp.getInstance().showOutsatandingdilaog(binding.rduTopArea);

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") &&
                !generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes") && !generalFunc.getMemberId().equals("")) {

            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
            bottomInfoDialog.setListener(() -> {
                generalFunc.storeData("isFirstTimeSmartLoginView", "Yes");
                manageHomeFrag();
            });
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_TXT"),
                    R.raw.biometric, generalFunc.retrieveLangLBl("", "LBL_OK"), true);
        }

        String OPEN_CHAT = generalFunc.retrieveValue(ChatMsgHandler.OPEN_CHAT);
        if (Utils.checkText(OPEN_CHAT)) {
            JSONObject OPEN_CHAT_DATA_OBJ = generalFunc.getJsonObject(OPEN_CHAT);
            if (OPEN_CHAT_DATA_OBJ != null) {
                ChatMsgHandler.performAction(OPEN_CHAT_DATA_OBJ.toString());
            }
        }

    }

    @Override
    protected void onRestoreInstanceState(@NonNull Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);

        MyApp.getInstance().refreshView(this, "");
    }

    private void manageHomeFrag() {
        generalFunc.isLocationPermissionGranted(true);
        if (homeFragment != null && isHomeFrg) {
            homeFragment.initializeLocationCheckDone();
        } else if (homeDaynamicFragment != null && isHomeFrg) {
            homeDaynamicFragment.initializeLocationCheckDone();
        } else if (homeDaynamic_22_fragment != null && isHomeFrg) {
            homeDaynamic_22_fragment.initializeLocationCheckDone();
        } else if (homeDynamic_23_fragment != null && isHomeFrg) {
            homeDynamic_23_fragment.initializeLocationCheckDone();
        } else if (homeDynamic_24_fragment != null && isHomeFrg) {
            homeDynamic_24_fragment.initializeLocationCheckDone();
        }
    }

    public void checkBiddingView(int pos) {
        if (isBookingFrg) {
            myBookingFragment.setFrag(pos);
        } else {
            Bundle bn = new Bundle();
            bn.putInt("viewPos", pos);
            new ActUtils(getActContext()).startActWithData(BookingActivity.class, bn);
        }
    }

    private void googleAdds() {
        AdView mAdView;
        binding.bannerGoogle.setVisibility(View.VISIBLE);
        //manage Google ads
        MobileAds.initialize(getActContext());
        mAdView = new AdView(getActContext());
        mAdView.setAdSize(com.google.android.gms.ads.AdSize.FULL_BANNER);
        mAdView.setAdUnitId(generalFunc.getJsonValueStr("GOOGLE_ADMOB_ID", obj_userProfile));
        AdRequest adRequest = new AdRequest.Builder().build();
        binding.bannerGoogle.addView(mAdView);
        mAdView.loadAd(adRequest);
    }

    private void facebookAdds() {
        binding.bannerFB.setVisibility(View.VISIBLE);
        com.facebook.ads.AdView adView;
        adView = new com.facebook.ads.AdView(this, "IMG_16_9_APP_INSTALL#" + generalFunc.getJsonValueStr("FACEBOOK_PLACEMENT_ID", obj_userProfile), AdSize.BANNER_HEIGHT_50);
        // Add the ad view to your activity layout
        binding.bannerFB.addView(adView);
        // Request an ad
        adView.loadAd();
    }

    private void manageView(boolean isHome) {
        binding.bannerGoogle.setVisibility(isHome ? View.VISIBLE : View.GONE);
        binding.bannerFB.setVisibility(isHome ? View.VISIBLE : View.GONE);
        getWindow().setStatusBarColor(ContextCompat.getColor(this, R.color.appThemeColor_1));
        if (isHome) {
            if (isNewHome_24 && homeDynamic_24_fragment != null && homeDynamic_24_fragment.mUFXServices23ProView != null) {
                homeDynamic_24_fragment.mUFXServices23ProView = null;
            } else if (isNewHome_23 && homeDynamic_23_fragment != null && homeDynamic_23_fragment.mUFXServices23ProView != null) {
                homeDynamic_23_fragment.mUFXServices23ProView = null;
            }
        } else {
            PermissionHandlers.getInstance().setVisibility(View.GONE);
        }
    }

    public void pubNubMsgArrived(String message) {

        String driverMsg = generalFunc.getJsonValue("Message", message);
        String eType = generalFunc.getJsonValue("eType", message);

        if (driverMsg.equals("CabRequestAccepted")) {
            String eSystem = generalFunc.getJsonValueStr("eSystem", obj_userProfile);
            if (eSystem != null && eSystem.equalsIgnoreCase("DeliverAll")) {
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message));
                return;
            }

            if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                return;
            } else if (app_type != null && app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX)) {

                if (!eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                    MyApp.getInstance().restartWithGetDataApp();
                    return;
                }
            }

            if (generalFunc.isJSONkeyAvail("iCabBookingId", message) && !generalFunc.getJsonValue("iCabBookingId", message).trim().equals("")) {
                MyApp.getInstance().restartWithGetDataApp();
            } else {
                if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX) || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                    return;
                } else {
                    MyApp.getInstance().restartWithGetDataApp();
                }
            }
        }
    }

    private void openHomeFragment() {
        isHomeFrg = true;
        PermissionHandlers.getInstance().initiatePermissionHandler();
        if (PermissionHandlers.getInstance().getVisibilityPager()) {
            return;
        }
        manageView(true);

        if (isNewHome_24) {
            if (homeDynamic_24_fragment == null) {
                homeDynamic_24_fragment = new HomeDynamic_24_Fragment();
            }
            openPageFrag(1, homeDynamic_24_fragment);

        } else if (isNewHome_23) {
            if (homeDynamic_23_fragment == null) {
                homeDynamic_23_fragment = new HomeDynamic_23_Fragment();
            }
            openPageFrag(1, homeDynamic_23_fragment);

        } else if (isNewHome_22) {
            if (homeDaynamic_22_fragment == null) {
                homeDaynamic_22_fragment = new HomeDaynamic_22_Fragment();
            }
            openPageFrag(1, homeDaynamic_22_fragment);

        } else if (isNewHome) {
            if (homeDaynamicFragment == null) {
                homeDaynamicFragment = new HomeDaynamicFragment();
            }
            openPageFrag(1, homeDaynamicFragment);

        } else {

            if (homeFragment == null) {
                homeFragment = new HomeFragment();
            }
            openPageFrag(1, homeFragment);
            bottomBtnPos = 1;
        }
    }

    @Override
    protected void onResume() {
        super.onResume();

        if (myProfileFragment != null && isProfileFrg) {
            myProfileFragment.onResume();
        }

        if (myWalletFragment != null && isWalletFrg) {
            myWalletFragment.onResume();
        }

        if (myBookingFragment != null && isBookingFrg) {
            myBookingFragment.onResume();
        }

        if (myServicesFragment != null && isServicesFrg) {
            myServicesFragment.onResume();
        }
        if (myRentItemListFragment != null && isRentItemListFrg) {
            myRentItemListFragment.onResume();
        }
    }

    public void setBottomFrg(int pos) {
        isHomeFrg = false;
        isBookingFrg = false;
        isServicesFrg = false;
        isRentItemListFrg = false;
        isWalletFrg = false;
        isProfileFrg = false;

        if (pos == 0) {
            if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                MyApp.getInstance().restartWithGetDataApp();
            } else {
                openHomeFragment();
            }
        } else if (pos == 1) {
            if (ServiceModule.isOnlyBuySellRentEnable()) {
                openRentItemListFragment();
            } else if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                openServicesFragment();
            } else {
                openHistoryFragment();
            }
        } else if (pos == 2) {
            if (ServiceModule.isRideOnly() || ServiceModule.RideDeliveryProduct || ServiceModule.isCubeXApp) {
                openHistoryFragment();
            } else {
                openWalletFragment();
            }
        } else if (pos == 3) {
            openProfileFragment();
        }
    }

    private void openHistoryFragment() {
        isBookingFrg = true;
        manageView(false);
        if (generalFunc.getMemberId().equals("")) {
            openProfileFragment();
            return;
        }

        if (myBookingFragment == null) {
            myBookingFragment = new MyBookingFragment();
        }
        openPageFrag(2, myBookingFragment);
        bottomBtnPos = 2;
    }

    private void openRentItemListFragment() {
        isRentItemListFrg = true;
        manageView(false);
        if (generalFunc.getMemberId().equals("")) {
            openProfileFragment();
            return;
        }

        if (myRentItemListFragment == null) {
            myRentItemListFragment = new RentItemListFragment();
        }
        openPageFrag(2, myRentItemListFragment);
        bottomBtnPos = 2;
    }

    private void openServicesFragment() {
        isServicesFrg = true;
        manageView(false);
        if (generalFunc.getMemberId().equals("")) {
            openProfileFragment();
            return;
        }

        if (myServicesFragment == null) {
            myServicesFragment = new ServicesFragment();
        }
        openPageFrag(2, myServicesFragment);
        bottomBtnPos = 2;
    }

    private void openProfileFragment() {
        isProfileFrg = true;
        manageView(false);
        if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
        }
        openPageFrag(4, myProfileFragment);
        bottomBtnPos = 4;
    }

    private void openWalletFragment() {
        isWalletFrg = true;
        manageView(false);

        if (generalFunc.getMemberId().equals("")) {
            openProfileFragment();
            return;

        }
        if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
        }
        openPageFrag(3, myWalletFragment);
        bottomBtnPos = 3;
    }

    @Override
    public void onBottomTabSelected(int position) {
        setBottomFrg(position);
    }

    private void openPageFrag(int position, Fragment fragToOpen) {
        if (MyApp.isAppKilled()) {
            return;
        }
        int leftAnim = bottomBtnPos > position ? R.anim.slide_from_left : R.anim.slide_from_right;
        int rightAnim = bottomBtnPos > position ? R.anim.slide_to_right : R.anim.slide_to_left;

        try {
            getSupportFragmentManager().beginTransaction().setCustomAnimations(leftAnim, rightAnim).replace(R.id.fragContainer, fragToOpen).commit();
        } catch (Exception e) {
            Logger.e("ExceptionFrag", "::" + e.getMessage());
        }
    }

    @Override
    public void onBackPressed() {

        if (isNewHome_24) {
            if (homeDynamic_24_fragment != null) {
                if (homeDynamic_24_fragment.CAT_TYPE_MODE.equals("1") && generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")) {
                    homeDynamic_24_fragment.setMainCategory();
                    return;
                }
            }

        } else if (isNewHome_23) {
            if (homeDynamic_23_fragment != null) {
                if (homeDynamic_23_fragment.CAT_TYPE_MODE.equals("1") && generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")) {
                    homeDynamic_23_fragment.setMainCategory();
                    return;
                }
            }

        } else if (isNewHome_22) {
            if (homeDaynamic_22_fragment != null) {
                if (homeDaynamic_22_fragment.isLoading) {
                    return;
                }
                if (homeDaynamic_22_fragment.CAT_TYPE_MODE.equals("1") && generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")) {
                    homeDaynamic_22_fragment.multiServiceSelect.clear();
                    homeDaynamic_22_fragment.manageToolBarAddressView(false);
                    binding.rduTopArea.setVisibility(View.VISIBLE);
                    homeDaynamic_22_fragment.configCategoryView();
                    return;
                }
            }

        } else if (isNewHome) {

            if (homeDaynamicFragment != null) {
                if (homeDaynamicFragment.isLoading) {
                    return;
                }
                if (homeDaynamicFragment.CAT_TYPE_MODE.equals("1") && generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")) {
                    homeDaynamicFragment.multiServiceSelect.clear();
                    homeDaynamicFragment.backImgView.setVisibility(View.GONE);
                    homeDaynamicFragment.manageToolBarAddressView(false);
                    binding.rduTopArea.setVisibility(View.VISIBLE);
                    homeDaynamicFragment.configCategoryView();
                    return;
                }
            }
        } else {
            if (homeFragment != null) {
                if (homeFragment.isLoading) {
                    return;
                }
                if (homeFragment.CAT_TYPE_MODE.equals("1") && generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")) {
                    homeFragment.multiServiceSelect.clear();
                    homeFragment.backImgView.setVisibility(View.GONE);
                    homeFragment.manageToolBarAddressView(false);
                    binding.rduTopArea.setVisibility(View.VISIBLE);
                    homeFragment.MainLayout.setBackgroundColor(Color.parseColor("#FFFFFF"));
                    homeFragment.MainTopArea.setBackgroundColor(Color.parseColor("#FFFFFF"));
                    homeFragment.bannerArea.setBackgroundColor(Color.parseColor("#FFFFFF"));
                    homeFragment.selectServiceTxt.setBackgroundColor(Color.parseColor("#FFFFFF"));

                    homeFragment.configCategoryView();
                    return;
                }
            }
        }
        super.onBackPressed();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (homeDynamic_24_fragment != null && isHomeFrg) {
            homeDynamic_24_fragment.onActivityResult(requestCode, resultCode, data);
        } else if (homeDynamic_23_fragment != null && isHomeFrg) {
            homeDynamic_23_fragment.onActivityResult(requestCode, resultCode, data);
        } else if (homeFragment != null && isHomeFrg) {
            homeFragment.onActivityResult(requestCode, resultCode, data);
        } else if (homeDaynamic_22_fragment != null && isHomeFrg) {
            homeDaynamic_22_fragment.onActivityResult(requestCode, resultCode, data);
        } else if (homeDaynamicFragment != null && isHomeFrg) {
            homeDaynamicFragment.onActivityResult(requestCode, resultCode, data);
        } else if (myWalletFragment != null && isWalletFrg) {
            myWalletFragment.onActivityResult(requestCode, resultCode, data);
        } else if (myProfileFragment != null && isProfileFrg) {
            myProfileFragment.onActivityResult(requestCode, resultCode, data);
        } else if (myBookingFragment != null && isBookingFrg) {
            myBookingFragment.onActivityResult(requestCode, resultCode, data);
        } else if (myServicesFragment != null && isServicesFrg) {
            myServicesFragment.onActivityResult(requestCode, resultCode, data);
        } else if (myRentItemListFragment != null && isRentItemListFrg) {
            myRentItemListFragment.onActivityResult(requestCode, resultCode, data);
        }
    }

    private Context getActContext() {
        return UberXHomeActivity.this;
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (homeFragment != null && isHomeFrg) {
            homeFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (homeDaynamicFragment != null && isHomeFrg) {
            homeDaynamicFragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (homeDaynamic_22_fragment != null && isHomeFrg) {
            homeDaynamic_22_fragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (homeDynamic_23_fragment != null && isHomeFrg) {
            homeDynamic_23_fragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        } else if (homeDynamic_24_fragment != null && isHomeFrg) {
            homeDynamic_24_fragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
        }
    }

    public void serviceAreaClickHandle() {
        if (homeDynamic_24_fragment != null && (homeDynamic_24_fragment.binding.headerAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("", "")) || homeDynamic_24_fragment.binding.headerAddressTxt.getText().toString().equalsIgnoreCase(homeDynamic_24_fragment.LBL_LOCATING_YOU_TXT))) {
            homeDynamic_24_fragment.openSourceLocationView();
        } else if (homeDynamic_23_fragment != null && (homeDynamic_23_fragment.binding.headerAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("", "")) || homeDynamic_23_fragment.binding.headerAddressTxt.getText().toString().equalsIgnoreCase(homeDynamic_23_fragment.LBL_LOCATING_YOU_TXT))) {
            homeDynamic_23_fragment.openSourceLocationView();
        }
    }
}