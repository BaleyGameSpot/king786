package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityMoreServiceInfoBinding;
import com.buddyverse.main.databinding.MoreserviceitemBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class MoreServiceInfoActivity extends ParentActivity {

    private ActivityMoreServiceInfoBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_more_service_info);

        initViews();
        getSpecialData();
    }

    private void initViews() {
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
        addToClickHandler(binding.toolbarInclude.backImgView);
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TITLE_REQUESTED_SERVICES"));
    }

    @SuppressLint("SetTextI18n")
    private void getSpecialData() {

        binding.loadingBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getSpecialInstructionData");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        String iCabRequestId = getIntent().getStringExtra("iCabRequestId");
        String iTripId = getIntent().getStringExtra("iTripId");
        if (iTripId != null && !iTripId.equalsIgnoreCase("")) {
            parameters.put("iTripId", iTripId);
        } else if (iCabRequestId != null && !iCabRequestId.equalsIgnoreCase("")) {
            parameters.put("iCabRequestId", iCabRequestId);
        } else {
            parameters.put("iCabBookingId", getIntent().getStringExtra("iCabBookingId"));
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            binding.loadingBar.setVisibility(View.GONE);

            if (responseObj != null && !responseObj.toString().equalsIgnoreCase("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    if (binding.itemContainer.getChildCount() > 0) {
                        binding.itemContainer.removeAllViewsInLayout();
                    }

                    JSONArray itemArray = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    if (itemArray != null) {
                        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);

                        String LBL_SPECIAL_INSTRUCTION_TXT = generalFunc.retrieveLangLBl("", "LBL_SPECIAL_INSTRUCTION_TXT");
                        String LBL_NO_SPECIAL_INSTRUCTION = generalFunc.retrieveLangLBl("", "LBL_NO_SPECIAL_INSTRUCTION");

                        for (int i = 0; i < itemArray.length(); i++) {

                            JSONObject data = generalFunc.getJsonObject(itemArray, i);
                            @NonNull MoreserviceitemBinding iBinding = MoreserviceitemBinding.inflate(inflater, binding.itemContainer, false);

                            //
                            iBinding.serviceNameTxt.setText(generalFunc.getJsonValueStr("title", data));
                            iBinding.qtyTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("Qty", data)));

                            String subtitle = generalFunc.getJsonValueStr("subtitle", data);
                            if (Utils.checkText(subtitle)) {
                                iBinding.subTitleTxt.setText(subtitle);
                                iBinding.subTitleTxt.setVisibility(View.VISIBLE);
                            } else {
                                iBinding.subTitleTxt.setVisibility(View.GONE);
                            }

                            iBinding.commentHTxt.setText(LBL_SPECIAL_INSTRUCTION_TXT + " : ");
                            String comment = generalFunc.getJsonValueStr("comment", data);
                            if (Utils.checkText(comment)) {
                                iBinding.commentVTxt.setText(comment);
                            } else {
                                iBinding.commentVTxt.setText(LBL_NO_SPECIAL_INSTRUCTION);
                            }

                            binding.itemContainer.addView(iBinding.getRoot());
                        }
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)), true);
                }
            } else {
                generalFunc.showError(true);
            }
        });
    }

    private Context getActContext() {
        return MoreServiceInfoActivity.this;
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        if (view.getId() == R.id.backImgView) {
            MoreServiceInfoActivity.super.onBackPressed();
        }
    }
}