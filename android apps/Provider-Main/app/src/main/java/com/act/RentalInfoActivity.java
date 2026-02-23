package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.buddyverse.providers.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.WKWebView;

import java.util.HashMap;

public class RentalInfoActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backImgView;

    MTextView baseFareHTxt, baseFareVTxt, baseFareInfotxt, addKMFareHTxt,
            addKMFareVTxt, addKmFareInfoTxt, addTimeFareHTxt, addTimeFareVTxt, addTimeFareInfoTxt;

    String userProfileJson;
    HashMap<String, String> data;
    MTextView noteTitleTxt;
    WKWebView noteMsgTxt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_rental_info);

        data = (HashMap<String, String>) getIntent().getSerializableExtra("data");
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        titleTxt = findViewById(R.id.titleTxt);
        backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);

        baseFareHTxt = findViewById(R.id.baseFareHTxt);
        baseFareVTxt = findViewById(R.id.baseFareVTxt);
        baseFareInfotxt = findViewById(R.id.baseFareInfotxt);
        addKMFareHTxt = findViewById(R.id.addKMFareHTxt);
        addKMFareVTxt = findViewById(R.id.addKMFareVTxt);
        addKmFareInfoTxt = findViewById(R.id.addKmFareInfoTxt);
        addTimeFareHTxt = findViewById(R.id.addTimeFareHTxt);
        addTimeFareVTxt = findViewById(R.id.addTimeFareVTxt);
        addTimeFareInfoTxt = findViewById(R.id.addTimeFareInfoTxt);
        noteTitleTxt = findViewById(R.id.noteTitleTxt);
        noteMsgTxt = findViewById(R.id.noteMsgTxt);

        setLabel();
    }


    public void setLabel() {
        baseFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENTAL_FARE_TXT"));
        baseFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPrice")));

        if (generalFunc.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
            baseFareInfotxt.setText(generalFunc.retrieveLangLBl("", "LBL_INCLUDES") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_DISPLAY_KMS"));
        } else {
            baseFareInfotxt.setText(generalFunc.retrieveLangLBl("", "LBL_INCLUDES") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT"));

        }

        addKMFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPricePerKM")));


        if (generalFunc.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
            addKMFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_FARE"));
            addKmFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_DISPLAY_KMS"));
        } else {
            addKMFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_MILES_FARE"));
            addKmFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) +
                    " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT"));

        }

        addTimeFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPricePerHour")));


        addTimeFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT"));
        addTimeFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_RIDE_TIME_FARE"));

        noteTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + ":");

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_A_TXT") + " " + data.get("vVehicleType"));
        noteMsgTxt.loadData(data.get("page_desc"));

    }

    public Context getActContext() {
        return RentalInfoActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == backImgView.getId()) {
            onBackPressed();
        }
    }

}
