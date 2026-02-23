package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.location.Location;
import android.os.Bundle;
import android.text.InputType;
import android.view.Gravity;
import android.view.View;
import android.widget.LinearLayout;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.google.android.gms.maps.CameraUpdate;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityTripRatingBinding;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;
import java.util.Objects;

public class TripRatingActivity extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener {

    private GoogleMap gMap;

    private Location userLocation;

    private MButton btn_type2;
    private String iTripId_str, vImage = "", iOrderId = "";

    private HashMap<String, String> data_trip;
    private boolean isSubmitClicked = false;
    private ActivityTripRatingBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_trip_rating);


        data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        iTripId_str = data_trip.get("TripId");
        iOrderId = data_trip.get("LastOrderId");
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();


        ((SupportMapFragment) Objects.requireNonNull(getSupportFragmentManager().findFragmentById(R.id.mapV2))).getMapAsync(this);

        if (data_trip != null && data_trip.containsKey("PPicName") && Utils.checkText(data_trip.get("PPicName"))) {
            vImage = CommonUtilities.USER_PHOTO_PATH + data_trip.get("PassengerId") + "/"
                    + data_trip.get("PPicName");
            new LoadImage.builder(LoadImage.bind(vImage), binding.userImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).build();
        } else {
            vImage = "errorImage";
            new LoadImage.builder(LoadImage.bind(vImage), binding.userImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).build();
        }


        binding.toolbarInclude.backImgView.setVisibility(View.GONE);

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) binding.toolbarInclude.titleTxt.getLayoutParams();
        params.setMargins(Utils.dipToPixels(getActContext(), 20), 0, 0, 0);
        binding.toolbarInclude.titleTxt.setLayoutParams(params);

        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(btn_type2);


        binding.commentBox.setHideUnderline(true);

        binding.commentBox.setSingleLine(false);
        binding.commentBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        binding.commentBox.setGravity(Gravity.TOP);
        setLabels();

        binding.nameTxt.setText(data_trip.get("PName"));

        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (Utils.checkText(restratValue_str) &&  restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }

        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");

    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    @Override
    protected void onDestroy() {
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        super.onDestroy();

    }

    @Override
    protected void onPause() {
        super.onPause();


    }

    public void setLabels() {
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RATING"));
        binding.pageTitle.setText(generalFunc.retrieveLangLBl("", "LBL_DRIVER_RATING_TITLE"));
        binding.rateTxt.setText(generalFunc.retrieveLangLBl("Rate", "LBL_RATE"));
        binding.commentBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_FEEDBACK"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_SUBMIT_TXT"));
    }

    public void submitRating() {
        isSubmitClicked = true;
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "submitRating");
        parameters.put("iGeneralUserId", generalFunc.getMemberId());
        parameters.put("tripID", iTripId_str);
        parameters.put("rating", "" + binding.ratingBar.getRating() + "");
        parameters.put("message", Utils.getText(binding.commentBox));
        parameters.put("UserType", Utils.app_type);
        parameters.put("iMemberId", generalFunc.getMemberId());

        if (!iOrderId.equalsIgnoreCase("")) {
            parameters.put("iOrderId", iOrderId);
            parameters.put("eFromUserType", Utils.app_type);
            parameters.put("eToUserType", Utils.passenger_app_type);
            parameters.put("eSystem", Utils.eSystem_Type);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        isSubmitClicked = true;
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            isSubmitClicked = false;
                            showBookingAlert(responseString);
                        } else {
                            isSubmitClicked = false;
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        isSubmitClicked = false;
                        generalFunc.showError();
                    }
                });

    }

    public void showBookingAlert(String responseString) {


        String eType = generalFunc.getJsonValue("eType", responseString);
        String titleTxt;
        String mesasgeTxt;
        if (generalFunc.getJsonValue("eType", responseString).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleTxt = generalFunc.retrieveLangLBl("Booking Successful", "LBL_JOB_FINISHED");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_JOB_FINISHED_TXT");
        } else if (eType.equalsIgnoreCase("Deliver") || eType.equals(Utils.eType_Multi_Delivery)) {
            titleTxt = generalFunc.retrieveLangLBl("Booking Successful", "LBL_DELIVERY_SUCCESS_FINISHED");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_FINISHED_TXT");
        } else {
            titleTxt = generalFunc.retrieveLangLBl("Booking Successful", "LBL_SUCCESS_FINISHED");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_TRIP_FINISHED_TXT");
        }
        if (!iOrderId.equalsIgnoreCase("")) {
            titleTxt = generalFunc.retrieveLangLBl("Booking Successful", "LBL_SUCCESS_FINISHED_DRDL");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_FINISHED_DELIVERY_TXT");

        }
        if (data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
            titleTxt = generalFunc.retrieveLangLBl("", "LBL_TASK_COMPLETED_TXT");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_TASK_FINISHED_TXT");
        }

        if (generalFunc.getJsonValue("isMedicalServiceTrip", responseString).equalsIgnoreCase("Yes")) {
            titleTxt = generalFunc.retrieveLangLBl("", "LBL_SUCCESS_FINISHED");
            mesasgeTxt = generalFunc.retrieveLangLBl("", "LBL_MEDICAL_TRIP_FINISHED_TXT");
        }
        binding.ratingArea.setVisibility(View.GONE);
        CustomDialog customDialog = new CustomDialog(this);
        customDialog.setDetails(titleTxt, mesasgeTxt, generalFunc.retrieveLangLBl("Ok", "LBL_OK_THANKS"), "", false, R.drawable.ic_correct_2, false, 1, true);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {
            generalFunc.saveGoOnlineInfo();
            MyApp.getInstance().refreshView(this, responseString);
        });
        if (!this.isFinishing()) {
            customDialog.show();
        }

    }


    public Context getActContext() {
        return TripRatingActivity.this;
    }

    @SuppressLint("MissingSuperCall")
    @Override
    public void onBackPressed() {
        return;
    }

    @Override
    public void onLocationUpdate(Location location) {
        this.userLocation = location;
        CameraUpdate cameraPosition = generalFunc.getCameraPosition(userLocation, gMap);

        if (cameraPosition != null) {
            getMap().moveCamera(cameraPosition);

        }
    }

    public CameraPosition cameraForUserPosition() {


        if (userLocation == null) {
            return null;
        }


        double currentZoomLevel = getMap().getCameraPosition().zoom;

        if (Utils.defaultZomLevel > currentZoomLevel) {
            currentZoomLevel = Utils.defaultZomLevel;
        }
        return new CameraPosition.Builder().target(new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude())).bearing(getMap().getCameraPosition().bearing)
                .zoom((float) currentZoomLevel).build();


    }

    public GoogleMap getMap() {
        return this.gMap;
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;
        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(true);
        }

        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        gMap.getUiSettings().setScrollGesturesEnabled(false);
        gMap.getUiSettings().setZoomGesturesEnabled(false);

        GetLocationUpdates.getInstance().startLocationUpdates(this, this);

    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(TripRatingActivity.this);

        if (i == btn_type2.getId()) {
            if (!isSubmitClicked) {

                if (binding.ratingBar.getRating() < 0.5) {
                    generalFunc.showMessage(generalFunc.getCurrentView(TripRatingActivity.this),
                            generalFunc.retrieveLangLBl("", "LBL_ERROR_RATING_DIALOG_TXT"));
                    return;
                }
                if (data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
                    submitBiddingRating();
                } else {
                    submitRating();
                }
            }
        }
    }


    private void submitBiddingRating() {
        isSubmitClicked = true;
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "submitRatingBiddingService");
        parameters.put("iBiddingPostId", data_trip.get("TripId"));
        parameters.put("rating", "" + binding.ratingBar.getRating() + "");
        parameters.put("message", Utils.getText(binding.commentBox));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {
                        isSubmitClicked = true;
                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            isSubmitClicked = false;
                            showBookingAlert(responseString);
                        } else {
                            isSubmitClicked = false;
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        isSubmitClicked = false;
                        generalFunc.showError();
                    }
                });

    }
}
