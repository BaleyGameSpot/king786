package com.act;

import static android.text.Spanned.SPAN_INCLUSIVE_INCLUSIVE;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.os.Handler;
import android.text.Editable;
import android.text.InputType;
import android.text.SpannableString;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.text.style.AbsoluteSizeSpan;
import android.text.style.ForegroundColorSpan;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatDialog;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.countryview.view.CountryPicker;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.gms.tasks.OnCompleteListener;
import com.google.android.gms.tasks.Task;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.firebase.FirebaseException;
import com.google.firebase.auth.AuthResult;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseAuthInvalidCredentialsException;
import com.google.firebase.auth.PhoneAuthCredential;
import com.google.firebase.auth.PhoneAuthOptions;
import com.google.firebase.auth.PhoneAuthProvider;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DialogVerificationMethodBinding;
import com.model.ServiceModule;
import com.mukesh.OtpView;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.editBox.MaterialEditText;

import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;
import java.util.concurrent.TimeUnit;

public class VerifyInfoActivity extends ParentActivity {

    RelativeLayout emailView;
    RelativeLayout smsView;
    ProgressBar loading;

    ImageView backImgView;
    String required_str = "";
    String error_verification_code = "";
    MTextView titleTxt;

    MButton okBtn, emailOkBtn, mobContinueBtn, emailContinueBtn;
    MButton emailResendBtn;
    MTextView resendBtn;

    Bundle bundle;
    String reqType = "";
    String vEmail = "", vPhone = "";

    String phoneVerificationCode = "";
    String emailVerificationCode = "";

    MTextView phonetxt;
    MTextView emailTxt;
    MTextView smsOTPTitleTxt;


    boolean isEditInfoTapped = false;
    CountDownTimer countDnTimer;
    CountDownTimer countDnEmailTimer;

    int resendTime = 0;
    int resendSecAfter;
    int maxAllowdCount;
    int resendSecInMilliseconds;
    boolean isProcessRunning = false;
    boolean isEmailSendProcessRunning = false;

    // Edit Email Or Number
    boolean isDialogOpen = false;
    private String error_email_str = "";
    BottomSheetDialog editInfoDialog;
    boolean isCountrySelected = false;
    private String vPhoneCode;
    private String vCountryCode;
    String msg = "";
    ImageView logoutImageview;
    String LBL_RESEND_SMS = "", LBL_RESEND_EMAIL = "";
    private boolean isbackshow = true, isrestart = false;

    LinearLayout mobOtpArea, mobEditArea;
    LinearLayout emailEditArea, emailOtpArea;
    OtpView mob_otp_view, email_otp_view;
    CountryPicker countryPicker;
    private boolean IS_FIREBASE = false;
    private MaterialEditText firebaseOTP_Txt;
    private LinearLayout firebaseOTP_View;
    private String mVerificationId;
    private FirebaseAuth mAuth;
    MTextView enterOtpError;
    MTextView helpOTPTxtView;
    private AppCompatDialog vDialog;
    private String verificationMethod = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_verify_info);


        LBL_RESEND_SMS = generalFunc.retrieveLangLBl("", "LBL_RESEND_SMS");
        LBL_RESEND_EMAIL = generalFunc.retrieveLangLBl("", "LBL_RESEND_EMAIL");

        bundle = new Bundle();
        bundle = getIntent().getExtras();
        msg = bundle.getString("msg");

        resendSecAfter = GeneralFunctions.parseIntegerValue(30, generalFunc.getJsonValue(Utils.VERIFICATION_CODE_RESEND_TIME_IN_SECONDS_KEY, generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        maxAllowdCount = GeneralFunctions.parseIntegerValue(5, generalFunc.getJsonValue(Utils.VERIFICATION_CODE_RESEND_COUNT_KEY, generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        resendTime = GeneralFunctions.parseIntegerValue(30, generalFunc.getJsonValue(Utils.VERIFICATION_CODE_RESEND_COUNT_RESTRICTION_KEY, generalFunc.retrieveValue(Utils.USER_PROFILE_JSON)));
        resendSecInMilliseconds = resendSecAfter * 1 * 1000;

        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        loading = (ProgressBar) findViewById(R.id.loading);

        mAuth = FirebaseAuth.getInstance();
        firebaseOTP_View = (LinearLayout) findViewById(R.id.firebaseOTP_View);
        firebaseOTP_View.setVisibility(View.GONE);

        firebaseOTP_Txt = (MaterialEditText) findViewById(R.id.firebaseOTP_Txt);
        firebaseOTP_Txt.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP"));

        mob_otp_view = (OtpView) findViewById(R.id.otp_view);
        mob_otp_view.setOtpCompletionListener(otp -> okBtn.performClick());

        email_otp_view = (OtpView) findViewById(R.id.email_otp_view);
        email_otp_view.setOtpCompletionListener(otp -> emailOkBtn.performClick());

        enterOtpError = findViewById(R.id.enterOtpError);
        enterOtpError.setVisibility(View.GONE);
        helpOTPTxtView = findViewById(R.id.helpOTPTxtView);

        if (generalFunc.isRTLmode()) {
            email_otp_view.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
            mob_otp_view.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
            backImgView.setRotation(180);
        }

        mobOtpArea = (LinearLayout) findViewById(R.id.mobOtpArea);
        mobEditArea = (LinearLayout) findViewById(R.id.mobEditArea);
        emailEditArea = (LinearLayout) findViewById(R.id.emailEditArea);
        emailOtpArea = (LinearLayout) findViewById(R.id.emailOtpArea);

        smsOTPTitleTxt = ((MTextView) findViewById(R.id.smsOTPTitleTxt));
        smsOTPTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_DIGIT_OTP_TXT"));
        phonetxt = ((MTextView) findViewById(R.id.phoneTxt));
        emailTxt = ((MTextView) findViewById(R.id.emailTxt));

        if (!getIntent().hasExtra("MOBILE")) {
            vEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
            vPhone = generalFunc.getJsonValueStr("vCode", obj_userProfile) + generalFunc.getJsonValueStr("vPhone", obj_userProfile);
        } else {
            vPhone = getIntent().getStringExtra("MOBILE");
        }

        emailView = (RelativeLayout) findViewById(R.id.emailView);
        smsView = (RelativeLayout) findViewById(R.id.smsView);

        if (msg.equalsIgnoreCase("DO_EMAIL_PHONE_VERIFY")) {
            emailView.setVisibility(View.VISIBLE);
            smsView.setVisibility(View.VISIBLE);
            reqType = "DO_EMAIL_PHONE_VERIFY";
        } else if (msg.equalsIgnoreCase("DO_EMAIL_VERIFY")) {
            emailView.setVisibility(View.VISIBLE);
            smsView.setVisibility(View.GONE);
            reqType = "DO_EMAIL_VERIFY";
        } else if (msg.equalsIgnoreCase("DO_PHONE_VERIFY")) {
            smsView.setVisibility(View.VISIBLE);
            emailView.setVisibility(View.GONE);
            reqType = "DO_PHONE_VERIFY";
        }

        okBtn = ((MaterialRippleLayout) findViewById(R.id.okBtn)).getChildView();
        mobContinueBtn = ((MaterialRippleLayout) findViewById(R.id.mobContinueBtn)).getChildView();
        emailContinueBtn = ((MaterialRippleLayout) findViewById(R.id.emailContinueBtn)).getChildView();
        resendBtn = (MTextView) findViewById(R.id.resendBtn);

        emailOkBtn = ((MaterialRippleLayout) findViewById(R.id.emailOkBtn)).getChildView();
        emailResendBtn = ((MaterialRippleLayout) findViewById(R.id.emailResendBtn)).getChildView();

        okBtn.setId(Utils.generateViewId());
        addToClickHandler(okBtn);
        mobContinueBtn.setId(Utils.generateViewId());
        addToClickHandler(mobContinueBtn);
        emailContinueBtn.setId(Utils.generateViewId());
        addToClickHandler(emailContinueBtn);
        addToClickHandler(resendBtn);
        addToClickHandler(mobEditArea);
        addToClickHandler(emailEditArea);
        emailOkBtn.setId(Utils.generateViewId());
        addToClickHandler(emailOkBtn);
        addToClickHandler(emailResendBtn);
        helpOTPTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_EMAIL_NOTE_DEMO"));


        setLabels();

        String isBackShow = getIntent().getStringExtra("isbackshow");
        if (isBackShow != null && isBackShow.equalsIgnoreCase("No")) {
            isbackshow = false;
        }


        isrestart = getIntent().getBooleanExtra("isrestart", false);


        if (ServiceModule.isDeliverAllOnly() && (!isbackshow || isrestart)) {
            logoutImageview = (ImageView) findViewById(R.id.logoutImageview);
            logoutImageview.setVisibility(View.VISIBLE);
            addToClickHandler(logoutImageview);
            backImgView.setVisibility(View.GONE);
            backImgView.setOnClickListener(null);
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

    private void handleSendSms() {
        verificationMethod = "";
        if (msg.equalsIgnoreCase("DO_EMAIL_PHONE_VERIFY")) {
            sendVerificationSMS("Both");
        } else if (msg.equalsIgnoreCase("DO_EMAIL_VERIFY")) {
            sendVerificationSMS("Email");
        } else if (msg.equalsIgnoreCase("DO_PHONE_VERIFY")) {
            if (generalFunc.retrieveValue("ENABLE_WHATSAPP_OTP_VERIFICATION").equalsIgnoreCase("Yes")) {
                openVerificationDialog();
            } else {
                sendVerificationSMS("Mobile");
            }
        }
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
            verificationMethod = "sms";
            sendVerificationSMS("Mobile");
        });
        vmBinding.whatsAppArea.setOnClickListener(v -> {
            vmBinding.cancelImg.performClick();
            verificationMethod = "whatsApp";
            sendVerificationSMS("Mobile");
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

    @Override
    protected void onResume() {
        super.onResume();
    }


    public void manageSmsHelpTxt(Boolean isDefault) {

        if (isDefault) {
            String text1 = generalFunc.retrieveLangLBl("", "LBL_MOB_VERIFICATION_NOTE") + " ";
            String text2 = generalFunc.retrieveLangLBl("", "LBL_OTP_TO_VERIFY");
            SpannableString span1 = new SpannableString(text1);
            span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);

            SpannableString span2 = new SpannableString(text2);
            span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
            span2.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)), 0, text2.length(), 0);
            CharSequence finalText = TextUtils.concat(span1, "", span2);

            ((MTextView) findViewById(R.id.smsHelpTitleTxt)).setText(finalText);
        } else {
            String text1 = generalFunc.retrieveLangLBl("", "LBL_ENTER _OTP_NOTE") + " ";
            if (IS_FIREBASE) {
                text1 = generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_NOTE_FIREBASE") + " ";
            }
            String text2 = "+" + vPhone;
            SpannableString span1 = new SpannableString(text1);
            span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);

            SpannableString span2 = new SpannableString(text2);
            span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
            span2.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)), 0, text2.length(), 0);
            CharSequence finalText = TextUtils.concat(span1, "", span2);

            ((MTextView) findViewById(R.id.smsHelpTitleTxt)).setText(finalText);

        }

    }

    public void manageEmailHelpTxt(Boolean isDefault) {

        if (isDefault) {
            String text1 = generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFICATION_NOTE") + " ";
            String text2 = generalFunc.retrieveLangLBl("", "LBL_OTP_TO_VERIFY");
            SpannableString span1 = new SpannableString(text1);
            span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);

            SpannableString span2 = new SpannableString(text2);
            span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
            span2.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)), 0, text2.length(), 0);
            CharSequence finalText = TextUtils.concat(span1, "", span2);

            ((MTextView) findViewById(R.id.emailHelpTitleTxt)).setText(finalText);
        } else {
            String text1 = generalFunc.retrieveLangLBl("", "LBL_ENTER _OTP_NOTE") + " ";
            if (IS_FIREBASE) {
                text1 = generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_NOTE_FIREBASE") + " ";
            }
            String text2 = vEmail;
            SpannableString span1 = new SpannableString(text1);
            span1.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text1.length(), SPAN_INCLUSIVE_INCLUSIVE);

            SpannableString span2 = new SpannableString(text2);
            span2.setSpan(new AbsoluteSizeSpan(Utils.dpToPx(12, getActContext())), 0, text2.length(), SPAN_INCLUSIVE_INCLUSIVE);
            span2.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)), 0, text2.length(), 0);
            CharSequence finalText = TextUtils.concat(span1, "", span2);

            ((MTextView) findViewById(R.id.emailHelpTitleTxt)).setText(finalText);

        }

    }

    private void setLabels() {


        manageSmsHelpTxt(true);
        manageEmailHelpTxt(true);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_VERIFY_TXT"));
        ((MTextView) findViewById(R.id.smsTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_VERIFy_TXT"));

        ((MTextView) findViewById(R.id.emailTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_VERIFy_TXT"));


        ((MTextView) findViewById(R.id.emailSubTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL") + " ");

        // ((MTextView) findViewById(R.id.emailHelpTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_EMAIL_SENT_NOTE"));
        ((MTextView) findViewById(R.id.smsSubTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HINT_TXT"));
        ((MTextView) findViewById(R.id.smsOTPTitleTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_DIGIT_OTP_TXT"));

        ((MTextView) findViewById(R.id.phoneTxt)).setText("+" + vPhone);
        ((MTextView) findViewById(R.id.emailTxt)).setText(vEmail);

        okBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_TXT"));
        resendBtn.setText(LBL_RESEND_SMS);


        emailOkBtn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        mobContinueBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        emailContinueBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
        emailResendBtn.setText(LBL_RESEND_EMAIL);


        error_verification_code = generalFunc.retrieveLangLBl("", "LBL_VERIFICATION_CODE_INVALID");
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR_TXT");
    }

    public void sendVerificationSMS(String showTimerFor) {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "sendVerificationSMS");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("MobileNo", vPhone);
        parameters.put("UserType", Utils.app_type);
        parameters.put("REQ_TYPE", reqType);

        if (Utils.checkText(verificationMethod)) {
            parameters.put("verificationMethod", verificationMethod);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            loading.setVisibility(View.GONE);

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    switch (reqType) {
                        case "DO_EMAIL_PHONE_VERIFY" -> {
                            if (!generalFunc.getJsonValue(Utils.message_str, responseString).equals("")) {
                                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("",
                                        generalFunc.getJsonValue(Utils.message_str, responseString)));
                            } else {
                                if (!generalFunc.getJsonValue(Utils.message_str + "_sms", responseString).equalsIgnoreCase("LBL_MOBILE_VERIFICATION_FAILED_TXT")) {
                                    phoneVerificationCode = generalFunc.getJsonValue(Utils.message_str + "_sms", responseString);
                                } else {
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("",
                                            generalFunc.getJsonValue(Utils.message_str + "_sms", responseString)));
                                }
                                if (!generalFunc.getJsonValue(Utils.message_str + "_email", responseString).equalsIgnoreCase("LBL_EMAIL_VERIFICATION_FAILED_TXT")) {
                                    emailVerificationCode = generalFunc.getJsonValue(Utils.message_str + "_email", responseString);
                                } else {
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("",
                                            generalFunc.getJsonValue(Utils.message_str + "_email", responseString)));
                                }
                            }
                        }
                        case "DO_EMAIL_VERIFY" -> {
                            emailVerificationCode = generalFunc.getJsonValue(Utils.message_str, responseString);
                            emailEditArea.setVisibility(View.GONE);
                            showDemoOtpNote(true, false);
                            emailOtpArea.setVisibility(View.VISIBLE);
                            manageEmailHelpTxt(false);
                        }
                        case "DO_PHONE_VERIFY" -> {
                            IS_FIREBASE = false;
                            boolean isSMSWhats = verificationMethod.equalsIgnoreCase("whatsApp");
                            if (!isSMSWhats && generalFunc.getJsonValue("MOBILE_NO_VERIFICATION_METHOD", responseString).equals("Firebase")) {
                                IS_FIREBASE = true;
                                mob_otp_view.setVisibility(View.GONE);
                                firebaseOTP_View.setVisibility(View.VISIBLE);
                                if (Utils.checkText(generalFunc.getJsonValue("Phone", responseString))) {
                                    sendVerificationCodeFirebase(generalFunc.getJsonValue("Phone", responseString));
                                }
                            }
                            smsOTPTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_DIGIT_OTP_TXT").replace("###", IS_FIREBASE ? "6" : "4"));
                            phoneVerificationCode = generalFunc.getJsonValue(Utils.message_str, responseString);
                            showDemoOtpNote(true, true);
                            mobOtpArea.setVisibility(View.VISIBLE);
                            mobEditArea.setVisibility(View.GONE);
                            manageSmsHelpTxt(false);
                        }
                        case "PHONE_VERIFIED" -> {
                            enableOrDisable(true, showTimerFor);
                            removecountDownTimer("Mobile");
                            isProcessRunning = false;
                            verifySuccessMessage(generalFunc.retrieveLangLBl("",
                                    generalFunc.getJsonValue(Utils.message_str, responseString)), true, false);
                        }
                        case "EMAIL_VERIFIED" -> {
                            enableOrDisable(true, showTimerFor);
                            removecountDownTimer("Email");
                            isEmailSendProcessRunning = false;
                            verifySuccessMessage(generalFunc.retrieveLangLBl("",
                                    generalFunc.getJsonValue(Utils.message_str, responseString)), false, true);
                        }
                    }
                    String userdetails = generalFunc.getJsonValue("userDetails", responseString);
                    if (!userdetails.equals("") && userdetails != null) {
                        String messageData = generalFunc.getJsonValue(Utils.message_str, userdetails);
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, messageData);
                    }
                    checkVerification(responseString, isDataAvail, showTimerFor);
                } else {
                    checkVerification(responseString, isDataAvail, showTimerFor);
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));

                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void sendVerificationCodeFirebase(String phoneNum) {
        PhoneAuthOptions options = PhoneAuthOptions.newBuilder(mAuth)
                .setPhoneNumber("+" + phoneNum).setTimeout(60L, TimeUnit.SECONDS).setActivity(this)
                .setCallbacks(new PhoneAuthProvider.OnVerificationStateChangedCallbacks() {
                    @Override
                    public void onCodeSent(@NonNull String s, @NonNull PhoneAuthProvider.ForceResendingToken forceResendingToken) {
                        super.onCodeSent(s, forceResendingToken);
                        mVerificationId = s;
                    }

                    @Override
                    public void onVerificationCompleted(@NonNull PhoneAuthCredential phoneAuthCredential) {
                        String code = phoneAuthCredential.getSmsCode();
                        if (code != null) {
                            phoneVerificationCode = code;
                        }
                    }

                    @Override
                    public void onVerificationFailed(FirebaseException e) {
                        Logger.d("onVerificationFailed", "::" + e.getMessage());
                    }
                }).build();
        PhoneAuthProvider.verifyPhoneNumber(options);
    }

    private void verifyVerificationCode(String code) {
        if (code.equalsIgnoreCase("")) {
            enterOtpError.setVisibility(View.VISIBLE);
            enterOtpError.setText(required_str);
//            Utils.setErrorFields(firebaseOTP_Txt, error_verification_code);
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
        mAuth.signInWithCredential(credential)
                .addOnCompleteListener(VerifyInfoActivity.this, new OnCompleteListener<AuthResult>() {
                    @Override
                    public void onComplete(@NonNull Task<AuthResult> task) {
                        if (task.isSuccessful()) {
                            reqType = "PHONE_VERIFIED";
                            sendVerificationSMS("");
                        } else {
                            if (task.getException() instanceof FirebaseAuthInvalidCredentialsException) {
                                Utils.setErrorFields(firebaseOTP_Txt, error_verification_code);
                            }
                        }
                    }
                });
    }

    public void showDemoOtpNote(boolean isShow, boolean isMobile) {

        if (generalFunc.retrieveValue(Utils.SITE_TYPE_KEY).equalsIgnoreCase("Demo")) {
            if (isShow) {
                if (isMobile) {
                    if (IS_FIREBASE) {
                        helpOTPTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_FIREBASE_NOTE_DEMO"));
                    } else {
                        helpOTPTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_NOTE_DEMO"));
                    }
                } else {
                    helpOTPTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_OTP_EMAIL_NOTE_DEMO"));
                }
            }
            helpOTPTxtView.setVisibility(isShow ? View.VISIBLE : View.GONE);
        } else {
            helpOTPTxtView.setVisibility(View.GONE);
        }

    }

    private void checkVerification(String responseString, boolean isDataAvail, String showTimerFor) {
        boolean isEmailVerified = generalFunc.getJsonValue("eEmailFailed", responseString).equals("Yes");
        switch (reqType) {

            case "DO_EMAIL_PHONE_VERIFY":
                boolean isSMSVerified = generalFunc.getJsonValue("eSMSFailed", responseString).equals("Yes");

                if (isEmailVerified && isSMSVerified) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Both");
                } else if (isEmailVerified) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Email");
                    resendProcess("Mobile");
                } else if (isSMSVerified) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Mobile");
                    resendProcess("Email");
                } else if (isDataAvail) {
                    resendProcess(showTimerFor);
                } else if (!isDataAvail) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Both");
                }
                break;
            case "DO_EMAIL_VERIFY":
                if (isEmailVerified) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Email");
                } else if (isDataAvail) {
                    resendProcess(showTimerFor);
                } else if (!isDataAvail) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Email");
                }
                break;
            case "DO_PHONE_VERIFY":
                if (isEmailVerified) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Mobile");
                    break;
                } else if (isDataAvail) {
                    resendProcess(showTimerFor);
                } else if (!isDataAvail) {
                    enableOrDisable(true, showTimerFor);
                    removecountDownTimer("Mobile");
                }
        }
    }

    public void verifySuccessMessage(String message, final boolean sms, final boolean email) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            if (TextUtils.isEmpty(generalFunc.getMemberId())) {
                if (TextUtils.isEmpty(generalFunc.getMemberId())) {
                    isProcessRunning = false;
                    processBackEvent(true);
                }
            } else {
                if (sms == true) {
                    smsView.setVisibility(View.GONE);
                    isProcessRunning = false;
                    if (emailView.getVisibility() == View.GONE) {
                        processBackEvent(false);
                    }
                } else if (email == true) {
                    emailView.setVisibility(View.GONE);
                    isProcessRunning = false;
                    if (smsView.getVisibility() == View.GONE) {
                        processBackEvent(false);
                    }
                }
            }
        });
        generateAlert.setContentMessage("", message);
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.showAlertBox();
    }


    private void processBackEvent(boolean normalFlow) {
        if (isrestart) {
            MyApp.getInstance().restartWithGetDataApp();
        } else if (!isbackshow) {
            new ActUtils(getActContext()).setOkResult();
            finish();
        } else {
            if (normalFlow) {
                new ActUtils(getActContext()).setOkResult();
            }
            VerifyInfoActivity.super.onBackPressed();
        }
    }


    public void resendProcess(final String showTimerFor) {

        if (!Utils.checkText(showTimerFor)) {
            enableOrDisable(true, showTimerFor);
            removecountDownTimer(showTimerFor);
            return;
        }


        enableOrDisable(false, showTimerFor);

        if (Utils.checkText(showTimerFor)) {
            setTime(generalFunc.parseLongValue(0L, String.valueOf(resendSecInMilliseconds)), showTimerFor);
            removecountDownTimer(showTimerFor);

            if (showTimerFor.equalsIgnoreCase("Email")) {
                showEmailTimer(showTimerFor);
            } else if (showTimerFor.equalsIgnoreCase("Mobile")) {
                showTimer(showTimerFor);
            } else if (showTimerFor.equalsIgnoreCase("Both")) {
                showTimer("Mobile");
                showEmailTimer("Email");
            }

        } else {
            Handler handler = new Handler();
            handler.postDelayed(new Runnable() {
                @Override
                public void run() {
                    enableOrDisable(true, showTimerFor);
                }
            }, resendSecInMilliseconds);
        }
    }

    private void setTime(long milliseconds, String showTimerFor) {
        int minutes = (int) (milliseconds / 1000) / 60;
        int seconds = (int) (milliseconds / 1000) % 60;

        String formattedTxt = String.format(Locale.ENGLISH, "%02d:%02d", minutes, seconds);
        int color = Color.parseColor("#FFFFFF");

        if (showTimerFor.equalsIgnoreCase("Both")) {
            resendBtn.setTextColor(color);
            emailResendBtn.setTextColor(color);

            resendBtn.setText(formattedTxt);
            emailResendBtn.setText(formattedTxt);

        } else if (showTimerFor.equalsIgnoreCase("Email")) {
            emailResendBtn.setTextColor(color);
            emailResendBtn.setText(formattedTxt);
        } else if (showTimerFor.equalsIgnoreCase("Mobile")) {
            resendBtn.setTextColor(color);
            resendBtn.setText(formattedTxt);
        }

    }

    public void showTimer(String showTimerFor) {
        countDnTimer = new CountDownTimer(resendSecInMilliseconds, 1000) {
            @Override
            public void onTick(long milliseconds) {
                isProcessRunning = true;
                setTime(milliseconds, showTimerFor);
            }

            @Override
            public void onFinish() {
                isProcessRunning = false;
                // this function will be called when the timecount is finished
                enableOrDisable(true, showTimerFor);
                removecountDownTimer("Mobile");
            }
        }.start();

    }


    public void showEmailTimer(String showTimerFor) {


        countDnEmailTimer = new CountDownTimer(resendSecInMilliseconds, 1000) {
            @Override
            public void onTick(long milliseconds) {
                isEmailSendProcessRunning = true;
                setTime(milliseconds, showTimerFor);
            }

            @Override
            public void onFinish() {
                isEmailSendProcessRunning = false;
                // this function will be called when the timecount is finished
                enableOrDisable(true, showTimerFor);
                removecountDownTimer("Email");
            }
        }.start();

    }

    private void removecountDownTimer(String type) {

        if (type.equalsIgnoreCase("Mobile")) {
            if (countDnTimer != null) {
                countDnTimer.cancel();
                countDnTimer = null;
                isProcessRunning = false;
            }
        } else if (type.equalsIgnoreCase("Email")) {
            if (countDnEmailTimer != null) {
                countDnEmailTimer.cancel();
                countDnEmailTimer = null;
                isEmailSendProcessRunning = false;
            }
        } else if (type.equalsIgnoreCase("Both")) {
            if (countDnTimer != null) {
                countDnTimer.cancel();
                countDnTimer = null;
                isProcessRunning = false;
            }

            if (countDnEmailTimer != null) {
                countDnEmailTimer.cancel();
                countDnEmailTimer = null;
                isEmailSendProcessRunning = false;
            }
        }

    }

    public Context getActContext() {
        return VerifyInfoActivity.this;
    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(VerifyInfoActivity.this);
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == okBtn.getId()) {
            if (IS_FIREBASE) {
                String finalCode = Utils.getText(firebaseOTP_Txt);
                if ((generalFunc.retrieveValue(Utils.SITE_TYPE_KEY).equalsIgnoreCase("Demo") && finalCode.equalsIgnoreCase("123456"))) {
                    reqType = "PHONE_VERIFIED";
                    sendVerificationSMS("");
                    return;
                }
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
                    reqType = "PHONE_VERIFIED";
                    sendVerificationSMS("");
                }
            }
        } else if (i == resendBtn.getId()) {
            reqType = "DO_PHONE_VERIFY";
            sendVerificationSMS("Mobile");
        } else if (i == mobEditArea.getId()) {
            Bundle bn = new Bundle();
            bn.putBoolean("isEdit", true);
            bn.putBoolean("isMobile", true);
            isEditInfoTapped = true;
            openEditDilaog("Mobile");
        } else if (i == emailOkBtn.getId()) {

            String finalCode = Utils.getText(email_otp_view);
            boolean isEmailCodeEntered = Utils.checkText(finalCode) ?
                    ((emailVerificationCode.equalsIgnoreCase(finalCode) ||
                            (generalFunc.retrieveValue(Utils.SITE_TYPE_KEY).equalsIgnoreCase("Demo") && finalCode.equalsIgnoreCase("1234"))) ? true
                            : Utils.setErrorFields(email_otp_view, error_verification_code)) : Utils.setErrorFields(email_otp_view, required_str);
            if (isEmailCodeEntered) {
                reqType = "EMAIL_VERIFIED";
                sendVerificationSMS("");
            }
        } else if (i == emailResendBtn.getId()) {
            reqType = "DO_EMAIL_VERIFY";
            sendVerificationSMS("Email");
        } else if (i == emailEditArea.getId()) {
            isEditInfoTapped = true;
            openEditDilaog("Email");
        } else if (i == R.id.logoutImageview) {
            MyApp.getInstance().logOutFromDevice(false);

        } else if (i == mobContinueBtn.getId()) {
            handleSendSms();
        } else if (i == emailContinueBtn.getId()) {
            handleSendSms();
        }
    }


    public void openEditDilaog(String type) {
        // Reset Country Selection

        isCountrySelected = false;
        vPhoneCode = "";
        vCountryCode = "";

        editInfoDialog = new BottomSheetDialog(getActContext());
        View contentView = View.inflate(getActContext(), R.layout.design_edit_phn_email_dialog, null);

        editInfoDialog.setContentView(contentView);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(1500);
        View bottomSheetView = editInfoDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        setCancelable(editInfoDialog, false);

        MTextView titleTxt, hintTxt, errorTxt, updateEmailTxt, updateMobileTxt, cancelTxt;
        LinearLayout updateEmailArea, updateMobileArea;
        ImageView iv_img_icon;
        EditText mobileBox, countryBox, emailBox;

        titleTxt = (MTextView) editInfoDialog.findViewById(R.id.titleTxt);
        hintTxt = (MTextView) editInfoDialog.findViewById(R.id.hintTxt);
        errorTxt = (MTextView) editInfoDialog.findViewById(R.id.errorTxt);
        iv_img_icon = (ImageView) editInfoDialog.findViewById(R.id.iv_img_icon);
        updateEmailTxt = (MTextView) editInfoDialog.findViewById(R.id.updateEmailTxt);
        cancelTxt = (MTextView) editInfoDialog.findViewById(R.id.cancelTxt);
        updateMobileTxt = (MTextView) editInfoDialog.findViewById(R.id.updateMobileTxt);
        emailBox = (EditText) editInfoDialog.findViewById(R.id.emailBox);
        mobileBox = (EditText) editInfoDialog.findViewById(R.id.mobileBox);
        countryBox = (EditText) editInfoDialog.findViewById(R.id.countryBox);
        updateEmailArea = (LinearLayout) editInfoDialog.findViewById(R.id.updateEmailArea);
        updateMobileArea = (LinearLayout) editInfoDialog.findViewById(R.id.updateMobileArea);


        String text = type.equalsIgnoreCase("Email") ? generalFunc.retrieveLangLBl("", "LBL_EMAIL_LBL_TXT") : generalFunc.retrieveLangLBl("", "LBL_MOBILE_NUMBER_HEADER_TXT");
        titleTxt.setText(text);

        String hintText = type.equalsIgnoreCase("Email") ? generalFunc.retrieveLangLBl("To update your existing email id, please enter new email id below.", "LBL_EMAIL_EDIT_NOTE") : generalFunc.retrieveLangLBl("To update your existing mobile number, please enter new mobile number below.", "LBL_MOBILE_EDIT_NOTE");
        hintTxt.setText(hintText);

        int icon = type.equalsIgnoreCase("Email") ? R.drawable.ic_email_verification : R.drawable.ic_mobile_verification_icon;
        iv_img_icon.setImageResource(icon);


        cancelTxt.setText(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));

        updateEmailArea.setVisibility(View.GONE);
        updateMobileArea.setVisibility(View.GONE);
        errorTxt.setVisibility(View.GONE);

        if (type.equalsIgnoreCase("Email")) {
            updateEmailArea.setVisibility(View.VISIBLE);
            updateMobileArea.setVisibility(View.GONE);
        } else if (type.equalsIgnoreCase("Mobile")) {
            updateEmailArea.setVisibility(View.GONE);
            updateMobileArea.setVisibility(View.VISIBLE);
        }

        mobileBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_MOBILE_HINT"));
        emailBox.setHint(generalFunc.retrieveLangLBl("", "LBL_ENTER_EMAIL_HINT"));

        //set KeyPad
        mobileBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);
        mobileBox.setImeOptions(EditorInfo.IME_ACTION_DONE);

        // Set Existing Details
        String vCodeStr = generalFunc.getJsonValueStr("vCode", obj_userProfile);
        if (Utils.checkText(vCodeStr)) {
            countryBox.setText("+" + vCodeStr);
            isCountrySelected = true;
            vPhoneCode = vCodeStr;
            vCountryCode = generalFunc.getJsonValueStr("vCountry", obj_userProfile);
        }
        Utils.removeInput(countryBox);

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {

            editInfoDialog.findViewById(R.id.imageView2).setVisibility(View.VISIBLE);

            countryBox.setOnClickListener(v -> {
                if (countryPicker == null) {
                    countryPicker = new CountryPicker.Builder(getActContext()).showingDialCode(true)
                            .setLocale(MyUtils.getLocale()).showingFlag(true)
                            .enablingSearch(true)
                            .setCountrySelectionListener(country -> setData(country.getCode(), country.getDialCode(), country.getFlagName()))
                            .build();
                }
                countryPicker.show(getActContext());
            });

            countryBox.setOnTouchListener((v, motionEvent) -> {
                if (motionEvent.getAction() == MotionEvent.ACTION_UP && !countryBox.hasFocus()) {
                    countryBox.performClick();
                }
                return true;
            });
        } else {
            editInfoDialog.findViewById(R.id.imageView2).setVisibility(View.GONE);
        }

        updateEmailTxt.setOnClickListener(view -> {

            // Hide KeyBoard
            Utils.hideKeyPad(VerifyInfoActivity.this);

            if (type.equalsIgnoreCase("Email")) {

                boolean emailEntered = Utils.checkText(emailBox) && generalFunc.isEmailValid(Utils.getText(emailBox));


                if (!emailEntered && !Utils.checkText(emailBox)) {
                    errorTxt.setText(required_str);
                } else if (!emailEntered && Utils.checkText(emailBox) && !generalFunc.isEmailValid(Utils.getText(emailBox))) {
                    errorTxt.setText(error_email_str);
                }


                if (!emailEntered) {

                    errorTxt.setVisibility(View.VISIBLE);
                    return;
                }
                errorTxt.setVisibility(View.GONE);

                if (Utils.getText(emailBox).trim().equalsIgnoreCase(generalFunc.getJsonValueStr("vEmail", obj_userProfile).trim())) {
                    editInfoDialog.dismiss();
                    return;
                }
                updateProfile(type, Utils.getText(emailBox), "", vCountryCode, vPhoneCode);

            }
        });


        updateMobileTxt.setOnClickListener(view -> {

            // Hide KeyBoard
            Utils.hideKeyPad(VerifyInfoActivity.this);

            boolean mobileEntered = Utils.checkText(mobileBox);
            boolean countryEntered = isCountrySelected;

            String LBL_INVALID_MOBILE_NO = generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO");
            if (!mobileEntered || countryEntered) {

                errorTxt.setText(required_str);
            } else if (mobileEntered && (mobileBox.length() < 3)) {
                errorTxt.setText(LBL_INVALID_MOBILE_NO);
            }

            if (!mobileEntered || !countryEntered) {
                errorTxt.setVisibility(View.VISIBLE);
                return;
            }

            errorTxt.setVisibility(View.GONE);

            String currentMobileNum = generalFunc.getJsonValueStr("vPhone", obj_userProfile);
            String currentPhoneCode = generalFunc.getJsonValueStr("vCode", obj_userProfile);

            if (!currentPhoneCode.equals(vPhoneCode) || !currentMobileNum.equals(Utils.getText(mobileBox))) {
                updateProfile(type, "", Utils.getText(mobileBox), vCountryCode, vPhoneCode);
                return;
            }

            editInfoDialog.dismiss();


        });

        cancelTxt.setOnClickListener(view -> editInfoDialog.dismiss());

        editInfoDialog.setOnDismissListener(dialogInterface -> isDialogOpen = false);
        isDialogOpen = true;
        LayoutDirection.setLayoutDirection(editInfoDialog);
        editInfoDialog.show();

    }


    public void setCancelable(Dialog dialogview, boolean cancelable) {
        final Dialog dialog = dialogview;
        View touchOutsideView = dialog.getWindow().getDecorView().findViewById(R.id.touch_outside);
        View bottomSheetView = dialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        if (cancelable) {
            touchOutsideView.setOnClickListener(v -> {
                if (dialog.isShowing()) {
                    dialog.cancel();
                }
            });
            BottomSheetBehavior.from(bottomSheetView).setHideable(true);
        } else {
            touchOutsideView.setOnClickListener(null);
            BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        }
    }


    public void updateProfile(String type, String email, String mobile, String countryCode, String vPhoneCode) {

        boolean isMobile = type.equalsIgnoreCase("Mobile");
        boolean isEmail = type.equalsIgnoreCase("Email");

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateUserProfileDetail");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("vName", generalFunc.getJsonValueStr("vName", obj_userProfile));
        parameters.put("vLastName", generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        parameters.put("vPhone", isMobile ? mobile : generalFunc.getJsonValueStr("vPhone", obj_userProfile));
        parameters.put("vPhoneCode", isMobile ? vPhoneCode : generalFunc.getJsonValueStr("vCode", obj_userProfile));
        parameters.put("vCountry", isMobile ? countryCode : generalFunc.getJsonValueStr("vCountry", obj_userProfile));
        parameters.put("vEmail", isEmail ? email : generalFunc.getJsonValueStr("vEmail", obj_userProfile));
        parameters.put("CurrencyCode", generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));
        parameters.put("LanguageCode", generalFunc.getJsonValueStr("vLang", obj_userProfile));
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

                    new ConfigureMemberData(responseString, generalFunc, getActContext(), false);

                    vEmail = generalFunc.getJsonValueStr("vEmail", obj_userProfile);
                    vPhone = generalFunc.getJsonValueStr("vCode", obj_userProfile) + generalFunc.getJsonValueStr("vPhone", obj_userProfile);


                    ((MTextView) findViewById(R.id.phoneTxt)).setText("+" + vPhone);
                    ((MTextView) findViewById(R.id.emailTxt)).setText(vEmail);


                    String ePhoneVerified = generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile);
                    String eEmailVerified = generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile);


                    enableOrDisable(true, type);
                    removecountDownTimer(type);

                    if (isMobile && !ePhoneVerified.equalsIgnoreCase("Yes")) {
                        reqType = "DO_PHONE_VERIFY";
                        phoneVerificationCode = "";
                    } else if (isEmail && !eEmailVerified.equalsIgnoreCase("Yes")) {
                        reqType = "DO_EMAIL_VERIFY";
                        emailVerificationCode = "";
                    }

                    editInfoDialog.dismiss();
                    sendVerificationSMS(type);


                } else {
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void enableOrDisable(boolean activate, String showTimerFor) {

        boolean isNotBlank = Utils.checkText(showTimerFor);
        if (isNotBlank && showTimerFor.equalsIgnoreCase("Both")) {
            setButtonEnabled(mobContinueBtn, activate);
            setButtonEnabled(emailContinueBtn, activate);
        } else if (isNotBlank && showTimerFor.equalsIgnoreCase("Email")) {
            setButtonEnabled(emailContinueBtn, activate);
        } else if (isNotBlank && showTimerFor.equalsIgnoreCase("Mobile")) {
            setButtonEnabled(mobContinueBtn, activate);
        } else if (!isNotBlank) {
            setButtonEnabled(mobContinueBtn, activate);
            setButtonEnabled(emailContinueBtn, activate);
        }

        if (activate && isNotBlank) {
            if (isNotBlank && showTimerFor.equalsIgnoreCase("Both")) {
                mobContinueBtn.setText(LBL_RESEND_SMS);
                emailContinueBtn.setText(LBL_RESEND_EMAIL);
            } else if (isNotBlank && showTimerFor.equalsIgnoreCase("Email")) {
                emailContinueBtn.setText(LBL_RESEND_EMAIL);
            } else if (isNotBlank && showTimerFor.equalsIgnoreCase("Mobile")) {
                mobContinueBtn.setText(LBL_RESEND_SMS);

            }
        }
    }

    private void setButtonEnabled(MButton btn, boolean setEnable) {
        btn.setFocusableInTouchMode(setEnable);
        btn.setFocusable(setEnable);
        btn.setEnabled(setEnable);
        if (setEnable) {
            addToClickHandler(btn);
        } else {
            btn.setOnClickListener(null);
        }
        btn.setTextColor(setEnable ? Color.parseColor("#FFFFFF") : Color.parseColor("#BABABA"));
        btn.setClickable(setEnable);
    }

    @Override
    public void onBackPressed() {
        if (mobOtpArea.getVisibility() == View.VISIBLE) {
            mobOtpArea.setVisibility(View.GONE);
            showDemoOtpNote(false, false);
            mobEditArea.setVisibility(View.VISIBLE);
            firebaseOTP_Txt.setText("");
            manageSmsHelpTxt(true);
            return;
        }

        if (emailOtpArea.getVisibility() == View.VISIBLE) {
            emailOtpArea.setVisibility(View.GONE);
            showDemoOtpNote(false, false);
            emailEditArea.setVisibility(View.VISIBLE);
            manageEmailHelpTxt(true);
            return;
        }


        removecountDownTimer("Both");

        if (!isbackshow || isrestart) {

        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onDestroy() {
        removecountDownTimer("Both");
        super.onDestroy();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE && resultCode == Activity.RESULT_OK && data != null) {
            if (editInfoDialog != null) {

                vCountryCode = data.getStringExtra("vCountryCode");
                vPhoneCode = data.getStringExtra("vPhoneCode");
                isCountrySelected = true;

                ((EditText) editInfoDialog.findViewById(R.id.countryBox)).setText("+" + vPhoneCode);
            }
        }
    }

    public void setData(String vCountryCode, String vPhoneCode, String vSImage) {
        this.vCountryCode = vCountryCode;
        this.vPhoneCode = vPhoneCode;
        isCountrySelected = true;


        GeneralFunctions generalFunctions = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
        ((EditText) editInfoDialog.findViewById(R.id.countryBox)).setText("+" + generalFunctions.convertNumberWithRTL(vPhoneCode));
    }
}
