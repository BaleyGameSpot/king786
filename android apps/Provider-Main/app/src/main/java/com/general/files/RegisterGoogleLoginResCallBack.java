package com.general.files;

import android.content.Context;
import android.view.View;

import androidx.annotation.NonNull;

import com.act.MobileStegeActivity;
import com.google.android.gms.auth.api.signin.GoogleSignInAccount;
import com.google.android.gms.auth.api.signin.GoogleSignInResult;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MyProgressDialog;

import org.json.JSONObject;

import java.util.HashMap;

/**
 * Created by Admin on 29-06-2016.
 */
public class RegisterGoogleLoginResCallBack implements GoogleApiClient.OnConnectionFailedListener {
    Context mContext;
    GeneralFunctions generalFunc;

    MyProgressDialog myPDialog;
    MobileStegeActivity appLoginAct;

    public RegisterGoogleLoginResCallBack(Context mContext) {
        this.mContext = mContext;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        appLoginAct = (MobileStegeActivity) mContext;

    }

    public void handleSignInResult(GoogleSignInResult result) {

        if (result.isSuccess()) {
            GoogleSignInAccount acct = result.getSignInAccount();
            String personName = acct.getDisplayName();
            String email = acct.getEmail();
            String id = acct.getId();
            String imageUrl = acct.getPhotoUrl() + "";
            Logger.d("imageUrl", "::" + imageUrl);
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
        ApiHandler.execute(mContext, parameters, false, true, generalFunc, responseString -> {
            hideLoader();
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            Logger.d("Response", "::" + responseStringObject);

            if (responseStringObject != null) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                if (isDataAvail == true) {


                    new ConfigureMemberData(responseString, generalFunc, mContext, true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObject));
                    appLoginAct.manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(mContext,
                            false, generalFunc).startProcess();
                } else {
                    if (!generalFunc.getJsonValue(Utils.message_str, responseStringObject).equals("DO_REGISTER")) {
                        generalFunc.showGeneralMessage("",
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                    } else {

                        signupUser(email, fName, lName, fbId, imageUrl);
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
        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {
            hideLoader();
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            Logger.d("Response", "::" + responseString);

            if (responseStringObject != null) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                if (isDataAvail == true) {
                    new ConfigureMemberData(responseString, generalFunc, mContext, true);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObject));
                    appLoginAct.manageSinchClient(generalFunc.getJsonValue(Utils.message_str, responseString));
                    new OpenMainProfile(mContext,
                             false, generalFunc).startProcess();
                } else {

                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }


    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {
        closeDialog();
    }

    public void closeDialog() {
        if (myPDialog != null) {
            myPDialog.close();
        }
    }

}
