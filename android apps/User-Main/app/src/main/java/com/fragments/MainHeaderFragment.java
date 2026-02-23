package com.fragments;

import android.app.Activity;
import android.content.Intent;
import android.graphics.Color;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.drawerlayout.widget.DrawerLayout;

import com.act.MainActivity;
import com.act.SearchLocationActivity;
import com.act.UberxFilterActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.map.GeoMapLoader;
import com.map.minterface.OnCameraIdleListener;
import com.map.minterface.OnCameraMoveListener;
import com.map.models.LatLng;
import com.model.ServiceModule;
import com.model.Stop_Over_Points_Data;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class MainHeaderFragment extends BaseFragment implements GetAddressFromLocation.AddressFound, ViewTreeObserver.OnGlobalLayoutListener {

    public ImageView menuImgView, menuBtn, backImgView, backBtn;
    public MTextView pickUpLocTxt, sourceLocSelectTxt, destLocSelectTxt;
    public View area_source, area2, view;
    public ImageView uberXbackImgView, listImage, mapImage, filterImage;
    public MTextView mapTxt, listTxt, uberXTitleTxtView;
    public LinearLayout pickupLocArea1, uberXMainHeaderLayout, MainHeaderLayout, destarea, switchArea, UberxProviderswitchArea;
    public boolean isfirst = false, isAddressEnable, isDestinationMode = false;
    public GetAddressFromLocation getAddressFromLocation;
    MainActivity mainAct;
    GeneralFunctions generalFunc;
    GeoMapLoader.GeoMap geoMap;
    MTextView destLocTxt;
    String SERVICE_PROVIDER_FLOW = "", pickUpAddress = "", destAddress = "";
    MainHeaderFragment mainHeaderFrag;
    // String userProfileJson = "";
    MTextView pickUpLocHTxt, destLocHTxt;
    boolean isUfx = false;
    public boolean isclickabledest = false, isclickablesource = false;

    ImageView addPickUpImage, editPickupImage, addPickArea2Image, editPickArea2Image;
    ImageView addDestImageview, editDestImageview, addDestarea2Image, editDestarea2Image;

    /*Multi Delivery*/ LinearLayout destinationArea1;

    public int fragmentWidth = 0, fragmentHeight = 0;
    public boolean isRecent = false, isSchedule = false;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        if (view != null) {
            return view;
        }

        view = inflater.inflate(R.layout.fragment_main_header, container, false);
        menuImgView = (ImageView) view.findViewById(R.id.menuImgView);
        menuBtn = (ImageView) view.findViewById(R.id.menuBtn);
        backImgView = (ImageView) view.findViewById(R.id.backImgView);
        backBtn = (ImageView) view.findViewById(R.id.backBtn);
        pickUpLocTxt = (MTextView) view.findViewById(R.id.pickUpLocTxt);
        sourceLocSelectTxt = (MTextView) view.findViewById(R.id.sourceLocSelectTxt);
        destLocSelectTxt = (MTextView) view.findViewById(R.id.destLocSelectTxt);
        destLocTxt = (MTextView) view.findViewById(R.id.destLocTxt);
        pickUpLocHTxt = (MTextView) view.findViewById(R.id.pickUpLocHTxt);
        destLocHTxt = (MTextView) view.findViewById(R.id.destLocHTxt);
        pickupLocArea1 = (LinearLayout) view.findViewById(R.id.pickupLocArea1);
        destinationArea1 = (LinearLayout) view.findViewById(R.id.destinationArea1);
        addToClickHandler(pickupLocArea1);
        destarea = (LinearLayout) view.findViewById(R.id.destarea);
        addToClickHandler(destarea);

        addPickUpImage = (ImageView) view.findViewById(R.id.addPickUpImage);
        editPickupImage = (ImageView) view.findViewById(R.id.editPickupImage);
        addDestImageview = (ImageView) view.findViewById(R.id.addDestImageview);
        editDestImageview = (ImageView) view.findViewById(R.id.editDestImageview);
        addPickArea2Image = (ImageView) view.findViewById(R.id.addPickArea2Image);
        editPickArea2Image = (ImageView) view.findViewById(R.id.editPickArea2Image);
        addDestarea2Image = (ImageView) view.findViewById(R.id.addDestarea2Image);
        editDestarea2Image = (ImageView) view.findViewById(R.id.editDestarea2Image);
        uberXMainHeaderLayout = (LinearLayout) view.findViewById(R.id.uberXMainHeaderLayout);
        MainHeaderLayout = (LinearLayout) view.findViewById(R.id.MainHeaderLayout);
        switchArea = (LinearLayout) view.findViewById(R.id.switchArea);
        UberxProviderswitchArea = (LinearLayout) view.findViewById(R.id.UberxProviderswitchArea);
        mapTxt = (MTextView) view.findViewById(R.id.mapTxt);
        listTxt = (MTextView) view.findViewById(R.id.listTxt);
        listImage = (ImageView) view.findViewById(R.id.listImage);
        mapImage = (ImageView) view.findViewById(R.id.mapImage);
        filterImage = (ImageView) view.findViewById(R.id.filterImage);
        uberXTitleTxtView = (MTextView) view.findViewById(R.id.titleTxt);
        uberXbackImgView = (ImageView) view.findViewById(R.id.backImgViewuberx);


        area_source = view.findViewById(R.id.area_source);
        area2 = view.findViewById(R.id.area2);

        mainAct = (MainActivity) requireActivity();

        isUfx = getArguments().getBoolean("isUfx", false);
        Logger.d("isMultiDelivery", "::" + mainAct.isMultiDelivery());
        if (!mainAct.isMenuImageShow && !mainAct.isMultiDelivery()) {
            menuBtn.setVisibility(View.GONE);
            backBtn.setVisibility(View.VISIBLE);
            mainAct.mDrawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
        } else if (mainAct.isMenuImageShow && !isUfx) {
            menuBtn.setVisibility(View.VISIBLE);
            backBtn.setVisibility(View.GONE);
        } else {
            mainAct.mDrawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
        }

        generalFunc = mainAct.generalFunc;
        if (generalFunc != null && generalFunc.isRTLmode()) {
            uberXbackImgView.setRotation(180);
            backImgView.setRotation(180);
            backBtn.setRotation(180);
        }
        pickUpLocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICK_UP_FROM"));
        destLocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DROP_AT"));
        mapTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MAP_TXT"));
        listTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LIST_TXT"));

        uberXTitleTxtView.setText(generalFunc.retrieveLangLBl("Service Providers", "LBL_PROVIDER"));
        mainHeaderFrag = mainAct.getMainHeaderFrag();
        SERVICE_PROVIDER_FLOW = generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", mainAct.obj_userProfile);
        uberXTitleTxtView.setVisibility(View.VISIBLE);
        getAddressFromLocation = new GetAddressFromLocation(mainAct.getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);

        pickUpLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
        if (ServiceModule.isServiceProviderOnly()) {
            area_source.setVisibility(View.GONE);
            area2.setVisibility(View.GONE);
        }

        if (mainAct.isMultiDelivery()) {
            setDestinationViewEnableOrDisabled(false);
        }

        if (isUfx || ServiceModule.isServiceProviderOnly()) {
            area_source.setVisibility(View.GONE);
            area2.setVisibility(View.GONE);

        }

        addToClickHandler(menuImgView);
        addToClickHandler(menuBtn);
        addToClickHandler(backImgView);
        addToClickHandler(backBtn);
        addToClickHandler(listTxt);
        addToClickHandler(mapTxt);
        addToClickHandler(listImage);
        addToClickHandler(mapImage);
        addToClickHandler(filterImage);
        addToClickHandler(uberXbackImgView);
        addToClickHandler(sourceLocSelectTxt);
        addToClickHandler(destLocSelectTxt);

        destLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));
        destLocSelectTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));

        handlePickEditIcon();

        if (isUfx) {
            if (ServiceModule.ServiceProvider) {
                uberXMainHeaderLayout.setVisibility(View.VISIBLE);
                MainHeaderLayout.setVisibility(View.GONE);
                if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);

                    switchArea.setVisibility(View.GONE);
                } else {
                    switchArea.setVisibility(View.GONE);
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                }
                mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_List, false);
            } else if (ServiceModule.isServiceProviderOnly()) {
                uberXMainHeaderLayout.setVisibility(View.VISIBLE);
                MainHeaderLayout.setVisibility(View.GONE);
                if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                    switchArea.setVisibility(View.GONE);
                } else {
                    switchArea.setVisibility(View.GONE);
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                }
                mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_List, false);
            } else {
                uberXMainHeaderLayout.setVisibility(View.GONE);
                if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                } else {
                    switchArea.setVisibility(View.GONE);
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                }
            }
        } else {
            if (ServiceModule.isServiceProviderOnly()) {
                uberXMainHeaderLayout.setVisibility(View.VISIBLE);
                MainHeaderLayout.setVisibility(View.GONE);
                switchArea.setVisibility(View.GONE);
                UberxProviderswitchArea.setVisibility(View.VISIBLE);
                mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_List, false);
            } else {
                uberXMainHeaderLayout.setVisibility(View.GONE);
                if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                } else {
                    switchArea.setVisibility(View.GONE);
                    UberxProviderswitchArea.setVisibility(View.VISIBLE);
                }
            }
        }
        if (mainAct != null) {
            mainAct.addDrawer.setMenuImgClick(view, false);
        }

        int nowColor = getResources().getColor(R.color.pickup_req_now_btn);
        int laterColor = getResources().getColor(R.color.pickup_req_later_btn);
        int btnRadius = Utils.dipToPixels(mainAct, 25);
        int strokeColor = Color.parseColor("#00000000");

        new CreateRoundedView(nowColor, btnRadius, 0, strokeColor, view.findViewById(R.id.imgsourcearea1));
        new CreateRoundedView(laterColor, btnRadius, 0, strokeColor, view.findViewById(R.id.imagemarkerdest1));
        new CreateRoundedView(nowColor, btnRadius, 0, strokeColor, view.findViewById(R.id.imgsourcearea2));
        new CreateRoundedView(laterColor, btnRadius, 0, strokeColor, view.findViewById(R.id.imagemarkerdest2));

        LatLng cameraPosition = mainAct.cameraForUserPosition();

        String latitude = mainAct.getIntent().getStringExtra("latitude");
        String longitude = mainAct.getIntent().getStringExtra("longitude");

        if (mainAct.getMap() != null && latitude != null && longitude != null && !latitude.equals("0.0") && !longitude.equals("0.0")) {
            mainAct.getMap().moveCamera(new LatLng(GeneralFunctions.parseDoubleValue(0.0, latitude), GeneralFunctions.parseDoubleValue(0.0, longitude), Utils.defaultZomLevel));
        } else if (cameraPosition != null && mainAct.getMap() != null) {
            mainAct.getMap().moveCamera(cameraPosition);
        }

        if (cameraPosition != null) {
            onGoogleMapCameraChangeList gmap = new onGoogleMapCameraChangeList();
            gmap.onCameraIdle();
        }

        addGlobalLayoutListner();

        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        addGlobalLayoutListner();
    }

    private void addGlobalLayoutListner() {

        if (getView() != null) {
            getView().getViewTreeObserver().removeGlobalOnLayoutListener(this);
        }
        if (view != null) {
            view.getViewTreeObserver().removeGlobalOnLayoutListener(this);
        }

        if (getView() != null) {

            getView().getViewTreeObserver().addOnGlobalLayoutListener(this);
        } else if (view != null) {
            view.getViewTreeObserver().addOnGlobalLayoutListener(this);
        }
    }


    public void manageView() {
        UberxProviderswitchArea.setVisibility(View.GONE);
    }

    public void setDestinationViewEnableOrDisabled(String selectedType, boolean callsetDestview) {

        try {

            if (mainAct.isDeliver(selectedType) || ServiceModule.isDeliveronly()) {
                destinationArea1.setVisibility(View.GONE);
                isDestinationMode = false;

                if (callsetDestview) {
                    setDestinationView(mainAct.cabSelectionFrag != null ? false : true);
                }

                setSelected("1");

                area2.setVisibility(View.GONE);
                area_source.setVisibility(View.VISIBLE);

            } else {
                destinationArea1.setVisibility(View.VISIBLE);
                view.findViewById(R.id.headerArea).setVisibility(View.GONE);
                MainHeaderLayout.setBackgroundColor(getResources().getColor(R.color.appThemeColor_bg_parent_1));

                int color = getResources().getColor(R.color.black);
                ((ImageView) view.findViewById(R.id.menuImgView)).setColorFilter(color);
                ((ImageView) view.findViewById(R.id.backImgView)).setColorFilter(color);
                ((MTextView) view.findViewById(R.id.titleText)).setVisibility(View.GONE);

                if (mainAct.pickUpLocation != null) {
                    area2.setVisibility(View.VISIBLE);
                    area_source.setVisibility(View.GONE);
                    isDestinationMode = true;
                }

            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

    }

    private void setDestinationViewEnableOrDisabled(boolean callsetDestview) {

        destinationArea1.setVisibility(View.GONE);
        isDestinationMode = false;

        if (callsetDestview) {
            setDestinationView(mainAct.cabSelectionFrag != null ? false : true);
        }

        setSelected("1");

        area2.setVisibility(View.GONE);
        area_source.setVisibility(View.VISIBLE);

        mainAct.setMarginToLocationButton(false, 0);

    }

    private void setSelected(String selected) {

        if (getActivity() == null) {
            return;
        }

        MainHeaderLayout.setBackgroundColor(ServiceModule.RideDeliveryProduct ? getResources().getColor(R.color.appThemeColor_bg_parent_1) : getResources().getColor(R.color.appThemeColor_1));

        int btnColor = ServiceModule.RideDeliveryProduct ? getResources().getColor(R.color.black) : getResources().getColor(R.color.white);

        ((ImageView) view.findViewById(R.id.menuImgView)).setColorFilter(btnColor);
        ((ImageView) view.findViewById(R.id.backImgView)).setColorFilter(btnColor);

        if (generalFunc.isMultiDelivery() && ServiceModule.isDeliveronly()) {
            ((MTextView) view.findViewById(R.id.titleText)).setVisibility(View.VISIBLE);
            ((MTextView) view.findViewById(R.id.titleText)).setText(generalFunc.retrieveLangLBl("New Booking", "LBL_MULTI_NEW_BOOKING_TXT"));
        }

        if (selected.equalsIgnoreCase("1")) {

            ((View) view.findViewById(R.id.lineView)).setBackgroundColor(getResources().getColor(R.color.appThemeColor_Dark_1));

            ((MTextView) view.findViewById(R.id.tv1)).setTextColor(getResources().getColor(R.color.appThemeColor_1));
            ((MTextView) view.findViewById(R.id.tv1)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_VEHICLE_TYPE"));
            ((LinearLayout) view.findViewById(R.id.tabArea1)).setBackground(getResources().getDrawable(R.drawable.tab_background_selected_23));


            ((MTextView) view.findViewById(R.id.tv2)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            ((MTextView) view.findViewById(R.id.tv2)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_ROUTE"));

            ((MTextView) view.findViewById(R.id.tv3)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            ((MTextView) view.findViewById(R.id.tv3)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_PRICE"));
            view.findViewById(R.id.headerArea).setVisibility(View.VISIBLE);

        }

    }

    private void setDestinationView(boolean callCabs) {

        area2.setVisibility(View.GONE);
        area_source.setVisibility(View.GONE);


        mainAct.destAddress = "";
        mainAct.destLocLatitude = "";
        mainAct.destLocLongitude = "";

        if (mainAct.isMenuImageShow && !ServiceModule.isDeliveronly()) {
            menuImgView.setVisibility(View.GONE);
            menuBtn.setVisibility(View.GONE);
            backImgView.setVisibility(View.VISIBLE);
            backBtn.setVisibility(View.VISIBLE);
        }


        if (mainAct != null && callCabs) {
            mainAct.addcabselectionfragment();
        }

        try {
            assert mainAct != null;
            mainAct.cabSelectionFrag.isSkip = true;
            mainAct.cabSelectionFrag.isRouteFail = false;

            try {

                Handler handler = new Handler();
                handler.postDelayed(() -> {
                    if (mainAct.cabSelectionFrag != null) {
                        mainAct.cabSelectionFrag.handleSourceMarker("--");
                    }

                }, 200);
            } catch (Exception e) {

            }

            if (getMap() != null) {
                getMap().clear();
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }


    public void setGoogleMapInstance(GeoMapLoader.GeoMap geoMap) {
        this.geoMap = geoMap;
        this.getMap().setOnCameraIdleListener(new onGoogleMapCameraChangeList());
        this.getMap().setOnCameraMoveListener(new OnCameraMoveListener() {
            @Override
            public void onCameraMove() {
                if (mainAct != null && mainAct.cabSelectionFrag != null) {
                    mainAct.cabSelectionFrag.mangeMrakerPostion();
                }
            }
        });
    }

    private GeoMapLoader.GeoMap getMap() {
        return this.geoMap;
    }

    public void setDefaultView() {
        mainHeaderFrag.isclickablesource = false;
        mainHeaderFrag.isclickabledest = false;

        if (!ServiceModule.isServiceProviderOnly()) {
            area2.setVisibility(View.GONE);
        }

        destLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));
        destLocSelectTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));
        mainAct.setDestinationPoint("", "", "", false);

        if (mainAct.pickUpLocation != null) {
            if (getMap() != null) {
                getMap().moveCamera(new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude(), getMap().getCameraPosition().zoom));
            }
        }
    }

    public void setDestinationAddress(String destAddress) {
        LatLng center = null;
        if (getMap() != null && getMap().getCameraPosition() != null) {
            center = getMap().getCameraPosition();
        }

        if (center == null) {
            return;
        }

        if (destLocTxt != null) {
            destLocTxt.setText(destAddress);
        } else {
            this.destAddress = destAddress;
        }

        mainAct.setDestinationPoint("" + center.latitude, "" + center.longitude, destAddress, true);
    }

    public String getPickUpAddress() {
        return pickUpLocTxt != null ? pickUpLocTxt.getText().toString() : "";
    }

    public void setPickUpAddress(String pickUpAddress) {
        LatLng center = null;
        if (getMap() != null && getMap().getCameraPosition() != null) {
            center = getMap().getCameraPosition();
        }
        if (center == null) {
            return;
        }

        if (sourceLocSelectTxt != null) {
            sourceLocSelectTxt.setText(pickUpAddress);
        }
        this.pickUpAddress = pickUpAddress;
        if (pickUpLocTxt != null) {
            pickUpLocTxt.setText(pickUpAddress);
            handlePickEditIcon();
        } else {
            this.pickUpAddress = pickUpAddress;
        }

        mainAct.pickUpLocationAddress = this.pickUpAddress;
        Location pickupLocation = new Location("");
        pickupLocation.setLongitude(center.longitude);
        pickupLocation.setLatitude(center.latitude);
        mainAct.pickUpLocation = pickupLocation;
        mainAct.addOrResetStopOverPoints(center.latitude, center.longitude, this.pickUpAddress, true);


        Intent data1 = new Intent();
        data1.putExtra("Address", mainAct.pickUpLocationAddress);
        data1.putExtra("Latitude", "" + mainAct.pickUpLocation.getLatitude());
        data1.putExtra("Longitude", "" + mainAct.pickUpLocation.getLongitude());


        mainAct.showSelectionDialog(data1, true);
    }

    public void handlePickEditIcon() {
        addPickUpImage.setVisibility(View.GONE);
        editPickupImage.setVisibility(View.VISIBLE);
        addPickArea2Image.setVisibility(View.GONE);
        editPickArea2Image.setVisibility(View.VISIBLE);
    }


    public void handleDestEditIcon() {
        addDestImageview.setVisibility(View.GONE);
        editDestImageview.setVisibility(View.VISIBLE);
        addDestarea2Image.setVisibility(View.GONE);
        editDestarea2Image.setVisibility(View.VISIBLE);
    }

    public void handleDestAddIcon() {
        addDestImageview.setVisibility(View.VISIBLE);
        editDestImageview.setVisibility(View.GONE);
        addDestarea2Image.setVisibility(View.VISIBLE);
        editDestarea2Image.setVisibility(View.GONE);
    }

    public void configDestinationMode(boolean isDestinationMode) {
        this.isDestinationMode = isDestinationMode;
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {

        if (getActivity() == null || (getActivity() == null && mainAct != null && mainAct.isMultiDelivery())) {
            return;
        }

        if (latitude == mainAct.pickUp_tmpLatitude && longitude == mainAct.pickUp_tmpLongitude && !mainAct.pickUp_tmpAddress.equalsIgnoreCase("")) {
            address = mainAct.pickUp_tmpAddress;
        }

        geocodeobject = Utils.removeWithSpace(geocodeobject);

        if (!isDestinationMode || mainAct.isMultiDelivery()) {
            mainAct.tempDestGeoCode = geocodeobject;
            pickUpLocTxt.setText(address);
            handlePickEditIcon();
            sourceLocSelectTxt.setText(address);
        } else {
            mainAct.tempPickupGeoCode = geocodeobject;
        }
        mainAct.onAddressFound(address);

        if (mainAct.isMultiDelivery()) {
            setDestinationViewEnableOrDisabled(true);
        }


        Location location = new Location("gps");
        location.setLatitude(latitude);
        location.setLongitude(longitude);
        if (!isDestinationMode || mainAct.isMultiDelivery()) {
            mainAct.pickUpLocationAddress = address;
            mainAct.currentGeoCodeObject = geocodeobject;

            mainAct.addOrResetStopOverPoints(latitude, longitude, address, true);

            if (mainAct.cabSelectionFrag == null) {
                if (mainAct.loadAvailCabs != null) {
                    mainAct.pickUpLocation = location;
                    mainAct.loadAvailCabs.setPickUpLocation(location);
                    mainAct.loadAvailCabs.pickUpAddress = mainAct.pickUpLocationAddress;
                    mainAct.loadAvailCabs.changeCabs();
                }
            }
        }

        if ((mainAct.cabSelectionFrag == null && !isDestinationMode) && !mainAct.isMultiDelivery()) {
            //   Logger.d("SET_PICKUP", "in 9-");

            Intent data1 = new Intent();
            data1.putExtra("Address", mainAct.pickUpLocationAddress);
            data1.putExtra("Latitude", "" + mainAct.pickUpLocation.getLatitude());
            data1.putExtra("Longitude", "" + mainAct.pickUpLocation.getLongitude());


            mainAct.showSelectionDialog(data1, true);
        }

        if (mainAct.loadAvailCabs == null) {
            mainAct.isDriverAssigned = false;
            mainAct.initializeLoadCab();
        }


        if (mainAct.cabSelectionFrag != null) {
            if (!isDestinationMode || mainAct.isMultiDelivery()) {
                if (mainAct.cabTypesArrList.size() < 1) {
                    mainAct.loadAvailCabs.checkAvailableCabs();
                } else {
                    isPickUpAddressStateChanged(mainAct.pickUpLocation);
                }
            }
        }

        if (!isfirst) {
            isfirst = true;
            mainAct.uberXAddress = address;
            mainAct.uberXlat = latitude;
            mainAct.uberXlong = longitude;

            if (!isDestinationMode || mainAct.isMultiDelivery()) {
                pickUpLocTxt.setText(address);
                handlePickEditIcon();
                sourceLocSelectTxt.setText(address);
                Location pickUpLoc = new Location("");
                pickUpLoc.setLatitude(latitude);
                pickUpLoc.setLongitude(longitude);
                mainAct.pickUpLocation = pickUpLoc;
            }

            showAddressArea();
        }

        mainAct.configDestinationMode(isDestinationMode);
        //   Logger.d("SET_PICKUP", "-->13");
        mainAct.onAddressFound(address);

        if (isfirst && mainAct.eFly && !Utils.checkText(mainAct.iFromStationId)) {
            //  Logger.d("SET_PICKUP", "-->13 11");
            area2.setVisibility(View.GONE);
            area_source.setVisibility(View.GONE);
        }

        mainAct.currentGeoCodeObject = geocodeobject;
    }

    public void showAddressArea() {
        if (!isDestinationMode || mainAct.isMultiDelivery()) if (mainAct.isMultiDelivery()) {
        } else if (!ServiceModule.isServiceProviderOnly()) {
            // area2.setVisibility(View.VISIBLE);
            area_source.setVisibility(View.GONE);
        }
        if (isUfx) {
            //  if (ServiceModule.RideDeliveryUbexProduct) {
            area_source.setVisibility(View.GONE);
            area2.setVisibility(View.GONE);

            // }
        }


        if (mainAct.isMultiDelivery()) {
        } else {
            if (!ServiceModule.isServiceProviderOnly()) {
                // area2.setVisibility(View.VISIBLE);
                area_source.setVisibility(View.GONE);
            }
            if (isUfx) {
                area_source.setVisibility(View.GONE);
                area2.setVisibility(View.GONE);
            }
        }

        if (!ServiceModule.isServiceProviderOnly() && !mainAct.isMultiDelivery()) {
            // area2.setVisibility(View.VISIBLE);
            area_source.setVisibility(View.GONE);
        }

        if (isUfx) {
            //if (ServiceModule.RideDeliveryUbexProduct) {
            area_source.setVisibility(View.GONE);
            area2.setVisibility(View.GONE);
            // }
        }

        if (!mainAct.isMultiDelivery()) {
            isDestinationMode = true;
        }
    }


    public String getAvailableCarTypesIds() {
        String carTypesIds = "";
        for (int i = 0; i < mainAct.cabTypesArrList.size(); i++) {
            String iVehicleTypeId = mainAct.cabTypesArrList.get(i).get("iVehicleTypeId");

            carTypesIds = carTypesIds.equals("") ? iVehicleTypeId : (carTypesIds + "," + iVehicleTypeId);
        }
        return carTypesIds;
    }

    public void isPickUpAddressStateChanged(Location pickupLocation) {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "CheckSourceLocationState");
        parameters.put("PickUpLatitude", pickupLocation.getLatitude() + "");
        parameters.put("PickUpLongitude", pickupLocation.getLongitude() + "");
        parameters.put("SelectedCarTypeID", getAvailableCarTypesIds());
        parameters.put("CurrentCabGeneralType", mainAct.getCurrentCabGeneralType());

        ApiHandler.execute(mainAct, parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    if (mainAct.loadAvailCabs != null) {
                        mainAct.loadAvailCabs.checkAvailableCabs();
                    }
                }
            } else {
                if (mainAct.loadAvailCabs != null) {
                    mainAct.loadAvailCabs.checkAvailableCabs();
                }
            }
        });

    }


    public void disableDestMode() {
        isDestinationMode = false;
        mainAct.configDestinationMode(isDestinationMode);
    }

    public void releaseResources() {
        if (this.getMap() != null) {
            this.getMap().setOnCameraIdleListener(null);
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        }
    }

    public void releaseAddressFinder() {
        if (this.getMap() != null) {
            this.getMap().setOnCameraIdleListener(null);
        }
    }

    public void addAddressFinder() {
        if (this.getMap() != null) {
            this.getMap().setOnCameraIdleListener(new onGoogleMapCameraChangeList());
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        Logger.d("onActivityResult", "::" + requestCode + "::" + resultCode);
        if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE) {
            isclickablesource = false;
        }

        if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE && resultCode == Activity.RESULT_OK && data != null && getMap() != null) {

            addOrResetListOfAddresses(data, false);

        } else if (requestCode == Utils.SEARCH_DEST_LOC_REQ_CODE) {

            if (resultCode == mainAct.RESULT_OK && data != null && getMap() != null) {
                if (mainAct.eFly) {
                    //     Logger.d("SET_PICKUP", "in 7");
                    mainAct.showSelectionDialog(data, false);

                } else {
                    addOrResetListOfAddresses(data, true);
                }

            } else {
                isSchedule = false;
                isclickabledest = false;
            }


        } else if (requestCode == Utils.PLACE_AUTOCOMPLETE_REQUEST_CODE) {
            isclickabledest = false;
            if (requestCode == Activity.RESULT_CANCELED) {

            } else {
                isclickabledest = false;
            }

        }
    }

    /**
     * @param data          data with lattitude,longitude,address ,isSkip - Intent
     * @param isDestination -is Destination - true/false
     */
    public void addOrResetListOfAddresses(Intent data, boolean isDestination) {
        // multi stop Over enabled

        Utils.hideKeyboard(getContext());
        if (data.hasExtra("stopOverPointsList")) {
            String data1 = data.getStringExtra("stopOverPointsList");
            Gson gson = new Gson();

            mainAct.stopOverPointsList = gson.fromJson(data1, new TypeToken<ArrayList<Stop_Over_Points_Data>>() {
            }.getType());


            if (mainAct.stopOverPointsList.size() < 1) {
                if (mainAct.cabSelectionFrag != null) {
                    isclickabledest = false;
                    mainAct.onBackPressed();
                } else {
                    isclickabledest = false;
                }
            } else if (mainAct.stopOverPointsList.size() < 2) {

                if (data.getBooleanExtra("isSkip", false)) {
                    if (!isDestination) {
                        addSourceOrDestAddress(isDestination, data, false);
                        addSourceOrDestAddress(!isDestination, data, true);
                    } else if (isDestination) {
                        addSourceOrDestAddress(!isDestination, data, false);
                        addSourceOrDestAddress(isDestination, data, true);
                    }
                } else if (mainAct.stopOverPointsList.get(0).isDestination() == isDestination) {
                    addSourceOrDestAddress(isDestination, data, true);
                } else if (mainAct.stopOverPointsList.get(0).isDestination() != isDestination) {
                    isclickabledest = false;
                    addSourceOrDestAddress(false, data, true);
                } else if (mainAct.cabSelectionFrag != null) {
                    isclickabledest = false;
                    mainAct.onBackPressed();
                } else {
                    isclickabledest = false;
                    return;
                }
            } else if (mainAct.stopOverPointsList.size() >= 2) {
                if (!isDestination) {
                    addSourceOrDestAddress(isDestination, data, false);
                    Intent intentData = new Intent();
                    addSourceOrDestAddress(true, data.hasExtra("stopOverPointsList") ? intentData.putExtra("stopOverPointsList", data.getStringExtra("stopOverPointsList")) : intentData, true);
                } else if (isDestination) {
                    Intent intentData = new Intent();
                    addSourceOrDestAddress(false, data.hasExtra("stopOverPointsList") ? intentData.putExtra("stopOverPointsList", data.getStringExtra("stopOverPointsList")) : intentData, false);

                    addSourceOrDestAddress(isDestination, data, true);

                }
            } else {
                addSourceOrDestAddress(isDestination, data, true);
            }

            if (mainAct.loadAvailCabs != null) {
                if (mainAct.loadAvailCabs.currentWebTask != null) {
                    mainAct.loadAvailCabs.currentWebTask.cancel(true);
                    mainAct.loadAvailCabs.currentWebTask = null;
                }
                mainAct.loadAvailCabs.changeCabs();
            }
        } else {
            // multi stop Over disabled
            addSourceOrDestAddress(isDestination, data, true);
        }


    }

    /**
     * @param isDestination -is Destination - true/false
     * @param data          - data with lattitude,longitude,address ,isSkip - Intent
     * @param routeDraw     - need to draw route - true/false
     */
    private void addSourceOrDestAddress(boolean isDestination, Intent data, boolean routeDraw) {
        double Latitude = 0.0;
        double Longitude = 0.0;
        String Address = "";
        boolean isChanged = false;

        if (data.hasExtra("iLocationId")) {
            //  Logger.d("SET_PICKUP", "set location Id");

            String locationId = data.getStringExtra("iLocationId");
            if (isDestination) {
                if (Utils.checkText(mainAct.iToStationId) && mainAct.iToStationId.equalsIgnoreCase("locationId")) {
                    isChanged = true;
                }
                mainAct.iToStationId = locationId;
            } else {

                if (Utils.checkText(mainAct.iFromStationId) && mainAct.iFromStationId.equalsIgnoreCase("locationId")) {
                    isChanged = true;
                }
                mainAct.iFromStationId = locationId;
            }
        }

        if (data.hasExtra("Address")) {
            Latitude = GeneralFunctions.parseDoubleValue(0.0, data.getStringExtra("Latitude"));
            Longitude = GeneralFunctions.parseDoubleValue(0.0, data.getStringExtra("Longitude"));
            Address = data.getStringExtra("Address");
        }


        if (isDestination) {
            if (mainAct.stopOverPointsList.size() >= 2 && !data.getBooleanExtra("isSkip", false)) {
                if (mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).isAddressAdded()) {
                    Latitude = mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).getDestLat();
                    Longitude = mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).getDestLong();
                    Address = mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).getDestAddress();

                } else if (mainAct.cabSelectionFrag != null) {
                    isclickabledest = false;
                    mainAct.onBackPressed();
                } else {
                    isclickabledest = false;
                    return;
                }
            }

            isclickabledest = false;
            isDestinationMode = true;
            mainAct.isDestinationMode = true;
            isAddressEnable = true;


            destLocTxt.setText(Address);
            destLocSelectTxt.setText(Address);
            handleDestEditIcon();


            if (data.getBooleanExtra("isSkip", false)) {
                mainAct.iToStationId = "";
                area2.setVisibility(View.GONE);
                area_source.setVisibility(View.GONE);

                boolean hasRunningInstance = mainAct.cabSelectionFrag == null ? false : true;
                setDestinationView(mainAct.cabSelectionFrag == null);


                if (mainAct != null) {

                    if (mainAct.stopOverPointsList.size() == 2) {
                        mainAct.stopOverPointsList.remove(mainAct.stopOverPointsList.size() - 1);
                    }

                    mainAct.setDestinationPoint("", "", "", false);

                    if (hasRunningInstance) {
                        new Handler().postDelayed(() -> {
                            /*End of the day trip change*/
                            if (mainAct.loadAvailCabs != null && generalFunc.retrieveValue(Utils.ENABLE_DRIVER_DESTINATIONS_KEY).equalsIgnoreCase("Yes")) {

                                mainAct.loadAvailCabs.filterDrivers(true);

                            }
                        }, 2000);
                    }


                }
                return;
            }

            mainAct.setDestinationPoint("" + Latitude, "" + Longitude, Address, true);

            LatLng destlocation = new LatLng(Latitude, Longitude);

            LatLng cameraPosition = new LatLng(destlocation.latitude, destlocation.longitude, getMap().getCameraPosition().zoom);

            if (mainAct.cabSelectionFrag != null && routeDraw) {
                mainAct.cabSelectionFrag.findRoute("--");
            }

            if (mainAct.cabSelectionFrag == null) {
                getMap().moveCamera(cameraPosition);
            } else {
                getMap().clear();
            }

            if (!data.hasExtra("iLocationId")) {
                getMap().moveCamera(cameraPosition);
            }

            mainAct.destAddress = Address;
            destLocTxt.setText(Address);
            destLocSelectTxt.setText(Address);
            handleDestEditIcon();


            if (mainAct != null && mainAct.cabSelectionFrag == null && mainAct.isMultiDelivery()) {
                mainAct.addcabselectionfragment();
            } else {
                if (mainAct != null) {
                    mainAct.addcabselectionfragment();
                }
            }

            if (isChanged && mainAct != null && mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.checkAvailableCabs();

            }


            if (mainAct != null) {
                mainAct.cabSelectionFrag.isSkip = false;
            }

            area2.setVisibility(View.GONE);
            area_source.setVisibility(View.GONE);

            if (mainAct != null && mainAct.isMenuImageShow && mainAct.isUfx) {
                menuImgView.setVisibility(View.VISIBLE);
                menuBtn.setVisibility(View.VISIBLE);
                backImgView.setVisibility(View.GONE);
                backBtn.setVisibility(View.GONE);
            }


        } else {

            if (mainAct != null && mainAct.stopOverPointsList.size() >= 1 && !mainAct.isMultiDelivery()) {
                if (mainAct.stopOverPointsList.get(0).isAddressAdded()) {
                    Latitude = mainAct.stopOverPointsList.get(0).getDestLat();
                    Longitude = mainAct.stopOverPointsList.get(0).getDestLong();
                    Address = mainAct.stopOverPointsList.get(0).getDestAddress();

                } else if (mainAct.cabSelectionFrag != null) {
                    isclickabledest = false;
                    mainAct.onBackPressed();
                } else {
                    isclickabledest = false;
                    return;
                }
            }


            LatLng pickUplocation = new LatLng(Latitude, Longitude, Utils.defaultZomLevel);

            if (mainAct != null && mainAct.pickUpLocation == null) {
                if (getMap() != null) {
                    getMap().clear();
                    getMap().moveCamera(pickUplocation);
                }
                onAddressFound(Address, pickUplocation.latitude, pickUplocation.longitude, "");
                return;
            }


            isAddressEnable = true;

            pickUpLocTxt.setText(Address);
            sourceLocSelectTxt.setText(Address);


            LatLng cameraPosition = new LatLng(pickUplocation.latitude, pickUplocation.longitude, getMap().getCameraPosition().zoom);

            mainAct.pickUpLocationAddress = Address;
            if (mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.pickUpAddress = mainAct.pickUpLocationAddress;
            }
            if (mainAct.cabSelectionFrag != null) {
                if (mainAct.isPoolCabTypeIdSelected) {
                    mainAct.cabSelectionFrag.poolBackImage.performClick();
                }
            }

            if (mainAct.pickUpLocation == null) {
                final Location location = new Location("gps");
                location.setLatitude(pickUplocation.latitude);
                location.setLongitude(pickUplocation.longitude);

                mainAct.pickUpLocation = location;
            } else {
                mainAct.pickUpLocation.setLatitude(pickUplocation.latitude);
                mainAct.pickUpLocation.setLongitude(pickUplocation.longitude);
            }

            mainAct.addOrResetStopOverPoints(pickUplocation.latitude, pickUplocation.longitude, mainAct.pickUpLocationAddress, true);
            if (!data.hasExtra("iLocationId")) {
                //   Logger.d("SET_PICKUP", "in 8");

                Intent data1 = new Intent();
                data1.putExtra("Address", mainAct.pickUpLocationAddress);
                data1.putExtra("Latitude", "" + mainAct.pickUpLocation.getLatitude());
                data1.putExtra("Longitude", "" + mainAct.pickUpLocation.getLongitude());

                mainAct.showSelectionDialog(data1, true);
            } else {
                getMap().moveCamera(cameraPosition);
            }

            if (mainAct != null && mainAct.cabSelectionFrag == null && mainAct.isMultiDelivery()) {
                mainAct.addcabselectionfragment();
            }
            if (mainAct.cabSelectionFrag != null && routeDraw) {
                mainAct.cabSelectionFrag.findRoute("--");
            }

            if (mainAct.cabSelectionFrag == null) {
                getMap().moveCamera(cameraPosition);
            } else {
                getMap().clear();
            }

            if (mainAct.loadAvailCabs == null || mainAct.isMultiDelivery()) {
                mainAct.isDriverAssigned = false;
                mainAct.initializeLoadCab();
            }
            if (mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.pickUpAddress = mainAct.pickUpLocationAddress;
                mainAct.loadAvailCabs.setPickUpLocation(mainAct.pickUpLocation);
                if (mainAct.cabSelectionFrag != null) {
                    if (!isDestinationMode || mainAct.isMultiDelivery()) {
                        if (mainAct.cabTypesArrList.size() < 1) {
                            mainAct.loadAvailCabs.checkAvailableCabs();
                        } else {
                            isPickUpAddressStateChanged(mainAct.pickUpLocation);
                        }
                    }
                }
            }

            if (mainAct.cabSelectionFrag == null) {
                getMap().clear();
                getMap().moveCamera(pickUplocation.zoom(Utils.defaultZomLevel));
            }

        }
        Utils.hideKeyboard(getActivity());
    }
    /*Multi Stop Over Implementation end*/

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }

    public void setSourceAddress(double latitude, double longitude) {
        if (latitude == 0.0 || longitude == 0.0) {
            return;
        }
        try {

            String longi = mainAct.getIntent().getStringExtra("longitude");
            if (longi == null || (longi != null && longi.equalsIgnoreCase(""))) {
                getAddressFromLocation.setLocation(latitude, longitude);
                getAddressFromLocation.execute();
            } else {
                onAddressFound(mainAct.getIntent().getStringExtra("address"), GeneralFunctions.parseDoubleValue(0, mainAct.getIntent().getStringExtra("latitude")), GeneralFunctions.parseDoubleValue(0, mainAct.getIntent().getStringExtra("longitude")), "");
            }


        } catch (Exception e) {
            Logger.e("cameraPosition", ":MainHeader:" + e.getMessage());
        }
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int id = view.getId();
        if (id == destarea.getId()) {

            try {
                if (Utils.checkText(mainAct.pickUpLocationAddress) && mainAct.pickUpLocation != null) {

                    if (!isclickabledest) {
                        isclickabledest = true;
                        isDestinationMode = true;
                        LatLng pickupPlaceLocation = null;

                        Bundle bn = new Bundle();
                        bn.putString("locationArea", "dest");
                        bn.putBoolean("isDriverAssigned", mainAct.isDriverAssigned);

                        if (mainAct.pickUpLocation != null) {
                            pickupPlaceLocation = new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude());
                            bn.putDouble("lat", pickupPlaceLocation.latitude);
                            bn.putDouble("long", pickupPlaceLocation.longitude);
                            bn.putString("address", mainAct.pickUpLocationAddress);
                        } else {
                            bn.putDouble("lat", 0.0);
                            bn.putDouble("long", 0.0);
                            bn.putString("address", "");

                        }

                        if (mainAct.destLocation != null && mainAct.destLocation.getLatitude() != 0.0) {
                            bn.putDouble("lat", mainAct.destLocation.getLatitude());
                            bn.putDouble("long", mainAct.destLocation.getLongitude());
                            bn.putString("address", mainAct.destAddress);
                        }

                        bn.putString("type", mainAct.getCurrentCabGeneralType());
                        if (mainAct.eFly) {
                            bn.putBoolean("eFly", mainAct.eFly);
                        }

                        if (mainAct != null && mainAct.isMultiStopOverEnabled()) {
                            Gson gson = new Gson();
                            String json = gson.toJson(mainAct.stopOverPointsList);
                            bn.putString("stopOverPointsList", json);
                            bn.putString("iscubejekRental", "" + mainAct.iscubejekRental);
                            bn.putString("isRental", "" + mainAct.isRental);
                        }

                        bn.putBoolean("isRecent", isRecent);
                        bn.putString("loc_pick_bar", "");
                        bn.putBoolean("isSchedule", isSchedule);
                        bn.putBoolean("isTaxiBid", mainAct.isTaxiBid);
                        new ActUtils(mainAct.getActContext()).startActForResult(mainHeaderFrag, SearchLocationActivity.class, Utils.SEARCH_DEST_LOC_REQ_CODE, bn);
                    }
                }

            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        } else if (view.getId() == pickupLocArea1.getId()) {
            try {
                if (!isclickablesource) {
                    isclickablesource = true;
                    LatLng pickupPlaceLocation = null;
                    Bundle bn = new Bundle();
                    bn.putString("locationArea", "source");
                    bn.putBoolean("isDriverAssigned", mainAct.isDriverAssigned);
                    if (mainAct != null && mainAct.pickUpLocation != null) {
                        pickupPlaceLocation = new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude());
                        bn.putDouble("lat", pickupPlaceLocation.latitude);
                        bn.putDouble("long", pickupPlaceLocation.longitude);
                        bn.putString("address", mainAct.pickUpLocationAddress);
                    } else {
                        bn.putDouble("lat", 0.0);
                        bn.putDouble("long", 0.0);
                        bn.putString("address", "");
                    }
                    bn.putString("type", mainAct.getCurrentCabGeneralType());
                    if (mainAct != null && mainAct.eFly) {
                        bn.putBoolean("eFly", mainAct.eFly);
                    }


                    if (mainAct != null && mainAct.isMultiStopOverEnabled()) {
                        Gson gson = new Gson();
                        String json = gson.toJson(mainAct.stopOverPointsList);
                        bn.putString("stopOverPointsList", json);
                        bn.putString("iscubejekRental", "" + mainAct.iscubejekRental);
                        bn.putString("isRental", "" + mainAct.isRental);
                    }

                    new ActUtils(mainAct.getActContext()).startActForResult(mainHeaderFrag, SearchLocationActivity.class, Utils.SEARCH_PICKUP_LOC_REQ_CODE, bn);
                }
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());

            }
        } else if (view.getId() == R.id.sourceLocSelectTxt) {
            try {
                if (Utils.checkText(mainAct.pickUpLocationAddress)) {
                    isAddressEnable = true;
                }

                if (mainAct.isMultiDelivery()) {
                    area_source.setVisibility(View.GONE);
                    area2.setVisibility(View.GONE);
                } else {
                    area_source.setVisibility(View.VISIBLE);
                    area2.setVisibility(View.GONE);
                }

                disableDestMode();

                if (mainAct.getDestinationStatus()) {
                    destLocSelectTxt.setText(mainAct.getDestAddress());
                    handleDestEditIcon();
                } else {
                    destLocSelectTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));
                    handleDestAddIcon();
                }
            } catch (Exception e) {
                Logger.e("ExceptionSourceLocSelect", ":::" + e.toString());
            }
        } else if (view.getId() == R.id.destLocSelectTxt) {
            if (mainAct.getDestinationStatus()) {
                isAddressEnable = true;
            }

            if (!isclickabledest && mainAct.pickUpLocation != null && Utils.checkText(mainAct.pickUpLocationAddress)) {
                area2.setVisibility(View.VISIBLE);
                area_source.setVisibility(View.GONE);
                isDestinationMode = true;
                mainAct.configDestinationMode(isDestinationMode);
                if (!mainAct.getDestinationStatus()) {
                    new Handler().postDelayed(() -> destarea.performClick(), 250);
                }
            }
        } else if (view.getId() == backBtn.getId()) {
            if (mainAct.cabSelectionFrag != null) {
                if (mainAct.cabSelectionFrag.design_linear_layout_car_details.getVisibility() == View.VISIBLE) {
                    mainAct.setMapPaddingGeneral();
                    mainAct.cabSelectionFrag.animateCarView(View.GONE);
                    return;
                }
            }
            if (mainAct.isMenuImageShow) {
                menuImgView.setVisibility(View.VISIBLE);
                menuBtn.setVisibility(View.VISIBLE);
                backImgView.setVisibility(View.GONE);
                backBtn.setVisibility(View.GONE);
            } else {
                menuImgView.setVisibility(View.GONE);
                menuBtn.setVisibility(View.GONE);
                backImgView.setVisibility(View.VISIBLE);
                backBtn.setVisibility(View.VISIBLE);
            }
            mainAct.onBackPressed();
        } else if (view.getId() == menuBtn.getId()) {
            mainAct.addDrawer.checkDrawerState(true);
        } else if (view.getId() == listTxt.getId() || view.getId() == listImage.getId()) {

            RelativeLayout.LayoutParams userlocparams = (RelativeLayout.LayoutParams) (mainAct.userLocBtnImgView).getLayoutParams();
            userlocparams.bottomMargin = Utils.dipToPixels(mainAct.getActContext(), Utils.dpToPx(mainAct.getActContext(), 10));
            mainAct.userLocBtnImgView.requestLayout();

            mainAct.userLocBtnImgView.setVisibility(View.GONE);
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            params.gravity = Gravity.TOP;
            mainAct.ridelaterHandleView.setLayoutParams(params);
            mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_List, false);
        } else if (view.getId() == mapTxt.getId() || view.getId() == mapImage.getId()) {
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            params.gravity = Gravity.BOTTOM;
            mainAct.ridelaterHandleView.setLayoutParams(params);
            mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_Map, false);
            mainAct.userLocBtnImgView.setVisibility(View.VISIBLE);
        } else if (view.getId() == filterImage.getId()) {

            if (!mainAct.isVideoConsultEnable) {
                mainAct.redirectToMapOrList(Utils.Cab_UberX_Type_Filter, false);
            }

            Bundle bn = new Bundle();
            bn.putString("SelectedVehicleTypeId", mainAct.selectedCabTypeId);
            bn.putString("parentId", mainAct.getIntent().getStringExtra("parentId"));
            bn.putString("eForVideoConsultation", mainAct.isVideoConsultEnable ? "Yes" : "No");
            new ActUtils(getContext()).startActForResult(UberxFilterActivity.class, bn, Utils.FILTER_REQ_CODE);
        } else if (view.getId() == uberXbackImgView.getId()) {
            mainAct.onBackPressed();
        }

    }


    public class onGoogleMapCameraChangeList implements OnCameraIdleListener {
        @Override
        public void onCameraIdle() {
            if (getAddressFromLocation == null) {
                return;
            }
            if (mainAct.cabSelectionFrag != null) {
                mainAct.cabSelectionFrag.mangeMrakerPostion();
                return;
            }

            LatLng center = null;
            if (getMap() != null && getMap().getCameraPosition() != null) {
                center = getMap().getCameraPosition();
            }

            if (center == null) {
                return;
            }

            if (!isAddressEnable) {
                setSourceAddress(center.latitude, center.longitude);
                mainAct.onMapCameraChanged();
            } else {
                isAddressEnable = false;
            }
        }
    }

    @Override
    public void onGlobalLayout() {
        if (getView() != null || view != null) {
            if (getView() != null) {

                fragmentWidth = getView().getWidth();
                fragmentHeight = getView().getHeight();
            } else if (view != null) {

                fragmentWidth = view.getWidth();
                fragmentHeight = view.getHeight();
            }

            Logger.e("FragHeight", "is :::" + fragmentHeight + "\n" + "Frag Width is :::" + fragmentWidth);

        }
    }
}
