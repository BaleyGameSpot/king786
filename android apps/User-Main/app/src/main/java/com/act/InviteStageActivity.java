package com.act;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.activity.ParentActivity;
import com.dialogs.ImageTitleDialog;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.OpenMainProfile;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;

public class InviteStageActivity extends ParentActivity {


    MTextView titleTxt;
    ImageView backBtn, nextBtn;
    String required_str = "";
    MaterialEditText invitecodeBox;
    MTextView optionalTxt;
    ImageView inviteQueryImg;
    View contentArea;
    View llLoaderView;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_invite_stage);
        init();
    }

    private void init() {
        backBtn = findViewById(R.id.backBtn);
        nextBtn = findViewById(R.id.nextBtn);
        titleTxt = findViewById(R.id.titleTxt);
        optionalTxt = findViewById(R.id.optionalTxt);
        inviteQueryImg = findViewById(R.id.inviteQueryImg);
        invitecodeBox = (MaterialEditText) findViewById(R.id.invitecodeBox);
        contentArea = findViewById(R.id.contentArea);
        llLoaderView = findViewById(R.id.llLoaderView);
        llLoaderView.setVisibility(View.GONE);
        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        if (generalFunc.isRTLmode()) {
            invitecodeBox.setPaddings(paddingValStart, 0, 0, 0);
        } else {
            invitecodeBox.setPaddings(0, 0, paddingValStart, 0);
        }
        invitecodeBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_REFERRAL_CODE_HINT"), generalFunc.retrieveLangLBl("", "LBL_REFERRAL_CODE_HINT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        optionalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OPTIONAL"));
        titleTxt.setText(generalFunc.retrieveLangLBl("Have a referral code?", "LBL_REFERRAL_TITLE"));
        addToClickHandler(backBtn);
        addToClickHandler(nextBtn);
        addToClickHandler(inviteQueryImg);
        if (generalFunc.isRTLmode()) {
            nextBtn.setRotation(180);
            backBtn.setRotation(180);
        }

        manageAnimation(contentArea);
    }

    @Override
    public void onBackPressed() {
        new ActUtils(getActContext()).setOkResult();
        super.onBackPressed();
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == backBtn.getId()) {
            onBackPressed();
        } else if (i == nextBtn.getId()) {
            registerUser();
        } else if (i == inviteQueryImg.getId()) {
            ImageTitleDialog.build(getActContext(), generalFunc.retrieveLangLBl(" What is Referral / Invite Code ?", "LBL_REFERAL_SCHEME_TXT"), generalFunc.retrieveLangLBl("", "LBL_REFERAL_SCHEME"), generalFunc.retrieveLangLBl("", "LBL_OK"), R.drawable.invite);
        }
    }


    private Context getActContext() {
        return InviteStageActivity.this;
    }

    private void registerUser() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "signup");
        parameters.put("vFirstName", getIntent().getStringExtra("vFirstName"));
        parameters.put("vLastName", getIntent().getStringExtra("vLastName"));
        parameters.put("vEmail", getIntent().getStringExtra("vEmail"));
        parameters.put("vPhone", getIntent().getStringExtra("vmobile"));
        parameters.put("vPassword", getIntent().getStringExtra("vPassword"));
        parameters.put("PhoneCode", getIntent().getStringExtra("vPhoneCode"));
        parameters.put("CountryCode", getIntent().getStringExtra("vCountryCode"));
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("vInviteCode", Utils.getText(invitecodeBox));
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));


        llLoaderView.setVisibility(View.VISIBLE);

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            llLoaderView.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (generalFunc.retrieveValue("isSmartLogin") != null && generalFunc.retrieveValue("isSmartLogin").equalsIgnoreCase("Yes")) {
                        generalFunc.storeData("isUserSmartLogin", "Yes");
                    } else {
                        generalFunc.storeData("isUserSmartLogin", "No");
                    }
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), true);

                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(getActContext(), generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }
}