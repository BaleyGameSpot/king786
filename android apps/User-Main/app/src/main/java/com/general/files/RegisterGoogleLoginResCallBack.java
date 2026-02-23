package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.NonNull;

import com.act.AccountverificationActivity;
import com.act.ContactUsActivity;
import com.act.MobileStegeActivity;
import com.act.deliverAll.LoginActivity;
import com.act.deliverAll.SignUpActivity;
import com.google.android.gms.auth.api.signin.GoogleSignInAccount;
import com.google.android.gms.auth.api.signin.GoogleSignInResult;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MyProgressDialog;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 29-06-2016.
 */
public class RegisterGoogleLoginResCallBack implements GoogleApiClient.OnConnectionFailedListener {
    Context mContext;
    GeneralFunctions generalFunc;

    MyProgressDialog myPDialog;
    MobileStegeActivity appMainLoginAct;
    LoginActivity appLoginAct;
    SignUpActivity appSignUpAct;
    public boolean isrestart;

    public RegisterGoogleLoginResCallBack(Context mContext) {
        this.mContext = mContext;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        appMainLoginAct = (MobileStegeActivity) mContext;

    }

    public RegisterGoogleLoginResCallBack(Context mContext, boolean isrestart) {
        this.mContext = mContext;
        this.isrestart = isrestart;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        if (mContext instanceof LoginActivity) {
            appLoginAct = (LoginActivity) mContext;
        } else if (mContext instanceof SignUpActivity) {
            appSignUpAct = (SignUpActivity) mContext;
        }

    }

    public void handleSignInResult(GoogleSignInResult result) {

        if (result.isSuccess()) {
            GoogleSignInAccount acct = result.getSignInAccount();
            String personName = acct.getDisplayName();
            String email = acct.getEmail();
            String id = acct.getId();
            String imageUrl = acct.getPhotoUrl() + "";
            registergoogleUser(email, personName, "", id, imageUrl);
        }
    }

    public void registergoogleUser(final String email, final String fName, final String lName, final String fbId, String imageUrl) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "LoginWithFB");
        parameters.put("vFirstName", fName);
        parameters.put("vLastName", lName);
        parameters.put("vEmail", email);
        parameters.put("iFBId", fbId);
        parameters.put("eLoginType", "Google");
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vImageURL", imageUrl);

        showLoader();
        ApiHandler.execute(mContext, parameters, false, true, generalFunc, (String responseString) -> {

            hideLoader();
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail == true) {
                    new ConfigureMemberData(responseString, generalFunc, mContext, true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValue(Utils.message_str, responseString));
                    if (appMainLoginAct != null) {
                        appMainLoginAct.manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    }
                    if (appMainLoginAct == null) {
                        if (isrestart) {
                            new OpenMainProfile(mContext,
                                    generalFunc.getJsonValue(Utils.message_str, responseString), false, generalFunc).startProcess();

                        } else {
                            //((Activity)mContext).finish();
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

    public void openContactUsDialog(String responseString) {
        GenerateAlertBox alertBox = new GenerateAlertBox(mContext);
        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
        alertBox.setBtnClickList(new GenerateAlertBox.HandleAlertBtnClick() {
            @Override
            public void handleBtnClick(int btn_id) {

                alertBox.closeAlertBox();
                if (btn_id == 0) {
                    new ActUtils(mContext).startAct(ContactUsActivity.class);
                }
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
        parameters.put("eSignUpType", "Google");
        parameters.put("vImageURL", imageUrl);


        showLoader();
        ApiHandler.execute(mContext, parameters, false, true, generalFunc, responseString -> {

            hideLoader();
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail == true) {


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
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));


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

        generalFunc.storeData(storeData);


    }


    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {
        // Google Play Services connection failed - this is non-blocking
        // The error is likely due to missing SHA-1 fingerprint in Google Cloud Console
        // or misconfigured API keys. Google Sign-In features will not work until fixed.
        Logger.d("RegisterGoogleLoginResCallBack", "Google Play Services connection failed: " + connectionResult.getErrorMessage() + " (Error code: " + connectionResult.getErrorCode() + ")");
    }
}
