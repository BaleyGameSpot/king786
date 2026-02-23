package com.act;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.OpenMainProfile;
import com.general.files.PasswordViewHideManager;
import com.google.firebase.FirebaseException;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseAuthInvalidCredentialsException;
import com.google.firebase.auth.PhoneAuthCredential;
import com.google.firebase.auth.PhoneAuthOptions;
import com.google.firebase.auth.PhoneAuthProvider;
import com.buddyverse.main.R;
import com.mukesh.OtpView;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;
import com.view.MyProgressDialog;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

public class PasswordStageActivity extends ParentActivity {

    static final int NEXT_STAGE = 002;
    ImageView backBtn, nextBtn;
    MTextView titleTxt, otpHelpTitleTxt;
    View optViewArea, firebaseOTP_View, passwordArea;
    OtpView mob_otp_view;
    private boolean IS_FIREBASE = false;
    String SIGN_IN_OPTION = "";
    String phoneVerificationCode = "";
    String required_str = "";
    String error_verification_code = "";
    private MaterialEditText firebaseOTP_Txt;
    MaterialEditText passwordBox;
    private String mVerificationId;
    MTextView forgotPassTxt;
    View contentArea;
    MTextView resendBtn;
    CountDownTimer countDnTimer;
    int resendSecInMilliseconds;
    int resendSecAfter;
    boolean isProcessRunning = false;
    String LBL_RESEND_OTP_SIGNIN;
    LinearLayout llLoaderView;
    MTextView enterOtpError;
    boolean isRegister = false;
    private MyProgressDialog progressDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_password_stage);
        init();
    }

    private void init() {
        progressDialog = new MyProgressDialog(getActContext(), false, generalFunc.retrieveLangLBl("Loading", "LBL_LOADING_TXT"));
        isRegister = getIntent().getBooleanExtra("isRegister", false);
        backBtn = findViewById(R.id.backBtn);
        nextBtn = findViewById(R.id.nextBtn);
        titleTxt = findViewById(R.id.titleTxt);
        resendBtn = findViewById(R.id.resendBtn);
        resendBtn.setVisibility(View.GONE);
        otpHelpTitleTxt = findViewById(R.id.otpHelpTitleTxt);
        optViewArea = findViewById(R.id.optViewArea);
        passwordArea = findViewById(R.id.passwordArea);
        firebaseOTP_View = findViewById(R.id.firebaseOTP_View);
        forgotPassTxt = findViewById(R.id.forgotPassTxt);
        firebaseOTP_Txt = (MaterialEditText) findViewById(R.id.firebaseOTP_Txt);
        llLoaderView = (LinearLayout) findViewById(R.id.llLoaderView);
        llLoaderView.setVisibility(View.GONE);
        passwordBox = (MaterialEditText) findViewById(R.id.passwordBox);
        passwordBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
        passwordBox.setTypeface(SystemFont.FontStyle.DEFAULT.font);
        new PasswordViewHideManager(getActContext(), passwordBox, generalFunc);
        mob_otp_view = findViewById(R.id.mob_otp_view);
        contentArea = findViewById(R.id.contentArea);
        enterOtpError = findViewById(R.id.enterOtpError);
        enterOtpError.setVisibility(View.GONE);
        addToClickHandler(backBtn);
        addToClickHandler(nextBtn);
        addToClickHandler(resendBtn);
        SIGN_IN_OPTION = generalFunc.retrieveValue("SIGN_IN_OPTION");
        error_verification_code = generalFunc.retrieveLangLBl("", "LBL_VERIFICATION_CODE_INVALID");
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OTP_SENT_TITLE"));
        passwordBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_PASSWORD_LBL_TXT"));
        forgotPassTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FORGET_YOUR_PASS_TXT"));
        resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RESEND_SMS"));
        LBL_RESEND_OTP_SIGNIN = generalFunc.retrieveLangLBl("Resend code in", "LBL_RESEND_OTP_SIGNIN");
        resendSecAfter = GeneralFunctions.parseIntegerValue(30, generalFunc.getJsonValue(Utils.VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_KEY, generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        resendSecInMilliseconds = resendSecAfter * 1 * 1000;
        firebaseOTP_Txt.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP"));
        addToClickHandler(forgotPassTxt);
        if (SIGN_IN_OPTION.equalsIgnoreCase("OTP")) {
            optViewArea.setVisibility(View.VISIBLE);
            passwordArea.setVisibility(View.GONE);
            boolean isFirebase = generalFunc.retrieveValue("MOBILE_NO_VERIFICATION_METHOD").equalsIgnoreCase("Firebase");
            boolean isSMSWhats = getIntent().getStringExtra("verificationMethod").equalsIgnoreCase("whatsApp");
            if (isFirebase && !isSMSWhats) {
                IS_FIREBASE = true;
                firebaseOTP_View.setVisibility(View.VISIBLE);
                mob_otp_view.setVisibility(View.GONE);

                sendVerificationCodeFirebase(getIntent().getStringExtra("mob"));
            } else {
                mob_otp_view.setVisibility(View.VISIBLE);
                firebaseOTP_View.setVisibility(View.GONE);
                sendVerificationSMS();
            }

        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("What's the password?", "LBL_PASSWORD_TITLE"));
            optViewArea.setVisibility(View.GONE);
            passwordArea.setVisibility(View.VISIBLE);
        }
        otpHelpTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_CODE_SENT_TO_MOBILE_TXT") + getIntent().getStringExtra("mob"));
        if (generalFunc.isRTLmode()) {
            nextBtn.setRotation(180);
            backBtn.setRotation(180);
        }
        manageAnimation(contentArea);

        if (isRegister) {
            titleTxt.setText(generalFunc.retrieveLangLBl("Create password" + "", "LBL_CREATE_PASSWORD_TXT"));
            forgotPassTxt.setVisibility(View.GONE);
        }
        firebaseOTP_Txt.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

            }

            @Override
            public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {
                enterOtpError.setVisibility(View.GONE);
            }

            @Override
            public void afterTextChanged(Editable editable) {

            }
        });
    }

    private Context getActContext() {
        return PasswordStageActivity.this;
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

            if (SIGN_IN_OPTION.equalsIgnoreCase("OTP")) {

                if (IS_FIREBASE) {
                    String finalCode = Utils.getText(firebaseOTP_Txt);
                    if (phoneVerificationCode.equalsIgnoreCase(finalCode)) {
                        verifyVerificationCode(phoneVerificationCode);
                    } else {
                        verifyVerificationCode(finalCode);
                    }
                } else {
                    String finalCode = Utils.getText(mob_otp_view);
                    boolean isCodeEntered = Utils.checkText(finalCode) ?
                            ((phoneVerificationCode.equalsIgnoreCase(finalCode) ||
                                    (generalFunc.retrieveValue(Utils.SITE_TYPE_KEY).equalsIgnoreCase("Demo") && finalCode.equalsIgnoreCase("1234"))) ? true
                                    : Utils.setErrorFields(mob_otp_view, error_verification_code)) : Utils.setErrorFields(mob_otp_view, required_str);
                    if (isCodeEntered) {
                        verifyUser();
                    }
                }
            } else {

                String noWhiteSpace = generalFunc.retrieveLangLBl("Password should not contain whitespace.", "LBL_ERROR_NO_SPACE_IN_PASS");
                String pass_length = generalFunc.retrieveLangLBl("Password must be", "LBL_ERROR_PASS_LENGTH_PREFIX")
                        + " " + Utils.minPasswordLength + " " + generalFunc.retrieveLangLBl("or more character long.", "LBL_ERROR_PASS_LENGTH_SUFFIX");

                boolean passwordEntered = Utils.checkText(passwordBox) ?
                        (Utils.getText(passwordBox).contains(" ") ? Utils.setErrorFields(passwordBox, noWhiteSpace)
                                : (Utils.getText(passwordBox).length() >= Utils.minPasswordLength ? true : Utils.setErrorFields(passwordBox, pass_length)))
                        : Utils.setErrorFields(passwordBox, required_str);
                if (!passwordEntered) {
                    return;
                }
                verifyUser();
            }


        } else if (i == forgotPassTxt.getId()) {
            Bundle bn = new Bundle();
            bn.putString("vmobile", getIntent().getStringExtra("vmobile"));
            bn.putString("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
            bn.putString("vCountryCode", getIntent().getStringExtra("vCountryCode"));
            bn.putString("vSImage", getIntent().getStringExtra("vSImage"));
            new ActUtils(getActContext()).startActWithData(ForgotPasswordActivity.class, bn);
        } else if (i == resendBtn.getId()) {

            if (IS_FIREBASE) {
                sendVerificationCodeFirebase(getIntent().getStringExtra("mob"));
            } else {
                sendVerificationSMS();
            }

        }
    }

    private void verifyVerificationCode(String code) {
        if (code.equalsIgnoreCase("")) {
            enterOtpError.setVisibility(View.VISIBLE);
            enterOtpError.setText(required_str);
//            Utils.setErrorFields(firebaseOTP_Txt, required_str);
        } else {
            try {
                PhoneAuthCredential credential = PhoneAuthProvider.getCredential(mVerificationId, code);
                signInWithPhoneAuthCredential(credential);
            } catch (Exception e) {
                enterOtpError.setVisibility(View.VISIBLE);
                enterOtpError.setText(error_verification_code);
//                Utils.setErrorFields(firebaseOTP_Txt, error_verification_code);
            }
        }
    }

    private void signInWithPhoneAuthCredential(PhoneAuthCredential credential) {
        FirebaseAuth.getInstance().signInWithCredential(credential)
                .addOnCompleteListener(PasswordStageActivity.this, task -> {
                    if (task.isSuccessful()) {
                        verifyUser();
                    } else {
                        if (task.getException() instanceof FirebaseAuthInvalidCredentialsException) {
                            Utils.setErrorFields(firebaseOTP_Txt, error_verification_code);
                        }
                    }
                });
    }

    private void sendVerificationCodeFirebase(String phoneNum) {
        progressDialog.show();
        PhoneAuthOptions options = PhoneAuthOptions.newBuilder(FirebaseAuth.getInstance())
                .setPhoneNumber("+" + phoneNum).setTimeout(60L, TimeUnit.SECONDS).setActivity(this)
                .setCallbacks(new PhoneAuthProvider.OnVerificationStateChangedCallbacks() {
                    @Override
                    public void onCodeSent(@NonNull String s, @NonNull PhoneAuthProvider.ForceResendingToken forceResendingToken) {
                        super.onCodeSent(s, forceResendingToken);
                        mVerificationId = s;
                        progressDialog.close();
                        resendBtn.setVisibility(View.VISIBLE);
                        showTimer();

                    }

                    @Override
                    public void onCodeAutoRetrievalTimeOut(@NonNull String s) {
                        super.onCodeAutoRetrievalTimeOut(s);
                        resendBtn.setVisibility(View.VISIBLE);
                        progressDialog.close();
                        resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_AUTH_FAILED_TRY_LATER"));
                    }

                    @Override
                    public void onVerificationCompleted(@NonNull PhoneAuthCredential phoneAuthCredential) {
                        progressDialog.close();
                        String code = phoneAuthCredential.getSmsCode();
                        if (code != null) {
                            phoneVerificationCode = code;
                        }
                    }

                    @Override
                    public void onVerificationFailed(@NonNull FirebaseException e) {
                        Logger.d("onVerificationFailed", "::" + e.getMessage());
                        progressDialog.close();
                        resendBtn.setVisibility(View.VISIBLE);
                        resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_AUTH_FAILED_TRY_LATER"));
                    }
                }).build();
        PhoneAuthProvider.verifyPhoneNumber(options);
    }

    private void verifyUser() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "AuthenticateMember");
        parameters.put("MobileNo", getIntent().getStringExtra("vmobile"));
        parameters.put("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
        parameters.put("UserType", Utils.app_type);

        // (ChangeLanguageToDefault,ChangeCurrencyToDefault) these keys will decide if the language and currency will change to default when default values a sre selected manually.
        parameters.put("IS_LANGUAGE_CHANGED", MyUtils.isLangChanged());
        parameters.put("IS_CURRENCY_CHANGED", MyUtils.isCurrencyChanged());

        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        if (isRegister) {
            parameters.put("isNewPassword", "Yes");
        } else {
            parameters.put("isNewPassword", "No");
        }
        if (SIGN_IN_OPTION.equalsIgnoreCase("Password")) {
            parameters.put("vPassword", Utils.getText(passwordBox));
        }

        llLoaderView.setVisibility(View.VISIBLE);
        nextBtn.setEnabled(false);
        Utils.hideKeyboard(getActContext());

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {
            nextBtn.setEnabled(true);
            llLoaderView.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    MyUtils.ShowIntroScreens();
                    if (generalFunc.getJsonValue("IS_REGISTERED", responseString).equalsIgnoreCase("Yes")) {
                        if (generalFunc.retrieveValue("isSmartLogin") != null && generalFunc.retrieveValue("isSmartLogin").equalsIgnoreCase("Yes")) {
                            generalFunc.storeData("isUserSmartLogin", "Yes");
                        } else {
                            generalFunc.storeData("isUserSmartLogin", "No");
                        }
                        new ConfigureMemberData(responseString, generalFunc, getActContext(), true);

                        generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                        manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                        new OpenMainProfile(getActContext(), generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();

                        MyUtils.configLangChanged("No");
                        MyUtils.configCurrencyChanged("No");
                    } else {
                        Bundle bn = new Bundle();
                        bn.putString("mob", "+" + getIntent().getStringExtra("mob"));
                        bn.putString("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
                        bn.putString("vCountryCode", getIntent().getStringExtra("vCountryCode"));
                        bn.putString("vmobile", getIntent().getStringExtra("vmobile"));
                        bn.putString("vPassword", Utils.getText(passwordBox));
                        new ActUtils(getActContext()).startActForResult(NameStageActivity.class, bn, NEXT_STAGE);
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void sendVerificationSMS() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendAuthOtp");
        parameters.put("MobileNo", getIntent().getStringExtra("vmobile"));
        parameters.put("vPhoneCode", getIntent().getStringExtra("vPhoneCode"));
        parameters.put("verificationMethod", getIntent().getStringExtra("verificationMethod"));
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            resendBtn.setVisibility(View.VISIBLE);
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    showTimer();
                    phoneVerificationCode = generalFunc.getJsonValue(Utils.message_str, responseString);
                } else {
                    resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
            }
        });

    }

    private void showTimer() {
        countDnTimer = new CountDownTimer(resendSecInMilliseconds, 1000) {
            @Override
            public void onTick(long milliseconds) {
                isProcessRunning = true;
                setTime(milliseconds);
                enableOrDisable(false);
            }

            @Override
            public void onFinish() {
                isProcessRunning = false;
                // this function will be called when the timecount is finished
                enableOrDisable(true);
                removecountDownTimer();
            }
        }.start();
    }

    private void removecountDownTimer() {
        if (countDnTimer != null) {
            countDnTimer.cancel();
            countDnTimer = null;
            isProcessRunning = false;
        }
    }

    private void setButtonEnabled(MTextView btn, boolean setEnable) {
        btn.setFocusableInTouchMode(setEnable);
        btn.setFocusable(setEnable);
        btn.setEnabled(setEnable);
        if (setEnable) {
            addToClickHandler(btn);
        } else {
            btn.setOnClickListener(null);
        }

        btn.setTextColor(setEnable ? getActContext().getResources().getColor(R.color.appThemeColor_1) : Color.parseColor("#BABABA"));
        btn.setClickable(setEnable);
    }

    private void enableOrDisable(boolean activate) {
        setButtonEnabled(resendBtn, activate);
        if (activate) {
            resendBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RESEND_SMS"));
        }
    }

    private void setTime(long milliseconds) {
        int minutes = (int) (milliseconds / 1000) / 60;
        int seconds = (int) (milliseconds / 1000) % 60;

        String formattedTxt = String.format(Locale.ENGLISH, "%02d:%02d", minutes, seconds);
        int color = Color.parseColor("#000000");

        formattedTxt = LBL_RESEND_OTP_SIGNIN + " " + formattedTxt;

        resendBtn.setTextColor(color);
        resendBtn.setText(formattedTxt);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable @org.jetbrains.annotations.Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        manageAnimation(contentArea);
    }
}