package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.InputType;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.view.View;
import android.view.inputmethod.EditorInfo;

import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.countryview.view.CountryPicker;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SetOnTouchList;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityForgotPasswordBinding;
import com.service.handler.ApiHandler;
import com.utils.LoadImageGlide;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import java.util.HashMap;

public class ForgotPasswordActivity extends ParentActivity implements GenerateAlertBox.HandleAlertBtnClick {


    String required_str = "";
    String error_email_str = "";
    ActivityForgotPasswordBinding binding;

    int submitBtnId;
    boolean isEmail = true;
    static String vCountryCode = "";
    static String vSImage = "";
    static boolean isCountrySelected = false;
    static String vPhoneCode = "";
    CountryPicker countryPicker;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_forgot_password);
        initView();
        removeInput();
        setLabel();

    }

    public Context getActContext() {
        return ForgotPasswordActivity.this;
    }

    private void initView() {


        vCountryCode = Utils.checkText(getIntent().getStringExtra("vCountryCode")) ? getIntent().getStringExtra("vCountryCode") : generalFunc.retrieveValue(Utils.DefaultCountryCode);
        vPhoneCode = Utils.checkText(getIntent().getStringExtra("vPhoneCode")) ? getIntent().getStringExtra("vPhoneCode") : generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        vSImage = Utils.checkText(getIntent().getStringExtra("vSImage")) ? getIntent().getStringExtra("vSImage") : generalFunc.retrieveValue(Utils.DefaultCountryImage);

        if (!vSImage.equals("")) {
            new LoadImageGlide.builder(this, LoadImageGlide.bind(vSImage), binding.countryimage).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        }

        if (!vPhoneCode.equalsIgnoreCase("")) {
            binding.countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
            isCountrySelected = true;
        }

        binding.countryBox.setShowClearButton(false);

        addToClickHandler(binding.imgClose);
        submitBtnId = Utils.generateViewId();
        addToClickHandler(binding.btnArea);


        if (generalFunc.isRTLmode()) {
            binding.btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }


    }

    public void removeInput() {
        Utils.removeInput(binding.countryBox);

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            binding.countrydropimage.setVisibility(View.VISIBLE);
            binding.countryBox.setOnTouchListener(new SetOnTouchList());
            addToClickHandler(binding.countryBox);
        } else {
            binding.countrydropimage.setVisibility(View.GONE);
        }
    }

    private void setLabel() {


        binding.mobileBoxHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_OR_EMAIL_FORGOT_PASSWORD"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR");
        binding.forgotpasswordHint.setText(generalFunc.retrieveLangLBl("", "LBL_FORGET_YOUR_PASS_TXT"));
        binding.forgotpasswordNote.setText(generalFunc.retrieveLangLBl("", "LBL_FORGET_PASS_NOTE"));
        binding.btnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SUBMIT_TXT"));

        binding.emailBox.addTextChangedListener(new TextWatcher() {

            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence charSequence, int start, int before, int count) {
                if (charSequence.length() > 3 && TextUtils.isDigitsOnly(binding.emailBox.getText())) {
                    binding.yearSelectArea.setVisibility(View.VISIBLE);
                    binding.viewDiv.setVisibility(View.VISIBLE);
                    isEmail = false;
                } else {
                    isEmail = true;
                    binding.yearSelectArea.setVisibility(View.GONE);
                    binding.viewDiv.setVisibility(View.GONE);
                }
                binding.errorTxt.setVisibility(View.GONE);

            }

            @Override
            public void afterTextChanged(Editable s) {

            }
        });

        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, Utils.getText(binding.emailBox));
        binding.emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);
        binding.emailBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_MOBILE_NUMBER_OR_EMAIL_FORGOT_PASSWORD"));
        if (isEmailBlankAndOptional) {
            if (Utils.checkText(getIntent().getStringExtra("vmobile"))) {
                binding.emailBox.setText(getIntent().getStringExtra("vmobile"));
            }
            isEmail = false;
            binding.yearSelectArea.setVisibility(View.VISIBLE);
            binding.viewDiv.setVisibility(View.VISIBLE);


        } else {
            binding.emailBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
            isEmail = true;
            binding.yearSelectArea.setVisibility(View.GONE);
            binding.viewDiv.setVisibility(View.GONE);

        }
    }

    @Override
    public void handleBtnClick(int btn_id) {
        Utils.hideKeyboard(getActContext());
        if (btn_id == 1) {
            onBackPressed();
        }
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && data != null) {

            vCountryCode = data.getStringExtra("vCountryCode");
            vPhoneCode = data.getStringExtra("vPhoneCode");
            isCountrySelected = true;
            vSImage = data.getStringExtra("vSImage");
            new LoadImageGlide.builder(this, LoadImageGlide.bind(vSImage), binding.countryimage).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
            binding.countryBox.setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
        }

    }

    public void checkValues() {
        boolean countryEntered = false;
        boolean emailEntered = Utils.checkText(binding.emailBox.getText().toString().replace("+", ""));
        if (!emailEntered) {
            binding.errorTxt.setText(required_str);
            binding.errorTxt.setVisibility(View.VISIBLE);
        }


        String regexStr = "^[0-9]*$";
        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes") && binding.yearSelectArea.getVisibility() == View.VISIBLE && binding.emailBox.getText().toString().trim().replace("+", "").matches(regexStr)) {

            if (emailEntered) {
                emailEntered = binding.emailBox.length() >= 3;
                if (!emailEntered) {
                    binding.errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
                    binding.errorTxt.setVisibility(View.VISIBLE);
                }
            } else {
                binding.errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
                binding.errorTxt.setVisibility(View.VISIBLE);
            }

            if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes") && binding.yearSelectArea.getVisibility() == View.VISIBLE) {
                countryEntered = isCountrySelected ? true : false;
                if (binding.countryBox.getText().length() == 0) {
                    countryEntered = false;
                }

                if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {

                    if (!countryEntered) {
                        Utils.setErrorFields(binding.countryBox, required_str);
                        binding.countrydropimage.setVisibility(View.GONE);
                    } else {
                        binding.countrydropimage.setVisibility(View.VISIBLE);

                    }
                } else {
                    binding.countrydropimage.setVisibility(View.GONE);
                }
            }

            if (!emailEntered) {
                return;
            }
            forgptPasswordCall();

        } else {
            emailEntered = Utils.checkText(binding.emailBox);
            if (Utils.checkText(binding.emailBox)) {
                emailEntered = (generalFunc.isEmailValid(Utils.getText(binding.emailBox)));
            } else {
                binding.errorTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR"));
                binding.errorTxt.setVisibility(View.VISIBLE);
            }

            if (!emailEntered) {
                binding.errorTxt.setText(required_str);
                binding.errorTxt.setVisibility(View.VISIBLE);
            } else if (emailEntered && !generalFunc.isEmailValid(Utils.getText(binding.emailBox))) {
                binding.errorTxt.setText(error_email_str);
                binding.errorTxt.setVisibility(View.VISIBLE);
            }

            if (emailEntered == false) {
                return;
            }
            forgptPasswordCall();
        }

    }

    public void forgptPasswordCall() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "requestResetPassword");
        parameters.put("vEmail", Utils.getText(binding.emailBox));
        parameters.put("UserType", Utils.app_type);
        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
            parameters.put("isEmail", isEmail ? "Yes" : "No");
            if (!isEmail) {
                parameters.put("PhoneCode", vPhoneCode);
                parameters.put("CountryCode", vCountryCode);
            }
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    binding.emailBox.setText("");
                    GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.setBtnClickList(ForgotPasswordActivity.this);
                    generateAlert.showAlertBox();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }

            } else {
                generalFunc.showError();
            }
        });

    }

    public void setData(String vCountryCode, String vPhoneCode, String vSImage) {
        ForgotPasswordActivity.vCountryCode = vCountryCode;
        ForgotPasswordActivity.vPhoneCode = vPhoneCode;
        isCountrySelected = true;
        ForgotPasswordActivity.vSImage = vSImage;
        new LoadImageGlide.builder(this, LoadImageGlide.bind(vSImage), binding.countryimage).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
        GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
        binding.countryBox.setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.countryBox) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true).setLocale(MyUtils.getLocale()).showingFlag(true).enablingSearch(true).setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName())).build();
            }
            countryPicker.show(getActContext());
        } else if (i == binding.btnArea.getId()) {

            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {

                generalFunc.showMessage(binding.emailBox, generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            } else {
                checkValues();

            }

        } else if (i == binding.imgClose.getId()) {
            onBackPressed();

        }

    }


}
