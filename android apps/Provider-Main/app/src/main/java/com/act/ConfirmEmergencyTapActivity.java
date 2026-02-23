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
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityConfirmEmergencyTapBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import java.util.HashMap;

public class ConfirmEmergencyTapActivity extends ParentActivity {

    private ActivityConfirmEmergencyTapBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_confirm_emergency_tap);
        setLabels();
        addToClickHandler(binding.toolbarInclude.backImgView);
        addToClickHandler((findViewById(R.id.policeContactArea)));
        addToClickHandler((findViewById(R.id.emeContactArea)));
        if (generalFunc.isRTLmode()) {
            binding.Arrow1.setRotation(180);
            binding.Arrow2.setRotation(180);
        }
    }

    public void setLabels() {
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMERGENCY_CONTACT"));
        binding.pageTitle.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_EME_PAGE_TITLE"));
        binding.callPoliceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_POLICE"));
        binding.sendAlertTxt.setText(generalFunc.retrieveLangLBl("",
                "LBL_SEND_ALERT_EME_CONTACT"));
    }

    public void sendAlertToEmeContacts() {
        final HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "sendAlertToEmergencyContacts");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iTripId", getIntent().getStringExtra("TripId"));
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    if (responseString != null && !responseString.equals("")) {
                        String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);

                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", message_str));
                        } else {
                            if (generalFunc.getJsonValue(Utils.message_str_one, responseString).equalsIgnoreCase("SmsError")) {
                                generalFunc.showGeneralMessage("", message_str);
                            } else {
                                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message_str), "", generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), i -> {
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
        Utils.hideKeyboard(ConfirmEmergencyTapActivity.this);
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
