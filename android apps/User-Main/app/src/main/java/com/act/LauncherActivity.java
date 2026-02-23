package com.act;

import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.provider.Settings;
import android.view.View;
import android.view.WindowManager;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.window.SplashScreenView;

import androidx.core.app.ActivityCompat;
import androidx.core.splashscreen.SplashScreen;
import androidx.databinding.DataBindingUtil;
import androidx.multidex.BuildConfig;

import com.act.deliverAll.ServiceHomeActivity;
import com.activity.ParentActivity;
import com.general.call.VOIPMsgHandler;
import com.general.files.AESEnDecryption;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.GetHomeScreenData;
import com.general.files.GetUserData;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.SetGeneralData;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GoogleApiAvailability;
import com.google.android.gms.common.GooglePlayServicesUtil;
import com.google.android.gms.security.ProviderInstaller;
import com.google.android.material.snackbar.Snackbar;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityLauncherBinding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;

public class LauncherActivity extends ParentActivity implements ProviderInstaller.ProviderInstallListener, ServerTask.FileDataResponse {

    private GenerateAlertBox currentAlertBox;
    private static final int ERROR_DIALOG_REQUEST_CODE = 1;
    private String response_str_generalConfigData = "", response_str_autologin = "";
    private boolean mRetryProviderInstall;
    private String LBL_BTN_OK_TXT, LBL_CANCEL_TXT, LBL_RETRY_TXT, LBL_TRY_AGAIN_TXT;
    private ActivityLauncherBinding binding;
    private boolean providerInstallerCallbackReceived = false;
    private Handler providerInstallerTimeoutHandler;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        SplashScreen splashScreen = SplashScreen.installSplashScreen(this);
        splashScreen.setKeepOnScreenCondition(() -> false);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            getSplashScreen().setOnExitAnimationListener(SplashScreenView::remove);
        }
        setFullScreen();
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_launcher);

        MyUtils.setAppName(this, binding.appNameTxt);
        Animation fadeIn = AnimationUtils.loadAnimation(this, android.R.anim.fade_in);
        fadeIn.setDuration(1500);
        binding.appNameTxt.startAnimation(fadeIn);

        generalFunc.storeData("isInLauncher", "true");
        binding.drawOverMsgTxtView.setText(generalFunc.retrieveLangLBl("Please wait while we are checking app's configuration. This will take few seconds.", "LBL_DRAW_OVER_APP_NOTE"));

        LBL_RETRY_TXT = generalFunc.retrieveLangLBl("Retry", "LBL_RETRY_TXT");
        LBL_CANCEL_TXT = generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT");
        LBL_BTN_OK_TXT = generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT");
        LBL_TRY_AGAIN_TXT = generalFunc.retrieveLangLBl("Please try again.", "LBL_TRY_AGAIN_TXT");

        new Handler().postDelayed(() -> {
            if (generalFunc.isUserLoggedIn() && Utils.checkText(generalFunc.getMemberId())) {
                if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                    MyApp.getInstance().isGetDetailCall = true;
                }
            }
            if (!MyApp.getInstance().isHMSOnly()) {
                providerInstallerCallbackReceived = false;
                // Set a timeout of 5 seconds for ProviderInstaller
                providerInstallerTimeoutHandler = new Handler(Looper.getMainLooper());
                providerInstallerTimeoutHandler.postDelayed(() -> {
                    if (!providerInstallerCallbackReceived) {
                        Logger.d("LauncherActivity", "ProviderInstaller timeout, proceeding anyway");
                        providerInstallerCallbackReceived = true;
                        checkConfigurations();
                    }
                }, 5000);
                try {
                    ProviderInstaller.installIfNeededAsync(LauncherActivity.this, LauncherActivity.this);
                } catch (Exception e) {
                    Logger.e("LauncherActivity", "ProviderInstaller error: " + e.getMessage());
                    if (providerInstallerTimeoutHandler != null) {
                        providerInstallerTimeoutHandler.removeCallbacksAndMessages(null);
                    }
                    providerInstallerCallbackReceived = true;
                    checkConfigurations();
                }
            } else {
                checkConfigurations();
            }
        }, 1000);
    }

    private void setFullScreen() {
        getWindow().setFlags(WindowManager.LayoutParams.FLAG_LAYOUT_NO_LIMITS, WindowManager.LayoutParams.FLAG_LAYOUT_NO_LIMITS);
        getWindow().setNavigationBarColor(getResources().getColor(android.R.color.transparent));

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            getWindow().setDecorFitsSystemWindows(false);
        } else {
            getWindow().getDecorView().setSystemUiVisibility(
                    View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                            | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                            | View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                            | View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                            | View.SYSTEM_UI_FLAG_FULLSCREEN
                            | View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY);
        }
    }


    private void checkConfigurations() {
        binding.drawOverMsgTxtView.setVisibility(View.GONE);
        closeAlert();

//        if (!MyApp.getInstance().isHMSOnly()) {
//            int status = (GoogleApiAvailability.getInstance()).isGooglePlayServicesAvailable(getActContext());
//            if (status == ConnectionResult.SERVICE_VERSION_UPDATE_REQUIRED) {
//                showErrorOnPlayServiceDialog(generalFunc.retrieveLangLBl("This application requires updated google play service. " + "Please install Or update it from play store", "LBL_UPDATE_PLAY_SERVICE_NOTE"));
//                return;
//            } else if (status != ConnectionResult.SUCCESS) {
//                showErrorOnPlayServiceDialog(generalFunc.retrieveLangLBl("This application requires updated google play service. " + "Please install Or update it from play store", "LBL_UPDATE_PLAY_SERVICE_NOTE"));
//                return;
//            }
//        }
        if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
            showNoInternetDialog();
            return;
        }
        continueProcess();
    }

    private void continueProcess() {
        closeAlert();
        Utils.setAppLocal(getActContext());

        if (obj_userProfile == null || !Utils.checkText(obj_userProfile.toString())) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        }

        if (generalFunc.isUserLoggedIn() && Utils.checkText(generalFunc.getMemberId()) && obj_userProfile != null) {
            if (this.response_str_autologin.trim().equalsIgnoreCase("")) {
                new OpenMainProfile(getActContext(), obj_userProfile.toString(), true, generalFunc).startProcess();
                autoLogin();
            } else {
                continueAutoLogin(this.response_str_autologin);
            }

            JSONArray serviceArray = generalFunc.getJsonArray("ServiceCategories", obj_userProfile.toString());
            int serviceArrLength = serviceArray != null ? serviceArray.length() : 0;
            if (generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile).equalsIgnoreCase("0")
                    && (generalFunc.isDeliverOnlyEnabled() && serviceArrLength > 1)) {

                new GetHomeScreenData().getHomeScreenData(this, generalFunc, obj_userProfile);
            }
        } else {
            if (this.response_str_generalConfigData.trim().equalsIgnoreCase("")) {
                String strBucketData = MyApp.getInstance().readFromFile(this);
                if (Utils.checkText(strBucketData)) {
                    JSONObject responseObj = generalFunc.getJsonObject(strBucketData);
                    if (generalFunc.getJsonValue("vCode", generalFunc.getJsonValueStr("DefaultLanguageValues", responseObj)).equalsIgnoreCase(generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY))) {
                        setBucketData(strBucketData);
                    } else {
                        downloadGeneralData();
                    }
                } else {
                    downloadGeneralData();
                    if (!Utils.checkText(generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY))) {
                        ApiHandler.downloadFile(this, CommonUtilities.BUCKET_PATH, this).execute();
                    }
                }
            } else {
                continueDownloadGeneralData(this.response_str_generalConfigData);
            }
        }
    }

    private void setBucketData(String strBucketData) {
        continueDownloadGeneralData(strBucketData);
        manageConfigData();
    }

    private void manageConfigData() {
        GetUserData objRefresh = new GetUserData(generalFunc, MyApp.getInstance().getCurrentAct());
        objRefresh.GetConfigDataForLocalStorage();
    }

    private void reStartAppDialog() {
        closeAlert();
        generalFunc.showGeneralMessage("", LBL_TRY_AGAIN_TXT, LBL_BTN_OK_TXT, "", buttonId -> generalFunc.restartApp());
    }

    private void downloadGeneralData() {
        closeAlert();
        //binding.loaderView.setVisibility(View.VISIBLE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "generalConfigData");
        parameters.put("UserType", Utils.app_type);
        parameters.put("AppVersion", BuildConfig.VERSION_NAME);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        //parameters.putAll(GetFeatureClassList.getAllGeneralClasses());


        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (isFinishing()) {
                reStartAppDialog();
                return;
            }
            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    response_str_generalConfigData = responseString;
                    continueDownloadGeneralData(responseString);
                    MyApp.getInstance().writeToFile(responseString, this);
                } else {
                    String isAppUpdate = generalFunc.getJsonValue("isAppUpdate", responseString);
                    if (!isAppUpdate.trim().equals("") && isAppUpdate.equals("true")) {
                        showAppUpdateDialog(generalFunc.retrieveLangLBl("New update is available to download. " + "Downloading the latest update, you will get latest features, improvements and bug fixes.", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    } else {
                        String setMsg = LBL_TRY_AGAIN_TXT;
                        if (Utils.checkText(generalFunc.getJsonValue(Utils.message_str, responseString))) {
                            setMsg = generalFunc.getJsonValue(Utils.message_str, responseString);
                        }
                        currentAlertBox = generalFunc.showGeneralMessage("", setMsg, LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> {
                            if (buttonId == 1) {
                                continueProcess();
                            }
                        });
                    }
                }
            } else {
                showError();
            }
        });
    }

    private void continueDownloadGeneralData(String responseString) {
        if (isFinishing()) {
            return;
        }
        JSONObject responseObj = generalFunc.getJsonObject(responseString);

        storeImportantData(responseString);
        new SetGeneralData(generalFunc, responseObj);
        Utils.setAppLocal(getActContext());

        if (generalFunc.getJsonValue("SERVER_MAINTENANCE_ENABLE", responseString).equalsIgnoreCase("Yes")) {
            new ActUtils(getActContext()).startAct(AppRestrictedActivity.class);
            finish();
            return;
        }

        if (generalFunc.isDeliverOnlyEnabled()) {

            generalFunc.storeData(Utils.USER_PROFILE_JSON, responseString);

            JSONArray serviceArray = generalFunc.getJsonArray("ServiceCategories", responseString);
            int serviceArrLength = serviceArray != null ? serviceArray.length() : 0;
            if (serviceArrLength > 1) {

                ServiceModule.configure();
                if (ServiceModule.DeliverAll) {
                    new GetHomeScreenData().getHomeScreenData(this, generalFunc, responseObj);
                }

                ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
                for (int i = 0; i < serviceArrLength; i++) {
                    JSONObject serviceObj = generalFunc.getJsonObject(serviceArray, i);
                    HashMap<String, String> serviceMap = new HashMap<>();
                    serviceMap.put("iServiceId", generalFunc.getJsonValue("iServiceId", serviceObj.toString()));
                    serviceMap.put("vServiceName", generalFunc.getJsonValue("vServiceName", serviceObj.toString()));
                    serviceMap.put("vImage", generalFunc.getJsonValue("vImage", serviceObj.toString()));
                    serviceMap.put("iCompanyId", generalFunc.getJsonValue("STORE_ID", serviceObj.toString()));
                    String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", serviceObj);
                    serviceMap.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                    serviceMap.put("vCategory", generalFunc.getJsonValue("vService", serviceObj.toString()));
                    serviceMap.put("ispriceshow", generalFunc.getJsonValue("ispriceshow", serviceObj.toString()));
                    list_item.add(serviceMap);
                }
                Bundle bn = new Bundle();
                bn.putSerializable("servicedata", list_item);
                new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);

            } else {
                Bundle bn = new Bundle();
                bn.putBoolean("isfoodOnly", true);
                bn.putString("iCompanyId", generalFunc.getJsonValue("STORE_ID", responseString));
                bn.putString("ispriceshow", generalFunc.getJsonValue("ispriceshow", responseString));
                if (serviceArray.length() == 1) {
                    bn.putString("iServiceId", generalFunc.getJsonValue("iServiceId", generalFunc.getJsonObject(serviceArray, 0).toString()));
                }
                new ActUtils(getActContext()).startActWithData(ServiceHomeActivity.class, bn);

            }
        } else {
            Bundle bn = new Bundle();
            bn.putBoolean("isAnimated", true);
            new ActUtils(getActContext()).startActWithData(AppLoginActivity.class, bn);
            overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
        }
        try {
            ActivityCompat.finishAffinity(MyApp.getInstance().getCurrentAct());
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private void storeImportantData(String responseString) {
        generalFunc.storeData("TSITE_DB", generalFunc.getJsonValue("TSITE_DB", responseString));
        generalFunc.storeData("GOOGLE_API_REPLACEMENT_URL", generalFunc.getJsonValue("GOOGLE_API_REPLACEMENT_URL", responseString));
        generalFunc.storeData("APP_LAUNCH_IMAGES", generalFunc.getJsonValue("APP_LAUNCH_IMAGES", responseString));
        generalFunc.storeData(Utils.APP_SERVICE_URL_KEY, generalFunc.getJsonValue("APP_SERVICE_URL", responseString));
        Utils.APP_SERVICE_URL = generalFunc.getJsonValue("APP_SERVICE_URL", responseString);
    }

    private void autoLogin() {
        closeAlert();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDetail");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vDeviceType", Utils.deviceType);
        parameters.put("UserType", Utils.app_type);
        parameters.put("AppVersion", BuildConfig.VERSION_NAME);
        if (!generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY).equalsIgnoreCase("")) {
            parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        }
        /*if (obj_userProfile != null) {
            parameters.put("OLD_PROFILE_RESPONSE", obj_userProfile + "");
        }*/

        MyApp.getInstance().isGetDetailCall = true;

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            MyApp.getInstance().isGetDetailCall = false;

            if (responseString != null && !responseString.equals("")) {

                if (generalFunc.getJsonValue("changeLangCode", responseString).equalsIgnoreCase("Yes")) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), false);
                }
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);


                if (message.equals("SESSION_OUT")) {
                    MyApp.getInstance().notifySessionTimeOut();
                    Utils.runGC();
                    return;
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (Utils.checkText(generalFunc.retrieveValue("RTC_DATA_offer"))) {
                        new Handler(Looper.getMainLooper()).postDelayed(() -> {
                            //
                            VOIPMsgHandler.performAction(generalFunc, generalFunc.retrieveValue("RTC_DATA_offer"));
                            generalFunc.storeData("RTC_DATA_offer", "");
                        }, 1000);
                    }
                    response_str_autologin = responseString;
                    continueAutoLogin(responseString);

                } else {
//                    if (!generalFunc.getJsonValue("isAppUpdate", responseString).trim().equals("")
//                            && generalFunc.getJsonValue("isAppUpdate", responseString).equals("true")) {
//
//                        showAppUpdateDialog(generalFunc.retrieveLangLBl("New update is available to download. " + "Downloading the latest update, you will get latest features, improvements and bug fixes.", generalFunc.getJsonValue(Utils.message_str, responseString)));
//                    } else {
//                        if (generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_CONTACT_US_STATUS_NOTACTIVE_PASSENGER") || generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("LBL_ACC_DELETE_TXT")) {
//
//                            showContactUs(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
//                            return;
//                        }
//                        showError("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
//                    }
                }
            } else {
                showError();
            }
        });

    }

    private void continueAutoLogin(String responseString) {
        String message = generalFunc.getJsonValue(Utils.message_str, responseString);
        if (generalFunc.getJsonValue("SERVER_MAINTENANCE_ENABLE", message).equalsIgnoreCase("Yes")) {
            new ActUtils(getActContext()).startAct(AppRestrictedActivity.class);
            finish();
            return;
        }

        storeImportantData(responseString);

        generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
        DateTimeUtils.setDateTimeFormat(generalFunc, generalFunc.getJsonObject(responseString));
        ServerTask.MAPS_API_REPLACEMENT_STRATEGY = generalFunc.getJsonValue("MAPS_API_REPLACEMENT_STRATEGY", message);
        generalFunc.storeData("MAPS_API_REPLACEMENT_STRATEGY", ServerTask.MAPS_API_REPLACEMENT_STRATEGY);
        generalFunc.storeData("CHECK_SYSTEM_STORE_SELECTION", generalFunc.getJsonValue("CHECK_SYSTEM_STORE_SELECTION", message));

        generalFunc.storeData(Utils.ENABLE_GOPAY_KEY, generalFunc.getJsonValue(Utils.ENABLE_GOPAY_KEY, message));
        generalFunc.storeData(Utils.DELIVERALL_KEY, generalFunc.getJsonValue("DELIVERALL", message));
        generalFunc.storeData(Utils.ONLYDELIVERALL_KEY, generalFunc.getJsonValue("ONLYDELIVERALL", message));

        if (generalFunc.isDeliverOnlyEnabled()) {
            boolean isEmailBlankAndOptional = generalFunc.isEmailBlankAndOptional(generalFunc, generalFunc.getJsonValue("vEmail", message));
            if (generalFunc.getJsonValue("vPhone", message).equals("") || (generalFunc.getJsonValue("vEmail", message).equals("") && !isEmailBlankAndOptional)) {
                Bundle bn = new Bundle();
                bn.putBoolean("isRestart", true);
                new ActUtils(getActContext()).startActForResult(AccountverificationActivity.class, bn, Utils.SOCIAL_LOGIN_REQ_CODE);
                return;
            }

            String ePhoneVerified = generalFunc.getJsonValue("ePhoneVerified", message);
            String vPhoneCode = generalFunc.getJsonValue("vPhoneCode", message);
            String vPhone = generalFunc.getJsonValue("vPhone", message);
            if (!ePhoneVerified.equals("Yes")) {
                Bundle bn = new Bundle();
                bn.putString("MOBILE", vPhoneCode + vPhone);
                bn.putString("msg", "DO_PHONE_VERIFY");
                bn.putBoolean("isrestart", true);
                bn.putString("isbackshow", "No");
                new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_MOBILE_REQ_CODE);
                return;
            }

            JSONArray serviceArray = generalFunc.getJsonArray("ServiceCategories", message);
            int serviceArrLength = serviceArray != null ? serviceArray.length() : 0;
            if (serviceArrLength > 1 && generalFunc.isAnyDeliverOptionEnabled()) {

                ServiceModule.configure();

                ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
                for (int i = 0; i < serviceArrLength; i++) {
                    JSONObject serviceObj = generalFunc.getJsonObject(serviceArray, i);
                    HashMap<String, String> servicemap = new HashMap<>();
                    servicemap.put("iServiceId", generalFunc.getJsonValue("iServiceId", serviceObj.toString()));
                    servicemap.put("vServiceName", generalFunc.getJsonValue("vServiceName", serviceObj.toString()));
                    servicemap.put("vImage", generalFunc.getJsonValue("vImage", serviceObj.toString()));
                    servicemap.put("iCompanyId", generalFunc.getJsonValue("STORE_ID", serviceObj.toString()));
                    String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", serviceObj);
                    servicemap.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                    servicemap.put("vCategory", generalFunc.getJsonValue("vService", serviceObj.toString()));
                    servicemap.put("ispriceshow", generalFunc.getJsonValue("ispriceshow", serviceObj.toString()));
                    list_item.add(servicemap);
                }
                Bundle bn = new Bundle();
                bn.putSerializable("servicedata", list_item);
                new ActUtils(getActContext()).startActWithData(UberXHomeActivity.class, bn);

            } else {
                Bundle bn = new Bundle();
                bn.putBoolean("isfoodOnly", true);
                bn.putString("iCompanyId", generalFunc.getJsonValue("STORE_ID", message));
                bn.putString("ispriceshow", generalFunc.getJsonValue("ispriceshow", message));
                if (serviceArray.length() == 1) {
                    bn.putString("iServiceId", generalFunc.getJsonValue("iServiceId", generalFunc.getJsonObject(serviceArray, 0).toString()));
                }
                new ActUtils(getActContext()).startActWithData(ServiceHomeActivity.class, bn);

            }
            try {
                ActivityCompat.finishAffinity(MyApp.getInstance().getCurrentAct());
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
        } else {
            new OpenMainProfile(getActContext(), generalFunc.getJsonValue(Utils.message_str, responseString), true, generalFunc).startProcess();
        }
    }

    private void closeAlert() {
        try {
            if (currentAlertBox != null) {
                currentAlertBox.closeAlertBox();
                currentAlertBox = null;
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private void showContactUs(String content) {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage("", content, generalFunc.retrieveLangLBl("Contact Us", "LBL_CONTACT_US_TXT"), LBL_BTN_OK_TXT, buttonId -> {
            if (buttonId == 0) {
                new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                showContactUs(content);
            } else if (buttonId == 1) {
                MyApp.getInstance().logOutFromDevice(true);
            }
        });
    }

    private void showError() {
        closeAlert();
        currentAlertBox = MyApp.getInstance().getGeneralFun(MyApp.getInstance().getCurrentAct()).showGeneralMessage("", LBL_TRY_AGAIN_TXT, LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> handleBtnClick(buttonId, "ERROR"));
    }

    private void showError(String title, String contentMsg) {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage(title, contentMsg, LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> handleBtnClick(buttonId, "ERROR"));
    }

    private void showNoInternetDialog() {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"), LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> handleBtnClick(buttonId, "NO_INTERNET"));
    }


    private void showErrorOnPlayServiceDialog(String content) {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage("", content, LBL_RETRY_TXT, generalFunc.retrieveLangLBl("Update", "LBL_UPDATE"), buttonId -> handleBtnClick(buttonId, "NO_PLAY_SERVICE"));
    }

    private void showAppUpdateDialog(String content) {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("New update available", "LBL_NEW_UPDATE_AVAIL"), content, LBL_RETRY_TXT, generalFunc.retrieveLangLBl("Update", "LBL_UPDATE"), buttonId -> handleBtnClick(buttonId, "APP_UPDATE"));
    }

    private Context getActContext() {
        return LauncherActivity.this;
    }

    private void handleBtnClick(int buttonId, String alertType) {
        Utils.hideKeyboard(getActContext());
        if (buttonId == 0) {
            if (!alertType.equals("NO_PLAY_SERVICE") && !alertType.equals("APP_UPDATE")) {
                MyApp.getInstance().getCurrentAct().finishAffinity();
            } else {
                checkConfigurations();
            }
        } else {


            String appURL = "";

            if (!MyApp.getInstance().isHMSOnly()) {
                appURL = "http://play.google.com/store/apps/details?id=" + BuildConfig.APPLICATION_ID;
            } else {
                appURL = "market://details?id=" + BuildConfig.APPLICATION_ID;
            }

            if (alertType.equals("NO_PLAY_SERVICE")) {
                boolean isSuccessfulOpen = new ActUtils(getActContext()).openURL("market://details?id=com.google.android.gms");
                if (!isSuccessfulOpen) {
                    new ActUtils(getActContext()).openURL("http://play.google.com/store/apps/details?id=com.google.android.gms");
                }
                checkConfigurations();
            } else if (alertType.equals("NO_PERMISSION")) {
                generalFunc.openSettings();
            } else if (alertType.equals("APP_UPDATE")) {
                boolean isSuccessfulOpen = new ActUtils(getActContext()).openURL("market://details?id=" + BuildConfig.APPLICATION_ID);
                if (!isSuccessfulOpen) {
                    new ActUtils(getActContext()).openURL(appURL);
                }
                checkConfigurations();
            } else if (!alertType.equals("NO_GPS")) {
                checkConfigurations();
            } else {
                new ActUtils(getActContext()).startActForResult(Settings.ACTION_LOCATION_SOURCE_SETTINGS, Utils.REQUEST_CODE_GPS_ON);
            }
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        switch (requestCode) {
            case Utils.REQUEST_CODE_GPS_ON:
            case GeneralFunctions.MY_SETTINGS_REQUEST:
                checkConfigurations();
                break;
            case ERROR_DIALOG_REQUEST_CODE:
                mRetryProviderInstall = true;
                break;
            case Utils.OVERLAY_PERMISSION_REQ_CODE:
                binding.drawOverMsgTxtView.setVisibility(View.GONE);
                if (!generalFunc.canDrawOverlayViews(getActContext())) {
                    binding.drawOverMsgTxtView.setVisibility(View.VISIBLE);
                    new Handler(Looper.myLooper()).postDelayed(this::checkConfigurations, 15000);
                } else {
                    checkConfigurations();
                }
                break;
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        generalFunc.storeData("isInLauncher", "false");
    }

    @Override
    public void onProviderInstalled() {
        providerInstallerCallbackReceived = true;
        if (providerInstallerTimeoutHandler != null) {
            providerInstallerTimeoutHandler.removeCallbacksAndMessages(null);
        }
        checkConfigurations();
    }

    @Override
    public void onProviderInstallFailed(int errorCode, Intent intent) {
        providerInstallerCallbackReceived = true;
        if (providerInstallerTimeoutHandler != null) {
            providerInstallerTimeoutHandler.removeCallbacksAndMessages(null);
        }
        if (GooglePlayServicesUtil.isUserRecoverableError(errorCode)) {
            // Recoverable error. Show a dialog prompting the user to
            // install/update/enable Google Play services.
            GooglePlayServicesUtil.showErrorDialogFragment(errorCode, this, ERROR_DIALOG_REQUEST_CODE, dialog -> {
                // The user chose not to take the recovery action
                onProviderInstallerNotAvailable();
            });
        } else {
            // Google Play services is not available.
            onProviderInstallerNotAvailable();
        }
    }

    private void onProviderInstallerNotAvailable() {
        // This is reached if the provider cannot be updated for some reason.
        // App should consider all HTTP communication to be vulnerable, and take
        // appropriate action.
        providerInstallerCallbackReceived = true;
        if (providerInstallerTimeoutHandler != null) {
            providerInstallerTimeoutHandler.removeCallbacksAndMessages(null);
        }
        checkConfigurations();
        // Only show message if view is still available
        if (binding != null && binding.drawOverMsgTxtView != null) {
            showMessageWithAction(binding.drawOverMsgTxtView, generalFunc.retrieveLangLBl("provider cannot be updated for some reason.", "LBL_PROVIDER_NOT_AVALIABLE_TXT"));
        }
    }

    @Override
    protected void onPostResume() {
        super.onPostResume();
        if (mRetryProviderInstall) {
            ProviderInstaller.installIfNeededAsync(this, this);
        }
        mRetryProviderInstall = false;
    }

    private void showMessageWithAction(View view, String message) {
        Snackbar snackbar = Snackbar.make(view, message, Snackbar.LENGTH_INDEFINITE);
        snackbar.setDuration(10000);
        snackbar.show();
    }

    @Override
    public void onDownload(File file) {
        String decryptStr = AESEnDecryption.getInstance().decrypt(AESEnDecryption.getInstance().fetchKeyAndIVAnData(MyApp.getInstance().readFromFile(file)));
        if (Utils.checkText(decryptStr)) {
            MyApp.getInstance().writeToFile(decryptStr, this);
            setBucketData(MyApp.getInstance().readFromFile(this));
            MyApp.getInstance().callPreLoadData();
        } else {
            onDownloadError("");
        }
    }

    @Override
    public void onDownloadError(String s) {
        downloadGeneralData();
        MyApp.getInstance().callPreLoadData();
    }
}