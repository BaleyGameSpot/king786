package com.act;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityFareBreakDownBinding;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Objects;

public class FareBreakDownActivity extends ParentActivity {

    private ActivityFareBreakDownBinding binding;
    private String selectedCar = "", distance = "", time = "";
    private boolean isSkip, isFixFare;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_fare_break_down);

        initViews();

        if (getIntent().getBooleanExtra("isData", false)) {
            availableData();
            return;
        }

        selectedCar = getIntent().getStringExtra("SelectedCar");
        distance = getIntent().getStringExtra("distance");
        time = getIntent().getStringExtra("time");
        isFixFare = getIntent().getBooleanExtra("isFixFare", false);
        isSkip = getIntent().getBooleanExtra("isSkip", false);

        callBreakdownRequest();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FARE_BREAKDOWN_TXT"));

        binding.carTypeTitle.setText(getIntent().getStringExtra("vVehicleType"));
    }

    private void availableData() {
        JSONArray fareArray = generalFunc.getJsonArray(getIntent().getStringExtra("fareData"));
        if (fareArray != null) {
            addFareDetailLayout(fareArray);
            binding.carTypeTitle.setText(getIntent().getStringExtra("PriceBreakdownTitle"));
        } else {
            generalFunc.showError(true);
        }
    }

    private void callBreakdownRequest() {
        binding.mainArea.setVisibility(View.GONE);
        binding.progressBar.setVisibility(View.VISIBLE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getEstimateFareDetailsArr");
        parameters.put("iUserId", generalFunc.getMemberId());

        parameters.put("distance", distance);
        parameters.put("time", time);
        parameters.put("SelectedCar", selectedCar);
        if (!isSkip) {
            parameters.put("isDestinationAdded", "Yes");
            String destLat = getIntent().getStringExtra("destLat");
            if (destLat != null && !destLat.equalsIgnoreCase("")) {
                parameters.put("DestLatitude", destLat);
                parameters.put("DestLongitude", getIntent().getStringExtra("destLong"));
            }
            String picupLat = getIntent().getStringExtra("picupLat");
            if (picupLat != null && !picupLat.equalsIgnoreCase("")) {
                parameters.put("StartLatitude", picupLat);
                parameters.put("EndLongitude", getIntent().getStringExtra("pickUpLong"));
            }
            if (getIntent().hasExtra("eFly")) {
                parameters.put("iFromStationId", getIntent().getStringExtra("iFromStationId"));
                parameters.put("iToStationId", getIntent().getStringExtra("iToStationId"));
                parameters.put("eFly", getIntent().getStringExtra("eFly"));
            }
        } else {
            parameters.put("isDestinationAdded", "No");
        }

        ApiHandler.execute(this, parameters, false, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            binding.mainArea.setVisibility(View.VISIBLE);
            binding.progressBar.setVisibility(View.GONE);

            if (responseObj != null && !responseObj.toString().equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    if (generalFunc.getJsonValueStr("VEHICLE_TYPE_SHOW_METHOD", obj_userProfile) != null &&
                            generalFunc.getJsonValueStr("VEHICLE_TYPE_SHOW_METHOD", obj_userProfile).equalsIgnoreCase("Vertical")) {
                        if (isFixFare || getIntent().hasExtra("eFly")) {
                            binding.fareBreakdownNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENERAL_NOTE_FLAT_FARE_EST"));
                        } else {
                            binding.fareBreakdownNoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENERAL_NOTE_FARE_EST"));
                        }
                    } else {
                        binding.fareBreakdownNoteTxt.setText(generalFunc.getJsonValueStr("tInfoText", responseObj));
                    }
                    addFareDetailLayout(generalFunc.getJsonArray(Utils.message_str, responseObj));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void addFareDetailLayout(JSONArray jobjArray) {
        if (binding.fareDetailDisplayArea.getChildCount() > 0) {
            binding.fareDetailDisplayArea.removeAllViewsInLayout();
        }
        for (int i = 0; i < jobjArray.length(); i++) {
            JSONObject jobject = generalFunc.getJsonObject(jobjArray, i);
            try {
                String rName = Objects.requireNonNull(jobject.names()).getString(0);
                String rValue = jobject.get(rName).toString();
                binding.fareDetailDisplayArea.addView(MyUtils.addFareDetailRow(this, generalFunc, rName, rValue, (jobjArray.length() - 1) == i));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            FareBreakDownActivity.super.onBackPressed();
        }
    }
}