package com.act;

import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivitySearchPickupLocationBinding;
import com.map.GeoMapLoader;
import com.map.minterface.OnCameraIdleListener;
import com.map.minterface.OnCameraMoveStartedListener;
import com.map.models.LatLng;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class SearchPickupLocationActivity extends ParentActivity implements GetAddressFromLocation.AddressFound, GetLocationUpdates.LocationUpdates, OnCameraIdleListener, GeoMapLoader.OnMapReadyCallback, OnCameraMoveStartedListener {
    private ActivitySearchPickupLocationBinding binding;
    private ImageView backImgView;
    private MTextView titleTxt;

    private MButton doneBtn;

    private boolean isAddressEnable = false, isFirstLocation = true, isAddressStore = false, isbtnclick = false;
    public boolean isPlaceSelected = false;
    private GeoMapLoader.GeoMap geoMap;
    private LatLng placeLocation;

    private GetLocationUpdates getLastLocation;
    private GetAddressFromLocation getAddressFromLocation;

    private String userHomeLocationLatitude_str, userHomeLocationLongitude_str, userWorkLocationLatitude_str, userWorkLocationLongitude_str;
    private String home_address_str, work_address_str, iUserFavAddressId = "";

    private String eType = "";
    private Location userLocation;
    private SearchPickupLocationActivity listener;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_search_pickup_location);

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        titleTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
        titleTxt.setSelected(true);
        titleTxt.setSingleLine(true);

        backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);

        (new GeoMapLoader(this, R.id.mapFragmentContainer)).bindMap(this);

        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);

        doneBtn = ((MaterialRippleLayout) binding.doneBtn).getChildView();
        doneBtn.setId(Utils.generateViewId());
        addToClickHandler(doneBtn);

        addToClickHandler(binding.homePlaceTxt);
        addToClickHandler(binding.workPlaceTxt);
        addToClickHandler(binding.locationImage);
        checkLocations();
        setLabels();

        JSONArray userFavouriteAddressArr = generalFunc.getJsonArray("UserFavouriteAddress", obj_userProfile);
        if (userFavouriteAddressArr != null && userFavouriteAddressArr.length() > 0) {
            for (int i = 0; i < userFavouriteAddressArr.length(); i++) {
                JSONObject dataItem = generalFunc.getJsonObject(userFavouriteAddressArr, i);
                if (generalFunc.getJsonValueStr("eType", dataItem).equalsIgnoreCase(eType)) {
                    iUserFavAddressId = generalFunc.getJsonValueStr("iUserFavAddressId", dataItem);
                }
            }
        }

        if (!(getIntent().getBooleanExtra("isDataFilled", false)) && getIntent().getBooleanExtra("isFromGoingTo", false)) {
            binding.locationImage.performClick();
        }
    }

    @Override
    protected void onDestroy() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
        releaseResources();
        super.onDestroy();
    }

    private void setLabels() {
        binding.placeTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SEARCH_LOC"));
        binding.pinImgView.setVisibility(View.GONE);
        if (getIntent().getStringExtra("isPickUpLoc") != null && getIntent().getStringExtra("isPickUpLoc").equals("true")) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SET_PICK_UP_LOCATION_TXT"));
        } else if (getIntent().getStringExtra("isHome") != null && getIntent().getStringExtra("isHome").equals("true")) {
            isAddressStore = true;
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_LOCATION_TXT"));
            binding.homePlaceTxt.setText(generalFunc.retrieveLangLBl("Home Place", "LBL_HOME_PLACE"));
        } else if (getIntent().getStringExtra("isWork") != null && getIntent().getStringExtra("isWork").equals("true")) {
            isAddressStore = true;
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_WORKLOCATION"));
            binding.workPlaceTxt.setText(generalFunc.retrieveLangLBl("Work Place", "LBL_WORK_PLACE"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_DESTINATION_HEADER_TXT"));
        }
        if (getIntent().getStringExtra("IS_FROM_SELECT_LOC") != null && getIntent().getStringExtra("IS_FROM_SELECT_LOC").equalsIgnoreCase("Yes")) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_LOCATION_TXT"));
        }
        doneBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
    }

    private void checkLocations() {

        HashMap<String, String> data = new HashMap<>();
        data.put("userHomeLocationAddress", "");
        data.put("userHomeLocationLatitude", "");
        data.put("userHomeLocationLongitude", "");
        data.put("userWorkLocationAddress", "");
        data.put("userWorkLocationLatitude", "");
        data.put("userWorkLocationLongitude", "");
        data = generalFunc.retrieveValue(data);

        home_address_str = data.get("userHomeLocationAddress");

        userHomeLocationLatitude_str = data.get("userHomeLocationLatitude");
        userHomeLocationLongitude_str = data.get("userHomeLocationLongitude");

        work_address_str = data.get("userWorkLocationAddress");
        userWorkLocationLatitude_str = data.get("userWorkLocationLatitude");
        userWorkLocationLongitude_str = data.get("userWorkLocationLongitude");

        if (getIntent().getStringExtra("isHome") != null && getIntent().getStringExtra("isHome").equals("true")) {

            eType = "HOME";
            if (home_address_str != null && !home_address_str.equals("")) {
                binding.homePlaceTxt.setVisibility(View.VISIBLE);
                binding.placeArea.setVisibility(View.GONE);
                (findViewById(R.id.seperationLine)).setVisibility(View.VISIBLE);
            }
        }
        if (getIntent().getStringExtra("isWork") != null && getIntent().getStringExtra("isWork").equals("true")) {

            eType = "WORK";
            if (work_address_str != null && !work_address_str.equals("")) {
                binding.workPlaceTxt.setVisibility(View.VISIBLE);
                binding.placeArea.setVisibility(View.GONE);
                (findViewById(R.id.seperationLine)).setVisibility(View.VISIBLE);
            }
        }
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        this.geoMap = geoMap;

        setGoogleMapCameraListener(this);
        checkLocation();
        this.geoMap.getUiSettings().setCompassEnabled(false);


        // TODO : Last execute code
        LatLng placeLocation = getLocationLatLng(true);
        if (placeLocation != null && placeLocation.latitude != 0.0 && placeLocation.longitude != 0.0) {
            Location temploc = new Location("source");
            temploc.setLatitude(placeLocation.latitude);
            temploc.setLongitude(placeLocation.longitude);
            onLocationUpdate(temploc);
        }
    }

    private void checkLocation() {
        if (generalFunc.isLocationPermissionGranted(false) && !isPlaceSelected) {
            getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, false, this);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (geoMap != null) {
            checkLocation();
        }
    }

    private void setCameraPosition(LatLng location) {
        geoMap.moveCamera(new LatLng(location.latitude, location.longitude, Utils.defaultZomLevel));
    }

    private LatLng getLocationLatLng(boolean setText) {
        LatLng placeLocation = null;

        String isPickUpLoc = getIntent().getStringExtra("isPickUpLoc");

        if (isPickUpLoc != null && isPickUpLoc.equals("true")) {
            if (getIntent().hasExtra("PickUpLatitude") && getIntent().hasExtra("PickUpLongitude")) {

                isAddressEnable = true;
                placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("PickUpLatitude")),
                        GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("PickUpLongitude")), Utils.defaultZomLevel);

            }

            if (setText && getIntent().hasExtra("PickUpAddress")) {
                binding.pinImgView.setVisibility(View.VISIBLE);
                if (!Utils.checkText(getIntent().getStringExtra("PickUpAddress"))) {
                    assert placeLocation != null;
                    getAddressFromLocation.setLocation(placeLocation.latitude, placeLocation.longitude);
                    getAddressFromLocation.execute();
                } else {
                    isPlaceSelected = true;
                    binding.placeTxtView.setText("" + getIntent().getStringExtra("PickUpAddress"));
                }
            }

        } else if (getIntent().getStringExtra("isDestLoc") != null && getIntent().hasExtra("DestLatitude") && getIntent().hasExtra("DestLongitude")) {

            isAddressEnable = true;
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("DestLatitude")),
                    GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("DestLongitude")), Utils.defaultZomLevel);

            if (setText && getIntent().hasExtra("DestAddress")) {
                binding.pinImgView.setVisibility(View.VISIBLE);
                if (!Utils.checkText(getIntent().getStringExtra("DestAddress"))) {
                    getAddressFromLocation.setLocation(placeLocation.latitude, placeLocation.longitude);
                    getAddressFromLocation.execute();
                } else {
                    isPlaceSelected = true;
                    binding.placeTxtView.setText("" + getIntent().getStringExtra("DestAddress"));
                }
            }

        } else if (getIntent().getStringExtra("isHome") != null && getIntent().getStringExtra("isHome").equals("true") && Utils.checkText(home_address_str)) {
            HashMap<String, String> data = new HashMap<>();
            data.put("userHomeLocationAddress", "");
            data.put("userHomeLocationLatitude", "");
            data.put("userHomeLocationLongitude", "");
            data = generalFunc.retrieveValue(data);

            if (Utils.checkText(data.get("userHomeLocationLatitude")) && Utils.checkText(data.get("userHomeLocationLongitude"))) {
                isAddressEnable = true;
                placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, data.get("userHomeLocationLatitude")),
                        GeneralFunctions.parseDoubleValue(0.0, data.get("userHomeLocationLongitude")), Utils.defaultZomLevel);
            }
            if (setText) {
                isPlaceSelected = true;
                binding.pinImgView.setVisibility(View.VISIBLE);
                binding.placeTxtView.setText("" + data.get("userHomeLocationAddress"));
            }

        } else if (getIntent().getStringExtra("isWork") != null && getIntent().getStringExtra("isWork").equals("true") && Utils.checkText(work_address_str)) {
            HashMap<String, String> data = new HashMap<>();
            data.put("userWorkLocationAddress", "");
            data.put("userWorkLocationLatitude", "");
            data.put("userWorkLocationLongitude", "");
            data = generalFunc.retrieveValue(data);

            if (Utils.checkText(data.get("userWorkLocationLatitude")) && Utils.checkText(data.get("userWorkLocationLongitude"))) {
                isAddressEnable = true;
                placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, data.get("userWorkLocationLatitude")),
                        GeneralFunctions.parseDoubleValue(0.0, data.get("userWorkLocationLongitude")), Utils.defaultZomLevel);
            }
            if (setText) {
                isPlaceSelected = true;
                binding.pinImgView.setVisibility(View.VISIBLE);
                binding.placeTxtView.setText("" + data.get("userWorkLocationAddress"));

            }
        } else if (userLocation != null) {
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLatitude()),
                    GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLongitude()), Utils.defaultZomLevel);

        } else {
            if (!generalFunc.checkLocationPermission(true)) {
                return placeLocation;
            }

            LocationManager locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
            Location getLastLocation = locationManager.getLastKnownLocation(LocationManager.PASSIVE_PROVIDER);
            if (getLastLocation != null) {
                LatLng UserLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLatitude()),
                        GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLongitude()), Utils.defaultZomLevel);
                if (UserLocation.latitude != 0.0 && UserLocation.longitude != 0.0) {
                    placeLocation = UserLocation;
                }
            }
        }
        return placeLocation;
    }

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }

        if (isFirstLocation) {
            placeLocation = getLocationLatLng(true);
            if (isAddressEnable && listener == null) {
                setGoogleMapCameraListener(this);
            }
            if (placeLocation != null) {
                setCameraPosition(new LatLng(placeLocation.latitude, placeLocation.longitude, Utils.defaultZomLevel));
            } else {
                setCameraPosition(new LatLng(location.getLatitude(), location.getLongitude(), Utils.defaultZomLevel));
            }

            binding.pinImgView.setVisibility(View.VISIBLE);
            isFirstLocation = false;
        }
        userLocation = location;
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        binding.placeTxtView.setText(address);
        isPlaceSelected = true;
        this.placeLocation = new LatLng(latitude, longitude, 14.0f);

        if (geoMap != null) {
            geoMap.clear();
            if (isFirstLocation) {
                geoMap.moveCamera(this.placeLocation);
            }
            isFirstLocation = false;
            setGoogleMapCameraListener(this);
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.PLACE_CUSTOME_LOC_REQUEST_CODE) {
            if (resultCode == RESULT_OK) {
                binding.pinImgView.setVisibility(View.VISIBLE);

                binding.placeTxtView.setText(data.getStringExtra("Address"));
                isPlaceSelected = true;
                isAddressEnable = true;
                LatLng placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, data.getStringExtra("Latitude")), GeneralFunctions.parseDoubleValue(0.0, data.getStringExtra("Longitude")), Utils.defaultZomLevel);

                this.placeLocation = placeLocation;

                if (geoMap != null && placeLocation != null) {
                    geoMap.clear();
                    geoMap.moveCamera(placeLocation);
                }

            } else if (resultCode == RESULT_CANCELED && !isPlaceSelected) {
                if (data != null) {
                    if (data.getBooleanExtra("isFromGoingTo", false)) {
                        finish();
                    }
                }
            }
        }
    }

    private Context getActContext() {
        return SearchPickupLocationActivity.this;
    }

    private void releaseResources() {
        setGoogleMapCameraListener(null);
        if (geoMap != null) {
            geoMap.releaseMap();
        }
        getAddressFromLocation.setAddressList(null);
        getAddressFromLocation = null;
    }

    private void addHomeWorkAddress(String vAddress, String vLatitude, String vLongitude) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateUserFavouriteAddress");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vAddress", vAddress);
        parameters.put("vLatitude", vLatitude);
        parameters.put("vLongitude", vLongitude);
        parameters.put("eType", eType);
        parameters.put("iUserFavAddressId", iUserFavAddressId);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            Logger.d("Response", "::" + responseString);
            doneBtn.setEnabled(true);
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    String messgeJson = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, messgeJson);

                    Bundle bn = new Bundle();
                    bn.putString("Address", Utils.getText(binding.placeTxtView));
                    bn.putString("Latitude", "" + placeLocation.latitude);
                    bn.putString("Longitude", "" + placeLocation.longitude);

                    if (getIntent().hasExtra("isFromMulti")) {
                        bn.putBoolean("isFromMulti", true);
                        bn.putInt("pos", getIntent().getIntExtra("pos", -1));
                    }

                    if (getIntent().hasExtra("isFromStopOver")) {
                        bn.putInt("pos", getIntent().getIntExtra("pos", -1));
                    }


                    if (getIntent().getStringExtra("isHome") != null && getIntent().getStringExtra("isHome").equals("true")) {
                        generalFunc.storeData("userHomeLocationLatitude", "" + placeLocation.latitude);
                        generalFunc.storeData("userHomeLocationLongitude", "" + placeLocation.longitude);
                        generalFunc.storeData("userHomeLocationAddress", "" + Utils.getText(binding.placeTxtView));
                    }
                    if (getIntent().getStringExtra("isWork") != null && getIntent().getStringExtra("isWork").equals("true")) {
                        generalFunc.storeData("userWorkLocationLatitude", "" + placeLocation.latitude);
                        generalFunc.storeData("userWorkLocationLongitude", "" + placeLocation.longitude);
                        generalFunc.storeData("userWorkLocationAddress", "" + Utils.getText(binding.placeTxtView));
                    }
                    new ActUtils(getActContext()).setOkResult(bn);
                    backImgView.performClick();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                isbtnclick = false;
                generalFunc.showError();
            }
        });
    }

    @Override
    public void onCameraMoveStarted(int reason) {
        if (binding.pinImgView.getVisibility() == View.VISIBLE) {
            if (!isAddressEnable) {
                isPlaceSelected = false;
                binding.placeTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
            }
        }
    }

    @Override
    public void onCameraIdle() {
        if (getAddressFromLocation == null) {
            return;
        }

        if (binding.pinImgView.getVisibility() == View.GONE) {
            return;
        }

        if (!isAddressEnable && geoMap != null) {
            LatLng center = geoMap.getCameraCenterPosition();
            setGoogleMapCameraListener(null);
            if (center != null) {
                getAddressFromLocation.setLocation(center.latitude, center.longitude);
                getAddressFromLocation.setLoaderEnable(true);
                getAddressFromLocation.execute();
            }
        } else {
            isAddressEnable = false;
        }
    }

    private void setGoogleMapCameraListener(SearchPickupLocationActivity act) {
        listener = act;
        if (geoMap != null) {
            geoMap.setOnCameraMoveStartedListener(act);
            geoMap.setOnCameraIdleListener(act);
        }
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (i == R.id.locationImage) {

            LatLng placeLocation = getLocationLatLng(false);
            Bundle bn = new Bundle();
            if (getIntent().hasExtra("locationArea")) {
                bn.putString("locationArea", getIntent().getStringExtra("locationArea"));
            } else {
                bn.putString("locationArea", "");
            }
            if (getIntent().hasExtra("isFromGoingTo")) {
                bn.putBoolean("isFromGoingTo", true);
            }
            bn.putString("hideSetMapLoc", "");
            if (placeLocation != null) {

                bn.putDouble("lat", placeLocation.latitude);
                bn.putDouble("long", placeLocation.longitude);
            } else {
                bn.putDouble("lat", 0.0);
                bn.putDouble("long", 0.0);
                bn.putString("address", "");
            }

            bn.putBoolean("isPlaceAreaShow", false);

            if (getIntent().hasExtra("isFromMulti")) {
                bn.putBoolean("isFromMulti", true);
                bn.putInt("pos", getIntent().getIntExtra("pos", -1));
            }
            if (getIntent().hasExtra("isFromStopOver")) {
                bn.putInt("pos", getIntent().getIntExtra("pos", -1));
            }
            if (getIntent().hasExtra("stopOverPointsList")) {
                bn.putSerializable("stopOverPointsList", getIntent().getSerializableExtra("stopOverPointsList"));
            }

            new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class, bn, Utils.PLACE_CUSTOME_LOC_REQUEST_CODE);


        } else if (i == doneBtn.getId()) {

            if (!isbtnclick) {
                if (!isPlaceSelected) {
                    generalFunc.showMessage(binding.mapFragmentContainer, generalFunc.retrieveLangLBl("Please set location.", "LBL_SET_LOCATION"));
                    return;
                }

                isbtnclick = true;

                if (isAddressStore && placeLocation != null) {
                    addHomeWorkAddress(Utils.getText(binding.placeTxtView), placeLocation.latitude + "", placeLocation.longitude + "");
                } else {
                    Bundle bn = new Bundle();
                    bn.putString("Address", Utils.getText(binding.placeTxtView));
                    bn.putString("Latitude", "" + placeLocation.latitude);
                    bn.putString("Longitude", "" + placeLocation.longitude);

                    if (getIntent().hasExtra("isFromMulti")) {
                        bn.putBoolean("isFromMulti", true);
                        bn.putInt("pos", getIntent().getIntExtra("pos", -1));
                    }

                    if (getIntent().hasExtra("isFromStopOver")) {
                        bn.putInt("pos", getIntent().getIntExtra("pos", -1));
                    }

                    new ActUtils(getActContext()).setOkResult(bn);
                    backImgView.performClick();
                }
            }

        } else if (i == binding.homePlaceTxt.getId()) {
            onAddressFound(home_address_str, GeneralFunctions.parseDoubleValue(0.0, userHomeLocationLatitude_str), GeneralFunctions.parseDoubleValue(0.0, userHomeLocationLongitude_str), "");
        } else if (i == binding.workPlaceTxt.getId()) {
            onAddressFound(work_address_str, GeneralFunctions.parseDoubleValue(0.0, userWorkLocationLatitude_str), GeneralFunctions.parseDoubleValue(0.0, userWorkLocationLongitude_str), "");
        }
    }
}