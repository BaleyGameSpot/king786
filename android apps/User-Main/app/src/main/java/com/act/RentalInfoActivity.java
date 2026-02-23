package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityRentalInfoBinding;
import com.utils.Utils;

import java.util.HashMap;

public class RentalInfoActivity extends ParentActivity {
    HashMap<String, String> data;
    ActivityRentalInfoBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rental_info);

        data = (HashMap<String, String>) getIntent().getSerializableExtra("data");
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.noteMsgTxt.setBackgroundColor(getResources().getColor(R.color.cardView23ProBG));
        setLabel();
    }


    public void setLabel() {
        binding.baseFareHTxt.setText(getIntent().getBooleanExtra("isInterCity", false) ? generalFunc.retrieveLangLBl("", "LBL_INTERCITY_PACKAGE_FARE") : generalFunc.retrieveLangLBl("", "LBL_RENTAL_FARE_TXT"));

        binding.toolbarInclude.titleTxt.setText(getIntent().getBooleanExtra("isInterCity", false) ? generalFunc.retrieveLangLBl("", "LBL_FARE_DETAILS_AND_RULES_TXT") : generalFunc.retrieveLangLBl("", "LBL_RENT_A_TXT") + " " + data.get("vVehicleType"));

        binding.baseFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPrice")));

        if (generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
            binding.baseFareInfotxt.setText(generalFunc.retrieveLangLBl("", "LBL_INCLUDES") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_DISPLAY_KMS"));
        } else {
            binding.baseFareInfotxt.setText(generalFunc.retrieveLangLBl("", "LBL_INCLUDES") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT"));

        }

        binding.addKMFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPricePerKM")));


        if (generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
            binding.addKMFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_FARE"));
            binding.addKmFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) + " "
                    + generalFunc.retrieveLangLBl("", "LBL_DISPLAY_KMS"));
        } else {
            binding.addKMFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_MILES_FARE"));
            binding.addKmFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fKiloMeter")) +
                    " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT"));
        }
        binding.addTimeFareVTxt.setText(generalFunc.convertNumberWithRTL(data.get("fPricePerHour")));


        binding.addTimeFareInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AFTER_FIRST") + " " + generalFunc.convertNumberWithRTL(data.get("fHour")) + " "
                + generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT"));
        binding.addTimeFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADDITIONAL_RIDE_TIME_FARE"));

        binding.noteTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + ":");

        if (data.containsKey("page_desc") && Utils.checkText(data.get("page_desc"))) {
            binding.noteMsgTxt.loadData(data.get("page_desc"));
        } else {
            binding.noteParentView.setVisibility(View.GONE);
        }

    }

    public Context getActContext() {
        return RentalInfoActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == binding.toolbarInclude.backImgView.getId()) {
            onBackPressed();
        }
    }

}
