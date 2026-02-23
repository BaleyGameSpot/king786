package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONObject;

public class UploadDocTypeWiseActivity extends ParentActivity {

    private JSONObject userProfileJsonObj;
    LinearLayout uberxArea, rideArea;

    MTextView ridetitleTxt, deliverytitleTxt, uberxtitleTxt;
    MTextView titleTxt;
    ImageView backImgView;

    public static int ADDVEHICLE = 1;

    int totalVehicles = 0;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_upload_doc_type_wise);

        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

        initView();
    }

    public void initView() {
        uberxArea = (LinearLayout) findViewById(R.id.uberxArea);
        rideArea = (LinearLayout) findViewById(R.id.rideArea);
        ridetitleTxt = (MTextView) findViewById(R.id.ridetitleTxt);
        deliverytitleTxt = (MTextView) findViewById(R.id.deliverytitleTxt);
        uberxtitleTxt = (MTextView) findViewById(R.id.uberxtitleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        addToClickHandler(uberxArea);
        addToClickHandler(rideArea);
        addToClickHandler(backImgView);

        LinearLayout biddingArea = (LinearLayout) findViewById(R.id.biddingArea);
        biddingArea.setVisibility(View.GONE);
        if (ServiceModule.ServiceBid) {

            biddingArea.setVisibility(View.VISIBLE);
            biddingArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(BiddingCategoryActivity.class));

            MTextView biddingTitleTxt = (MTextView) findViewById(R.id.biddingTitleTxt);
            biddingTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_BIDDING_SERVICES"));

            if (generalFunc.isRTLmode()) {
                ((ImageView) findViewById(R.id.biddingImageArrow)).setRotationY(180);
            }
        }


        totalVehicles = getIntent().getIntExtra("totalVehicles", 0);

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TYPE"));

        if (getIntent().getStringExtra("selView").equalsIgnoreCase("doc")) {
            ridetitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC"));
            deliverytitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC_DELIVERY"));
            uberxtitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_DOC_UFX"));
        } else {
            ridetitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_VEHICLES"));
            uberxtitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_OTHER_SERVICES"));
        }

        if (generalFunc.isRTLmode()) {
            ((ImageView) findViewById(R.id.imagearrow)).setRotationY(180);
            ((ImageView) findViewById(R.id.delimagearrow)).setRotationY(180);
            ((ImageView) findViewById(R.id.uberximagearrow)).setRotationY(180);

            backImgView.setRotation(180);

        }

        rideArea.setVisibility(View.GONE);
        if (generalFunc.getJsonValueStr("eShowVehicles", userProfileJsonObj) != null &&
                generalFunc.getJsonValueStr("eShowVehicles", userProfileJsonObj).equalsIgnoreCase("Yes")) {
            rideArea.setVisibility(View.VISIBLE);
        }

        uberxArea.setVisibility(View.GONE);
        if (app_type.equalsIgnoreCase(Utils.CabGeneralType_UberX) || ((app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX) && generalFunc.getJsonValueStr("UFX_SERVICE_AVAILABLE", obj_userProfile).equalsIgnoreCase("Yes")))) {
            uberxArea.setVisibility(View.VISIBLE);
        }

    }

    public Context getActContext() {
        return UploadDocTypeWiseActivity.this;
    }


    public void onClick(View view) {

        Bundle bn = new Bundle();
        bn.putString("PAGE_TYPE", "Driver");
        bn.putString("iDriverVehicleId", "");
        bn.putString("doc_file", "");
        bn.putString("iDriverVehicleId", "");
        Utils.hideKeyboard(UploadDocTypeWiseActivity.this);
        int id = view.getId();
        if (id == R.id.backImgView) {
            UploadDocTypeWiseActivity.super.onBackPressed();
        } else if (id == R.id.rideArea) {
            if (getIntent().getStringExtra("selView").equalsIgnoreCase("doc")) {
                new ActUtils(getActContext()).startActWithData(ListOfDocumentActivity.class, bn);
            } else {
                if (totalVehicles > 0) {
                    new ActUtils(getActContext()).startActWithData(ManageVehiclesActivity.class, bn);
                } else {
                    new ActUtils(getActContext()).startActForResult(AddVehicleActivity24.class, bn, ADDVEHICLE);
                }
            }
        } else if (id == R.id.uberxArea) {
            if (getIntent().getStringExtra("selView").equalsIgnoreCase("doc")) {
                bn.putString("seltype", Utils.CabGeneralType_UberX);
                new ActUtils(getActContext()).startActWithData(ListOfDocumentActivity.class, bn);
            } else {
                bn.putString("UBERX_PARENT_CAT_ID", getIntent().getStringExtra("UBERX_PARENT_CAT_ID"));
                new ActUtils(getActContext()).startActWithData(UfxCategoryActivity.class, bn);
            }
        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == RESULT_OK) {

            if (data.getStringExtra("iDriverVehicleId") != null && !data.getStringExtra("iDriverVehicleId").equalsIgnoreCase
                    ("")) {
                totalVehicles = 1;
                if (app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery)) {
                    if (totalVehicles > 0) {
                        ridetitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_VEHICLES_RIDE"));
                        deliverytitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_VEHICLES_DELIVERY"));
                    } else {
                        ridetitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLES_RIDE"));
                        deliverytitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_VEHICLES_DELIVERY"));
                    }
                } else {
                    ridetitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE_VEHICLES"));
                    deliverytitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_VEHICLES_DELIVERY"));
                    uberxtitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANANGE_OTHER_SERVICES"));
                }
            }
        }
    }
}
