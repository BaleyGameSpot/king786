package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;

import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.ViewStopOverDetailRecyclerAdapter;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.ErrorView;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;


/**
 * Created by Admin on 03-11-2017.
 */

public class ViewStopOverDetailsActivity extends ParentActivity implements ViewStopOverDetailRecyclerAdapter.OnItemClickList {

    private ViewStopOverDetailRecyclerAdapter stopOverDetailRecyclerAdapter;
    private MTextView titleTxt;
    private ProgressBar loading;
    private ErrorView errorView;
    ArrayList<HashMap<String, String>> stopOverDetailList = new ArrayList<>();
    HashMap<String, String> data_trip;
    private ImageView backImgView;
    private RecyclerView stopOverPointsRecyclerView;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stop_over_details);
        if (getIntent().hasExtra("TRIP_DATA")) {
            this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        }


        init();
        setLables();
        setView();
        getTripDeliveryLocations();

    }


    private void setLables() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Trips", "LBL_TRIP_PLANNER_TXT"));
    }

    public Context getActContext() {
        return ViewStopOverDetailsActivity.this;
    }

    private void init() {
        loading = (ProgressBar) findViewById(R.id.loading);
        errorView = (ErrorView) findViewById(R.id.errorView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        stopOverPointsRecyclerView = (RecyclerView) findViewById(R.id.stopOverPointsRecyclerView);
        addToClickHandler(backImgView);
    }


    public void getTripDeliveryLocations() {
        if (errorView.getVisibility() == View.VISIBLE) {
            errorView.setVisibility(View.GONE);
        }

        if (loading.getVisibility() != View.VISIBLE) {
            loading.setVisibility(View.VISIBLE);
        }
        stopOverDetailList.clear();
        stopOverDetailRecyclerAdapter.notifyDataSetChanged();

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetStopOverPoint");
        parameters.put("iCabBookingId", "");

        String iCabBookingId = getIntent().hasExtra("iCabBookingId") ? getIntent().getStringExtra("iCabBookingId") : "";
        if (Utils.checkText(iCabBookingId)) {
            parameters.put("iCabBookingId", iCabBookingId);
        }

        String iCabRequestId = getIntent().hasExtra("iCabRequestId") ? getIntent().getStringExtra("iCabRequestId") : "";
        if (Utils.checkText(iCabRequestId)) {
            parameters.put("iCabRequestId", iCabRequestId);
        }
        parameters.put("iTripId", getIntent().getStringExtra("TripId"));
        parameters.put("userType", Utils.userType);
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        closeLoader();

                        if (generalFunc.checkDataAvail(Utils.action_str, responseStringObj)) {

                            JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, responseStringObj);
                            if (messageArray != null && messageArray.length() > 0) {

                                for (int i = 0; i < messageArray.length(); i++) {
                                    JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                                    HashMap<String, String> map = new HashMap<>();
                                    map.put("tDAddress", generalFunc.getJsonValueStr("tDAddress", obj_temp));
                                    map.put("tDestLatitude", generalFunc.getJsonValueStr("tDestLatitude", obj_temp));
                                    map.put("tDestLongitude", generalFunc.getJsonValueStr("tDestLongitude", obj_temp));
                                    map.put("LBL_STOPOVER_POINT", generalFunc.retrieveLangLBl("", "LBL_STOPOVER_POINT"));
                                    map.put("LBL_NEXT_STOP_OVER_POINT", generalFunc.retrieveLangLBl("", "LBL_NEXT_STOP_OVER_POINT"));
                                    map.put("eReached", generalFunc.getJsonValueStr("eReached", obj_temp));
                                    map.put("eCanceled", generalFunc.getJsonValueStr("eCanceled", obj_temp));
                                    map.put("iStopId", generalFunc.getJsonValueStr("iStopId", obj_temp));

                                    stopOverDetailList.add(map);

                                }
                                stopOverDetailRecyclerAdapter.notifyDataSetChanged();

                            }
                            stopOverDetailRecyclerAdapter.notifyDataSetChanged();

                        } else {
                            String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                            generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Error", "LBL_ERROR_TXT"),
                                    generalFunc.retrieveLangLBl("", msg_str));
                            stopOverDetailRecyclerAdapter.notifyDataSetChanged();

                        }
                    } else {
                        generateErrorView();
                        stopOverDetailRecyclerAdapter.notifyDataSetChanged();
                    }
                });

    }

    public void generateErrorView() {

        closeLoader();

        generalFunc.generateErrorView(errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");

        if (errorView.getVisibility() != View.VISIBLE) {
            errorView.setVisibility(View.VISIBLE);
        }
        errorView.setOnRetryListener(() -> getTripDeliveryLocations());
    }


    public void setView() {
        stopOverDetailRecyclerAdapter = new ViewStopOverDetailRecyclerAdapter(getActContext(), ViewStopOverDetailsActivity.this, stopOverDetailList, generalFunc);
        stopOverPointsRecyclerView.setAdapter(stopOverDetailRecyclerAdapter);
        stopOverDetailRecyclerAdapter.notifyDataSetChanged();
        stopOverDetailRecyclerAdapter.setOnItemClickList(this);
    }

    public void closeLoader() {
        if (loading.getVisibility() == View.VISIBLE) {
            loading.setVisibility(View.GONE);
        }
    }

    @Override
    public void onBackPressed() {

        if (backImgView.getVisibility() == View.VISIBLE) {
            super.onBackPressed();

        }
    }

    @Override
    public void onItemClick(String data, String type, int position) {

    }


    public void onClick(View view) {
        if (view.getId() == R.id.backImgView) {
            ViewStopOverDetailsActivity.super.onBackPressed();
        }
    }


    @Override
    protected void onDestroy() {
        super.onDestroy();
    }
}
