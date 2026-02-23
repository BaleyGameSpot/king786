package com.act;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.OngoingTripAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 21-02-2017.
 */
public class OnGoingTripsActivity extends ParentActivity implements OngoingTripAdapter.OnItemClickListener {

    OngoingTripAdapter ongoingTripAdapter;
    String iTripId = "";
    String driverStatus = "";
    private MTextView titleTxt, noOngoingTripsTxt;
    private RecyclerView onGoingTripsListRecyclerView;
    private ImageView backImgView;
    private ProgressBar loading_ongoing_trips;
    private ErrorView errorView;

    private ArrayList<HashMap<String, String>> list = new ArrayList<>();
    private boolean fromNoti;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ongoingtrips_layout);
        iTripId = getIntent().getStringExtra("iTripId");
        fromNoti = getIntent().getBooleanExtra("fromNoti", false);
        init();
        setLables();
    }


    private void init() {
        noOngoingTripsTxt = (MTextView) findViewById(R.id.noOngoingTripsTxt);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        onGoingTripsListRecyclerView = (RecyclerView) findViewById(R.id.onGoingTripsListRecyclerView);
        loading_ongoing_trips = (ProgressBar) findViewById(R.id.loading_ongoing_trips);

        errorView = (ErrorView) findViewById(R.id.errorView);


        String Last_trip_data = generalFunc.getJsonValueStr("TripDetails", obj_userProfile);
        iTripId = generalFunc.getJsonValue("iTripId", Last_trip_data);
        addToClickHandler(backImgView);
    }

    @Override
    protected void onResume() {
        super.onResume();
        getOngoingUserTrips(fromNoti ? iTripId : "");
    }


    private void setViews() {

        ongoingTripAdapter = new OngoingTripAdapter(getActContext(), list, generalFunc, false);
        onGoingTripsListRecyclerView.setAdapter(ongoingTripAdapter);
        ongoingTripAdapter.setOnItemClickListener(this);
        ongoingTripAdapter.notifyDataSetChanged();

        if (list.size() > 0) {
            onGoingTripsListRecyclerView.setVisibility(View.VISIBLE);
            noOngoingTripsTxt.setVisibility(View.GONE);
        } else {
            onGoingTripsListRecyclerView.setVisibility(View.GONE);
            noOngoingTripsTxt.setVisibility(View.VISIBLE);
        }
    }


    public void getOngoingUserTrips(String iTripId) {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        loading_ongoing_trips.setVisibility(View.VISIBLE);

        if (ongoingTripAdapter != null && list.size() > 0) {
            list.clear();
            ongoingTripAdapter.notifyDataSetChanged();
        }

        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getOngoingUserTrips");
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                list = new ArrayList<>();
                closeLoader();

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONArray message = generalFunc.getJsonArray(Utils.message_str, responseString);

                    String senderName = generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile);
                    if (message != null && message.length() > 0) {
                        for (int i = 0; i < message.length(); i++) {
                            JSONObject jobject1 = generalFunc.getJsonObject(message, i);
                            HashMap<String, String> map = new HashMap<>();
                            map.put("iDriverId", generalFunc.getJsonValueStr("iDriverId", jobject1));
                            map.put("driverImage", generalFunc.getJsonValueStr("driverImage", jobject1));
                            map.put("driverName", generalFunc.getJsonValueStr("driverName", jobject1));
                            map.put("vCode", generalFunc.getJsonValueStr("vCode", jobject1));
                            map.put("driverMobile", generalFunc.getJsonValueStr("driverMobile", jobject1));
                            map.put("driverStatus", generalFunc.getJsonValueStr("driverStatus", jobject1));
                            map.put("driverRating", generalFunc.getJsonValueStr("driverRating", jobject1));
                            map.put("vRideNo", generalFunc.getJsonValueStr("vRideNo", jobject1));
                            map.put("tSaddress", generalFunc.getJsonValueStr("tSaddress", jobject1));
                            map.put("iTripId", generalFunc.getJsonValueStr("iTripId", jobject1));
                            map.put("senderName", senderName);
                            map.put("Booking_LBL", generalFunc.retrieveLangLBl("Booking No", "LBL_BOOKING"));
                            map.put("dDateOrig", generalFunc.getJsonValueStr("dDateOrig", jobject1));
                            map.put("SelectedTypeName", generalFunc.getJsonValueStr("SelectedTypeName", jobject1));
                            driverStatus = generalFunc.getJsonValueStr("driverStatus", jobject1);
                            map.put("driverLatitude", generalFunc.getJsonValueStr("driverLatitude", jobject1));
                            map.put("driverLongitude", generalFunc.getJsonValueStr("driverLongitude", jobject1));
                            map.put("eServiceLocation", generalFunc.getJsonValueStr("eServiceLocation", jobject1));

                            map.put("tStratLat", generalFunc.getJsonValueStr("driverLatitude", jobject1));
                            map.put("tStartLong", generalFunc.getJsonValueStr("driverLongitude", jobject1));
                            map.put("eFareType", generalFunc.getJsonValueStr("eFareType", jobject1));
                            map.put("moreServices", generalFunc.getJsonValueStr("moreServices", jobject1));

                            String vChargesDetailData = generalFunc.getJsonValueStr("vChargesDetailData", jobject1);
                            JSONObject vChargesDetailDataObj = generalFunc.getJsonObject(vChargesDetailData);
                            map.put("vChargesDetailDataAvailable", vChargesDetailDataObj != null && vChargesDetailDataObj.length() > 0 ? "Yes" : "No");
                            map.put("fMaterialFee", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("fMaterialFee", vChargesDetailDataObj) : "");
                            map.put("fMiscFee", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("fMiscFee", vChargesDetailDataObj) : "");
                            map.put("fDriverDiscount", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("fDriverDiscount", vChargesDetailDataObj) : "");
                            map.put("vConfirmationCode", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("vConfirmationCode", vChargesDetailDataObj) : "");
                            map.put("serviceCost", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("serviceCost", vChargesDetailDataObj) : "");
                            map.put("totalAmount", vChargesDetailDataObj != null ? generalFunc.getJsonValueStr("totalAmount", vChargesDetailDataObj) : "");

                            map.put("eApproveRequestSentByDriver", generalFunc.getJsonValueStr("eApproveRequestSentByDriver", jobject1));
                            map.put("eApproved", generalFunc.getJsonValueStr("eApproved", jobject1));
                            map.put("verifyChargesLBL", generalFunc.retrieveLangLBl("Verify Charges", "LBL_VERIFY_ADDITIONAL_CHARGES_TXT"));

                            map.put("vServiceTitle", generalFunc.getJsonValueStr("vServiceTitle", jobject1));
                            String eType = generalFunc.getJsonValueStr("eType", jobject1);
                            map.put("eType", eType);
                            map.put("liveTrackLBL", generalFunc.retrieveLangLBl("", "LBL_MULTI_LIVE_TRACK_TEXT"));
                            map.put("viewDetailLBL", generalFunc.retrieveLangLBl("View Details", "LBL_VIEW_DETAILS"));

                            if (ServiceModule.Delivery || ServiceModule.DeliverAll) {
                                if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                                    map.put("eTypeName", generalFunc.retrieveLangLBl("Delivery", "LBL_DELIVERY"));
                                } else {
                                    map.put("eTypeName", generalFunc.retrieveLangLBl("", "LBL_SERVICES"));
                                }
                            } else {
                                map.put("eTypeName", "");
                            }

                            list.add(map);

                            if (Utils.checkText(iTripId) && iTripId.equalsIgnoreCase(generalFunc.getJsonValueStr("iTripId", jobject1))) {
                                RedirectToOngoingTripDetail(i);
                                break;
                            }
                        }
                    } else {
                        noOngoingTripsTxt.setVisibility(View.VISIBLE);
                        noOngoingTripsTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                } else {
                    if (!GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                        noOngoingTripsTxt.setVisibility(View.VISIBLE);
                        noOngoingTripsTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    } else {
                        generateErrorView();
                    }
                }
                setViews();

            }
        });

    }

    public void closeLoader() {
        if (loading_ongoing_trips.getVisibility() == View.VISIBLE) {
            loading_ongoing_trips.setVisibility(View.GONE);
        }
    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
            noOngoingTripsTxt.setVisibility(View.GONE);
        }
        errorView.setOnRetryListener(() -> getOngoingUserTrips(fromNoti ? iTripId : ""));
    }


    public void RedirectToOngoingTripDetail(int position) {

        if (fromNoti) {
            fromNoti = false;
        }
        Bundle bn = new Bundle();
        bn.putSerializable("TripDetail", list.get(position));
        bn.putSerializable("driverStatus", driverStatus);
        bn.putString("showChargesScreen", "Yes");
        bn.putString("iTripId", list.get(position).get("iTripId"));
        bn.putString("eType", list.get(position).get("eType"));

        new ActUtils(getActContext()).startActForResult(OnGoingTripDetailsActivity.class, bn, Utils.LIVE_TRACK_REQUEST_CODE);

    }

    @Override
    public void onItemClickList(String type, int position) {
        Utils.hideKeyboard(getActContext());
        if (type.equalsIgnoreCase("View Detail")) {
            Bundle bn = new Bundle();
            bn.putSerializable("TripDetail", list.get(position));
            bn.putSerializable("driverStatus", driverStatus);
            bn.putString("iTripId", list.get(position).get("iTripId"));
            bn.putString("eType", list.get(position).get("eType"));
            new ActUtils(getActContext()).startActForResult(OnGoingTripDetailsActivity.class, bn, Utils.LIVE_TRACK_REQUEST_CODE);
        } else if (type.equalsIgnoreCase("Verify Charge")) {
            Bundle bn = new Bundle();
            bn.putSerializable("TripDetail", list.get(position));
            bn.putString("iTripId", list.get(position).get("iTripId"));
            bn.putString("iDriverId", list.get(position).get("iDriverId"));


            bn.putString("fMaterialFee", list.get(position).get("fMaterialFee"));
            bn.putString("fMiscFee", list.get(position).get("fMiscFee"));
            bn.putString("fDriverDiscount", list.get(position).get("fDriverDiscount"));
            bn.putString("vConfirmationCode", list.get(position).get("vConfirmationCode"));
            bn.putString("eApproveRequestSentByDriver", list.get(position).get("eApproveRequestSentByDriver"));
            bn.putString("serviceCost", list.get(position).get("serviceCost"));
            bn.putString("totalAmount", list.get(position).get("totalAmount"));


            new ActUtils(getActContext()).startActWithData(AdditionalChargeActivity.class, bn);
        } else if (type.equalsIgnoreCase("Live Track")) {
            MyApp.getInstance().restartWithGetDataApp(list.get(position).get("iTripId"));
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.LIVE_TRACK_REQUEST_CODE && resultCode == RESULT_OK && data != null) {
            MyApp.getInstance().restartWithGetDataApp();
            onBackPressed();
        } else if (requestCode == Utils.MULTIDELIVERY_HISTORY_RATE_CODE) {
            onBackPressed();
        }
    }

    private void setLables() {


        titleTxt.setText(generalFunc.retrieveLangLBl("My Ongoing Trips", "LBL_MY_ON_GOING_JOB"));
        noOngoingTripsTxt.setText(generalFunc.retrieveLangLBl("No Ongoing Trips Available.", "LBL_NO_ONGOING_TRIPS_AVAIL"));
    }

    private Activity getActContext() {
        return OnGoingTripsActivity.this;
    }

    @Override
    public void onBackPressed() {

        //bug_004 start
        if (ServiceModule.RideDeliveryUbexProduct) {
            if ((generalFunc.getJsonValueStr("vTripStatus", obj_userProfile).equalsIgnoreCase("Active") ||
                    generalFunc.getJsonValueStr("vTripStatus", obj_userProfile).equalsIgnoreCase("On Going Trip")) && !generalFunc.getJsonValueStr("eType", obj_userProfile).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {

                if (getIntent().hasExtra("isTripRunning")) {
                    MyApp.getInstance().restartWithGetDataApp();
                }

            } else if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                MyApp.getInstance().restartWithGetDataApp();
            } else if (getIntent().getBooleanExtra("isRestart", false)) {

                Bundle bn = new Bundle();
                new ActUtils(getActContext()).startActWithData(UberXActivity.class, bn);
                finishAffinity();
            } else {
                OnGoingTripsActivity.super.onBackPressed();
            }
        } else if (ServiceModule.RideDeliveryProduct || ServiceModule.isDeliveronly()) {
            if ((generalFunc.getJsonValueStr("vTripStatus", obj_userProfile).equalsIgnoreCase("Active") ||
                    generalFunc.getJsonValueStr("vTripStatus", obj_userProfile).equalsIgnoreCase("On Going Trip")) && !generalFunc.getJsonValueStr("eType", obj_userProfile).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {

                if (getIntent().hasExtra("isTripRunning")) {
                    MyApp.getInstance().restartWithGetDataApp();
                }

            } else if (generalFunc.prefHasKey(Utils.isMultiTrackRunning) && generalFunc.retrieveValue(Utils.isMultiTrackRunning).equalsIgnoreCase("Yes")) {
                MyApp.getInstance().restartWithGetDataApp();
            } else if (getIntent().getBooleanExtra("isRestart", false)) {
                Bundle bn = new Bundle();
                if (generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {
                    if (generalFunc.getJsonValue("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equals("Yes")
                            || generalFunc.getJsonValue("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equals("Yes")) {
                        new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);
                    } else {
                        new ActUtils(getActContext()).startActWithData(RideDeliveryActivity.class, bn);
                    }
                } else {


                    bn.putString("iVehicleCategoryId", generalFunc.getJsonValueStr("DELIVERY_CATEGORY_ID", obj_userProfile));
                    bn.putString("vCategory", generalFunc.getJsonValueStr("DELIVERY_CATEGORY_NAME", obj_userProfile));
                    new ActUtils(getActContext()).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                }
                finishAffinity();
            } else {

                OnGoingTripsActivity.super.onBackPressed();
            }
        } else {
            OnGoingTripsActivity.super.onBackPressed();
        }
        //bug_004 end

    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {

            onBackPressed();

        }
    }


}
