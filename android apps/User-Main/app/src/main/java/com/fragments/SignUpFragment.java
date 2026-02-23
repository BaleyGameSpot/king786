package com.fragments;


import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextPaint;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.fragment.app.Fragment;

import com.act.AppLoignRegisterActivity;
import com.act.SupportActivity;
import com.countryview.view.CountryPicker;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.PasswordViewHideManager;
import com.general.files.SetOnTouchList;
import com.general.files.SmartLogin;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;

/**
 * A simple {@link Fragment} subclass.
 */
public class SignUpFragment extends BaseFragment {

    static MaterialEditText countryBox;
    static String vCountryCode = "";
    static String vPhoneCode = "";
    static String vSImage = "";
    static boolean isCountrySelected = false;
    View view;
    AppLoignRegisterActivity appLoginAct;
    GeneralFunctions generalFunc;
    MaterialEditText fNameBox;
    MaterialEditText lNameBox;
    MaterialEditText emailBox;
    MaterialEditText passwordBox;
    MaterialEditText invitecodeBox;
    MaterialEditText mobileBox;

    // SignUpFragment signUpFrag;
    ImageView inviteQueryImg, imgSmartLoginQuery;
    LinearLayout inviteCodeArea, llSmartLoginEnable;
    private SmartLogin mSmartLogin;
    String required_str = "";
    String error_email_str = "";

    MTextView signbootomHint;

    ImageView countrydropimage, countrydropimagerror;

    CheckBox checkboxTermsCond, checkboxSmartLogin;
    MTextView txtTermsCond;

    MTextView btnTxt, titleTxt;
    LinearLayout btnArea;
    ImageView btnImg;
    LinearLayout imgClose;
    static ImageView countryimage;


    CountryPicker countryPicker;

    public static void setdata(int requestCode, int resultCode, Intent data) {

        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && data != null) {

            vCountryCode = data.getStringExtra("vCountryCode");
            vPhoneCode = data.getStringExtra("vPhoneCode");
            isCountrySelected = true;
            vSImage = data.getStringExtra("vSImage");

            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();

            GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
            countryBox.setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
        }

    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        view = inflater.inflate(R.layout.fragment_sign_up, container, false);

        appLoginAct = (AppLoignRegisterActivity) getActivity();
        generalFunc = appLoginAct.generalFunc;

        fNameBox = (MaterialEditText) view.findViewById(R.id.fNameBox);
        lNameBox = (MaterialEditText) view.findViewById(R.id.lNameBox);
        emailBox = (MaterialEditText) view.findViewById(R.id.emailBox);
        countryBox = (MaterialEditText) view.findViewById(R.id.countryBox);
        mobileBox = (MaterialEditText) view.findViewById(R.id.mobileBox);
        passwordBox = (MaterialEditText) view.findViewById(R.id.passwordBox);
        invitecodeBox = (MaterialEditText) view.findViewById(R.id.invitecodeBox);
        signbootomHint = (MTextView) view.findViewById(R.id.signbootomHint);
        countryimage = view.findViewById(R.id.countryimage);

        countrydropimage = (ImageView) view.findViewById(R.id.countrydropimage);
        countrydropimagerror = (ImageView) view.findViewById(R.id.countrydropimagerror);
        checkboxTermsCond = (CheckBox) view.findViewById(R.id.checkboxTermsCond);
        txtTermsCond = (MTextView) view.findViewById(R.id.txtTermsCond);
        addToClickHandler(txtTermsCond);

        mSmartLogin = new SmartLogin(getActContext(), generalFunc);
        llSmartLoginEnable = (LinearLayout) view.findViewById(R.id.llSmartLoginEnable);
        checkboxSmartLogin = (CheckBox) view.findViewById(R.id.checkboxSmartLogin);
        checkboxSmartLogin.setText(" " + generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"));
        imgSmartLoginQuery = (ImageView) view.findViewById(R.id.imgSmartLoginQuery);
        imgSmartLoginQuery.setOnClickListener(v -> generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_INFO")));

        vCountryCode = generalFunc.retrieveValue(Utils.DefaultCountryCode);
        vPhoneCode = generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);
        new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();


        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        int paddingValEnd = (int) getResources().getDimension(R.dimen._12sdp);
        if (generalFunc.isRTLmode()) {
            countryBox.setPaddings(paddingValEnd, 0, paddingValStart, 0);
            invitecodeBox.setPaddings(paddingValStart, 0, 0, 0);
        } else {
            countryBox.setPaddings(paddingValStart, 0, paddingValEnd, 0);
            invitecodeBox.setPaddings(0, 0, paddingValStart, 0);
        }


        if (!vPhoneCode.equalsIgnoreCase("")) {
            countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
            isCountrySelected = true;
        }
        inviteQueryImg = (ImageView) view.findViewById(R.id.inviteQueryImg);
        inviteCodeArea = (LinearLayout) view.findViewById(R.id.inviteCodeArea);
        addToClickHandler(inviteQueryImg);

        inviteCodeArea.setVisibility(View.GONE);

        if (generalFunc.isReferralSchemeEnable()) {
            inviteCodeArea.setVisibility(View.VISIBLE);
        }

        btnArea = (LinearLayout) view.findViewById(R.id.btnArea);
        btnTxt = (MTextView) view.findViewById(R.id.btnTxt);
        titleTxt = (MTextView) view.findViewById(R.id.titleTxt);

        btnImg = (ImageView) view.findViewById(R.id.btnImg);
        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }
        addToClickHandler(btnArea);

        removeInput();
        setLabels();


        passwordBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);

        mobileBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);
        fNameBox.setInputType(InputType.TYPE_CLASS_TEXT);
        lNameBox.setInputType(InputType.TYPE_CLASS_TEXT);

        fNameBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        lNameBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        emailBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        passwordBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        mobileBox.setImeOptions(EditorInfo.IME_ACTION_DONE);

        passwordBox.setTypeface(SystemFont.FontStyle.DEFAULT.font);

        new PasswordViewHideManager(getActContext(), passwordBox, generalFunc);
        countryBox.setShowClearButton(false);
        imgClose = (LinearLayout) view.findViewById(R.id.imgClose);
        addToClickHandler(imgClose);

        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        mSmartLogin.isSmartLoginEnable(llSmartLoginEnable, checkboxSmartLogin);
    }

    public void removeInput() {
        Utils.removeInput(countryBox);

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            // countrydropimage.setVisibility(View.GONE);
            countryBox.setOnTouchListener(new SetOnTouchList());
            addToClickHandler(countryBox);
        }
    }

    public void setLabels() {


        btnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGNUP"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGNUP"));

        fNameBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_FIRST_NAME_HEADER_TXT"));
        lNameBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_LAST_NAME_HEADER_TXT"));
        emailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_LBL_TXT"));
        countryBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_COUNTRY_TXT"));
        mobileBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        passwordBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_PASSWORD_LBL_TXT"));

        signbootomHint.setText(generalFunc.retrieveLangLBl("", "LBL_ALREADY_HAVE_ACC"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR");

        invitecodeBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_REFERRAL_CODE_HINT"), generalFunc.retrieveLangLBl("", "LBL_REFERRAL_CODE_HINT"));


        String termsfirstVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION_PREFIX_TXT");
        String termsSecondVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION");
        String termsThirdVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION_POSTFIX_TXT");
        String termsVal = termsfirstVal + " " + termsSecondVal + " " + termsThirdVal;
        manageSpanView(termsVal, termsSecondVal, txtTermsCond);

        String signupFirstVal = generalFunc.retrieveLangLBl("", "LBL_ALREADY_HAVE_ACC");
        String signupSecondVal = generalFunc.retrieveLangLBl("", "LBL_HEADER_TOPBAR_SIGN_IN_TXT");
        String signupThirdVal = generalFunc.retrieveLangLBl("", "LBL_NOW");
        String signupfinalVal = signupFirstVal + " " + signupSecondVal + " " + signupThirdVal;

        manageSpanView(signupfinalVal, signupSecondVal, signbootomHint);

    }


    ClickableSpan ClickableSpan = new ClickableSpan() {
        @Override
        public void onClick(View view) {


            if (view == txtTermsCond) {
                Bundle bn = new Bundle();
                bn.putBoolean("islogin", true);
                new ActUtils(getActContext()).startActWithData(SupportActivity.class, bn);
            } else if (view == signbootomHint) {
                if (!appLoginAct.isSignInFirst) {
                    appLoginAct.signUpFragment = null;
                    appLoginAct.hadnleFragment(new SignInFragment(), true, false);
                } else {
                    appLoginAct.signUpFragment = null;
                    appLoginAct.hadnleFragment(new SignInFragment(), false, false);
                }
                appLoginAct.signheaderHint.setText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN_WITH_SOC_ACC_HINT"));

            }

        }

        @Override
        public void updateDrawState(TextPaint ds) {
            ds.setColor(getResources().getColor(R.color.appThemeColor_1));    // you can use custom color
            ds.setUnderlineText(false);    // this remove the underline
        }
    };

    public void manageSpanView(String finalString, String clickAbleString, MTextView txtView) {
        String firstVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION_PREFIX_TXT");
        String secondVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION");
        String thirdVal = generalFunc.retrieveLangLBl("", "LBL_TERMS_CONDITION_POSTFIX_TXT");
        SpannableString termsSpan = new SpannableString(finalString);


        termsSpan.setSpan(
                ClickableSpan, // Span to add
                finalString.indexOf(clickAbleString), // Start of the span (inclusive)
                finalString.indexOf(clickAbleString) + String.valueOf(clickAbleString).length(), // End of the span (exclusive)
                Spanned.SPAN_EXCLUSIVE_EXCLUSIVE // Do not extend the span when text add later
        );
        txtView.setText(termsSpan);
        txtView.setMovementMethod(LinkMovementMethod.getInstance());
    }

    public void checkData() {
        Utils.hideKeyboard(getActContext());

        String noWhiteSpace = generalFunc.retrieveLangLBl("Password should not contain whitespace.", "LBL_ERROR_NO_SPACE_IN_PASS");
        String pass_length = generalFunc.retrieveLangLBl("Password must be", "LBL_ERROR_PASS_LENGTH_PREFIX")
                + " " + Utils.minPasswordLength + " " + generalFunc.retrieveLangLBl("or more character long.", "LBL_ERROR_PASS_LENGTH_SUFFIX");

        boolean fNameEntered = Utils.checkText(fNameBox) ? true : Utils.setErrorFields(fNameBox, required_str);
        boolean lNameEntered = Utils.checkText(lNameBox) ? true : Utils.setErrorFields(lNameBox, required_str);
        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(emailBox));
        boolean emailEntered = isEmailBlankAndOptional ? true : (Utils.checkText(emailBox) ?
                (generalFunc.isEmailValid(Utils.getText(emailBox)) ? true : Utils.setErrorFields(emailBox, error_email_str))
                : Utils.setErrorFields(emailBox, required_str));
        boolean mobileEntered = Utils.checkText(mobileBox) ? true : Utils.setErrorFields(mobileBox, required_str);
        boolean countryEntered = isCountrySelected ? true : false;
        boolean passwordEntered = Utils.checkText(passwordBox) ?
                (Utils.getText(passwordBox).contains(" ") ? Utils.setErrorFields(passwordBox, noWhiteSpace)
                        : (Utils.getText(passwordBox).length() >= Utils.minPasswordLength ? true : Utils.setErrorFields(passwordBox, pass_length)))
                : Utils.setErrorFields(passwordBox, required_str);

        if (countryBox.getText().length() == 0) {
            countryEntered = false;
        }

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {

            if (!countryEntered) {

                Utils.setErrorFields(countryBox, required_str);
                countrydropimagerror.setVisibility(View.VISIBLE);
                countrydropimage.setVisibility(View.GONE);
            } else {
                countrydropimage.setVisibility(View.VISIBLE);
                countrydropimagerror.setVisibility(View.GONE);

            }
        }

        if (mobileEntered) {
            mobileEntered = mobileBox.length() >= 3 ? true : Utils.setErrorFields(mobileBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
        }

        if (fNameEntered == false || lNameEntered == false || emailEntered == false || mobileEntered == false
                || countryEntered == false || passwordEntered == false) {
            return;
        }

        btnArea.setEnabled(false);
        if (generalFunc.retrieveValue(Utils.MOBILE_VERIFICATION_ENABLE_KEY).equals("Yes")) {

            checkUserExist();
        } else {
            registerUser();
        }
    }

    public void registerUser() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "signup");
        parameters.put("vFirstName", Utils.getText(fNameBox));
        parameters.put("vLastName", Utils.getText(lNameBox));
        parameters.put("vEmail", Utils.getText(emailBox));
        parameters.put("vPhone", Utils.getText(mobileBox));
        parameters.put("vPassword", Utils.getText(passwordBox));
        parameters.put("PhoneCode", vPhoneCode);
        parameters.put("CountryCode", vCountryCode);
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("vInviteCode", Utils.getText(invitecodeBox));
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));


        appLoginAct.llLoaderView.setVisibility(View.VISIBLE);
        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            appLoginAct.llLoaderView.setVisibility(View.GONE);
            btnArea.setEnabled(true);
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail == true) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), true);

                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    appLoginAct.manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(getActContext(),
                            generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();
                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void checkUserExist() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "isUserExist");
        parameters.put("Email", Utils.getText(emailBox));
        parameters.put("Phone", Utils.getText(mobileBox));

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            btnArea.setEnabled(true);

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail == true) {
                    notifyVerifyMobile();
                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void notifyVerifyMobile() {
        Bundle bn = new Bundle();
        bn.putString("MOBILE", vPhoneCode + Utils.getText(mobileBox));
        bn.putString("msg", "DO_PHONE_VERIFY");
    }

    public Context getActContext() {
        return appLoginAct.getActContext();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && resultCode == appLoginAct.RESULT_OK && data != null) {

            vCountryCode = data.getStringExtra("vCountryCode");
            vPhoneCode = data.getStringExtra("vPhoneCode");
            vSImage = data.getStringExtra("vSImage");
            Log.d("TestData", "::" + vSImage);
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
            isCountrySelected = true;
            countryBox.setTextColor(getResources().getColor(R.color.black));
        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE && resultCode == appLoginAct.RESULT_OK) {
            String MSG_TYPE = data == null ? "" : (data.getStringExtra("MSG_TYPE") == null ? "" : data.getStringExtra("MSG_TYPE"));
            if (!MSG_TYPE.equals("EDIT_PROFILE")) {
                registerUser();
            }
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }

    public void setData(String vCountryCode, String vPhoneCode, String vSImage) {
        this.vCountryCode = vCountryCode;
        this.vPhoneCode = vPhoneCode;
        isCountrySelected = true;
        this.vSImage = vSImage;
        new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
        countryBox.setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
    }


    public void onClickView(View view) {

        if (!isAdded()) {
            return;
        }
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == btnArea.getId()) {
            checkData();
        } else if (i == R.id.countryBox) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        //.setCountries(items_list)
                        .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                        .build();
            }
            countryPicker.show(getActContext());

        } else if (i == inviteQueryImg.getId()) {
            generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl(" What is Referral / Invite Code ?", "LBL_REFERAL_SCHEME_TXT"),
                    generalFunc.retrieveLangLBl("", "LBL_REFERAL_SCHEME"));

        } else if (i == imgClose.getId()) {
            appLoginAct.onBackPressed();
        }


    }


}
