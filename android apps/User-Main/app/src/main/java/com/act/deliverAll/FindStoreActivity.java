package com.act.deliverAll;


import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.os.Bundle;
import android.os.Handler;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.PlacesAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.InternetConnection;
import com.general.files.RecurringTask;
import com.buddyverse.main.R;
import com.act.SearchPickupLocationActivity;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.Utils;
import com.view.MTextView;
import com.view.anim.loader.AVLoadingIndicatorView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.security.SecureRandom;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class FindStoreActivity extends ParentActivity implements PlacesAdapter.setRecentLocClickList {
    MTextView titleTxt;
    MTextView topSearchHTXt;
    MTextView recentSearchHTXt;
    ImageView backImgView;
    ImageView googleimagearea;
    ImageView imageCancel;
    GeneralFunctions generalFunc;
    String userProfileJson = "";

    LinearLayout mapLocArea;
    LinearLayout sourceLocationView;
    LinearLayout topSearchArea;
    LinearLayout recentSearchArea;
    MaterialEditText searchTxt;
    ArrayList<HashMap<String, String>> recentLocList = new ArrayList<>();
    JSONArray SourceLocations_arr;
    ArrayList<HashMap<String, String>> colorHasmap = new ArrayList<HashMap<String, String>>();
    String whichLocation = "Source";

    String session_token = "";
    String currentSearchQuery = "";
    Handler handler = null;
    private AVLoadingIndicatorView loaderView;
    int MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE = 2;

    ArrayList<HashMap<String, String>> placelist;
    PlacesAdapter placesAdapter;
    RecurringTask sessionTokenFreqTask = null;
    RecyclerView placesRecyclerView;
    InternetConnection intCheck;
    private final SecureRandom random = new SecureRandom();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_find_store);
        setUi();
        placelist = new ArrayList<>();
        MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE = GeneralFunctions.parseIntegerValue(2, generalFunc.getJsonValue("MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE", userProfileJson));

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


                if (handler == null) {
                    handler = new Handler();
                } else {

                    handler.removeCallbacksAndMessages(null);
                    handler = new Handler();
                }
                loaderView.setVisibility(View.VISIBLE);
                imageCancel.setVisibility(View.GONE);


                handler.postDelayed(() -> {

                    currentSearchQuery = Objects.requireNonNull(searchTxt.getText()).toString();

                    if (s.length() >= MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE) {
                        if (session_token.trim().equalsIgnoreCase("")) {
                            session_token = Utils.userType + "_" + generalFunc.getMemberId() + "_" + System.currentTimeMillis();
                            initializeSessionRegeneration();
                        }

                        topSearchArea.setVisibility(View.VISIBLE);
                        recentSearchArea.setVisibility(View.GONE);
                        getGooglePlaces(currentSearchQuery, -1);
                    } else {
                        loaderView.setVisibility(View.GONE);
                        imageCancel.setVisibility(View.VISIBLE);
                        googleimagearea.setVisibility(View.GONE);
                        topSearchArea.setVisibility(View.VISIBLE);
//                            noPlacedata.setVisibility(View.GONE);
                        recentSearchArea.setVisibility(View.GONE);

                    }
                }, 750);
            }
        });

        searchTxt.setOnEditorActionListener((v, actionId, event) -> {
            if (actionId == EditorInfo.IME_ACTION_SEARCH) {
                getSearchGooglePlace(v.getText().toString(), -1);

                return true;
            }
            return false;
        });
    }

    public void getGooglePlaces(String input, int selectedPos) {

        HashMap<String, String> hashMap = new HashMap<>();
        hashMap.put("selectedPos", selectedPos + "");
        String latitude = "";
        String longitude = "";

        if (getIntent().getStringExtra("latitude") != null && !getIntent().getStringExtra("latitude").equals("")) {
            latitude = getIntent().getStringExtra("latitude");
            longitude = getIntent().getStringExtra("longitude");
        }


        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(latitude, longitude).setData_Str(input).setToken(session_token).build(), AppService.Service.PLACE_SUGGESTIONS, new AppService.ServiceDelegate() {
            @Override
            public void onResult(HashMap<String, Object> data) {


            }
        });

    }

    public void initializeSessionRegeneration() {

        if (sessionTokenFreqTask != null) {
            sessionTokenFreqTask.stopRepeatingTask();
        }
        sessionTokenFreqTask = new RecurringTask(170000);
        sessionTokenFreqTask.setTaskRunListener(instance -> session_token = Utils.userType + "_" + generalFunc.getMemberId() + "_" + System.currentTimeMillis());

        sessionTokenFreqTask.startRepeatingTask();
    }


    public void getSearchGooglePlace(String input, int position) {


        String latitude = "";
        String longitude = "";

        if (getIntent().getStringExtra("latitude") != null && !getIntent().getStringExtra("latitude").equals("")) {
            latitude = getIntent().getStringExtra("latitude");
            longitude = getIntent().getStringExtra("longitude");
        }


        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(latitude, longitude).setData_Str(input).setToken(session_token).build(), AppService.Service.PLACE_SUGGESTIONS, new AppService.ServiceDelegate() {
            @Override
            public void onResult(HashMap<String, Object> data) {


            }
        });

    }

    private void setUi() {
        placesRecyclerView = (RecyclerView) findViewById(R.id.placesRecyclerView);
        loaderView = (AVLoadingIndicatorView) findViewById(R.id.loaderView);
        titleTxt = findViewById(R.id.titleTxt);
        topSearchHTXt = findViewById(R.id.topSearchHTXt);
        recentSearchHTXt = findViewById(R.id.recentSearchHTXt);
        searchTxt = findViewById(R.id.searchTxt);
        topSearchArea = findViewById(R.id.topSearchArea);
        recentSearchArea = findViewById(R.id.recentSearchArea);
        sourceLocationView = findViewById(R.id.sourceLocationView);
        backImgView = findViewById(R.id.backImgView);
        googleimagearea = findViewById(R.id.googleimagearea);
        imageCancel = findViewById(R.id.imageCancel);

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        backImgView.setVisibility(View.VISIBLE);
        imageCancel.setVisibility(View.GONE);
        titleTxt.setText("Selcet A Store");
        searchTxt.setHideUnderline(true);
        mapLocArea = (LinearLayout) findViewById(R.id.mapLocArea);
        addToClickHandler(mapLocArea);

        if (generalFunc.getJsonArray("RANDOM_COLORS_KEY_VAL_ARR", userProfileJson) != null) {
            JSONArray jsonArray = generalFunc.getJsonArray("RANDOM_COLORS_KEY_VAL_ARR", userProfileJson);
            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject jsonObject = generalFunc.getJsonObject(jsonArray, i);
                HashMap<String, String> colorMap = new HashMap<>();
                colorMap.put("BG_COLOR", generalFunc.getJsonValueStr("BG_COLOR", jsonObject));
                colorMap.put("TEXT_COLOR", generalFunc.getJsonValueStr("TEXT_COLOR", jsonObject));
                colorHasmap.add(colorMap);
            }
        }

        setLocations();
    }

    private void setLocations() {
        final LayoutInflater mInflater = (LayoutInflater)
                getActContext().getSystemService(Activity.LAYOUT_INFLATER_SERVICE);

        SourceLocations_arr = generalFunc.getJsonArray("SourceLocations", userProfileJson);

        if (sourceLocationView != null) {
            sourceLocationView.removeAllViews();
            recentLocList.clear();
        }
        for (int i = 0; i < SourceLocations_arr.length(); i++) {

            final View view = mInflater.inflate(R.layout.item_recent_loc_design, null);
            JSONObject loc_obj = generalFunc.getJsonObject(SourceLocations_arr, i);

            MTextView recentAddrTxtView = (MTextView) view.findViewById(R.id.recentAddrTxtView);
            LinearLayout recentAdapterView = (LinearLayout) view.findViewById(R.id.recentAdapterView);

            ImageView arrowImage = (ImageView) view.findViewById(R.id.recarrowImage);
            ImageView roundImage = (ImageView) view.findViewById(R.id.roundImage);
            LinearLayout imageabackArea = (LinearLayout) view.findViewById(R.id.imageabackArea);


            if (generalFunc.isRTLmode()) {
                arrowImage.setRotationY(180);
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

                double lati = generalFunc.parseDoubleValue(0.0, tStartLat);
                double longi = generalFunc.parseDoubleValue(0.0, tStartLong);
//                        resetOrAddDest(selectedPos, tSaddress, lati, longi, "");

            });
            sourceLocationView.addView(view);
        }
    }


    public HashMap<String, String> getRandomColor() {
        if (colorHasmap.size() > 0) {
            int randomIndex = random.nextInt(colorHasmap.size());

            return colorHasmap.get(randomIndex);
        }
        return null;
    }

    public Context getActContext() {
        return FindStoreActivity.this;
    }


    @Override
    public void itemRecentLocClick(int position) {

    }


    public void onClick(View view) {
        Bundle bndl = new Bundle();

        if (view.getId() == backImgView.getId()) {
            onBackPressed();
        } else if (view.getId() == R.id.mapLocArea) {
            bndl.putString("locationArea", getIntent().getStringExtra("locationArea"));
            String from = !whichLocation.equals("dest") ? "isPickUpLoc" : "isDestLoc";
            String lati = !whichLocation.equals("dest") ? "PickUpLatitude" : "DestLatitude";
            String longi = !whichLocation.equals("dest") ? "PickUpLongitude" : "DestLongitude";
            String address = !whichLocation.equals("dest") ? "PickUpAddress" : "DestAddress";


            bndl.putString(from, "true");
//                if (getIntent().getDoubleExtra("lat", 0.0) != 0.0 && getIntent().getDoubleExtra("long", 0.0) != 0.0) {
            bndl.putString(lati, "" + getIntent().getDoubleExtra("lat", 0.0));
            bndl.putString(longi, "" + getIntent().getDoubleExtra("long", 0.0));
            if (getIntent().hasExtra("address") && Utils.checkText(getIntent().getStringExtra("address"))) {
                bndl.putString(address, "" + getIntent().getStringExtra("address"));
            } else {
                bndl.putString(address, "");
            }

//                } else if (getIntent().getDoubleExtra("lat", 0.0) != 0.0 && getIntent().getDoubleExtra("long", 0.0) != 0.0) {
//                    bndl.putString(lati, "" + getIntent().getDoubleExtra("lat", 0.0));
//                    bndl.putString(longi, "" + getIntent().getDoubleExtra("long", 0.0));
//                    if (getIntent().hasExtra("address") && Utils.checkText(getIntent().getStringExtra("address"))) {
//                        bndl.putString(address, "" + getIntent().getStringExtra("address"));
//                    } else {
//                        bndl.putString(address, "");
//                    }
//
//                }


            bndl.putString("IS_FROM_SELECT_LOC", "Yes");

            if (getIntent().hasExtra("isFromMulti")) {
                bndl.putBoolean("isFromMulti", true);
                bndl.putInt("pos", getIntent().getIntExtra("pos", -1));
            }


            if (getIntent().hasExtra("eSystem")) {
                bndl.putString("eSystem", Utils.eSystem_Type);
            }


            new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                    bndl, Utils.ADD_MAP_LOC_REQ_CODE);


        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.ADD_MAP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {

            String Latitude = data.getStringExtra("Latitude");
            String Longitude = data.getStringExtra("Longitude");
            String Address = data.getStringExtra("Address");

            double lati = generalFunc.parseDoubleValue(0.0, Latitude);
            double longi = generalFunc.parseDoubleValue(0.0, Longitude);

            Bundle bn = new Bundle();
            bn.putString("Address", Address);
            bn.putString("Latitude", "" + Latitude);
            bn.putString("Longitude", "" + Longitude);

            new ActUtils(getActContext()).setOkResult(bn);
            backImgView.performClick();

        }
    }
}