package com.act;

import android.app.Activity;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.RideDeliveryCardPagerAdapter;
import com.activity.ParentActivity;
import com.adapter.files.RideDeliveryCategoryAdapter;
import com.dialogs.BottomInfoDialog;
import com.fragments.MyBookingFragment;
import com.fragments.MyProfileFragment;
import com.fragments.MyWalletFragment;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.AddBottomBar;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.LoadAvailableCab;
import com.general.files.MyApp;
import com.general.files.OpenAdvertisementDialog;
import com.general.files.OpenCatType;
import com.buddyverse.main.R;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.MarkerOptions;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.LoopingCirclePageIndicator;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Timer;
import java.util.TimerTask;

public class RideDeliveryActivity extends ParentActivity implements ViewPager.OnPageChangeListener, RideDeliveryCardPagerAdapter.OnItemClickList, RideDeliveryCategoryAdapter.OnItemClickList, GeoMapLoader.OnMapReadyCallback
        , GetLocationUpdates.LocationUpdates {


    ViewPager bannerViewPager;
    LoopingCirclePageIndicator bannerCirclePageIndicator;
    private RideDeliveryCardPagerAdapter mCardAdapter;
    ArrayList<HashMap<String, String>> imagesList;
    ArrayList<HashMap<String, String>> itemList;
    RecyclerView dataListRecyclerView;
    RideDeliveryCategoryAdapter rideDeliveryCategoryAdapter;


    AddBottomBar addBottomBar;
    FrameLayout container;

    MyProfileFragment myProfileFragment;
    MyWalletFragment myWalletFragment;
    public MyBookingFragment myBookingFragment;

    private static final int SEL_CARD = 004;
    public static final int TRANSFER_MONEY = 87;
    public boolean iswallet = false;
    MTextView whereTxt, aroundTxt;
    MTextView homePlaceTxt, homePlaceHTxt;
    MTextView workPlaceTxt, workPlaceHTxt;
    LinearLayout homeLocArea, workLocArea;
    ImageView homeActionImgView, workActionImgView;
    private static final int RC_SIGN_IN_UP = 007;
    static LinearLayout MainLayout, bottomMenuArea, homeWorkArea;
    ProgressBar mProgressBar;

    boolean isRide = false;
    GeoMapLoader.GeoMap geoMap;
    GetLocationUpdates getLastLocation;
    boolean isFirstLocation = true;
    public Location userLocation;
    LoadAvailableCab loadAvailCabs;
    public ArrayList<HashMap<String, String>> listOfDrivers;
    ArrayList<Marker> driverMarkerList = new ArrayList<>();
    ImageView backImgView;
    Toolbar toolbar;
    int currentPage = 0;
    Timer timer;
    final long DELAY_MS = 500;//delay in milliseconds before task is to be executed
    final long PERIOD_MS = 7500; // time in milliseconds between successive task executions.


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_ride_delivery);

        initView();
        getDetails();
        addBottomBar = new AddBottomBar(getActContext(), obj_userProfile);

        if (ServiceModule.RideDeliveryProduct || ServiceModule.isDeliveronly()) {
            String advertise_banner_data = generalFunc.getJsonValueStr("advertise_banner_data", obj_userProfile);
            if (advertise_banner_data != null && !advertise_banner_data.equalsIgnoreCase("")) {

                String image_url = generalFunc.getJsonValue("image_url", advertise_banner_data);
                if (image_url != null && !image_url.equalsIgnoreCase("")) {
                    HashMap<String, String> map = new HashMap<>();
                    map.put("image_url", image_url);
                    map.put("tRedirectUrl", generalFunc.getJsonValue("tRedirectUrl", advertise_banner_data));
                    map.put("vImageWidth", generalFunc.getJsonValue("vImageWidth", advertise_banner_data));
                    map.put("vImageHeight", generalFunc.getJsonValue("vImageHeight", advertise_banner_data));
                    new OpenAdvertisementDialog(getActContext(), map, generalFunc);
                }
            }
            MyApp.getInstance().showOutsatandingdilaog(whereTxt);
        }

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") &&
                !generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {

            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_TXT"),
                    R.raw.biometric, generalFunc.retrieveLangLBl("", "LBL_OK"), true);
            generalFunc.storeData("isFirstTimeSmartLoginView", "Yes");
        }
    }


    public void initView() {

        backImgView = (ImageView) findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);
        ((MTextView) findViewById(R.id.appNameTxt)).setVisibility(View.VISIBLE);
        toolbar = (Toolbar) findViewById(R.id.toolbar);
        container = (FrameLayout) findViewById(R.id.container);
        whereTxt = (MTextView) findViewById(R.id.whereTxt);
        aroundTxt = (MTextView) findViewById(R.id.aroundTxt);
        homePlaceTxt = (MTextView) findViewById(R.id.homePlaceTxt);
        homePlaceHTxt = (MTextView) findViewById(R.id.homePlaceHTxt);
        workPlaceTxt = (MTextView) findViewById(R.id.workPlaceTxt);
        workPlaceHTxt = (MTextView) findViewById(R.id.workPlaceHTxt);
        homeLocArea = (LinearLayout) findViewById(R.id.homeLocArea);
        workLocArea = (LinearLayout) findViewById(R.id.workLocArea);
        MainLayout = (LinearLayout) findViewById(R.id.MainLayout);
        bottomMenuArea = (LinearLayout) findViewById(R.id.bottomMenuArea);
        homeWorkArea = (LinearLayout) findViewById(R.id.homeWorkArea);
        bannerViewPager = (ViewPager) findViewById(R.id.bannerViewPager);
        bannerCirclePageIndicator = findViewById(R.id.bannerCirclePageIndicator);
        homeActionImgView = (ImageView) findViewById(R.id.homeActionImgView);
        workActionImgView = (ImageView) findViewById(R.id.workActionImgView);
        mProgressBar = (ProgressBar) findViewById(R.id.mProgressBar);
        addToClickHandler(homeLocArea);
        addToClickHandler(workLocArea);
        addToClickHandler(homeActionImgView);
        addToClickHandler(workActionImgView);

        bannerViewPager.addOnPageChangeListener(this);
        dataListRecyclerView = (RecyclerView) findViewById(R.id.dataListRecyclerView);
        whereTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WHERE_TO"));
        aroundTxt.setText(generalFunc.retrieveLangLBl("Around You", "LBL_AROUND_YOU"));
        checkPlaces();

        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainLayout, R.layout.ridedlivery_shimmer_view);

        new Handler(Looper.myLooper()).postDelayed(() -> {
            try {
                SkeletonViewHandler.getInstance().hideSkeletonView();
                (new GeoMapLoader(RideDeliveryActivity.this, R.id.mapFragmentContainer)).bindMap(RideDeliveryActivity.this);
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
        }, 5000);
    }

    public GeoMapLoader.GeoMap getMap() {
        return this.geoMap;
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {

        this.geoMap = geoMap;
        geoMap.getUiSettings().setAllGesturesEnabled(false);
        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(false);
            getMap().getUiSettings().setTiltGesturesEnabled(false);
            getMap().getUiSettings().setZoomControlsEnabled(false);
            getMap().getUiSettings().setCompassEnabled(false);
            getMap().getUiSettings().setMyLocationButtonEnabled(false);
        }


        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        checkLocation();

    }

    private void checkLocation() {
        if (generalFunc.isLocationPermissionGranted(false)) {
            if (getLastLocation != null) {
                getLastLocation.stopLocationUpdates();
                getLastLocation = null;
            }
            GetLocationUpdates.locationResolutionAsked = false;
            getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
        }
    }

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }

        this.userLocation = location;


        if (isFirstLocation) {

            double currentZoomLevel = Utils.defaultZomLevel;

            if (getMap() != null) {
                getMap().moveCamera(new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude(), (float) currentZoomLevel));
            }


            isFirstLocation = false;
            if (itemList != null && itemList.size() > 0) {
                getOnlineDriversRideDelivery(itemList.get(0).get("eCatType"));
            }
        }

    }


    public void initializeLoadCab() {


        loadAvailCabs = new LoadAvailableCab(getActContext(), generalFunc, "1", userLocation,
                getMap(), obj_userProfile.toString());
        loadAvailCabs.checkAvailableCabs();
    }


    public void onClick(View view) {
        int i = view.getId();


        if (i == R.id.homeLocArea) {


            HashMap<String, String> data = new HashMap<>();
            data.put("userHomeLocationAddress", "");
            data.put("userHomeLocationLatitude", "");
            data.put("userHomeLocationLongitude", "");
            data = GeneralFunctions.retrieveValue(data, getActContext());
            final String home_address_str = data.get("userHomeLocationAddress");
            if (home_address_str != null && !home_address_str.equalsIgnoreCase("")) {
                HashMap<String, String> hasmpObj = itemList.get(0);
                hasmpObj.put("isHome", "Yes");
                hasmpObj.remove("isWork");
                (new OpenCatType(getActContext(), hasmpObj)).execute();

            }


        } else if (i == R.id.workLocArea) {

            HashMap<String, String> data = new HashMap<>();
            data.put("userWorkLocationAddress", "");
            data.put("userWorkLocationLatitude", "");
            data.put("userWorkLocationLongitude", "");
            data = generalFunc.retrieveValue(data);


            String work_address_str = data.get("userWorkLocationAddress");


            if (work_address_str != null && !work_address_str.equalsIgnoreCase("")) {
                HashMap<String, String> hasmpObj = itemList.get(0);
                hasmpObj.remove("isHome");
                hasmpObj.put("isWork", "Yes");
                (new OpenCatType(getActContext(), hasmpObj)).execute();

            }
        } else if (i == R.id.homeActionImgView) {
            if (getIntent().hasExtra("eSystem") && !Utils.checkText(generalFunc.getMemberId())) {
                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", false);
                new ActUtils(getActContext()).startActForResult(RideDeliveryActivity.class, bn, RC_SIGN_IN_UP);
                return;
            }

            Bundle bn = new Bundle();
            bn.putString("isHome", "true");


            new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                    bn, Utils.ADD_HOME_LOC_REQ_CODE);

        } else if (i == R.id.workActionImgView) {
            if (getIntent().hasExtra("eSystem") && !Utils.checkText(generalFunc.getMemberId())) {
                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", false);
                new ActUtils(getActContext()).startActForResult(RideDeliveryActivity.class, bn, RC_SIGN_IN_UP);
                return;
            }

            Bundle bn = new Bundle();
            bn.putString("isWork", "true");


            new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                    bn, Utils.ADD_WORK_LOC_REQ_CODE);

        }


    }


    public void checkPlaces() {

        String home_address_str = generalFunc.retrieveValue("userHomeLocationAddress");
        String work_address_str = generalFunc.retrieveValue("userWorkLocationAddress");

        if (home_address_str != null && !home_address_str.equalsIgnoreCase("")) {

            homePlaceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
            homePlaceHTxt.setText("" + home_address_str);
            homePlaceHTxt.setVisibility(View.VISIBLE);
            homeActionImgView.setImageResource(R.mipmap.ic_edit);

        } else {
            homePlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
            homePlaceTxt.setText("" + generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
            homeActionImgView.setImageResource(R.mipmap.ic_pluse);
        }

        if (work_address_str != null && !work_address_str.equalsIgnoreCase("")) {

            workPlaceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WORK_PLACE"));
            workPlaceHTxt.setText("" + work_address_str);
            workPlaceHTxt.setVisibility(View.VISIBLE);
            workActionImgView.setImageResource(R.mipmap.ic_edit);

        } else {
            workPlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WORK_PLACE"));
            workPlaceTxt.setText("" + generalFunc.retrieveLangLBl("", "LBL_ADD_WORK_PLACE_TXT"));
            workActionImgView.setImageResource(R.mipmap.ic_pluse);
        }

        if (home_address_str != null && home_address_str.equalsIgnoreCase("")) {
            homePlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
            homePlaceTxt.setVisibility(View.GONE);
            homePlaceHTxt.setVisibility(View.VISIBLE);
            homeActionImgView.setImageResource(R.mipmap.ic_pluse);
        }

        if (work_address_str != null && work_address_str.equalsIgnoreCase("")) {
            workPlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_WORK_PLACE_TXT"));
            workPlaceTxt.setText("----");
            workPlaceTxt.setVisibility(View.GONE);
            workPlaceHTxt.setVisibility(View.VISIBLE);
            workActionImgView.setImageResource(R.mipmap.ic_pluse);
        }
    }

    public Activity getActContext() {
        return RideDeliveryActivity.this;
    }

    @Override
    protected void onResume() {
        super.onResume();

        if (getMap() != null) {
            checkLocation();
        }
        if (myProfileFragment != null && isProfilefrg) {
            myProfileFragment.onResume();
        }

        if (myWalletFragment != null && isWalletfrg) {
            myWalletFragment.onResume();
        }

        if (myBookingFragment != null && isBookingfrg) {
            myBookingFragment.onResume();
        }
        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        if (iswallet) {

            iswallet = false;
        }


    }

    public void openHistoryFragment() {
        this.getWindow().setStatusBarColor(getResources().getColor(R.color.appThemeColor_1));
        SkeletonViewHandler.getInstance().hideSkeletonView();

        isProfilefrg = false;
        isWalletfrg = false;
        isBookingfrg = true;
        MainLayout.setVisibility(View.GONE);
        container.setVisibility(View.VISIBLE);
        if (myBookingFragment == null) {
            myBookingFragment = new MyBookingFragment();
        } else {
            myBookingFragment.onDestroy();
            myBookingFragment = new MyBookingFragment();
        }

        getSupportFragmentManager().beginTransaction()
                .replace(R.id.container, myBookingFragment).commit();
    }

    public void manageHome() {
        isProfilefrg = false;
        isWalletfrg = false;
        isBookingfrg = false;
        MainLayout.setVisibility(View.VISIBLE);
        container.setVisibility(View.GONE);

        if (imagesList != null && imagesList.get(0).get("vStatusBarColor") != null) {
            bannerViewPager.setCurrentItem(0);
            toolbar.setBackgroundColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
            this.getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
        }
    }

    public void openProfileFragment() {
        this.getWindow().setStatusBarColor(getResources().getColor(R.color.appThemeColor_1));
        SkeletonViewHandler.getInstance().hideSkeletonView();
        isProfilefrg = true;
        isWalletfrg = false;
        isBookingfrg = false;
        MainLayout.setVisibility(View.GONE);
        container.setVisibility(View.VISIBLE);
        if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
        }

        getSupportFragmentManager().beginTransaction()
                .replace(R.id.container, myProfileFragment).commit();


    }

    boolean isProfilefrg = false;
    boolean isWalletfrg = false;
    boolean isBookingfrg = false;

    public void openWalletFragment() {
        this.getWindow().setStatusBarColor(getResources().getColor(R.color.appThemeColor_1));
        SkeletonViewHandler.getInstance().hideSkeletonView();
        isProfilefrg = false;
        isWalletfrg = true;
        isBookingfrg = false;

        MainLayout.setVisibility(View.GONE);
        container.setVisibility(View.VISIBLE);
        if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
        }

        getSupportFragmentManager().beginTransaction()
                .replace(R.id.container, myWalletFragment).commit();


    }

    public void getDetails() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getServiceCategoryDetails");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iVehicleCategoryId", "0");

        if (getIntent().getStringExtra("latitude") != null && !getIntent().getStringExtra("latitude").equalsIgnoreCase("")) {
            parameters.put("vLatitude", getIntent().getStringExtra("latitude"));
        }
        if (getIntent().getStringExtra("longitude") != null && !getIntent().getStringExtra("longitude").equalsIgnoreCase("")) {
            parameters.put("vLongitude", getIntent().getStringExtra("longitude"));
        }


        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);

                if (isDataAvail) {
                    mProgressBar.setVisibility(View.GONE);
                    MainLayout.setVisibility(View.VISIBLE);
                    // bottomMenuArea.setVisibility(View.VISIBLE);

                    itemList = new ArrayList<>();
                    JSONArray itemarr = generalFunc.getJsonArray(Utils.message_str, responseObj);
                    for (int i = 0; i < itemarr.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(itemarr, i);
                        HashMap<String, String> itemObj = new HashMap<>();
                        itemObj.put("vCategory", generalFunc.getJsonValueStr("vCategory", obj_temp));
                        itemObj.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));
                        itemObj.put("eCatType", generalFunc.getJsonValueStr("eCatType", obj_temp));
                        itemObj.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp));

                        itemList.add(itemObj);
                        if (generalFunc.getJsonValueStr("eCatType", obj_temp).equalsIgnoreCase("Ride")) {
                            isRide = true;
                        }

                    }


                    JSONArray bannerarr = generalFunc.getJsonArray("bannerArray", responseObj);
                    imagesList = new ArrayList<>();
                    mCardAdapter = new RideDeliveryCardPagerAdapter();


                    for (int i = 0; i < bannerarr.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(bannerarr, i);

                        String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                        HashMap<String, String> bannerObj = new HashMap<>();
                        bannerObj.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
                        bannerObj.put("vSubtitle", generalFunc.getJsonValueStr("vSubtitle", obj_temp));
                        bannerObj.put("vBtnTtitle", generalFunc.getJsonValueStr("vBtnTtitle", obj_temp));
                        bannerObj.put("vTextColor", generalFunc.getJsonValueStr("vTextColor", obj_temp));
                        bannerObj.put("vBtnBgColor", generalFunc.getJsonValueStr("vBtnBgColor", obj_temp));
                        bannerObj.put("vBtnTextColor", generalFunc.getJsonValueStr("vBtnTextColor", obj_temp));
                        bannerObj.put("eCatType", generalFunc.getJsonValueStr("eCatType", obj_temp));
                        bannerObj.put("vCategory", generalFunc.getJsonValueStr("vCategory", obj_temp));
                        bannerObj.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp));
                        bannerObj.put("vImage", vImage);
                        bannerObj.put("vStatusBarColor", generalFunc.getJsonValueStr("vStatusBarColor", obj_temp));


                        imagesList.add(bannerObj);
                        mCardAdapter.addCardItem(bannerObj, getActContext(), this);
                    }
                    if (imagesList != null) {
                        toolbar.setBackgroundColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
                        this.getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));

                        int imageListSize = imagesList.size();
                        if (imageListSize > 2) {
                            bannerViewPager.setOffscreenPageLimit(3);
                        } else if (imageListSize > 1) {
                            bannerViewPager.setOffscreenPageLimit(2);
                        }
                    }

                    GridLayoutManager gridLay = new GridLayoutManager(getActContext(), 3);
                    dataListRecyclerView.setLayoutManager(gridLay);
                    bannerViewPager.setAdapter(mCardAdapter);
                    bannerViewPager.setOffscreenPageLimit(3);
                    rideDeliveryCategoryAdapter = new RideDeliveryCategoryAdapter(getActContext(), itemList, generalFunc);
                    rideDeliveryCategoryAdapter.setOnItemClickList(this);
                    dataListRecyclerView.setAdapter(rideDeliveryCategoryAdapter);

                    if (isRide) {
                        homeWorkArea.setVisibility(View.VISIBLE);
                        whereTxt.setVisibility(View.VISIBLE);

                    } else {
                        homeWorkArea.setVisibility(View.GONE);
                        whereTxt.setVisibility(View.GONE);
                    }
                    getOnlineDriversRideDelivery(itemList.get(0).get("eCatType"));
                    bannerCirclePageIndicator.setDataSize(imagesList.size());
                    bannerCirclePageIndicator.setViewPager(bannerViewPager);

                    if (imagesList != null && imagesList.size() == 1) {
                        bannerCirclePageIndicator.setVisibility(View.GONE);
                    }
                    manageAutoScroll();

                } else {
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (btn_id == 1) {
                            finish();

                        }
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();

                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int position) {
        try {


            if (imagesList.get(position).get("vStatusBarColor") != null) {
                toolbar.setBackgroundColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
                this.getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
            }
        } catch (Exception e) {

        }


    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }


    @Override
    public void onItemClick(int position) {


        (new OpenCatType(getActContext(), itemList.get(position))).execute();

    }

    @Override
    public void onBannerItemClick(int position) {
        (new OpenCatType(getActContext(), imagesList.get(position))).execute();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == RC_SIGN_IN_UP && resultCode == RESULT_OK) {

        } else if (requestCode == Utils.ADD_HOME_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {

            String Latitude = data.getStringExtra("Latitude");
            String Longitude = data.getStringExtra("Longitude");
            String Address = data.getStringExtra("Address");

            generalFunc.storeData("userHomeLocationLatitude", "" + Latitude);
            generalFunc.storeData("userHomeLocationLongitude", "" + Longitude);
            generalFunc.storeData("userHomeLocationAddress", "" + Address);

            homePlaceTxt.setText(Address);
            checkPlaces();


        } else if (requestCode == Utils.ADD_WORK_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {
            String Latitude = data.getStringExtra("Latitude");
            String Longitude = data.getStringExtra("Longitude");
            String Address = data.getStringExtra("Address");


            generalFunc.storeData("userWorkLocationLatitude", "" + Latitude);
            generalFunc.storeData("userWorkLocationLongitude", "" + Longitude);
            generalFunc.storeData("userWorkLocationAddress", "" + Address);

            workPlaceTxt.setText(Address);
            checkPlaces();


        } else if (myWalletFragment != null && isWalletfrg) {
            myWalletFragment.onActivityResult(requestCode, resultCode, data);
        }
    }

    public void manageAutoScroll() {
        final Handler handler = new Handler();
        final Runnable Update = () -> {
            if (!isProfilefrg && !iswallet && !isBookingfrg) {
                if (currentPage == imagesList.size()) {
                    currentPage = 0;
                }
                bannerViewPager.setCurrentItem(currentPage++, true);
            }
        };

        timer = new Timer(); // This will create a new Thread
        timer.schedule(new TimerTask() { // task to be scheduled
            @Override
            public void run() {
                handler.post(Update);
            }
        }, DELAY_MS, PERIOD_MS);
    }

    public void getOnlineDriversRideDelivery(String etype) {
        if (userLocation == null) {
            return;
        }


        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getOnlineDriversRideDelivery");
        parameters.put("PickUpLatitude", "" + userLocation.getLatitude());
        parameters.put("PickUpLongitude", "" + userLocation.getLongitude());
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("PickUpAddress", "");
        parameters.put("eType", etype);


        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                JSONArray availCabArr = generalFunc.getJsonArray("DriverList", responseString);

                if (availCabArr != null) {
                    listOfDrivers = new ArrayList<>();
                    for (int i = 0; i < availCabArr.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(availCabArr, i);

                        JSONObject carDetailsJson = generalFunc.getJsonObject("DriverCarDetails", obj_temp);
                        HashMap<String, String> driverDataMap = new HashMap<String, String>();
                        driverDataMap.put("driver_id", generalFunc.getJsonValueStr("iDriverId", obj_temp));
                        driverDataMap.put("Name", generalFunc.getJsonValueStr("vName", obj_temp));
                        driverDataMap.put("eIsFeatured", generalFunc.getJsonValueStr("eIsFeatured", obj_temp));
                        driverDataMap.put("LastName", generalFunc.getJsonValueStr("vLastName", obj_temp));
                        driverDataMap.put("Latitude", generalFunc.getJsonValueStr("vLatitude", obj_temp));
                        driverDataMap.put("Longitude", generalFunc.getJsonValueStr("vLongitude", obj_temp));
                        driverDataMap.put("GCMID", generalFunc.getJsonValueStr("iGcmRegId", obj_temp));
                        driverDataMap.put("iAppVersion", generalFunc.getJsonValueStr("iAppVersion", obj_temp));
                        driverDataMap.put("driver_img", generalFunc.getJsonValueStr("vImage", obj_temp));
                        driverDataMap.put("average_rating", generalFunc.getJsonValueStr("vAvgRating", obj_temp));
                        driverDataMap.put("DIST_TO_PICKUP_INT", generalFunc.getJsonValueStr("distance", obj_temp));
                        driverDataMap.put("vPhone_driver", generalFunc.getJsonValueStr("vPhone", obj_temp));
                        driverDataMap.put("vPhoneCode_driver", generalFunc.getJsonValueStr("vCode", obj_temp));
                        driverDataMap.put("tProfileDescription", generalFunc.getJsonValueStr("tProfileDescription", obj_temp));
                        driverDataMap.put("ACCEPT_CASH_TRIPS", generalFunc.getJsonValueStr("ACCEPT_CASH_TRIPS", obj_temp));
                        driverDataMap.put("vWorkLocationRadius", generalFunc.getJsonValueStr("vWorkLocationRadius", obj_temp));
                        driverDataMap.put("PROVIDER_RADIUS", generalFunc.getJsonValueStr("vWorkLocationRadius", obj_temp));
                        driverDataMap.put("iGcmRegId", generalFunc.getJsonValueStr("iGcmRegId", obj_temp));

                        driverDataMap.put("DriverGender", generalFunc.getJsonValueStr("eGender", obj_temp));
                        driverDataMap.put("eFemaleOnlyReqAccept", generalFunc.getJsonValueStr("eFemaleOnlyReqAccept", obj_temp));

                        driverDataMap.put("eHandiCapAccessibility", generalFunc.getJsonValueStr("eHandiCapAccessibility", carDetailsJson));
                        driverDataMap.put("eChildSeatAvailable", generalFunc.getJsonValueStr("eChildSeatAvailable", carDetailsJson));
                        driverDataMap.put("eWheelChairAvailable", generalFunc.getJsonValueStr("eWheelChairAvailable", carDetailsJson));
                        driverDataMap.put("vCarType", generalFunc.getJsonValueStr("vCarType", carDetailsJson));
                        driverDataMap.put("vColour", generalFunc.getJsonValueStr("vColour", carDetailsJson));
                        driverDataMap.put("vLicencePlate", generalFunc.getJsonValueStr("vLicencePlate", carDetailsJson));
                        driverDataMap.put("make_title", generalFunc.getJsonValueStr("make_title", carDetailsJson));
                        driverDataMap.put("model_title", generalFunc.getJsonValueStr("model_title", carDetailsJson));
                        driverDataMap.put("fAmount", generalFunc.getJsonValueStr("fAmount", carDetailsJson));
                        driverDataMap.put("eRental", generalFunc.getJsonValueStr("vRentalCarType", carDetailsJson));
                        /*End of the day feature - driver is in destination Mode*/
                        driverDataMap.put("eDestinationMode", generalFunc.getJsonValueStr("eDestinationMode", obj_temp));


                        driverDataMap.put("vCurrencySymbol", generalFunc.getJsonValueStr("vCurrencySymbol", carDetailsJson));

                        driverDataMap.put("PROVIDER_RATING_COUNT", generalFunc.getJsonValueStr("PROVIDER_RATING_COUNT", obj_temp));

                        driverDataMap.put("eFareType", generalFunc.getJsonValueStr("eFareType", carDetailsJson));
                        driverDataMap.put("ePoolRide", generalFunc.getJsonValueStr("ePoolRide", carDetailsJson));
                        driverDataMap.put("fMinHour", generalFunc.getJsonValueStr("fMinHour", carDetailsJson));
                        driverDataMap.put("eTripStatusActive", generalFunc.getJsonValueStr("eTripStatusActive", obj_temp));
                        driverDataMap.put("eFavDriver", generalFunc.getJsonValueStr("eFavDriver", obj_temp));
                        driverDataMap.put("iStopId", generalFunc.getJsonValueStr("iStopId", carDetailsJson));
                        driverDataMap.put("eIconType", generalFunc.getJsonValueStr("eIconType", carDetailsJson));

                        driverDataMap.put("IS_PROVIDER_ONLINE", generalFunc.getJsonValueStr("IS_PROVIDER_ONLINE", obj_temp));
                        listOfDrivers.add(driverDataMap);
                    }

                }


                filterDrivers(false);


            } else {
                removeDriversFromMap(true);

            }

        });

    }

    public void removeDriversFromMap(boolean isUnSubscribeAll) {
        if (driverMarkerList.size() > 0) {
            ArrayList<Marker> tempDriverMarkerList = new ArrayList<>();
            tempDriverMarkerList.addAll(driverMarkerList);
            for (int i = 0; i < tempDriverMarkerList.size(); i++) {
                Marker marker_temp = driverMarkerList.get(0);
                marker_temp.remove();
                driverMarkerList.remove(0);

            }
        }


    }

    public void filterDrivers(boolean isCheckAgain) {
        ArrayList<Marker> driverMarkerList_temp = new ArrayList<>();
        LatLngBounds.Builder builder = new LatLngBounds.Builder();
        if (listOfDrivers != null) {
            for (int i = 0; i < listOfDrivers.size(); i++) {
                HashMap<String, String> driverData = listOfDrivers.get(i);
                double driverLocLatitude = GeneralFunctions.parseDoubleValue(0.0, driverData.get("Latitude"));
                double driverLocLongitude = GeneralFunctions.parseDoubleValue(0.0, driverData.get("Longitude"));
                builder.include(new LatLng(driverLocLatitude, driverLocLongitude));
                Marker driverMarker = drawMarker(new LatLng(driverLocLatitude, driverLocLongitude), "", driverData);
                driverMarkerList_temp.add(driverMarker);


            }

            driverMarkerList.addAll(driverMarkerList_temp);
        }
    }

    public Marker drawMarker(LatLng point, String Name, HashMap<String, String> driverData) {

        MarkerOptions markerOptions = new MarkerOptions();
        String eIconType = driverData.get("eIconType");

        int iconId = R.mipmap.car_driver;
        if (eIconType.equalsIgnoreCase("Ambulance")) {
            iconId = R.mipmap.car_driver_ambulance;
        } else if (eIconType.equalsIgnoreCase("Bike")) {
            iconId = R.mipmap.car_driver_1;
        } else if (eIconType.equalsIgnoreCase("Cycle")) {
            iconId = R.mipmap.car_driver_2;
        } else if (eIconType.equalsIgnoreCase("Truck")) {
            iconId = R.mipmap.car_driver_4;
        } else if (eIconType.equalsIgnoreCase("Fly")) {
            iconId = R.mipmap.ic_fly_icon;
        }

        markerOptions.position(point).title("DriverId" + driverData.get("driver_id")).icon(BitmapDescriptorFactory.fromResource(iconId))
                .anchor(0.5f, 0.5f).flat(true);


        // Adding marker on the Google Map
        final Marker marker = getMap().addMarker(markerOptions);
        marker.setRotation(0);
        marker.setVisible(true);


        return marker;
    }

    public void pubNubMsgArrived(String message) {

        String driverMsg = generalFunc.getJsonValue("Message", message);
        String eType = generalFunc.getJsonValue("eType", message);
        //   String app_type = APP_TYPE;

        if (driverMsg.equals("CabRequestAccepted")) {
            String eSystem = generalFunc.getJsonValueStr("eSystem", obj_userProfile);
            if (eSystem != null && eSystem.equalsIgnoreCase("DeliverAll")) {
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message));
                return;
            }


            if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {

                return;
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

}