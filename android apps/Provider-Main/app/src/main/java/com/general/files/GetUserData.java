package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.os.Handler;

import androidx.core.app.ActivityCompat;

import com.act.AppLoginActivity;
import com.buddyverse.providers.BuildConfig;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONObject;

import java.util.HashMap;

/**
 * Created by Admin on 19-06-2017.
 */

public class GetUserData {

    GeneralFunctions generalFunc;
    Context mContext;
    boolean releaseCurrActInstance = true;

    public GetUserData(GeneralFunctions generalFunc, Context mContext) {
        this.generalFunc = generalFunc;
        this.mContext = mContext;
        this.releaseCurrActInstance = true;

    }

    public GetUserData(GeneralFunctions generalFunc, Context mContext, boolean releaseCurrActInstance) {
        this.generalFunc = generalFunc;
        this.mContext = mContext;
        this.releaseCurrActInstance = releaseCurrActInstance;
    }

    public void getData() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDetail");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.app_type);
        parameters.put("AppVersion", BuildConfig.VERSION_NAME);


        ServerTask exeWebServer = ApiHandler.execute(mContext, parameters, true, true, generalFunc, responseString -> {
            JSONObject responseStringObject = generalFunc.getJsonObject(responseString);

            if (responseStringObject != null) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject);

                String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObject);

                if (message.equals("SESSION_OUT")) {
                    MyApp.getInstance().notifySessionTimeOut();
                    Utils.runGC();
                    return;
                }

                if (isDataAvail) {
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, generalFunc.getJsonValueStr(Utils.message_str, responseStringObject));
                    new OpenMainProfile(mContext, true, generalFunc).startProcess();
                    if (releaseCurrActInstance) {
                        Handler handler = new Handler();
                        handler.postDelayed(() -> {
                            try {
                                ActivityCompat.finishAffinity((Activity) mContext);
                                Utils.runGC();
                            } catch (Exception e) {

                            }
                        }, 300);
                    }
                } else {
                    if (!generalFunc.getJsonValueStr("isAppUpdate", responseStringObject).trim().equals("")
                            && generalFunc.getJsonValueStr("isAppUpdate", responseStringObject).equals("true")) {

                    } else {

                        if (generalFunc.getJsonValueStr(Utils.message_str, responseStringObject).equalsIgnoreCase("LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY") ||
                                generalFunc.getJsonValueStr(Utils.message_str, responseStringObject).equalsIgnoreCase("LBL_ACC_DELETE_TXT") ||
                                generalFunc.getJsonValueStr(Utils.message_str, responseStringObject).equalsIgnoreCase("LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER")) {

                            GenerateAlertBox alertBox = generalFunc.notifyRestartApp("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObject)));
                            alertBox.setCancelable(false);
                            alertBox.setBtnClickList(btn_id -> {

                                if (btn_id == 1) {
                                    MyApp.getInstance().logOutFromDevice(true);
                                }
                            });
                            return;
                        }

                    }

                    showError(false);
                }
            } else {
                showError(false);
            }
        });
        exeWebServer.setCancelAble(false);
    }


    public void GetConfigData() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "generalConfigData");
        parameters.put("UserType", Utils.app_type);
        parameters.put("AppVersion", BuildConfig.VERSION_NAME);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));

        ApiHandler.execute(mContext, parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    new SetGeneralData(generalFunc, responseObj);
                    new Handler().postDelayed(() -> {
                        try {
                            new ActUtils(mContext).startAct(AppLoginActivity.class);
                            ActivityCompat.finishAffinity((Activity) mContext);

                        } catch (Exception e) {
                            throw new RuntimeException(e);
                        }
                        Utils.runGC();
                    }, 300);
                }
            }
        });
    }

    public void GetConfigDataForLocalStorage() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "generalConfigData");
        parameters.put("UserType", Utils.app_type);
        parameters.put("AppVersion", BuildConfig.VERSION_NAME);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));

        ApiHandler.execute(mContext, parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null && !responseObj.toString().equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    MyApp.getInstance().writeToFile(responseString, mContext);
                }
            } else {
                showError(true);
            }
        });

    }

    private void showError(boolean isBtnCancelShow) {
        MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct()).showGeneralMessage("",
                generalFunc.retrieveLangLBl("", "LBL_PLEASE_TRY_AGAIN_TXT"),
                isBtnCancelShow ? generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT") : "",
                generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"), buttonId -> {
                    if (buttonId == 0) {
                        MyApp.getInstance().getCurrentAct().finish();
                    } else {
                        generalFunc.restartApp();
                    }
                });
    }
}