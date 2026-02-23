package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;

public class EmailStageActivity extends ParentActivity {

    MTextView titleTxt;
    ImageView backBtn, nextBtn;
    String required_str = "";
    String error_email_str = "";
    MaterialEditText emailBox;
    MTextView optionalTxt;
    View contentArea;
    static final int NEXT_STAGE = 002;
    LinearLayout llLoaderView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_email_stage);
        init();
    }

    private void init() {
        backBtn = findViewById(R.id.backBtn);
        nextBtn = findViewById(R.id.nextBtn);
        titleTxt = findViewById(R.id.titleTxt);
        optionalTxt = findViewById(R.id.optionalTxt);
        emailBox = (MaterialEditText) findViewById(R.id.emailBox);
        contentArea = findViewById(R.id.contentArea);
        llLoaderView = (LinearLayout) findViewById(R.id.llLoaderView);
        llLoaderView.setVisibility(View.GONE);
        emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);
        emailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_LBL_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR");
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_TITLE"));
        optionalTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OPTIONAL"));
        addToClickHandler(backBtn);
        addToClickHandler(nextBtn);
        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(emailBox));
        if (isEmailBlankAndOptional) {
            optionalTxt.setVisibility(View.VISIBLE);
        }
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
            boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(emailBox));
            boolean emailEntered = isEmailBlankAndOptional ? true : (Utils.checkText(emailBox) ?
                    (generalFunc.isEmailValid(Utils.getText(emailBox)) ? true : Utils.setErrorFields(emailBox, error_email_str))
                    : Utils.setErrorFields(emailBox, required_str));

            if (!emailEntered) {
                return;
            }

            verifyUser();
        }
    }

    private void verifyUser() {

        if (Utils.checkText(emailBox)) {
            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "CheckMemberAccount");
            parameters.put("vEmail", Utils.getText(emailBox));
            parameters.put("UserType", Utils.app_type);
            parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
            parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));

            nextBtn.setEnabled(false);
            Utils.hideKeyboard(getActContext());

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
                nextBtn.setEnabled(true);

                if (responseString != null && !responseString.equals("") && !MyApp.getInstance().isMyAppInBackGround()) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                        manageView();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                } else {
                    generalFunc.showError();
                }
            });
        } else {
            manageView();
        }
    }

    private void manageView() {
        if (generalFunc.isReferralSchemeEnable()) {
            Bundle bn = new Bundle();
            bn.putString("mob", "+" + getIntent().getStringExtra("mob"));
            bn.putString("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
            bn.putString("vCountryCode", getIntent().getStringExtra("vCountryCode"));
            bn.putString("vmobile", getIntent().getStringExtra("vmobile"));
            bn.putString("vFirstName", getIntent().getStringExtra("vFirstName"));
            bn.putString("vLastName", getIntent().getStringExtra("vLastName"));
            bn.putString("vPassword", getIntent().getStringExtra("vPassword"));
            bn.putString("vEmail", Utils.getText(emailBox));

            new ActUtils(getActContext()).startActForResult(InviteStageActivity.class, bn, NEXT_STAGE);

        } else {
            registerUser();
        }
    }

    private void registerUser() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "signup");
        parameters.put("vFirstName", getIntent().getStringExtra("vFirstName"));
        parameters.put("vLastName", getIntent().getStringExtra("vLastName"));
        parameters.put("vEmail", Utils.getText(emailBox));
        parameters.put("vPhone", getIntent().getStringExtra("vmobile"));
        parameters.put("vPassword", getIntent().getStringExtra("vPassword"));
        parameters.put("PhoneCode", getIntent().getStringExtra("vPhoneCode"));
        parameters.put("CountryCode", getIntent().getStringExtra("vCountryCode"));
        parameters.put("vDeviceType", Utils.deviceType);
        //  parameters.put("vInviteCode", Utils.getText(invitecodeBox));
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
                    new OpenMainProfile(getActContext(), false, generalFunc).startProcess();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private Context getActContext() {
        return EmailStageActivity.this;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable @org.jetbrains.annotations.Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        manageAnimation(contentArea);
    }
}