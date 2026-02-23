package com.act;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.MultiPaymentTypeRecyclerAdapter;
import com.dialogs.BottomScheduleDialog;
import com.general.files.ActUtils;
import com.general.files.CovidDialog;
import com.general.files.DataParser;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.PreferenceDailog;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.model.Delivery_Data;
import com.model.Multi_Delivery_Data;
import com.model.Multi_Dest_Info_Detail_Data;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;

/**
 * Created by Esite on 10-04-2018.
 */

public class MultiDeliveryThirdPhaseActivity extends ParentActivity implements MultiPaymentTypeRecyclerAdapter.OnTypeSelectListener, BottomScheduleDialog.OndateSelectionListener {
    HashMap<String, String> mapData = new HashMap<>();

    MTextView titleTxt;
    ImageView backImgView;
    MTextView paymentTitle;
    LinearLayout fareDetailDisplayArea, mainContentArea;

    String selectedcar = "";
    String vVehicleType = "";

    LinearLayout paymentArea;
    private String userProfileJson;
    private ArrayList<Multi_Delivery_Data> list = new ArrayList<>();

    MButton btn_type2;
    MButton btn_schedule;

    boolean isCardValidated = true;

    // promo code
    private String appliedPromoCode = "";

    public JSONArray ja = new JSONArray();
    ArrayList<Multi_Dest_Info_Detail_Data> dest_details_Array = new ArrayList<>();

    RecyclerView paymentMethodRecyclerView;
    String selectedRecipientName = "", selectedPayTypeName = "";
    MultiPaymentTypeRecyclerAdapter payTypeAdapter;
    ArrayList<HashMap<String, String>> paymentTypeList = new ArrayList<>();
    ArrayList<HashMap<String, String>> recipientList = new ArrayList<>();
    public double totalFare = 0.0;
    public String individualFare = "0.0";

    //PaymentBy

    String Payment_Type_1 = "Sender";
    public String Payment_Type_2 = "Receiver";
    public String Payment_Type_3 = "Individual";

    public int selectedPos = -1;
    public int selectedRecipientPos = -1;
    public String currencySymbol = "";

    public String PAYMENT_DONE_BY = "PaymentDoneBy";
    private String PAYMENT_PERSON_NAME = "name";
    private String RECIPIENT_NAME = "recipientName";
    private String ITEM_ID_KEY = "ItemID";

    boolean isOutStandingDailogShow = false;
    AlertDialog outstanding_dialog;


    View couponCodeArea;
    LinearLayout promocodeArea;
    MTextView promocodeappliedHTxt;
    MTextView promocodeappliedVTxt;

    ImageView couponCodeImgView, couponCodeCloseImgView;
    MTextView applyCouponHTxt;
    MTextView applyCouponVTxt;
    MTextView appliedPromoHTxtView;
    String responseStr = "";

    ArrayList<Multi_Delivery_Data> templistofViews = new ArrayList<>();  // temp list
    ArrayList<Multi_Delivery_Data> wayPointslist = new ArrayList<>();  // List of Way Points/ middle points
    ArrayList<Multi_Delivery_Data> destPointlist = new ArrayList<>();  // destination Points
    ArrayList<Multi_Delivery_Data> finalPointlist = new ArrayList<>();  // final Points list with time & distance & based on shortest location first algorithm


    private String distance = "";
    private String time = "";
    LinearLayout errorViewArea;
    ErrorView errorView;

    String SYSTEM_PAYMENT_FLOW = "";
    String APP_PAYMENT_METHOD = "";
    String APP_PAYMENT_MODE = "";
    boolean isCashSelected = true;
    boolean iswalletSelect = false;
    ImageView carTypeImg, infoImage;
    private String selectedDateTime = "", cabRquestType, tInfoText;
    ProgressBar progress_bar;
    LinearLayout PromoCodearea;
    public double EachRecipeintAmount = 0.0;

    int maxDestAddAllowedCount;

    LinearLayout scheduleArea;
    private static final int WEBVIEWPAYMENT = 001;
    boolean isDeliverNow = false;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_multi_third_phase_multi);
        mapData = (HashMap<String, String>) getIntent().getSerializableExtra("selectedData");

        distance = mapData.get("distance");
        time = mapData.get("time");


        retriverUserProfileJson();

        if (getIntent().hasExtra("selectedDetails")) {

            Gson gson = new Gson();
            String data1 = getIntent().getStringExtra("selectedDetails");
            String data2 = getIntent().getStringExtra("timeAndDistArr");
            maxDestAddAllowedCount = getIntent().getIntExtra("maxDestAddAllowedCount", 0);
            Logger.d("timeAndDistArr", "::" + data2);

            list = gson.fromJson(data1, new TypeToken<ArrayList<Multi_Delivery_Data>>() {
                    }.getType()
            );

            dest_details_Array = new Gson().fromJson(data2, new TypeToken<ArrayList<Multi_Dest_Info_Detail_Data>>() {
                    }.getType()
            );

        }


        init();
        setLables();

        setSelected("3");

        paymentTypeList = new ArrayList<>();
        recipientList = new ArrayList<>();

        // add Payment Details

        for (int i = 0; i < list.size(); i++) {

            if (list.get(i).getIsFromLoc().equalsIgnoreCase("Yes")) {

                HashMap<String, String> map = new HashMap<>();
                map.put(PAYMENT_DONE_BY, Payment_Type_1);
                map.put(PAYMENT_PERSON_NAME, generalFunc.retrieveLangLBl("Sender", "LBL_MULTI_SENDER_TXT"));
                paymentTypeList.add(map);

                HashMap<String, String> map1 = new HashMap<>();
                map1.put(PAYMENT_DONE_BY, Payment_Type_2);
                map1.put(PAYMENT_PERSON_NAME, generalFunc.retrieveLangLBl("Any One Receiver", "LBL_MULTI_ANY_RECIPIENT"));
                paymentTypeList.add(map1);

                if (list.size() > 2) {
                    HashMap<String, String> map2 = new HashMap<>();
                    map2.put(PAYMENT_DONE_BY, Payment_Type_3);
                    map2.put(PAYMENT_PERSON_NAME, generalFunc.retrieveLangLBl("Each Receiver", "LBL_MULTI_EACH_RECIPIENT"));
                    paymentTypeList.add(map2);
                }


            } else {

                ArrayList<Delivery_Data> arrayList = list.get(i).getDt();
                if (arrayList != null && arrayList.size() > 0) {
                    for (int j = 0; j < arrayList.size(); j++) {
                        if (arrayList.get(j) != null && (arrayList.get(j).getvFieldName().equalsIgnoreCase("Recepient Name") || arrayList.get(j).getiDeliveryFieldId().equalsIgnoreCase("2"))) {
                            HashMap<String, String> map = new HashMap<>();
                            map.put(PAYMENT_DONE_BY, Payment_Type_2);
                            map.put(RECIPIENT_NAME, arrayList.get(j).getvFieldValue());
                            map.put(ITEM_ID_KEY, "" + arrayList.get(j).getItemID());
                            map.put(PAYMENT_PERSON_NAME, generalFunc.retrieveLangLBl("", "LBL_RECIPIENT") + " " + (i) + " (" + arrayList.get(j).getvFieldValue() + ")");
                            recipientList.add(map);
                            break;
                        }
                    }

                }
            }


        }

        // if route calculation enable && manual calculation then call findRoute

        if (generalFunc.retrieveValue(Utils.ENABLE_ROUTE_CALCULATION_MULTI_KEY).equalsIgnoreCase("Yes") && getIntent().hasExtra("isManualCalculation")) {
            findRoute("call");
            return;
        }

        // or only call fare estimate
        callFareEstimate(dest_details_Array);

    }

    private void retriverUserProfileJson() {
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        SYSTEM_PAYMENT_FLOW = generalFunc.getJsonValue("SYSTEM_PAYMENT_FLOW", userProfileJson);
        APP_PAYMENT_MODE = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson);
        APP_PAYMENT_METHOD = generalFunc.getJsonValue("APP_PAYMENT_METHOD", userProfileJson);

    }

    public void generateErrorView(String url, String type, boolean removePromo) {

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");


        if (errorViewArea.getVisibility() != View.VISIBLE) {
            errorViewArea.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(new ErrorView.RetryListener() {
            @Override
            public void onRetry() {

                if (type.equalsIgnoreCase("callFareDetailsRequest")) {
                    callFareDetailsRequest(removePromo);
                } else {
                    findRoute(url);
                }

            }
        });
    }

    private void callFareEstimate(ArrayList<Multi_Dest_Info_Detail_Data> dest_details_Array) {
        // generate all locations time & distance array to pass on type=getEstimateFareDetailsArr
        if (dest_details_Array.size() > 0) {

            for (int i = 0; i < dest_details_Array.size(); i++) {
                try {
                    JSONObject deliveriesObjall = new JSONObject();
                    deliveriesObjall.put("time", dest_details_Array.get(i).getTime());
                    deliveriesObjall.put("distance", dest_details_Array.get(i).getDistance());
                    ja.put(deliveriesObjall);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }

            }

        }

        if ((ja != null && ja.length() > 0) || (!distance.equalsIgnoreCase(""))) {

            callFareDetailsRequest(false);
        }
    }

    String waypoints = "";

    private void findRoute(String directionsUrl) {
        btn_type2.setEnabled(false);
        btn_schedule.setEnabled(false);
        PromoCodearea.setVisibility(View.GONE);
        String url = directionsUrl;
        HashMap<String, String> hashMap = new HashMap<>();
        ArrayList<String> data_waypoints = new ArrayList<>();
        if (Utils.checkText(url)) {
            // Origin of route
            String str_origin = "origin=" + list.get(0).getDestLat() + "," + list.get(0).getDestLong();


            String str_dest = "";

            wayPointslist = new ArrayList<>();      // List of Way Points
            destPointlist = new ArrayList<>();      // destination Points
            finalPointlist = new ArrayList<>();     // final Points list with time & distance & based on shortest location first algorithm
            dest_details_Array = new ArrayList<>(); // time & distance array of shortest location first

            templistofViews = new ArrayList<>();    // temp list

            for (int i = 0; i < list.size(); i++) {
                if (list.get(i).isDetailsAdded() == true) {
                    templistofViews.add(list.get(i));
                }
            }
            // Retrive middle & destination points

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

            String parameters = "";
            // Building the parameters to the web service
            if (templistofViews.size() > 1) {
                parameters = str_origin + "&" + str_dest + "&" + waypoints;

            } else {
                parameters = str_origin + "&" + str_dest;

            }
            hashMap.put("s_latitude", list.get(0).getDestLat() + "");
            hashMap.put("s_longitude", list.get(0).getDestLong() + "");
            hashMap.put("parameters", parameters);
            hashMap.put("waypoints", data_waypoints.toString());

        }

        progress_bar.setVisibility(View.VISIBLE);


        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(hashMap.get("s_latitude"), hashMap.get("s_longitude")).setDestLatitude(hashMap.get("d_latitude")).setDestLongitude(hashMap.get("d_longitude")).setWayPoints(MyApp.getInstance().GetStringArray(data_waypoints)).build(), AppService.Service.DIRECTION, new AppService.ServiceDelegate() {
            @Override
            public void onResult(HashMap<String, Object> data) {
                manageResult(data);

            }
        });
    }

    // Store Details
    private void storeDetails(ArrayList<Multi_Delivery_Data> array, boolean setFocusable) {

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


    private void setLables() {
        promocodeappliedHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
        titleTxt.setText(generalFunc.retrieveLangLBl("New Booking", "LBL_MULTI_NEW_BOOKING_TXT"));

        paymentTitle.setText(generalFunc.retrieveLangLBl("Responsible for payment", "LBL_MULTI_RESPONSIBLE_FOR_PAYMENT_TXT"));

        ((MTextView) findViewById(R.id.chargeTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_CHARGES_TXT"));

        if (mapData.containsKey("isDeliverNow") && mapData.get("isDeliverNow").equals("true")) {
            btn_type2.setText(generalFunc.retrieveLangLBl("Deliver Now", "LBL_BOOK_NOW"));
            btn_schedule.setText(generalFunc.retrieveLangLBl("Deliver Now", "LBL_BOOK_LATER"));
        } else {
            btn_type2.setText(generalFunc.retrieveLangLBl("Confirm Booking", "LBL_BOOK_NOW"));
            btn_schedule.setText(generalFunc.retrieveLangLBl("Confirm Booking", "LBL_BOOK_LATER"));
        }
    }

    private void init() {

        scheduleArea = (LinearLayout) findViewById(R.id.scheduleArea);

        if (!generalFunc.getJsonValue("DELIVERY_LATER_BOOKING_ENABLED", userProfileJson).equalsIgnoreCase("Yes")) {
            scheduleArea.setVisibility(View.GONE);
        }

        PromoCodearea = (LinearLayout) findViewById(R.id.PromoCodearea);
        progress_bar = (ProgressBar) findViewById(R.id.progress_bar);
        carTypeImg = (ImageView) findViewById(R.id.carTypeImg);
        paymentMethodRecyclerView = (RecyclerView) findViewById(R.id.paymentMethodRecyclerView);
        paymentTitle = (MTextView) findViewById(R.id.paymentTitle);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        fareDetailDisplayArea = (LinearLayout) findViewById(R.id.fareDetailDisplayArea);
        mainContentArea = (LinearLayout) findViewById(R.id.mainContentArea);
        paymentArea = (LinearLayout) findViewById(R.id.paymentArea);

        couponCodeArea = findViewById(R.id.couponCodeArea);
        promocodeArea = (LinearLayout) findViewById(R.id.promocodeArea);
        promocodeappliedHTxt = (MTextView) findViewById(R.id.promocodeappliedHTxt);
        promocodeappliedVTxt = (MTextView) findViewById(R.id.promocodeappliedVTxt);
        couponCodeImgView = (ImageView) findViewById(R.id.couponCodeImgView);
        couponCodeCloseImgView = (ImageView) findViewById(R.id.couponCodeCloseImgView);
        if (generalFunc.isRTLmode()) {
            couponCodeImgView.setRotationY(180);
        }
        applyCouponHTxt = (MTextView) findViewById(R.id.applyCouponHTxt);
        applyCouponVTxt = (MTextView) findViewById(R.id.applyCouponVTxt);
        applyCouponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_APPLY_PROMO_TXT"));
        applyCouponVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_COUPON"));
        appliedPromoHTxtView = (MTextView) findViewById(R.id.appliedPromoHTxtView);

        errorViewArea = (LinearLayout) findViewById(R.id.errorViewArea);
        errorView = (ErrorView) findViewById(R.id.errorView);

        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        btn_schedule = ((MaterialRippleLayout) findViewById(R.id.btn_schedule)).getChildView();
        btn_type2.setId(Utils.generateViewId());
        btn_type2.setOnClickListener(new setOnClickList());
        btn_schedule.setOnClickListener(new setOnClickList());

        couponCodeArea.setOnClickListener(new setOnClickList());

        selectedcar = mapData.get("SelectedCar");
        vVehicleType = mapData.get("vVehicleType");

        infoImage = findViewById(R.id.infoImage);
        infoImage.setOnClickListener(new setOnClickList());
    }

    @Override
    public void onTypeSelect(int position, int recipientPos) {

        selectedRecipientName = "";
        selectedPayTypeName = paymentTypeList.get(position).get(PAYMENT_DONE_BY);
        selectedPos = position;
        selectedRecipientPos = recipientPos;


        if ((selectedRecipientPos == -1 || !Utils.checkText(recipientList.get(recipientPos).get(RECIPIENT_NAME))) && (selectedPayTypeName != null && selectedPayTypeName.equalsIgnoreCase(Payment_Type_2))) {
            if (maxDestAddAllowedCount == 1 && recipientList != null && recipientList.size() > 0) {
                selectedRecipientName = recipientList.get(0).get(RECIPIENT_NAME);
            }
        } else {
            if (selectedPayTypeName != null && selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) && recipientList != null && recipientList.size() > 0) {
                selectedRecipientName = recipientList.get(recipientPos).get(RECIPIENT_NAME);
            }
        }
    }

    private void manageResult(HashMap<String, Object> data) {
        if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
            return;
        }

        String responseString = data.get("RESPONSE_DATA").toString();
        progress_bar.setVisibility(View.GONE);
        if (responseString != null && !responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {
            btn_type2.setEnabled(true);
            btn_schedule.setEnabled(true);
            if (responseString != null && !responseString.equals("")) {
                JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
                if (obj_routes != null && obj_routes.length() > 0) {

                    DataParser parser = new DataParser(getActContext(), list, wayPointslist, destPointlist, finalPointlist, dest_details_Array);
                    HashMap<String, String> routeMap = new HashMap<>();
                    routeMap.put("routes", obj_routes.toString());
                    try {
                        parser.getDistanceArray(new JSONObject(routeMap.toString()));
                        distance = parser.distance;
                        time = parser.time;
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }


                    if (finalPointlist.size() > 0) {
                        ArrayList<Multi_Delivery_Data> finalAllPointlist = new ArrayList<>();
                        finalAllPointlist = new ArrayList<>();
                        finalAllPointlist.add(list.get(0));
                        finalAllPointlist.addAll(finalPointlist);


                        list.clear();
                        list.addAll(finalAllPointlist);
                    }

                    storeDetails(list, true);

                    callFareEstimate(dest_details_Array);

                }


            } else {
                generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"),
                        generalFunc.retrieveLangLBl("", "LBL_GOOGLE_DIR_NO_ROUTE"));
            }
        } else {

            JSONObject obj_routes = generalFunc.getJsonObject(responseString);
            if (obj_routes != null) {
                distance = data.get("DISTANCE").toString();
                time = data.get("DURATION").toString();

                if (finalPointlist.size() > 0) {
                    ArrayList<Multi_Delivery_Data> finalAllPointlist = new ArrayList<>();
                    finalAllPointlist = new ArrayList<>();
                    finalAllPointlist.add(list.get(0));
                    finalAllPointlist.addAll(finalPointlist);
                    list.clear();
                    list.addAll(finalAllPointlist);
                }

                storeDetails(list, true);
                Log.d("dest_details_Array", "::" + dest_details_Array.size());

                callFareEstimate(dest_details_Array);

            } else {
                GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                alertBox.setContentMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                alertBox.setBtnClickList(btn_id -> {
                    alertBox.closeAlertBox();
                    backImgView.performClick();

                });
                alertBox.showAlertBox();
            }

        }

    }


    private class setOnClickList implements View.OnClickListener {

        @Override
        public void onClick(View view) {
            int i = view.getId();
            Utils.hideKeyboard(getActContext());
            if (i == R.id.backImgView) {
                onBackPressed();
            } else if (i == R.id.infoImage) {
                PreferenceDailog preferenceDailog = new PreferenceDailog(getActContext());
                preferenceDailog.showPreferenceDialog(((MTextView) findViewById(R.id.carTypeName)).getText().toString(), tInfoText, 0);
            } else if (i == btn_schedule.getId()) {

                if (selectedPos == -1) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Payment Method", "LBL_CHOOSE_PAYMENT_METHOD"));
                    return;
                }

                if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) && !Utils.checkText(selectedRecipientName.trim())) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Select", "LBL_SELECT_TXT") + " " + generalFunc.retrieveLangLBl("Recipient", "LBL_RECIPIENT"));
                    return;
                }

                chooseDateTime();


            } else if (i == btn_type2.getId()) {
                if (APP_PAYMENT_MODE.contains("Cash")) {
                    if (selectedPos == -1) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Payment Method", "LBL_CHOOSE_PAYMENT_METHOD"));
                        return;
                    }
                }

                if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) && !Utils.checkText(selectedRecipientName.trim())) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Select", "LBL_SELECT_TXT") + " " + generalFunc.retrieveLangLBl("Recipient", "LBL_RECIPIENT"));
                    return;
                }

                Bundle bn = new Bundle();
                if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) || selectedPayTypeName.equalsIgnoreCase(Payment_Type_3)) {
                    bn.putBoolean("isRecipient", true);
                }
                cabRquestType = Utils.CabReqType_Now;
                String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + Utils.eType_Multi_Delivery;
                if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) || selectedPayTypeName.equalsIgnoreCase(Payment_Type_3)) {
                    bn.putBoolean("isRecipient", true);
                    url = url + "&isPayBySender=No";
                } else {
                    url = url + "&isPayBySender=Yes";
                }


                url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                url = url + "&GeneralUserType=" + Utils.app_type;
                url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
                url = url + "&ePaymentOption=" + "Card";
                url = url + "&vPayMethod=" + "Instant";
                url = url + "&SYSTEM_TYPE=" + "APP";
                url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


                bn.putString("url", url);
                bn.putBoolean("handleResponse", true);
                bn.putBoolean("isBack", false);
                new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

            } else if (i == R.id.couponCodeArea) {
                Bundle bn = new Bundle();
                bn.putString("CouponCode", appliedPromoCode);
                bn.putString("eType", Utils.CabGeneralType_Deliver);
                bn.putString("vSourceLatitude", mapData.get("pickUpLocLattitude"));
                bn.putString("vSourceLongitude", mapData.get("pickUpLocLongitude"));
                bn.putString("vDestLatitude", "");
                bn.putString("vDestLongitude", "");
                bn.putString("tDeliveryLocations", String.valueOf(generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.MUTLI_DELIVERY_JSON_DETAILS_KEY))));
                bn.putString("eTakeAway", "No");
                new ActUtils(getActContext()).startActForResult(CouponActivity.class, bn, Utils.SELECT_COUPON_REQ_CODE);
            } else if (i == R.id.couponCodeCloseImgView) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CONFIRM_COUPON_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                    if (buttonId == 1) {
                        appliedPromoCode = "";
                        callFareDetailsRequest(true);
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_COUPON_REMOVE_SUCCESS"));
                    }
                });
            }
        }
    }

    public void setCancelable(Dialog dialogview, boolean cancelable) {
        final Dialog dialog = dialogview;
        View touchOutsideView = dialog.getWindow().getDecorView().findViewById(R.id.touch_outside);
        View bottomSheetView = dialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        if (cancelable) {
            touchOutsideView.setOnClickListener(v -> {
                if (dialog.isShowing()) {
                    dialog.cancel();
                }
            });
            BottomSheetBehavior.from(bottomSheetView).setHideable(true);
        } else {
            touchOutsideView.setOnClickListener(null);
            BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        }
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {
            MultiDeliveryThirdPhaseActivity.super.onBackPressed();
        }
    }


    // payement handling

    public void setCashSelection() {
        isCardValidated = false;

        paymentArea.setVisibility(View.VISIBLE);
    }

    public void setCardSelection(boolean b) {
        paymentArea.setVisibility(View.GONE);

    }


    public void checkPaymentCard() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "CheckCard");
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                String action = generalFunc.getJsonValue(Utils.action_str, responseString);
                if (action.equals("1")) {

                    isCardValidated = true;
                    setCardSelection(true);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }


    private void checkDetails() {


        isOutStandingDailogShow = false;

        // add responsible for payment based on selected payment method

        if (selectedPos != -1) {
            for (int j = 1; j < list.size(); j++) {

                list.get(j).setPaymentType(paymentTypeList.get(selectedPos).get(PAYMENT_DONE_BY));
                list.get(j).setePaymentByReceiver("No");
                for (int k = 0; k < list.get(j).getDt().size(); k++) {

                    list.get(j).getDt().get(k).setPaymentDoneBy("No");
                    list.get(j).getDt().get(k).setePaymentByReceiver("No");

                    if (selectedRecipientPos != -1 && selectedRecipientPos + 1 == j && paymentTypeList.get(selectedPos).get(PAYMENT_DONE_BY).equalsIgnoreCase(Payment_Type_2)) {
                        list.get(j).setePaymentByReceiver("Yes");
                        list.get(j).getDt().get(k).setPaymentDoneBy("Yes");
                        list.get(j).getDt().get(k).setePaymentByReceiver("Yes");

                    } else if (paymentTypeList.get(selectedPos).get(PAYMENT_DONE_BY).equalsIgnoreCase(Payment_Type_3)) {
                        list.get(j).setePaymentByReceiver("Yes");
                        list.get(j).getDt().get(k).setPaymentDoneBy("Yes");
                        list.get(j).getDt().get(k).setePaymentByReceiver("Yes");
                    } else if (selectedRecipientPos == -1 && maxDestAddAllowedCount == 1 && selectedPos != 0) {
                        list.get(j).setePaymentByReceiver("Yes");
                        list.get(j).getDt().get(k).setPaymentDoneBy("Yes");
                        list.get(j).getDt().get(k).setePaymentByReceiver("Yes");
                    }
                }
            }
        }


        Bundle bn = new Bundle();
        if (list.size() > 0) {
            Gson gson = new Gson();
            String json = gson.toJson(list);
            bn.putString("list", json);
        }

        bn.putString("isCashPayment", "" + isCashSelected);
        bn.putString("paymentMethod", selectedPayTypeName);
        bn.putString("isWallet", "" + iswalletSelect);
        bn.putString("promocode", appliedPromoCode);
        bn.putString("totalFare", currencySymbol + "" + totalFare);
        bn.putString("cabRquestType", cabRquestType);
        bn.putString("selectedTime", selectedDateTime);

        if (mapData.containsKey("time_multi")) {
            bn.putString("total_del_dist", "" + mapData.get("distance_multi"));
            bn.putString("total_del_time", "" + mapData.get("time_multi"));
        } else {
            bn.putString("total_del_dist", "" + distance);
            bn.putString("total_del_time", "" + time);
        }

        (new ActUtils(getActContext())).setOkResult(bn);
        finish();

    }

    public void checkCardConfig() {
        retriverUserProfileJson();

        if (APP_PAYMENT_METHOD.equalsIgnoreCase("Stripe")) {
            String vStripeCusId = generalFunc.getJsonValue("vStripeCusId", userProfileJson);
            if (vStripeCusId.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(false, false);
            }
        } else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Braintree")) {

            String vCreditCard = generalFunc.getJsonValue("vCreditCard", userProfileJson);
            String vBrainTreeCustEmail = generalFunc.getJsonValue("vBrainTreeCustEmail", userProfileJson);
            if (vCreditCard.equals("") && vBrainTreeCustEmail.equalsIgnoreCase("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(false, false);
            }
        } else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Paymaya") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Omise") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Adyen") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Xendit")) {
            String vCreditCard = generalFunc.getJsonValue("vCreditCard", userProfileJson);
            String vXenditToken = generalFunc.getJsonValue("vXenditToken", userProfileJson);

            if (vCreditCard.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(false, false);
            }
        }
        /*Flutterwave Payment GateWay*/
        else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Flutterwave")) {
            String vFlutterWaveToken = generalFunc.getJsonValue("vFlutterWaveToken", userProfileJson);
            if (vFlutterWaveToken.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(false, false);
            }
        }
    }

    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;

    public void outstandingDialog(boolean isReqNow, String data) {

        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);

        final MTextView outStandingTitle = (MTextView) dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = (MTextView) dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = (MTextView) dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = (MTextView) dialogView.findViewById(R.id.adjustTitleTxt);
        final LinearLayout cardArea = (LinearLayout) dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = (LinearLayout) dialogView.findViewById(R.id.adjustarea);

        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }

        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));
        outStandingValue.setText(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", data));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));
        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_OUT_AMT_DELIVERY_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", data);
        String outstanding_restriction_label_card = generalFunc.getJsonValue("outstanding_restriction_label_card", data);
        String outstanding_restriction_label_cash = generalFunc.getJsonValue("outstanding_restriction_label_cash", data);


        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", data);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", data);
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", data);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;


        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            /*if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            } else if (!isCashSelected && outstanding_restriction_label_card != null && !outstanding_restriction_label_card.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label_card);
                return;
            } else if (isCashSelected && outstanding_restriction_label_cash != null && !outstanding_restriction_label_cash.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label_cash);
                return;
            }*/
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            //String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }
        /*else if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {


            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                    }
                });
                generateAlert.setContentMessage("", outstanding_restriction_label);
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));

                generateAlert.showAlertBox();
                return;
            }
        }*/

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
            /*if (generalFunc.isRTLmode()) {
                (dialogView.findViewById(R.id.contactUsArrow)).setRotationY(180);
            }*/
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();


            userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

            callOutStandingPayAmout(isReqNow);


        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            } else if (!isCashSelected && outstanding_restriction_label_card != null && !outstanding_restriction_label_card.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label_card);
                return;
            } else if (isCashSelected && outstanding_restriction_label_cash != null && !outstanding_restriction_label_cash.isEmpty()) {
                outstanding_dialog.dismiss();
                generalFunc.showGeneralMessage("", outstanding_restriction_label_cash);
                return;
            }
            if (cardArea.getVisibility() == View.GONE) {
                outstanding_dialog.dismiss();
            }

            if (isReqNow) {
                isOutStandingDailogShow = true;
                //btn_type2.performClick();
                checkDetails();
            }
        });


        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        btn_type2.setOnClickListener(v -> {
            //ridenowclick = false;
            outstanding_dialog.dismiss();
        });

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.cardimagearrow)).setRotationY(180);
            (dialogView.findViewById(R.id.adjustimagearrow)).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    public void checkCardConfig(boolean isOutstanding, boolean isReqNow) {
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        if (APP_PAYMENT_METHOD.equalsIgnoreCase("Stripe")) {
            String vStripeCusId = generalFunc.getJsonValue("vStripeCusId", userProfileJson);
            if (vStripeCusId.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(isOutstanding, isReqNow);
            }
        } else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Braintree")) {

            String vCreditCard = generalFunc.getJsonValue("vCreditCard", userProfileJson);
            String vBrainTreeCustEmail = generalFunc.getJsonValue("vBrainTreeCustEmail", userProfileJson);
            if (vCreditCard.equals("") && vBrainTreeCustEmail.equalsIgnoreCase("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(isOutstanding, isReqNow);
            }

        } else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Paymaya") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Omise") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Adyen") ||
                APP_PAYMENT_METHOD.equalsIgnoreCase("Xendit")) {
            String vCreditCard = generalFunc.getJsonValue("vCreditCard", userProfileJson);
            if (vCreditCard.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(isOutstanding, isReqNow);
            }
        }
        /*Flutterwave Payment GateWay*/
        else if (APP_PAYMENT_METHOD.equalsIgnoreCase("Flutterwave")) {
            String vFlutterWaveToken = generalFunc.getJsonValue("vFlutterWaveToken", userProfileJson);
            if (vFlutterWaveToken.equals("")) {
                OpenCardPaymentAct(true);
            } else {
                showPaymentBox(isOutstanding, isReqNow);
            }
        }
    }

    public void showPaymentBox(boolean isOutstanding, boolean isReqNow) {
        androidx.appcompat.app.AlertDialog alertDialog;
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(getActContext());
        builder.setTitle("");
        builder.setCancelable(false);
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.input_box_view, null);
        builder.setView(dialogView);

        final MaterialEditText input = (MaterialEditText) dialogView.findViewById(R.id.editBox);
        final MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);

        Utils.removeInput(input);

        subTitleTxt.setVisibility(View.VISIBLE);
        subTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TITLE_PAYMENT_ALERT"));
        input.setText(generalFunc.getJsonValue("vCreditCard", userProfileJson));

        builder.setPositiveButton(generalFunc.retrieveLangLBl("Confirm", "LBL_BTN_TRIP_CANCEL_CONFIRM_TXT"), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
                if (isOutstanding) {
                    callOutStandingPayAmout(isReqNow);
                } else {
                    checkPaymentCard();
                }
            }
        });

        builder.setNeutralButton(generalFunc.retrieveLangLBl("Change", "LBL_CHANGE"), (dialog, which) -> {
            dialog.cancel();
            OpenCardPaymentAct(true);
        });
        builder.setNegativeButton(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), (dialog, which) -> dialog.cancel());


        alertDialog = builder.create();
        alertDialog.setCancelable(false);
        alertDialog.setCanceledOnTouchOutside(false);
        alertDialog.show();
    }


    public void checkOutStandingAmount(boolean isReqNow) {
        String fOutStandingAmount = "";
        if (Utils.checkText(responseStr)) {
            fOutStandingAmount = generalFunc.getJsonValue("fOutStandingAmount", responseStr);
        }
        boolean isDataAvail = GeneralFunctions.parseDoubleValue(0.0, fOutStandingAmount) > 0;

        if (!isOutStandingDailogShow && isDataAvail) {
            outstandingDialog(isReqNow, responseStr);
        } else {
            isOutStandingDailogShow = true;
            checkDetails();
        }
    }

    public void chooseDateTime() {
        BottomScheduleDialog bottomScheduleDialog = new BottomScheduleDialog(this, this);
        bottomScheduleDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_SCHEDULE")
                , generalFunc.retrieveLangLBl("", "LBL_SET"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT")
                , "", false, Calendar.getInstance());
    }

    @Override
    public void onScheduleSelection(String selDateTime, Date date, String iCabBookingId) {

        selectedDateTime = selDateTime;
        Bundle bn = new Bundle();
        bn.putString("isWallet", iswalletSelect ? "Yes" : "No");
        bn.putBoolean("isCash", isCashSelected);
        if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) || selectedPayTypeName.equalsIgnoreCase(Payment_Type_3)) {
            bn.putBoolean("isRecipient", true);
        }
        cabRquestType = Utils.CabReqType_Later;


        String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + Utils.eType_Multi_Delivery;

        if (selectedPayTypeName.equalsIgnoreCase(Payment_Type_2) || selectedPayTypeName.equalsIgnoreCase(Payment_Type_3)) {
            bn.putBoolean("isRecipient", true);
            url = url + "&isPayBySender=No";
        } else {
            url = url + "&isPayBySender=Yes";
        }

        url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
        url = url + "&GeneralUserType=" + Utils.app_type;
        url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
        url = url + "&ePaymentOption=" + "Card";
        url = url + "&vPayMethod=" + "Instant";
        url = url + "&SYSTEM_TYPE=" + "APP";
        url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


        bn.putString("url", url);
        bn.putBoolean("handleResponse", true);
        bn.putBoolean("isBack", false);
        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
    }


    public void callOutStandingPayAmout(boolean isReqNow) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "ChargePassengerOutstandingAmount");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {

                        isOutStandingDailogShow = true;
                        checkDetails();
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();
                } else {
                    if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LOW_WALLET_AMOUNT")) {
                        String walletMsg = "";
                        String low_balance_content_msg = generalFunc.getJsonValue("low_balance_content_msg", responseString);

                        if (low_balance_content_msg != null && !low_balance_content_msg.equalsIgnoreCase("")) {
                            walletMsg = low_balance_content_msg;
                        } else {
                            walletMsg = generalFunc.retrieveLangLBl("", "LBL_WALLET_LOW_AMOUNT_MSG_TXT");
                        }
                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_LOW_WALLET_BALANCE"), walletMsg, generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_ADD_MONEY"), button_Id -> {
                            if (button_Id == 1) {

                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            } else {

                            }
                        });
                    } else {

                        Bundle bn = new Bundle();
                        bn.putString("url", generalFunc.getJsonValue("OUTSTANDING_PAYMENT_URL", responseString));
                        bn.putBoolean("isApiCall", true);
                        new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
                    }
                }
            } else {
                if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_OUTSTANDING_AMOUT_ALREADY_PAID_TXT")) {
                    String message = generalFunc.getJsonValue(Utils.message_str_one, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

                    if (generalFunc.getJsonValue("fOutStandingAmount", userProfileJson) != null &&
                            GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("fOutStandingAmount", userProfileJson)) > 0) {
                        isOutStandingDailogShow = false;
                    } else {
                        isOutStandingDailogShow = true;
                    }

                    checkDetails();

                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });
        exeWebServer.setCancelAble(false);

    }

    public void OpenCardPaymentAct(boolean fromcabselection) {
        Bundle bn = new Bundle();
        bn.putBoolean("fromFareSummery", fromcabselection);
        new ActUtils(getActContext()).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
    }

    // Add Payment Layout
    private void buildPaymentAdapter() {


        if (APP_PAYMENT_MODE.contains("Cash")) {
            paymentArea.setVisibility(View.VISIBLE);
        } else {
            paymentArea.setVisibility(View.GONE);
        }


    }

    //get fare estimate
    private void callFareDetailsRequest(boolean removePromo) {
        btn_type2.setEnabled(false);
        btn_schedule.setEnabled(false);
        HashMap<String, Object> parameters = new HashMap<String, Object>();
        parameters.put("type", "getEstimateFareDetailsArr");
        parameters.put("iUserId", "" + generalFunc.getMemberId());
        parameters.put("distance", "" + distance);
        parameters.put("time", "" + time);
        parameters.put("SelectedCar", "" + selectedcar);

        if (removePromo) {
            parameters.put("PromoCode", "");
        } else {
            parameters.put("PromoCode", "" + appliedPromoCode);
        }
        parameters.put("tDeliveryLocations", String.valueOf(generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.MUTLI_DELIVERY_JSON_DETAILS_KEY))));
        parameters.put("ePaymentMode", (isCashSelected ? "cash" : "card"));
        parameters.put("details_arr", ja);
        parameters.put("totNoRecipient", "" + recipientList.size());
        parameters.put("userType", Utils.userType);
        parameters.put("eType", Utils.eType_Multi_Delivery);

        ApiHandler.execute(getActContext(), parameters, true, generalFunc, responseString -> {
            responseStr = responseString;
            if (responseString != null && !responseString.equals("")) {
                mainContentArea.setVisibility(View.VISIBLE);
                paymentTitle.setVisibility(View.VISIBLE);
                btn_type2.setEnabled(true);
                btn_schedule.setEnabled(true);
                PromoCodearea.setVisibility(View.VISIBLE);
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    if (removePromo) {
                        appliedPromoCode = "";
                        defaultPromoView();
                    }


                    (findViewById(R.id.fareDetailArea)).setVisibility(View.VISIBLE);
                    JSONArray FareDetailsArrNewObj = null;

                    FareDetailsArrNewObj = generalFunc.getJsonArray(Utils.message_str, responseString);

                    // Store Rounded Distance and Time
                    mapData.put("distance_multi", generalFunc.getJsonValue("distance_multi", responseString));
                    mapData.put("time_multi", generalFunc.getJsonValue("time_multi", responseString));

                    tInfoText = generalFunc.getJsonValue("tInfoText", responseString);

                    if (tInfoText != null && !tInfoText.equalsIgnoreCase("")) {
                        infoImage.setVisibility(View.VISIBLE);
                    } else {
                        infoImage.setVisibility(View.GONE);
                    }


                    currencySymbol = generalFunc.getJsonValue("vSymbol", responseString);
                    totalFare = generalFunc.parseDoubleValue(0.00, generalFunc.getJsonValue("total_fare_amount", responseString));
                    EachRecipeintAmount = generalFunc.parseDoubleValue(0.00, generalFunc.getJsonValue("EachRecipeintAmount", responseString));
                    if (EachRecipeintAmount < 0) {
                        individualFare = "" + BigDecimal.valueOf(EachRecipeintAmount).setScale(2, BigDecimal.ROUND_HALF_UP);
                    } else {
                        individualFare = "" + EachRecipeintAmount;
                    }
                    carTypeImg.setVisibility(View.VISIBLE);

                    String vehicleimage = generalFunc.getJsonValue("vehicleImage", responseString);
                    if (Utils.checkText(vehicleimage)) {
                        new LoadImage.builder(LoadImage.bind(vehicleimage), carTypeImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                    } /*else {
                        carTypeImg.setBackgroundColor(getResources().getColor(R.color.imageBg));
                    }*/


                    addFareDetailLayout(FareDetailsArrNewObj);
                } else {
                    btn_type2.setEnabled(false);
                    btn_schedule.setEnabled(false);
                    PromoCodearea.setVisibility(View.GONE);
                    (findViewById(R.id.fareDetailArea)).setVisibility(View.GONE);
                }

            } else {
                generateErrorView("", "callFareDetailsRequest", removePromo);
            }
        });


    }

    // Add Fare Detail Layout
    private void addFareDetailLayout(JSONArray jobjArray) {

        if (fareDetailDisplayArea.getChildCount() > 0) {
            fareDetailDisplayArea.removeAllViewsInLayout();
        }


        boolean islast;
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String data = jobject.names().getString(0);

                if (i == 0) {
                    ((MTextView) findViewById(R.id.carTypeName)).setText(jobject.get(data).toString());
                    ((MTextView) findViewById(R.id.pickupAddressTxtView)).setText(getIntent().getStringExtra("pickUpLocAddress"));
                } else {
                    islast = i == (jobjArray.length() - 1);
                    String rName = jobject.names().getString(0);
                    String rValue = jobject.get(rName).toString();
                    fareDetailDisplayArea.addView(MyUtils.addFareDetailRow(getActContext(), generalFunc, rName, rValue, islast));

                    if (islast) {
                        if (EachRecipeintAmount < 0) {
                            try {
                                double indiFare = (totalFare / recipientList.size());
                                individualFare = "" + BigDecimal.valueOf(indiFare).setScale(2,
                                        BigDecimal.ROUND_HALF_UP);

                            } catch (Exception e) {
                                Logger.e("Exception", "::" + e.getMessage());
                            }
                        } else {
                            individualFare = "" + EachRecipeintAmount;
                        }

                        buildPaymentAdapter();

                        payTypeAdapter = new MultiPaymentTypeRecyclerAdapter(MultiDeliveryThirdPhaseActivity.this, generalFunc, recipientList, paymentTypeList, getActContext(), maxDestAddAllowedCount);
                        payTypeAdapter.setOnTypeSelectListener(this);
                        paymentMethodRecyclerView.setLayoutManager(new LinearLayoutManager(this));
                        paymentMethodRecyclerView.setAdapter(payTypeAdapter);
                        payTypeAdapter.notifyDataSetChanged();
                    }
                }

            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }

    }

    private Context getActContext() {
        return MultiDeliveryThirdPhaseActivity.this;
    }

    private void setSelected(String selected) {

        if (selected.equalsIgnoreCase("3")) {
            ((MTextView) findViewById(R.id.tv1)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
//            ((LinearLayout) findViewById(R.id.tabArea1)).setBackground(getResources().getDrawable(R.drawable.tab_background));
            ((LinearLayout) findViewById(R.id.tabArea1)).setBackground(null);

            ((MTextView) findViewById(R.id.tv1)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_VEHICLE_TYPE"));
//            ((LinearLayout) findViewById(R.id.tabArea2)).setBackground(getResources().getDrawable(R.drawable.tab_background));
            ((MTextView) findViewById(R.id.tv2)).setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            ((MTextView) findViewById(R.id.tv2)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_ROUTE"));
            ((LinearLayout) findViewById(R.id.tabArea3)).setBackground(getResources().getDrawable(R.drawable.tab_background_selected_23));
            ((MTextView) findViewById(R.id.tv3)).setTextColor(getResources().getColor(R.color.appThemeColor_1));
            ((MTextView) findViewById(R.id.tv3)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_PRICE"));


        }

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (resultCode == RESULT_OK && requestCode == WEBVIEWPAYMENT) {
            String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);

            if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_DELIVERY", userProfileJson).equalsIgnoreCase("Yes")) {
                Bundle bn = new Bundle();

                isDeliverNow = true;
                bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
            } else {
                checkDetails();
            }

        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == RESULT_OK && data != null) {
            String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
            this.userProfileJson = userProfileJson;

        } else if (requestCode == Utils.SELECT_COUPON_REQ_CODE && resultCode == RESULT_OK && data != null) {
            String couponCode = data.getStringExtra("CouponCode");
            if (couponCode == null) {
                couponCode = "";
            }
            appliedPromoCode = couponCode;
            setPromoCode();
        } else if (requestCode == Utils.SELECT_ORGANIZATION_PAYMENT_CODE) {

            if (resultCode == RESULT_OK && data != null) {
                if (data.getSerializableExtra("data").equals("")) {


                    if (data.getBooleanExtra("isCash", false)) {
                        isCashSelected = true;
                    } else {
                        isCashSelected = false;
                    }
                    if (data.getBooleanExtra("isWallet", false)) {
                        iswalletSelect = true;

                    } else {
                        iswalletSelect = false;

                    }
                    String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);

                    if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_DELIVERY", userProfileJson).equalsIgnoreCase("Yes")) {
                        Bundle bn = new Bundle();
                        isDeliverNow = true;
                        bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                        bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                        new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                        ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
                    } else {
                        checkDetails();
                    }


                }

            }
        } else if (requestCode == 77 && resultCode == RESULT_OK && data != null) {
            if (isDeliverNow) {
                checkDetails();
            } else {
                chooseDateTime();
            }
        }
    }


    public void setPromoCode() {
        if (Utils.checkText(appliedPromoCode)) {
            appliedPromoView();
        } else {
            callFareDetailsRequest(true);
        }
    }

    public void defaultPromoView() {
        promocodeArea.setVisibility(View.GONE);
        appliedPromoHTxtView.setVisibility(View.GONE);

        couponCodeCloseImgView.setVisibility(View.GONE);
        couponCodeImgView.setVisibility(View.VISIBLE);
        applyCouponHTxt.setTextColor(Color.parseColor("#333333"));
        applyCouponHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_APPLY_PROMO_TXT"));
        promocodeappliedHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
    }

    public void appliedPromoView() {
        appliedPromoHTxtView.setVisibility(View.VISIBLE);
        applyCouponHTxt.setText(appliedPromoCode);
        applyCouponHTxt.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        couponCodeCloseImgView.setVisibility(View.VISIBLE);
        couponCodeImgView.setVisibility(View.GONE);
        couponCodeCloseImgView.setOnClickListener(new setOnClickList());
        appliedPromoHTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_APPLIED_COUPON_CODE"));
        callFareDetailsRequest(false);
    }
}
