package com.act.trackService;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;

import androidx.activity.OnBackPressedCallback;
import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.gson.Gson;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityTrackAnyLiveTrackingBinding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.helper.MarkerAnim;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.model.SocketEvents;
import com.service.handler.AppService;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.utils.VectorUtils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Objects;

public class TrackAnyLiveTracking extends ParentActivity implements GeoMapLoader.OnMapReadyCallback {

    private ActivityTrackAnyLiveTrackingBinding binding;
    private HashMap<String, String> trackAnyHashMap;
    private GeoMapLoader.GeoMap geoMap;
    private Marker driverMarker;
    private String tStatus = "";

    private String stDriverLat, stDriverLong;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_track_any_live_tracking);

        trackAnyHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("trackAnyHashMap");

        new GeoMapLoader(this, binding.mapLiveTrackingContainer.getId()).bindMap(this);
        if (trackAnyHashMap == null) {
            return;
        }
        initialization();
    }

    private void initialization() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_LIVE_TRACKING_TXT"));

        String vUserImage = trackAnyHashMap.get("vImage");
        if (!Utils.checkText(vUserImage)) {
            vUserImage = "Temp";
        }
        new LoadImage.builder(LoadImage.bind(vUserImage), binding.imvUser).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        binding.txtUserName.setText(trackAnyHashMap.get("userName"));
        tStatus = trackAnyHashMap.get("LocationTrackingStatus");
        setStatus(Utils.checkText(trackAnyHashMap.get("GpsStatus")) && trackAnyHashMap.get("GpsStatus").equalsIgnoreCase("Yes"));

        binding.txtLiveTrackTitle.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_LIVE_TRACKING_TXT"));
        binding.txtLblStatus.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRACKING_STATUS_TXT").concat(": "));
        binding.txtGpsLblStatus.setText(generalFunc.retrieveLangLBl("GPS Status", "LBL_TRACK_SERVICE_GPS_STATUS_TXT").concat(": "));
        binding.txtCall.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        binding.navigationTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NAVIGATOR_TXT"));

        stDriverLat = trackAnyHashMap.get("userLatitude");
        stDriverLong = trackAnyHashMap.get("userLongitude");

        addToClickHandler(binding.txtCallArea);
        addToClickHandler(binding.navigationTxt);
        addToClickHandler(binding.userLocBtnImgView);

        getOnBackPressedDispatcher().addCallback(new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                new ActUtils(getActContext()).setOkResult();
                finish();
            }
        });
    }

    private Context getActContext() {
        return TrackAnyLiveTracking.this;
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();

        } else if (i == R.id.userLocBtnImgView) {
            if (MyApp.getInstance().currentLocation != null) {
                geoMap.animateCamera(new LatLng(MyApp.getInstance().currentLocation.getLatitude(), MyApp.getInstance().currentLocation.getLongitude(), geoMap.getCameraPosition().zoom));
            }

        } else if (i == R.id.txtCallArea) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setPhoneNumber(trackAnyHashMap.get("userPhone"))
                    .setToMemberName(trackAnyHashMap.get("userName"))
                    .setMedia(CommunicationManager.MEDIA.DEFAULT)
                    .build();
            CommunicationManager.getInstance().communicate(getActContext(), mDataProvider, CommunicationManager.TYPE.OTHER);

        } else if (i == R.id.navigationTxt) {
            openNavigationDialog(true);
        }
    }

    @Override
    public void onMapReady(@NonNull GeoMapLoader.GeoMap googleMap) {
        this.geoMap = googleMap;
        if (generalFunc.checkLocationPermission(true)) {
            googleMap.setMyLocationEnabled(false);
        }
        googleMap.getUiSettings().setTiltGesturesEnabled(false);
        googleMap.getUiSettings().setCompassEnabled(false);
        googleMap.getUiSettings().setMyLocationButtonEnabled(false);

        //
        tStatus = trackAnyHashMap.get("LocationTrackingStatus");
        setStatus(Utils.checkText(trackAnyHashMap.get("GpsStatus")) && trackAnyHashMap.get("GpsStatus").equalsIgnoreCase("Yes"));

        JSONArray userList = generalFunc.getJsonArray(trackAnyHashMap.get("userList"));
        if (userList != null) {
            for (int jk = 0; jk < userList.length(); jk++) {
                JSONObject object = generalFunc.getJsonObject(userList, jk);
                double lat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("vLatitude", object));
                double lon = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("vLongitude", object));
                if (lat != 0.0 && lon != 0.0) {
                    LatLng newLocation = new LatLng(lat, lon);
                    MarkerOptions userHomeOpt = new MarkerOptions();
                    userHomeOpt.position(newLocation);
                    userHomeOpt.title(generalFunc.getJsonValueStr("userName", object));
                    userHomeOpt.snippet(generalFunc.getJsonValueStr("vAddress", object));

                    if (generalFunc.getJsonValueStr("iTrackServiceUserId", object).equalsIgnoreCase(trackAnyHashMap.get("iTrackServiceUserId"))) {
                        userHomeOpt.icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.ic_track_trip_home, 0)).anchor(0.5f, 0.5f).flat(true);
                    } else {
                        userHomeOpt.icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.ic_place_marker, 0)).anchor(0.5f, 0.5f).flat(true);
                    }
                    Objects.requireNonNull(googleMap.addMarker(userHomeOpt)).setFlat(false);
                }
            }
        }

        if (!Objects.equals(trackAnyHashMap.get("userLatitude"), "") || !Objects.equals(trackAnyHashMap.get("userLongitude"), "") || !Objects.equals(trackAnyHashMap.get("userAddress"), "")) {

            LatLng latLng = new LatLng(GeneralFunctions.parseDoubleValue(0.0, trackAnyHashMap.get("userLatitude")), GeneralFunctions.parseDoubleValue(0.0, trackAnyHashMap.get("userLongitude")), Utils.defaultZomLevel);
            MarkerOptions markerOptions = new MarkerOptions();
            markerOptions.position(latLng).icon(BitmapDescriptorFactory.fromResource(R.drawable.track_icon)).anchor(0.5f, 0.5f).flat(true);
            geoMap.addMarker(markerOptions);
            geoMap.animateCamera(latLng);

        } else {
            if (!Utils.checkText(tStatus) && MyApp.getInstance().currentLocation != null) {
                LatLng latLng = new LatLng(MyApp.getInstance().currentLocation.getLatitude(), MyApp.getInstance().currentLocation.getLongitude(), Utils.defaultZomLevel);
                geoMap.moveCamera(latLng);
            }
        }
        geoMap.setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });
        //
        getRetrieveInfo();
    }

    private void getRetrieveInfo() {
        HashMap<String, String> dataMap = new HashMap<>();
        dataMap.put("MsgType", "RetrieveMemberInfo");
        dataMap.put("iMemberId", trackAnyHashMap.get("iTrackServiceUserId"));

        AppService.getInstance().sendMessage(SocketEvents.TRACKING_SERVICE, new Gson().toJson(dataMap), 10000, (name, errorObj, dataObj) -> {

            if (errorObj != null) {
                generalFunc.showError(true);
            } else {
                if (dataObj instanceof JSONObject obj) {
                    if (GeneralFunctions.checkDataAvail(Utils.action_str, obj)) {
                        String memberInfo = generalFunc.getJsonValueStr("MemberInfo", obj);
                        if (Utils.checkText(memberInfo)) {
                            tStatus = generalFunc.getJsonValue("OnlineStatus", memberInfo);
                            binding.txtLiveTrackSubTitle.setText(generalFunc.getJsonValue("LastLocUpdateLabel", memberInfo));
                            setStatus(Utils.checkText(generalFunc.getJsonValue("GpsStatus", memberInfo)) && generalFunc.getJsonValue("GpsStatus", memberInfo).equalsIgnoreCase("yes"));
                            updateDriverLocation(new LatLng(
                                    GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLatitude", memberInfo)),
                                    GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLongitude", memberInfo))));
                        }
                    }
                }
            }
        });
    }

    @Override
    public void onBackPressed() {
        new ActUtils(getActContext()).setOkResult();
        super.onBackPressed();
    }

    public void pubNubMsgArrived(final String message) {

        String iMemberId = generalFunc.getJsonValue("iMemberId", message);
        String msgType = generalFunc.getJsonValue("MsgType", message);
        String vLatitude = generalFunc.getJsonValue("vLatitude", message);
        String vLongitude = generalFunc.getJsonValue("vLongitude", message);

        stDriverLat = vLatitude;
        stDriverLong = vLongitude;
        if (!iMemberId.equalsIgnoreCase(trackAnyHashMap.get("iTrackServiceUserId"))) {
            return;
        }

        runOnUiThread(() -> {
            tStatus = generalFunc.getJsonValue("OnlineStatus", message);
            setStatus(Utils.checkText(generalFunc.getJsonValue("GpsStatus", message)) && generalFunc.getJsonValue("GpsStatus", message).equalsIgnoreCase("yes"));

            if (msgType.equals("TrackMemberRemoved")) {
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message), buttonId -> {
                    getOnBackPressedDispatcher().onBackPressed();
                });
            } else if (msgType.equals("LocationUpdate") && Utils.checkText(vLatitude) && Utils.checkText(vLongitude)) {
                LatLng driverLocation_update = new LatLng(
                        GeneralFunctions.parseDoubleValue(0.0, vLatitude),
                        GeneralFunctions.parseDoubleValue(0.0, vLongitude));
                binding.txtLiveTrackSubTitle.setText(generalFunc.getJsonValue("LastLocUpdateLabel", message));
                updateDriverLocation(driverLocation_update);
            }
        });
    }

    private void setStatus(boolean isGPSEnabled) {
        if (Utils.checkText(tStatus) && tStatus.equalsIgnoreCase("Yes")) {
            binding.txtStatus.setText(generalFunc.retrieveLangLBl("", "LBL_TRACKING_STATUS_ON_TXT"));
            binding.txtStatus.setTextColor(Color.parseColor("#34C759"));
        } else {
            binding.txtStatus.setText(generalFunc.retrieveLangLBl("", "LBL_TRACKING_STATUS_OFF_TXT"));
            binding.txtStatus.setTextColor(Color.parseColor("#800000"));
        }

        if (isGPSEnabled) {
            binding.txtGpsStatus.setText(generalFunc.retrieveLangLBl("", "LBL_TRACKING_STATUS_ON_TXT"));
            binding.txtGpsStatus.setTextColor(Color.parseColor("#34C759"));
        } else {
            binding.txtGpsStatus.setText(generalFunc.retrieveLangLBl("", "LBL_TRACKING_STATUS_OFF_TXT"));
            binding.txtGpsStatus.setTextColor(Color.parseColor("#800000"));
        }

    }

    private void updateDriverLocation(LatLng driverLocation) {
        if (driverLocation == null || geoMap == null || driverLocation.latitude == 0.0) {
            return;
        }

        if (driverMarker == null) {
            geoMap.clear();
            MarkerOptions markerOptions = new MarkerOptions();
            markerOptions.position(driverLocation).icon(BitmapDescriptorFactory.fromResource(R.drawable.track_icon)).anchor(0.5f, 0.5f).flat(true);
            driverMarker = geoMap.addMarker(markerOptions);
        } else {
            Location driver_loc = new Location("gps");
            driver_loc.setLatitude(driverLocation.latitude);
            driver_loc.setLongitude(driverLocation.longitude);
            MarkerAnim.animateMarker(driverMarker, this.geoMap, driver_loc, 0, 850);
        }
        geoMap.moveCamera(driverLocation);
    }

    private void openNavigationDialog(boolean isDirection) {
        MyCommonDialog.navigationDialog(getActContext(), generalFunc, () -> {

            try {
                if (isDirection) {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + stDriverLat + "," + stDriverLong));
                    startActivity(intent);
                } else {
                    String url_view = "http://maps.google.com/maps?q=loc:" + stDriverLat + "," + stDriverLong;
                    (new ActUtils(this)).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                }
            } catch (Exception e) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
            }
        }, () -> {
            try {
                if (isDirection) {
                    String uri = "https://waze.com/ul?q=" + MyApp.getInstance().currentLocation.getLatitude() + "," + MyApp.getInstance().currentLocation.getLongitude() + "&ll=" + stDriverLat + "," + stDriverLong + "&navigate=yes";
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                    startActivity(intent);
                } else {
                    String uri = "https://waze.com/ul?ll=" + stDriverLat + "," + stDriverLong;
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                    startActivity(intent);
                }
            } catch (Exception e) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
            }
        });
    }
}