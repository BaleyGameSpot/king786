package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class OrganizationActivity extends ParentActivity {

    MTextView titleTxt;
    LinearLayout orgnizationArea;
    MTextView orgnizationNameTxt, headingTxt;
    String selectOrgnizationId = "";
    MButton btn_type2;
    ImageView imagearrow;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_organization);

        titleTxt = findViewById(R.id.titleTxt);
        ImageView backImgView = findViewById(R.id.backImgView);
        orgnizationArea = findViewById(R.id.orgnizationArea);
        orgnizationNameTxt = findViewById(R.id.orgnizationNameTxt);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        headingTxt = findViewById(R.id.headingTxt);
        imagearrow = findViewById(R.id.imagearrow);

        if (generalFunc.isRTLmode()) {
            imagearrow.setRotation(180);
            backImgView.setRotation(180);
        }

        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(btn_type2);
        addToClickHandler(orgnizationArea);
        addToClickHandler(backImgView);
        headingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_ORGANIZATION_LINK_TO"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PROFILE_SETUP"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        orgnizationNameTxt.setText("- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT") + " -");
    }

    public Context getActContext() {
        return OrganizationActivity.this;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {

            onBackPressed();
        } else if (view.getId() == R.id.orgnizationArea) {
            Bundle bn = new Bundle();
            bn.putString("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));
            new ActUtils(getActContext()).startActForResult(SelectOrganizationActivity.class, bn, Utils.SELECT_ORGANIZATION_CODE);
        }
        if (view.getId() == btn_type2.getId()) {

            if (!Utils.checkText(selectOrgnizationId)) {
                generalFunc.showMessage(orgnizationArea, generalFunc.retrieveLangLBl("", "LBL_SELECT_ORGANIZATION"));
                return;
            }

            Bundle bn = new Bundle();


            HashMap<String, String> data = new HashMap<>();
            data.put("email", getIntent().getStringExtra("email"));
            data.put("iUserProfileMasterId", getIntent().getStringExtra("iUserProfileMasterId"));
            data.put("iOrganizationId", selectOrgnizationId);
            data.put("vCompany", orgnizationNameTxt.getText().toString().trim());
            bn.putSerializable("data", data);
            new ActUtils(getActContext()).startActWithData(MyBusinessProfileActivity.class, bn);

        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK) {
            // selectOrgnization
            orgnizationNameTxt.setText(data.getStringExtra("vCompany"));
            selectOrgnizationId = data.getStringExtra("iOrganizationId");

        }
    }
}
