package com.general.files;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.graphics.Color;
import android.location.Location;
import android.os.Bundle;
import android.provider.Settings;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.core.app.ActivityCompat;
import androidx.core.view.GravityCompat;
import androidx.drawerlayout.widget.DrawerLayout;

import com.act.AddAddressActivity;
import com.act.MainActivity;
import com.act.SearchLocationActivity;
import com.act.SearchPickupLocationActivity;
import com.act.UberXActivity;
import com.act.UberXHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity24;
import com.act.deliverAll.ServiceHomeActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.dialogs.BottomInfoDialog;
import com.fragments.DriverDetailFragment;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

public class OpenNoLocationView implements GetLocationUpdates.LocationUpdates {

    ViewGroup viewGroup;
    Activity currentAct;

    GetLocationUpdates getLocUpdate;

    View noLocView;
    GeneralFunctions generalFunc;
    private static OpenNoLocationView currentInst;
    private boolean isViewExecutionLocked = false;
    public JSONObject obj_userProfile;
    public String userProfileJson = "";
    private String mType = "";

    public static OpenNoLocationView getInstance(Activity currentAct, ViewGroup viewGroup) {
        if (currentInst == null) {
            currentInst = new OpenNoLocationView();
        }

        currentInst.viewGroup = viewGroup;
        currentInst.currentAct = currentAct;

        return currentInst;
    }

    private void getUserProfileJson() {
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        //checkGpsView();
    }

    public void configView(boolean isFromNetwork) {
        if (viewGroup != null && currentAct != null) {

            if (isViewExecutionLocked) {
                return;
            }

            isViewExecutionLocked = true;

            generalFunc = MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct());

            getUserProfileJson();
            closeView(generalFunc);

            boolean isNetworkConnected = new InternetConnection(currentAct).isNetworkConnected();
            LayoutInflater inflater = (LayoutInflater) currentAct.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @SuppressLint("InflateParams") View noLocView = inflater.inflate(R.layout.desgin_no_locatin_view, null);

            ImageView noLocMenuImgView = noLocView.findViewById(R.id.noLocMenuImgView);
            ImageView closeImageView = noLocView.findViewById(R.id.closeImageView);
            ImageView noLocImgView = noLocView.findViewById(R.id.noLocImgView);

            MTextView noLocTitleTxt = noLocView.findViewById(R.id.noLocTitleTxt);
            MTextView noLocMsgTxt = noLocView.findViewById(R.id.noLocMsgTxt);
            MTextView txtHowItWorks = noLocView.findViewById(R.id.txtHowItWorks);
            txtHowItWorks.setVisibility(View.GONE);

            MButton settingsBtn = ((MaterialRippleLayout) noLocView.findViewById(R.id.settingsBtn)).getChildView();
            MButton enterLocBtn = ((MaterialRippleLayout) noLocView.findViewById(R.id.enterLocBtn)).getChildView();
            LinearLayout enterLocBtnlayout = noLocView.findViewById(R.id.enterLocBtnlayout);

            settingsBtn.setBackgroundColor(MyApp.getInstance().getCurrentAct().getResources().getColor(R.color.appThemeColor_1));
            settingsBtn.setTextColor(Color.parseColor("#ffffff"));

            enterLocBtn.setBackgroundColor(Color.parseColor("#ffffff"));
            enterLocBtn.setTextColor(Color.parseColor("#000000"));

            enterLocBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_PICK_UP_ADDRESS_1"));

            if (currentAct instanceof UberXActivity || (currentAct instanceof MainActivity && MyApp.getInstance().uberXAct != null)) {
                noLocMenuImgView.setVisibility(View.INVISIBLE);
                closeImageView.setVisibility(View.INVISIBLE);
                noLocView.setPadding(0, getActionBarHeight(), 0, 0);
            }

            if (!isNetworkConnected && currentAct instanceof MainActivity
                    && ((MainActivity) currentAct).requestNearestCab != null
                    && ((MainActivity) currentAct).requestNearestCab.dialogRequestNearestCab != null
                    && ((MainActivity) currentAct).requestNearestCab.dialogRequestNearestCab.isShowing()) {

                noLocMenuImgView.setVisibility(View.INVISIBLE);
                closeImageView.setVisibility(View.INVISIBLE);

                closeView(generalFunc);
                isViewExecutionLocked = false;
                return;
            }

            noLocMenuImgView.setOnClickListener(v -> {
                if (currentAct instanceof MainActivity && MyApp.getInstance().uberXAct == null) {
                    ((MainActivity) currentAct).openDrawer();
                }
            });
            settingsBtn.setOnClickListener(v -> {
                switch (mType) {
                    case "NO_INTERNET":
                        new ActUtils(MyApp.getInstance().getCurrentAct()).startActForResult(Settings.ACTION_SETTINGS, Utils.REQUEST_CODE_NETWOEK_ON);
                        break;
                    case "NO_LOCATION":
                        if (!ActivityCompat.shouldShowRequestPermissionRationale(currentAct, Manifest.permission.ACCESS_FINE_LOCATION)) {
                            generalFunc.openSettings();
                        } else {
                            generalFunc.isLocationPermissionGranted(true);
                        }
                        break;
                    case "NO_GPS":
                        new ActUtils(MyApp.getInstance().getCurrentAct()).startActForResult(Settings.ACTION_LOCATION_SOURCE_SETTINGS, Utils.REQUEST_CODE_GPS_ON);
                        break;
                }
            });

            enterLocBtn.setOnClickListener(v -> {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "source");
                bn.putDouble("lat", 0.0);
                bn.putDouble("long", 0.0);
                if (currentAct instanceof UberXHomeActivity || currentAct instanceof FoodDeliveryHomeActivity || currentAct instanceof FoodDeliveryHomeActivity24 || currentAct instanceof AddAddressActivity) {
                    new ActUtils(currentAct).startActForResult(SearchLocationActivity.class, bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);
                } else if (currentAct instanceof SearchPickupLocationActivity) {
                    new ActUtils(currentAct).startActForResult(SearchLocationActivity.class, bn, Utils.PLACE_CUSTOME_LOC_REQUEST_CODE);
                } else {
                    new ActUtils(currentAct).startActForResult(SearchLocationActivity.class, bn, Utils.SEARCH_PICKUP_LOC_REQ_CODE);
                }
                /*closeView(generalFunc);
                isViewExecutionLocked = false;*/
            });

            if (!isNetworkConnected) {

                currentInst.noLocView = noLocView;

                enterLocBtn.setVisibility(View.INVISIBLE);
                enterLocBtnlayout.setVisibility(View.INVISIBLE);

                noLocImgView.setImageResource(R.drawable.ic_wifi_off);

                settingsBtn.setText(generalFunc.retrieveLangLBl("", "LBL_SETTINGS"));
                noLocTitleTxt.setText(generalFunc.retrieveLangLBl("Internet Connection", "LBL_NO_INTERNET_TITLE"));
                noLocMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_SUB_TITLE"));

                addView(noLocView, "NO_INTERNET", generalFunc);

                isViewExecutionLocked = false;
                return;
            }

            if (currentAct instanceof MainActivity mainActivity) {
                DriverDetailFragment driverDetailFrag = mainActivity.getDriverDetailFragment();
                if (driverDetailFrag != null) {
                    enterLocBtn.setVisibility(View.INVISIBLE);
                    enterLocBtnlayout.setVisibility(View.INVISIBLE);
                } else if (currentAct.getIntent().getBooleanExtra("fromMulti", true)) {
                    enterLocBtn.setVisibility(View.INVISIBLE);
                    enterLocBtnlayout.setVisibility(View.INVISIBLE);

                    if (mainActivity.selAddresArea.getVisibility() == View.VISIBLE ||
                            (mainActivity.mainHeaderFrag != null && mainActivity.mainHeaderFrag.view != null && mainActivity.mainHeaderFrag.view.findViewById(R.id.headerArea) != null && mainActivity.mainHeaderFrag.view.findViewById(R.id.headerArea).getVisibility() == View.VISIBLE)) {

                        if ((ServiceModule.isRideOnly() || ServiceModule.isDeliveronly()) && mainActivity.isFirstTimeIsRideOnly) {
                            if (!generalFunc.isLocationPermissionGranted(false)) {
                                generalFunc.isLocationPermissionGranted(true);
                                mainActivity.isFirstTimeIsRideOnly = false;
                                isViewExecutionLocked = false;
                                return;
                            }
                        }
                    }
                }
            }

            if (!generalFunc.isLocationPermissionGranted(false)) {

                if (myCloseView()) {
                    return;
                }

                noLocImgView.setImageResource(R.drawable.ic_permission_denied);

                txtHowItWorks.setText(generalFunc.retrieveLangLBl("", "LBL_HOW_IT_WORKS_TXT"));
                txtHowItWorks.setVisibility(View.VISIBLE);
                txtHowItWorks.setOnClickListener(v -> {
                    BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(currentAct, generalFunc);
                    bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_ALLOW_LOC_PERMISSION_TXT"), generalFunc.retrieveLangLBl("", "LBL_LOC_PERMISSION_NOTE_USER_TXT"), R.drawable.ic_permission_location_1, generalFunc.retrieveLangLBl("", "LBL_OK"));
                });

                settingsBtn.setText(generalFunc.retrieveLangLBl("", "LBL_ALLOW_LOC_PERMISSION_TXT"));
                noLocTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_PERMISSION_DENIED"));
                noLocMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_PERMISSION_DENIED_DETAILS_1"));

                addView(noLocView, "NO_LOCATION", generalFunc);

                isViewExecutionLocked = false;
                return;
            } else if (generalFunc.isLocationPermissionGranted(false)) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
            }

            if (!generalFunc.isLocationEnabled()) {

                if (myCloseView()) {
                    return;
                }

                settingsBtn.setText(generalFunc.retrieveLangLBl("", "LBL_TURN_ON_LOC_SERVICE"));
                noLocTitleTxt.setText(generalFunc.retrieveLangLBl("Enable Location Service", "LBL_LOCATION_SERVICES_TURNED_OFF"));
                noLocMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_SERVICES_TURNED_OFF_DETAILS"));

                addView(noLocView, "NO_GPS", generalFunc);

                isViewExecutionLocked = false;
                return;
            } else if (generalFunc.isLocationEnabled()) {
                if (myCloseView()) {
                    return;
                }

                if (getLocUpdate != null) {
                    getLocUpdate.stopLocationUpdates();
                    getLocUpdate = null;
                }
                if (currentAct instanceof UberXHomeActivity activity) {
                    if (activity.homeFragment != null) {
                        if (activity.homeFragment.CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                            activity.homeFragment.manageMainCatShimmer();
                        } else {
                            activity.homeFragment.manageMainSubCatShimmer();
                        }
                        activity.homeFragment.headerLocAddressTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
                    } else if (activity.homeDaynamicFragment != null) {
                        if (activity.homeDaynamicFragment.CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                            activity.homeDaynamicFragment.manageMainCatShimmer();
                        } else {
                            activity.homeDaynamicFragment.manageMainSubCatShimmer();
                        }
                        activity.homeDaynamicFragment.headerLocAddressHomeTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
                    } else if (activity.homeDaynamic_22_fragment != null) {
                        if (activity.homeDaynamic_22_fragment.CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                            activity.homeDaynamic_22_fragment.manageMainCatShimmer();
                        } else {
                            activity.homeDaynamic_22_fragment.manageMainSubCatShimmer();
                        }
                        activity.homeDaynamic_22_fragment.headerLocAddressHomeTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
                    } else if (activity.homeDynamic_23_fragment != null) {
                        activity.homeDynamic_23_fragment.binding.headerAddressTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
                    } else if (activity.homeDynamic_24_fragment != null) {
                        activity.homeDynamic_24_fragment.binding.headerAddressTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
                    }
                }

                getLocUpdate = new GetLocationUpdates(currentAct, Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);

            }

        } else {
            Logger.e("AssertError", "ViewGroup OR Activity cannot be null");
        }
        isViewExecutionLocked = false;
    }

    private boolean myCloseView() {
        if (currentAct instanceof RideSharingProHomeActivity) {
            if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }
        if (currentAct instanceof MainActivity activity) {
            if (activity.pickUpLocation != null && (activity.pickUpLocation.getLatitude() != 0.0/* && mainActivity.pickUpLocation.getLatitude() != 0.0 || mainActivity.isUfx*/)) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }
        if (currentAct instanceof UberXActivity activity) {
            if ((!activity.latitude.equalsIgnoreCase("0.0") || !activity.longitude.equalsIgnoreCase("0.0"))) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }

        if (currentAct instanceof UberXHomeActivity activity) {
            if (activity.homeFragment != null && activity.homeFragment.isUfxaddress) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
            if (activity.homeDaynamicFragment != null && activity.homeDaynamicFragment.isUfxaddress) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
            if (activity.homeDaynamic_22_fragment != null && activity.homeDaynamic_22_fragment.isUfxaddress) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
            if (activity.homeDynamic_23_fragment != null && activity.homeDynamic_23_fragment.isUfxAddress) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
            if (activity.homeDynamic_24_fragment != null && activity.homeDynamic_24_fragment.isUfxAddress) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }

        if (currentAct instanceof FoodDeliveryHomeActivity24 activity24) {
            if (activity24.mLatitude != null) {
                if ((!activity24.mLatitude.equalsIgnoreCase("0.0") || !activity24.mLongitude.equalsIgnoreCase("0.0"))) {
                    closeView(generalFunc);
                    isViewExecutionLocked = false;
                    return true;
                }
            }
        }

        if (currentAct instanceof FoodDeliveryHomeActivity activity) {
            if (activity.latitude != null) {
                if ((!activity.latitude.equalsIgnoreCase("0.0") || !activity.longitude.equalsIgnoreCase("0.0"))) {
                    closeView(generalFunc);
                    isViewExecutionLocked = false;
                    return true;
                }
            }
        }

        if (currentAct instanceof ServiceHomeActivity activity) {
            if ((!activity.latitude.equalsIgnoreCase("0.0") || !activity.longitude.equalsIgnoreCase("0.0"))) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }
        if (currentAct instanceof SearchPickupLocationActivity activity) {
            if (activity.isPlaceSelected) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }
        if (currentAct instanceof AddAddressActivity activity) {
            if (activity.isPlaceSelected) {
                closeView(generalFunc);
                isViewExecutionLocked = false;
                return true;
            }
        }
        return false;
    }

    private int getActionBarHeight() {
        int actionBarHeight = 0;
        TypedValue tv = new TypedValue();
        if (currentAct.getTheme().resolveAttribute(android.R.attr.actionBarSize, tv, true))
            actionBarHeight = TypedValue.complexToDimensionPixelSize(tv.data, currentAct.getResources().getDisplayMetrics());
        return actionBarHeight;
    }

    private void addView(View noLocView, String type, GeneralFunctions generalFunc) {

        mType = type;
        closeView(generalFunc);
        if (currentAct instanceof UberXHomeActivity activity) {
            if (activity.homeFragment != null
                    || activity.homeDaynamicFragment != null
                    || activity.homeDaynamic_22_fragment != null
                    || activity.homeDynamic_23_fragment != null
                    || activity.homeDynamic_24_fragment != null) {
                if (!activity.isHomeFrg) {
                    return;
                }
            }
        }
        currentInst.noLocView = noLocView;

        View rootView = generalFunc.getCurrentView(currentAct);

        if (viewGroup.getChildCount() > 0) {
            rootView = viewGroup.getChildAt(0);
        }

        if (rootView instanceof DrawerLayout) {
            RelativeLayout childView = null;

            for (int i = 0; i < ((DrawerLayout) rootView).getChildCount(); i++) {
                View tmp_childView = ((DrawerLayout) rootView).getChildAt(i);
                DrawerLayout.LayoutParams params = (DrawerLayout.LayoutParams) tmp_childView.getLayoutParams();
                if (params.gravity != GravityCompat.START && params.gravity != Gravity.START && tmp_childView instanceof RelativeLayout) {
                    childView = (RelativeLayout) tmp_childView;
                    break;
                }
            }

            if (childView != null) {
                noLocView.setLayoutParams(new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
//                childView.addView(noLocView);

                addNoLocationView(viewGroup, childView, noLocView);
                childView.bringChildToFront(noLocView);

            } else {
//                viewGroup.addView(noLocView);
                addNoLocationView(viewGroup, null, noLocView);
                viewGroup.bringChildToFront(noLocView);
            }
        } else {
            noLocView.setLayoutParams(new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
            addNoLocationView(viewGroup, null, noLocView);
            viewGroup.bringChildToFront(noLocView);
//            viewGroup.addView(noLocView);
        }
    }

    private void addNoLocationView(ViewGroup viewGroup, RelativeLayout childView, View noLocView) {
        if (childView != null) {
            childView.addView(noLocView);
        } else {
            viewGroup.addView(noLocView);
        }
    }

    public void closeView(GeneralFunctions generalFunc) {

        if (noLocView != null || currentAct.findViewById(R.id.noLocView) != null) {
            try {
                View rootView = generalFunc.getCurrentView(currentAct);

                if (viewGroup.getChildCount() > 0) {
                    rootView = viewGroup.getChildAt(0);
                }

                if (rootView instanceof DrawerLayout) {
                    RelativeLayout childView = null;
                    for (int i = 0; i < ((DrawerLayout) rootView).getChildCount(); i++) {
                        View tmp_childView = ((DrawerLayout) rootView).getChildAt(i);
                        DrawerLayout.LayoutParams params = (DrawerLayout.LayoutParams) tmp_childView.getLayoutParams();
                        if ((params.gravity != GravityCompat.START && params.gravity != Gravity.START) && tmp_childView instanceof RelativeLayout) {
                            childView = (RelativeLayout) tmp_childView;
                            break;
                        }
                    }

                    if (childView != null) {
                        childView.removeView(noLocView);
                    } else {
                        viewGroup.removeView(noLocView);
                    }
                } else {
                    viewGroup.removeView(noLocView);
                }

                noLocView = null;
                Logger.e("ViewRemove", ":Success:");
            } catch (Exception e) {
                Logger.e("ViewRemove", ":Exception:" + e.getMessage());
            }
        }
    }

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }
        if (getLocUpdate != null) {
            getLocUpdate.stopLocationUpdates();
            getLocUpdate = null;
        }
        if (currentAct instanceof UberXActivity) {
            ((UberXActivity) currentAct).onLocationUpdate(location);
        }
        if (currentAct instanceof MainActivity) {
            ((MainActivity) currentAct).onLocationUpdate(location);
        }

        if (currentAct instanceof UberXHomeActivity activity) {
            if (activity.homeFragment != null) {
                activity.homeFragment.onLocationUpdate(location);
            } else if (activity.homeDaynamicFragment != null) {
                activity.homeDaynamicFragment.onLocationUpdate(location);
            } else if (activity.homeDaynamic_22_fragment != null) {
                activity.homeDaynamic_22_fragment.onLocationUpdate(location);
            } else if (activity.homeDynamic_23_fragment != null) {
                activity.homeDynamic_23_fragment.onLocationUpdate(location);
            } else if (activity.homeDynamic_24_fragment != null) {
                activity.homeDynamic_24_fragment.onLocationUpdate(location);
            }
        }
    }
}