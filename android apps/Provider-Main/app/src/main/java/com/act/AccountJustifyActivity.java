package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.activity.ParentActivity;
import com.countryview.view.CountryPicker;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.SetOnTouchList;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONObject;

import java.util.HashMap;

public class AccountJustifyActivity extends ParentActivity {


    LinearLayout emailarea, mobileNoArea;
    MaterialEditText emailBox;
    MaterialEditText countryBox;
    MaterialEditText mobileBox;
    String vCountryCode = "";
    String vPhoneCode = "";
    boolean isCountrySelected = false;
    String required_str = "";
    String error_email_str = "";
    MTextView btnTxt;
    MButton btn_type2;
    int submitBtnId;
    MTextView titleTxt;
    ImageView backImgView, logoutImageview, btnImg;
    LinearLayout btnArea, imgClose;
    ImageView imageView1;
    MaterialEditText invitecodeBox;
    ImageView inviteQueryImg;
    CheckBox checkboxTermsCond;
    MTextView txtTermsCond;
    ImageView countryimage;
    String vSImage = "";
    LinearLayout inviteCodeArea;
    CountryPicker countryPicker;
    ImageView nextBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_accountverification);
        bindViews();
        setValueInfo();
        removeInput();
    }


    public void removeInput() {
        Utils.removeInput(countryBox);
        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            imageView1.setVisibility(View.VISIBLE);
            countryBox.setOnTouchListener(new SetOnTouchList());
            addToClickHandler(countryBox);
        } else {
            imageView1.setVisibility(View.GONE);
        }
    }


    private void bindViews() {
        countryimage = (ImageView) findViewById(R.id.countryimage);
        emailBox = (MaterialEditText) findViewById(R.id.emailBox);
        countryBox = (MaterialEditText) findViewById(R.id.countryBox);
        mobileBox = (MaterialEditText) findViewById(R.id.mobileBox);
        emailarea = (LinearLayout) findViewById(R.id.emailarea);
        mobileNoArea = (LinearLayout) findViewById(R.id.mobileNoArea);
        btnTxt = (MTextView) findViewById(R.id.btnTxt);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        logoutImageview = (ImageView) findViewById(R.id.logoutImageview);
        btnImg = (ImageView) findViewById(R.id.btnImg);
        imageView1 = (ImageView) findViewById(R.id.countrydropimage);
        invitecodeBox = (MaterialEditText) findViewById(R.id.invitecodeBox);
        inviteQueryImg = (ImageView) findViewById(R.id.inviteQueryImg);
        checkboxTermsCond = (CheckBox) findViewById(R.id.checkboxTermsCond);
        txtTermsCond = (MTextView) findViewById(R.id.txtTermsCond);
        addToClickHandler(txtTermsCond);
        addToClickHandler(inviteQueryImg);

        inviteCodeArea = (LinearLayout) findViewById(R.id.inviteCodeArea);
        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        if (generalFunc.isRTLmode()) {
            invitecodeBox.setPaddings(paddingValStart, 0, 0, 0);
        } else {
            invitecodeBox.setPaddings(0, 0, paddingValStart, 0);
        }
        inviteCodeArea.setVisibility(View.GONE);
        if (generalFunc.isReferralSchemeEnable()) {
            inviteCodeArea.setVisibility(View.VISIBLE);
        }
        imgClose = (LinearLayout) findViewById(R.id.imgClose);
        nextBtn = findViewById(R.id.nextBtn);
        addToClickHandler(nextBtn);
        logoutImageview.setVisibility(View.VISIBLE);
        addToClickHandler(logoutImageview);
        backImgView.setVisibility(View.GONE);
        HashMap<String, String> data = new HashMap<>();
        data.put(Utils.DefaultCountryCode, "");
        data.put(Utils.DefaultPhoneCode, "");
        data = generalFunc.retrieveValue(data);
        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);
        new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        int paddingVal = (int) getResources().getDimension(R.dimen._35sdp);
        countryBox.setPaddings(generalFunc.isRTLmode() ? 0 : paddingVal, 0, generalFunc.isRTLmode() ? paddingVal : 0, 0);
        vCountryCode = data.get(Utils.DefaultCountryCode);
        vPhoneCode = data.get(Utils.DefaultPhoneCode);
        if (vPhoneCode != null && !vPhoneCode.equalsIgnoreCase("")) {
            countryBox.setText("+" + vPhoneCode);
            isCountrySelected = true;
        }
        btn_type2 = ((MaterialRippleLayout) findViewById(R.id.btn_type2)).getChildView();
        btnArea = (LinearLayout) findViewById(R.id.btnArea);
        submitBtnId = Utils.generateViewId();
        addToClickHandler(btn_type2);
        addToClickHandler(btnArea);
        addToClickHandler(imgClose);

        emailBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        mobileBox.setImeOptions(EditorInfo.IME_ACTION_DONE);
        mobileBox.setInputType(InputType.TYPE_CLASS_NUMBER);

        String vPhone = generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        if (vPhone.equals("")) {
            mobileNoArea.setVisibility(View.VISIBLE);
        } else {
            mobileBox.setText(vPhone);
            mobileNoArea.setVisibility(View.GONE);

        }

        String vEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
        if (vEmail.equals("")) {
            emailarea.setVisibility(View.VISIBLE);
        } else {
            emailBox.setText(vEmail);
            emailarea.setVisibility(View.GONE);
        }

        countryBox.setShowClearButton(false);

        if (generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile) != null && !generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile).equalsIgnoreCase("")) {
            vSImage = generalFunc.getJsonValueStr("vSCountryImage", obj_userProfile);
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();

        }

        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }

    }

    public Context getActContext() {
        return AccountJustifyActivity.this;
    }

    private void setValueInfo() {

        emailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_LBL_TXT"));
        countryBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_COUNTRY_TXT"));
        mobileBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR");
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ARRIVED_DIALOG_BTN_CONTINUE_TXT"));
        btnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ARRIVED_DIALOG_BTN_CONTINUE_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ACC_INFO"));
        String LBL_INVITE_CODE_HINT = generalFunc.retrieveLangLBl("", "LBL_INVITE_CODE_HINT");
        invitecodeBox.setBothText(LBL_INVITE_CODE_HINT, LBL_INVITE_CODE_HINT);
        String attrString1 = generalFunc.retrieveLangLBl("", "LBL_AGREE_TERMS");
        String attrString2 = generalFunc.retrieveLangLBl("", "LBL_TERMS_AND_CONDITION");
        String attrString3 = generalFunc.retrieveLangLBl("", "LBL_WITHOUT_RESERVATION");
        String htmlString = "<u><font color=" + getActContext().getResources().getColor(R.color.appThemeColor_1) + ">" + attrString2 + "</font></u>";
        txtTermsCond.setText(GeneralFunctions.fromHtml(attrString1 + " " + htmlString + " " + attrString3));
        emailBox.getLabelFocusAnimator().start();
        countryBox.getLabelFocusAnimator().start();
        mobileBox.getLabelFocusAnimator().start();

    }

    public void setData(String vCountryCode, String vPhoneCode, String vSImage) {
        this.vCountryCode = vCountryCode;
        this.vPhoneCode = vPhoneCode;
        isCountrySelected = true;
        this.vSImage = vSImage;
        new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
    }


    public void onClick(View view) {

        int i = view.getId();
        Utils.hideKeyboard(AccountJustifyActivity.this);

        if (i == R.id.countryBox) {

            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                        .build();
            }
            countryPicker.show(getActContext());
        } else if (i == btnArea.getId() || i == nextBtn.getId()) {
            Utils.hideKeyboard(AccountJustifyActivity.this);
            handleDetails();
        } else if (i == logoutImageview.getId()) {

            MyApp.getInstance().logOutFromDevice(false);

        } else if (i == inviteQueryImg.getId()) {
            generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl(" What is Referral / Invite Code ?", "LBL_REFERAL_SCHEME_TXT"),
                    generalFunc.retrieveLangLBl("", "LBL_REFERAL_SCHEME"));
        } else if (i == txtTermsCond.getId()) {

            Bundle bn = new Bundle();
            bn.putBoolean("islogin", true);
            new ActUtils(getActContext()).startActWithData(SupportActivity.class, bn);

        } else if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.imgClose) {
            MyApp.getInstance().logOutFromDevice(false);
        }

    }


    public void handleDetails() {

        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(emailBox));

        boolean emailEntered = isEmailBlankAndOptional ? true : (Utils.checkText(emailBox) ?
                (generalFunc.isEmailValid(Utils.getText(emailBox)) ? true : Utils.setErrorFields(emailBox, error_email_str))
                : Utils.setErrorFields(emailBox, required_str));
        boolean mobileEntered = Utils.checkText(mobileBox) ? true : Utils.setErrorFields(mobileBox, required_str);
        boolean countryEntered = isCountrySelected ? true : Utils.setErrorFields(countryBox, required_str);

        if (mobileEntered) {
            mobileEntered = mobileBox.length() >= 3 ? true : Utils.setErrorFields(mobileBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
        }

        if (mobileNoArea.getVisibility() == View.GONE) {
            mobileEntered = true;
            countryEntered = true;
        }
        if (emailarea.getVisibility() == View.GONE) {
            emailEntered = true;
        }


        if (!emailEntered || !mobileEntered || !countryEntered) {
            return;
        }


        manageAccount();
    }

    public void manageAccount() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserProfileDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("vName", generalFunc.getJsonValueStr("vName", obj_userProfile));
        parameters.put("vLastName", generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        parameters.put("vPhone", Utils.getText(mobileBox));
        parameters.put("vPhoneCode", vPhoneCode);
        parameters.put("vCountry", vCountryCode);
        parameters.put("vEmail", Utils.getText(emailBox));


        HashMap<String, String> data = new HashMap<>();
        data.put(Utils.DEFAULT_CURRENCY_VALUE, "");
        data.put(Utils.LANGUAGE_CODE_KEY, "");
        data = generalFunc.retrieveValue(data);

        parameters.put("CurrencyCode", data.get(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("LanguageCode", data.get(Utils.LANGUAGE_CODE_KEY));
        parameters.put("UserType", Utils.app_type);
        parameters.put("vInviteCode", Utils.getText(invitecodeBox));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                    if (responseStringObj != null) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj);

                        if (isDataAvail) {

                            new ConfigureMemberData(responseString, generalFunc, getActContext(), true);

                            generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObj));
                            manageSinchClient(generalFunc.getJsonValueStr(Utils.message_str, responseStringObj));
                            new OpenMainProfile(getActContext(), false, generalFunc).startProcess();

                        } else {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && resultCode == RESULT_OK && data != null) {
            vCountryCode = data.getStringExtra("vCountryCode");
            vPhoneCode = data.getStringExtra("vPhoneCode");
            isCountrySelected = true;
            countryBox.setText("+" + vPhoneCode);
            vSImage = data.getStringExtra("vSImage");
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        }
    }
}