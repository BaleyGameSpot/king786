package com.fragments;


import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.text.InputType;
import android.text.method.ScrollingMovementMethod;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.FrameLayout;
import android.widget.ImageView;

import androidx.fragment.app.Fragment;

import com.act.MyProfileActivity;
import com.act.VerifyInfoActivity;
import com.countryview.view.CountryPicker;
import com.dialogs.OpenListView;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.GetUserData;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * A simple {@link Fragment} subclass.
 */
public class EditProfileFragment extends BaseFragment {

    MyProfileActivity myProfileAct;
    View view;

    GeneralFunctions generalFunc;

    JSONObject userProfileJson = null;

    MaterialEditText countryBox;
    MaterialEditText mobileBox;
    // MaterialEditText profileDescriptionEditBox;


    String selected_language_code = "";


    ArrayList<HashMap<String, String>> languageDataList = new ArrayList<>();
    ArrayList<HashMap<String, String>> currencyDataList = new ArrayList<>();


    String selected_currency = "";
    String default_selected_currency = "";


    MButton btn_type2;
    int submitBtnId;

    String required_str = "";
    String error_email_str = "";

    String vCountryCode = "";
    String vPhoneCode = "";
    String mobileBoxVal = "";
    boolean isCountrySelected = false;

    FrameLayout langSelectArea, currencySelectArea;
    ImageView countryimage;
    String vSImage = "";
    InternetConnection intCheck;
    CountryPicker countryPicker;

    MTextView fNameTextH, lNameTextH, emailTextH, langTextH, curTextH, mobileBoxHTxt, profileDescriptionHTxt;
    MaterialEditText txtfNametxt, txtlNametxt, txtemailtxt, txtlangtxt, txtcurtxt, txtprofileDescriptiontxt;
    ImageView langDropDownArrow, curDropDownArrow;
    View profileDescriptionEditBox;

    @SuppressLint("ClickableViewAccessibility")
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        setHasOptionsMenu(true);
        view = inflater.inflate(R.layout.fragment_edit_profile, container, false);

        myProfileAct = (MyProfileActivity) getActivity();
        intCheck = new InternetConnection(getActContext());

        generalFunc = myProfileAct.generalFunc;
        countryimage = view.findViewById(R.id.countryimage);

        View fNameBox = view.findViewById(R.id.fNameBox);
        fNameTextH = fNameBox.findViewById(R.id.mTextH);
        txtfNametxt = fNameBox.findViewById(R.id.mEditText);

        View lNameBox = view.findViewById(R.id.lNameBox);
        lNameTextH = lNameBox.findViewById(R.id.mTextH);
        txtlNametxt = lNameBox.findViewById(R.id.mEditText);

        View emailBox = view.findViewById(R.id.emailBox);
        emailTextH = emailBox.findViewById(R.id.mTextH);
        txtemailtxt = emailBox.findViewById(R.id.mEditText);

        profileDescriptionEditBox = view.findViewById(R.id.profileDescriptionEditBox);
        profileDescriptionHTxt = profileDescriptionEditBox.findViewById(R.id.mTextH);
        txtprofileDescriptiontxt = profileDescriptionEditBox.findViewById(R.id.mEditText);
        txtprofileDescriptiontxt.getLayoutParams().height = (int) getResources().getDimension(R.dimen._90sdp);

        countryBox = view.findViewById(R.id.countryBox);
        mobileBox = view.findViewById(R.id.mobileBox);

        View langBox = view.findViewById(R.id.langBox);
        langTextH = langBox.findViewById(R.id.mTextH);
        txtlangtxt = langBox.findViewById(R.id.mEditText);
        langDropDownArrow = langBox.findViewById(R.id.mDropDownArrow);
        langDropDownArrow.setVisibility(View.VISIBLE);

        View currencyBox = view.findViewById(R.id.currencyBox);
        curTextH = currencyBox.findViewById(R.id.mTextH);
        txtcurtxt = currencyBox.findViewById(R.id.mEditText);
        curDropDownArrow = currencyBox.findViewById(R.id.mDropDownArrow);
        curDropDownArrow.setVisibility(View.VISIBLE);

        mobileBoxHTxt = (MTextView) view.findViewById(R.id.mobileBoxHTxt);

        btn_type2 = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2)).getChildView();
        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);

        int imagewidth = (int) getResources().getDimension(R.dimen._30sdp);
        int imageheight = (int) getResources().getDimension(R.dimen._20sdp);

        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), vSImage, imagewidth, imageheight)), countryimage).build();
        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        int paddingValEnd = (int) getResources().getDimension(R.dimen._12sdp);

        currencySelectArea = view.findViewById(R.id.currencySelectArea);
        langSelectArea = view.findViewById(R.id.langSelectArea);

        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        addToClickHandler(btn_type2);
        mobileBox.setInputType(InputType.TYPE_CLASS_NUMBER);

        txtemailtxt.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);

        mobileBox.setImeOptions(EditorInfo.IME_ACTION_DONE);

        txtprofileDescriptiontxt.setSingleLine(false);
        txtprofileDescriptiontxt.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        txtprofileDescriptiontxt.setGravity(Gravity.TOP);
        txtprofileDescriptiontxt.setMovementMethod(new ScrollingMovementMethod());
        txtprofileDescriptiontxt.setOnTouchListener((view, motionEvent) -> {
            view.getParent().requestDisallowInterceptTouchEvent(true);
            if ((motionEvent.getAction() & MotionEvent.ACTION_MASK) == MotionEvent.ACTION_UP) {
                view.getParent().requestDisallowInterceptTouchEvent(false);
            }
            return false;
        });


        userProfileJson = myProfileAct.obj_userProfile;

        setLabels();

        removeInput();


        setData();
        buildLanguageList();

        myProfileAct.changePageTitle(generalFunc.retrieveLangLBl("", "LBL_EDIT_PROFILE_TXT"));

        if (myProfileAct.isEmail) {
            emailBox.requestFocus();
        }

        if (myProfileAct.isMobile) {
            mobileBox.requestFocus();
        }

        if (ServiceModule.IsTrackingProvider) {
            langSelectArea.setVisibility(View.GONE);
            currencySelectArea.setVisibility(View.GONE);
            profileDescriptionEditBox.setVisibility(View.GONE);
            btn_type2.setVisibility(View.GONE);

            Utils.removeInput(txtfNametxt);
            Utils.removeInput(txtlNametxt);
            Utils.removeInput(txtemailtxt);
            Utils.removeInput(mobileBox);
            Utils.removeInput(countryBox);
            view.findViewById(R.id.countrydropimage).setVisibility(View.GONE);
        }
        return view;
    }

    public void setLabels() {
        fNameTextH.setText(generalFunc.retrieveLangLBl("", "LBL_FIRST_NAME_HEADER_TXT"));
        txtfNametxt.setHint(generalFunc.retrieveLangLBl("", "LBL_FIRST_NAME_HEADER_TXT"));
        lNameTextH.setText(generalFunc.retrieveLangLBl("", "LBL_LAST_NAME_HEADER_TXT"));
        txtlNametxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LAST_NAME_HEADER_TXT"));
        emailTextH.setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_LBL_TXT"));
        txtemailtxt.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_EMAIL_HINT"));
        countryBox.setText(generalFunc.retrieveLangLBl("", "LBL_COUNTRY_TXT"));
        mobileBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        langTextH.setText(generalFunc.retrieveLangLBl("", "LBL_LANGUAGE_TXT"));
        curTextH.setText(generalFunc.retrieveLangLBl("", "LBL_CURRENCY_TXT"));
        profileDescriptionHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ABOUT_YOU"));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_UPDATE"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");
        mobileBoxHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HINT_TXT"));
        if (ServiceModule.ServiceProvider && myProfileAct.isUfxServicesEnabled) {
            profileDescriptionEditBox.setVisibility(View.VISIBLE);
        }
    }

    @SuppressLint("ClickableViewAccessibility")
    public void removeInput() {
        Utils.removeInput(countryBox);
        Utils.removeInput(txtlangtxt);
        Utils.removeInput(txtcurtxt);
        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            view.findViewById(R.id.countrydropimage).setVisibility(View.VISIBLE);
            addToClickHandler(countryBox);
            countryBox.setOnTouchListener(new setOnTouchList());
        } else {
            view.findViewById(R.id.countrydropimage).setVisibility(View.GONE);
        }
        txtlangtxt.setOnTouchListener(new setOnTouchList());
        txtcurtxt.setOnTouchListener(new setOnTouchList());
        addToClickHandler(txtlangtxt);
        addToClickHandler(txtcurtxt);
        txtlangtxt.setOnClickListener(view -> {
            //
            showLanguageList();
        });
        txtcurtxt.setOnClickListener(view -> {
            //
            showCurrencyList();
        });
    }

    public void setData() {
        txtfNametxt.setText(generalFunc.getJsonValueStr("vName", userProfileJson));
        txtlNametxt.setText(generalFunc.getJsonValueStr("vLastName", userProfileJson));

        if (generalFunc.retrieveValue("ENABLE_EDIT_DRIVER_PROFILE").equalsIgnoreCase("No")) {
            Utils.removeInput(txtfNametxt);
            Utils.removeInput(txtlNametxt);
        }

        txtemailtxt.setText(generalFunc.getJsonValueStr("vEmail", userProfileJson));
        countryBox.setText(generalFunc.convertNumberWithRTL("+" + generalFunc.getJsonValueStr("vCode", userProfileJson)));

        mobileBoxVal = generalFunc.getJsonValueStr("vPhone", userProfileJson);
        mobileBox.setText(mobileBoxVal);
        txtcurtxt.setText(generalFunc.getJsonValueStr("vCurrencyDriver", userProfileJson));
        txtprofileDescriptiontxt.setText(generalFunc.getJsonValueStr("tProfileDescription", userProfileJson));

        if (!generalFunc.getJsonValue("vCode", userProfileJson).equals("")) {
            isCountrySelected = true;
            vPhoneCode = generalFunc.getJsonValueStr("vCode", userProfileJson);
            vCountryCode = generalFunc.getJsonValueStr("vCountry", userProfileJson);
        }


        if (generalFunc.getJsonValueStr("vSCountryImage", userProfileJson) != null && !generalFunc.getJsonValueStr("vSCountryImage", userProfileJson).equalsIgnoreCase("")) {
            vSImage = generalFunc.getJsonValueStr("vSCountryImage", userProfileJson);
            int imagewidth = (int) getResources().getDimension(R.dimen._30sdp);
            int imageheight = (int) getResources().getDimension(R.dimen._20sdp);
            new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(getActContext(), vSImage, imagewidth, imageheight)), countryimage).build();


        }
        selected_currency = generalFunc.getJsonValueStr("vCurrencyDriver", userProfileJson);
        default_selected_currency = selected_currency;
    }

    public void buildLanguageList() {

        JSONArray languageList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.LANGUAGE_LIST_KEY));
        languageDataList.clear();


        HashMap<String, String> data = new HashMap<>();
        data.put(Utils.LANGUAGE_LIST_KEY, "");
        data.put(Utils.LANGUAGE_CODE_KEY, "");
        data = generalFunc.retrieveValue(data);

        for (int i = 0; i < languageList_arr.length(); i++) {
            JSONObject obj_temp = generalFunc.getJsonObject(languageList_arr, i);

            HashMap<String, String> mapData = new HashMap<>();
            mapData.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));
            mapData.put("vCode", generalFunc.getJsonValueStr("vCode", obj_temp));
            mapData.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));
            mapData.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));

            if (Utils.getText(txtlangtxt).equalsIgnoreCase(generalFunc.getJsonValueStr("vTitle", obj_temp))) {
                selLanguagePosition = i;
            }

            if ((data.get(Utils.LANGUAGE_CODE_KEY)).equalsIgnoreCase(generalFunc.getJsonValueStr("vCode", obj_temp))) {
                selLanguagePosition = i;

                txtlangtxt.setText(generalFunc.getJsonValueStr("vTitle", obj_temp));
            }

            languageDataList.add(mapData);

            if ((generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY)).equals(generalFunc.getJsonValue("vCode", obj_temp))) {
                selected_language_code = generalFunc.getJsonValueStr("vCode", obj_temp);

            }
        }


        if (languageDataList.size() < 2) {
            langSelectArea.setVisibility(View.GONE);
        }

        buildCurrencyList();

    }

    public void buildCurrencyList() {


        JSONArray currencyList_arr = generalFunc.getJsonArray(generalFunc.retrieveValue(Utils.CURRENCY_LIST_KEY));
        currencyDataList.clear();
        if (currencyList_arr != null) {
            for (int i = 0; i < currencyList_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(currencyList_arr, i);

                HashMap<String, String> mapData = new HashMap<>();
                mapData.put("vName", generalFunc.getJsonValueStr("vName", obj_temp));
                mapData.put("vCode", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                mapData.put("vSymbol", generalFunc.getJsonValueStr("vSymbol", obj_temp));
                mapData.put("vService_BG_color", generalFunc.getJsonValueStr("vService_BG_color", obj_temp));
                mapData.put("vService_TEXT_color", generalFunc.getJsonValueStr("vService_TEXT_color", obj_temp));

                if (Utils.getText(txtcurtxt).equalsIgnoreCase(generalFunc.getJsonValueStr("vName", obj_temp))) {
                    selCurrancyPosition = i;
                }

                currencyDataList.add(mapData);

            }
            if (generalFunc.getJsonValueStr("ENABLE_OPTION_UPDATE_CURRENCY", userProfileJson).equalsIgnoreCase("No")) {
                txtcurtxt.setVisibility(View.VISIBLE);
                txtcurtxt.setEnabled(false);
                txtcurtxt.setClickable(false);
            } else {

                if (currencyDataList.size() < 2) {
                    currencySelectArea.setVisibility(View.GONE);
                }

            }
        } else {
            currencySelectArea.setVisibility(View.GONE);
        }


        if (languageDataList.size() < 2) {
            langSelectArea.setVisibility(View.GONE);
        }
    }

    public void showCurrencyList() {
        OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_CURRENCY"), currencyDataList, OpenListView.OpenDirection.CENTER, true, position -> {

            selCurrancyPosition = position;
            HashMap<String, String> mapData = currencyDataList.get(position);

            selected_currency = mapData.get("vName");
            txtcurtxt.setText(mapData.get("vName"));

        }, true, generalFunc.retrieveLangLBl("", "LBL_CURRENCY_PREFER"), false).show(selCurrancyPosition, "vName");
    }

    int selCurrancyPosition = -1;
    int selLanguagePosition = -1;

    public void showLanguageList() {
        OpenListView.getInstance(getActContext(), getSelectLangText(), languageDataList, OpenListView.OpenDirection.CENTER, true, position -> {

            selLanguagePosition = position;
            HashMap<String, String> mapData = languageDataList.get(position);

            selected_language_code = mapData.get("vCode");

            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                generalFunc.showGeneralMessage("",
                        generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            } else {
                if (!generalFunc.retrieveValue(Utils.DEFAULT_LANGUAGE_VALUE).equals(mapData.get("vTitle"))) {
                    txtlangtxt.setText(mapData.get("vTitle"));
                    generalFunc.storeData(Utils.DEFAULT_LANGUAGE_VALUE, mapData.get("vTitle"));
                }
            }
        }, true, generalFunc.retrieveLangLBl("", "LBL_LANG_PREFER"), false).show(selLanguagePosition, "vTitle");


    }

    public String getSelectLangText() {
        return ("" + generalFunc.retrieveLangLBl("Select", "LBL_SELECT_LANGUAGE_HINT_TXT"));
    }

    public void checkValues() {


        boolean fNameEntered = Utils.checkText(txtfNametxt) ? true : Utils.setErrorFields(txtfNametxt, required_str);
        boolean lNameEntered = Utils.checkText(txtlNametxt) ? true : Utils.setErrorFields(txtlNametxt, required_str);
        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(txtemailtxt));
        boolean emailEntered = isEmailBlankAndOptional ? true : (Utils.checkText(txtemailtxt) ?
                (generalFunc.isEmailValid(Utils.getText(txtemailtxt)) ? true : Utils.setErrorFields(txtemailtxt, error_email_str))
                : Utils.setErrorFields(txtemailtxt, required_str));
        boolean mobileEntered = Utils.checkText(mobileBox) ? true : Utils.setErrorFields(mobileBox, required_str);
        boolean countryEntered = isCountrySelected ? true : false;
        boolean currencyEntered = !selected_currency.equals("") ? true : Utils.setErrorFields(txtcurtxt, required_str);


        if (mobileEntered) {
            mobileEntered = mobileBox.length() >= 3 ? true : Utils.setErrorFields(mobileBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
        }
        if (!fNameEntered || !lNameEntered || !emailEntered || !mobileEntered
                || !countryEntered || !currencyEntered) {
            return;
        }
        String currentMobileNum = generalFunc.getJsonValueStr("vPhone", userProfileJson);
        String currentPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", userProfileJson);
        if (!currentPhoneCode.equals(vPhoneCode) || !currentMobileNum.equals(mobileBoxVal)) {
            if (generalFunc.retrieveValue(Utils.MOBILE_VERIFICATION_ENABLE_KEY).equals("Yes")) {
                notifyVerifyMobile();
                return;
            }
        }


        updateProfile();

    }

    public void notifyVerifyMobile() {
        Bundle bn = new Bundle();
        bn.putString("MOBILE", vPhoneCode + mobileBoxVal);
        bn.putString("msg", "DO_PHONE_VERIFY");
        generalFunc.verifyMobile(bn, myProfileAct.getEditProfileFrag(), VerifyInfoActivity.class);

    }


    public void updateProfile() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserProfileDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("vName", Utils.getText(txtfNametxt));
        parameters.put("vLastName", Utils.getText(txtlNametxt));
        parameters.put("vPhone", Utils.getText(mobileBox));
        parameters.put("tProfileDescription", Utils.getText(txtprofileDescriptiontxt));
        parameters.put("vPhoneCode", vPhoneCode);
        parameters.put("vCountry", vCountryCode);
        parameters.put("vEmail", Utils.getText(txtemailtxt));
        parameters.put("CurrencyCode", selected_currency);
        parameters.put("LanguageCode", selected_language_code);
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {

                            String currentLangCode = generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY);
                            String vCurrencyPassenger = generalFunc.getJsonValueStr("vCurrencyDriver", userProfileJson);

                            String messgeJson = generalFunc.getJsonValue(Utils.message_str, responseString);
                            generalFunc.storeData(Utils.USER_PROFILE_JSON, messgeJson);
                            responseString = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);


                            new ConfigureMemberData(responseString, generalFunc, getActContext(), false);

                            if (!currentLangCode.equals(selected_language_code) || !selected_currency.equals(vCurrencyPassenger)) {
                                changeLanguagedata(selected_language_code);
                            } else {
                                myProfileAct.changeUserProfileJson(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                            }

                        } else {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }


    public void changeLanguagedata(String langcode) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", langcode);
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    if (responseString != null && !responseString.equals("")) {

                        boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                        if (isDataAvail) {

                            generalFunc.storeData(Utils.languageLabelsKey, generalFunc.getJsonValue(Utils.message_str, responseString));
                            generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));
                            generalFunc.storeData(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", responseString));
                            GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());

                            generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

                            GenerateAlertBox alertBox = generalFunc.notifyRestartApp();
                            alertBox.setCancelable(false);
                            alertBox.setBtnClickList(btn_id -> {

                                if (btn_id == 1) {
                                    //  generalFunc.restartApp();
                                    generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, selected_language_code);
                                    generalFunc.storeData(Utils.DEFAULT_CURRENCY_VALUE, selected_currency);
                                    GetUserData getUserData = new GetUserData(generalFunc, MyApp.getInstance().getApplicationContext());
                                    getUserData.GetConfigDataForLocalStorage();

                                    new Handler().postDelayed(() -> generalFunc.restartApp(), 100);

                                }
                            });
                        }
                    }
                });

    }

    public Context getActContext() {
        return myProfileAct.getActContext();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && resultCode == myProfileAct.RESULT_OK && data != null) {
            vCountryCode = data.getStringExtra("vCountryCode");
            vPhoneCode = data.getStringExtra("vPhoneCode");
            isCountrySelected = true;
            vSImage = data.getStringExtra("vSImage");
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
            countryBox.setText(generalFunc.convertNumberWithRTL("+" + vPhoneCode));
        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE && resultCode == myProfileAct.RESULT_OK) {


            updateProfile();

        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        Utils.hideKeyboard(getActivity());
    }

    public class setOnTouchList implements View.OnTouchListener {

        @Override
        public boolean onTouch(View view, MotionEvent motionEvent) {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                view.performClick();
            }
            return true;
        }
    }


    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == submitBtnId) {

            checkValues();
        } else if (i == R.id.countryBox) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                        .setLocale(MyUtils.getLocale()).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                        .build();
            }
            countryPicker.show(getActContext());
        }
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
}
