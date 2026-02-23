package com.act;

import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.google.android.gms.maps.CameraUpdate;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

public class AddAddressActivity extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener, GetAddressFromLocation.AddressFound, GoogleMap.OnCameraMoveStartedListener, GoogleMap.OnCameraIdleListener {


    ImageView backImgView;
    MTextView titleTxt;

    MaterialEditText companyBox;
    MaterialEditText postCodeBox;
    MaterialEditText addr2Box;
    MaterialEditText deliveryIntructionBox;

    ImageView locationImage;
    String addresslatitude = "";
    String addresslongitude;
    String address = "";
    MTextView locAddrTxtView;
    MButton btn_type2;
    LinearLayout loc_area;

    String required_str = "";

    String iCompanyId;
    GetAddressFromLocation getAddressFromLocation;

    public SupportMapFragment map;
    GoogleMap gMap;
    ImageView pinImgView;
    boolean isPlaceSelected = false;
    LatLng placeLocation;
    public boolean isAddressEnable = false;
    private boolean isFirstLocation = true;
    private Location userLocation;
    private AddAddressActivity listener;

    MTextView buildingTextH, landmarkTextH, addrtypeTextH;
    MaterialEditText buildingtxt, landmarktxt, addrtypetxt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_address);
        bindViews();

    }

    private void setValueInfo() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Add New Address", "LBL_WORKLOCATION"));
        buildingTextH.setText(generalFunc.retrieveLangLBl("Building/House/Flat No.", "LBL_JOB_LOCATION_HINT_INFO"));
        buildingtxt.setHint(generalFunc.retrieveLangLBl("Building/House/Flat No.", "LBL_JOB_LOCATION_HINT_INFO"));
        landmarkTextH.setText(generalFunc.retrieveLangLBl("Landmark(e.g hospital,park etc.)", "LBL_LANDMARK_HINT_INFO"));
        landmarktxt.setHint(generalFunc.retrieveLangLBl("Landmark(e.g hospital,park etc.)", "LBL_LANDMARK_HINT_INFO"));
        addrtypeTextH.setText(generalFunc.retrieveLangLBl("Nickname(optional-home,office etc.)", "LBL_ADDRESSTYPE_HINT_INFO"));
        addrtypetxt.setHint(generalFunc.retrieveLangLBl("Nickname(optional-home,office etc.)", "LBL_ADDRESSTYPE_HINT_INFO"));
        btn_type2.setText(generalFunc.retrieveLangLBl("Save", "LBL_BTN_SUBMIT_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
    }

    public void bindViews() {

        backImgView = (ImageView) findViewById(R.id.backImgView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        loc_area = (LinearLayout) findViewById(R.id.loc_area);
        map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);
        pinImgView = (ImageView) findViewById(R.id.pinImgView);

        View buildingBox = findViewById(R.id.buildingBox);
        buildingTextH = buildingBox.findViewById(R.id.mTextH);
        buildingtxt = buildingBox.findViewById(R.id.mEditText);

        View landmarkBox = findViewById(R.id.landmarkBox);
        landmarkTextH = landmarkBox.findViewById(R.id.mTextH);
        landmarktxt = landmarkBox.findViewById(R.id.mEditText);

        View addrtypeBox = findViewById(R.id.addrtypeBox);
        addrtypeTextH = addrtypeBox.findViewById(R.id.mTextH);
        addrtypetxt = addrtypeBox.findViewById(R.id.mEditText);

        companyBox = (MaterialEditText) findViewById(R.id.companyBox);
        postCodeBox = (MaterialEditText) findViewById(R.id.postCodeBox);
        addr2Box = (MaterialEditText) findViewById(R.id.addr2Box);
        deliveryIntructionBox = (MaterialEditText) findViewById(R.id.deliveryIntructionBox);
        locationImage = (ImageView) findViewById(R.id.locationImage);
        locAddrTxtView = (MTextView) findViewById(R.id.locAddrTxtView);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();

        addToClickHandler(btn_type2);
        addToClickHandler(loc_area);


        addresslatitude = getIntent().getStringExtra("latitude");
        addresslongitude = getIntent().getStringExtra("longitude");


        //locAddrTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_ADDRESS_TITLE_TXT"));
        locAddrTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));

        addToClickHandler(backImgView);
        addToClickHandler(locationImage);
        setValueInfo();

        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);
        map.getMapAsync(this);

    }

    public void handleDetails() {
        boolean buildingDataenterd = Utils.checkText(buildingtxt) || Utils.setErrorFields(buildingtxt, required_str);
        boolean landmarkDataenterd = Utils.checkText(landmarktxt) || Utils.setErrorFields(landmarktxt, required_str);

        if (!buildingDataenterd || !landmarkDataenterd) {
            return;

        }


        Bundle bn = new Bundle();
        bn.putString("Latitude", addresslatitude);
        bn.putString("Longitude", addresslongitude);
        if (Utils.checkText(addrtypetxt)) {
            address = Utils.getText(buildingtxt) + ", " + Utils.getText(landmarktxt) + ", " + Utils.getText(addrtypetxt) + ", " + address;
        } else {
            address = Utils.getText(buildingtxt) + ", " + Utils.getText(landmarktxt) + ", " + address;
        }
        bn.putString("Address", address);


        (new ActUtils(getActContext())).setOkResult(bn);
        finish();

    }


    public Context getActContext() {
        return AddAddressActivity.this;
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;

        setGoogleMapCameraListener(this);

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);

        LatLng placeLocation = getLocationLatLng();
        if (placeLocation != null) {
            Location temploc = new Location("source");
            temploc.setLatitude(placeLocation.latitude);
            temploc.setLongitude(placeLocation.longitude);
            onLocationUpdate(temploc);
        }
    }

    @Override
    protected void onDestroy() {
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        releaseResources();
        super.onDestroy();
    }

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }

        if (isFirstLocation) {
            LatLng placeLocation = getLocationLatLng();
            if (isAddressEnable && listener == null) {
                setGoogleMapCameraListener(this);
            }
            pinImgView.setVisibility(View.VISIBLE);
            if (placeLocation != null) {
                setCameraPosition(new LatLng(placeLocation.latitude, placeLocation.longitude));
            } else {
                setCameraPosition(new LatLng(location.getLatitude(), location.getLongitude()));
            }

            pinImgView.setVisibility(View.VISIBLE);
            isFirstLocation = false;
        }

        userLocation = location;
    }


    private void setCameraPosition(LatLng location) {
        if (gMap != null) {
            CameraPosition cameraPosition = new CameraPosition.Builder().target(
                            new LatLng(location.latitude,
                                    location.longitude))
                    .zoom(Utils.defaultZomLevel).build();
            gMap.moveCamera(CameraUpdateFactory.newCameraPosition(cameraPosition));
        }
    }

    private LatLng getLocationLatLng() {
        LatLng placeLocation = null;

        String CURRENT_ADDRESS = generalFunc.retrieveValue(Utils.CURRENT_ADDRESSS);

        if (getIntent().hasExtra("iCompanyId") && CURRENT_ADDRESS != null && !CURRENT_ADDRESS.equalsIgnoreCase("")) {
            address = CURRENT_ADDRESS;
            addresslatitude = generalFunc.retrieveValue(Utils.CURRENT_LATITUDE);
            addresslongitude = generalFunc.retrieveValue(Utils.CURRENT_LONGITUDE);

            if (iCompanyId != null && iCompanyId.equalsIgnoreCase("-1")) {
                addresslatitude = getIntent().getStringExtra("latitude");
                addresslongitude = getIntent().getStringExtra("longitude");
                address = getIntent().getStringExtra("address");
            }
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, addresslatitude), GeneralFunctions.parseDoubleValue(0.0, addresslongitude));

            isAddressEnable = true;
            pinImgView.setVisibility(View.VISIBLE);
            locAddrTxtView.setText(address);
        } else if (getIntent().hasExtra("latitude") && !getIntent().getStringExtra("latitude").equalsIgnoreCase("") && getIntent().hasExtra("longitude") && !getIntent().getStringExtra("longitude").equalsIgnoreCase("") && !getIntent().getStringExtra("address").equalsIgnoreCase("") && getIntent().hasExtra("address")) {

            isAddressEnable = true;
            addresslatitude = getIntent().getStringExtra("latitude");
            addresslongitude = getIntent().getStringExtra("longitude");
            address = getIntent().getStringExtra("address");
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, addresslatitude), GeneralFunctions.parseDoubleValue(0.0, addresslongitude));

            pinImgView.setVisibility(View.VISIBLE);
            locAddrTxtView.setText(address);

        } else if (userLocation != null) {
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLatitude()), GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLongitude()));

        } else {

            if (!generalFunc.checkLocationPermission(true)) {
                return placeLocation;
            }

            LocationManager locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
            Location getLastLocation = locationManager.getLastKnownLocation(LocationManager.PASSIVE_PROVIDER);
            if (getLastLocation == null) {
                Location providerLoc = GetLocationUpdates.getInstance().getLastLocation();
                if (providerLoc != null) {
                    getLastLocation = providerLoc;
                }
            }

            if (getLastLocation != null) {
                LatLng UserLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLatitude()),
                        GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLongitude()));
                if (UserLocation.latitude != 0.0 && UserLocation.longitude != 0.0) {
                    placeLocation = UserLocation;
                }
            }
        }


        return placeLocation;
    }

    public void releaseResources() {
        setGoogleMapCameraListener(null);
        this.gMap = null;
        getAddressFromLocation.setAddressList(null);
        getAddressFromLocation = null;
    }

    public void setGoogleMapCameraListener(AddAddressActivity act) {
        listener = act;
        if (gMap != null) {
            this.gMap.setOnCameraMoveStartedListener(act);
            this.gMap.setOnCameraIdleListener(act);
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {

            address = data.getStringExtra("Address");

            addresslatitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            addresslongitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, addresslatitude),
                    GeneralFunctions.parseDoubleValue(0.0, addresslongitude));
            if (placeLocation != null) {

                setCameraPosition(new LatLng(placeLocation.latitude, placeLocation.longitude));
                pinImgView.setVisibility(View.VISIBLE);
            }
            locAddrTxtView.setText(address);
        }
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        locAddrTxtView.setText(address);
        this.address = address;
        isPlaceSelected = true;
        this.placeLocation = new LatLng(latitude, longitude);

        addresslatitude = latitude + "";
        addresslongitude = longitude + "";

        CameraUpdate cu = CameraUpdateFactory.newLatLngZoom(this.placeLocation, 14.0f);
        if (gMap != null) {
            gMap.clear();
            if (isFirstLocation) {
                gMap.moveCamera(cu);
            }
            isFirstLocation = false;
            setGoogleMapCameraListener(this);
        }

    }

    @Override
    public void onCameraIdle() {


        if (getAddressFromLocation == null || pinImgView.getVisibility() == View.GONE) {

            return;
        }


        LatLng center = null;
        if (gMap != null && gMap.getCameraPosition() != null) {
            center = gMap.getCameraPosition().target;
        }

        if (center == null) {
            return;
        }


        if (!isAddressEnable) {
            setGoogleMapCameraListener(null);
            getAddressFromLocation.setLocation(center.latitude, center.longitude);
            getAddressFromLocation.setLoaderEnable(true);
            getAddressFromLocation.execute();
        } else {
            isAddressEnable = false;
        }

    }

    @Override
    public void onCameraMoveStarted(int i) {
        if (pinImgView.getVisibility() == View.VISIBLE) {
            if (!isAddressEnable) {
                locAddrTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
            }
        }

    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            AddAddressActivity.super.onBackPressed();
        } else if (i == R.id.loc_area) {

            if (generalFunc.isLocationEnabled()) {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "source");
                bn.putBoolean("isaddressview", true);
                bn.putString("hideSetMapLoc", "");
                if (getIntent().hasExtra("iCompanyId")) {
                    bn.putString("eSystem", Utils.eSystem_Type);
                }
                new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class,
                        bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);
            } else {

            }


        } else if (i == locationImage.getId()) {
            loc_area.performClick();
        } else if (i == btn_type2.getId()) {
            if (Utils.checkText(address)) {
                handleDetails();

            } else {
                generalFunc.showMessage(backImgView, generalFunc.retrieveLangLBl("", "LBL_SELECT_ADDRESS_TITLE_TXT"));

            }

        }
    }

}
