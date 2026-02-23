package com.act;

import static com.utils.MapUtils.createDrawableFromView;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;
import androidx.core.content.res.ResourcesCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.DefaultItemAnimator;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.MultiDestinationItemAdapter;
import com.general.files.ActUtils;
import com.general.files.DataParser;
import com.general.files.GeneralFunctions;
import com.general.files.MapComparator;
import com.general.files.MyApp;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.Polyline;
import com.map.helper.SphericalUtil;
import com.map.minterface.OnCameraIdleListener;
import com.map.minterface.OnCameraMoveCanceledListener;
import com.map.minterface.OnCameraMoveListener;
import com.map.minterface.OnCameraMoveStartedListener;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.MarkerOptions;
import com.map.models.PolylineOptions;
import com.model.Delivery_Data;
import com.model.Multi_Delivery_Data;
import com.model.Multi_Dest_Info_Detail_Data;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CircularImageView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.security.SecureRandom;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;

/**
 * Created by Esite on 03-04-2018.
 */

public class MultiDeliverySecondPhaseActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback, OnCameraMoveCanceledListener, OnCameraMoveStartedListener, OnCameraIdleListener, MultiDestinationItemAdapter.OnItemClickListener, OnCameraMoveListener {

    public int height = 0;
    MTextView titleTxt, pin_text;
    ImageView backImgView, pin_image;
    private SecureRandom r = new SecureRandom();
    View marker_view;
    ImageView iv_arrow;
    LinearLayout headerArea;
    FrameLayout bottomArea;
    // Area which going to be hide on map touch
    LinearLayout contentArea;
    LinearLayout innerLayout;
    LinearLayout subLayout;
    RecyclerView destRecyclerView;
    MultiDestinationItemAdapter destAdapter;
    NestedScrollView sv_main;
    boolean arrowUp = true;
    ArrayList<Marker> markerArrayList = new ArrayList<>();  // list of markers on map
    HashMap<String, String> mapData = new HashMap<>();
    ArrayList<Multi_Delivery_Data> listofViews = new ArrayList<>();      // list of main views
    ArrayList<Multi_Delivery_Data> templistofViews = new ArrayList<>();  // temp list of views
    ArrayList<Multi_Delivery_Data> wayPointslist = new ArrayList<>();   // List of Way Points/ middle points
    ArrayList<Multi_Delivery_Data> destPointlist = new ArrayList<>();   // destination Points
    ArrayList<Multi_Delivery_Data> finalPointlist = new ArrayList<>();  // final Points list with time & distance & based on shortest location first algorithm
    ArrayList<Multi_Dest_Info_Detail_Data> dest_details_Array = new ArrayList<Multi_Dest_Info_Detail_Data>();  // store all locations time & distance
    MButton nextbtn;
    String LBL_MULTI_FR_TXT, LBL_MULTI_TO_TXT, LBL_MULTI_ADD_NEW_DESTINATION;
    String serverKey;
    int DRIVER_ARRIVED_MIN_TIME_PER_MINUTE;
    LatLngBounds.Builder builder = new LatLngBounds.Builder();

    public int maxDestAddAllowedCount = 0;
    boolean isManualCalculation = false;
    ArrayList<Multi_Delivery_Data> destinationsArraylist = new ArrayList<>(); // list contains distance with manual filtered distance data
    ArrayList<Multi_Delivery_Data> filteredDestinationsArraylist = new ArrayList<>(); // temp list used for manual distance calculation
    int count = 0;
    private Animation animShow, animHide;
    private GeoMapLoader.GeoMap geoMap;
    private Polyline route_polyLine;
    private String time = "";
    private String distance = "";
    public String ALLOW_MULTIPLE_DEST_ADD_KEY = "";
    public boolean allowDestAdd = false;
    private CircularImageView iv_current_loc;

    boolean isMulti = true;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_multi_second_phase);

        ALLOW_MULTIPLE_DEST_ADD_KEY = generalFunc.prefHasKey(Utils.ALLOW_MULTIPLE_DEST_ADD_KEY) ? generalFunc.retrieveValue(Utils.ALLOW_MULTIPLE_DEST_ADD_KEY) : "";
        mapData = (HashMap<String, String>) getIntent().getSerializableExtra("selectedData");
        isMulti = getIntent().getBooleanExtra("fromMulti", true);
        setSelected("2");
        initView();
        nextbtn.setClickable(false);
        nextbtn.setFocusable(false);
        setlable();
    }

    private void setlable() {
        nextbtn.setText(generalFunc.retrieveLangLBl("Next", "LBL_BTN_NEXT_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("New Booking", "LBL_MULTI_NEW_BOOKING_TXT"));
        LBL_MULTI_FR_TXT = generalFunc.retrieveLangLBl("FR", "LBL_MULTI_FR_TXT");
        LBL_MULTI_TO_TXT = generalFunc.retrieveLangLBl("TO", "LBL_MULTI_TO_TXT");
        LBL_MULTI_ADD_NEW_DESTINATION = generalFunc.retrieveLangLBl("", "LBL_MULTI_ADD_NEW_DESTINATION");
        DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = generalFunc.parseIntegerValue(3, generalFunc.getJsonValue("DRIVER_ARRIVED_MIN_TIME_PER_MINUTE", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        serverKey = generalFunc.retrieveValue(Utils.GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY);
        /*Single Delivery UI as Multi Delivery - SdUiAsMd*/
        String maxDestination = Utils.checkText(mapData.get("maxDestination")) ? mapData.get("maxDestination") : generalFunc.retrieveValue(Utils.MAX_ALLOW_NUM_DESTINATION_MULTI_KEY);
        maxDestAddAllowedCount = generalFunc.parseIntegerValue(0, maxDestination);

    }

    public void addEmptyDestination(boolean addEmptyDest) {
        if (addEmptyDest) {
            Multi_Delivery_Data mt = new Multi_Delivery_Data();
            mt.setHintLable(LBL_MULTI_ADD_NEW_DESTINATION);
            mt.setFRLable(LBL_MULTI_FR_TXT);
            mt.setTOLable(LBL_MULTI_TO_TXT);
            listofViews.add(mt);
        }
        showAddDestinationArea();
        setScrollviewHeight();
    }

    private void setDestinationView(boolean addEmptyDest) {
        addEmptyDestination(addEmptyDest);

        destAdapter = new MultiDestinationItemAdapter(getActContext(), listofViews, generalFunc, false);
        destRecyclerView.setAdapter(destAdapter);

        LinearLayoutManager llm = new LinearLayoutManager(this);
        llm.setOrientation(LinearLayoutManager.VERTICAL);
        destRecyclerView.setItemAnimator(new DefaultItemAnimator());

        destRecyclerView.setLayoutManager(llm);
        destRecyclerView.setNestedScrollingEnabled(false);
        destAdapter.setOnItemClickListener(this);
        destAdapter.notifyDataSetChanged();

        destRecyclerView.smoothScrollToPosition(destRecyclerView.getAdapter().getItemCount());

        try {
            sv_main.post(() -> sv_main.fullScroll(NestedScrollView.FOCUS_DOWN));
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    private void setScrollviewHeight() {
        int margin = (Utils.dpToPx(getActContext(), 0));
//        int recyclerViewitemHeight = (Utils.dpToPx(getActContext(), 50));
        int recyclerViewitemHeight = (int) getActContext().getResources().getDimension(R.dimen._47sdp);

        Display mDisplay = this.getWindowManager().getDefaultDisplay();
        final int width = mDisplay.getWidth();
        final int height = mDisplay.getHeight();

        // set innerLayout to Default

        FrameLayout.LayoutParams layoutParams2 = (FrameLayout.LayoutParams) innerLayout.getLayoutParams();
        layoutParams2.width = ViewGroup.LayoutParams.MATCH_PARENT;
        layoutParams2.height = ViewGroup.LayoutParams.WRAP_CONTENT;
        innerLayout.setLayoutParams(layoutParams2);
        innerLayout.requestLayout();

        // set Recyclerview height according child
        ViewGroup.LayoutParams paramsRV = destRecyclerView.getLayoutParams();
        paramsRV.width = ViewGroup.LayoutParams.MATCH_PARENT;
        paramsRV.height = (recyclerViewitemHeight * listofViews.size());
        destRecyclerView.setLayoutParams(paramsRV);
        destRecyclerView.requestLayout();


        LinearLayout.LayoutParams layoutParams1 = (LinearLayout.LayoutParams) subLayout.getLayoutParams();
        layoutParams1.width = ViewGroup.LayoutParams.MATCH_PARENT;
        layoutParams1.height = ViewGroup.LayoutParams.MATCH_PARENT;
        subLayout.setLayoutParams(layoutParams1);
        subLayout.requestLayout();

        // set SubLayout to default
        LinearLayout.LayoutParams layoutParams = (LinearLayout.LayoutParams) subLayout
                .getLayoutParams();

        layoutParams.setMargins(0, 0, 0, 0);
        subLayout.setLayoutParams(layoutParams);
        subLayout.requestLayout();


        if (!arrowUp) {
            iv_current_loc.setVisibility(View.GONE);
            // set margin to SubLayout due to innerLayout's match parent changes

            LinearLayout.LayoutParams sl = (LinearLayout.LayoutParams) subLayout.getLayoutParams();
            sl.width = ViewGroup.LayoutParams.MATCH_PARENT;
            sl.height = ViewGroup.LayoutParams.MATCH_PARENT;
            sl.setMargins(0, margin, 0, 0);
            subLayout.setLayoutParams(sl);
            subLayout.requestLayout();


            // set innerLayout to match parent

            FrameLayout.LayoutParams lp1 = (FrameLayout.LayoutParams) innerLayout.getLayoutParams();
            lp1.width = ViewGroup.LayoutParams.MATCH_PARENT;
            lp1.height = ViewGroup.LayoutParams.MATCH_PARENT;
            innerLayout.setLayoutParams(lp1);
            innerLayout.requestLayout();

            if (listofViews.size() < 3) {
                iv_arrow.performClick();
            }

            reSetGoogleMap(false);
            return;
        }

        iv_current_loc.setVisibility(View.VISIBLE);

        if (listofViews.size() > 3) {
            iv_arrow.setVisibility(View.VISIBLE);
            ViewGroup.LayoutParams params123 = subLayout.getLayoutParams();
            params123.width = ViewGroup.LayoutParams.MATCH_PARENT;
            params123.height = (int) (height * 0.45);
            subLayout.setLayoutParams(params123);
            subLayout.requestLayout();
            reSetGoogleMap(false);
            return;
        } else {
            iv_arrow.setVisibility(View.GONE);
            destRecyclerView.scrollToPosition(0);
        }
        reSetGoogleMap(false);
    }

    private void reSetGoogleMap(boolean b) {
        if (b) {
            geoMap.setPadding(0, 0, 0, 0);
        } else {
            geoMap.setPadding(0, (int) getResources().getDimension(R.dimen._133sdp), 0, subLayout.getMeasuredHeight());
        }
    }

    private void initView() {
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        sv_main = (NestedScrollView) findViewById(R.id.sv_main);
        contentArea = (LinearLayout) findViewById(R.id.contentArea);
        innerLayout = (LinearLayout) findViewById(R.id.innerLayout);
        subLayout = (LinearLayout) findViewById(R.id.subLayout);
        headerArea = (LinearLayout) findViewById(R.id.headerArea);
        bottomArea = (FrameLayout) findViewById(R.id.bottomArea);
        iv_arrow = (ImageView) findViewById(R.id.iv_arrow);
        iv_current_loc = (CircularImageView) findViewById(R.id.iv_current_loc);
        addToClickHandler(iv_current_loc);
        destRecyclerView = (RecyclerView) findViewById(R.id.destRecyclerView);
        nextbtn = ((MaterialRippleLayout) findViewById(R.id.nextbtn)).getChildView();
        marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                .inflate(R.layout.custom_marker_parcel_delivery, null);

        pin_image = marker_view.findViewById(R.id.pin_image);
        pin_text = marker_view.findViewById(R.id.pintext);

        nextbtn.setId(Utils.generateViewId());
        addToClickHandler(nextbtn);
        addToClickHandler(backImgView);
        addToClickHandler(iv_arrow);

        /*Map Data*/

        (new GeoMapLoader(this, R.id.mapFragmentContainer)).bindMap(this);
    }

    private void setSelected(String selected) {

        if (selected.equalsIgnoreCase("2")) {

            ((MTextView) findViewById(R.id.tv1)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            ((MTextView) findViewById(R.id.tv1)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_VEHICLE_TYPE"));
            ((LinearLayout) findViewById(R.id.tabArea1)).setBackground(null);

            ((LinearLayout) findViewById(R.id.tabArea2)).setBackground(getResources().getDrawable(R.drawable.tab_background_selected_23));
            ((MTextView) findViewById(R.id.tv2)).setTextColor(getResources().getColor(R.color.appThemeColor_1));
            ((MTextView) findViewById(R.id.tv2)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_ROUTE"));


//            ((LinearLayout) findViewById(R.id.tabArea3)).setBackground(getResources().getDrawable(R.drawable.tab_background));
            ((MTextView) findViewById(R.id.tv3)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            ((MTextView) findViewById(R.id.tv3)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_PRICE"));


            findViewById(R.id.headerArea).setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        this.geoMap = geoMap;
        setListner(this);
        if (generalFunc.checkLocationPermission(true) == true) {
            getMap().setMyLocationEnabled(true);
        }

        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);

        double pickUpLat = GeneralFunctions.parseDoubleValue(0.0, mapData.get("pickUpLocLattitude"));
        double pickUpLong = GeneralFunctions.parseDoubleValue(0.0, mapData.get("pickUpLocLongitude"));
        setCameraPosition(new LatLng(pickUpLat, pickUpLong));


        Multi_Delivery_Data mt = new Multi_Delivery_Data();
        mt.setDestLat(pickUpLat);
        mt.setDestLong(pickUpLong);
        mt.setDestLatLong(new LatLng(pickUpLat, pickUpLong));
        mt.setHintLable(LBL_MULTI_ADD_NEW_DESTINATION);
        mt.setFRLable(LBL_MULTI_FR_TXT);
        mt.setTOLable(LBL_MULTI_TO_TXT);
        mt.setDestAddress(mapData.get("pickUpLocAddress"));
        mt.setIsFromLoc("Yes");

        ArrayList<Delivery_Data> dt = new ArrayList<Delivery_Data>();
        Delivery_Data frData = new Delivery_Data();
        frData.setDestLat(pickUpLat);
        frData.setDestLong(pickUpLong);
        frData.setDestLatLong(new LatLng(pickUpLat, pickUpLong));
        frData.setDestAddress(mapData.get("pickUpLocAddress"));
        frData.setIsFromLoc("Yes");
        dt.add(0, frData);
        mt.setDt(dt);

        boolean hasDetails = false;
        getStoredDetails(); // Get stored list of locations
        if (templistofViews.size() == 0) {
            listofViews.add(mt); // add pickup/source location
        } else {
            hasDetails = true;
            listofViews.add(mt); // add pickup/source location
            if ((maxDestAddAllowedCount == 1 && templistofViews.size() > 1)) {
                listofViews.add(templistofViews.get(0));  // add only 1st location from stored locations

            } else {
                listofViews.addAll(templistofViews);  // add stored locations
            }

        }

        setDestinationView(listofViews.size() < 2 ? true : false);
        addSourceMarker(listofViews.get(0).getDestLat(), listofViews.get(0).getDestLong());

        if (hasDetails) {
            sortArrayByDistance(true);

        }
    }

    private ArrayList<Multi_Delivery_Data> getStoredDetails() {
        templistofViews = new ArrayList<>();

        if (Utils.checkText(generalFunc.retrieveValue(Utils.MUTLI_DELIVERY_LIST_JSON_DETAILS_KEY))) {

            Gson gson = new Gson();
            String data1 = generalFunc.retrieveValue(Utils.MUTLI_DELIVERY_LIST_JSON_DETAILS_KEY);
            ArrayList<Multi_Delivery_Data> listofViewsData = gson.fromJson(data1, new TypeToken<ArrayList<Multi_Delivery_Data>>() {
                    }.getType()
            );

//            if (templistofViews == null) {
//                templistofViews = new ArrayList<>();
//            }

            for (int i = 0; i < listofViewsData.size(); i++) {
                if (listofViewsData.get(i).isDetailsAdded() == true) {
                    if (generalFunc.retrieveValue(Utils.ALLOW_MULTIPLE_DEST_ADD_KEY).equalsIgnoreCase("No") && templistofViews.size() == 1) {
                        break;
                    } else {
                        templistofViews.add(listofViewsData.get(i));
                        if (maxDestAddAllowedCount == 1) {
                            int size = templistofViews.size();
                            for (int k = 1; k < size; k++) {
                                templistofViews.remove(k);
                            }

                        }
                    }
                }
            }

        }

        return templistofViews;
    }

    private void addSourceMarker(double pickUpLat, double pickUpLong) {
        getMap().clear();
        markerArrayList.clear();
        builder = new LatLngBounds.Builder();

        int pinIcon = R.drawable.pin_dest_green_multi;
        String pinText = LBL_MULTI_FR_TXT;
        pin_image.setImageDrawable(getResources().getDrawable(pinIcon));
        pin_text.setText(pinText);
//        Bitmap marker_time_ic = generalFunc.writeTextOnDrawable(getActContext(), pinIcon, pinText, true, R.string.defaultFont);

        Marker source_marker = getMap().addMarker(
                new MarkerOptions().position(new LatLng(pickUpLat, pickUpLong))
                        .icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))));
        getMap().moveCamera(new LatLng(pickUpLat, pickUpLong, 14));
        builder.include(source_marker.getPosition());
        markerArrayList.add(source_marker);

    }

    private void setListner(MultiDeliverySecondPhaseActivity multiDeliverySecondPhaseActivity) {
        getMap().setOnCameraIdleListener(multiDeliverySecondPhaseActivity);
        getMap().setOnCameraMoveCanceledListener(multiDeliverySecondPhaseActivity);
        getMap().setOnCameraMoveStartedListener(multiDeliverySecondPhaseActivity);
        getMap().setOnCameraMoveListener(multiDeliverySecondPhaseActivity);
    }

    private void setCameraPosition(LatLng location) {
        getMap().moveCamera(new LatLng(location.latitude, location.longitude, Utils.defaultZomLevel));
    }

    public GeoMapLoader.GeoMap getMap() {
        return this.geoMap;
    }

    @Override
    public void onCameraMoveCanceled() {
        showOrHideViews(true);
    }

    @Override
    public void onCameraMoveStarted(int i) {
        showOrHideViews(false);
    }

    @Override
    public void onCameraIdle() {
        showOrHideViews(true);
    }

    void showOrHideViews(boolean show) {
        int bottomAreaState = innerLayout.getVisibility();
        int headerAreaState = headerArea.getVisibility();
        if (show && (bottomAreaState == 0 && headerAreaState == 0)) {
            return;
        } else if (!show && (bottomAreaState == 8 && headerAreaState == 8)) {
            return;
        }
        animateView(innerLayout, show);
        animateView(headerArea, show);
    }

    public void animateView(View view, boolean expand) {
        animShow = AnimationUtils.loadAnimation(this, R.anim.view_show);
        animHide = AnimationUtils.loadAnimation(this, R.anim.view_hide);

        if (expand) {
            view.setVisibility(View.VISIBLE);
            findViewById(R.id.view).setVisibility(View.VISIBLE);
            view.startAnimation(animShow);
            reSetGoogleMap(false);
        } else {
            view.startAnimation(animHide);
            view.setVisibility(View.GONE);
            findViewById(R.id.view).setVisibility(View.GONE);
            reSetGoogleMap(true);

        }
    }

    @Override
    public void onItemClickList(String type, int position) {
        //Item Clicked

        if (type.equalsIgnoreCase("Remove")) {
            boolean isDestDetailsAdded = false;
            if (listofViews.get(position).isDestination()) {
                isDestDetailsAdded = true;
            }
            listofViews.remove(position);

            showAddDestinationArea();

            destAdapter.notifyDataSetChanged();
            if (arrowUp) {
                setScrollviewHeight();
            } else if (listofViews.size() < 4) {
                setScrollviewHeight();
            }
            if (isDestDetailsAdded) {
                sortArrayByDistance(true);
            }

        } else if (type.equalsIgnoreCase("Select")) {

            if (listofViews.get(position).getIsFromLoc().equalsIgnoreCase("Yes")) {
                return;
            }

            addOrEditDeliveryDetails(position, type);

        } else if (type.equalsIgnoreCase("Add")) {

            addOrEditDeliveryDetails(-1, type);

        }
    }

    private void showAddDestinationArea() {
        allowDestAdd = false;

        if (listofViews.size() > 1 && listofViews.get(1).isDetailsAdded() && !ALLOW_MULTIPLE_DEST_ADD_KEY.equalsIgnoreCase("No")) {
            allowDestAdd = true;
        }
    }


    private void addOrEditDeliveryDetails(int position, String type) {
        if (listofViews.size() - 1 >= maxDestAddAllowedCount && position == -1) {
            generalFunc.showGeneralMessage("", "you can not add destinations more than" + maxDestAddAllowedCount);
            return;
        }
        Bundle bn = new Bundle();
        bn.putString("isDeliverNow", mapData.get("isDeliverNow"));
        bn.putInt("selectedPos", position);
        String json = "";
        if (position != -1) {
            Gson gson = new Gson();
            json = gson.toJson(listofViews.get(position).getDt());
        }

        if (position != -1 && listofViews.get(position).getDestLat() != 0.0 && listofViews.get(position).getDestLong() != 0.0) {
            bn.putDouble("lat", listofViews.get(position).getDestLat());
            bn.putDouble("long", listofViews.get(position).getDestLong());
            bn.putString("address", listofViews.get(position).getDestAddress());
            bn.putString("building", listofViews.get(position).getDestBuilding());
            bn.putString("apartment", listofViews.get(position).getDestApartment());
        } else {
            bn.putDouble("lat", 0.0);
            bn.putDouble("long", 0.0);
            bn.putString("address", "");
            bn.putString("building", "");
            bn.putString("apartment", "");
        }
        bn.putString("selectedDetails", json);
        bn.putBoolean("isDestAdded", position != -1 ? listofViews.get(position).isDetailsAdded() : false);
        bn.putBoolean("isEdit", position != -1 ? false : true);
        bn.putBoolean("isFromMulti", getIntent().getBooleanExtra("fromMulti", true));

        new ActUtils(getActContext()).startActForResult(EnterMultiDeliveryDetailsActivity.class, bn, Utils.MULTI_DELIVERY_DETAILS_ADD_CODE);
    }

    @Override
    public void onCameraMove() {
        showOrHideViews(false);
    }

    private Activity getActContext() {
        return MultiDeliverySecondPhaseActivity.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.MULTI_DELIVERY_DETAILS_ADD_CODE && resultCode == RESULT_OK && data != null) {

            int listPos = data.getIntExtra("selectedPos", -1);


            if (data.hasExtra("selectedDetails")) {

                if (listPos == -1) {
                    addEmptyDestination(true);
                    listPos = listofViews.size() - 1;
                }

                Gson gson = new Gson();
                String data1 = data.getStringExtra("selectedDetails");
                ArrayList<Delivery_Data> dataMap = gson.fromJson(data1, new TypeToken<ArrayList<Delivery_Data>>() {
                        }.getType()
                );
                if (dataMap != null && listofViews != null && listofViews.size() > 0) {
                    listofViews.get(listPos).setDt(dataMap);

                    for (int i = 0; i < dataMap.size(); i++) {
                        // set inside delivery Address details
                        if (dataMap.get(i).geteInputType().equalsIgnoreCase("SelectAddress")) {
                            listofViews.get(listPos).setDestAddress(dataMap.get(i).getDestAddress());
                            listofViews.get(listPos).setDestLong(dataMap.get(i).getDestLong());
                            listofViews.get(listPos).setDestLat(dataMap.get(i).getDestLat());
                            listofViews.get(listPos).setDestLatLong(dataMap.get(i).getDestLatLong());
                            listofViews.get(listPos).setDestBuilding(dataMap.get(i).getDestBuilding());
                            listofViews.get(listPos).setDestAparment(dataMap.get(i).getDestApartment());
                            listofViews.get(listPos).setDetailsAdded(true);
                            break;
                        }
                    }

                    if (listofViews.get(listPos).getIsFromLoc().equalsIgnoreCase("No")) {
                        sortArrayByDistance(true);
                    } else {
                        findRoute();
                    }
                }

            }

        } else if (requestCode == Utils.ALL_DELIVERY_DETAILS_ADDED_CODE && resultCode == RESULT_OK && data != null) {

            Gson gson = new Gson();
            ArrayList<Multi_Delivery_Data> list = new ArrayList<>();

            list = gson.fromJson(data.getStringExtra("list"), new TypeToken<ArrayList<Multi_Delivery_Data>>() {
                    }.getType()
            );

            storeDetails(list, false);


            Bundle bn = new Bundle();
            bn.putSerializable("deliveries", generalFunc.retrieveValue(Utils.MUTLI_DELIVERY_JSON_DETAILS_KEY));
            bn.putBoolean("isMulti", isMulti);
            bn.putString("paymentMethod", data.getStringExtra("paymentMethod"));
            bn.putString("promocode", data.getStringExtra("promocode"));

            bn.putString("total_del_dist", data.getStringExtra("total_del_dist"));
            bn.putString("total_del_time", data.getStringExtra("total_del_time"));
            bn.putString("totalFare", data.getStringExtra("totalFare"));
            bn.putString("totalFare", data.getStringExtra("totalFare"));
            bn.putString("cabRquestType", data.getStringExtra("cabRquestType"));
            bn.putString("selectedTime", data.getStringExtra("selectedTime"));


            Logger.d("Api", "second last screen bundle data" + bn.toString());
            (new ActUtils(getActContext())).setOkResult(bn);
            finish();


        }
    }

    // Sort array based on selected method - Manual calculation or normal
    private void sortArrayByDistance(boolean b) {
        if (generalFunc.retrieveValue(Utils.ENABLE_ROUTE_OPTIMIZE_MULTI_KEY).equalsIgnoreCase("Yes")) {
            // Manual Calculation
            if (count == 0) {
                // compare from source to dest1 n dest1 to dest2 with all destinations
                destinationsArraylist.clear();
                filteredDestinationsArraylist.clear();
                filteredDestinationsArraylist.add(listofViews.get(0));
                destinationsArraylist.addAll(listofViews);
            }

            Logger.d("Api", "loop main" + count);

            if (destinationsArraylist.size() > 1) {
                Logger.d("Api", "mainArraylist size()" + destinationsArraylist.size());
                for (int i = 1; i < destinationsArraylist.size(); i++) {
                    calculateManualDistance(i, count, ((i + 1) == destinationsArraylist.size()), b);
                }
            } else {

                listofViews = new ArrayList<>();
                listofViews.addAll(filteredDestinationsArraylist);
                count = 0;
                if (b) {
                    findRoute();
                }
            }
        } else {
            // compare from source to each destination once
            ArrayList<Multi_Delivery_Data> mainArraylist = new ArrayList<>();

            for (int i = 1; i < listofViews.size(); i++) {
                calculateDistance(listofViews.get(i), mainArraylist);
            }

            Collections.sort(mainArraylist, new MapComparator(""));

            ArrayList<Multi_Delivery_Data> tempArrayList = new ArrayList<>();
            tempArrayList.add(listofViews.get(0));
            tempArrayList.addAll(mainArraylist);

            listofViews = new ArrayList<>();
            listofViews.addAll(tempArrayList);

            if (b) {
                findRoute();
            }
        }
    }

    // Store Details
    private void storeDetails(ArrayList<Multi_Delivery_Data> array, boolean setFocusable) {

        nextbtn.setClickable(setFocusable);
        nextbtn.setFocusable(setFocusable);

        ArrayList<Multi_Delivery_Data> finalAllDetailsArray = new ArrayList<Multi_Delivery_Data>();

        JSONArray jaStore = new JSONArray();
        JSONArray jaStore1 = new JSONArray();
        JSONArray mainJaStore = new JSONArray();
        JSONArray mainAllJaStore = new JSONArray();

        if (setFocusable) {
            for (int i = 0; i < array.size(); i++) {
                storeDetails(i, jaStore, jaStore1, finalAllDetailsArray, array, setFocusable);
            }
        } else {
            for (int i = 1; i < array.size(); i++) {
                storeDetails(i, jaStore, jaStore1, finalAllDetailsArray, array, setFocusable);
            }
        }

        try {

            mainJaStore.put(0, jaStore1);
            mainAllJaStore.put(0, jaStore);
        } catch (JSONException e) {
            Logger.e("Exception", "::" + e.getMessage());
        }


        generalFunc.removeValue(Utils.MUTLI_DELIVERY_JSON_DETAILS_KEY);
        generalFunc.storeData(Utils.MUTLI_DELIVERY_JSON_DETAILS_KEY, jaStore1.toString());

        Gson gson = new Gson();
        String json = gson.toJson(finalAllDetailsArray);

        generalFunc.removeValue(Utils.MUTLI_DELIVERY_LIST_JSON_DETAILS_KEY);
        generalFunc.storeData(Utils.MUTLI_DELIVERY_LIST_JSON_DETAILS_KEY, json);


    }

    private JSONObject storeDetails(int pos, JSONArray jaStore, JSONArray jaStore1, ArrayList<Multi_Delivery_Data> finalAllDetailsArray, ArrayList<Multi_Delivery_Data> mainArray, boolean setFocusable) {
        JSONObject deliveriesObj = new JSONObject();
        JSONObject deliveriesObjall = new JSONObject();
        ArrayList<Delivery_Data> dt = mainArray.get(pos).getDt();
        Multi_Delivery_Data details = mainArray.get(pos);


        for (int i = 0; i < dt.size(); i++) {
            try {

                if (dt.get(i).geteInputType().equalsIgnoreCase("SelectAddress")) {
                    deliveriesObjall.put("DestAddress", dt.get(i).getDestAddress());
                    deliveriesObjall.put("DestLat", dt.get(i).getDestLat());
                    deliveriesObjall.put("DestLong", dt.get(i).getDestLong());
                } else {
                    deliveriesObjall.put("vFieldValue", dt.get(i).getvFieldValue());
                    deliveriesObjall.put("iDeliveryFieldId", dt.get(i).getiDeliveryFieldId());
                    deliveriesObjall.put("eInputType", dt.get(i).geteInputType());
                    deliveriesObjall.put("selectedOptionID", dt.get(i).getSelectedOptionID());
                    deliveriesObjall.put("itemID", dt.get(i).getItemID());
                    deliveriesObjall.put("ePaymentByReceiver", dt.get(i).getPaymentDoneBy());
                }


                if (dt.get(i).geteInputType().equals("Select")) {
                    deliveriesObj.put(dt.get(i).getiDeliveryFieldId(), dt.get(i).getSelectedOptionID());
                } else if (dt.get(i).geteInputType().equalsIgnoreCase("SelectAddress")) {
                    deliveriesObj.put("vReceiverAddress", dt.get(i).getDestAddress());
                    deliveriesObj.put("vReceiverLatitude", dt.get(i).getDestLat());
                    deliveriesObj.put("vReceiverLongitude", dt.get(i).getDestLong());
                } else if (dt.get(i).geteInputType().equals("Number")) {
                    HashMap<String, String> countryCodeData = dt.get(i).getCountryCodeData();
                    if (countryCodeData.containsKey("vPhoneCode")) {
                        deliveriesObj.put(dt.get(i).getiDeliveryFieldId(), countryCodeData.get("vPhoneCode")
                                + dt.get(i).getvFieldValue());
                    } else {
                        deliveriesObj.put(dt.get(i).getiDeliveryFieldId(), dt.get(i).getvFieldValue());
                    }
                } else {
                    deliveriesObj.put(dt.get(i).getiDeliveryFieldId(), dt.get(i).getvFieldValue());
                }


                deliveriesObj.put("ePaymentByReceiver", mainArray.get(pos).getePaymentByReceiver());

                jaStore.put(i, deliveriesObjall);

            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }
        if (setFocusable) {
            finalAllDetailsArray.add(pos, details);
            try {
                jaStore1.put(pos, deliveriesObj);
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }


        } else {
            finalAllDetailsArray.add(details);
            jaStore1.put(deliveriesObj);
        }


        return deliveriesObj;
    }

    // Compare with all entered destinations and sort all points according distance
    private void calculateManualDistance(int loopCount, int compareCount, boolean isLast, boolean b) {

        Multi_Delivery_Data deliveryDetailsObj = destinationsArraylist.get(loopCount);
        Multi_Delivery_Data deliveryDetailsObjSource = destinationsArraylist.get(0);
        Location loc1 = new Location("");
        loc1.setLatitude(deliveryDetailsObjSource.getDestLat());
        loc1.setLongitude(deliveryDetailsObjSource.getDestLong());

        Location loc2 = new Location("");
        loc2.setLatitude(deliveryDetailsObj.getDestLat());
        loc2.setLongitude(deliveryDetailsObj.getDestLong());


        int randomNo = r.nextInt(1000 + 1);
        long distanceInMeters = (long) loc1.distanceTo(loc2);  // direct distance
        int lowestTime = ((int) (((distanceInMeters / 1000) * DRIVER_ARRIVED_MIN_TIME_PER_MINUTE) * 60)); // estimated Time
        deliveryDetailsObj.setDistance(0);
        deliveryDetailsObj.setTime(lowestTime);
        deliveryDetailsObj.setDistance(distanceInMeters);
        deliveryDetailsObj.setID("" + randomNo);
        destinationsArraylist.set(loopCount, deliveryDetailsObj);

        if (isLast) {
            Collections.sort(destinationsArraylist, new MapComparator(""));
            filteredDestinationsArraylist.add(destinationsArraylist.get(0 + 1));
            destinationsArraylist.remove(0 + 1);
            count = compareCount + 1;

            if (listofViews.size() == count + 1) {
                listofViews = new ArrayList<>();
                listofViews.addAll(filteredDestinationsArraylist);
                count = 0;
                if (b) {
                    findRoute();
                }
            } else {
                sortArrayByDistance(b);
            }
        }


    }

    // Find and sort all points distance
    private void calculateDistance(Multi_Delivery_Data deliveryDetailsObj, ArrayList<Multi_Delivery_Data> mainArraylist) {

        Location loc1 = new Location("");
        loc1.setLatitude(listofViews.get(0).getDestLat());
        loc1.setLongitude(listofViews.get(0).getDestLong());

        Location loc2 = new Location("");
        loc2.setLatitude(deliveryDetailsObj.getDestLat());
        loc2.setLongitude(deliveryDetailsObj.getDestLong());

        int randomNo = r.nextInt(1000 + 1);
        long distanceInMeters = (long) loc1.distanceTo(loc2);  // direct distance
        int lowestTime = ((int) ((distanceInMeters / 1000) * DRIVER_ARRIVED_MIN_TIME_PER_MINUTE) * 60); // estimated Time
        deliveryDetailsObj.setDistance(0);
        deliveryDetailsObj.setTime(lowestTime);
        deliveryDetailsObj.setDistance(distanceInMeters);
        deliveryDetailsObj.setID("" + randomNo);
        mainArraylist.add(deliveryDetailsObj);
    }


    // Sort list data based on distance
    public void findRoute() {

        addSourceMarker(listofViews.get(0).getDestLat(), listofViews.get(0).getDestLong());
        HashMap<String, String> hashMap = new HashMap<>();
        long distanceVal = 0;
        long timeVal = 0;
        if (generalFunc.retrieveValue(Utils.ENABLE_ROUTE_OPTIMIZE_MULTI_KEY).equalsIgnoreCase("Yes")) {
            // do manual calculation of time & Distance n add pins on map

            dest_details_Array = new ArrayList<>();

            for (int i = 1; i < listofViews.size(); i++) {
                if (listofViews.get(i).isDetailsAdded() == true) {
                    int pinIcon;
                    if (i == listofViews.size() - 1) {
                        pinIcon = R.drawable.pin_dest_select_multi;
                    } else {
                        pinIcon = R.drawable.pin_dest_stop_point_multi;
                    }
                    String pinText = listofViews.size() > 2 ? ("" + i) : LBL_MULTI_TO_TXT;
//                    Bitmap marker_time_ic = generalFunc.writeTextOnDrawable(getActContext(), pinIcon, pinText, false, R.string.defaultFont);
                    pin_image.setImageDrawable(getResources().getDrawable(pinIcon));
                    pin_text.setText(pinText);
                    Marker dest_marker = geoMap.addMarker(new MarkerOptions().position(listofViews.get(i).getDestLatLong()).icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))));
                    if (markerArrayList.size() > i && markerArrayList.get(i) != null) {
                        markerArrayList.get(i).remove();
                    }
                    markerArrayList.add(dest_marker);
                    builder.include(dest_marker.getPosition());
                    listofViews.get(i).setDestination(true);
                    long origTime = listofViews.get(i).getTime();
                    long origDistance = listofViews.get(i).getDistance();
                    timeVal = timeVal + origTime;
                    distanceVal = distanceVal + origDistance;

                    Multi_Dest_Info_Detail_Data dt = new Multi_Dest_Info_Detail_Data();
                    dt.setDistance(origDistance);
                    dt.setTime(origTime);

                    distance = "" + (distanceVal);
                    time = "" + (timeVal);
                    isManualCalculation = true;
                    dest_details_Array.add(dt);
                }
            }

            setDestinationView(false);
            storeDetails(listofViews, true);

            if (listofViews.size() > 1) {
                bindAllMarkers();
            }
            return;
        }


        // Origin of route
        String str_origin = "origin=" + listofViews.get(0).getDestLat() + "," + listofViews.get(0).getDestLong();


        String str_dest = "";
        String waypoints = "";
        wayPointslist = new ArrayList<>();
        destPointlist = new ArrayList<>();
        finalPointlist = new ArrayList<>();
        dest_details_Array = new ArrayList<>();

        templistofViews = new ArrayList<>();
        isManualCalculation = false;

        for (int i = 0; i < listofViews.size(); i++) {
            if (listofViews.get(i).isDetailsAdded()) {
                templistofViews.add(listofViews.get(i));
            }
        }

        if (templistofViews.size() == 0) {

            if (route_polyLine != null) {
                route_polyLine.remove();
            }
            return;
        }

        ArrayList<String> data_waypoints = new ArrayList<>();
        if (templistofViews.size() > 0) {
            String lastAddress = "";
            for (int i = 0; i < templistofViews.size(); i++) {

                if (i == templistofViews.size() - 1) {
                    str_dest = "destination=" + templistofViews.get(templistofViews.size() - 1).getDestLat() + "," + templistofViews.get(templistofViews.size() - 1).getDestLong();

                    hashMap.put("d_latitude", templistofViews.get(templistofViews.size() - 1).getDestLat() + "");
                    hashMap.put("d_longitude", templistofViews.get(templistofViews.size() - 1).getDestLong() + "");
                    templistofViews.get(i).setDestination(true);
                    destPointlist.add(templistofViews.get(i));
                } else if (i == templistofViews.size() - 2) {
                    templistofViews.get(i).setDestination(true);
                    wayPointslist.add(templistofViews.get(i));
                    lastAddress = templistofViews.get(i).getDestLat() + "," + templistofViews.get(i).getDestLong();
                    data_waypoints.add(templistofViews.get(i).getDestLat() + "," + templistofViews.get(i).getDestLong());

                } else {
                    templistofViews.get(i).setDestination(true);
                    wayPointslist.add(templistofViews.get(i));
                    waypoints = waypoints + templistofViews.get(i).getDestLat() + "," + templistofViews.get(i).getDestLong() + "|";
                    data_waypoints.add(templistofViews.get(i).getDestLat() + "," + templistofViews.get(i).getDestLong());

                }

            }
            waypoints = "waypoints=optimize:true|" + waypoints + lastAddress;
        } else {
            str_dest = "destination=" + templistofViews.get(templistofViews.size() - 1).getDestLat() + "," + templistofViews.get(templistofViews.size() - 1).getDestLong();
            hashMap.put("d_latitude", templistofViews.get(templistofViews.size() - 1).getDestLat() + "");
            hashMap.put("d_longitude", templistofViews.get(templistofViews.size() - 1).getDestLong() + "");
            destPointlist.add(templistofViews.get(templistofViews.size() - 1));
        }

        hashMap.put("waypoints", data_waypoints.toString());
        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(listofViews.get(0).getDestLat() + "", listofViews.get(0).getDestLong() + "").setDestLatitude(hashMap.get("d_latitude")).setDestLongitude(hashMap.get("d_longitude")).setWayPoints(MyApp.getInstance().GetStringArray(data_waypoints)).build(), AppService.Service.DIRECTION, new AppService.ServiceDelegate() {
            @Override
            public void onResult(HashMap<String, Object> data) {
                if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                    isRouteFail = true;
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                    return;
                }

                manageResult(data, new LatLng(GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_latitude")), GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_longitude"))));

            }
        });


    }

    // Add Markers
    private void bindAllMarkers() {
        try {
            if (builder != null && getMap() != null) {
                LatLngBounds bounds = builder.build();
                LatLng center = bounds.getCenter();

                LatLng northEast = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
                LatLng southWest = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));

                builder.include(southWest);
                builder.include(northEast);

                int screenWidth = getResources().getDisplayMetrics().widthPixels;

                int padding = (int) (screenWidth * 0.15); // offset from edges of the map 15% of screen

                getMap().setMaxZoomPreference(geoMap.getMaxZoomLevel());
                getMap().animateCamera(builder.build(), padding);
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }


    // add route polyline line
    public PolylineOptions getGoogleRouteOptions(String directionJson, int width, int color, Context mContext, ArrayList<Multi_Delivery_Data> list, ArrayList<Multi_Delivery_Data> wayPointslist, ArrayList<Multi_Delivery_Data> destPointlist, ArrayList<Multi_Delivery_Data> finalPointlist, ArrayList<Multi_Dest_Info_Detail_Data> dest_details_array, ArrayList<Marker> markerArrayList, GeoMapLoader.GeoMap geoMap, LatLngBounds.Builder builder) {
        PolylineOptions lineOptions = new PolylineOptions();

        try {


            DataParser parser = new DataParser(mContext, list, wayPointslist, destPointlist, finalPointlist, dest_details_array, markerArrayList, geoMap, builder);
            HashMap<String, String> routeMap = new HashMap<>();
            routeMap.put("routes", generalFunc.getJsonArray("routes", directionJson).toString());
            String responseString = routeMap.toString();
            List<List<HashMap<String, String>>> routes_list = parser.parse(new JSONObject(responseString));
            distance = parser.distance;
            time = parser.time;
            ArrayList<LatLng> points = new ArrayList<LatLng>();

            if (routes_list.size() > 0) {
                // Fetching i-th route
                List<HashMap<String, String>> path = routes_list.get(0);

                // Fetching all the points in i-th route
                for (int j = 0; j < path.size(); j++) {
                    HashMap<String, String> point = path.get(j);

                    double lat = Double.parseDouble(point.get("lat"));
                    double lng = Double.parseDouble(point.get("lng"));
                    LatLng position = new LatLng(lat, lng);
                    points.add(position);

                }

                lineOptions.addAll(points);
                lineOptions.width(width);
                lineOptions.color(color);

                return lineOptions;
            } else {
                return null;
            }
        } catch (Exception e) {
            return null;
        }
    }


    boolean isRouteFail = false;


    public void manageResult(HashMap<String, Object> data, LatLng destLatLng) {
        if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
            return;
        }

        String responseString = data.get("RESPONSE_DATA").toString();

        if (responseString.equalsIgnoreCase("")) {
            GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
            alertBox.setContentMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
            alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
            alertBox.setBtnClickList(btn_id -> {
                alertBox.closeAlertBox();
                backImgView.performClick();

            });
            alertBox.showAlertBox();
            isRouteFail = true;
            return;
        }

        if (responseString != null && !responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {


            JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
            if (obj_routes != null && obj_routes.length() > 0) {
                isRouteFail = false;


                PolylineOptions lineOptions = getGoogleRouteOptions(responseString, 5, getActContext().getResources().getColor(R.color.black), getActContext(), listofViews, wayPointslist, destPointlist, finalPointlist, dest_details_Array, markerArrayList, getMap(), builder);

                if (lineOptions != null) {
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                    }
                    route_polyLine = getMap().addPolyline(lineOptions);
                }

                if (finalPointlist.size() > 0) {
                    ArrayList<Multi_Delivery_Data> finalAllPointlist = new ArrayList<>();

                    finalAllPointlist = new ArrayList<>();
                    finalAllPointlist.add(listofViews.get(0));
                    finalAllPointlist.addAll(finalPointlist);

                    listofViews.clear();
                    listofViews.addAll(finalAllPointlist);
                }

                setDestinationView(false);
                storeDetails(listofViews, true);
                bindAllMarkers();
            } else {
                generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"),
                        generalFunc.retrieveLangLBl("", "LBL_GOOGLE_DIR_NO_ROUTE"));
            }


        } else {
            isRouteFail = false;
            PolylineOptions lineOptions = new PolylineOptions();

            try {
                JSONArray obj_routes1 = generalFunc.getJsonArray("data", responseString);

                ArrayList<LatLng> points = new ArrayList<LatLng>();

                distance = data.get("DISTANCE").toString();
                time = data.get("DURATION").toString();
                if (obj_routes1 != null && obj_routes1.length() > 0) {
                    // Fetching i-th route
                    // Fetching all the points in i-th route
                    for (int j = 0; j < obj_routes1.length(); j++) {

                        JSONObject point = generalFunc.getJsonObject(obj_routes1, j);

                        LatLng position = new LatLng(GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("latitude", point).toString()), GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("longitude", point).toString()));


                        points.add(position);


                    }
                    Multi_Dest_Info_Detail_Data dt = new Multi_Dest_Info_Detail_Data();


                    dt.setDistance(Math.round(GeneralFunctions.parseDoubleValue(0, distance)));
                    dt.setTime(Math.round(GeneralFunctions.parseDoubleValue(0, time)));

                    dest_details_Array.add(dt);


                    lineOptions.addAll(points);
                    lineOptions.width(5);
                    lineOptions.color(getActContext().getResources().getColor(R.color.black));


                } else {

                }


                JSONArray waypoint_order = generalFunc.getJsonArray("waypoint_order", responseString);


                if (waypoint_order != null && waypoint_order.length() > 0) {
                    for (int l = 0; l < waypoint_order.length(); l++) {

                        int ordering = GeneralFunctions.parseIntegerValue(0, waypoint_order.get(l).toString());
                        Logger.d("Api", "waypoint_order sequence : ordering" + ordering);
                        wayPointslist.get(l).setSequenceId(ordering);
                        destPointlist.get(0).setSequenceId(waypoint_order.length());

                    }

                    finalPointlist.addAll(wayPointslist);
                    finalPointlist.addAll(destPointlist);

                } else {

                    addDestination(destLatLng.latitude, destLatLng.longitude, -1);
                }

                for (int i = 0; i < finalPointlist.size(); i++) {
                    addDestination(finalPointlist.get(i).getDestLat(), finalPointlist.get(i).getDestLong(), i);
                }


            } catch (Exception e) {
                Logger.d("DirectionResult", "::" + e.toString());
                Logger.e("Exception", "::" + e.getMessage());

            }

            if (lineOptions != null) {
                if (route_polyLine != null) {
                    route_polyLine.remove();
                }
                route_polyLine = getMap().addPolyline(lineOptions);
            }

            if (finalPointlist.size() > 0) {
                ArrayList<Multi_Delivery_Data> finalAllPointlist = new ArrayList<>();

                finalAllPointlist = new ArrayList<>();
                finalAllPointlist.add(listofViews.get(0));
                finalAllPointlist.addAll(finalPointlist);

                listofViews.clear();
                listofViews.addAll(finalAllPointlist);
            }

            setDestinationView(false);
            storeDetails(listofViews, true);
            bindAllMarkers();


        }


    }

    public void addDestination(double lat, double lng, int pos) {
        String pinText = generalFunc.retrieveLangLBl("TO", "LBL_MULTI_TO_TXT");

        if (finalPointlist.size() > 1) {
            pinText = "" + (pos + 1);
        }

        int pinIcon = R.drawable.pin_dest_stop_point_multi;
//        Bitmap marker_time_ic = generalFunc.writeTextOnDrawable(getActContext(), pinIcon, pinText, false, R.string.defaultFont);
        pin_image.setImageDrawable(ResourcesCompat.getDrawable(getResources(), pinIcon, null));
        pin_text.setText(pinText);


        Marker dest_marker = getMap().addMarker(new MarkerOptions().position(new LatLng(lat, lng))
                .icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))));
        builder.include(dest_marker.getPosition());
        markerArrayList.add(dest_marker);
    }


    // draw RouteLine


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == R.id.backImgView) {
            MultiDeliverySecondPhaseActivity.super.onBackPressed();
        } else if (i == nextbtn.getId()) {

            boolean detailsAdded = false;
            for (int j = 0; j < listofViews.size(); j++) {
                if (listofViews.get(j).isDetailsAdded() || listofViews.get(j).getIsFromLoc().equalsIgnoreCase("Yes")) {
                    detailsAdded = true;
                } else {
                    detailsAdded = false;
                }
            }

            // Check all details added or not ?
            if (detailsAdded == false) {
                if (maxDestAddAllowedCount == 1) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please add all location's details", "LBL_ADD_DEST_MSG_DELIVER_ITEM"));
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please add all location's details", "LBL_MULTI_AD_ALL_DEST_TXT"));
                }

                return;
            }

            if (isRouteFail) {
                GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                alertBox.setContentMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                alertBox.setBtnClickList(btn_id -> {
                    alertBox.closeAlertBox();

                });
                alertBox.showAlertBox();
            }


            if (Utils.checkText(time) && Utils.checkText(distance)) {

                Bundle bn = new Bundle();
                mapData.put("time", time);
                mapData.put("distance", distance);
                bn.putSerializable("selectedData", mapData);
                Gson gson = new Gson();
                String json = gson.toJson(listofViews);
                String jaStore = gson.toJson(dest_details_Array);
                // String json1 = gson.toJson(jaStore);
                bn.putString("selectedDetails", json);
                Logger.d("selectedDetails", "::" + json);
                Logger.d("selectedDetailstimeAndDistArr", "::" + jaStore);
                bn.putSerializable("timeAndDistArr", jaStore);
                bn.putBoolean("isFromMulti", getIntent().getBooleanExtra("fromMulti", true));
                bn.putBoolean("isManualCalculation", true);
                bn.putString("pickUpLocAddress", mapData.get("pickUpLocAddress"));
                bn.putInt("maxDestAddAllowedCount", maxDestAddAllowedCount);
                new ActUtils(getActContext()).startActForResult(MultiDeliveryThirdPhaseActivity.class, bn, Utils.ALL_DELIVERY_DETAILS_ADDED_CODE);
            }


        } else if (i == R.id.iv_arrow) {
            if (arrowUp) {
                iv_arrow.setImageResource(R.mipmap.arrow_sliding_down);
                arrowUp = false;
                setScrollviewHeight();
            } else {
                iv_arrow.setImageResource(R.mipmap.arrow_sliding_up);
                arrowUp = true;
                setScrollviewHeight();
            }
        } else if (i == R.id.iv_current_loc) {
            if (listofViews != null && listofViews.size() > 0) {
                getMap().moveCamera(new LatLng(listofViews.get(0).getDestLat(), listofViews.get(0).getDestLong(), 14));
            }
        }
    }


}
