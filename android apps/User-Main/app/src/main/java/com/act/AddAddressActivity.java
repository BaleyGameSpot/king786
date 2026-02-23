package com.act;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationManager;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityAddAddressBinding;
import com.map.GeoMapLoader;
import com.map.minterface.OnCameraIdleListener;
import com.map.minterface.OnCameraMoveStartedListener;
import com.map.models.LatLng;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.HashMap;
import java.util.Objects;

public class AddAddressActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback, GetLocationUpdates.LocationUpdates, GetAddressFromLocation.AddressFound, OnCameraMoveStartedListener, OnCameraIdleListener {

    private ActivityAddAddressBinding binding;

    String addresslatitude, addresslongitude, address;
    MButton btn_type2;

    String required_str = "", type = "", iUserAddressId, quantity = "0", SelectedVehicleTypeId = "", iCompanyId;
    GetAddressFromLocation getAddressFromLocation;
    GeoMapLoader.GeoMap geoMap;
    public boolean isPlaceSelected = false;
    private boolean isBlockIdle = false, isGenie = false, isChangeClick = false, isGenieAddress = false, PICK_DROP_GENIE = false, isFromMulti = false, address_unavailable = false;
    private boolean isAddressEnable = false, isFirstLocation = true;
    private LatLng placeLocation;
    private GetLocationUpdates getLastLocation;
    private Location userLocation;
    private AddAddressActivity listener;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_add_address);

        PICK_DROP_GENIE = getIntent().getBooleanExtra("PICK_DROP_GENIE", false);

        quantity = getIntent().getStringExtra("Quantity");
        SelectedVehicleTypeId = getIntent().getStringExtra("SelectedVehicleTypeId");
        isGenie = getIntent().getBooleanExtra("isGenie", false);
        isGenieAddress = getIntent().getBooleanExtra("isGenieAddress", false);

        isFromMulti = getIntent().getBooleanExtra("isFromMulti", false);

        binding.pinImgView.setVisibility(View.GONE);
        binding.StoreBox.getRoot().setVisibility(View.GONE);
        if (isGenie && !isGenieAddress) {
            binding.buildingBox.getRoot().setVisibility(View.GONE);
            binding.addrtypeBox.getRoot().setVisibility(View.GONE);
            binding.landmarkBox.getRoot().setVisibility(View.GONE);
            if (PICK_DROP_GENIE) {
                binding.buildingBox.getRoot().setVisibility(View.VISIBLE);
            } else {
                binding.StoreBox.getRoot().setVisibility(View.VISIBLE);
            }
        }


        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        addToClickHandler(btn_type2);
        addToClickHandler(binding.locArea);
        binding.locAddrTxtView.setText(address);
        addToClickHandler(binding.backImgView);
        addToClickHandler(binding.editLocation);
        setLabel();

        if (getIntent().hasExtra("iCompanyId")) {
            iCompanyId = getIntent().getStringExtra("iCompanyId");
        }

        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);

        (new GeoMapLoader(this, R.id.mapFragmentContainer)).bindMap(this);


        if (isGenie && !isGenieAddress) {
            binding.editLocation.performClick();
            isChangeClick = true;
        }

        binding.personNameArea.setVisibility(PICK_DROP_GENIE ? View.VISIBLE : View.GONE);

        if (generalFunc.isRTLmode()) {
            binding.backImgView.setRotation(180);
        }

        if (isFromMulti) {
            binding.StoreBox.getRoot().setVisibility(View.GONE);
            binding.addrtypeBox.getRoot().setVisibility(View.GONE);
            binding.landmarkBox.getRoot().setVisibility(View.GONE);
            binding.buildingBox.getRoot().setVisibility(View.VISIBLE);
            binding.buildingBox.mEditText.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_DETAILS_TXT"));
            binding.detailAddressArea.setVisibility(View.VISIBLE);
            binding.detailAddressHeaderTextView.setVisibility(View.VISIBLE);
            binding.detailAddressHeaderTextView.setText(generalFunc.retrieveLangLBl("", "LBL_LOCALITY_HINT_TXT"));
            binding.detailAddressEditText.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_DETAILS_TXT"));
            binding.personNameArea.setVisibility(View.GONE);
            split_address(getIntent().getStringExtra("address"));
        }
    }

    private void split_address(String address) {
        if (!Objects.equals(address, "") && address != null && (getIntent().getIntExtra("selectedPos", -1) != -1 || getIntent().getBooleanExtra("issubmit", false))) {
            String splitted[] = address.split(",", 3);
            String location_address = splitted[2];
            binding.buildingBox.mEditText.setText(getIntent().getStringExtra("building").trim());
            binding.detailAddressEditText.setText(getIntent().getStringExtra("apartment").trim());
            location_address = address.substring(getIntent().getStringExtra("building").length() + getIntent().getStringExtra("apartment").length() + 6);
            binding.locAddrTxtView.setText(location_address.trim());

        } else {
            binding.editLocation.performClick();
            address_unavailable = true;
            binding.buildingBox.mEditText.setText("");
            binding.detailAddressEditText.setText("");
        }

    }

    public void releaseResources() {
        setGoogleMapCameraListener(null);
        this.geoMap = null;
        getAddressFromLocation.setAddressList(null);
        getAddressFromLocation = null;
    }

    public void setGoogleMapCameraListener(AddAddressActivity act) {
        listener = act;
        this.geoMap.setOnCameraMoveStartedListener(act);
        this.geoMap.setOnCameraIdleListener(act);

    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {

        this.geoMap = geoMap;
        setGoogleMapCameraListener(this);

        checkLocation();
        geoMap.getUiSettings().setCompassEnabled(false);

        // TODO : Last execute code
        if (isFirstLocation) {
            if (Utils.checkText(addresslatitude) && Utils.checkText(addresslongitude)) {
                Location temploc = new Location("source");
                temploc.setLatitude(Double.parseDouble(addresslatitude));
                temploc.setLongitude(Double.parseDouble(addresslongitude));
                onLocationUpdate(temploc);
            }
        }

    }

    private void checkLocation() {
        if (generalFunc.isLocationPermissionGranted(false) && !isPlaceSelected && !isChangeClick) {
            getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, false, this);
            getLocationLatLng(true);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (geoMap != null) {
            checkLocation();
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

    @Override
    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }

        if (isFirstLocation) {
            LatLng placeLocation = getLocationLatLng(true);

            if (isAddressEnable && listener == null) {
                setGoogleMapCameraListener(this);
            }
            if (placeLocation != null) {
                setCameraPosition(new LatLng(placeLocation.latitude, placeLocation.longitude));
            } else {
                setCameraPosition(new LatLng(location.getLatitude(), location.getLongitude()));
            }
            binding.pinImgView.setVisibility(View.VISIBLE);
            isFirstLocation = false;
        }

        userLocation = location;
    }

    private void setCameraPosition(LatLng location) {
        try {
            geoMap.moveCamera(new LatLng(location.latitude, location.longitude, Utils.defaultZomLevel));
        } catch (Exception e) {

        }
    }

    private LatLng getLocationLatLng(boolean setText) {
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

            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, addresslatitude),
                    GeneralFunctions.parseDoubleValue(0.0, addresslongitude));
            isAddressEnable = true;
            binding.pinImgView.setVisibility(View.VISIBLE);
            binding.locAddrTxtView.setText(address);
        } else if (userLocation != null) {
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLatitude()),
                    GeneralFunctions.parseDoubleValue(0.0, "" + userLocation.getLongitude()));

        } else {
            if (!generalFunc.checkLocationPermission(true)) {
                return placeLocation;
            }

            LocationManager locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
            Location getLastLocation = locationManager.getLastKnownLocation(LocationManager.PASSIVE_PROVIDER);
            if (getLastLocation == null) {
                getLastLocation = MyApp.getInstance().currentLocation;
            }
            if (getLastLocation != null) {
                LatLng UserLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLatitude()),
                        GeneralFunctions.parseDoubleValue(0.0, "" + getLastLocation.getLongitude()), Utils.defaultZomLevel);
                if (UserLocation.latitude != 0.0 && UserLocation.longitude != 0.0) {
                    placeLocation = UserLocation;
                }
            }

            if (isFromMulti) {
                double lat = getIntent().getDoubleExtra("lat", 0.0);
                double lon = getIntent().getDoubleExtra("long", 0.0);
                if (lat != 0.0 && lon != 0.0) {

                    addresslatitude = String.valueOf(lat);
                    addresslongitude = String.valueOf(lon);
                    address = getIntent().getStringExtra("address");

                    placeLocation = new LatLng(lat, lon);
                    isAddressEnable = true;
                }
            }
        }


        return placeLocation;
    }

    private void setLabel() {
        binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_NEW_ADDRESS_TXT"));
        if (isGenie && !isGenieAddress && !PICK_DROP_GENIE) {
            binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STORE_LOCATION"));
        } else if (PICK_DROP_GENIE) {
            binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_PICK_UP_ADDRESS"));
        }
        binding.buildingBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_HINT_INFO"));
        binding.personNameBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_PERSON_NAME"));
        binding.landmarkBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_LANDMARK_HINT_INFO"));
        binding.addrtypeBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_ADDRESSTYPE_HINT_INFO"));

        binding.buildingBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_JOB_LOCATION_HINT_INFO"));
        binding.personNameBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_PERSON_NAME"));
        binding.landmarkBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_LANDMARK_HINT_INFO"));
        if (isGenie) {
            binding.StoreBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_STORE_NAME_FOR_GENIE"));
            binding.StoreBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_STORE_NAME_FOR_GENIE"));
        } else {
            binding.StoreBox.mTextH.setText(generalFunc.retrieveLangLBl("", "LBL_STORE_NAME"));
            binding.StoreBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_STORE_NAME"));
        }
        binding.addrtypeBox.mEditText.setBothText(generalFunc.retrieveLangLBl("", "LBL_ADDRESSTYPE_HINT_INFO"));

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_SAVE_ADDRESS_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        binding.locAddrTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SET_STORE_LOCATION"));
    }

    public void checkValues() {

        if (isFromMulti) {
            boolean buildingDataenterd = Utils.checkText(binding.buildingBox.mEditText) ? true
                    : Utils.setErrorFields(binding.buildingBox.mEditText, required_str);
            boolean apartmentAddressDataEntered = Utils.checkText(binding.detailAddressEditText) ? true
                    : Utils.setErrorFields(binding.detailAddressEditText, required_str);


            if (buildingDataenterd && apartmentAddressDataEntered) {
                Bundle bn = new Bundle();
                String address = binding.buildingBox.mEditText.getText().toString() + " , ";
                address = address + binding.detailAddressEditText.getText().toString() + " , ";
                address = address + binding.locAddrTxtView.getText().toString();
                bn.putBoolean("isFromMulti", true);
                bn.putString("Address", address);
                bn.putString("Building", binding.buildingBox.mEditText.getText().toString());
                bn.putString("Apartment", binding.detailAddressEditText.getText().toString());
                bn.putString("Latitude", addresslatitude);
                bn.putString("Longitude", addresslongitude);
                bn.putInt("pos", getIntent().getIntExtra("pos", -1));
                new ActUtils(getActContext()).setOkResult(bn);
                finish();
            }
            return;
        }

        if (isGenie && !isGenieAddress) {

            if (PICK_DROP_GENIE) {
                boolean buildingDataenterd = Utils.checkText(binding.buildingBox.mEditText) ? true
                        : Utils.setErrorFields(binding.buildingBox.mEditText, required_str);
                boolean personDataenterd = Utils.checkText(binding.personNameBox.mEditText) ? true
                        : Utils.setErrorFields(binding.personNameBox.mEditText, required_str);

                if (!buildingDataenterd || !personDataenterd) {
                    return;
                }
            } else {

                boolean storeDataenterd = Utils.checkText(binding.StoreBox.mEditText) ? true
                        : Utils.setErrorFields(binding.StoreBox.mEditText, required_str);
                if (storeDataenterd == false) {
                    return;
                }
            }
        } else {
            boolean buildingDataenterd = Utils.checkText(binding.buildingBox.mEditText) ? true
                    : Utils.setErrorFields(binding.buildingBox.mEditText, required_str);
            boolean landmarkDataenterd = Utils.checkText(binding.landmarkBox.mEditText) ? true
                    : Utils.setErrorFields(binding.landmarkBox.mEditText, required_str);

            if (!buildingDataenterd || !landmarkDataenterd) {
                return;
            }
        }


        if (!getIntent().hasExtra("iCompanyId")) {
            if (addresslatitude.equalsIgnoreCase("") || addresslatitude.equalsIgnoreCase("0.0") || addresslongitude.equalsIgnoreCase("") || addresslongitude.equalsIgnoreCase("0.0")) {
                generalFunc.showMessage(btn_type2, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));

                return;
            }
        }
        if (isGenie && !isGenieAddress) {
            Intent returnIntent = new Intent();
            returnIntent.putExtra("vServiceAddress", address);
            returnIntent.putExtra("vLandmark", binding.landmarkBox.mEditText.getText().toString().trim());
            returnIntent.putExtra("vLatitude", addresslatitude);
            returnIntent.putExtra("vLongitude", addresslongitude);
            if (PICK_DROP_GENIE) {
                returnIntent.putExtra("vstorename", binding.buildingBox.mEditText.getText().toString().trim());
                returnIntent.putExtra("vpersonName", binding.personNameBox.mEditText.getText().toString().trim());
            } else {
                returnIntent.putExtra("vstorename", binding.StoreBox.mEditText.getText().toString().trim());
            }
            setResult(Activity.RESULT_OK, returnIntent);
            finish();

        } else {
            addDeliveryAddr();
        }
    }

    private void addDeliveryAddr() {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "UpdateUserAddressDetails");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        parameters.put("vServiceAddress", address);
        parameters.put("vBuildingNo", binding.buildingBox.mEditText.getText().toString().trim());
        parameters.put("vLandmark", binding.landmarkBox.mEditText.getText().toString().trim());
        parameters.put("vAddressType", binding.addrtypeBox.mEditText.getText().toString().trim());
        parameters.put("vLatitude", addresslatitude);
        parameters.put("vLongitude", addresslongitude);
        if (isGenie) {
            parameters.put("eCatType", "Genie");

        }

        if (getIntent().hasExtra("iCompanyId")) {
            parameters.put("iCompanyId", iCompanyId);
        } else if (getIntent().getBooleanExtra("isBid", false)) {
        } else {
            parameters.put("iUserAddressId", "");
//            parameters.put("iSelectVehicalId", "");
            parameters.put("iSelectVehicalId", SelectedVehicleTypeId);

        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    if (geoMap != null) {
                        geoMap.setPadding(0, 0, 0, 0);
                    }
                    btn_type2.setText(generalFunc.retrieveLangLBl("add Location", "LBL_ADD_LOC"));

                    if (getIntent().hasExtra("iCompanyId") || getIntent().getBooleanExtra("isBid", false)) {


                        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                        generateAlert.setCancelable(false);
                        generateAlert.setBtnClickList(btn_id -> {
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                            Intent returnIntent = new Intent();
                            returnIntent.putExtra("ToTalAddress", "1");
                            setResult(Activity.RESULT_OK, returnIntent);
                            finish();

                        });
                        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                        generateAlert.showAlertBox();

                    } else {

                        String userprofileJson = "";


                        generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));

                        userprofileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);


                        String IsProceed = generalFunc.getJsonValue("IsProceed", responseString);

                        if (IsProceed.equalsIgnoreCase("No")) {

                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                generateAlert.closeAlertBox();
                                new ActUtils(getActContext()).setOkResult();
                                binding.backImgView.performClick();

                            });
                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("Job Location not allowed", "LBL_JOB_LOCATION_NOT_ALLOWED"));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                            generateAlert.showAlertBox();
                            return;
                        }

                        iUserAddressId = generalFunc.getJsonValue("AddressId", responseString);

                        if (type.equals(Utils.CabReqType_Later)) {


                            if (generalFunc.getJsonValue("ToTalAddress", userprofileJson).equals("1")) {
                                Bundle bundle = new Bundle();
                                bundle.putString("latitude", addresslatitude);
                                bundle.putString("longitude", addresslongitude);
                                bundle.putString("address", address);
                                bundle.putString("iUserAddressId", iUserAddressId);
                                bundle.putString("SelectvVehicleType", getIntent().getStringExtra("SelectvVehicleType"));
                                bundle.putString("SelectvVehiclePrice", getIntent().getStringExtra("SelectvVehiclePrice"));
                                bundle.putString("iUserAddressId", iUserAddressId);
                                bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
                                bundle.putString("Quantity", quantity);
                                bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                                bundle.putBoolean("isWalletShow", getIntent().getBooleanExtra("isWalletShow", false));
                                new ActUtils(getActContext()).startActWithData(ScheduleDateSelectActivity.class, bundle);

                                finish();
                            } else {
                                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                                generateAlert.setCancelable(false);
                                generateAlert.setBtnClickList(btn_id -> {
                                    generateAlert.closeAlertBox();
                                    new ActUtils(getActContext()).setOkResult();
                                    binding.backImgView.performClick();

                                });
                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                                generateAlert.showAlertBox();
                            }

                        } else {


                            if (generalFunc.getJsonValue("ToTalAddress", userprofileJson).equals("1")) {
                                Bundle bundle = new Bundle();
                                bundle.putBoolean("isufx", true);
                                bundle.putString("latitude", addresslatitude);
                                bundle.putString("longitude", addresslongitude);
                                bundle.putString("address", address);
                                bundle.putString("SelectvVehicleType", getIntent().getStringExtra("SelectvVehicleType"));
                                bundle.putString("SelectvVehiclePrice", getIntent().getStringExtra("SelectvVehiclePrice"));
                                bundle.putString("type", Utils.CabReqType_Now);
                                bundle.putString("iUserAddressId", iUserAddressId);
                                bundle.putString("Quantity", quantity);
                                bundle.putString("Quantityprice", getIntent().getStringExtra("Quantityprice"));
                                bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
                                bundle.putString("Sdate", "");
                                bundle.putString("Stime", "");
                                bundle.putBoolean("isWalletShow", getIntent().getBooleanExtra("isWalletShow", false));
                                new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);
                                finish();
                            } else {
                                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                                generateAlert.setCancelable(false);
                                generateAlert.setBtnClickList(btn_id -> {
                                    generateAlert.closeAlertBox();


                                    new ActUtils(getActContext()).setOkResult();
                                    binding.backImgView.performClick();

                                });
                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                                generateAlert.showAlertBox();
                            }
                        }
                    }

                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public Context getActContext() {
        return AddAddressActivity.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {
            isChangeClick = false;
            isFirstLocation = false;
            address = data.getStringExtra("Address");
            isPlaceSelected = true;
            addresslatitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            addresslongitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");
            placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, addresslatitude),
                    GeneralFunctions.parseDoubleValue(0.0, addresslongitude));
            if (placeLocation != null) {
                isBlockIdle = true;
                setCameraPosition(new LatLng(placeLocation.latitude, placeLocation.longitude));
                binding.pinImgView.setVisibility(View.VISIBLE);
            }
            binding.locAddrTxtView.setText(address);
            if (isGenie && Utils.checkText(address)) {
                String splitted[] = address.split(",", 2);
                binding.buildingBox.mEditText.setText(splitted[0].trim());
            }
        } else if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_CANCELED && data != null) {
            if (isChangeClick && isGenie && !isGenieAddress) {
                isChangeClick = false;
                binding.backImgView.performClick();
            }
        } else if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_CANCELED && data == null) {
            if (isFromMulti && address_unavailable && !isPlaceSelected) {
                address_unavailable = false;
                binding.backImgView.performClick();
            }
        }
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        binding.locAddrTxtView.setText(address);
        this.address = address;
        isPlaceSelected = true;
        this.placeLocation = new LatLng(latitude, longitude);

        addresslatitude = latitude + "";
        addresslongitude = longitude + "";

        if (geoMap != null) {
            geoMap.clear();
            if (isFirstLocation) {
                geoMap.moveCamera(this.placeLocation.zoom(14.0f));
            }
            isFirstLocation = false;
            setGoogleMapCameraListener(this);
        }

    }

    @Override
    public void onCameraMoveStarted(int i) {
        if (binding.pinImgView.getVisibility() == View.VISIBLE) {
            if (!isAddressEnable) {
                binding.locAddrTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
            }
        }

    }


    @Override
    public void onCameraIdle() {


        if (getAddressFromLocation == null || isBlockIdle || binding.pinImgView.getVisibility() == View.GONE) {
            isBlockIdle = false;
            return;
        }


        LatLng center = null;
        if (geoMap != null && geoMap.getCameraPosition() != null) {
            center = geoMap.getCameraPosition();
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


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            AddAddressActivity.super.onBackPressed();
        } else if (i == R.id.loc_area) {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            bn.putBoolean("isaddressview", true);
            if (getIntent().hasExtra("iCompanyId")) {
                bn.putString("eSystem", Utils.eSystem_Type);
            }
            bn.putBoolean("isGenie", getIntent().getBooleanExtra("isGenie", false));

            if (getIntent().getStringExtra("latitude") != null && !getIntent().getStringExtra("latitude").equals("")) {

                bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("latitude")));
                bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, getIntent().getStringExtra("longitude")));
            } else {
                if (MyApp.getInstance().currentLocation != null) {
                    bn.putDouble("lat", MyApp.getInstance().currentLocation.getLatitude());
                    bn.putDouble("long", MyApp.getInstance().currentLocation.getLongitude());
                }
                bn.putString("PickUpAddress", address);
            }


            new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class, bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);

        } else if (i == binding.editLocation.getId()) {
            binding.locArea.performClick();
        } else if (i == btn_type2.getId()) {
            if (Utils.checkText(address)) {
                checkValues();
            } else {
                if (isGenie) {
                    generalFunc.showMessage(binding.backImgView, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));

                } else {
                    generalFunc.showMessage(binding.backImgView, generalFunc.retrieveLangLBl("", "LBL_SELECT_ADDRESS_TITLE_TXT"));
                }
            }

        }
    }

}
