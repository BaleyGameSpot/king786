package com.act;

import android.content.Context;
import android.content.res.ColorStateList;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;

public class MyBusinessProfileActivity extends ParentActivity {


    public ImageView backImgView;
    MTextView titleTxt;
    MTextView statusTxt, noteTxt, noteHTxt;
    MaterialEditText emailBox, organizationBox;
    MButton btn_type2, deletebtn;
    int btnId;
    MTextView selprofilenoteTxt;

    HashMap<String, String> map;

    ImageView imageView;
    ImageView editIconImgView;
    String eStatus = "";
    boolean isupdate = false;
    LinearLayout ll_btn_type2, ll_deletebtn;

    String error_email_str = "";
    String required_str = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_my_business_profile);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        statusTxt = (MTextView) findViewById(R.id.statusTxt);
        noteTxt = (MTextView) findViewById(R.id.noteTxt);
        noteHTxt = (MTextView) findViewById(R.id.noteHTxt);
        selprofilenoteTxt = (MTextView) findViewById(R.id.selprofilenoteTxt);
        emailBox = (MaterialEditText) findViewById(R.id.emailBox);
        ll_btn_type2 = (LinearLayout) findViewById(R.id.ll_btn_type2);
        ll_deletebtn = (LinearLayout) findViewById(R.id.ll_deletebtn);
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        imageView = (ImageView) findViewById(R.id.imageView);
        deletebtn = ((MaterialRippleLayout) findViewById(R.id.deletebtn)).getChildView();
        deletebtn.setText(generalFunc.retrieveLangLBl("", "LBL_DELETE_BUSINESS_PROFILE"));
        deletebtn.setBackgroundTintList(ColorStateList.valueOf(getActContext().getResources().getColor(R.color.delete_btn_Red)));
        // noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INACTIVE_BUSINESS_PROFILE"));
        noteHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NOTE") + " :");
        addToClickHandler(deletebtn);
        editIconImgView = (ImageView) findViewById(R.id.editIconImgView);
        addToClickHandler(editIconImgView);

//        new CreateRoundedView(getResources().getColor(R.color.appThemeColor_Dark_1), Utils.dipToPixels(getActContext(), 15), 0,
//                getResources().getColor(R.color.appThemeColor_Dark_1), editIconImgView);
//
//        editIconImgView.setColorFilter(getResources().getColor(R.color.appThemeColor_TXT_1));
//        new CreateRoundedView(getResources().getColor(R.color.appThemeColor_hover_1), Utils.dipToPixels(getActContext(), 65), 2,
//                getResources().getColor(R.color.appThemeColor_hover_1), imageView);

        btnId = Utils.generateViewId();
        btn_type2.setId(btnId);
        deletebtn.setId(Utils.generateViewId());
        map = (HashMap<String, String>) getIntent().getSerializableExtra("data");
        selprofilenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FINAL_BUSINESS_PROFILE_SET_NOTE"));

        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");

        String ProfileStatus = map.get("ProfileStatus");
        if (ProfileStatus != null) {
            if (ProfileStatus.equalsIgnoreCase("Pending")) {
                noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PENDING_BUSINESS_PROFILE"));

            } else if (ProfileStatus.equalsIgnoreCase("Inactive")) {
                noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INACTIVE_BUSINESS_PROFILE"));
            } else if (ProfileStatus.equalsIgnoreCase("Terminate")) {
                noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TERMINATED_BUSINESS_PROFILE"));

            } else if (ProfileStatus.equalsIgnoreCase("Reject")) {
                noteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REJECTED_BUSINESS_PROFILE"));

            } else {
                if (ProfileStatus.equalsIgnoreCase("Active")) {
                    noteTxt.setText("");
                    noteTxt.setVisibility(View.GONE);
                    noteHTxt.setVisibility(View.GONE);
                }
                editIconImgView.setVisibility(View.VISIBLE);
                ll_deletebtn.setVisibility(View.VISIBLE);
                ll_btn_type2.setVisibility(View.GONE);
            }
        } else {
            noteTxt.setVisibility(View.GONE);
            noteHTxt.setVisibility(View.GONE);
            selprofilenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BUSINESS_PROFILE_SET_NOTE"));
        }

        addToClickHandler(btn_type2);
        organizationBox = (MaterialEditText) findViewById(R.id.organizationBox);

        String eProfileAdded = map.get("eProfileAdded");
        if (eProfileAdded != null && eProfileAdded.equalsIgnoreCase("Yes")) {
            statusTxt.setText(map.get("vProfileName"));
            ll_btn_type2.setVisibility(View.GONE);
            ll_deletebtn.setVisibility(View.VISIBLE);
        } else {
            statusTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ALL_SET"));
            ll_btn_type2.setVisibility(View.VISIBLE);
            ll_deletebtn.setVisibility(View.GONE);
        }


        emailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_FOR_RECEIPT"));
        organizationBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_YOUR_ORGANIZATION"));
        emailBox.setText(map.get("email"));
        organizationBox.setText(map.get("vCompany"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_CONFIRM_TXT"));


        Utils.removeInput(emailBox);
        Utils.removeInput(organizationBox);

        organizationBox.setHideUnderline(true);
        emailBox.setHideUnderline(true);
        addToClickHandler(backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    public void updateProfileData() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "UpdateUserOrganizationProfile");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        if (!eStatus.equalsIgnoreCase("")) {
            parameters.put("eStatus", eStatus);
        }
        if (isupdate) {
            parameters.put("vProfileEmail", emailBox.getText().toString().trim());
        } else {
            parameters.put("vProfileEmail", map.get("email"));
        }
        if (map.get("iUserProfileId") != null && !map.get("iUserProfileId").equalsIgnoreCase("")) {
            parameters.put("iUserProfileId", map.get("iUserProfileId"));
        }

        parameters.put("iUserProfileMasterId", map.get("iUserProfileMasterId"));
        parameters.put("iOrganizationId", map.get("iOrganizationId"));


        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail == true) {

                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)), null, generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), buttonId -> {

                        new ActUtils(getActContext()).startActClearTop(BusinessProfileActivity.class);
                        finish();
                    });
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
            }
        });
    }


    public Context getActContext() {
        return MyBusinessProfileActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == btn_type2.getId()) {

            boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(emailBox));
            boolean emailEntered = isEmailBlankAndOptional ? true : (Utils.checkText(emailBox) ?
                    (generalFunc.isEmailValid(Utils.getText(emailBox)) ? true : Utils.setErrorFields(emailBox, error_email_str))
                    : Utils.setErrorFields(emailBox, required_str));
            if (emailEntered == false) {
                return;
            }

            updateProfileData();
        } else if (i == R.id.editIconImgView) {
            emailBox.setHideUnderline(false);
            emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
            emailBox.setFocusableInTouchMode(true);
            emailBox.setFocusable(true);
            emailBox.setOnTouchListener((v, event) -> {

                return false;
            });
            ll_btn_type2.setVisibility(View.VISIBLE);
            ll_deletebtn.setVisibility(View.GONE);
            isupdate = true;
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_PROFILE_UPDATE_PAGE_TXT"));

        } else if (i == deletebtn.getId()) {

            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_DELETE_BUSINESS_PROFILE")), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                if (buttonId == 1) {
                    eStatus = "Deleted";
                    updateProfileData();
                }
            });
        }
    }

}
