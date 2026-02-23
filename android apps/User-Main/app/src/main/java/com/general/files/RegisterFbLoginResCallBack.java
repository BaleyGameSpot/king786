package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import com.act.AccountverificationActivity;
import com.act.ContactUsActivity;
import com.act.MobileStegeActivity;
import com.act.deliverAll.LoginActivity;
import com.act.deliverAll.SignUpActivity;
import com.facebook.CallbackManager;
import com.facebook.FacebookCallback;
import com.facebook.FacebookException;
import com.facebook.GraphRequest;
import com.facebook.login.LoginResult;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MyProgressDialog;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 29-06-2016.
 */
public class RegisterFbLoginResCallBack implements FacebookCallback<LoginResult> {
    private final InternetConnection intCheck;
    Context mContext;
    GeneralFunctions generalFunc;
    MyProgressDialog myPDialog;
    MobileStegeActivity appMainLoginAct;
    LoginActivity appLoginAct;
    SignUpActivity appSignUpAct;
    private CallbackManager callbackManager;
    public boolean isrestart;

    public RegisterFbLoginResCallBack(Context mContext, CallbackManager callbackManager) {
        this.mContext = mContext;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        this.callbackManager = callbackManager;
        appMainLoginAct = (MobileStegeActivity) mContext;
        intCheck = new InternetConnection(mContext);
    }

    public RegisterFbLoginResCallBack(Context mContext, CallbackManager callbackManager, boolean isrestart) {
        this.mContext = mContext;
        this.isrestart = isrestart;

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        this.callbackManager = callbackManager;
        if (mContext instanceof LoginActivity) {
            appLoginAct = (LoginActivity) mContext;
        } else if (mContext instanceof SignUpActivity) {
            appSignUpAct = (SignUpActivity) mContext;
        }
        intCheck = new InternetConnection(mContext);
    }


    @Override
    public void onSuccess(LoginResult loginResult) {

        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            closeDialog();
            return;
        }

        myPDialog = new MyProgressDialog(mContext, false, generalFunc.retrieveLangLBl("", "LBL_LOADING_TXT"));
        myPDialog.show();

        GraphRequest request = GraphRequest.newMeRequest(
                loginResult.getAccessToken(),
                (me, response) -> {
                    // Application code
                    myPDialog.close();
                    if (response.getError() != null) {
                        // handle error - Facebook API error (likely due to misconfigured App ID)
                        Logger.d("RegisterFbLoginResCallBack", "Facebook Graph API error: " + response.getError().getErrorMessage() + " (Error code: " + response.getError().getErrorCode() + ")");
                        generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR"), generalFunc.retrieveLangLBl("", "LBL_TRY_AGAIN"));
                    } else {
                        try {

                            String email_str = generalFunc.getJsonValue("email", me.toString());
                            String name_str = generalFunc.getJsonValue("name", me.toString());
                            String first_name_str = generalFunc.getJsonValue("first_name", me.toString());
                            String last_name_str = generalFunc.getJsonValue("last_name", me.toString());
                            String fb_id_str = generalFunc.getJsonValue("id", me.toString());

                            // URL imageURL = "https://graph.facebook.com/" + fb_id_str + "/picture?type=large";
                            URL imageURL = new URL("https://graph.facebook.com/" + fb_id_str + "/picture?type=large");

                            registerFbUser(email_str, first_name_str, last_name_str, fb_id_str, imageURL + "");

                            generalFunc.logOUTFrmFB();
                        } catch (Exception e) {

                        }
                    }
                });
        Bundle parameters = new Bundle();
        parameters.putString("fields", "id,name,first_name,last_name,email");
        request.setParameters(parameters);
        request.executeAsync();

        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            closeDialog();
        }

    }

    @Override
    public void onCancel() {
        closeDialog();
    }

    @Override
    public void onError(FacebookException error) {
        closeDialog();
    }


    public void registerFbUser(final String email, final String fName, final String lName, final String fbId, final String imageURL) {

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "LoginWithFB");
        parameters.put("vFirstName", fName);
        parameters.put("vLastName", lName);
        parameters.put("vEmail", email);
        parameters.put("iFBId", fbId);
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.userType);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vImageURL", imageURL);
        parameters.put("eLoginType", "Facebook");

        //   parameters.put("eSystem", Utils.eSystem_Type);
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

                            signupUser(email, fName, lName, fbId, imageURL);

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

    public void signupUser(final String email, final String fName, final String lName, final String fbId, String imageURL) {

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
        parameters.put("eSignUpType", "Facebook");
        parameters.put("vImageURL", imageURL);

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
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));


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
        generalFunc.storeData(storeData);

    }


    public void closeDialog() {
        if (myPDialog != null) {
            myPDialog.close();
        }
    }
}
