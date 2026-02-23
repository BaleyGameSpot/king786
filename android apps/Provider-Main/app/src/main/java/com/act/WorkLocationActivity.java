package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.text.InputFilter;
import android.text.InputType;
import android.view.MotionEvent;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.core.widget.NestedScrollView;

import com.activity.ParentActivity;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class WorkLocationActivity extends ParentActivity implements GetAddressFromLocation.AddressFound, GetLocationUpdates.LocationUpdatesListener {


    MTextView titleTxt;
    ImageView backImgView;

    ArrayList<String> items_work_location = new ArrayList<>();
    ArrayList<String> real_items_work_location = new ArrayList<>();
    ArrayList<HashMap<String, String>> items_work_radius = new ArrayList<>();
    MaterialEditText otherBox, durationtxt, locationtxt;
    String selected_work_location = "";
    String selected_work_radius = "";
    MTextView addressTxt, workradiusTitleTxt, workLocTitleTxt;
    GetAddressFromLocation getAddressFromLocation;
    Location location;
    ImageView editLocation, locationdrop, durationdrop;
    String eSelectWorkLocation = "";
    String vCountryUnitDriver = "";
    LinearLayout otherArea;
    MButton btn_type2;
    int submitBtnId;
    String required_str;

    MTextView demonoteText, demoText;
    MTextView noteText, noteDetailsText;
    LinearLayout workLocationArea, screenNoteAreaView;
    String SERVICE_PROVIDER_FLOW = "";
    private static final int ADD_ADDRESS = 006;
    CheckBox checkboxWork;
    LinearLayout addressArea;
    private NestedScrollView nesScrollView;
    private ProgressBar loading;
    View locationBox, durationBox;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_work_location);


        SERVICE_PROVIDER_FLOW = generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile);
        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);
        initViews();
        getDetails();


    }


    public void handleWorkAddress() {


        if (generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("Yes")) {
            if (eSelectWorkLocation.equalsIgnoreCase("")) {
                return;
            }

            if (eSelectWorkLocation.equalsIgnoreCase("Fixed")) {
                editLocation.setVisibility(View.VISIBLE);

                String WORKLOCATION = generalFunc.retrieveValue(Utils.WORKLOCATION);
                if (!WORKLOCATION.equals("")) {
                    addressTxt.setText(WORKLOCATION);
                } else {
                    if (location != null) {
                        getAddressFromLocation.setLocation(location.getLatitude(), location.getLongitude());
                        getAddressFromLocation.execute();
                    }
                }
            } else {
                editLocation.setVisibility(View.GONE);
                if (location != null) {
                    getAddressFromLocation.setLocation(location.getLatitude(), location.getLongitude());
                    getAddressFromLocation.execute();
                }
            }
        } else {

            editLocation.setVisibility(View.GONE);

            if (generalFunc.getJsonValueStr("ENABLE_SERVICE_AT_USER_LOC", obj_userProfile).equalsIgnoreCase("Yes")) {
                checkboxWork.setVisibility(View.VISIBLE);
            }

        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        handleWorkAddress();

        try {
            new Handler().postDelayed(() -> nesScrollView.setPadding(0, 0, 0, screenNoteAreaView.getHeight() + (((screenNoteAreaView).getHeight() * 10) / 100)), 500);
        } catch (Exception e) {

        }

    }

    public void setLabel() {
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_WORK_LOCATION"));

        addressTxt.setText(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"));
        workradiusTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RADIUS"));
        workLocTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YOUR_JOB_LOCATION_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        demonoteText.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + ":");

        demoText.setText(generalFunc.retrieveLangLBl("", "LBL_WORK_LOCATION_NOTE"));
        noteText.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + ":");
    }

    @SuppressLint("ClickableViewAccessibility")
    public void initViews() {

        backImgView = findViewById(R.id.backImgView);

        nesScrollView = findViewById(R.id.nesScrollView);
        screenNoteAreaView = findViewById(R.id.screenNoteAreaView);
        loading = findViewById(R.id.loading);
        nesScrollView.setVisibility(View.GONE);
        loading.setVisibility(View.GONE);
        checkboxWork = findViewById(R.id.checkboxWork);
        titleTxt = findViewById(R.id.titleTxt);
        addToClickHandler(backImgView);

        locationBox = findViewById(R.id.locationWorkBox);
        MTextView locationHtxt = locationBox.findViewById(R.id.mTextH);
        locationHtxt.setVisibility(View.GONE);
        locationtxt = locationBox.findViewById(R.id.mEditText);
        locationdrop = locationBox.findViewById(R.id.mDropDownArrow);
        locationdrop.setVisibility(View.VISIBLE);

        otherBox = findViewById(R.id.otherBox);
        workradiusTitleTxt = findViewById(R.id.workradiusTitleTxt);
        workLocTitleTxt = findViewById(R.id.workLocTitleTxt);
        demonoteText = findViewById(R.id.demonoteText);
        noteDetailsText = findViewById(R.id.noteDetailsText);
        workLocationArea = findViewById(R.id.workLocationArea);
        noteText = findViewById(R.id.noteText);
        demoText = findViewById(R.id.demoText);
        addressArea = findViewById(R.id.addressArea);
        otherBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        otherBox.setFilters(new InputFilter[]{new InputFilter.LengthFilter(4)});
        otherBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_RADIUS_HINT"));
        if (obj_userProfile != null && generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
            otherBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_RADIUS_PER_KMS"));
        } else {
            otherBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_RADIUS_PER_MILE"));
        }

        checkboxWork.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE_ENABLE_SERVICE_AT_PROVIDER_LOC"));

        if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
            checkboxWork.setVisibility(View.VISIBLE);
            if (generalFunc.getJsonValueStr("eEnableServiceAtLocation", obj_userProfile).equalsIgnoreCase("Yes")) {
                checkboxWork.setChecked(true);
            }
        }

        addToClickHandler(checkboxWork);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_SUBMIT_TXT"));

        durationBox = findViewById(R.id.radiusWorkBox);
        MTextView durationHtxt = durationBox.findViewById(R.id.mTextH);
        durationHtxt.setVisibility(View.GONE);
        durationtxt = durationBox.findViewById(R.id.mEditText);
        durationdrop = durationBox.findViewById(R.id.mDropDownArrow);
        durationdrop.setVisibility(View.VISIBLE);
        addressTxt = findViewById(R.id.addressTxt);
        editLocation = findViewById(R.id.editLocation);
        otherArea = findViewById(R.id.otherArea);


        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);


        setLabel();
        Utils.removeInput(locationtxt);
        Utils.removeInput(durationtxt);
        locationtxt.setOnTouchListener(new setOnTouchList());
        durationtxt.setOnTouchListener(new setOnTouchList());
        addToClickHandler(locationtxt);
        addToClickHandler(durationtxt);
        addToClickHandler(editLocation);
        locationtxt.setOnClickListener(view -> {
            //
            buildLocationWorkList();
        });
        durationtxt.setOnClickListener(view -> {
            //
            buildWorkRadiusList();
        });


        if (generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("yes")) {
            items_work_location.add(generalFunc.retrieveLangLBl("Specified Location", "LBL_SPECIFIED_LOCATION"));
            items_work_location.add(generalFunc.retrieveLangLBl("Any Location", "LBL_ANY_LOCATION"));
            real_items_work_location.add("Fixed");
            real_items_work_location.add("Dynamic");
            workLocationArea.setVisibility(View.VISIBLE);

            noteDetailsText.setText(generalFunc.retrieveLangLBl("", "LBL_INFO_WORK_LOCATION") + "\n\n" +
                    generalFunc.retrieveLangLBl("", "LBL_INFO_WORK_RADIUS"));

        } else {
            workLocationArea.setVisibility(View.GONE);
            workLocTitleTxt.setVisibility(View.GONE);
            noteDetailsText.setText(generalFunc.retrieveLangLBl("", "LBL_INFO_WORK_RADIUS"));
        }

        if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("PROVIDER") && generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("yes")) {
            noteDetailsText.setText(noteDetailsText.getText().toString() + "\n\n" + generalFunc.retrieveLangLBl("", "LBL_UFX_PROVIDER_LOC_NOTE"));
        }

        String eSelectWorkLocation = generalFunc.getJsonValueStr("eSelectWorkLocation", obj_userProfile);
        if (eSelectWorkLocation != null && !eSelectWorkLocation.equalsIgnoreCase("")) {
            selected_work_location = eSelectWorkLocation;

            if (selected_work_location.equalsIgnoreCase("Fixed")) {
                selCurrentPositionWork = 0;
                locationtxt.setText(generalFunc.retrieveLangLBl("Specified Location", "LBL_SPECIFIED_LOCATION"));

            } else {
                selCurrentPositionWork = 1;
                locationtxt.setText(generalFunc.retrieveLangLBl("Any Location", "LBL_ANY_LOCATION"));

            }
        }
        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        if (ServiceModule.Taxi || ServiceModule.Delivery) {
            screenNoteAreaView.setVisibility(View.VISIBLE);
        } else {
            screenNoteAreaView.setVisibility(View.GONE);
        }
    }


    public void updateuserRadius(final String val) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateRadius");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("vWorkLocationRadius", val);


        ApiHandler.execute(getActContext(), parameters, true, true, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {


                            final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                            generateAlert.setCancelable(false);
                            generateAlert.setBtnClickList(btn_id -> {
                                generateAlert.closeAlertBox();

                                otherArea.setVisibility(View.GONE);
                                otherBox.setText("");
                                getDetails();
                            });

                            generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("message1", responseString)));
                            generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));

                            generateAlert.showAlertBox();


                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {

        if (generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("eSelectWorkLocation", obj_userProfile).equalsIgnoreCase("Fixed")) {
            String WORKLOCATION = generalFunc.retrieveValue(Utils.WORKLOCATION);
            if (!WORKLOCATION.equals("")) {
                addressTxt.setText(WORKLOCATION);
            } else {
                addressTxt.setText(address);
            }
        } else {
            addressTxt.setText(address);
        }

    }

    @Override
    public void onLocationUpdate(Location location) {
        this.location = location;
    }


    public class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }

    public void MnageServiceLocation(Boolean ischeck) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "configureProviderServiceLocation");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("eEnableServiceAtLocation", ischeck ? "Yes" : "No");

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                            if (generalFunc.getJsonValueStr("eEnableServiceAtLocation", obj_userProfile).equalsIgnoreCase("Yes")) {
                                checkboxWork.setChecked(true);
                            } else {
                                checkboxWork.setChecked(false);
                            }


                        } else {
                            if (generalFunc.getJsonValueStr("eEnableServiceAtLocation", obj_userProfile).equalsIgnoreCase("Yes")) {
                                checkboxWork.setChecked(true);
                            } else {
                                checkboxWork.setChecked(false);
                            }
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }


    public void onClick(View view) {
        int id = view.getId();
        if (id == submitBtnId) {
            if (!Objects.requireNonNull(otherBox.getText()).toString().isEmpty()) {

                if (GeneralFunctions.parseIntegerValue(0, otherBox.getText().toString()) > 0) {
                    updateuserRadius(otherBox.getText().toString());
                } else {
                    Utils.setErrorFields(otherBox, generalFunc.retrieveLangLBl("", "LBL_FILL_PROPER_DETAILS"));
                }
            } else {
                Utils.setErrorFields(otherBox, required_str);
            }

        } else if (view == checkboxWork) {
            MnageServiceLocation(checkboxWork.isChecked());
            return;
        }

        if (id == R.id.backImgView) {
            WorkLocationActivity.super.onBackPressed();
        } else if (id == R.id.editLocation) {
            if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                Bundle bn = new Bundle();
                bn.putString("latitude", generalFunc.getJsonValueStr("vWorkLocationLatitude", obj_userProfile));
                bn.putString("longitude", generalFunc.getJsonValueStr("vWorkLocationLongitude", obj_userProfile));
                bn.putString("address", Utils.getText(addressTxt));
                new ActUtils(getActContext()).startActForResult(AddAddressActivity.class, bn, ADD_ADDRESS);

            } else {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "dest");
                if (location != null) {
                    bn.putDouble("lat", location.getLatitude());
                    bn.putDouble("long", location.getLongitude());
                }
                new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class, bn, Utils.SEARCH_PICKUP_LOC_REQ_CODE);
            }
        }
    }


    int selCurrentPositionWork = -1;

    public void buildLocationWorkList() {
        ArrayList<HashMap<String, String>> itemsList = new ArrayList<>();
        int loctaionlistsize = items_work_location.size();
        for (int i = 0; i < loctaionlistsize; i++) {
            HashMap<String, String> map = new HashMap<>();
            map.put("location", items_work_location.get(i));
            itemsList.add(map);
        }


        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_WORKLOCATION"), itemsList, OpenListView.OpenDirection.CENTER, true, position -> {


            selCurrentPositionWork = position;
            //  selected_work_location = mapData.get("location");
            selected_work_location = real_items_work_location.get(position);
            if (selected_work_location.equalsIgnoreCase("Fixed")) {

                if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
                    editLocation.performClick();
                    return;
                }

                locationtxt.setText(generalFunc.retrieveLangLBl("Specified Location", "LBL_SPECIFIED_LOCATION"));
            } else {
                locationtxt.setText(generalFunc.retrieveLangLBl("Any Location", "LBL_ANY_LOCATION"));
                if (SERVICE_PROVIDER_FLOW.equalsIgnoreCase("Provider")) {
                    checkboxWork.setVisibility(View.VISIBLE);
                }
            }

            updateWorkLocationSelection();


        }).show(selCurrentPositionWork, "location");


    }

    int selCurrentPositionRadius = -1;

    public void buildWorkRadiusList() {

        ArrayList<HashMap<String, String>> itemsList = new ArrayList<>();

        for (int i = 0; i < items_work_radius.size(); i++) {
            HashMap<String, String> map = new HashMap<>();
            if (!items_work_radius.get(i).get("value").equalsIgnoreCase("other")) {
                map.put("radius", (generalFunc.convertNumberWithRTL(items_work_radius.get(i).get("value")) + " " + items_work_radius.get(i).get("eUnit")));
            } else {
                map.put("radius", (generalFunc.convertNumberWithRTL(items_work_radius.get(i).get("name"))));
            }
            itemsList.add(map);
        }


        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_RADIUS"), itemsList, OpenListView.OpenDirection.CENTER, true, position -> {


            selCurrentPositionRadius = position;
            HashMap<String, String> mapData = itemsList.get(position);
            durationtxt.setText(mapData.get("radius"));
            selected_work_radius = mapData.get("radius");
            if (items_work_radius.get(position).get("value").equalsIgnoreCase("other")) {
                durationtxt.setText(generalFunc.convertNumberWithRTL(items_work_radius.get(position).get("name")));
                otherArea.setVisibility(View.VISIBLE);
                return;
            }
            updateuserRadius(selected_work_radius);

        }).show(selCurrentPositionRadius, "radius");


    }

    public void getDetails() {
        loading.setVisibility(View.VISIBLE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverWorkLocationUFX");
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    loading.setVisibility(View.GONE);
                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {
                            nesScrollView.setVisibility(View.VISIBLE);

                            items_work_radius.clear();

                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                            eSelectWorkLocation = generalFunc.getJsonValue("eSelectWorkLocation", message);
                            selected_work_location = eSelectWorkLocation;
                            vCountryUnitDriver = generalFunc.getJsonValue("vCountryUnitDriver", message);

                            generalFunc.storeData(Utils.WORKLOCATION, generalFunc.getJsonValue("vWorkLocation", message));

                            JSONArray radiusArray = generalFunc.getJsonArray("RadiusList", message);

                            for (int i = 0; i < radiusArray.length(); i++) {
                                JSONObject jsonObject = generalFunc.getJsonObject(radiusArray, i);

                                HashMap<String, String> map = new HashMap<>();
                                map.put("value", generalFunc.getJsonValue("value", jsonObject.toString()));
                                map.put("name", generalFunc.getJsonValue("value", jsonObject.toString()));
                                map.put("eUnit", generalFunc.getJsonValue("eUnit", jsonObject.toString()));
                                map.put("eSelected", generalFunc.getJsonValue("eSelected", jsonObject.toString()));

                                items_work_radius.add(map);
                            }

                            HashMap<String, String> map = new HashMap<>();
                            map.put("value", "Other");
                            map.put("name", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
                            map.put("eUnit", "");
                            map.put("eSelected", "");

                            items_work_radius.add(map);

                            handleWorkRadius();
                            handleWorkAddress();
                        } else {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), true);
                        }
                    } else {
                        generalFunc.showError(true);
                    }
                });
    }


    public void handleWorkRadius() {
        if (items_work_radius != null) {

            for (int i = 0; i < items_work_radius.size(); i++) {
                HashMap<String, String> workData = items_work_radius.get(i);
                if (workData.get("eSelected").equalsIgnoreCase("Yes")) {
                    selected_work_radius = workData.get("value");
                    durationtxt.setText(generalFunc.convertNumberWithRTL(selected_work_radius) + " " + workData.get("eUnit"));

                    selCurrentPositionRadius = i;
                    durationtxt.setText(generalFunc.convertNumberWithRTL(selected_work_radius) + " " + workData.get("eUnit"));
                }
            }

        }
    }

    public void updateWorkLocationSelection() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverWorkLocationSelectionUFX");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("eSelectWorkLocation", selected_work_location);
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                            eSelectWorkLocation = selected_work_location;
                            handleWorkAddress();

                        }
                    } else {
                        generalFunc.showError();
                    }
                });


    }

    public void updateWorkLocation(String worklat, String worklong, String workaddress) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverWorkLocationUFX");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("vWorkLocationLatitude", worklat);
        parameters.put("vWorkLocationLongitude", worklong);
        parameters.put("vWorkLocation", workaddress);

        if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
            parameters.put("eSelectWorkLocation", eSelectWorkLocation);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {
                            if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
                                eSelectWorkLocation = "Fixed";
                                parameters.put("eSelectWorkLocation", eSelectWorkLocation);
                                selCurrentPositionWork = 0;
                                locationtxt.setText(generalFunc.retrieveLangLBl("Specified Location", "LBL_SPECIFIED_LOCATION"));
                            }
                            addressTxt.setText(workaddress);
                            generalFunc.storeData(Utils.WORKLOCATION, workaddress);
                            handleWorkAddress();
                        }
                    } else {
                        generalFunc.showError();
                    }
                });


    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE) {
            if (resultCode == RESULT_OK) {
                String worklat = data.getStringExtra("Latitude");
                String worklong = data.getStringExtra("Longitude");
                String workadddress = data.getStringExtra("Address");

                updateWorkLocation(worklat, worklong, workadddress);
            }
        } else if (requestCode == ADD_ADDRESS) {
            if (resultCode == RESULT_OK) {
                String worklat = data.getStringExtra("Latitude");
                String worklong = data.getStringExtra("Longitude");
                String workadddress = data.getStringExtra("Address");

                updateWorkLocation(worklat, worklong, workadddress);
            } else if (resultCode == RESULT_CANCELED) {
                if (generalFunc.retrieveValue(Utils.WORKLOCATION).equals("")) {
                    selCurrentPositionWork = 1;
                    locationtxt.setText(generalFunc.retrieveLangLBl("", "LBL_ANY_LOCATION"));
                }
            }
        }
    }

    public Context getActContext() {
        return WorkLocationActivity.this;
    }
}
