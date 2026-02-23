package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.dialogs.OpenListView;
import com.fontanalyzer.SystemFont;
import com.general.PermissionHandler;
import com.general.PermissionHandlers;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.CancelTripDialog;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.OpenPassengerDetailDialog;
import com.general.files.SlideButton;
import com.general.files.UpdateDirections;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptor;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.LatLngBounds;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.buddyverse.providers.R;
import com.model.ChatMsgHandler;
import com.model.ServiceModule;
import com.mukesh.OtpView;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MarkerAnim;
import com.utils.MyUtils;
import com.utils.NavigationSensor;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class DriverArrivedActivity extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener {

    private MTextView timeTxt, distanceTxt, titleTxt, addressTxt, endTxt, wherePickPointTxt;
    private MButton btn_type2;
    public HashMap<String, String> data_trip;
    private SupportMapFragment map;
    private GoogleMap gMap;
    private Location userLocation;
    private AlertDialog dialog_declineOrder;
    private UpdateDirections updateDirections;
    private Marker driverMarker;

    private InternetConnection intCheck;
    private MarkerAnim MarkerAnim;
    String vImage = "";

    private boolean isCurrentLocationFocused = false, isIntializeDirectionUpdate = true, isPoolRide = false, isFirstMapMove = true, isOtpVerified = false, isOtpVerificationDenied = false;

    private String ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP = "", REQUEST_TYPE = "", tripId = "";
    private String passenger_lat = "", passenger_lon = "";
    private int selCurrentPosition = -1;

    //#UberPool
    private AppCompatImageView callArea, chatArea, userLocBtnImgView, emeTapImgView;
    private SlideButton arrivedSlideButton;
    private AppCompatImageView deliveryInfoView;
    private Dialog dialog_verify_via_otp;
    private LatLngBounds.Builder builderTracking;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_driver_arrived);
        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
                return;
            }
        }
        this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        if (data_trip == null) {
            if (!MyApp.getInstance().isGetDetailCall) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_TRY_AGAIN_TXT"), "", generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), i -> finish());
            }
            return;
        }
        Toolbar toolbar = findViewById(R.id.toolbar);
        generalFunc.setOverflowButtonColor(toolbar, getResources().getColor(R.color.white));

        generalFunc.storeData("PROVIDER_STATUS_MODE", "accept");

        MarkerAnim = new MarkerAnim();

        MarkerAnim.driverMarkerAnimFinished = true;


        //MyApp.getInstance().setOfflineState();

        RelativeLayout backArea = (RelativeLayout) findViewById(R.id.manageArea);
        endTxt = (MTextView) findViewById(R.id.endTxt);
        LinearLayout navigationArea = (LinearLayout) findViewById(R.id.navigationArea);
        intCheck = new InternetConnection(getActContext());

        ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP = generalFunc.getJsonValueStr("ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP", obj_userProfile);


        //gps view declaration end

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        addressTxt = (MTextView) findViewById(R.id.addressTxt);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);
        MTextView pickupTxt = (MTextView) findViewById(R.id.pickupTxt);
        MTextView RatingTxt = (MTextView) findViewById(R.id.RatingTxt);
        pickupTxt.setVisibility(View.GONE);
        MTextView personTxt = (MTextView) findViewById(R.id.personTxt);
        deliveryInfoView = (AppCompatImageView) findViewById(R.id.deliveryInfoView);
        addToClickHandler(deliveryInfoView);

        callArea = (AppCompatImageView) findViewById(R.id.callArea);
        chatArea = (AppCompatImageView) findViewById(R.id.chatArea);
        ImageView navigateAreaUP = (ImageView) findViewById(R.id.navigateAreaUP);

//        callArea.setBackground(getRoundBG("#3cca59"));
//        chatArea.setBackground(getRoundBG("#027bff"));
//        navigateAreaUP.setBackground(getRoundBG("#ffa60a"));


        addToClickHandler(callArea);
        addToClickHandler(chatArea);
        addToClickHandler(navigateAreaUP);

        userLocBtnImgView = findViewById(R.id.userLocBtnImgView);
        addToClickHandler(userLocBtnImgView);

        (findViewById(R.id.backImgView)).setVisibility(View.GONE);
        btn_type2.setId(Utils.generateViewId());

        emeTapImgView = (AppCompatImageView) findViewById(R.id.emeTapImgView);
        addToClickHandler(emeTapImgView);

        timeTxt = (MTextView) findViewById(R.id.timeTxt);
        distanceTxt = (MTextView) findViewById(R.id.distanceTxt);
        MTextView passengerNameVTxt = (MTextView) findViewById(R.id.passengerNameVTxt);
        LinearLayout RatingHArea = (LinearLayout) findViewById(R.id.RatingHArea);

        timeTxt = (MTextView) findViewById(R.id.timeTxt);

        timeTxt.setVisibility(View.GONE);

        pickupTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP"));


        PermissionHandlers.getInstance().initiatePermissionHandler();

        setTripButton();
        setData();
        setLabels();

        String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
        if (generalFunc.getJsonValue("eServiceLocation", last_trip_data) != null && generalFunc.getJsonValue("eServiceLocation", last_trip_data).equalsIgnoreCase("Driver")) {
            isIntializeDirectionUpdate = false;
            navigationArea.setVisibility(View.GONE);
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) backArea.getLayoutParams();
            params.setMargins(0, 0, 0, 0);
            backArea.setLayoutParams(params);
            timeTxt.setVisibility(View.GONE);
            arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_MARK_USER_ARRIVED_BTN_TITLE"));
        }

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            deliveryInfoView.setVisibility(View.VISIBLE);
            endTxt.setVisibility(View.VISIBLE);
            addToClickHandler(endTxt);
            passengerNameVTxt.setText(data_trip.get("PName") + " ");
            RatingTxt.setText(data_trip.get("PRating"));
            RatingHArea.setVisibility(View.VISIBLE);

        } else if (generalFunc.getJsonValue("ePoolRide", last_trip_data).equalsIgnoreCase("Yes")) {

            passengerNameVTxt.setText(data_trip.get("PName") + " " + data_trip.get("vLastName"));
            personTxt.setVisibility(View.VISIBLE);
            personTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("iPersonSize", last_trip_data)) + " " + generalFunc.retrieveLangLBl("", "LBL_PERSON"));

            pickupTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP"));
            pickupTxt.setVisibility(View.VISIBLE);
            isPoolRide = true;
            AppService.getInstance().executeService(AppService.Event.SUBSCRIBE, AppService.EventAction.CAB_REQUEST);
            btn_type2.setText(generalFunc.retrieveLangLBl("Mark As PIckUp", "LBL_POOL_MARK_AS_PICKUP"));
            arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("Mark As PIckUp", "LBL_POOL_MARK_AS_PICKUP"));
            setSupportActionBar(toolbar);
            RatingTxt.setText(data_trip.get("PRating"));
            RatingHArea.setVisibility(View.VISIBLE);

        } else {
            setSupportActionBar(toolbar);
            passengerNameVTxt.setText(data_trip.get("PName") + " ");
            RatingTxt.setText(data_trip.get("PRating"));
            RatingHArea.setVisibility(View.VISIBLE);
        }

        String OPEN_CHAT = generalFunc.retrieveValue(ChatMsgHandler.OPEN_CHAT);
        if (Utils.checkText(OPEN_CHAT)) {
            JSONObject OPEN_CHAT_DATA_OBJ = generalFunc.getJsonObject(OPEN_CHAT);
            if (OPEN_CHAT_DATA_OBJ != null) {
                ChatMsgHandler.performAction(OPEN_CHAT_DATA_OBJ.toString());
            }
        }

        generalFunc.storeData(Utils.DriverWaitingTime, "0");
        generalFunc.storeData(Utils.DriverWaitingSecTime, "0");

        map.getMapAsync(this);

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) titleTxt.getLayoutParams();
        params.setMargins(Utils.dipToPixels(getActContext(), 20), 0, 0, 0);
        titleTxt.setLayoutParams(params);

        addToClickHandler(btn_type2);

        GetLocationUpdates.getInstance().setTripStartValue(false, true, true, tripId);

        if (ServiceModule.IsTrackingProvider) {
            userLocBtnImgView.setVisibility(View.VISIBLE);
            navigationArea.setVisibility(View.GONE);
            RelativeLayout.LayoutParams params1 = (RelativeLayout.LayoutParams) backArea.getLayoutParams();
            params1.setMargins(0, 0, 0, 0);
            backArea.setLayoutParams(params1);

            endTxt.setVisibility(View.VISIBLE);
            addToClickHandler(endTxt);

            chatArea.setVisibility(View.GONE);
            RatingHArea.setVisibility(View.GONE);
            distanceTxt.setVisibility(View.GONE);
            timeTxt.setVisibility(View.GONE);

            ImageView imgTrack = findViewById(R.id.imgTrack);
            ImageView userImg = findViewById(R.id.userImg);
            String image_url = CommonUtilities.USER_PHOTO_PATH + data_trip.get("PassengerId") + "/" + data_trip.get("PPicName");
            new LoadImage.builder(LoadImage.bind(image_url), userImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
            imgTrack.setVisibility(View.GONE);
            if (Utils.checkText(data_trip.get("TripType"))) {
                imgTrack.setVisibility(View.VISIBLE);
                /*if (data_trip.get("TripType").equalsIgnoreCase("Pickup")) {
                    imgTrack.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_track_trip_org));
                } else {
                    imgTrack.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_track_trip_home));
                }*/
            }

            pickupTxt.setVisibility(View.VISIBLE);
            pickupTxt.setText(data_trip.get("orgName"));
            passengerNameVTxt.setTypeface(SystemFont.FontStyle.REGULAR.font);
            passengerNameVTxt.setText(data_trip.get("tStartAddress"));
            passengerNameVTxt.setTextSize(TypedValue.COMPLEX_UNIT_PX, getResources().getDimension(R.dimen._12ssp));

            arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TRIP_TXT"));

            if (data_trip.get("TripStatus").equalsIgnoreCase("Onboarding")) {
                arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_SLIDE_TO_MARK_ONBOARDING"));

            } else if (data_trip.get("TripStatus").equalsIgnoreCase("OnGoingTrip")) {
                arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_TRIP_TXT"));
                arrivedSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                endTxt.setVisibility(View.GONE);
            }
            emeTapImgView.setVisibility(View.GONE);
        }
    }

    private void setTripButton() {
        arrivedSlideButton = findViewById(R.id.startTripSlideButton);
        arrivedSlideButton.setBackgroundColor(getResources().getColor(R.color.appThemeColor_1));
        arrivedSlideButton.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
            if (ServiceModule.IsTrackingProvider) {
                if (isCompleted) {
                    updateTrackingTripStatus(data_trip.get("TripStatus"));
                }
                return;
            }

            String eAskCodeToUser = data_trip.get("eAskCodeToUser");
            String ePoolRide = data_trip.get("ePoolRide");
            if (Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified && ePoolRide.equalsIgnoreCase("Yes")) {
                if (isOtpVerificationDenied) {
                    isOtpVerificationDenied = false;
                    return;
                }
                openEnterOtpView();
            } else {
                if (isCompleted) {
                    setDriverStatusToArrived();
                }
            }

        });
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        return;
    }

    @Override
    protected void onResume() {
        super.onResume();

        if (this.userLocation != null) {
            onLocationUpdate(this.userLocation);
        }

        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }

        NavigationSensor.getInstance().configSensor(true);
    }


    @Override
    protected void onPause() {
        super.onPause();
        startLocationTracker();
        if (updateDirections != null) {
            updateDirections.releaseTask();
        }

        NavigationSensor.getInstance().configSensor(false);
    }


    public void setTimetext(String distance, String time) {
        try {
            timeTxt.setVisibility(View.VISIBLE);
            String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
            if (userProfileJson != null && !generalFunc.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {

                distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " ");
                timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            } else {
                distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " ");
                timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            }
        } catch (Exception e) {

        }
    }


    private void handleNoLocationDial() {

        if (generalFunc.isLocationEnabled()) {
            resetData();
        }

    }


    public void checkUserLocation() {
        if (generalFunc.isLocationEnabled() && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0)) {
            showprogress();
        } else {
            hideprogress();
        }
    }

    public void internetIsBack() {
        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }
    }

    private void showprogress() {
        new Handler().postDelayed(this::continueShowProgress, 2000);
        /*isCurrentLocationFocused = false;
        findViewById(R.id.errorLocArea).setVisibility(View.VISIBLE);

        findViewById(R.id.mProgressBar).setVisibility(View.VISIBLE);
        ((ProgressBar) findViewById(R.id.mProgressBar)).setIndeterminate(true);
        ((ProgressBar) findViewById(R.id.mProgressBar)).getIndeterminateDrawable().setColorFilter(getActContext().getResources().getColor(R.color.appThemeColor_1), android.graphics.PorterDuff.Mode.SRC_IN);*/

    }

    public void continueShowProgress() {

        if (!(generalFunc.isLocationEnabled() && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0))) {
            hideprogress();
            return;
        }

        isCurrentLocationFocused = false;

        if (!data_trip.get("REQUEST_TYPE").equalsIgnoreCase(Utils.CabGeneralType_UberX) || !data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
            findViewById(R.id.errorLocArea).setVisibility(View.VISIBLE);
            findViewById(R.id.mProgressBar).setVisibility(View.VISIBLE);
            ((ProgressBar) findViewById(R.id.mProgressBar)).setIndeterminate(true);
            ((ProgressBar) findViewById(R.id.mProgressBar)).getIndeterminateDrawable().setColorFilter(
                    getActContext().getResources().getColor(R.color.appThemeColor_1), android.graphics.PorterDuff.Mode.SRC_IN);

        }
    }

    private void hideprogress() {

        findViewById(R.id.errorLocArea).setVisibility(View.GONE);

        if (findViewById(R.id.mProgressBar) != null) {
            findViewById(R.id.mProgressBar).setVisibility(View.GONE);
        }
    }

    private void resetData() {
        if (intCheck.isNetworkConnected() && intCheck.check_int() && addressTxt.getText().equals(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"))) {
            setData();
        }

        if (!isCurrentLocationFocused) {
            setData();
            checkUserLocation();
        } else {
            checkUserLocation();
        }
    }


    private void setLabels() {

        endTxt.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_BTN_CANCEL_TXT"));

        setPageName();
        timeTxt.setText("--" + generalFunc.retrieveLangLBl("to reach", "LBL_REACH_TXT"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_ARRIVED_TXT"));

        arrivedSlideButton.setButtonText(generalFunc.retrieveLangLBl("Slide to arrive", "LBL_SLIDE_TO_ARRIVE"));

        // No location found but gps is on

        ((MTextView) findViewById(R.id.errorTitleTxt)).setText(generalFunc.retrieveLangLBl("Waiting for your location.", "LBL_LOCATION_FATCH_ERROR_TXT"));

        ((MTextView) findViewById(R.id.errorSubTitleTxt)).setText(generalFunc.retrieveLangLBl("Try to fetch  your accurate location. \"If you still face the problem, go to open sky instead of closed area\".", "LBL_NO_LOC_GPS_TXT"));

        wherePickPointTxt = findViewById(R.id.wherePickPointTxt);
        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && generalFunc.getJsonValueStr("ENABLE_PICKUP_AREA_PHOTO_UPLOAD", obj_userProfile).equalsIgnoreCase("Yes")) {
            wherePickPointTxt.setVisibility(View.VISIBLE);
            wherePickPointTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WHERE_PICKUP_POINT_TXT"));
            addToClickHandler(wherePickPointTxt);
            if (generalFunc.isRTLmode()) {
                wherePickPointTxt.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.start_curve_cardview_rtl));
            }
        } else {
            wherePickPointTxt.setVisibility(View.GONE);
        }

        boolean isKiosk = data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk");
        boolean isUser = Utils.checkText(data_trip.get("iGcmRegId_U"));
        if (isKiosk || !isUser) {
            wherePickPointTxt.setVisibility(View.GONE);
        }

        RelativeLayout.LayoutParams params1 = (RelativeLayout.LayoutParams) userLocBtnImgView.getLayoutParams();
        params1.setMargins(0, 0, 0, wherePickPointTxt.getVisibility() == View.VISIBLE ? (int) getResources().getDimension(R.dimen._7sdp) : (int) getResources().getDimension(R.dimen._1sdp));
        userLocBtnImgView.setLayoutParams(params1);
    }

    private void setPageName() {
        if (REQUEST_TYPE.equals("Deliver") || REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("Pickup Delivery", "LBL_PICKUP_DELIVERY"));
        } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_TXT"));
        } else if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BIDDING_LOCATION_TXT"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("Pick Up Passenger", "LBL_PICK_UP_PASSENGER"));
        }
        if (ServiceModule.IsTrackingProvider) {
            if (data_trip != null && data_trip.containsKey("TripType") && Utils.checkText(data_trip.get("TripType"))) {
                if (data_trip.get("TripType").equalsIgnoreCase("Pickup")) {
                    titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_PICKUP_TXT"));
                } else {
                    titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_DROPOFF_TXT"));
                }
            }
        }
    }

    @SuppressLint("PotentialBehaviorOverride")
    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;

        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(false);
        }
        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);

        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            if (ServiceModule.IsTrackingProvider) {
                marker.showInfoWindow();
            }
            return true;
        });
        getMap().setOnCameraMoveStartedListener(reason -> {

            if (reason == 1) {
                userLocBtnImgView.setVisibility(View.VISIBLE);
            }
        });


        double passenger_lat = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLatitude"));
        double passenger_lon = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLongitude"));

        int icon = R.drawable.ic_taxi_passanger_new;
        if (ServiceModule.ServiceProviderProduct || REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            icon = R.drawable.ufxprovider_new;
        }

        if (REQUEST_TYPE.equals(Utils.CabGeneralType_UberX)) {
            icon = R.drawable.ufxprovider_new;
        }
        if (REQUEST_TYPE.equals("Deliver") || REQUEST_TYPE.equals(Utils.eType_Multi_Delivery)) {
            icon = R.drawable.taxi_passenger_delivery_new;
        }

        BitmapDescriptor markerIcon;
        if (ServiceModule.IsTrackingProvider) {
            passenger_lat = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLatitude"));
            passenger_lon = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLongitude"));
            icon = R.drawable.ic_track_trip_org;
            markerIcon = VectorUtils.vectorToBitmap(getActContext(), icon, 0);
        } else {
            markerIcon = VectorUtils.vectorToBitmap(getActContext(), icon, ContextCompat.getColor(getApplicationContext(), R.color.black));
        }

        MarkerOptions marker_passenger_opt = new MarkerOptions().position(new LatLng(passenger_lat, passenger_lon));
        marker_passenger_opt.icon(markerIcon).anchor(0.5f, 1);
        getMap().addMarker(marker_passenger_opt).setFlat(false);


        checkUserLocation();

        startLocationTracker();
    }

    private void startLocationTracker() {
        if (this.getMap() == null) {
            return;
        }
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        GetLocationUpdates.getInstance().setTripStartValue(false, true, true, tripId);
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
    }

    public GoogleMap getMap() {
        return this.gMap;
    }

    private void setData() {

        double passenger_lat = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLatitude"));
        double passenger_lon = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLongitude"));

        addressTxt.setText(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"));
        addressTxt.setText(data_trip.get("tSaddress"));

        if (ServiceModule.IsTrackingProvider) {
            passenger_lat = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLatitude"));
            passenger_lon = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLongitude"));
        }
        setPassengerLocation("" + passenger_lat, "" + passenger_lon);
        SelectableRoundedImageView userImg = findViewById(R.id.userImg);

        if (data_trip != null && data_trip.containsKey("PPicName") && Utils.checkText(data_trip.get("PPicName"))) {
            vImage = data_trip.get("PPicName");
        }
        userImg.setVisibility(View.VISIBLE);
        String image_url;
        if (Utils.checkText(vImage)) {
            image_url = CommonUtilities.USER_PHOTO_PATH + data_trip.get("PassengerId") + "/" + vImage;
        } else {
            image_url = "Temp";
        }
        new LoadImage.builder(LoadImage.bind(image_url), userImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


        REQUEST_TYPE = data_trip.get("REQUEST_TYPE");
        tripId = data_trip.get("TripId");

        setPageName();
    }


    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            isCurrentLocationFocused = false;
            return;
        }

        if (gMap == null) {
            this.userLocation = location;
            return;
        }

        updateDriverMarker(new LatLng(location.getLatitude(), location.getLongitude()));

        this.userLocation = location;

        checkUserLocation();

        if (ServiceModule.IsTrackingProvider) {
            double orgLatitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLatitude"));
            double orgLongitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("orgLongitude"));
            builderTracking = new LatLngBounds.Builder();
            builderTracking.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
            builderTracking.include(new LatLng(orgLatitude, orgLongitude));

            JSONArray userList = generalFunc.getJsonArray(data_trip.get("userList"));
            if (userList != null) {
                for (int jk = 0; jk < userList.length(); jk++) {
                    JSONObject object = generalFunc.getJsonObject(userList, jk);
                    double lat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("vLatitude", object));
                    double lon = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("vLongitude", object));
                    if (lat != 0.0 && lon != 0.0) {
                        LatLng newLocation = new LatLng(lat, lon);
                        builderTracking.include(newLocation);
                        MarkerOptions moTrackTripHome = new MarkerOptions();
                        moTrackTripHome.position(newLocation);
                        moTrackTripHome.icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.ic_track_trip_home, 0)).anchor(0.5f, 0.5f).flat(true);
                        moTrackTripHome.title(generalFunc.getJsonValueStr("userName", object));
                        moTrackTripHome.snippet(generalFunc.getJsonValueStr("vAddress", object));
                        gMap.addMarker(moTrackTripHome);
                    }
                }
            }
            //if (isFirstMapMove) {
            int width = map.getView().getMeasuredWidth();
            int height = map.getView().getMeasuredHeight();
            height = (height - (int) (height * 0.40));
            int padding = (int) (width * 0.10); // offset from edges of the map 10% of screen

            gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builderTracking.build(), width, height, padding));
//                gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builderTracking.build(), Utils.dipToPixels(getActContext(), 40)));
            isFirstMapMove = false;
            //}
            return;
        }

        if (!ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP.equalsIgnoreCase("Yes")) {
            if (!data_trip.get("DestLocLatitude").equals("") && !data_trip.get("DestLocLatitude").equals("0") && !data_trip.get("DestLocLongitude").equals("") && !data_trip.get("DestLocLongitude").equals("0")) {

                double passenger_lat = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("DestLocLatitude"));
                double passenger_lon = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("DestLocLongitude"));
                LatLngBounds.Builder builder = new LatLngBounds.Builder();
                builder.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
                builder.include(new LatLng(passenger_lat, passenger_lon));
                gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), Utils.dipToPixels(getActContext(), 40)));

            }
        }

        if (ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP.equalsIgnoreCase("Yes") || (data_trip.get("DestLocLatitude").equals("") || data_trip.get("DestLocLatitude").equals("0") || data_trip.get("DestLocLongitude").equals("") || data_trip.get("DestLocLongitude").equals("0"))) {
            try {
                if (isFirstMapMove) {
                    getMap().moveCamera(generalFunc.getCameraPosition(location, gMap));
                    isFirstMapMove = false;
                } else {
                    getMap().animateCamera(generalFunc.getCameraPosition(location, gMap), 1000, null);
                }
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

        if (updateDirections != null) {
            updateDirections.changeUserLocation(location);
        } else {
            if (isIntializeDirectionUpdate) {
                Location destLoc = new Location("gps");
                destLoc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLatitude")));
                destLoc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLongitude")));
                updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
                updateDirections.scheduleDirectionUpdate();
            }
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == MyUtils.AUDIO_PERMISSION_REQ_CODE) {
            PermissionHandler.getInstance().initiateHandle(this, false, permissions, "", requestCode, requestCode);
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == MyUtils.AUDIO_PERMISSION_REQ_CODE) {
            if (MyApp.getInstance().checkMicWithStorePermission(generalFunc, false)) {
                PermissionHandler.getInstance().closeView();
            }
        } else if (requestCode == Utils.REQUEST_CODE_GPS_ON) {
            handleNoLocationDial();
        }
    }


    private void updateDriverMarker(final LatLng newLocation) {
        if (MyApp.getInstance().isMyAppInBackGround() || gMap == null) {
            return;
        }

        if (driverMarker == null) {

            if (ServiceModule.ServiceProviderProduct || REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX) || REQUEST_TYPE.equalsIgnoreCase("Bidding")) {

                String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValueStr("vImage", obj_userProfile);
                View marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.uberx_provider_maker_design, null);
                SelectableRoundedImageView providerImgView = (SelectableRoundedImageView) marker_view.findViewById(R.id.providerImgView);

                providerImgView.setImageResource(R.mipmap.ic_no_pic_user);

                final View finalMarker_view = marker_view;
                if (Utils.checkText(generalFunc.getJsonValueStr("vImage", obj_userProfile))) {

                    MarkerOptions markerOptions_driver = new MarkerOptions();
                    markerOptions_driver.position(newLocation);
                    markerOptions_driver.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view))).anchor(0.5f, 0.5f).flat(false);
                    driverMarker = gMap.addMarker(markerOptions_driver);

                    new LoadImage.builder(LoadImage.bind(image_url), providerImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).setPicassoListener(new LoadImage.PicassoListener() {
                        @Override
                        public void onSuccess() {
                            driverMarker.setIcon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view)));
                        }

                        @Override
                        public void onError() {
                            driverMarker.setIcon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view)));
                        }
                    }).build();

                    driverMarker.setFlat(false);
                    driverMarker.setAnchor(0.5f, 1);
                } else {
                    MarkerOptions markerOptions_driver = new MarkerOptions();
                    markerOptions_driver.position(newLocation);
                    markerOptions_driver.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view))).anchor(0.5f, 1.5f).flat(false);
                    driverMarker = gMap.addMarker(markerOptions_driver);
                    driverMarker.setFlat(false);
                    driverMarker.setAnchor(0.5f, 1);

                }
            } else {


                int iconId = R.mipmap.car_driver;

                if (data_trip.containsKey("vVehicleType")) {

                    if (data_trip.get("vVehicleType").equalsIgnoreCase("Ambulance")) {
                        iconId = R.mipmap.car_driver_ambulance;
                    } else if (data_trip.get("vVehicleType").equalsIgnoreCase("Bike")) {
                        iconId = R.mipmap.car_driver_1;
                    } else if (data_trip.get("vVehicleType").equalsIgnoreCase("Cycle")) {
                        iconId = R.mipmap.car_driver_2;
                    } else if (data_trip.get("vVehicleType").equalsIgnoreCase("Truck")) {
                        iconId = R.mipmap.car_driver_4;
                    } else if (data_trip.get("vVehicleType").equalsIgnoreCase("Fly")) {
                        iconId = R.mipmap.ic_fly_icon;
                    }
                }
                if (ServiceModule.IsTrackingProvider) {
                    iconId = R.mipmap.car_driver_7;
                }

                MarkerOptions markerOptions_driver = new MarkerOptions();
                markerOptions_driver.position(newLocation);
                markerOptions_driver.icon(BitmapDescriptorFactory.fromResource(iconId)).anchor(0.5f, 0.5f).flat(true);

                driverMarker = gMap.addMarker(markerOptions_driver);

            }

        }

        if (this.userLocation != null && newLocation != null) {
            LatLng currentLatLng = new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude());

            float rotation = driverMarker == null ? 0 : driverMarker.getRotation();

            if (MarkerAnim.currentLng != null) {
                rotation = (float) MarkerAnim.bearingBetweenLocations(MarkerAnim.currentLng, newLocation);
            } else {
                rotation = (float) MarkerAnim.bearingBetweenLocations(currentLatLng, newLocation);
            }

            if (ServiceModule.ServiceProviderProduct || REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX) || REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
                rotation = 0;
            }

            HashMap<String, String> previousItemOfMarker = MarkerAnim.getLastLocationDataOfMarker(driverMarker);

            HashMap<String, String> data_map = new HashMap<>();
            data_map.put("vLatitude", "" + newLocation.latitude);
            data_map.put("vLongitude", "" + newLocation.longitude);
            data_map.put("iDriverId", "" + generalFunc.getMemberId());
            data_map.put("RotationAngle", "" + rotation);
            data_map.put("LocTime", "" + System.currentTimeMillis());

            Location location = new Location("marker");
            location.setLatitude(newLocation.latitude);
            location.setLongitude(newLocation.longitude);

            if (MarkerAnim.toPositionLat.get("" + newLocation.latitude) == null || MarkerAnim.toPositionLong.get("" + newLocation.longitude) == null) {
                if (previousItemOfMarker.get("LocTime") != null && !previousItemOfMarker.get("LocTime").equals("")) {
                    long previousLocTime = GeneralFunctions.parseLongValue(0, previousItemOfMarker.get("LocTime"));
                    long newLocTime = GeneralFunctions.parseLongValue(0, data_map.get("LocTime"));

                    if (previousLocTime != 0 && newLocTime != 0) {
                        if ((newLocTime - previousLocTime) > 0 && !MarkerAnim.driverMarkerAnimFinished) {
                            MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                        } else if ((newLocTime - previousLocTime) > 0) {
                            MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                        }
                    } else if ((previousLocTime == 0 || newLocTime == 0) && MarkerAnim.driverMarkerAnimFinished == false) {
                        MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                    } else {
                        MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                    }
                } else if (!MarkerAnim.driverMarkerAnimFinished) {
                    MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                } else {
                    MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, data_map.get("LocTime"));
                }
            }
        }

    }


    private static Bitmap createDrawableFromView(Context context, View view) {
        DisplayMetrics displayMetrics = new DisplayMetrics();
        ((Activity) context).getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        view.setLayoutParams(new RelativeLayout.LayoutParams(RelativeLayout.LayoutParams.WRAP_CONTENT, RelativeLayout.LayoutParams.WRAP_CONTENT));
        view.measure(displayMetrics.widthPixels, displayMetrics.heightPixels);
        view.layout(0, 0, displayMetrics.widthPixels, displayMetrics.heightPixels);
        view.buildDrawingCache();
        Bitmap bitmap = Bitmap.createBitmap(view.getMeasuredWidth(), view.getMeasuredHeight(), Bitmap.Config.ARGB_8888);

        Canvas canvas = new Canvas(bitmap);
        view.draw(canvas);

        return bitmap;
    }


    private void setDriverStatusToArrived() {
        HashMap<String, String> parameters = new HashMap<>();
        if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            parameters.put("type", "UpdateDriverBiddingTaskStatus");
            parameters.put("iBiddingPostId", tripId);
            parameters.put("vTaskStatus", "Arrived");
        } else {
            parameters.put("type", "DriverArrived");
            parameters.put("TripId", tripId);
            parameters.put("iDriverId", generalFunc.getMemberId());
        }
        if (isPoolRide) {
            MyApp.getInstance().ispoolRequest = true;
        }

        if (userLocation != null) {
            parameters.put("vLatitude", "" + userLocation.getLatitude());
            parameters.put("vLongitude", "" + userLocation.getLongitude());
        } else if (GetLocationUpdates.getInstance().getLastLocation() != null) {
            parameters.put("vLatitude", "" + GetLocationUpdates.getInstance().getLastLocation().getLatitude());
            parameters.put("vLongitude", "" + GetLocationUpdates.getInstance().getLastLocation().getLongitude());
        }
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {
                MyApp.getInstance().ispoolRequest = false;

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {

                    JSONObject message = generalFunc.getJsonObject(Utils.message_str, responseStringObject);

                    data_trip.put("DestLocLatitude", generalFunc.getJsonValueStr("DLatitude", message));
                    data_trip.put("DestLocLongitude", generalFunc.getJsonValueStr("DLongitude", message));
                    data_trip.put("DestLocAddress", generalFunc.getJsonValueStr("DAddress", message));
                    data_trip.put("eTollSkipped", generalFunc.getJsonValueStr("eTollSkipped", message));
                    data_trip.put("vTripStatus", "Arrived");

                    stopProcess();
                    // MyApp.getInstance().restartWithGetDataApp();
                    MyApp.getInstance().refreshView(this, responseString);


                } else {
                    String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);
                    if (msg_str.equals("DO_RESTART") || msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                        generalFunc.restartApp();
                    } else {
                        arrivedSlideButton.resetButtonView(arrivedSlideButton.btnText.getText().toString());
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    }

                }
            } else {
                generalFunc.showError();
            }
        });


    }

    private void openEnterOtpView() {
        if (dialog_verify_via_otp != null) {
            dialog_verify_via_otp.dismiss();
            dialog_verify_via_otp = null;
        }
        dialog_verify_via_otp = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_verify_via_otp.setContentView(R.layout.verify_with_otp_layout);
        MTextView titleTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.titleTxt);
        MTextView cancelTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.cancelTxt);

        MTextView verifyOtpNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpNote);
        MTextView verifyOtpValidationNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpValidationNote);
        LinearLayout OtpAddArea = (LinearLayout) dialog_verify_via_otp.findViewById(R.id.OtpAddArea);
        MaterialEditText otpBox = (MaterialEditText) dialog_verify_via_otp.findViewById(R.id.otpBox);
        OtpView otp_view = (OtpView) dialog_verify_via_otp.findViewById(R.id.otp_verify_view);
        MButton btn_type2 = ((MaterialRippleLayout) dialog_verify_via_otp.findViewById(R.id.btn_type2)).getChildView();

        if (generalFunc.isRTLmode()) {
            otp_view.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        }
        int vRandomCode = generalFunc.parseIntegerValue(4, data_trip.get("vRandomCode"));
        String LBL_OTP_INVALID_TXT = generalFunc.retrieveLangLBl("", "LBL_OTP_INVALID_TXT");
        if (vRandomCode <= 6) {
            OtpAddArea.setVisibility(View.VISIBLE);
            otpBox.setVisibility(View.GONE);
            verifyOtpValidationNote.setText(LBL_OTP_INVALID_TXT);
            otp_view.setItemCount(generalFunc.parseIntegerValue(4, String.valueOf(vRandomCode)));
        } else {
            otpBox.setBothText("", generalFunc.retrieveLangLBl("OTP", "LBL_ENTER_OTP_TITLE_TXT"));
            OtpAddArea.setVisibility(View.GONE);
            otpBox.setVisibility(View.VISIBLE);
            otpBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        }

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));

        titleTxt.setText(generalFunc.retrieveLangLBl("Verify OTP", "LBL_OTP_VERIFICATION_TITLE_TXT"));
        verifyOtpNote.setText(generalFunc.retrieveLangLBl("Ask user to provide you an OTP.", "LBL_OTP_VERIFICATION_DESCRIPTION_TXT"));
        btn_type2.setEnabled(false);
//        btn_type2.setBackgroundColor(getResources().getColor(R.color.gray));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            isOtpVerified = false;
            isOtpVerificationDenied = true;
            if (dialog_verify_via_otp != null) {
                dialog_verify_via_otp.dismiss();
                dialog_verify_via_otp = null;
            }

            arrivedSlideButton.resetButtonView(arrivedSlideButton.btnText.getText().toString());

        });

        String vText = data_trip.get("vText");
        Logger.d("MD5_HASH", "Original  Values is ::" + vText);
        btn_type2.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            if (OtpAddArea.getVisibility() == View.VISIBLE) {
                String finalCode = Utils.getText(otp_view);

                boolean isCorrectCOde = Utils.checkText(finalCode) && generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde ? true : Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {
                    verifyOtpValidationNote.setVisibility(View.GONE);
                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }

                    isOtpVerified = true;
                    setDriverStatusToArrived();
                } else {
                    verifyOtpValidationNote.setVisibility(View.VISIBLE);
                }
            } else {
                String finalCode = Utils.getText(otpBox);
                boolean isCorrectCOde = Utils.checkText(finalCode) && generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde ? true : Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {

                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }
                    isOtpVerified = true;
                    setDriverStatusToArrived();
                }
            }
        });
        otp_view.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < otp_view.getItemCount()) {
                    btn_type2.setEnabled(false);
                    verifyOtpValidationNote.setVisibility(View.GONE);
                    otp_view.setLineColor(getResources().getColor(R.color.gray));
//                    btn_type2.setBackgroundColor(getResources().getColor(R.color.gray));
                }
            }
        });

        otpBox.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < vRandomCode) {
                    btn_type2.setEnabled(false);
                } else {
                    btn_type2.setEnabled(true);
                }
            }
        });

        otp_view.setOtpCompletionListener(otp -> {
            verifyOtpValidationNote.setVisibility(View.GONE);
            otp_view.setLineColor(getResources().getColor(R.color.appThemeColor_1));
            btn_type2.setEnabled(true);

        });
        otp_view.setCursorVisible(true);
        dialog_verify_via_otp.setCanceledOnTouchOutside(false);
        Window window = dialog_verify_via_otp.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_verify_via_otp.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);

        LayoutDirection.setLayoutDirection(dialog_verify_via_otp);
        dialog_verify_via_otp.show();
    }

    public boolean onCreateOptionsMenu(Menu menu) {
        if (ServiceModule.IsTrackingProvider) {
            return false;
        }
        MenuInflater menuInflater = getMenuInflater();
        menuInflater.inflate(R.menu.trip_accept_menu, menu);

        String msg;
        String msgCancel;
        if (REQUEST_TYPE != null && REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            msg = generalFunc.retrieveLangLBl("", "LBL_VIEW_USER_DETAIL");
            msgCancel = generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB");
        } else if (REQUEST_TYPE != null && (REQUEST_TYPE.equalsIgnoreCase("Deliver") || REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery))) {
            msg = generalFunc.retrieveLangLBl("", "LBL_VIEW_DELIVERY_DETAILS");
            msgCancel = generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY");
        } else if (REQUEST_TYPE != null && REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            msg = generalFunc.retrieveLangLBl("", "LBL_VIEW_USER_DETAIL");
            msgCancel = generalFunc.retrieveLangLBl("", "LBL_CANCEL_BIDDING");
        } else {
            msg = generalFunc.retrieveLangLBl("", "LBL_VIEW_PASSENGER_DETAIL");
            msgCancel = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TRIP");
        }

        menu.findItem(R.id.menu_passenger_detail).setTitle(msg);
        menu.findItem(R.id.menu_cancel_trip).setTitle(msgCancel);

        menu.findItem(R.id.menu_call).setTitle(generalFunc.retrieveLangLBl("Call", "LBL_CALL_ACTIVE_TRIP"));
        if (REQUEST_TYPE != null && REQUEST_TYPE.equals(Utils.CabGeneralType_UberX)) {
            String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
            if (!generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("") && generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.menu_specialInstruction).setTitle(generalFunc.retrieveLangLBl("Special Instruction", "LBL_TITLE_REQUESTED_SERVICES"));
            } else {
                menu.findItem(R.id.menu_specialInstruction).setTitle(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"));
            }
        }
        menu.findItem(R.id.menu_message).setTitle(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_ACTIVE_TRIP"));
        menu.findItem(R.id.menu_sos).setTitle(generalFunc.retrieveLangLBl("Emergency or SOS", "LBL_EMERGENCY_SOS_TXT")).setVisible(false);

        if (REQUEST_TYPE != null && REQUEST_TYPE.equals(Utils.CabGeneralType_UberX)) {
            menu.findItem(R.id.menu_passenger_detail).setVisible(true);
            menu.findItem(R.id.menu_call).setVisible(false);
            menu.findItem(R.id.menu_message).setVisible(false);
            menu.findItem(R.id.menu_bidding).setVisible(false);
            menu.findItem(R.id.menu_specialInstruction).setVisible(true);
            menu.findItem(R.id.menu_waybill_trip).setVisible(false);
        } else if (REQUEST_TYPE != null && REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            menu.findItem(R.id.menu_passenger_detail).setVisible(true);
            menu.findItem(R.id.menu_call).setVisible(false);
            menu.findItem(R.id.menu_message).setVisible(false);
            menu.findItem(R.id.menu_specialInstruction).setVisible(false);

            menu.findItem(R.id.menu_cancel_trip).setVisible(false);
            menu.findItem(R.id.menu_waybill_trip).setVisible(false);
            menu.findItem(R.id.menu_bidding).setTitle(generalFunc.retrieveLangLBl("", "LBL_REQUESTED_BIDDING")).setVisible(true);

        } else {

            boolean eFlyEnabled = data_trip.get("eFly").equalsIgnoreCase("Yes");
            boolean isWayBillEnabled = generalFunc.getJsonValue("WAYBILL_ENABLE", obj_userProfile) != null && generalFunc.getJsonValueStr("WAYBILL_ENABLE", obj_userProfile).equalsIgnoreCase("yes");

            menu.findItem(R.id.menu_passenger_detail).setVisible(true);
            menu.findItem(R.id.menu_call).setVisible(false);
            menu.findItem(R.id.menu_message).setVisible(false);
            menu.findItem(R.id.menu_bidding).setVisible(false);
            menu.findItem(R.id.menu_specialInstruction).setVisible(false);
            menu.findItem(R.id.menu_waybill_trip).setTitle(generalFunc.retrieveLangLBl("Way Bill", "LBL_MENU_WAY_BILL")).setVisible(isWayBillEnabled && !eFlyEnabled);
        }

        Utils.setMenuTextColor(menu.findItem(R.id.menu_sos), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_bidding), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_call), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_message), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_passenger_detail), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_cancel_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_waybill_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_specialInstruction), getResources().getColor(R.color.black));
        return true;
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_MENU) {
            return true;
        }
        return super.onKeyDown(keyCode, event);
    }

    private void getDeclineReasonsList() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetCancelReasons");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        parameters.put("eJobType", generalFunc.getJsonValue("eJobType", generalFunc.getJsonValue("TripDetails", obj_userProfile.toString())));

        if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            parameters.put("iBiddingPostId", tripId);
        } else {
            parameters.put("iTripId", data_trip.get("iTripId"));
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (Utils.checkText(responseString)) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    showDeclineReasonsAlert(responseStringObj);
                } else {
                    String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                    if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {

                        MyApp.getInstance().restartWithGetDataApp();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                    }
                }

            } else {
                generalFunc.showError();
            }
        });

    }

    private void showDeclineReasonsAlert(JSONObject responseString) {
        selCurrentPosition = -1;
        String titleDialog;
        if (Objects.requireNonNull(data_trip.get("eType")).equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            titleDialog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TRIP");
        } else if (Objects.requireNonNull(data_trip.get("eType")).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleDialog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB");
        } else if (Objects.requireNonNull(data_trip.get("eType")).equalsIgnoreCase("Bidding")) {
            titleDialog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_BIDDING");
        } else {
            titleDialog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY");
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));


        ArrayList<HashMap<String, String>> list = new ArrayList<>();
        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
        if (arr_msg != null) {

            for (int i = 0; i < arr_msg.length(); i++) {

                JSONObject obj_tmp = generalFunc.getJsonObject(arr_msg, i);


                HashMap<String, String> datamap = new HashMap<>();
                datamap.put("title", generalFunc.getJsonValueStr("vTitle", obj_tmp));
                datamap.put("id", generalFunc.getJsonValueStr("iCancelReasonId", obj_tmp));
                list.add(datamap);
            }

            HashMap<String, String> othermap = new HashMap<>();
            othermap.put("title", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
            othermap.put("id", "");
            list.add(othermap);

            MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
            MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
            MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
            MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
            ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
            subTitleTxt.setText(titleDialog);
            MTextView declinereasonBox = (MTextView) dialogView.findViewById(R.id.declinereasonBox);
            declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
            submitTxt.setClickable(false);
            submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));
            submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
            cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
            submitTxt.setOnClickListener(v -> {

                if (selCurrentPosition == -1) {
                    return;
                }

                if (!Utils.checkText(reasonBox) && selCurrentPosition == (list.size() - 1)) {
                    errorTextView.setVisibility(View.VISIBLE);
                    errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                    return;
                }

                new CancelTripDialog(getActContext(), data_trip, generalFunc, list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox), false, reasonBox.getText().toString().trim(), userLocation != null ? userLocation : GetLocationUpdates.getInstance().getLastLocation());

            });
            cancelTxt.setOnClickListener(v -> {
                Utils.hideKeyboard(getActContext());
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });

            cancelImg.setOnClickListener(v -> {
                Utils.hideKeyboard(getActContext());
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });

            declinereasonBox.setOnClickListener(v -> OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), list, OpenListView.OpenDirection.CENTER, true, position -> {
                selCurrentPosition = position;
                HashMap<String, String> mapData = list.get(position);
                errorTextView.setVisibility(View.GONE);
                declinereasonBox.setText(mapData.get("title"));
                if (selCurrentPosition == (list.size() - 1)) {
                    reasonBox.setVisibility(View.VISIBLE);
                    commentArea.setVisibility(View.VISIBLE);
                } else {
                    reasonBox.setVisibility(View.GONE);
                    commentArea.setVisibility(View.GONE);
                }
                submitTxt.setClickable(true);
                submitTxt.setTextColor(getResources().getColor(R.color.white));
            }).show(selCurrentPosition, "title"));

            dialog_declineOrder = builder.create();
            dialog_declineOrder.setCancelable(false);
            dialog_declineOrder.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
            LayoutDirection.setLayoutDirection(dialog_declineOrder);
            if (dialog_declineOrder != null && !this.isFinishing()) {
                dialog_declineOrder.show();
            }
        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
        }
    }

    @SuppressLint("NonConstantResourceId")
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        int itemId = item.getItemId();
        if (itemId == R.id.menu_passenger_detail) {
            if (REQUEST_TYPE.equals("Deliver")) {
                Bundle bn = new Bundle();
                bn.putString("TripId", tripId);
                bn.putSerializable("data_trip", data_trip);
                new ActUtils(getActContext()).startActWithData(ViewDeliveryDetailsActivity.class, bn);
            } else {
                new OpenPassengerDetailDialog(getActContext(), data_trip, generalFunc, false, new OpenPassengerDetailDialog.DialogListener() {
                    @Override
                    public void callClick() {
                        callArea.performClick();
                    }

                    @Override
                    public void msgClick() {
                        chatArea.performClick();
                    }
                });
            }
            return true;
        } else if (itemId == R.id.menu_cancel_trip) {
            getDeclineReasonsList();
            return true;
        } else if (itemId == R.id.menu_bidding) {
            Bundle bn1 = new Bundle();
            bn1.putString("iBiddingPostId", tripId);
            bn1.putBoolean("isViewOnly", true);
            new ActUtils(getActContext()).startActWithData(BiddingViewDetailsActivity.class, bn1);
            return true;
        } else if (itemId == R.id.menu_waybill_trip) {
            Bundle bn = new Bundle();
            bn.putSerializable("data_trip", data_trip);
            new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn);
            return true;
        } else if (itemId == R.id.menu_call) {
            callArea.performClick();
            return true;
        } else if (itemId == R.id.menu_message) {
            chatArea.performClick();
            return true;
        } else if (itemId == R.id.menu_specialInstruction) {
            String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
            if (!generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("") && generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("Yes")) {
                Bundle bundle = new Bundle();
                bundle.putString("iTripId", data_trip.get("iTripId"));
                new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bundle);
            } else {
                if (data_trip.get("tUserComment") != null && !data_trip.get("tUserComment").equals("")) {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"), data_trip.get("tUserComment"));
                } else {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"), generalFunc.retrieveLangLBl("", "LBL_NO_SPECIAL_INSTRUCTION"));
                }
            }
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    protected void onDestroy() {
        stopProcess();
        super.onDestroy();
    }

    private void stopProcess() {
        if (updateDirections != null) {
            updateDirections.releaseTask();
            updateDirections = null;
        }
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.retrieveInstance().stopLocationUpdates(this);
        }
    }

    private Context getActContext() {
        return DriverArrivedActivity.this; // Must be context of activity not application
    }

    private void openNavigationDialog(final String dest_lat, final String dest_lon) {
        MyCommonDialog.navigationDialog(getActContext(), generalFunc, () -> {
            try {
                String uri = "https://play.google.com/store/apps/details?id=com.google.android.apps.maps";
                if (MyCommonDialog.isPackageInstalled("com.google.android.apps.maps", getActContext().getPackageManager())) {
                    uri = "http://maps.google.com/maps?daddr=" + dest_lat + "," + dest_lon;
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
                } else {
                    commonCustomDialog(uri, R.drawable.ic_google_map,
                            generalFunc.retrieveLangLBl("", "LBL_GOOGLE_MAPS_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_GOOGLE_MAPS"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                }
            } catch (Exception e) {
                generalFunc.showMessage(timeTxt, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
            }
        }, () -> {
            try {
                String uri = "https://play.google.com/store/apps/details?id=com.waze";
                if (MyCommonDialog.isPackageInstalled("com.waze", getActContext().getPackageManager())) {
                    uri = "waze://?ll=" + dest_lat + "," + dest_lon + "&navigate=yes";
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
                } else {
                    commonCustomDialog(uri, R.drawable.ic_waze_map,
                            generalFunc.retrieveLangLBl("", "LBL_WAZE_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_WAZE"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                }
            } catch (Exception e) {
                generalFunc.showMessage(timeTxt, generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
            }
        }, () -> {
            if ((generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_RIDE", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_UFX", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_BIDDING", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase("Bidding")) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_DELIVERY", obj_userProfile).equalsIgnoreCase("Yes") && (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery) || REQUEST_TYPE.equalsIgnoreCase("Deliver")))) {
                openNavigationView(dest_lat, dest_lon, addressTxt.getText().toString().trim());
            }
        }, generalFunc.getJsonValueStr("GOOGLE_NAV_OPTION", obj_userProfile), obj_userProfile);
    }

    private void commonCustomDialog(String uri, int img, String title, String message, String positiveBtnTxt, String negativeBtnTxt) {
        CustomDialog customDialog = new CustomDialog(getActContext());
        customDialog.setDetails(title, message, positiveBtnTxt, negativeBtnTxt, false, img, false, 1, false);
        customDialog.createDialog();
        customDialog.setNegativeButtonClick(() -> {
        });
        customDialog.setPositiveButtonClick(() -> {
            //
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
        });
        customDialog.show();
    }

    private void openNavigationView(final String dest_lat, final String dest_lon, final String address) {
        Bundle bn = new Bundle();
        bn.putString("dest_lat", dest_lat);
        bn.putString("dest_lon", dest_lon);
        bn.putString("address", address);
        new ActUtils(getActContext()).startActWithData(NavigationMapActivity.class, bn);
    }

    private void updateTrackingTripStatus(String tripStatus) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateTrackingTripStatus");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iTrackServiceTripId", data_trip.get("iTrackServiceTripId"));
        parameters.put("TripStatus", tripStatus);
        parameters.put("tCancelReason", "");

        if (userLocation != null) {
            parameters.put("tEndLat", "" + userLocation.getLatitude());
            parameters.put("tEndLong", "" + userLocation.getLongitude());
        } else if (GetLocationUpdates.getInstance().getLastLocation() != null) {
            parameters.put("tEndLat", "" + GetLocationUpdates.getInstance().getLastLocation().getLatitude());
            parameters.put("tEndLong", "" + GetLocationUpdates.getInstance().getLastLocation().getLongitude());
        }
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            arrivedSlideButton.resetButtonView(arrivedSlideButton.btnText.getText().toString());

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    MyApp.getInstance().refreshView(this, responseString);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void setPassengerLocation(String passenger_lat, String passenger_lon) {
        this.passenger_lat = passenger_lat;
        this.passenger_lon = passenger_lon;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(DriverArrivedActivity.this);
        if (i == btn_type2.getId()) {
            btn_type2.setEnabled(false);
            setDriverStatusToArrived();
//                buildMsgOnArrivedBtn();
        } else if (i == R.id.callArea || i == R.id.chatArea) {

            boolean isAdmin = data_trip.get("eBookingFrom").equalsIgnoreCase("Admin");
            boolean isKiosk = data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk");
            boolean isUser = Utils.checkText(data_trip.get("iGcmRegId_U"));

            boolean isDefaultMedia = false;
            CommunicationManager.MEDIA media = CommunicationManager.MEDIA_TYPE;
            if (isAdmin && !isUser) {
                media = CommunicationManager.MEDIA.DEFAULT;
                isDefaultMedia = true;
            } else if (isKiosk && !isUser) {
                media = CommunicationManager.MEDIA.DEFAULT;
                isDefaultMedia = true;
            }

            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(data_trip.get("PassengerId"))
                    .setPhoneNumber(data_trip.get("PPhone"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(data_trip.get("PName"))
                    .setToMemberImage(data_trip.get("PPicName"))

                    .setMedia(media)
                    .setTripId(isDefaultMedia ? "" : data_trip.get("iTripId"))

                    .setBookingNo(data_trip.get("vRideNo"))
                    .setBid(REQUEST_TYPE.equalsIgnoreCase("Bidding"))
                    .build();

            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, i == R.id.chatArea ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);

        } else if (i == R.id.navigateAreaUP) {
            openNavigationDialog(passenger_lat, passenger_lon);
        } else if (view.getId() == userLocBtnImgView.getId()) {
            if (builderTracking == null) {
                builderTracking = new LatLngBounds.Builder();
                builderTracking.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
            }
            gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builderTracking.build(), Utils.dipToPixels(getActContext(), 40)));

        } else if (view.getId() == wherePickPointTxt.getId()) {

            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(data_trip.get("PassengerId"))
                    .setPhoneNumber(data_trip.get("PPhone"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(data_trip.get("PName"))
                    .setToMemberImage(data_trip.get("PPicName"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)

                    .setBookingNo(data_trip.get("vRideNo"))
                    .setTripId(data_trip.get("iTripId"))
                    .isForPickupPhotoRequest("Yes")
                    .setBid(REQUEST_TYPE.equalsIgnoreCase("Bidding"))
                    .build();

            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.CHAT);

        } else if (view.getId() == emeTapImgView.getId()) {
            if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                SafetyTools.getInstance().initiate(getActContext(), generalFunc, tripId, REQUEST_TYPE);
                SafetyTools.getInstance().safetyToolsDialog(false);
            } else {
                Bundle bn = new Bundle();
                bn.putString("TripId", tripId);
                new ActUtils(getActContext()).startActWithData(ConfirmEmergencyTapActivity.class, bn);
            }

        } else if (view.getId() == deliveryInfoView.getId()) {
            Bundle bn = new Bundle();
            bn.putString("TripId", tripId);
            bn.putString("Status", "driverArrived");
            bn.putSerializable("TRIP_DATA", data_trip);
            new ActUtils(getActContext()).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);

        } else if (view.getId() == endTxt.getId()) {
            if (ServiceModule.IsTrackingProvider) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_TRIP_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                    if (buttonId == 1) {
                        updateTrackingTripStatus("Cancelled");
                    }
                });
            } else {
                getDeclineReasonsList();
            }
        }
    }


    private GradientDrawable getRoundBG(String color) {
        int strokeWidth = 2;
        int strokeColor = Color.parseColor("#CCCACA");
        int fillColor = Color.parseColor(color);
        GradientDrawable gD = new GradientDrawable();
        gD.setColor(fillColor);
        gD.setShape(GradientDrawable.RECTANGLE);
        gD.setCornerRadius(100);
        gD.setStroke(strokeWidth, strokeColor);
        return gD;
    }
}