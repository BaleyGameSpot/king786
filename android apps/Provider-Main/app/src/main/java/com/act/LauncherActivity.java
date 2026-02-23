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

import com.activity.ParentActivity;
import com.general.PermissionHandlers;
import com.general.call.VOIPMsgHandler;
import com.general.files.AESEnDecryption;
import com.general.files.ActUtils;
import com.general.files.ConfigureMemberData;
import com.general.files.GeneralFunctions;
import com.general.files.GetFeatureClassList;
import com.general.files.GetLocationUpdates;
import com.general.files.GetUserData;
import com.general.files.MyApp;
import com.general.files.OpenMainProfile;
import com.general.files.SetGeneralData;
import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.GoogleApiAvailability;
import com.google.android.gms.security.ProviderInstaller;
import com.google.android.material.snackbar.Snackbar;
import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ActivityLauncherBinding;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.DateTimeUtils;
import com.utils.DeviceSettings;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONObject;

import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;

public class LauncherActivity extends ParentActivity implements ProviderInstaller.ProviderInstallListener, ServerTask.FileDataResponse {

    private GenerateAlertBox currentAlertBox;
    private static final int ERROR_DIALOG_REQUEST_CODE = 1;
    private String response_str_generalConfigData = "", response_str_autologin = "";
    private boolean mRetryProviderInstall, isnotification = false, isPermissionShown_general;
    private String LBL_BTN_OK_TXT, LBL_CANCEL_TXT, LBL_RETRY_TXT, LBL_TRY_AGAIN_TXT;
    private final ArrayList<String> requestPermissions = new ArrayList<>();
    private ActivityLauncherBinding binding;


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

        if (generalFunc.isUserLoggedIn() && Utils.checkText(generalFunc.getMemberId()) &&
                generalFunc.getJsonValueStr("vAvailability", obj_userProfile).equalsIgnoreCase("Available")) {
            MyApp.getInstance().setOnlineState();
        }

        new Handler().postDelayed(() -> {
            if (generalFunc.isUserLoggedIn() && Utils.checkText(generalFunc.getMemberId())) {
                if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                    MyApp.getInstance().isGetDetailCall = true;
                }
                if (DeviceSettings.isDeviceGPSEnabled()) {
                    GetLocationUpdates.getInstance().startLocationUpdates(null, null);
                }
            }

            ProviderInstaller.installIfNeededAsync(LauncherActivity.this, LauncherActivity.this);
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

    private void checkConfigurations(boolean isPermissionShown) {
        binding.drawOverMsgTxtView.setVisibility(View.GONE);

        isPermissionShown_general = isPermissionShown;

        closeAlert();

        int status = (GoogleApiAvailability.getInstance()).isGooglePlayServicesAvailable(getActContext());
        if (status == ConnectionResult.SERVICE_VERSION_UPDATE_REQUIRED) {
            showErrorOnPlayServiceDialog(generalFunc.retrieveLangLBl("This application requires updated google play service. " +
                    "Please install Or update it from play store", "LBL_UPDATE_PLAY_SERVICE_NOTE"));
            return;
        } else if (status != ConnectionResult.SUCCESS) {
            showErrorOnPlayServiceDialog(generalFunc.retrieveLangLBl("This application requires updated google play service. " +
                    "Please install Or update it from play store", "LBL_UPDATE_PLAY_SERVICE_NOTE"));
            return;
        }
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

            boolean isAppRestarted = generalFunc.retrieveValue("APP_RESTART_EVENT").equalsIgnoreCase("Yes");
            boolean isBatterySetting = generalFunc.retrieveValue(PermissionHandlers.BATTERY_SETTINGS_KEY).equalsIgnoreCase("Yes");
            if (isAppRestarted && isBatterySetting) {
                generalFunc.storeData("APP_RESTART_EVENT", "No");
                new OpenMainProfile(getActContext(), true, generalFunc, isnotification).startProcess();
                return;
            }

            generalFunc.storeData("APP_RESTART_EVENT", "No");
            generalFunc.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "No");

            if (this.response_str_autologin.trim().equalsIgnoreCase("")) {
                new OpenMainProfile(getActContext(), true, generalFunc, isnotification).startProcess();
                autoLogin();
            } else {
                continueAutoLogin(this.response_str_autologin);
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
        parameters.putAll(GetFeatureClassList.getAllGeneralClasses());

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (isFinishing()) {
                reStartAppDialog();
                return;
            }

            if (responseObj != null) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    if (!generalFunc.isAllPermissionGranted(isPermissionShown_general, requestPermissions)) {
                        response_str_generalConfigData = responseString;
                        showNoPermission();
                        return;
                    }

                    continueDownloadGeneralData(responseString);

                } else {
                    if (!generalFunc.getJsonValueStr("isAppUpdate", responseObj).trim().equals("")
                            && generalFunc.getJsonValueStr("isAppUpdate", responseObj).equals("true")) {

                        showAppUpdateDialog(generalFunc.retrieveLangLBl("New update is available to download. " +
                                        "Downloading the latest update, you will get latest features, improvements and bug fixes.",
                                generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                    } else {
                        String setMsg = LBL_TRY_AGAIN_TXT;
                        if (Utils.checkText(generalFunc.getJsonValueStr(Utils.message_str, responseObj))) {
                            setMsg = generalFunc.getJsonValueStr(Utils.message_str, responseObj);
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


        if (!generalFunc.isAllPermissionGranted(true, requestPermissions)) {
            showNoPermission();
            return;
        }

        Bundle bn = new Bundle();
        bn.putBoolean("isAnimated", true);
        new ActUtils(getActContext()).startActWithData(AppLoginActivity.class, bn);
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
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

            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null) {

                if (generalFunc.getJsonValueStr("changeLangCode", responseObj).equalsIgnoreCase("Yes")) {
                    new ConfigureMemberData(responseString, generalFunc, getActContext(), false);
                }
                String message = generalFunc.getJsonValueStr(Utils.message_str, responseObj);

                if (message.equals("SESSION_OUT")) {
                    MyApp.getInstance().notifySessionTimeOut();
                    Utils.runGC();
                    return;
                }

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                    if (Utils.checkText(generalFunc.retrieveValue("RTC_DATA_offer"))) {
                        new Handler(Looper.getMainLooper()).postDelayed(() -> {
                            //
                            VOIPMsgHandler.performAction(generalFunc, generalFunc.retrieveValue("RTC_DATA_offer"));
                            generalFunc.storeData("RTC_DATA_offer", "");
                        }, 1000);
                    }

                    if (!generalFunc.isAllPermissionGranted(isPermissionShown_general, requestPermissions)) {
                        response_str_autologin = responseString;
                        showNoPermission();
                        return;
                    }
                    continueAutoLogin(responseString);

                } else {
                    if (!generalFunc.getJsonValueStr("isAppUpdate", responseObj).trim().equals("")
                            && generalFunc.getJsonValueStr("isAppUpdate", responseObj).equals("true")) {

                        showAppUpdateDialog(generalFunc.retrieveLangLBl("New update is available to download. " +
                                        "Downloading the latest update, you will get latest features, improvements and bug fixes.",
                                generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                    } else {

                        if (generalFunc.getJsonValueStr(Utils.message_str, responseObj).equalsIgnoreCase("LBL_CONTACT_US_STATUS_NOTACTIVE_COMPANY") ||
                                generalFunc.getJsonValueStr(Utils.message_str, responseObj).equalsIgnoreCase("LBL_ACC_DELETE_TXT") ||
                                generalFunc.getJsonValueStr(Utils.message_str, responseObj).equalsIgnoreCase("LBL_CONTACT_US_STATUS_NOTACTIVE_DRIVER")) {

                            showContactUs(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));

                            return;
                        }
                        showError(generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                    }
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
        JSONObject responseObj = generalFunc.getJsonObject(responseString);
        DateTimeUtils.setDateTimeFormat(generalFunc, responseObj);

        storeImportantData(responseString);

        generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
        generalFunc.storeData(Utils.SESSION_ID_KEY, generalFunc.getJsonValue("tSessionId", message));
        generalFunc.storeData(Utils.DEVICE_SESSION_ID_KEY, generalFunc.getJsonValue("tDeviceSessionId", message));
        generalFunc.storeData(Utils.WORKLOCATION, generalFunc.getJsonValue("vWorkLocation", message));

        new OpenMainProfile(getActContext(), true, generalFunc, isnotification).startProcess();
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

    private void showError(String contentMsg) {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage("", contentMsg, LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> handleBtnClick(buttonId, "ERROR"));
    }

    private void showNoInternetDialog() {
        closeAlert();
        currentAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"), LBL_CANCEL_TXT, LBL_RETRY_TXT, buttonId -> handleBtnClick(buttonId, "NO_INTERNET"));
    }


    public void showNoPermission() {
        currentAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application requires some permission to be granted to work. Please allow it.",
                "LBL_ALLOW_PERMISSIONS_APP"), LBL_CANCEL_TXT, generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS"), buttonId -> handleBtnClick(buttonId, "NO_PERMISSION"));
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
                checkConfigurations(false);
            }

        } else if (alertType.equals("APP_UPDATE")) {
            boolean isSuccessfulOpen = new ActUtils(getActContext()).openURL("market://details?id=" + BuildConfig.APPLICATION_ID);
            if (!isSuccessfulOpen) {
                new ActUtils(getActContext()).openURL("http://play.google.com/store/apps/details?id=" + BuildConfig.APPLICATION_ID);
            }
            checkConfigurations(false);
        } else if (alertType.equals("NO_PERMISSION")) {
            generalFunc.openSettings();

        } else {
            if (alertType.equals("NO_PLAY_SERVICE")) {
                boolean isSuccessfulOpen = new ActUtils(getActContext()).openURL("market://details?id=com.google.android.gms");
                if (!isSuccessfulOpen) {
                    new ActUtils(getActContext()).openURL("http://play.google.com/store/apps/details?id=com.google.android.gms");
                }
                checkConfigurations(false);
            } else if (!alertType.equals("NO_GPS")) {
                checkConfigurations(false);
            } else {
                new ActUtils(getActContext()).
                        startActForResult(Settings.ACTION_LOCATION_SOURCE_SETTINGS, Utils.REQUEST_CODE_GPS_ON);
                checkConfigurations(false);
            }

        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        switch (requestCode) {
            case Utils.REQUEST_CODE_GPS_ON -> checkConfigurations(false);
            case GeneralFunctions.MY_SETTINGS_REQUEST -> checkConfigurations(false);
            case Utils.OVERLAY_PERMISSION_REQ_CODE -> {
                binding.drawOverMsgTxtView.setVisibility(View.GONE);
                if (!generalFunc.canDrawOverlayViews(getActContext())) {
                    binding.drawOverMsgTxtView.setVisibility(View.VISIBLE);
                    new Handler(Looper.myLooper()).postDelayed(() -> checkConfigurations(true), 15000);
                } else {
                    checkConfigurations(true);
                }
            }
            case ERROR_DIALOG_REQUEST_CODE -> mRetryProviderInstall = true;
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String permissions[], int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == GeneralFunctions.MY_PERMISSIONS_REQUEST) {
            if (!generalFunc.isAllPermissionGranted(false, requestPermissions)) {
                return;
            }
            checkConfigurations(false);
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        generalFunc.storeData("isInLauncher", "false");
    }

    @Override
    public void onProviderInstalled() {
        checkConfigurations(true);
    }

    @Override
    public void onProviderInstallFailed(int errorCode, Intent intent) {

        int resultCode = GoogleApiAvailability.getInstance().isGooglePlayServicesAvailable(this);
        if (resultCode != ConnectionResult.SUCCESS) {
            GoogleApiAvailability.getInstance().showErrorDialogFragment(this, errorCode, ERROR_DIALOG_REQUEST_CODE,
                    dialog -> {
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
        checkConfigurations(true);
        showMessageWithAction(binding.drawOverMsgTxtView, generalFunc.retrieveLangLBl("provider cannot be updated for some reason.", "LBL_PROVIDER_NOT_AVALIABLE_TXT"));
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