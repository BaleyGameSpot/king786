package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.location.Address;
import android.location.Geocoder;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.annotation.OptIn;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.media3.common.util.UnstableApi;
//import androidx.navigation.Navigator;

import com.activity.ParentActivity;
import com.dialogs.BottomInfoDialog;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.google.android.libraries.navigation.DisplayOptions;
import com.google.android.libraries.navigation.ListenableResultFuture;
import com.google.android.libraries.navigation.NavigationApi;
import com.google.android.libraries.navigation.Navigator;
import com.google.android.libraries.navigation.RoutingOptions;
import com.google.android.libraries.navigation.StylingOptions;
import com.google.android.libraries.navigation.SupportNavigationFragment;
import com.google.android.libraries.navigation.Waypoint;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityNavigationMapBinding;
import com.utils.Logger;
import com.utils.MyUtils;

import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

public class NavigationMapActivity extends ParentActivity {

    private ActivityNavigationMapBinding binding;

    private SupportNavigationFragment mNavFragment;
    @Nullable
    private Navigator mNavigator;

    private RoutingOptions mRoutingOptions;

    @Nullable
    private ListenableResultFuture<Navigator.RouteStatus> pendingRoute;

    private DisplayOptions displayOption;

    @Nullable
    private Navigator.ArrivalListener arrivalListener;
    private GoogleMap gMap;
    private String address;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_navigation_map);

        initial();
        click();

        //Navigator
        NavigationApi.getNavigator(this, new NavigationApi.NavigatorListener() {
            @SuppressLint("MissingPermission")
            @Override
            public void onNavigatorReady(Navigator navigator) {
                mNavigator = navigator;
                mNavFragment = (SupportNavigationFragment) getSupportFragmentManager().findFragmentById(R.id.navigation_fragment);
                // Set the camera to follow the device location with 'TILTED' driving view.
                mNavFragment.getMapAsync(googleMap -> {
                    gMap = googleMap;
                    googleMap.followMyLocation(GoogleMap.CameraPerspective.TILTED);
                    googleMap.getUiSettings().setCompassEnabled(false);
                    googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(getApplicationContext(), R.raw.map_style));
                });
                // Set the travel mode (DRIVING, WALKING, CYCLING, TWO_WHEELER, or TAXI).
                mRoutingOptions = new RoutingOptions();
                mRoutingOptions.travelMode(RoutingOptions.TravelMode.DRIVING);
                mNavigator.setHeadsUpNotificationEnabled(true);
                mNavFragment.setSpeedLimitIconEnabled(true);
                mNavFragment.setHeaderEnabled(true);
                mNavFragment.setEtaCardEnabled(false);
                mNavFragment.setSpeedometerEnabled(true);
                mNavFragment.setTrafficIncidentCardsEnabled(true);
                mNavFragment.setMenuVisibility(true);
                mNavFragment.setTripProgressBarEnabled(false);
                mNavFragment.setTrafficPromptsEnabled(true);
                mNavFragment.setAllowEnterTransitionOverlap(true);
                mNavFragment.setAllowReturnTransitionOverlap(true);
                mNavFragment.setRecenterButtonEnabled(false);
                displayOption = new DisplayOptions();
                mNavFragment.setStylingOptions(new StylingOptions()
                        .primaryDayModeThemeColor(ContextCompat.getColor(getActContext(), R.color.dark_green))
                        .secondaryDayModeThemeColor(ContextCompat.getColor(getActContext(), R.color.dark_green_secondary))
                        .primaryNightModeThemeColor(ContextCompat.getColor(getActContext(), R.color.dark_green))
                        .secondaryNightModeThemeColor(ContextCompat.getColor(getActContext(), R.color.dark_green_secondary))
                        .headerLargeManeuverIconColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerSmallManeuverIconColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerNextStepTextColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerNextStepTextSize(25f)
                        .headerDistanceValueTextColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerDistanceUnitsTextColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerDistanceValueTextSize(25f)
                        .headerDistanceUnitsTextSize(22f)
                        .headerInstructionsTextColor(ContextCompat.getColor(getActContext(), R.color.white))
                        .headerInstructionsFirstRowTextSize(29f)
                        .headerInstructionsSecondRowTextSize(25f)
                        .headerGuidanceRecommendedLaneColor(ContextCompat.getColor(getActContext(), R.color.white)));
                if (intCheck.isNetworkConnected() && intCheck.check_int()) {
                    navigateToPlace(mRoutingOptions, displayOption);
                }
            }

            @Override
            public void onError(@NavigationApi.ErrorCode int errorCode) {
                switch (errorCode) {
                    case NavigationApi.ErrorCode.NOT_AUTHORIZED ->
                            displayMessage("Error loading Navigation SDK: Your API key is invalid or not authorized to use the Navigation SDK.");
                    case NavigationApi.ErrorCode.TERMS_NOT_ACCEPTED ->
                            displayMessage("Error loading Navigation SDK: User did not accept the Navigation Terms of Use.");
                    case NavigationApi.ErrorCode.NETWORK_ERROR ->
                            displayMessage("Error loading Navigation SDK: Network error.");
                    case NavigationApi.ErrorCode.LOCATION_PERMISSION_MISSING ->
                            displayMessage("Error loading Navigation SDK: Location permission is missing.");
                    default -> displayMessage("Error loading Navigation SDK: " + errorCode);
                }
            }
        });
    }

    private Context getActContext() {
        return NavigationMapActivity.this;
    }

    @SuppressLint("MissingPermission")
    private void click() {
        binding.exitArea.setOnClickListener(view -> {
            onBackPressed();
        });
        binding.audioArea.setOnClickListener(view -> {
            binding.snackbarTxt.setVisibility(View.GONE);
            binding.audioView.setVisibility(View.VISIBLE);
            binding.audioArea.setVisibility(View.GONE);
        });
        binding.silentAudio.setOnClickListener(view -> {
            mNavigator.setAudioGuidance(Navigator.AudioGuidance.SILENT);
            binding.audioArea.setVisibility(View.VISIBLE);
            binding.audioArea.setImageResource(R.drawable.ic_silent_audio);
            binding.silentAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.appThemeColor_1)));
            binding.startAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.onlyAlertAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.audioView.setVisibility(View.GONE);
            /*binding.snackbarTxt.setVisibility(View.VISIBLE);
            binding.snackbarTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STOP_AUDIO"));
            new Handler().postDelayed(() -> {
                binding.snackbarTxt.setVisibility(View.GONE);
            }, 2000);*/

        });
        binding.startAudio.setOnClickListener(view -> {
            mNavigator.setAudioGuidance(Navigator.AudioGuidance.VOICE_ALERTS_AND_GUIDANCE);
            binding.audioArea.setVisibility(View.VISIBLE);
            binding.audioArea.setImageResource(R.drawable.ic_start_audio);
            binding.startAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.appThemeColor_1)));
            binding.silentAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.onlyAlertAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.audioView.setVisibility(View.GONE);
            /*binding.snackbarTxt.setVisibility(View.VISIBLE);
            binding.snackbarTxt.setText(generalFunc.retrieveLangLBl("", "LBL_START_AUDIO"));
            new Handler().postDelayed(() -> {
                binding.snackbarTxt.setVisibility(View.GONE);
            }, 2000);*/
        });
        binding.onlyAlertAudio.setOnClickListener(view -> {
            mNavigator.setAudioGuidance(Navigator.AudioGuidance.VOICE_ALERTS_ONLY);
            binding.audioArea.setVisibility(View.VISIBLE);
            binding.audioArea.setImageResource(R.drawable.ic_alert_audio);
            binding.onlyAlertAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.appThemeColor_1)));
            binding.startAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.silentAudio.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(this, R.color.black)));
            binding.audioView.setVisibility(View.GONE);
            /*binding.snackbarTxt.setVisibility(View.VISIBLE);
            binding.snackbarTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ALERT_ONLY_AUDIO"));
            new Handler().postDelayed(() -> {
                binding.snackbarTxt.setVisibility(View.GONE);
            }, 2000);*/
        });
        binding.zoomArea.setOnClickListener(v -> gMap.animateCamera(CameraUpdateFactory.zoomOut()));
        binding.compassArea.setOnClickListener(v -> {
            binding.northArea.setVisibility(View.VISIBLE);
            binding.compassArea.setVisibility(View.GONE);
            gMap.followMyLocation(GoogleMap.CameraPerspective.TILTED);
        });
        binding.northArea.setOnClickListener(v -> {
            binding.northArea.setVisibility(View.GONE);
            binding.compassArea.setVisibility(View.VISIBLE);
            gMap.followMyLocation(GoogleMap.CameraPerspective.TOP_DOWN_NORTH_UP);
        });
        binding.recenterBtn.setOnClickListener(v -> {
            gMap.followMyLocation(GoogleMap.CameraPerspective.TILTED);
            binding.recenterBtn.setVisibility(View.GONE);
        });
    }

    private void initial() {
        binding.recenterBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RECENTER"));


    }

    private void displayMessage(String s) {
        generalFunc.showGeneralMessage("", s);
    }

    @Override
    public void onBackPressed() {
        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Exit Navigation", "LBL_EXIT_NAVIGATION_TITLE_TXT"), generalFunc.retrieveLangLBl("", "LBL_WANT_EXIT_NAVIGATION_TXT"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"),
                generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> {
                    if (buttonId == 1) {
                        if (pendingRoute != null) {
                            pendingRoute.cancel(true);
                        }
                        if (mNavigator != null) {
                            mNavigator.clearDestinations();
                            mNavigator.stopGuidance();
                            mNavigator.setTaskRemovedBehavior(Navigator.TaskRemovedBehavior.QUIT_SERVICE);
                            mNavigator.cleanup();
                            mNavigator.setHeadsUpNotificationEnabled(false);
                            if (arrivalListener != null) {
                                mNavigator.removeArrivalListener(arrivalListener);
                            }
                        }
                        super.onBackPressed();
                    }
                });

    }

    private void navigateToPlace(@NonNull RoutingOptions travelMode, DisplayOptions displayOption) {
        Waypoint destination;
        double passenger_lat = GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("dest_lat"));
        double passenger_lon = GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("dest_lon"));

        List<Address> addresses = null;
        Geocoder geocoder = new Geocoder(this, Locale.getDefault());

        try {
            addresses = geocoder.getFromLocation(passenger_lat, passenger_lon, 1); // Here 1 represent max location result to returned, by documents it recommended 1 to 5

        } catch (IOException ignored) {
        }

        assert addresses != null;
        address = addresses.get(0).getAddressLine(0);

        try {

            destination = Waypoint.builder()
                    .setLatLng(passenger_lat, passenger_lon)
                    .setTitle(getIntent().getStringExtra("address"))
                    .setVehicleStopover(true)
                    .build();
        } catch (Exception e) {
            displayMessage("Error starting navigation: Place ID is not supported.");
            return;
        }
        // Create a future to await the result of the asynchronous navigator task.
        pendingRoute = mNavigator.setDestination(destination, travelMode, displayOption);

        // Define the action to perform when the SDK has determined the route.
        pendingRoute.setOnResultListener(code -> {
            switch (code) {
                case OK -> {
                    // Hide the toolbar to maximize the navigation UI.
                    if (getActionBar() != null) {
                        getActionBar().hide();
                    }
                    // Enable voice audio guidance (through the device speaker).
                    mNavigator.setAudioGuidance(Navigator.AudioGuidance.VOICE_ALERTS_AND_GUIDANCE);
                    // Simulate vehicle progress along the route for demo/debug builds.
                    /*if (BuildConfig.DEBUG) {
                        mNavigator.getSimulator().simulateLocationsAlongExistingRoute(
                                new SimulationOptions().speedMultiplier(5));
                    }*/
                    // Start turn-by-turn guidance along the current route.
                    mNavigator.startGuidance();
                    arrivalListener = arrivalEvent -> {
                        if (!isFinishing()) {
                            mNavigator.stopGuidance();
                            CustomDialog customDialog = new CustomDialog(this);
                            customDialog.setDetails(generalFunc.retrieveLangLBl(" ", "LBL_GOOGLE_NAVIGATION_ARRIVE"), address, generalFunc.retrieveLangLBl(" ", "LBL_OK"), "", false, R.drawable.ic_correct_2, false, 1, true);
                            customDialog.createDialog();
                            customDialog.setPositiveButtonClick(() -> {
                                MyApp.getInstance().restartWithGetDataApp();
                            });
                            customDialog.show();
                        }
                    };
                    mNavigator.addArrivalListener(arrivalListener);
                    Logger.e("TestData1", "onRemainingTimeOrDistanceChanged: Time or distance estimate" + " has changed." + mNavigator.getCurrentTimeAndDistance());
                    mNavigator.addRemainingTimeOrDistanceChangedListener(60, 100, new Navigator.RemainingTimeOrDistanceChangedListener() {
                        @SuppressLint("SetTextI18n")
                        @Override
                        public void onRemainingTimeOrDistanceChanged() {
                            mNavigator.getCurrentTimeAndDistance().getDelaySeverity();
                            binding.timeArea.setText(getTimeTxt(mNavigator.getCurrentTimeAndDistance().getSeconds() / 60));
                            binding.distanceArea.setText(getRouteDistance(mNavigator.getCurrentTimeAndDistance().getMeters()) + " . " + addRemainingTimeToCurrentTime(mNavigator.getCurrentTimeAndDistance().getSeconds()));
                        }
                    });
                    gMap.setOnCameraChangeListener(cameraPosition -> {
                        // Get current zoom level
                        binding.recenterBtn.setVisibility(View.VISIBLE);
                    });
                }
                case NO_ROUTE_FOUND ->
                        displayMessage(generalFunc.retrieveLangLBl("Ok", "LBL_SOMETHING_WENT_WRONG_MSG"));
                case NETWORK_ERROR ->
                        displayMessage(generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
                case ROUTE_CANCELED -> displayMessage("Error starting navigation: Route canceled.");
                default -> displayMessage("Error starting navigation: " + code);
            }
        });


    }

    @OptIn(markerClass = UnstableApi.class)
    private String addRemainingTimeToCurrentTime(long remainingTimeInSeconds) {

        long currentTimeMillis = System.currentTimeMillis();
        long remainingTimeMillis = TimeUnit.SECONDS.toMillis(remainingTimeInSeconds);
        long expectedTimeMillis = currentTimeMillis + remainingTimeMillis;

        SimpleDateFormat sdf = new SimpleDateFormat("hh:mm aa", MyUtils.getLocale());
        return sdf.format(new Date(expectedTimeMillis));
    }

    private String formatHoursAndMinutes(int totalMinutes) {
        String minutes = Integer.toString(totalMinutes % 60);
        minutes = minutes.length() == 1 ? "0" + minutes : minutes;
        return (totalMinutes / 60) + ":" + minutes;
    }


    private String getTimeTxt(int duration) {
        if (duration < 1) {
            duration = 1;
        }
        String timeToreach = "" + duration;

        timeToreach = duration >= 60 ? formatHoursAndMinutes(duration) : timeToreach;
        String durationTxt = (duration < 60 ? generalFunc.retrieveLangLBl("", "LBL_MINS_SMALL") : generalFunc.retrieveLangLBl("", "LBL_HOUR_TXT"));
        durationTxt = duration == 1 ? generalFunc.retrieveLangLBl("", "LBL_MIN_SMALL") : durationTxt;
        durationTxt = duration > 120 ? generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") : durationTxt;
        return timeToreach + " " + durationTxt;
    }

    private String getRouteDistance(double distance) {
        distance = distance / 1000;
        String eUnit = generalFunc.getJsonValueStr("eUnit", obj_userProfile);
        if (!eUnit.equalsIgnoreCase("KMs")) {
            distance = distance * 0.621371;
        }
        distance = generalFunc.round(distance, 2);
        if (eUnit.equalsIgnoreCase("KMs")) {
            return distance + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT");
        } else {
            return distance + "  " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT");
        }
    }
}