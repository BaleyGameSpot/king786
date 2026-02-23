package com.general.files;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.WindowManager;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import com.act.AccountverificationActivity;
import com.act.ContactUsActivity;
import com.act.MobileStegeActivity;
import com.act.deliverAll.LoginActivity;
import com.act.deliverAll.SignUpActivity;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.WKWebView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.util.ArrayList;
import java.util.HashMap;

public class OpenLinkedinDialog {

    Context mContext;
    HashMap<String, String> data;
    GeneralFunctions generalFunc;

    androidx.appcompat.app.AlertDialog alertDialog;

    MobileStegeActivity appMainLoginAct;
    LoginActivity appLoginAct;
    SignUpActivity appSignUpAct;
    public boolean isrestart;

    public OpenLinkedinDialog(Context mContext, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        appMainLoginAct = (MobileStegeActivity) mContext;

        show();
    }

    public OpenLinkedinDialog(Context mContext, GeneralFunctions generalFunc, boolean isrestart) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.isrestart = isrestart;
        if (mContext instanceof LoginActivity) {
            appLoginAct = (LoginActivity) mContext;
        } else if (mContext instanceof SignUpActivity) {
            appSignUpAct = (SignUpActivity) mContext;
        }

        show();
    }

    AVLoadingIndicatorView loaderView;

    @SuppressLint("SetJavaScriptEnabled")
    public void show() {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(mContext);
        builder.setTitle("");

        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.linkedin_dailog, null);
        dialogView.setBackgroundColor(mContext.getResources().getColor(android.R.color.transparent));
        builder.setView(dialogView);

        WKWebView mWebView = (WKWebView) dialogView.findViewById(R.id.linkedinWebview);
        loaderView = (AVLoadingIndicatorView) dialogView.findViewById(R.id.loaderView);
        mWebView.setWebViewClient(new myWebClient());
        Logger.d("LinkedIn", "Url" + CommonUtilities.LINKEDINLOGINLINK);
        mWebView.loadUrl(CommonUtilities.LINKEDINLOGINLINK);
        mWebView.setFocusable(true);


        (dialogView.findViewById(R.id.cancelBtn)).setOnClickListener(view -> {
            if (alertDialog != null) {
                alertDialog.dismiss();
            }
        });


        alertDialog = builder.create();
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.getWindow().setLayout(WindowManager.LayoutParams.MATCH_PARENT, WindowManager.LayoutParams.MATCH_PARENT);
        alertDialog.getWindow().setBackgroundDrawableResource(android.R.color.transparent);
        alertDialog.show();

    }

    public class myWebClient extends WebViewClient {

        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {


            view.loadUrl(url);
            return true;

        }


        @Override
        public void onPageStarted(WebView view, String url, Bitmap favicon) {

            String data = url;
            data = data.substring(data.indexOf("data") + 5, data.length());
            try {
                String datajson = URLDecoder.decode(data, "UTF-8");
                loaderView.setVisibility(View.VISIBLE);

                view.setOnTouchListener(null);


                if (url.contains("status=1")) {
                    registerLinkedinUser(generalFunc.getJsonValue("emailAddress", datajson), generalFunc.getJsonValue("firstName", datajson), generalFunc.getJsonValue("lastName", datajson), generalFunc.getJsonValue("id", datajson),
                            generalFunc.getJsonValue("pictureUrl", datajson));
                }

                if (url.contains("status=2")) {
                    if (alertDialog != null) {
                        alertDialog.dismiss();
                    }
                }
            } catch (UnsupportedEncodingException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

        }


        @Override
        public void onReceivedError(WebView view, int errorCode,
                                    String description, String failingUrl) {

            generalFunc.showError();
            loaderView.setVisibility(View.GONE);
        }

        @Override
        public void onPageFinished(WebView view, String url) {
            loaderView.setVisibility(View.GONE);

            view.setOnTouchListener((v, event) -> {
                switch (event.getAction()) {
                    case MotionEvent.ACTION_DOWN:
                    case MotionEvent.ACTION_UP:
                        if (!v.hasFocus()) {
                            v.requestFocus();
                        }
                        break;
                }
                return false;
            });

        }
    }

    public void registerLinkedinUser(final String email, final String fName, final String lName, final String fbId, String imageUrl) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "LoginWithFB");
        parameters.put("vFirstName", fName);
        parameters.put("vLastName", lName);
        parameters.put("vEmail", email);
        parameters.put("iFBId", fbId);
        parameters.put("eLoginType", "LinkedIn");
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vImageURL", imageUrl);
        showLoader();
        ApiHandler.execute(mContext, parameters, false, true, generalFunc, responseString -> {

            hideLoader();
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    new ConfigureMemberData(responseString, generalFunc, mContext, true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));

                    if (appMainLoginAct == null) {
                        if (isrestart) {
                            new OpenMainProfile(mContext,
                                    generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();

                        } else {
                            //((Activity)mContext).finish();
                            String userProfileJsonObj = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                            setGeneralData(userProfileJsonObj);
                            boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, generalFunc.getJsonValue("vEmail", userProfileJsonObj));
                            if (generalFunc.getJsonValue("vPhone", userProfileJsonObj).equals("") || (generalFunc.getJsonValue("vEmail", userProfileJsonObj).equals("") & !isEmailBlankAndOptional)) {
                                //open account verification screen
                                if (generalFunc.getMemberId() != null && !generalFunc.getMemberId().equals("")) {
                                    if (!generalFunc.getMemberId().equals("")) {
                                        Bundle bn = new Bundle();
                                        bn.putBoolean("isRestart", isrestart);
                                        new ActUtils(mContext).startActForResult(AccountverificationActivity.class, bn, Utils.SOCIAL_LOGIN_REQ_CODE);
                                    } else {
                                        generalFunc.restartApp();
                                    }
                                }

                            } else {
                                Intent returnIntent = new Intent();
                                ((Activity) mContext).setResult(Activity.RESULT_OK, returnIntent);
                                ((Activity) mContext).finish();
                            }

                        }
                    } else {
                        new OpenMainProfile(mContext,
                                generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();
                    }
                } else {

                    if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Deleted")) {
                        openContactUsDialog(responseString);
                    } else if (generalFunc.getJsonValue("eStatus", responseString).equalsIgnoreCase("Inactive")) {
                        openContactUsDialog(responseString);
                    } else {
                        if (!generalFunc.getJsonValue(Utils.message_str, responseString).equals("DO_REGISTER")) {
                            generalFunc.showGeneralMessage("",
                                    generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        } else {

                            signupUser(email, fName, lName, fbId, imageUrl);

                        }
                    }

                }
            } else {
                generalFunc.showError();
            }
        });

    }

    private void showLoader() {

        if (mContext instanceof MobileStegeActivity) {
            MobileStegeActivity activity = (MobileStegeActivity) mContext;
            activity.llLoaderView.setVisibility(View.VISIBLE);
        }
    }

    private void hideLoader() {
        if (mContext instanceof MobileStegeActivity) {
            MobileStegeActivity activity = (MobileStegeActivity) mContext;
            activity.llLoaderView.setVisibility(View.GONE);
        }
    }

    public void openContactUsDialog(String responseString) {
        GenerateAlertBox alertBox = new GenerateAlertBox(mContext);
        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        alertBox.setBtnClickList(btn_id -> {

            alertBox.closeAlertBox();
            if (btn_id == 0) {
                new ActUtils(mContext).startAct(ContactUsActivity.class);
            }
        });
        alertBox.showAlertBox();
    }

    public void signupUser(final String email, final String fName, final String lName, final String fbId, String imageUrl) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "signup");
        parameters.put("vFirstName", fName);
        parameters.put("vLastName", lName);
        parameters.put("vEmail", email);
        parameters.put("vFbId", fbId);
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("eSignUpType", "LinkedIn");
        parameters.put("vImageURL", imageUrl);

        showLoader();

        ApiHandler.execute(mContext, parameters, false, true, generalFunc, responseString -> {

            hideLoader();
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    new ConfigureMemberData(responseString, generalFunc, mContext, true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    if (appMainLoginAct != null) {
                        appMainLoginAct.manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    }

                    if (appMainLoginAct == null) {
                        String userProfileJsonObj = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                        setGeneralData(userProfileJsonObj);
                        boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, generalFunc.getJsonValue("vEmail", userProfileJsonObj));

                        if (generalFunc.getJsonValue("vPhone", userProfileJsonObj).equals("") || (generalFunc.getJsonValue("vEmail", userProfileJsonObj).equals("") && !isEmailBlankAndOptional)) {
                            //open account verification screen
                            if (generalFunc.getMemberId() != null && !generalFunc.getMemberId().equals("")) {
                                if (!generalFunc.getMemberId().equals("")) {
                                    Bundle bn = new Bundle();
                                    bn.putBoolean("isRestart", isrestart);
                                    new ActUtils(mContext).startActForResult(AccountverificationActivity.class, bn, Utils.SOCIAL_LOGIN_REQ_CODE);
                                } else {
                                    generalFunc.restartApp();
                                }
                            }
                        } else {
                            Intent returnIntent = new Intent();
                            ((Activity) mContext).setResult(Activity.RESULT_OK, returnIntent);
                            ((Activity) mContext).finish();
                        }
                    } else {
                        new OpenMainProfile(mContext,
                                generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();
                    }
                } else {


                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void setGeneralData(String userProfileJsonObjData) {

        HashMap<String, String> storeData = new HashMap<>();
        ArrayList<String> removeData = new ArrayList<>();

        JSONObject userProfileJsonObj = generalFunc.getJsonObject(userProfileJsonObjData);

        new SetGeneralData(generalFunc, userProfileJsonObj);

        removeData.add("userHomeLocationLatitude");
        removeData.add("userHomeLocationLongitude");
        removeData.add("userHomeLocationAddress");
        removeData.add("userWorkLocationLatitude");
        removeData.add("userWorkLocationLongitude");
        removeData.add("userWorkLocationAddress");
        generalFunc.removeValue(removeData);

        if (generalFunc.getJsonArray("UserFavouriteAddress", userProfileJsonObjData) == null) {
            return;
        }

        JSONArray userFavouriteAddressArr = generalFunc.getJsonArray("UserFavouriteAddress", userProfileJsonObjData);
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


    }
}
