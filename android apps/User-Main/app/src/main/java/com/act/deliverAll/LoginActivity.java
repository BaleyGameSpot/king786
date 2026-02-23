package com.act.deliverAll;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;

import android.text.Editable;
import android.text.InputType;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextPaint;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import com.activity.ParentActivity;
import com.fontanalyzer.SystemFont;
import com.act.ContactUsActivity;
import com.act.ForgotPasswordActivity;
import com.buddyverse.main.R;
import com.act.VerifyInfoActivity;
import com.facebook.CallbackManager;
import com.facebook.FacebookSdk;
import com.facebook.WebDialog;
import com.facebook.login.widget.LoginButton;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.PasswordViewHideManager;
import com.general.files.RegisterFbLoginResCallBack;
import com.general.files.RegisterGoogleLoginResCallBack;
import com.general.files.RegisterLinkedinLoginResCallBack;
import com.general.files.SetGeneralData;
import com.general.files.SetOnTouchList;
import com.general.files.ConfigureMemberData;
import com.general.files.SmartLogin;
import com.general.files.ActUtils;
import com.google.android.gms.auth.api.Auth;
import com.google.android.gms.auth.api.signin.GoogleSignInOptions;
import com.google.android.gms.auth.api.signin.GoogleSignInResult;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.service.handler.ApiHandler;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;

public class LoginActivity extends ParentActivity implements GoogleApiClient.OnConnectionFailedListener {

    MaterialEditText emailBox;
    MaterialEditText passwordBox;


    int submitBtnId;
    MTextView forgetPassTxt;

    String required_str = "";
    String error_email_str = "";


    public MTextView titleTxt;


    public MTextView signheaderHint, orTxt;
    GoogleApiClient mGoogleApiClient;
    CallbackManager callbackManager;
    LinearLayout socialarea;
    ImageView imagefacebook, imagetwitter, imageGoogle, imagelinkedin;
    boolean isGoogleLogin = true;
    boolean isFacebookLogin = true;
    boolean isTwitterLogin = true;
    boolean isLinkdinLogin = true;
    LoginButton loginButton;

    private static final int RC_SIGN_IN = 001;
    private static final int RC_SIGN_UP = 002;


    JSONObject userProfileJsonObj;

    MTextView btnTxt;
    LinearLayout btnArea;
    ImageView btnImg, imgSmartLoginQuery, imgSmartLoginClickView;
    ;
    LinearLayout imgClose, llSmartLoginEnable, llLoaderView;
    CheckBox checkboxSmartLogin;
    private SmartLogin mSmartLogin;
    MTextView signbootomHint;

    static MaterialEditText countryBox;
    static String vCountryCode = "";
    static String vPhoneCode = "";
    static String vSImage = "";
    static boolean isCountrySelected = false;
    static ImageView countryimage;
    RelativeLayout countryArea;
    ImageView countrydropimage, countrydropimagerror;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
      /*  FacebookSdk.sdkInitialize(getActContext());
        FacebookSdk.setWebDialogTheme(R.style.FBDialogtheme);*/
        WebDialog.setWebDialogTheme(R.style.FBDialogtheme);
        setContentView(R.layout.activity_login);


        GoogleSignInOptions gso = new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
                .requestEmail()
                .build();
        mGoogleApiClient = new GoogleApiClient.Builder(this)
                .enableAutoManage(this, this)
                .addApi(Auth.GOOGLE_SIGN_IN_API, gso)
                .build();


        callbackManager = CallbackManager.Factory.create();

        initview();


        setLabels();
    }

    private void initview() {

        if (generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY) != null && !generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY).isEmpty()) {
            // Facebook Sign-In disabled
            // FacebookSdk.setApplicationId(generalFunc.retrieveValue(Utils.FACEBOOK_APPID_KEY));
        }
        titleTxt = (MTextView) findViewById(R.id.titleTxt);


        signbootomHint = (MTextView) findViewById(R.id.signbootomHint);
        emailBox = (MaterialEditText) findViewById(R.id.emailBox);
        emailBox.setHelperTextAlwaysShown(true);
        passwordBox = (MaterialEditText) findViewById(R.id.passwordBox);

        forgetPassTxt = (MTextView) findViewById(R.id.forgetPassTxt);


        btnArea = (LinearLayout) findViewById(R.id.btnArea);
        btnTxt = (MTextView) findViewById(R.id.btnTxt);
        btnImg = (ImageView) findViewById(R.id.btnImg);
        imgClose = (LinearLayout) findViewById(R.id.imgClose);
        addToClickHandler(imgClose);
        addToClickHandler(btnArea);
        addToClickHandler(forgetPassTxt);
        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }


        signheaderHint = (MTextView) findViewById(R.id.signheaderHint);
        orTxt = (MTextView) findViewById(R.id.orTxt);


        passwordBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
        passwordBox.setTypeface(SystemFont.FontStyle.DEFAULT.font);

        new PasswordViewHideManager(getActContext(), passwordBox, generalFunc);

        countryArea = (RelativeLayout) findViewById(R.id.countryArea);
        countryBox = (MaterialEditText) findViewById(R.id.countryBox);
        countryimage = findViewById(R.id.countryimage);
        countrydropimage = (ImageView) findViewById(R.id.countrydropimage);
        countrydropimagerror = (ImageView) findViewById(R.id.countrydropimagerror);

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
                    signInUser(true);
                }
            }
        });

        llLoaderView = (LinearLayout) findViewById(R.id.llLoaderView);
        llLoaderView.setVisibility(View.GONE);
        llSmartLoginEnable = (LinearLayout) findViewById(R.id.llSmartLoginEnable);
        checkboxSmartLogin = (CheckBox) findViewById(R.id.checkboxSmartLogin);
        checkboxSmartLogin.setText(" " + generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"));
        imgSmartLoginQuery = (ImageView) findViewById(R.id.imgSmartLoginQuery);
        imgSmartLoginQuery.setOnClickListener(v -> generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_INFO")));

        imgSmartLoginClickView = (ImageView) findViewById(R.id.imgSmartLoginClickView);
        imgSmartLoginClickView.setVisibility(View.GONE);
        imgSmartLoginClickView.setOnClickListener(v -> mSmartLogin.clickEvent());

        emailBox.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS | InputType.TYPE_CLASS_TEXT);

        emailBox.setImeOptions(EditorInfo.IME_ACTION_NEXT);
        passwordBox.setImeOptions(EditorInfo.IME_ACTION_DONE);

        submitBtnId = Utils.generateViewId();

        addToClickHandler(forgetPassTxt);

        socialarea = (LinearLayout) findViewById(R.id.socialarea);

        signheaderHint = (MTextView) findViewById(R.id.signheaderHint);
        orTxt = (MTextView) findViewById(R.id.orTxt);


        imagefacebook = (ImageView) findViewById(R.id.imagefacebook);
        imagetwitter = (ImageView) findViewById(R.id.imagetwitter);
        imageGoogle = (ImageView) findViewById(R.id.imageGoogle);
        imagelinkedin = (ImageView) findViewById(R.id.imagelinkedin);


        addToClickHandler(imagefacebook);
        addToClickHandler(imagetwitter);
        addToClickHandler(imageGoogle);
        addToClickHandler(imagelinkedin);

        vCountryCode = generalFunc.retrieveValue(Utils.DefaultCountryCode);
        vPhoneCode = generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        vSImage = generalFunc.retrieveValue(Utils.DefaultCountryImage);

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

        if (generalFunc.retrieveValue(Utils.FACEBOOK_LOGIN).equalsIgnoreCase("NO")) {
            isFacebookLogin = false;
            imagefacebook.setVisibility(View.GONE);
        }
        
        if (generalFunc.retrieveValue(Utils.GOOGLE_LOGIN).equalsIgnoreCase("NO")) {
            isGoogleLogin = false;
            imageGoogle.setVisibility(View.GONE);
        }
        if (generalFunc.retrieveValue(Utils.TWITTER_LOGIN).equalsIgnoreCase("NO")) {
            isTwitterLogin = false;
            imagetwitter.setVisibility(View.GONE);
        }


        if (generalFunc.retrieveValue(Utils.LINKDIN_LOGIN).equalsIgnoreCase("NO")) {
            isLinkdinLogin = false;
            imagelinkedin.setVisibility(View.GONE);
        }

        if (!isTwitterLogin && !isGoogleLogin & !isFacebookLogin && !isLinkdinLogin) {
            socialarea.setVisibility(View.GONE);
            signheaderHint.setVisibility(View.GONE);

        }

        loginButton = new LoginButton(getActContext());


        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
            removeInput();
            emailBox.addTextChangedListener(new TextWatcher() {
                @Override
                public void beforeTextChanged(CharSequence charSequence, int i, int i1, int i2) {

                }

                @Override
                public void onTextChanged(CharSequence charSequence, int i, int i1, int i2) {

                    if (charSequence.length() > 3 && TextUtils.isDigitsOnly(emailBox.getText())) {
                        countryArea.setVisibility(View.VISIBLE);
                    } else {
                        countryArea.setVisibility(View.GONE);
                    }

                }

                @Override
                public void afterTextChanged(Editable editable) {

                }
            });
        }

        callbackManager = CallbackManager.Factory.create();

        loginButton.setReadPermissions(Arrays.asList("public_profile", "email"));

        loginButton.registerCallback(callbackManager, new RegisterFbLoginResCallBack(getActContext(), callbackManager, getIntent().getBooleanExtra("isRestart", true)));
    }

    @Override
    public void onResume() {
        super.onResume();
        mSmartLogin.isSmartLoginEnable(llSmartLoginEnable, checkboxSmartLogin);
    }

    ActivityResultLauncher<Intent> someActivityResultLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                if (result.getResultCode() == RESULT_OK) {
                    if (mSmartLogin.ResultOk()) {
                        signInUser(true);
                    }
                }
            });

    public void removeInput() {
        Utils.removeInput(countryBox);

        if (generalFunc.retrieveValue("showCountryList").equalsIgnoreCase("Yes")) {
            // countrydropimage.setVisibility(View.GONE);
            countryBox.setOnTouchListener(new SetOnTouchList());
            addToClickHandler(countryBox);
        }
    }


    public void setLabels() {
        emailBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_PHONE_EMAIL"));
        emailBox.setHelperText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN_MOBILE_EMAIL_HELPER"));
        passwordBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_PASSWORD_LBL_TXT"));

        countryBox.setBothText(generalFunc.retrieveLangLBl("", "LBL_COUNTRY_TXT"));

        forgetPassTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FORGET_YOUR_PASS_TXT"));
        btnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN_SIGN_IN_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        error_email_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_EMAIL_ERROR");

        signheaderHint.setText(generalFunc.retrieveLangLBl("", "LBL_SIGN_IN_WITH_SOC_ACC_HINT"));

        orTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OR_TXT"));

        String firstVal = generalFunc.retrieveLangLBl("", "LBL_ALREADY_HAVE_ACC");
        String secondVal = generalFunc.retrieveLangLBl("", "LBL_SIGNUP");
        String thirdVal = generalFunc.retrieveLangLBl("", "LBL_NOW");
        String finalVal = firstVal + " " + secondVal + " " + thirdVal;
        SpannableString signUpspan = new SpannableString(finalVal);

        //  signUpspan.setSpan(new ForegroundColorSpan(getResources().getColor(R.color.appThemeColor_1)),  firstVal.length(),firstVal.length()+ secondVal.length()+1, Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);


        // Initialize a new ClickableSpan to display appthemecolor background
        ClickableSpan signUpClickableSpan = new ClickableSpan() {
            @Override
            public void onClick(View view) {

                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", getIntent().getBooleanExtra("isRestart", true));
                new ActUtils(getActContext()).startActForResult(SignUpActivity.class, bn, RC_SIGN_UP);

            }

            @Override
            public void updateDrawState(TextPaint ds) {
                ds.setColor(getResources().getColor(R.color.appThemeColor_1));    // you can use custom color
                ds.setUnderlineText(false);    // this remove the underline
            }
        };
        signUpspan.setSpan(
                signUpClickableSpan, // Span to add
                finalVal.indexOf(secondVal), // Start of the span (inclusive)
                finalVal.indexOf(secondVal) + String.valueOf(secondVal).length(), // End of the span (exclusive)
                Spanned.SPAN_EXCLUSIVE_EXCLUSIVE // Do not extend the span when text add later
        );
        signbootomHint.setText(signUpspan);
        signbootomHint.setMovementMethod(LinkMovementMethod.getInstance());


    }

    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {
        // Google Play Services connection failed - this is non-blocking
        // The error is likely due to missing SHA-1 fingerprint in Google Cloud Console
        // or misconfigured API keys. Google Sign-In features will not work until fixed.
        Logger.d("LoginActivity", "Google Play Services connection failed: " + connectionResult.getErrorMessage() + " (Error code: " + connectionResult.getErrorCode() + ")");
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == RC_SIGN_IN) {
            GoogleSignInResult result = Auth.GoogleSignInApi.getSignInResultFromIntent(data);
            //handleSignInResult(result);

            new RegisterGoogleLoginResCallBack(getActContext(), getIntent().getBooleanExtra("isRestart", true)).handleSignInResult(result);
        } else if (requestCode == Utils.SELECT_COUNTRY_REQ_CODE) {

        } else if (requestCode == RC_SIGN_UP && resultCode == RESULT_OK) {
            Intent returnIntent = new Intent();
            setResult(Activity.RESULT_OK, returnIntent);
            finish();

        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE && resultCode == RESULT_OK) {
            Intent returnIntent = new Intent();
            setResult(Activity.RESULT_OK, returnIntent);
            finish();
        } else if (requestCode == Utils.SOCIAL_LOGIN_REQ_CODE && resultCode == RESULT_OK) {
            String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
            userProfileJsonObj = generalFunc.getJsonObject(userProfileJson);

            String ePhoneVerified = generalFunc.getJsonValue("ePhoneVerified", userProfileJson);

            if (!ePhoneVerified.equals("Yes")) {
                notifyVerifyMobile();
                return;
            }

            Intent returnIntent = new Intent();
            setResult(Activity.RESULT_OK, returnIntent);
            finish();

        } else {
            callbackManager.onActivityResult(requestCode, resultCode, data);
        }

    }


    public void onClick(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == btnArea.getId()) {
            checkValues();
        } else if (i == forgetPassTxt.getId()) {
            new ActUtils(getActContext()).startAct(ForgotPasswordActivity.class);
        }
//            else if (i == registerTxt.getId()) {
//                Bundle bn = new Bundle();
//                bn.putBoolean("isRestart", getIntent().getBooleanExtra("isRestart", true));
//                new ActUtils(getActContext()).startActForResult(SignUpActivity.class, bn, RC_SIGN_UP);
//
//            }

        if (i == imagefacebook.getId()) {
            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {

                generalFunc.showError();
            } else {
                loginButton.performClick();
            }

        } else if (i == imagetwitter.getId()) {

            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {

                generalFunc.showError();
            }


        } else if (i == imageGoogle.getId()) {
            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {

                generalFunc.showError();
            } else if (mGoogleApiClient == null) {
                // Google Play Services not available - show error message
                generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR"), 
                    "Google Sign-In is not available. Please check your Google Play Services configuration.");
            } else {
                Intent signInIntent = Auth.GoogleSignInApi.getSignInIntent(mGoogleApiClient);
                startActivityForResult(signInIntent, RC_SIGN_IN);
            }

        } else if (i == imagelinkedin.getId()) {
            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {

                generalFunc.showError();
            } else {
                RegisterLinkedinLoginResCallBack registerLinkedinLoginResCallBack = new RegisterLinkedinLoginResCallBack(getActContext(), getIntent().getBooleanExtra("isRestart", true));
                registerLinkedinLoginResCallBack.continueLogin();
            }
        } else if (i == imgClose.getId()) {
            finish();
        }


    }


    public void checkValues() {
        Utils.hideKeyboard(getActContext());
        String noWhiteSpace = generalFunc.retrieveLangLBl("Password should not contain whitespace.", "LBL_ERROR_NO_SPACE_IN_PASS");
        String pass_length = generalFunc.retrieveLangLBl("Password must be", "LBL_ERROR_PASS_LENGTH_PREFIX")
                + " " + Utils.minPasswordLength + " " + generalFunc.retrieveLangLBl("or more character long.", "LBL_ERROR_PASS_LENGTH_SUFFIX");
        boolean countryEntered = false;


        boolean emailEntered = Utils.checkText(emailBox) ? true
                : Utils.setErrorFields(emailBox, required_str);

        boolean passwordEntered = Utils.checkText(passwordBox) ?
                (Utils.getText(passwordBox).contains(" ") ? Utils.setErrorFields(passwordBox, noWhiteSpace)
                        : (Utils.getText(passwordBox).length() >= Utils.minPasswordLength ? true : Utils.setErrorFields(passwordBox, pass_length)))
                : Utils.setErrorFields(passwordBox, required_str);


        String regexStr = "^[0-9]*$";

        if (emailBox.getText().toString().trim().replace("+", "").matches(regexStr)) {
            if (emailEntered) {
                emailEntered = emailBox.length() >= 3 ? true : Utils.setErrorFields(emailBox, generalFunc.retrieveLangLBl("", "LBL_INVALID_MOBILE_NO"));
            }

            if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
                countryEntered = isCountrySelected ? true : false;
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
            }
        } else {
            emailEntered = Utils.checkText(emailBox) ?
                    (generalFunc.isEmailValid(Utils.getText(emailBox)) ? true : Utils.setErrorFields(emailBox, error_email_str))
                    : Utils.setErrorFields(emailBox, required_str);

            if (emailEntered == false) {
                return;
            }
        }

        if (emailEntered == false || passwordEntered == false) {
            return;
        }

        btnArea.setEnabled(false);
        signInUser(false);
    }

    public void notifyVerifyMobile() {
        String vPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", userProfileJsonObj);
//        String vPhoneCode = generalFunc.retrieveValue(Utils.DefaultPhoneCode);
        Bundle bn = new Bundle();
        bn.putString("MOBILE", vPhoneCode + generalFunc.getJsonValue("vPhone", userProfileJsonObj));
        bn.putString("msg", "DO_PHONE_VERIFY");
        bn.putBoolean("isrestart", false);
        bn.putString("isbackshow", "No");
        new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
    }


    public void signInUser(boolean isSmartLogin) {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "signIn");
        if (isSmartLogin) {
            parameters.put("vEmail", "");
            parameters.put("vPassword", "");
        } else {
            parameters.put("vEmail", Utils.getText(emailBox));
            parameters.put("vPassword", Utils.getText(passwordBox));
        }
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("eSystem", Utils.eSystem_Type);
        if (generalFunc.retrieveValue("ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD").equalsIgnoreCase("Yes")) {
            parameters.put("CountryCode", vCountryCode);
            parameters.put("PhoneCode", vPhoneCode);
        }


        llLoaderView.setVisibility(View.VISIBLE);
        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            llLoaderView.setVisibility(View.GONE);
            btnArea.setEnabled(true);
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    setGeneralData();

                    String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

                    String ePhoneVerified = generalFunc.getJsonValue("ePhoneVerified", userProfileJson);

//                        if (!ePhoneVerified.equals("Yes")) {
//                            notifyVerifyMobile();
//                            return;
//                        }

//                        Intent returnIntent = new Intent();
//                        setResult(Activity.RESULT_OK, returnIntent);
//                        finish();

                    MyApp.getInstance().restartWithGetDataApp();


                } else {
                    passwordBox.setText("");
                    if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Deleted")) {
                        openContactUsDialog(responseString);
                    } else if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Inactive")) {
                        openContactUsDialog(responseString);
                    } else {
                        generalFunc.showGeneralMessage("",
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void setGeneralData() {
        String responseString = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        ArrayList<String> removeData = new ArrayList<>();
        HashMap<String, String> storeData = new HashMap<>();
        userProfileJsonObj = generalFunc.getJsonObject(responseString);
        new SetGeneralData(generalFunc, userProfileJsonObj);
        storeData.put(Utils.ISWALLETBALNCECHANGE, "No");

        removeData.add("userHomeLocationLatitude");
        removeData.add("userHomeLocationLongitude");
        removeData.add("userHomeLocationAddress");
        removeData.add("userWorkLocationLatitude");
        removeData.add("userWorkLocationLongitude");
        removeData.add("userWorkLocationAddress");
        generalFunc.removeValue(removeData);

        if (generalFunc.getJsonArray("UserFavouriteAddress", responseString) == null) {
            return;
        }

        JSONArray userFavouriteAddressArr = generalFunc.getJsonArray("UserFavouriteAddress", responseString);
        if (userFavouriteAddressArr.length() > 0) {

            for (int i = 0; i < userFavouriteAddressArr.length(); i++) {
                JSONObject dataItem = generalFunc.getJsonObject(userFavouriteAddressArr, i);

                if (generalFunc.getJsonValueStr("eType", dataItem).equalsIgnoreCase("HOME")) {

                    storeData.put("userHomeLocationLatitude", generalFunc.getJsonValueStr("vLatitude", dataItem));
                    storeData.put("userHomeLocationLongitude", generalFunc.getJsonValueStr("vLongitude", dataItem));
                    storeData.put("userHomeLocationAddress", generalFunc.getJsonValueStr("vAddress", dataItem));

                } else if (generalFunc.getJsonValueStr("eType", dataItem).equalsIgnoreCase("WORK")) {
                    storeData.put("userWorkLocationLatitude", generalFunc.getJsonValueStr("vLatitude", dataItem));
                    storeData.put("userWorkLocationLongitude", generalFunc.getJsonValueStr("vLongitude", dataItem));
                    storeData.put("userWorkLocationAddress", generalFunc.getJsonValueStr("vAddress", dataItem));

                }

            }
        }

        generalFunc.storeData(storeData);

    }


    public void openContactUsDialog(String responseString) {
        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        alertBox.setBtnClickList(new GenerateAlertBox.HandleAlertBtnClick() {
            @Override
            public void handleBtnClick(int btn_id) {

                alertBox.closeAlertBox();
                if (btn_id == 0) {
                    new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                }
            }
        });
        alertBox.showAlertBox();
    }

    public Context getActContext() {
        return LoginActivity.this;
    }
}
