package com.act;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityConfirmEmergencyTapBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class ConfirmEmergencyTapActivity extends ParentActivity {
    public String iTripId;
    private ActivityConfirmEmergencyTapBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_confirm_emergency_tap);
        iTripId = getIntent().getStringExtra("TripId");
        setLabels();
        addToClickHandler(binding.toolbarInclude.backImgView);
        addToClickHandler(binding.policeContactArea);
        addToClickHandler(binding.emeContactArea);

        if (generalFunc.isRTLmode()) {
            binding.Arrow1.setRotation(180);
            binding.Arrow2.setRotation(180);
        }
    }

    private void setLabels() {
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT"));
        binding.pageTitle.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_EME_PAGE_TITLE"));
        binding.callPoliceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_POLICE"));
        binding.sendAlertTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SEND_ALERT_EME_CONTACT"));
    }

    public void sendAlertToEmeContacts() {
        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendAlertToEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iTripId", iTripId);
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                } else {
                    if (generalFunc.getJsonValueStr(Utils.message_str_one, responseObj).equalsIgnoreCase("SmsError")) {
                        generalFunc.showGeneralMessage("", generalFunc.getJsonValueStr(Utils.message_str, responseObj));
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)), "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), i -> {
                            if (i == 1) {
                                new ActUtils(getActContext()).startAct(EmergencyContactActivity.class);
                            }
                        });
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private Context getActContext() {
        return ConfirmEmergencyTapActivity.this;
    }

    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (i == binding.policeContactArea.getId()) {

            try {
                Intent callIntent = new Intent(Intent.ACTION_DIAL);
                callIntent.setData(Uri.parse("tel:" + generalFunc.getJsonValueStr("SITE_POLICE_CONTROL_NUMBER", obj_userProfile)));
                startActivity(callIntent);
            } catch (Exception e) {
                // TODO: handle exception
            }
        } else if (i == binding.emeContactArea.getId()) {
            sendAlertToEmeContacts();
        }
    }
}