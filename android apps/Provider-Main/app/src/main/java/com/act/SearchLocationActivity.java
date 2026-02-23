package com.act;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.PlacesAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;
import com.general.files.RecurringTask;
import com.google.android.gms.maps.model.LatLng;
import com.buddyverse.providers.R;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.ServiceColor;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

import java.security.SecureRandom;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;

public class SearchLocationActivity extends ParentActivity implements PlacesAdapter.setRecentLocClickList {

    public boolean isAddressEnable;
    String whichLocation = "";
    MTextView cancelTxt;
    RecyclerView placesRecyclerView;
    EditText searchTxt;
    ArrayList<HashMap<String, String>> placelist;
    PlacesAdapter placesAdapter;
    ImageView imageCancel;
    MTextView noPlacedata;
    InternetConnection intCheck;
    ImageView googleimagearea;

    String session_token = "";
    int MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE = 2;
    String currentSearchQuery = "";
    RecurringTask sessionTokenFreqTask = null;

    LinearLayout mapLocArea, sourceLocationView, destLocationView;
    MTextView mapLocTxt, homePlaceTxt, homePlaceHTxt;
    LinearLayout homeLocArea;
    MTextView placesTxt, recentLocHTxtView;
    LinearLayout placearea, placesarea;
    LinearLayout placesInfoArea;
    ImageView homeActionImgView, ivRightArrow2;

    JSONArray SourceLocations_arr;
    JSONArray DestinationLocations_arr;
    ArrayList<HashMap<String, String>> recentLocList = new ArrayList<>();
    ArrayList<HashMap<String, String>> colorHasmap = new ArrayList<>();
    ImageView homeroundImage, workroundImage;
    LinearLayout homeImgBack, workImgBack;
    // Handler handler = null;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_search_location);


        intCheck = new InternetConnection(getActContext());
        if (generalFunc.getJsonArray("RANDOM_COLORS_KEY_VAL_ARR", obj_userProfile) != null) {
            JSONArray jsonArray = generalFunc.getJsonArray("RANDOM_COLORS_KEY_VAL_ARR", obj_userProfile);
            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject jsonObject = generalFunc.getJsonObject(jsonArray, i);
                HashMap<String, String> colorMap = new HashMap<>();
                colorMap.put("BG_COLOR", generalFunc.getJsonValueStr("BG_COLOR", jsonObject));
                colorMap.put("TEXT_COLOR", generalFunc.getJsonValueStr("TEXT_COLOR", jsonObject));
                colorHasmap.add(colorMap);
            }
        }

        googleimagearea = (ImageView) findViewById(R.id.googleimagearea);
        cancelTxt = (MTextView) findViewById(R.id.cancelTxt);
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        placesRecyclerView = (RecyclerView) findViewById(R.id.placesRecyclerView);
        placesRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrollStateChanged(@NotNull RecyclerView recyclerView, int newState) {
                super.onScrollStateChanged(recyclerView, newState);
                Utils.hideKeyboard(getActContext());
            }
        });
        searchTxt = (EditText) findViewById(R.id.searchTxt);
        searchTxt.setHint(generalFunc.retrieveLangLBl("Search", "LBL_Search"));

        addToClickHandler(cancelTxt);
        imageCancel = (ImageView) findViewById(R.id.imageCancel);
        noPlacedata = (MTextView) findViewById(R.id.noPlacedata);
        addToClickHandler(imageCancel);

        homeroundImage = (ImageView) findViewById(R.id.homeroundImage);
        homeImgBack = (LinearLayout) findViewById(R.id.homeImgBack);

        workroundImage = (ImageView) findViewById(R.id.workroundImage);
        workImgBack = (LinearLayout) findViewById(R.id.workImgBack);

        homeLocArea = (LinearLayout) findViewById(R.id.homeLocArea);
        placesInfoArea = (LinearLayout) findViewById(R.id.placesInfoArea);
        placearea = (LinearLayout) findViewById(R.id.placearea);
        placesarea = (LinearLayout) findViewById(R.id.placesarea);
        homeActionImgView = (ImageView) findViewById(R.id.homeActionImgView);
        ivRightArrow2 = (ImageView) findViewById(R.id.ivRightArrow2);
        placesTxt = (MTextView) findViewById(R.id.locPlacesTxt);
        homePlaceTxt = (MTextView) findViewById(R.id.homePlaceTxt);
        homePlaceHTxt = (MTextView) findViewById(R.id.homePlaceHTxt);
        recentLocHTxtView = (MTextView) findViewById(R.id.recentLocHTxtView);
        mapLocArea = (LinearLayout) findViewById(R.id.mapLocArea);
        addToClickHandler(mapLocArea);
        mapLocTxt = (MTextView) findViewById(R.id.mapLocTxt);
        destLocationView = (LinearLayout) findViewById(R.id.destLocationView);
        sourceLocationView = (LinearLayout) findViewById(R.id.sourceLocationView);

        addToClickHandler(homeLocArea);
        addToClickHandler(placesTxt);
        addToClickHandler(homeActionImgView);

        if (generalFunc.isRTLmode()) {
            ivRightArrow2.setRotation(180);
        }

        setLables();

        showAddHomeAddressArea();
        placelist = new ArrayList<>();
        MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE = GeneralFunctions.parseIntegerValue(2, generalFunc.getJsonValueStr("MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE", obj_userProfile));


        searchTxt.setOnFocusChangeListener((v, hasFocus) -> {

            if (!hasFocus) {
                Utils.hideSoftKeyboard((Activity) getActContext(), searchTxt);
            } else {
                Utils.showSoftKeyboard((Activity) getActContext(), searchTxt);
            }
        });

        searchTxt.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {

                if (currentSearchQuery.equals(s.toString().trim())) {
                    return;
                }

                currentSearchQuery = searchTxt.getText().toString();

                if (s.length() >= MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE) {
                    if (session_token.trim().equalsIgnoreCase("")) {
                        session_token = Utils.userType + "_" + generalFunc.getMemberId() + "_" + System.currentTimeMillis();
                        initializeSessionRegeneration();
                    }

                    placesRecyclerView.setVisibility(View.VISIBLE);

                    if (getIntent().hasExtra("eSystem")) {
                        googleimagearea.setVisibility(View.VISIBLE);
                    }
                    placesarea.setVisibility(View.GONE);
                    getGooglePlaces(currentSearchQuery);
                } else {
                    if (getIntent().getBooleanExtra("isPlaceAreaShow", true)) {
                        placesarea.setVisibility(View.VISIBLE);
                    }
                    googleimagearea.setVisibility(View.GONE);
                    placesRecyclerView.setVisibility(View.GONE);
                    noPlacedata.setVisibility(View.GONE);
                }
            }
        });

        searchTxt.setOnEditorActionListener((v, actionId, event) -> {
            if (actionId == EditorInfo.IME_ACTION_SEARCH) {
                // getSearchGooglePlace(v.getText().toString());
                getGooglePlaces(v.getText().toString());
                return true;
            }
            return false;
        });

        if (getIntent().hasExtra("hideSetMapLoc")) {
            mapLocArea.setVisibility(View.GONE);
        } else {
            mapLocArea.setVisibility(View.VISIBLE);
        }

        if (getIntent().hasExtra("eSystem")) {
            mapLocArea.setVisibility(View.GONE);
        }

        placesRecyclerView.setHasFixedSize(false);
        RecyclerView.LayoutManager mLayoutManager = new LinearLayoutManager(getApplicationContext());
        placesRecyclerView.setLayoutManager(mLayoutManager);
        if (getCallingActivity() != null && getCallingActivity().getClassName().equals(AddAddressActivity.class.getName())) {
            (findViewById(R.id.recentScrollView)).setVisibility(View.GONE);
            (findViewById(R.id.recentLocHTxtView)).setVisibility(View.GONE);
        }

    }


    private void showAddHomeAddressArea() {
        if (getIntent().hasExtra("requestType")) {
            placesarea.setVisibility(View.VISIBLE);
            placesRecyclerView.setVisibility(View.GONE);
            googleimagearea.setVisibility(View.GONE);
            placesInfoArea.setVisibility(View.VISIBLE);
            setWhichLocationAreaSelected(getIntent().getStringExtra("locationArea"));
        } else {
            placesarea.setVisibility(View.GONE);
            placesRecyclerView.setVisibility(View.VISIBLE);
            placesInfoArea.setVisibility(View.GONE);
            googleimagearea.setVisibility(View.GONE);
            searchTxt.requestFocus();
            Utils.showSoftKeyboard((Activity) getActContext(), searchTxt);
        }
    }

    private void setLables() {
        homePlaceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
        homePlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
        mapLocTxt.setText(generalFunc.retrieveLangLBl("Set location on map", "LBL_SET_LOC_ON_MAP"));

        placesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAV_LOCATIONS"));
        recentLocHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_RECENT_LOCATIONS"));
        setRandomColor();

    }

    public void setRandomColor() {
        homeImgBack.getBackground().setColorFilter(ServiceColor.RIDE.color, PorterDuff.Mode.SRC_ATOP);
        workImgBack.getBackground().setColorFilter(ServiceColor.PARCEL_DELIVERY.color, PorterDuff.Mode.SRC_ATOP);
    }


    public void checkPlaces(final String whichLocationArea) {

        String home_address_str = generalFunc.retrieveValue("userHomeLocationAddress");


        if (home_address_str != null && !home_address_str.equalsIgnoreCase("")) {

            homePlaceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
            homePlaceTxt.setTextColor(getResources().getColor(R.color.black));
            homePlaceHTxt.setText("" + home_address_str);
            homePlaceHTxt.setVisibility(View.VISIBLE);
            homePlaceTxt.setVisibility(View.VISIBLE);
            homePlaceHTxt.setTextColor(Color.parseColor("#909090"));
            homeActionImgView.setImageResource(R.mipmap.ic_edit);

        } else {
            homePlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_HOME_PLACE"));
            homePlaceTxt.setText("" + generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
            homePlaceTxt.setTextColor(Color.parseColor("#909090"));
            homeActionImgView.setImageResource(R.mipmap.ic_pluse);
        }


        if (home_address_str != null && home_address_str.equalsIgnoreCase("")) {
            homePlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_HOME_PLACE_TXT"));
            homePlaceTxt.setText("----");
            homePlaceTxt.setVisibility(View.GONE);
            homePlaceHTxt.setTextColor(getResources().getColor(R.color.black));

            homePlaceTxt.setTextColor(Color.parseColor("#909090"));

            homePlaceHTxt.setVisibility(View.VISIBLE);
            homeActionImgView.setImageResource(R.mipmap.ic_pluse);
        }

    }

    private void getRecentLocations(final String whichView) {
        final LayoutInflater mInflater = (LayoutInflater)
                getActContext().getSystemService(Activity.LAYOUT_INFLATER_SERVICE);

        DestinationLocations_arr = generalFunc.getJsonArray("DestinationLocations", obj_userProfile);
        SourceLocations_arr = generalFunc.getJsonArray("SourceLocations", obj_userProfile);

        if (DestinationLocations_arr != null || SourceLocations_arr != null) {

            if (whichView.equals("dest")) {

                if (destLocationView != null) {
                    destLocationView.removeAllViews();
                    recentLocList.clear();
                }
                for (int i = 0; i < DestinationLocations_arr.length(); i++) {
                    final View view = mInflater.inflate(R.layout.item_recent_loc_design, null);
                    JSONObject destLoc_obj = generalFunc.getJsonObject(DestinationLocations_arr, i);


                    MTextView recentAddrTxtView = (MTextView) view.findViewById(R.id.recentAddrTxtView);
                    LinearLayout recentAdapterView = (LinearLayout) view.findViewById(R.id.recentAdapterView);
                    ImageView arrowImg = (ImageView) view.findViewById(R.id.arrowImg);
                    if (generalFunc.isRTLmode()) {
                        arrowImg.setRotation(180);
                    }

                    LinearLayout imageabackArea = (LinearLayout) view.findViewById(R.id.imageabackArea);
                    ImageView roundImage = (ImageView) view.findViewById(R.id.roundImage);
                    final String tEndLat = generalFunc.getJsonValueStr("tDestLatitude", destLoc_obj);
                    final String tEndLong = generalFunc.getJsonValueStr("tDestLongitude", destLoc_obj);
                    final String tDaddress = generalFunc.getJsonValueStr("tDaddress", destLoc_obj);

                    recentAddrTxtView.setText(tDaddress);

                    HashMap<String, String> map = new HashMap<>();
                    map.put("tLat", tEndLat);
                    map.put("tLong", tEndLong);
                    map.put("taddress", tDaddress);

                    if (getRandomColor() != null) {
                        HashMap<String, String> colorMap = getRandomColor();
                        map.put("BG_COLOR", colorMap.get("BG_COLOR"));
                        map.put("TEXT_COLOR", colorMap.get("TEXT_COLOR"));
                        imageabackArea.getBackground().setColorFilter(Color.parseColor(colorMap.get("BG_COLOR")), PorterDuff.Mode.SRC_ATOP);
                        roundImage.setColorFilter(Color.parseColor(colorMap.get("TEXT_COLOR")), PorterDuff.Mode.SRC_IN);
                    }

                    recentLocList.add(map);
                    recentAdapterView.setOnClickListener(view1 -> {
                        if (whichView != null) {
                            if (whichView.equals("dest")) {

                                Bundle bn = new Bundle();
                                bn.putString("Address", tDaddress);
                                bn.putString("Latitude", "" + tEndLat);
                                bn.putString("Longitude", "" + tEndLong);
                                bn.putBoolean("isSkip", false);
                                new ActUtils(getActContext()).setOkResult(bn);

                                finish();
                            }

                        } else {

                        }
                    });
                    destLocationView.addView(view);
                    destLocationView.setVisibility(View.VISIBLE);
                }
            } else {
                if (sourceLocationView != null) {
                    sourceLocationView.removeAllViews();
                    recentLocList.clear();
                }
                for (int i = 0; i < SourceLocations_arr.length(); i++) {

                    final View view = mInflater.inflate(R.layout.item_recent_loc_design, null);
                    JSONObject loc_obj = generalFunc.getJsonObject(SourceLocations_arr, i);

                    MTextView recentAddrTxtView = (MTextView) view.findViewById(R.id.recentAddrTxtView);
                    LinearLayout recentAdapterView = (LinearLayout) view.findViewById(R.id.recentAdapterView);
                    LinearLayout imageabackArea = (LinearLayout) view.findViewById(R.id.imageabackArea);
                    ImageView roundImage = (ImageView) view.findViewById(R.id.roundImage);
                    ImageView arrowImg = (ImageView) view.findViewById(R.id.arrowImg);
                    if (generalFunc.isRTLmode()) {
                        arrowImg.setRotation(180);
                    }

                    final String tStartLat = generalFunc.getJsonValueStr("tStartLat", loc_obj);
                    final String tStartLong = generalFunc.getJsonValueStr("tStartLong", loc_obj);
                    final String tSaddress = generalFunc.getJsonValueStr("tSaddress", loc_obj);

                    recentAddrTxtView.setText(tSaddress);
                    HashMap<String, String> map = new HashMap<>();
                    map.put("tLat", tStartLat);
                    map.put("tLong", tStartLong);
                    map.put("taddress", tSaddress);

                    if (getRandomColor() != null) {
                        HashMap<String, String> colorMap = getRandomColor();
                        map.put("BG_COLOR", colorMap.get("BG_COLOR"));
                        map.put("TEXT_COLOR", colorMap.get("TEXT_COLOR"));
                        imageabackArea.getBackground().setColorFilter(Color.parseColor(colorMap.get("BG_COLOR")), PorterDuff.Mode.SRC_ATOP);
                        roundImage.setColorFilter(Color.parseColor(colorMap.get("TEXT_COLOR")), PorterDuff.Mode.SRC_IN);
                    }

                    recentLocList.add(map);
                    recentAdapterView.setOnClickListener(view12 -> {
                        if (whichView != null) {
                            if (whichView.equals("source")) {

                                Bundle bn = new Bundle();
                                bn.putString("Address", tSaddress);
                                bn.putString("Latitude", "" + tStartLat);
                                bn.putString("Longitude", "" + tStartLong);

                                new ActUtils(getActContext()).setOkResult(bn);

                                finish();

                            }


                        } else {

                        }
                    });
                    sourceLocationView.addView(view);
                    sourceLocationView.setVisibility(View.VISIBLE);
                }
            }

        } else {
            destLocationView.setVisibility(View.GONE);
            sourceLocationView.setVisibility(View.GONE);
            recentLocHTxtView.setVisibility(View.GONE);
        }
    }


    public void initializeSessionRegeneration() {

        if (sessionTokenFreqTask != null) {
            sessionTokenFreqTask.stopRepeatingTask();
        }
        sessionTokenFreqTask = new RecurringTask(170000);
        sessionTokenFreqTask.setTaskRunListener(instance -> session_token = Utils.userType + "_" + generalFunc.getMemberId() + "_" + System.currentTimeMillis());

        sessionTokenFreqTask.startRepeatingTask();
    }


    @Override
    public void itemRecentLocClick(int position) {

        HashMap<String, String> hashMap = new HashMap<>();
        hashMap.put("Place_id", placelist.get(position).get("Place_id"));
        hashMap.put("description", placelist.get(position).get("description"));
        hashMap.put("session_token", placelist.get(position).get("session_token"));


        String latitude = "";
        String longitude = "";
        if (getIntent().getDoubleExtra("long", 0.0) != 0.0) {
            latitude = getIntent().getDoubleExtra("lat", 0.0) + "";
            longitude = getIntent().getDoubleExtra("long", 0.0) + "";
        }

        if (placelist.get(position).get("Place_id") == null || placelist.get(position).get("Place_id").equals("")) {


            HashMap<String, Object> data_dict = new HashMap<>();
            data_dict.put("ADDRESS", placelist.get(position).get("description"));
            data_dict.put("LATITUDE", GeneralFunctions.parseDoubleValue(0.0, placelist.get(position).get("latitude")));
            data_dict.put("LONGITUDE", GeneralFunctions.parseDoubleValue(0.0, placelist.get(position).get("longitude")));
            data_dict.put("RESPONSE_TYPE", AppService.Service.PLACE_DETAILS);
            handlePlaceDeailsRespose(data_dict);

        } else {
            AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(latitude, longitude).setPlaceId(placelist.get(position).get("Place_id")).setServiceId(placelist.get(position).get("vServiceId")).setData_Str(placelist.get(position).get("description")).setToken(session_token).build(), AppService.Service.PLACE_DETAILS, data -> handlePlaceDeailsRespose(data));

        }

    }

    private void handlePlaceDeailsRespose(HashMap<String, Object> data) {
        Bundle bn = new Bundle();
        bn.putString("Address", data.get("ADDRESS").toString());
        bn.putString("Latitude", data.get("LATITUDE").toString());
        bn.putString("Longitude", data.get("LONGITUDE").toString());

        Utils.hideKeyboard(getActContext());

        new ActUtils(getActContext()).setOkResult(bn);


        finish();

    }

    public void setWhichLocationAreaSelected(String locationArea) {
        this.whichLocation = locationArea;

        if (locationArea.equals("dest")) {
            destLocationView.setVisibility(View.VISIBLE);
            sourceLocationView.setVisibility(View.GONE);
            getRecentLocations("dest");
            searchTxt.requestFocus();
            checkPlaces(locationArea);

        } else if (locationArea.equals("source")) {
            destLocationView.setVisibility(View.GONE);
            sourceLocationView.setVisibility(View.VISIBLE);
            getRecentLocations("source");
            checkPlaces(locationArea);
        }

    }

    public Context getActContext() {
        return SearchLocationActivity.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.ADD_HOME_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {
            HashMap<String, String> storeData = new HashMap<>();
            storeData.put("userHomeLocationLatitude", "" + data.getStringExtra("Latitude"));
            storeData.put("userHomeLocationLongitude", "" + data.getStringExtra("Longitude"));
            storeData.put("userHomeLocationAddress", "" + data.getStringExtra("Address"));
            generalFunc.storeData(storeData);

            homePlaceTxt.setText(data.getStringExtra("Address"));
            checkPlaces(whichLocation);


            Bundle bn = new Bundle();
            bn.putString("Latitude", data.getStringExtra("Latitude"));
            bn.putString("Longitude", "" + data.getStringExtra("Longitude"));
            bn.putString("Address", "" + data.getStringExtra("Address"));
            new ActUtils(getActContext()).setOkResult(bn);
            finish();

        } else if (requestCode == Utils.ADD_MAP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {

            Bundle bn = new Bundle();
            bn.putString("Latitude", data.getStringExtra("Latitude"));
            bn.putString("Longitude", "" + data.getStringExtra("Longitude"));
            bn.putString("Address", "" + data.getStringExtra("Address"));


            new ActUtils(getActContext()).setOkResult(bn);
            finish();

        }
    }

    public void getGooglePlaces(String input) {

        String session_token = this.session_token;
        String latitude = "";
        String longitude = "";


        HashMap<String, String> hashMap = new HashMap<>();
        hashMap.put("input", input);

        if (getIntent().getDoubleExtra("long", 0.0) != 0.0) {

            latitude = getIntent().getDoubleExtra("lat", 0.0) + "";
            longitude = getIntent().getDoubleExtra("long", 0.0) + "";
        }
        hashMap.put("session_token", session_token);


        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(latitude, longitude).setData_Str(input).setToken(session_token).build(), AppService.Service.PLACE_SUGGESTIONS, data -> {


            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {

                placelist.clear();
                if (placesAdapter != null) {
                    placesAdapter.notifyDataSetChanged();
                }

                String msg = generalFunc.retrieveLangLBl("We didn't find any places matched to your entered place. Please try again with another text.", "LBL_NO_PLACES_FOUND");
                noPlacedata.setText(msg);
                placesRecyclerView.setVisibility(View.VISIBLE);

                noPlacedata.setVisibility(View.VISIBLE);

                return;
            }


            placelist.clear();
            placelist.addAll((Collection<? extends HashMap<String, String>>) data.get("PLACE_SUGGESTION_DATA"));
            imageCancel.setVisibility(View.VISIBLE);


            if (currentSearchQuery.length() == 0) {
                placesRecyclerView.setVisibility(View.GONE);
                noPlacedata.setVisibility(View.GONE);

                return;
            }


            if (placelist.size() > 0) {
                noPlacedata.setVisibility(View.GONE);
                placesRecyclerView.setVisibility(View.VISIBLE);

                JSONObject jsonObject = new JSONObject(data);

                String RESPONSE_DATA = generalFunc.getJsonValueStr("RESPONSE_DATA", jsonObject);
                googleimagearea.setVisibility(View.GONE);
                if (Utils.checkText(RESPONSE_DATA)) {
                    JSONObject RESPONSE_DATA_OBJ = generalFunc.getJsonObject(RESPONSE_DATA);
                    String vServiceName = generalFunc.getJsonValueStr("vServiceName", RESPONSE_DATA_OBJ);
                    if (!RESPONSE_DATA_OBJ.has("vServiceName") || (Utils.checkText(vServiceName) && vServiceName.equalsIgnoreCase("Google"))) {
                        googleimagearea.setVisibility(View.VISIBLE);
                    }
                }
                if (placesAdapter == null) {
                    placesAdapter = new PlacesAdapter(getActContext(), placelist);
                    placesRecyclerView.setAdapter(placesAdapter);
                    placesAdapter.itemRecentLocClick(SearchLocationActivity.this);

                } else {
                    placesAdapter.notifyDataSetChanged();
                }
            } else if (currentSearchQuery.length() == 0) {
                placelist.clear();
                if (placesAdapter != null) {
                    placesAdapter.notifyDataSetChanged();
                }

                String msg = generalFunc.retrieveLangLBl("We didn't find any places matched to your entered place. Please try again with another text.", "LBL_NO_PLACES_FOUND");
                noPlacedata.setText(msg);
                placesRecyclerView.setVisibility(View.VISIBLE);

                noPlacedata.setVisibility(View.VISIBLE);

                return;
            } else {

                placelist.clear();
                if (placesAdapter != null) {
                    placesAdapter.notifyDataSetChanged();
                }
                String msg = "";
                if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                    msg = generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT");

                } else {
                    msg = generalFunc.retrieveLangLBl("Error occurred while searching nearest places. Please try again later.", "LBL_PLACE_SEARCH_ERROR");

                }

                noPlacedata.setText(msg);
                placesRecyclerView.setVisibility(View.VISIBLE);
                noPlacedata.setVisibility(View.VISIBLE);
            }

        });


        noPlacedata.setVisibility(View.GONE);

    }


    public void onClick(View view) {
        int i = view.getId();
        Bundle bndl = new Bundle();

        if (i == R.id.cancelTxt) {
            finish();

        } else if (i == R.id.imageCancel) {
            placesRecyclerView.setVisibility(View.GONE);
            searchTxt.setText("");
            noPlacedata.setVisibility(View.GONE);
        } else if (i == R.id.homeLocArea) {

//                if (mpref_place != null) {

            final String home_address_str = generalFunc.retrieveValue("userHomeLocationAddress");
            final String home_addr_latitude = generalFunc.retrieveValue("userHomeLocationLatitude");
            final String home_addr_longitude = generalFunc.retrieveValue("userHomeLocationLongitude");

            if (home_address_str != null && !home_address_str.equalsIgnoreCase("")) {

                LatLng placeLocation = new LatLng(generalFunc.parseDoubleValue(0.0, home_addr_latitude), generalFunc.parseDoubleValue(0.0, home_addr_longitude));
                Bundle bn = new Bundle();
                bn.putString("Address", home_address_str);
                bn.putString("Latitude", "" + placeLocation.latitude);
                bn.putString("Longitude", "" + placeLocation.longitude);
                bn.putBoolean("isSkip", false);
                new ActUtils(getActContext()).setOkResult(bn);
                finish();
            } else {
                bndl.putString("isHome", "true");
                new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                        bndl, Utils.ADD_HOME_LOC_REQ_CODE);
            }


        } else if (i == R.id.homeActionImgView) {
            if (intCheck.isNetworkConnected()) {
                Bundle bn = new Bundle();
                bn.putString("isHome", "true");
                new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                        bn, Utils.ADD_HOME_LOC_REQ_CODE);
            } else {
                generalFunc.showMessage(mapLocArea, generalFunc.retrieveLangLBl("", "LBL_NO_INTERNET_TXT"));
            }
        } else if (i == R.id.mapLocArea) {
            bndl.putString("locationArea", getIntent().getStringExtra("locationArea"));
            String from = !whichLocation.equals("dest") ? "isPickUpLoc" : "isDestLoc";
            String lati = !whichLocation.equals("dest") ? "PickUpLatitude" : "DestLatitude";
            String longi = !whichLocation.equals("dest") ? "PickUpLongitude" : "DestLongitude";
            String address = !whichLocation.equals("dest") ? "PickUpAddress" : "DestAddress";


            bndl.putString(from, "true");
            if (getIntent().getDoubleExtra("lat", 0.0) != 0.0 && getIntent().getDoubleExtra("long", 0.0) != 0.0) {
                bndl.putString(lati, "" + getIntent().getDoubleExtra("lat", 0.0));
                bndl.putString(longi, "" + getIntent().getDoubleExtra("long", 0.0));
                if (getIntent().hasExtra("address") && Utils.checkText(getIntent().getStringExtra("address"))) {
                    bndl.putString(address, "" + getIntent().getStringExtra("address"));
                } else {
                    bndl.putString(address, "");
                }

            }

            bndl.putString("IS_FROM_SELECT_LOC", "Yes");

            new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                    bndl, Utils.ADD_MAP_LOC_REQ_CODE);


        }

    }


    @Override
    protected void onDestroy() {
        if (sessionTokenFreqTask != null) {
            sessionTokenFreqTask.stopRepeatingTask();
        }
        super.onDestroy();
    }

    public HashMap<String, String> getRandomColor() {
        if (colorHasmap.size() > 0) {
            int randomIndex = new SecureRandom().nextInt(colorHasmap.size());

            return colorHasmap.get(randomIndex);
        }
        return null;
    }
}
