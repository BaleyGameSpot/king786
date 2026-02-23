package com.act.rideSharingPro;

import static com.general.files.UpdateDirections.formatHoursAndMinutes;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.location.LocationManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
import android.view.View;
import android.widget.ImageView;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.app.ActivityCompat;
import androidx.databinding.DataBindingUtil;

import com.act.ConfirmEmergencyTapActivity;
import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.general.files.RecurringTask;
import com.general.files.UpdateDirections;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRideShareActiveTripBinding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.Polyline;
import com.map.helper.MarkerAnim;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.service.model.EventInformation;
import com.utils.MapUtils;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideShareActiveTripActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback, GetLocationUpdates.LocationUpdates, RecurringTask.OnTaskRunCalled {

    private ActivityRideShareActiveTripBinding binding;
    private MTextView titleTxt, addressTxt;
    private GeoMapLoader.GeoMap gMap;
    private JSONObject tridData;
    private Marker dest_marker, driver_marker;
    private int startDestIcon, endDestinationIcon, carIcon;
    private UpdateDirections updateDirections;
    private String startDesLat, startDestLong, distance = "", time = "";
    private MarkerAnim animateMarker;
    private Location driverLocation;
    private GetLocationUpdates getLastLocation;
    private Polyline route_polyLine;
    private RecurringTask updateFreqTask;
    private boolean isFirstTime = true;
    private String currentRideState, publishRideId;
    private HashMap<String, String> mapData;
    private boolean isFromPublishRide = false;
    private int DESTINATION_UPDATE_TIME_INTERVAL;
    private boolean isOkResult = false;
    private GenerateAlertBox bgAlertBox;
    private long notification_permission_launch_time = -1;
    private final ActivityResultLauncher<Intent> settingLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (result.getResultCode() == Activity.RESULT_OK) {
            if (bgAlertBox != null) {
                bgAlertBox.closeAlertBox();
            }
            stopLocationUpdates();
            GetLocationUpdates.locationResolutionAsked = false;
            getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
        }
    });
    private final ActivityResultLauncher<String> backgroundLocationLauncher = registerForActivityResult(new ActivityResultContracts.RequestPermission(), isGranted -> {
        if (bgAlertBox != null) {
            bgAlertBox.closeAlertBox();
        }
        if (!isGranted) {
            if ((System.currentTimeMillis() - notification_permission_launch_time) < 500) {
                openSetting();
            }
        }
    });

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_ride_share_active_trip);

        isFromPublishRide = getIntent().getBooleanExtra("isFromPublishRide", false);
        try {
            if (getIntent().hasExtra("tripData")) {
                tridData = new JSONObject(getIntent().getStringExtra("tripData"));
            }
            if (getIntent().hasExtra("publishRideId")) {
                publishRideId = getIntent().getStringExtra("publishRideId");
            }

        } catch (JSONException e) {
            throw new RuntimeException(e);
        }

        initialization();
        setLabels();
        updateViewVisibility();
        setViewClickListener();
    }

    private void initialization() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        titleTxt = findViewById(R.id.titleTxt);
        addressTxt = findViewById(R.id.addressTxt);

        (new GeoMapLoader(this, R.id.mapFragmentContainer)).bindMap(this);

        binding.startTripSlideButton.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
        binding.endTripSlideButton.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);

        startDestIcon = R.mipmap.ic_source_marker;
        endDestinationIcon = R.mipmap.ic_dest_marker;
        carIcon = R.mipmap.car_driver;
        animateMarker = new MarkerAnim();
        DESTINATION_UPDATE_TIME_INTERVAL = (int) ((generalFunc.parseDoubleValue(2, generalFunc.getJsonValue("DESTINATION_UPDATE_TIME_INTERVAL", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)))) * 60 * 1000);
    }

    @SuppressLint("SetTextI18n")
    private void updateViewVisibility() {
        if (isFromPublishRide) {
            binding.driverName.setVisibility(View.GONE);
            binding.navigateArea.setVisibility(View.VISIBLE);
            binding.callview.setVisibility(View.INVISIBLE);
            binding.navigationViewToolBar.setVisibility(View.VISIBLE);
            titleTxt.setText(generalFunc.retrieveLangLBl("En Route", "LBL_EN_ROUTE_TXT"));
        } else {
            binding.driverName.setVisibility(View.VISIBLE);
            binding.navigateArea.setVisibility(View.GONE);
            binding.endTripSlideButton.setVisibility(View.GONE);
            binding.startTripSlideButton.setVisibility(View.GONE);
            binding.emeTapImgView.setVisibility(View.GONE);
            binding.deliveryInfoView.setVisibility(View.GONE);
            binding.navigationViewToolBar.setVisibility(View.GONE);
            binding.navigationViewArea.setVisibility(View.GONE);
            binding.callview.setVisibility(View.VISIBLE);
            titleTxt.setText(generalFunc.retrieveLangLBl("Track Driver", "LBL_RIDE_SHARE_TRACK_DRIVER"));
            binding.driverName.setText(generalFunc.retrieveLangLBl("Driver Name", "LBL_DRIVER_NAME") + " : " + mapData.get("DriverName"));
        }
    }

    private void setLabels() {
        if (isFromPublishRide) {
            if (tridData != null) {
                startDesLat = generalFunc.getJsonValueStr("Latitude", tridData);
                startDestLong = generalFunc.getJsonValueStr("Longitude", tridData);
            }
        } else {
            mapData = (HashMap<String, String>) getIntent().getSerializableExtra("mapData");
            if (mapData != null) {
                startDesLat = mapData.get("tStartLat");
                startDestLong = mapData.get("tStartLong");
            }
        }
        if (tridData != null) {
            try {
                currentRideState = tridData.getString("RideState");
                if (currentRideState.equalsIgnoreCase("MarkAsPickup")) {
                    addressTxt.setText(tridData.getString("Location"));
                    binding.emeTapImgView.setVisibility(View.GONE);
                    binding.startTripSlideButton.setVisibility(View.VISIBLE);
                    binding.endTripSlideButton.setVisibility(View.GONE);
                    binding.startTripSlideButton.setButtonText(tridData.getString("Label"));
                    binding.startTripSlideButton.setBackgroundColor(getResources().getColor(R.color.appThemeColor_1));
                    findRoute(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData));
                } else if (currentRideState.equalsIgnoreCase("TripEnd")) {
                    addressTxt.setText(tridData.getString("Location"));
                    binding.emeTapImgView.setVisibility(View.VISIBLE);
                    binding.startTripSlideButton.setVisibility(View.GONE);
                    binding.endTripSlideButton.setVisibility(View.VISIBLE);
                    binding.endTripSlideButton.setButtonText(tridData.getString("Label"));
                    binding.endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                    startDestIcon = endDestinationIcon;
                    findRoute(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData));
                } else if (currentRideState.equalsIgnoreCase("PaymentCollected")) {
                    Bundle intent = new Bundle();
                    intent.putBoolean("isRideEnded", true);
                    intent.putString("publishRideId", publishRideId);
                    intent.putString("riderDetails", tridData.toString());
                    new ActUtils(getActContext()).startActForResult(RideSharePaymentSummaryActivity.class, intent, MyUtils.REFRESH_DATA_REQ_CODE);
                }
                isOkResult = true;
            } catch (JSONException e) {
                throw new RuntimeException(e);
            }
        }
    }

    private void setViewClickListener() {
        addToClickHandler(binding.deliveryInfoView);
        addToClickHandler(binding.emeTapImgView);
        addToClickHandler(binding.navigateArea);
        addToClickHandler(binding.callview);
        binding.startTripSlideButton.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
            if (isCompleted) {
                rideStatusTypeCall();
            }
        });
        binding.endTripSlideButton.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
            if (isCompleted) {
                rideStatusTypeCall();
            }
        });
    }

    private void rideStatusTypeCall() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "publishRideUpdateState");
        parameters.put("iPublishedRideId", publishRideId);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    tridData = generalFunc.getJsonObject(Utils.message_str, responseString);
                    if (generalFunc.getJsonValueStr("RideState", tridData).equalsIgnoreCase("MarkAsPickup")) {
                        setLabels();
                        setDestinationLocation(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData), endDestinationIcon, carIcon);
                        findRoute(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData));

                    } else if (generalFunc.getJsonValueStr("RideState", tridData).equalsIgnoreCase("TripEnd")) {
                        setLabels();
                        setDestinationLocation(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData), endDestinationIcon, carIcon);
                        findRoute(generalFunc.getJsonValueStr("Latitude", tridData), generalFunc.getJsonValueStr("Longitude", tridData));

                    } else if (generalFunc.getJsonValueStr("RideState", tridData).equalsIgnoreCase("PaymentCollected")) {
                        Bundle intent = new Bundle();
                        intent.putBoolean("isRideEnded", true);
                        intent.putString("publishRideId", publishRideId);
                        intent.putString("riderDetails", tridData.toString());
                        new ActUtils(getActContext()).startActForResult(RideSharePaymentSummaryActivity.class, intent, MyUtils.REFRESH_DATA_REQ_CODE);

                    } else if (generalFunc.getJsonValueStr("message", tridData).equalsIgnoreCase("LBL_RIDE_SHARE_TRIP_END_SUCCESSFULLY")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void publishRidePaymentSummery() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "publishRidePaymentSummery");
        parameters.put("iPublishedRideId", publishRideId);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    tridData = generalFunc.getJsonObject(Utils.message_str, responseString);
                    Bundle intent = new Bundle();
                    intent.putBoolean("isRideEnded", false);
                    intent.putString("publishRideId", publishRideId);
                    intent.putString("riderDetails", tridData.toString());
                    new ActUtils(getActContext()).startActForResult(RideSharePaymentSummaryActivity.class, intent, MyUtils.REFRESH_DATA_REQ_CODE);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private Context getActContext() {
        return RideShareActiveTripActivity.this;
    }

    @Override
    public void onMapReady(@NonNull GeoMapLoader.GeoMap googleMap) {
        this.gMap = googleMap;
        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(false);
        }

        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);

        setDestinationLocation(startDesLat, startDestLong, startDestIcon, carIcon);

        stopLocationUpdates();
        GetLocationUpdates.locationResolutionAsked = false;
        getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
    }

    private void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
            getLastLocation = null;
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (data.getBooleanExtra("isRideEnded", false) || data.getBooleanExtra("markPaymentCollect", false)) {
                    new ActUtils(getActContext()).setOkResult();
                    finish();
                }
            }
        }
    }

    private void setDestinationLocation(String DestLat, String DestLong, int destinationIconId, int carIconId) {
        if (dest_marker != null) {
            dest_marker.remove();
        }
        MarkerOptions markerOptions_dest = new MarkerOptions();
        double latitude = GeneralFunctions.parseDoubleValue(0.0, DestLat);
        double longitude = GeneralFunctions.parseDoubleValue(0.0, DestLong);
        markerOptions_dest.position(new LatLng(latitude, longitude));
        markerOptions_dest.icon(BitmapDescriptorFactory.fromResource(destinationIconId)).anchor(0.5f, 0.5f).flat(true);
        markerOptions_dest.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_dest_marker)).anchor(0.5f, 0.5f);

        dest_marker = gMap.addMarker(markerOptions_dest);
        getMap().moveCamera(new LatLng(latitude, longitude, Utils.defaultZomLevel));
    }

    private GeoMapLoader.GeoMap getMap() {
        return this.gMap;
    }

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }
        if (gMap == null) {
            this.driverLocation = location;
            return;
        }
        updateDriverMarker(location);
        if (isFromPublishRide) {
            ArrayList<String> channelName = new ArrayList<>();
            channelName.add("ONLINE_RIDE_LOC_" + generalFunc.getMemberId());
            AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).setMessage(generalFunc.buildLocationJson(location)).build(), AppService.Event.PUBLISH);
        }

        if (isFirstTime) {
            isFirstTime = false;
            updateFreqTask = new RecurringTask(DESTINATION_UPDATE_TIME_INTERVAL);
            updateFreqTask.setTaskRunListener(this);
            updateFreqTask.startRepeatingTask();
        }
    }

    private void updateDriverMarker(final Location updatedLocation) {
        LatLng newLocation = new LatLng(updatedLocation.getLatitude(), updatedLocation.getLongitude());
        if (MyApp.getInstance().isMyAppInBackGround() || gMap == null) {
            return;
        }

        this.driverLocation = updatedLocation;

        if (driver_marker == null) {
            MarkerOptions markerOptions_driver = new MarkerOptions();
            markerOptions_driver.position(newLocation);
            markerOptions_driver.icon(BitmapDescriptorFactory.fromResource(carIcon)).anchor(0.5f, 0.5f).flat(true);
            driver_marker = gMap.addMarker(markerOptions_driver);
        }

        String tripId = "";

        if (this.driverLocation != null && newLocation != null) {
            LatLng currentLatLng = new LatLng(driverLocation.getLatitude(), driverLocation.getLongitude());
            float rotation = driver_marker == null ? 0 : driver_marker.getRotation();

            if (animateMarker.currentLng != null) {
                rotation = (float) animateMarker.bearingBetweenLocations(animateMarker.currentLng, newLocation);
            } else {
                rotation = (float) animateMarker.bearingBetweenLocations(currentLatLng, newLocation);
            }


            HashMap<String, String> previousItemOfMarker = animateMarker.getLastLocationDataOfMarker(driver_marker);

            HashMap<String, String> data_map = new HashMap<>();
            double vLatitude = newLocation.latitude;
            double vLongitude = newLocation.longitude;
            data_map.put("vLatitude", "" + vLatitude);
            data_map.put("vLongitude", "" + vLongitude);
            data_map.put("iDriverId", "" + generalFunc.getMemberId());
            data_map.put("RotationAngle", "" + rotation);
            data_map.put("LocTime", "" + System.currentTimeMillis());

            Location location = new Location("marker");
            location.setLatitude(vLatitude);
            location.setLongitude(vLongitude);


            String prevLocTime = previousItemOfMarker.get("LocTime");
            String LocTime = data_map.get("LocTime");

            if (animateMarker.toPositionLat.get("" + vLatitude) == null || animateMarker.toPositionLong.get("" + vLongitude) == null) {
                if (prevLocTime != null && !prevLocTime.equals("")) {

                    long previousLocTime = GeneralFunctions.parseLongValue(0, prevLocTime);
                    long newLocTime = GeneralFunctions.parseLongValue(0, LocTime);

                    if (previousLocTime != 0 && newLocTime != 0) {

                        if ((newLocTime - previousLocTime) > 0 && animateMarker.driverMarkerAnimFinished == false) {
                            animateMarker.addToListAndStartNext(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                        } else if ((newLocTime - previousLocTime) > 0) {
                            animateMarker.animateMarker(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                        }

                    } else if ((previousLocTime == 0 || newLocTime == 0) && animateMarker.driverMarkerAnimFinished == false) {
                        animateMarker.addToListAndStartNext(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                    } else {
                        animateMarker.animateMarker(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                    }
                } else if (animateMarker.driverMarkerAnimFinished == false) {
                    animateMarker.addToListAndStartNext(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                } else {
                    animateMarker.animateMarker(driver_marker, this.gMap, location, rotation, 850, tripId, LocTime);
                }
            }
        }
    }

    @Override
    public void onTaskRun(RecurringTask instance) {
        Utils.runGC();
        findRoute(startDesLat, startDestLong);
    }

    private void findRoute(String startDesLat, String startDestLong) {

        if (dest_marker == null || driver_marker == null || driverLocation.getLatitude() == 0.0 || driverLocation.getLongitude() == 0.0) {
            return;
        }
        AppService.getInstance().executeService((getActContext()), new DataProvider.DataProviderBuilder(driverLocation.getLatitude() + "", driverLocation.getLongitude() + "")
                .setDestLatitude(startDesLat).setDestLongitude(startDestLong).setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                return;
            }
            String responseString = data.get("RESPONSE_DATA").toString();

            if (responseString.equalsIgnoreCase("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
            if (!responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {

                JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
                if (obj_routes != null && obj_routes.length() > 0) {
                    JSONObject obj_legs = generalFunc.getJsonObject(generalFunc.getJsonArray("legs", generalFunc.getJsonObject(obj_routes, 0).toString()), 0);
                    distance = "" + (GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("distance", obj_legs.toString()).toString())));
                    time = "" + (GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("duration", obj_legs.toString()).toString())));

                    LatLng sourceLocation = new LatLng(driverLocation.getLatitude(), driverLocation.getLongitude());
                    LatLng destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, startDesLat), GeneralFunctions.parseDoubleValue(0.0, startDestLong));
                    responseString = data.get("ROUTES").toString();
                    route_polyLine = MapUtils.handleMapAnimation(getActContext(), generalFunc, responseString, sourceLocation, destLocation, gMap, route_polyLine, true, false);
                } else {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"), generalFunc.retrieveLangLBl("", "LBL_GOOGLE_DIR_NO_ROUTE"));
                }
            } else {
                distance = Objects.requireNonNull(data.get("DISTANCE")).toString();
                time = Objects.requireNonNull(data.get("DURATION")).toString();
                LatLng sourceLocation = new LatLng(driverLocation.getLatitude(), driverLocation.getLongitude());
                LatLng destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, startDesLat), GeneralFunctions.parseDoubleValue(0.0, startDestLong));

                HashMap<String, Object> data_dict = new HashMap<>();
                data_dict.put("routes", data.get("ROUTES"));
                responseString = data_dict.toString();
                route_polyLine = MapUtils.handleMapAnimation(getActContext(), generalFunc, responseString, sourceLocation, destLocation, gMap, route_polyLine, false, false);
            }
            double distance_final = generalFunc.parseDoubleValue(0.0, distance);
            if (!generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
                distance_final = distance_final * 0.000621371;
            } else {
                distance_final = distance_final * 0.00099999969062399994;
            }
            distance_final = generalFunc.round(distance_final, 2);
            setTimeText(generalFunc.formatUpto2Digit(distance_final) + "", getTimeTxt((int) (GeneralFunctions.parseDoubleValue(0, time) / 60)));
        });
    }

    private String getTimeTxt(int duration) {

        if (duration < 1) {
            duration = 1;
        }
        String durationTxt = "";
        String timeToreach = duration == 0 ? "--" : "" + duration;

        timeToreach = duration >= 60 ? formatHoursAndMinutes(duration) : timeToreach;


        durationTxt = (duration < 60 ? generalFunc.retrieveLangLBl("", "LBL_MINS_SMALL") : generalFunc.retrieveLangLBl("", "LBL_HOUR_TXT"));

        durationTxt = duration == 1 ? generalFunc.retrieveLangLBl("", "LBL_MIN_SMALL") : durationTxt;
        durationTxt = duration > 120 ? generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") : durationTxt;

        return timeToreach + " " + durationTxt;
    }

    @SuppressLint("SetTextI18n")
    private void setTimeText(String distance, String time) {
        try {
            if (!generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
                binding.distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " ");
                binding.timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            } else {
                binding.distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " ");
                binding.timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            }
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.deliveryInfoView.getId()) {
            publishRidePaymentSummery();

        } else if (i == binding.emeTapImgView.getId()) {
            if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                SafetyTools.getInstance().initiate(getActContext(), generalFunc, publishRideId, "");
                SafetyTools.getInstance().safetyToolsDialog(false);
            } else {
                Bundle bn = new Bundle();
                bn.putString("TripId", publishRideId);
                new ActUtils(getActContext()).startActWithData(ConfirmEmergencyTapActivity.class, bn);
            }

        } else if (i == R.id.navigateArea) {
            openNavigationDialog(startDesLat, startDestLong);

        } else if (i == R.id.callview) {
            if (mapData != null) {
                MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                        .setPhoneNumber(mapData.get("DriverPhone"))
                        .setToMemberName(mapData.get("DriverName"))
                        .setMedia(CommunicationManager.MEDIA.DEFAULT)
                        .build();
                CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);
            }
        }
    }

    private void openNavigationDialog(final String dest_lat, final String dest_lon) {
        MyCommonDialog.navigationDialog(getActContext(), generalFunc,
                () -> {
                    try {
                        String url_view = "http://maps.google.com/maps?daddr=" + dest_lat + "," + dest_lon;
                        (new ActUtils(getActContext())).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                    } catch (Exception e) {
                        generalFunc.showMessage(binding.timeTxt, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
                    }
                }, () -> {
                    try {
                        String uri = "https://play.google.com/store/apps/details?id=com.waze";
                        if (MyCommonDialog.isPackageInstalled("com.waze", getActContext().getPackageManager())) {
                            uri = "waze://?ll=" + dest_lat + "," + dest_lon + "&navigate=yes";
                        }
                        Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                        startActivity(intent);
                    } catch (Exception e) {
                        generalFunc.showMessage(binding.timeTxt, generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
                    }
                });
    }

    @Override
    protected void onResume() {
        super.onResume();

        if (isFromPublishRide) {
            if (ActivityCompat.checkSelfPermission(getActContext(), Manifest.permission.ACCESS_BACKGROUND_LOCATION) != PackageManager.PERMISSION_GRANTED) {
                bgAlertBox = generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_BACKGROUND_LOC_PER_TXT"), generalFunc.retrieveLangLBl("",
                                "LBL_BG_LOC_ALLOW_NOTE_ANDROID"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"),
                        buttonId -> {
                            if (buttonId == 1) {
                                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                                    notification_permission_launch_time = System.currentTimeMillis();
                                    backgroundLocationLauncher.launch(Manifest.permission.ACCESS_BACKGROUND_LOCATION);
                                }
                            } else {
                                finish();
                            }
                        });
            }
        }


        if (this.driverLocation != null) {
            onLocationUpdate(this.driverLocation);
        }
        subscribeToDriverLocChannel();

        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }
    }

    private void openSetting() {
        Intent intent = new Intent();
        intent.setAction(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
        Uri uri = Uri.fromParts("package", MyApp.getInstance().getApplicationContext().getPackageName(), null);
        intent.setData(uri);
        settingLauncher.launch(intent);
    }

    @Override
    public void onBackPressed() {
        if (isOkResult) {
            new ActUtils(getActContext()).setOkResult();
        }
        super.onBackPressed();
    }

    private void subscribeToDriverLocChannel() {
        if (mapData != null) {
            if (mapData.containsKey("iDriverId")) {
                ArrayList<String> channelName = new ArrayList<>();
                channelName.add("ONLINE_RIDE_LOC_" + mapData.get("iDriverId"));
                AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).build(), AppService.Event.SUBSCRIBE);
            }
        }
    }

    private void unSubscribeToDriverLocChannel() {
        if (mapData != null) {
            if (mapData.containsKey("iDriverId")) {
                ArrayList<String> channelName = new ArrayList<>();
                channelName.add("ONLINE_RIDE_LOC_" + mapData.get("iDriverId"));
                AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).build(), AppService.Event.UNSUBSCRIBE);
                if (updateDirections != null) {
                    updateDirections.releaseTask();
                    updateDirections = null;
                }
            }
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        unSubscribeToDriverLocChannel();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        stopLocationUpdates();
        releaseTask();
        unSubscribeToDriverLocChannel();
    }

    private void releaseTask() {
        if (updateFreqTask != null) {
            updateFreqTask.stopRepeatingTask();
            updateFreqTask = null;
        }
        Utils.runGC();
    }

    public void pubNubMsgArrived(final String message) {

        runOnUiThread(() -> {

            String msgType = generalFunc.getJsonValue("MsgType", message);
            String DriverId = generalFunc.getJsonValue("iDriverId", message);

            if (!generalFunc.getMemberId().equalsIgnoreCase(DriverId)) {
                if (msgType.equalsIgnoreCase("RideSharePickup")) {
                    generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message), i -> {
                        new ActUtils(getActContext()).setOkResult();
                        finish();
                    });
                } else if (msgType.equals("LocationUpdate")) {
                    LatLng driverLocation_update = new LatLng(
                            GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLatitude", message)),
                            GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLongitude", message)));
                    Location location = new Location(LocationManager.GPS_PROVIDER);
                    location.setLatitude(driverLocation_update.latitude);
                    location.setLongitude(driverLocation_update.longitude);
                    updateDriverMarker(location);
                }
            }
        });
    }
}