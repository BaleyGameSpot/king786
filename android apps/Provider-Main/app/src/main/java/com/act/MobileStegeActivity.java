package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextPaint;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResult;
import androidx.activity.result.ActivityResultCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatDialog;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.countryview.view.CountryPicker;
import com.facebook.FacebookSdk;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.RegisterGoogleLoginResCallBack;
import com.general.files.SetOnTouchList;
import com.general.files.SmartLogin;
import com.general.files.Socialpicker;
import com.google.android.gms.auth.api.Auth;
import com.google.android.gms.auth.api.signin.GoogleSignInResult;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DialogVerificationMethodBinding;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

public class MobileStegeActivity extends ParentActivity {

    static final int RC_SIGN_IN = 001, NEXT_STAGE = 2;
    static MaterialEditText countryBox;
    static String vCountryCode = "", vPhoneCode = "", vSImage = "";
    static boolean isCountrySelected = false;
    static ImageView countryimage;
    CountryPicker countryPicker;
    Locale locale;
    MaterialEditText mobileBox;
    ImageView backBtn, nextBtn;
    MTextView titleTxt, soicalHintTxt;
    static public SmartLogin mSmartLogin;
    public LinearLayout llLoaderView;
    String required_str = "";
    View contentArea;
    MTextView txtTermsCond;
    private AppCompatDialog vDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_mobile_stege);
        init();
    }

    @SuppressLint({"ClickableViewAccessibility", "SetTextI18n"})
    private void init() {
        if (generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY) != null && !generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY).isEmpty()) {
            FacebookSdk.setApplicationId(generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY));
        }
        if (Utils.checkText(generalFunc.retrieveValue("FACEBOOK_CLIENT_TOKEN"))) {
            FacebookSdk.setClientToken(generalFunc.retrieveValue("FACEBOOK_CLIENT_TOKEN"));
        }
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        titleTxt = findViewById(R.id.titleTxt);
        countryBox = findViewById(R.id.countryBox);
        mobileBox = findViewById(R.id.mobileBox);
        soicalHintTxt = findViewById(R.id.soicalHintTxt);
        llLoaderView = findViewById(R.id.llLoaderView);
        mobileBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        countryimage = findViewById(R.id.countryimage);
        backBtn = findViewById(R.id.backBtn);
        nextBtn = findViewById(R.id.nextBtn);
        contentArea = findViewById(R.id.contentArea);
        addToClickHandler(backBtn);
        addToClickHandler(nextBtn);
        vCountryCode = generalFunc.retrieveValue(Utils.DefaultCountryCode);
        vPhoneCode = generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);

        txtTermsCond = findViewById(R.id.txtTermsCond);
        String firstVal = generalFunc.retrieveLangLBl("By proceeding, I accept the", "LBL_TERMS_CONDITION_PREFIX_TXT");
        String secondVal = generalFunc.retrieveLangLBl("Terms and Condition & Privacy Policy", "LBL_TERMS_CONDITION_PRIVACY");
        String termsVal = firstVal + " " + secondVal;
        manageSpanView(termsVal, secondVal, txtTermsCond);


        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_MOBILE_NO"));
        soicalHintTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONNECT_WITH_SOCIAL_ACCOUNTS"));
        if (!generalFunc.retrieveValue("isOtherSignInOptionsAvailable").equalsIgnoreCase("Yes")) {
            soicalHintTxt.setVisibility(View.GONE);
        }

        manageAnimation(contentArea);


        if (!vSImage.equals("")) {
            new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        }
        int paddingValStart = (int) getResources().getDimension(R.dimen._35sdp);
        int paddingValEnd = (int) getResources().getDimension(R.dimen._12sdp);
        if (generalFunc.isRTLmode()) {
            countryBox.setPaddings(paddingValEnd, 0, paddingValStart, 0);
        } else {
            countryBox.setPaddings(paddingValStart, 0, paddingValEnd, 0);
        }

        if (!vPhoneCode.equalsIgnoreCase("")) {
            countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
            isCountrySelected = true;
        }

        countryBox.setShowClearButton(false);
        Utils.removeInput(countryBox);
        mSmartLogin = new SmartLogin(getActContext(), generalFunc, new SmartLogin.OnEventListener() {
            @Override
            public void onPasswordViewEvent(Intent i) {
                someActivityResultLauncher.launch(i);
            }

            @Override
            public void onResumeEvent() {

            }

            @Override
            public void onAuthenticationSucceeded() {
                if (mSmartLogin.ResultOk()) {
                    signInUser();
                }
            }
        });

        mobileBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT"));
        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            findViewById(R.id.countrydropimage).setVisibility(View.VISIBLE);
            addToClickHandler(countryBox);
            countryBox.setOnTouchListener(new SetOnTouchList());
        } else {
            findViewById(R.id.countrydropimage).setVisibility(View.GONE);
        }

        addToClickHandler(soicalHintTxt);
        if (generalFunc.isRTLmode()) {
            nextBtn.setRotation(180);
            backBtn.setRotation(180);
            soicalHintTxt.setCompoundDrawablesWithIntrinsicBounds(R.drawable.ic_left_arrow, 0, 0, 0);
            soicalHintTxt.setCompoundDrawablePadding((int) getResources().getDimension(R.dimen._10sdp));
        }
    }

    ClickableSpan ClickableSpan = new ClickableSpan() {
        @Override
        public void onClick(View view) {
            if (view == txtTermsCond) {
                Bundle bn = new Bundle();
                bn.putBoolean("islogin", true);
                new ActUtils(getActContext()).startActWithData(SupportActivity.class, bn);
            }

        }

        @Override
        public void updateDrawState(TextPaint ds) {
            ds.setColor(getResources().getColor(R.color.appThemeColor_1));    // you can use custom color
            ds.setUnderlineText(false);    // this remove the underline
        }
    };

    public void manageSpanView(String finalString, String clickAbleString, MTextView txtView) {
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

    ActivityResultLauncher<Intent> someActivityResultLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), new ActivityResultCallback<ActivityResult>() {
                @Override
                public void onActivityResult(ActivityResult result) {
                    if (result.getResultCode() == RESULT_OK) {
                        if (mSmartLogin.ResultOk()) {
                            signInUser();
                        }
                    }
                }
            });

    @Override
    public void onResume() {
        super.onResume();
        mSmartLogin.onResume();
    }

    private Context getActContext() {
        return MobileStegeActivity.this;
    }

    @SuppressLint("SetTextI18n")
    private void setData(String countryCode, String phoneCode, String sImage) {
        vCountryCode = countryCode;
        vPhoneCode = phoneCode;
        isCountrySelected = true;
        vSImage = sImage;
        new LoadImage.builder(LoadImage.bind(vSImage), countryimage).build();
        countryBox.setText("+" + generalFunc.convertNumberWithRTL(vPhoneCode));
    }

    private void signInUser() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "signIn");
        parameters.put("vEmail", "");
        parameters.put("vPassword", "");
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
            parameters.put("CountryCode", generalFunc.retrieveValue(Utils.DefaultCountryCode));
            parameters.put("PhoneCode", generalFunc.retrieveValue(Utils.DefaultPhoneCode));
        }

        llLoaderView.setVisibility(View.VISIBLE);
        llLoaderView.bringToFront();

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            llLoaderView.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(getActContext(), false, generalFunc).startProcess();
                } else {
                    if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Deleted")) {
                        openContactUsDialog(responseString);
                    } else if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Inactive")) {
                        openContactUsDialog(responseString);
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void openContactUsDialog(String responseString) {
        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        alertBox.setBtnClickList(btn_id -> {
            alertBox.closeAlertBox();
            if (btn_id == 0) {
                new ActUtils(getActContext()).startAct(ContactUsActivity.class);
            }
        });
        alertBox.showAlertBox();
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.countryBox) {
            if (countryPicker == null) {
                countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                        .setLocale(locale).showingFlag(true)
                        .enablingSearch(true)
                        .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                        .build();
            }
            countryPicker.show(getActContext());
        } else if (i == backBtn.getId()) {
            onBackPressed();
        } else if (i == soicalHintTxt.getId()) {
            Socialpicker.build(getActContext(), mSmartLogin.checkPrivacyAdded());
        } else if (i == nextBtn.getId()) {
            boolean mobileEntered = Utils.checkText(mobileBox) || Utils.setErrorFields(mobileBox, required_str);
            if (mobileEntered) {
                mobileEntered = mobileBox.length() >= 3 || Utils.setErrorFields(mobileBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
            }
            if (!mobileEntered) {
                return;
            }

            if (generalFunc.retrieveValue("SIGN_IN_OPTION").equalsIgnoreCase("OTP")) {
                if (generalFunc.retrieveValue("ENABLE_WHATSAPP_OTP_VERIFICATION").equalsIgnoreCase("Yes")) {
                    openVerificationDialog();
                } else {
                    nextScreen("");
                }
            } else {
                verifyUser();
            }
        }
    }

    private void nextScreen(@NonNull String verificationMethod) {
        Bundle bn = new Bundle();
        bn.putString("mob", "+" + vPhoneCode + Utils.getText(mobileBox));
        bn.putString("vPhoneCode", vPhoneCode);
        bn.putString("vCountryCode", vCountryCode);
        bn.putString("vmobile", Utils.getText(mobileBox));
        bn.putString("verificationMethod", verificationMethod);
        new ActUtils(getActContext()).startActForResult(PasswordStageActivity.class, bn, NEXT_STAGE);
    }

    private void openVerificationDialog() {
        if (vDialog != null && vDialog.isShowing()) {
            return;
        }
        vDialog = new AppCompatDialog(getActContext());
        LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull DialogVerificationMethodBinding vmBinding = DialogVerificationMethodBinding.inflate(inflater, null, false);

        //
        vmBinding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_VERIFICATION_METHOD_OTP_TXT"));
        vmBinding.smsVerificationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTP_VERIFICATION_BY_TXT"));
        vmBinding.smsVerificationVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTP_VERIFICATION_METHOD_SMS_TXT"));
        vmBinding.whatsAppVerificationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTP_VERIFICATION_BY_TXT"));
        vmBinding.whatsAppVerificationVTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTP_VERIFICATION_METHOD_WHATSAPP_TXT"));
        vmBinding.smsArea.setOnClickListener(v -> {
            vmBinding.cancelImg.performClick();
            nextScreen("sms");
        });
        vmBinding.whatsAppArea.setOnClickListener(v -> {
            vmBinding.cancelImg.performClick();
            nextScreen("whatsApp");
        });
        vmBinding.cancelImg.setOnClickListener(v -> {
            vDialog.dismiss();
            vDialog = null;
        });

        //
        vDialog.setContentView(vmBinding.getRoot());
        int width = (int) Utils.getScreenPixelWidth(getActContext()) - getResources().getDimensionPixelSize(R.dimen._30sdp);
        Objects.requireNonNull(vDialog.getWindow()).setLayout(width, ViewGroup.LayoutParams.WRAP_CONTENT);
        vDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(vDialog);
        Objects.requireNonNull(vDialog.getWindow()).setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        vDialog.show();
    }

    private void verifyUser() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CheckMemberAccount");
        parameters.put("MobileNo", Utils.getText(mobileBox));
        parameters.put("vPhoneCode", vPhoneCode);
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));

        nextBtn.setEnabled(false);
        Utils.hideKeyboard(getActContext());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            nextBtn.setEnabled(true);

            if (responseString != null && !responseString.equals("") && !MyApp.getInstance().isMyAppInBackGround()) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (generalFunc.getJsonValue("showEnterPassword", responseString).equalsIgnoreCase("Yes")) {

                        Bundle bn = new Bundle();
                        bn.putString("mob", "+" + vPhoneCode + Utils.getText(mobileBox));
                        bn.putString("vPhoneCode", vPhoneCode);
                        bn.putString("vCountryCode", vCountryCode);
                        bn.putString("vmobile", Utils.getText(mobileBox));
                        bn.putString("vSImage", vSImage);
                        bn.putBoolean("isRegister", true);
                        new ActUtils(getActContext()).startActForResult(PasswordStageActivity.class, bn, NEXT_STAGE);
                    } else {

                        Bundle bn = new Bundle();
                        bn.putString("mob", "+" + vPhoneCode + Utils.getText(mobileBox));
                        bn.putString("vPhoneCode", vPhoneCode);
                        bn.putString("vCountryCode", vCountryCode);
                        bn.putString("vmobile", Utils.getText(mobileBox));
                        bn.putString("vSImage", vSImage);
                        bn.putBoolean("isRegister", false);
                        new ActUtils(getActContext()).startActForResult(PasswordStageActivity.class, bn, NEXT_STAGE);
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == RC_SIGN_IN) {
            GoogleSignInResult result = Auth.GoogleSignInApi.getSignInResultFromIntent(data);
            new RegisterGoogleLoginResCallBack(getActContext()).handleSignInResult(result);
        } else if (requestCode == NEXT_STAGE) {
            manageAnimation(contentArea);
        } else {
            if (Socialpicker.callbackManager != null) {
                Socialpicker.callbackManager.onActivityResult(requestCode, resultCode, data);
            }
        }
    }
}