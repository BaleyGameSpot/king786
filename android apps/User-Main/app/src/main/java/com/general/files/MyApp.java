package com.general.files;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Application;
import android.app.NotificationManager;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.pm.ActivityInfo;
import android.content.res.Configuration;
import android.graphics.Color;
import android.location.Location;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowManager;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.multidex.MultiDex;

import com.act.AccountverificationActivity;
import com.act.AddAddressActivity;
import com.act.AdditionalChargeActivity;
import com.act.AppRestrictedActivity;
import com.act.CarWashBookingDetailsActivity;
import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.LauncherActivity;
import com.act.MainActivity;
import com.act.NetworkChangeReceiver;
import com.act.OnGoingTripDetailsActivity;
import com.act.PaymentWebviewActivity;
import com.act.RentalDetailsActivity;
import com.act.RideDeliveryActivity;
import com.act.SearchPickupLocationActivity;
import com.act.UberXActivity;
import com.act.UberXHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.TrackOrderActivity;
import com.data.models.DataPreLoad;
import com.datepicker.DateUtils;
import com.facebook.appevents.AppEventsLogger;
import com.general.call.CommunicationManager;
import com.general.call.VOIPActivity;
import com.general.features.SafetyTools;
import com.google.android.material.snackbar.Snackbar;
import com.google.gson.Gson;
import com.huawei.hms.push.HmsMessaging;
import com.buddyverse.main.BuildConfig;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.LayoutSessionLoaderViewBinding;
import com.model.ServiceModule;
import com.model.SocketEvents;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.handler.EventService;
import com.utils.CommonUtilities;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

import io.realm.Realm;
import io.realm.RealmConfiguration;

/**
 * Created by Admin on 28-06-2016.
 */
public class MyApp extends Application implements EventService.AppDataListener {

    private String TAG = "MY_APP_LOGS";

    private static MyApp mMyApp;
    public static boolean isJSEnabled = true;

    public static synchronized MyApp getInstance() {
        return mMyApp;
    }

    private GeneralFunctions generalFun;
    private boolean isAppInBackground = true;
    public boolean isGetDetailCall = false;

    private GpsReceiver mGpsReceiver;
    private ActRegisterReceiver actRegisterReceiver;
    private LocalNotification localNotification = null;
    private NetworkChangeReceiver mNetWorkReceiver = null;

    private Activity currentAct = null, initialAct;
    public Activity lastAct;

    public MainActivity mainAct;
    public UberXActivity uberXAct;
    public UberXHomeActivity uberXHomeAct;
    public CommonDeliveryTypeSelectionActivity commonDeliveryAct;
    public CarWashBookingDetailsActivity carWashAct;
    public OnGoingTripDetailsActivity onGoingTripDetailsAct = null;
    public TrackOrderActivity trackOrderActivity;
    public RideDeliveryActivity rideDeliveryActivity;
    public AdditionalChargeActivity additionalChargesAct = null;

    private GenerateAlertBox generateSessionAlert;

    public Location currentLocation;
    private static boolean isAppKilled = false;
    private ActivityLifecycleCallbacks activityLifecycleCallbacks;

    @Override
    public void onCreate() {
        super.onCreate();
        Logger.d(TAG, "Object Created >> MYAPP ");

        Utils.SERVER_CONNECTION_URL = CommonUtilities.SERVER_URL;
        Utils.IS_APP_IN_DEBUG_MODE = BuildConfig.DEBUG ? "Yes" : "No";
        Utils.userType = BuildConfig.USER_TYPE;
        Utils.app_type = BuildConfig.USER_TYPE;
        Utils.USER_ID_KEY = BuildConfig.USER_ID_KEY;
        Utils.IS_OPTIMIZE_MODE_ENABLE = true;
        Utils.eSystem_Type_KIOSK = "";

        isAppKilled = false;
        isGetDetailCall = false;

        GeneralFunctions.GMS_AVAILABLE_OVERRIDE = false;
        GeneralFunctions.IS_GMS_AVAILABLE_DEBUG = false;

        mMyApp = (MyApp) this.getApplicationContext();
        generalFun = new GeneralFunctions(this);
        AppUtils.initializeApp();

        HashMap<String, String> default_params = new HashMap<>();
        default_params.put("HMS_DEVICE", isHMSOnly() ? "Yes" : "No");
        default_params.put("HUAWEI_DEVICE", generalFun.isHmsAvailable() ? "Yes" : "No");

        ApiHandler.default_params = default_params;

        ApiHandler.listOfTypes.clear();
        ApiHandler.listOfTypes.add("loadStaticInfo");
        ApiHandler.listOfTypes.add("GetCancelReasons");
        ApiHandler.listOfTypes.add("getFAQ");
        ApiHandler.listOfTypes.add("staticPage");
        ApiHandler.listOfTypes.add("getAppImages");
        ApiHandler.listOfTypes.add("getMessageHistory");

        ApiHandler.listOf3rdPartyURLs.clear();
        ApiHandler.listOf3rdPartyURLs.add(CommonUtilities.TOLLURL);
        ApiHandler.listOf3rdPartyURLs.add(CommonUtilities.BUCKET_PATH);
        ApiHandler.listOf3rdPartyURLs.add("https://cdn.livechatinc.com/");

        callPreLoadData();

        HashMap<String, String> storeData = new HashMap<>();
        storeData.put("SERVERURL", CommonUtilities.SERVER_URL);
        storeData.put("SERVERWEBSERVICEPATH", CommonUtilities.SERVER_WEBSERVICE_PATH);
        storeData.put("USERTYPE", BuildConfig.USER_TYPE);
        GeneralFunctions.storeData(storeData, this);

        setScreenOrientation();
        new GetCountryList(this);

        try {
            AppEventsLogger.activateApp(this);
        } catch (Exception e) {
            Logger.d("FBError", "Facebook SDK initialization failed: " + e.getMessage());
        }

        Realm.init(this);
        RealmConfiguration config = new RealmConfiguration.Builder().name("FoodApp.realm").schemaVersion(1).build(); //NOSONAR
        Realm.setDefaultConfiguration(config);

        if (mGpsReceiver == null) {
            registerReceiver(); //NOSONAR
        }

        if (actRegisterReceiver == null) {
            registerActReceiver();
        }

        if (isHMSOnly()) {
            try {
                com.huawei.hms.maps.MapsInitializer.initialize(getApplicationContext());
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
            HmsMessaging.getInstance(this).setAutoInitEnabled(true);
        }

    }

    public void callPreLoadData() {
        Utils.APP_SERVICE_URL = generalFun.retrieveValue(Utils.APP_SERVICE_URL_KEY);
        if (Utils.checkText(Utils.APP_SERVICE_URL)) {
            DataPreLoad.getInstance().execute();
        }
    }

    public boolean isHMSOnly() {
        if (!generalFun.isGmsAvailable() && generalFun.isHmsAvailable()) {
            return true;
        }
        return false;
    }

    private void clearFile(OutputStreamWriter outputStreamWriter) {
        try {
            PrintWriter writer = new PrintWriter(outputStreamWriter);
            writer.print("");
        } catch (Exception e) {
            Logger.d("ClearFile", ":" + e.getMessage());
        }
    }

    public void writeToFile(String data, Context context) {
        try {
            OutputStreamWriter outputStreamWriter = new OutputStreamWriter(context.openFileOutput("config_test.txt", Context.MODE_PRIVATE));
            clearFile(outputStreamWriter);
            outputStreamWriter.write(data);
            outputStreamWriter.close();
        } catch (IOException e) {
            Log.e("Exception", "File write failed: " + e.getMessage());
        }
    }

    public String readFromFile(Context context) {

        String ret = "";
        BufferedReader bufferedReader = null;

        try {
            InputStream inputStream = context.openFileInput("config_test.txt");

            if (inputStream != null) {
                InputStreamReader inputStreamReader = new InputStreamReader(inputStream);
                bufferedReader = new BufferedReader(inputStreamReader);
                String receiveString = "";
                StringBuilder stringBuilder = new StringBuilder();

                while ((receiveString = bufferedReader.readLine()) != null) {
                    stringBuilder.append("\n").append(receiveString);
                }

                inputStream.close();
                ret = stringBuilder.toString();
            }
        } catch (FileNotFoundException e) {
            Log.e("File not found: ", e.toString());
        } catch (IOException e) {
            Log.e("Can not read file: ", e.toString());
        } finally {
            try {
                if (bufferedReader != null) {
                    bufferedReader.close();
                }
            } catch (IOException ignored) {
            }
        }

        return ret;
    }

    public String readFromFile(File file) {
        if (file == null) {
            return "";
        }

        String ret = "";
        BufferedReader bufferedReader = null;

        try {
//            InputStreamReader inputStreamReader = new InputStreamReader(getResources().openRawResource(R.raw.config_data));

            bufferedReader = new BufferedReader(new FileReader(file));
            String receiveString = "";
            StringBuilder stringBuilder = new StringBuilder();
            while ((receiveString = bufferedReader.readLine()) != null) {
                stringBuilder.append("\n").append(receiveString);
            }
            ret = stringBuilder.toString();
            bufferedReader.close();
        } catch (FileNotFoundException e) {
            Log.e("File not found: ", e.toString());
        } catch (IOException e) {
            Log.e("Can not read file: ", e.toString());
        } finally {
            try {
                Objects.requireNonNull(bufferedReader).close();
            } catch (IOException ignored) {
            }
        }

        return ret;
    }

    public LocalNotification getLoclaNotificationObj() {
        if (localNotification == null) {
            localNotification = new LocalNotification();
        }
        return localNotification;

    }

    @SuppressLint("SetTextI18n")
    private void openSessionLoaderView(@NonNull Activity act) {
        if (isGetDetailCall) {

            boolean isIgnoreAct = act instanceof LauncherActivity || act instanceof AppRestrictedActivity;

            if (act instanceof LauncherActivity activity && !activity.intCheck.isNetworkConnected() && !activity.intCheck.check_int()) {
                isIgnoreAct = false;
            }
            if (isIgnoreAct) {
                return;
            }

            LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            @NonNull LayoutSessionLoaderViewBinding binding = LayoutSessionLoaderViewBinding.inflate(inflater, null, false);
            String noteValue = generalFun.retrieveLangLBl("", Utils.app_type.equalsIgnoreCase("Passenger") ? ServiceModule.OnlyRideSharingPro ? "LBL_LOCATING_YOU_TXT" : "LBL_FINDING_SERVICES_NEARBY_TXT" : "LBL_LOCATING_YOU_TXT");
            binding.noteTxt.setText(noteValue + " ...");

            MyUtils.setBounceAnimation(this, binding.cardCircleArea, R.anim.session_update_zoom_out, () -> {
                //
                MyUtils.setBounceAnimation(this, binding.cardCircleArea, R.anim.session_update_zoom_in, null);
            });

            act.getWindow().addContentView(binding.getRoot(), new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.MATCH_PARENT));
            act.getWindow().addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
            act.getWindow().setStatusBarColor(getResources().getColor(R.color.white));
        }
    }

    public boolean isDrakModeOn() {
        int currentNightMode = MyApp.getInstance().getApplicationContext().getResources().getConfiguration().uiMode & Configuration.UI_MODE_NIGHT_MASK;

        if (currentNightMode == Configuration.UI_MODE_NIGHT_NO) {
            return false;
        } else return currentNightMode == Configuration.UI_MODE_NIGHT_YES;
    }

    public static Realm getRealmInstance() {
        RealmConfiguration config = new RealmConfiguration.Builder()
                .deleteRealmIfMigrationNeeded()
                .build(); //NOSONAR

        return Realm.getInstance(config);
    }

    public GeneralFunctions getGeneralFun(Context mContext) {
        return new GeneralFunctions(mContext, R.id.backImgView);
    }

    public GeneralFunctions getAppLevelGeneralFunc() {
        if (generalFun == null) {
            generalFun = new GeneralFunctions(this);
        }
        return generalFun;
    }

    public boolean isMyAppInBackGround() {
        return this.isAppInBackground;
    }

    @Override
    protected void attachBaseContext(Context base) {
        super.attachBaseContext(base);
        MultiDex.install(this);
    }

    @Override
    public void onLowMemory() {
        super.onLowMemory();
//        Logger.d(TAG, "Object Destroyed >> MYAPP onLowMemory");
    }

    @Override
    public void onTrimMemory(int level) {
        super.onTrimMemory(level);
//        Logger.d(TAG, "Object Destroyed >> MYAPP onTrimMemory");
    }


    @Override
    public void onTerminate() {
        super.onTerminate();
        Logger.d(TAG, "Object Destroyed >> MYAPP onTerminate");

        isAppKilled = true;

        terminateAppServices();

        if (generalFun.prefHasKey(Utils.iServiceId_KEY)) {
            generalFun.removeValue(Utils.iServiceId_KEY);
        }

    }

    private void configureAppServices() {
        Logger.d(TAG, "Service Initialized");

        SocketEvents.buildEvents(generalFun.getMemberId());

        AppService.getInstance().resetAppServices();
        AppService.getInstance().setAppDataListener(this);
        if (Utils.IS_APP_IN_DEBUG_MODE.equalsIgnoreCase("Yes")) {
            AppService.getInstance().enableDebugging();
        }

        new Handler(Looper.getMainLooper()).postDelayed(() -> CommunicationManager.getInstance().initiateService(generalFun, generalFun.retrieveValue(Utils.USER_PROFILE_JSON)), 2000);
    }

    public void terminateAppServices() {
        SafetyTools.getInstance().dismissAllDialog();
        continueDestroyServices();
    }

    public void resetAppServices() {
//        AppService.destroy();
//
//        new Handler(Looper.getMainLooper()).postDelayed(this::configureAppServices, 2000);
    }

    private void continueDestroyServices() {
        AppService.destroy();
        releaseGpsReceiver();
        releaseActReceiver();
        removeAllRunningInstances();
        unregisterNetWorkReceiver();

        NotificationManager nMgr = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        nMgr.cancelAll();

        releaseAllAct();
    }

    public void refreshWithConfigData() {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct());
        objRefresh.GetConfigData();
    }

    private void releaseGpsReceiver() {
        if (mGpsReceiver != null)
            this.unregisterReceiver(mGpsReceiver);
        this.mGpsReceiver = null;
    }


    private void releaseActReceiver() {

        if (actRegisterReceiver != null)
            this.unregisterReceiver(actRegisterReceiver);
        this.actRegisterReceiver = null;
    }


    private void registerActReceiver() {
        if (actRegisterReceiver == null) {
            IntentFilter mIntentFilter = new IntentFilter();
            mIntentFilter.addAction(String.format("%s%s%s%s%s", "Act", "ivi", "tyR", "egis", "ter"));

            this.actRegisterReceiver = new ActRegisterReceiver();
            registerSysReceiver(this.actRegisterReceiver, mIntentFilter);
        }
    }


    private void registerReceiver() {
        IntentFilter mIntentFilter = new IntentFilter();
        mIntentFilter.addAction(LocationManager.PROVIDERS_CHANGED_ACTION);

        this.mGpsReceiver = new GpsReceiver();
        registerSysReceiver(this.mGpsReceiver, mIntentFilter);
    }


    public void registerSysReceiver(BroadcastReceiver receiver, IntentFilter filter) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            this.registerReceiver(receiver, filter, Context.RECEIVER_NOT_EXPORTED); // NOSONAR
        } else {
            this.registerReceiver(receiver, filter); // NOSONAR
        }
    }

    private void removeAllRunningInstances() {
        Logger.e("NetWorkDEMO", "removeAllRunningInstances called");
        connectReceiver(false);
    }

    private void registerNetWorkReceiver() {

        if (mNetWorkReceiver == null) {
            try {
                Logger.e("NetWorkDemo", "Network connectivity registered");
                IntentFilter mIntentFilter = new IntentFilter();
                mIntentFilter.addAction(ConnectivityManager.CONNECTIVITY_ACTION);
                mIntentFilter.addAction(ConnectivityManager.EXTRA_NO_CONNECTIVITY);
                /*Extra Filter Started */
                mIntentFilter.addAction(ConnectivityManager.EXTRA_IS_FAILOVER);
                mIntentFilter.addAction(ConnectivityManager.EXTRA_REASON);
                mIntentFilter.addAction(ConnectivityManager.EXTRA_EXTRA_INFO);
                /*Extra Filter Ended */
//                mIntentFilter.addAction("android.net.conn.CONNECTIVITY_CHANGE");
//                mIntentFilter.addAction("android.net.wifi.WIFI_STATE_CHANGED");

                this.mNetWorkReceiver = new NetworkChangeReceiver();
                registerSysReceiver(this.mNetWorkReceiver, mIntentFilter);
            } catch (Exception e) {
                Logger.e("NetWorkDemo", "Network connectivity register error occurred");
            }
        }
    }

    private void unregisterNetWorkReceiver() {

        if (mNetWorkReceiver != null)
            try {
                Logger.e("NetWorkDemo", "Network connectivity unregistered");
                this.unregisterReceiver(mNetWorkReceiver);
                this.mNetWorkReceiver = null;
            } catch (Exception e) {
                Logger.e("NetWorkDemo", "Network connectivity register error occurred");
                Logger.e("Exception", "::" + e.getMessage());
            }

    }

    private void RegisterActivity() {
        sendBroadcast(new Intent(String.format("%s%s%s%s%s", "Act", "ivi", "tyR", "egis", "ter"))); //NOSONAR
    }

    public JSONArray GetStringArray(ArrayList<String> data_waypoints) {


        JSONArray jsonArray = new JSONArray();
        if (data_waypoints != null && data_waypoints.size() > 0) {
            for (int i = 0; i < data_waypoints.size(); i++) {
                jsonArray.put(data_waypoints.get(i));
            }
        }
        return jsonArray;
    }

    public boolean isCurrentActByConfigView() {
        return currentAct instanceof MainActivity
                || currentAct instanceof UberXActivity
                /*|| currentAct instanceof UberXHomeActivity*/
                || currentAct instanceof FoodDeliveryHomeActivity
                /*|| currentAct instanceof ServiceHomeActivity*/
                || currentAct instanceof AddAddressActivity
                || currentAct instanceof SearchPickupLocationActivity
                || currentAct instanceof RideDeliveryActivity
                || currentAct instanceof RentalDetailsActivity;
    }

    public void CheckConfIngView() {
        if (isCurrentActByConfigView()) {

            new Handler(Looper.getMainLooper()).postDelayed(() -> {

                if (currentAct != null) {
                    ViewGroup viewGroup = currentAct.findViewById(android.R.id.content);
                    if (currentAct instanceof RideDeliveryActivity) {
                        viewGroup = currentAct.findViewById(R.id.MainLayout);
                    }
                    if (isCurrentActByConfigView()) {
                        OpenNoLocationView.getInstance(currentAct, viewGroup).configView(false);
                    }
                }
            }, 500);
        }
    }

    private void setScreenOrientation() {
        if (activityLifecycleCallbacks == null) {
            activityLifecycleCallbacks = new ActivityLifecycleCallbacks() {

                @Override
                public void onActivityCreated(Activity activity, Bundle savedInstanceState) {
                    lastAct = currentAct;
                    Utils.runGC();
                    // new activity created; force its orientation to portrait
                    try {
                        activity.setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);
                    } catch (Exception e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                    activity.setTitle(getResources().getString(R.string.app_name));

                    if (!(activity instanceof LauncherActivity) && !(activity instanceof AppRestrictedActivity) && !(activity instanceof AccountverificationActivity) && generalFun.isUserLoggedIn() && (activity.isTaskRoot() || (savedInstanceState != null && savedInstanceState.containsKey("RESTART_STATE") && savedInstanceState.getString("RESTART_STATE").equalsIgnoreCase("true"))) && initialAct == null) {
                        configureAppServices();
                    }

                    setCurrentAct(activity);
                    activity.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_HIDDEN);
                }

                @Override
                public void onActivityStarted(Activity activity) {
                    Utils.runGC();
                }

                @Override
                public void onActivityResumed(Activity activity) {
                    openSessionLoaderView(activity);
                    Utils.runGC();

                    setCurrentAct(activity);

                    Logger.d("CheckAppBackGround", "::" + isAppInBackground + " | currentAct-> " + currentAct);

                    isAppInBackground = false;

                    LocalNotification.clearAllNotifications();

                    CheckConfIngView();

                    DateUtils.ok_str = generalFun.retrieveLangLBl("Ok", "LBL_OK");
                    DateUtils.cancel_str = generalFun.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT");
                }

                @Override
                public void onActivityPaused(Activity activity) {
                    Utils.runGC();
                    isAppInBackground = true;
                }

                @Override
                public void onActivityStopped(Activity activity) {
                    Utils.runGC();
                }


                @Override
                public void onActivitySaveInstanceState(Activity activity, Bundle bundle) {
                    /*Called to retrieve per-instance state from an activity before being killed so that the state can be restored in onCreate(Bundle) or onRestoreInstanceState(Bundle) (the Bundle populated by this method will be passed to both).*/
                    //  removeAllRunningInstances();
                }

                @Override
                public void onActivityDestroyed(Activity activity) {

                    Utils.runGC();
                    Utils.hideKeyboard(activity);


                    if (initialAct != null && initialAct == activity) {
                        Logger.e(TAG, "App Destroyed");
                        onTerminate();
                    }

                    if (activity instanceof UberXActivity && uberXAct == activity) {
                        uberXAct = null;
                    }

                    if (activity instanceof UberXHomeActivity && uberXHomeAct == activity) {
                        uberXHomeAct = null;
                    }

                    if (activity instanceof CommonDeliveryTypeSelectionActivity && commonDeliveryAct == activity) {
                        commonDeliveryAct = null;
                    }

                    if (activity instanceof TrackOrderActivity && trackOrderActivity == activity) {
                        trackOrderActivity = null;
                    }
                    if (currentAct instanceof CarWashBookingDetailsActivity && currentAct == activity) {
                        carWashAct = null;
                    }

                    if (activity instanceof MainActivity && mainAct == activity) {
                        mainAct = null;
                    }

                    if (activity instanceof OnGoingTripDetailsActivity && onGoingTripDetailsAct == activity) {
                        onGoingTripDetailsAct = null;
                    }
                    if (activity instanceof AdditionalChargeActivity && additionalChargesAct == activity) {
                        additionalChargesAct = null;
                    }

                    LocalNotification.clearAllNotifications();
                }
            };
            registerActivityLifecycleCallbacks(activityLifecycleCallbacks);
        }
    }

    private void connectReceiver(boolean isConnect) {
        if (isConnect && mNetWorkReceiver == null) {
            registerNetWorkReceiver();
        } else if (!isConnect && mNetWorkReceiver != null) {
            unregisterNetWorkReceiver();
        }
    }


    public Activity getCurrentAct() {
        return currentAct;
    }

    public String getVersionName() {
        return BuildConfig.VERSION_NAME;
    }

    public String getVersionCode() {
        return BuildConfig.VERSION_CODE + "";
    }

    private void releaseAllAct() {
        lastAct = null;
        initialAct = null;
        mainAct = null;
        uberXAct = null;
        uberXHomeAct = null;
        onGoingTripDetailsAct = null;
        additionalChargesAct = null;
    }

    private void setCurrentAct(Activity currentAct) {

        if ((!(currentAct instanceof LauncherActivity) || initialAct == null) && isAppKilled()) {
            onCreate();
        }

        this.currentAct = currentAct;

        WakeLocker.getInstance().release(this.currentAct);
        if (currentAct instanceof VOIPActivity) {
            WakeLocker.getInstance().acquire();
        }

        RegisterActivity();

        if (currentAct instanceof LauncherActivity) {
            releaseAllAct();
        }


        if (!(currentAct instanceof LauncherActivity) && (initialAct == null || initialAct.isFinishing())) {
            initialAct = currentAct;
            Logger.e(TAG, "SET INITIAL ACT::" + initialAct.toString());
        }

        if (currentAct instanceof MainActivity) {
            mainAct = (MainActivity) currentAct;
        }
        if (currentAct instanceof AdditionalChargeActivity) {
            additionalChargesAct = (AdditionalChargeActivity) currentAct;
        }

        if (currentAct instanceof RideDeliveryActivity) {
            rideDeliveryActivity = (RideDeliveryActivity) currentAct;
        }

        if (currentAct instanceof CarWashBookingDetailsActivity) {
            carWashAct = (CarWashBookingDetailsActivity) currentAct;
        }

        if (currentAct instanceof TrackOrderActivity) {
            trackOrderActivity = (TrackOrderActivity) currentAct;
        }

        if (currentAct instanceof UberXActivity) {
            uberXAct = (UberXActivity) currentAct;
            mainAct = null;
            additionalChargesAct = null;
        }
        if (currentAct instanceof UberXHomeActivity) {
            uberXHomeAct = (UberXHomeActivity) currentAct;
            mainAct = null;
            additionalChargesAct = null;
        }

        if (currentAct instanceof OnGoingTripDetailsActivity) {
            onGoingTripDetailsAct = (OnGoingTripDetailsActivity) currentAct;
        }

        connectReceiver(true);
    }

    public static boolean isAppKilled() {
        if (mMyApp == null || !isAppInstanceAvailable()) {
            return true;
        }
        return isAppKilled;
    }

    public static boolean isAppInstanceAvailable() {
        try {
            if (MyApp.getInstance() == null || MyApp.getInstance().getApplicationContext() == null || MyApp.getInstance().getApplicationContext().getPackageManager() == null || MyApp.getInstance().getApplicationContext().getPackageName() == null) {
                return false;
            }
        } catch (Exception e) {
            return false;
        }

        return true;
    }

    public void restartWithGetDataApp() {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct());
        objRefresh.getData();
    }

    public void refreshData() {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getApplicationContext());
        objRefresh.getLatestDataOnly();
    }

    public void restartWithGetDataApp(String tripId) {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct(), tripId);
        objRefresh.getData();
    }

    public void refreshView(Activity context, String responseString) {
        storeUserData(responseString);
        new OpenMainProfile(context, "", false, generalFun).startProcess();
    }

    public void storeUserData(String responseString) {
        if (!responseString.trim().equalsIgnoreCase("")) {
            generalFun.storeData(Utils.USER_PROFILE_JSON, generalFun.getJsonValue("USER_DATA", responseString));
        }
    }

    public void notifySessionTimeOut() {
        if (generateSessionAlert != null) {
            return;
        }

        terminateAppServices();

        generateSessionAlert = new GenerateAlertBox(MyApp.getInstance().getCurrentAct());


        generateSessionAlert.setContentMessage(generalFun.retrieveLangLBl("", "LBL_BTN_TRIP_CANCEL_CONFIRM_TXT"),
                generalFun.retrieveLangLBl("Your session is expired. Please login again.", "LBL_SESSION_TIME_OUT"));
        generateSessionAlert.setPositiveBtn(generalFun.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
        generateSessionAlert.setCancelable(false);
        generateSessionAlert.setBtnClickList(btn_id -> {

            if (btn_id == 1) {
                forceLogoutRemoveData();
            }
        });

        generateSessionAlert.showSessionOutAlertBox();
    }

    public void logOutFromDevice(boolean isForceLogout) {

        if (generalFun != null) {
            final HashMap<String, String> parameters = new HashMap<>();

            parameters.put("type", "callOnLogout");
            parameters.put("iMemberId", generalFun.getMemberId());
            parameters.put("UserType", Utils.userType);

            ApiHandler.execute(getCurrentAct(), parameters, true, false, generalFun, responseString -> {

                if (responseString != null && !responseString.equals("")) {

                    boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                    if (isDataAvail) {

                        if (getCurrentAct() instanceof MainActivity) {
                            ((MainActivity) getCurrentAct()).releaseScheduleNotificationTask();
                        }

                        forceLogoutRemoveData();
                    } else {
                        if (isForceLogout) {
                            generalFun.showGeneralMessage("",
                                    generalFun.retrieveLangLBl("", generalFun.getJsonValue(Utils.message_str, responseString)), buttonId -> (MyApp.getInstance().getGeneralFun(getCurrentAct())).restartApp());
                        } else {
                            generalFun.showGeneralMessage("",
                                    generalFun.retrieveLangLBl("", generalFun.getJsonValue(Utils.message_str, responseString)));
                        }
                    }
                } else {
                    if (isForceLogout) {
                        generalFun.showError(buttonId -> (MyApp.getInstance().getGeneralFun(getCurrentAct())).restartApp());
                    } else {
                        generalFun.showError();
                    }
                }
            });
        }
    }

    public void forceLogoutRemoveData() {

        onTerminate();
        if (generalFun.retrieveValue("isUserSmartLogin").equalsIgnoreCase("Yes")) {
            HashMap<String, String> storeData = new HashMap<>();
            storeData.put(Utils.iMemberId_KEY, generalFun.retrieveValue(Utils.iMemberId_KEY));
            storeData.put(Utils.isUserLogIn, generalFun.retrieveValue(Utils.isUserLogIn));
            storeData.put(Utils.USER_PROFILE_JSON, generalFun.retrieveValue(Utils.USER_PROFILE_JSON));
            generalFun.storeData("QUICK_LOGIN_DIC", new Gson().toJson(storeData));
        } else {
            generalFun.storeData("isFirstTimeSmartLoginView", "No");
            generalFun.storeData("isUserSmartLogin", "No");
        }
        generalFun.storeData("SERVICE_HOME_DATA", "");
        generalFun.storeData("SERVICE_HOME_DATA_23", "");
        MyApp.getInstance().writeToFile("", this);
        generalFun.logOutUser(MyApp.this);
        CommonUtilities.ageRestrictServices.clear();
        generalFun.restartApp();
    }

    public ArrayList<String> checkCameraWithMicPermission(boolean isCamera, boolean isPhone) {
        ArrayList<String> requestPermissions = new ArrayList<>();
        if (isCamera) {
            requestPermissions.add(Manifest.permission.CAMERA);
        }
        if (isPhone) {
            requestPermissions.add(Manifest.permission.READ_PHONE_STATE);
        }
        requestPermissions.add(Manifest.permission.RECORD_AUDIO);
        return requestPermissions;
    }

    public boolean checkMicWithStorePermission(GeneralFunctions generalFunc, boolean openDialog) {
        ArrayList<String> requestPermissions = new ArrayList<>();
        requestPermissions.add(Manifest.permission.RECORD_AUDIO);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            if (android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                requestPermissions.add(Manifest.permission.READ_MEDIA_AUDIO);
            } else {
                requestPermissions.add(Manifest.permission.READ_EXTERNAL_STORAGE);
            }
        } else {
            requestPermissions.add(Manifest.permission.WRITE_EXTERNAL_STORAGE);
        }
        return generalFunc.isAllPermissionGranted(openDialog, requestPermissions, MyUtils.AUDIO_PERMISSION_REQ_CODE);
    }

    public void showOutsatandingdilaog(View view) {
        String userProfileJson = generalFun.retrieveValue(Utils.USER_PROFILE_JSON);
        if (userProfileJson != null && generalFun.getJsonValue("fOutStandingAmount", userProfileJson) != null
                && GeneralFunctions.parseFloatValue(0, generalFun.getJsonValue("fOutStandingAmount", userProfileJson)) > 0) {
            Snackbar snackbar = Snackbar
                    .make(view, generalFun.getJsonValue("PaymentPendingMsg", userProfileJson), Snackbar.LENGTH_INDEFINITE)
                    .setAction(generalFun.retrieveLangLBl("", "LBL_BTN_PAYMENT_TXT"), view1 -> {
                        Bundle bn = new Bundle();
                        bn.putString("url", generalFun.getJsonValue("OUTSTANDING_PAYMENT_URL", userProfileJson));
                        bn.putBoolean("handleResponse", true);
                        bn.putBoolean("isBack", false);
                        bn.putBoolean("isApiCall", true);
                        new ActUtils(getCurrentAct()).startActWithData(PaymentWebviewActivity.class, bn);
                    });
            snackbar.setActionTextColor(Color.YELLOW);
            snackbar.setTextMaxLines(10);
            snackbar.setDuration(8000);

            snackbar.show();

        }

    }

    // TODO: 30-01-2023 >> Do not delete this Method OR Rename
    public boolean validateApiResponse(String response) {

        JSONObject responseObj = generalFun.getJsonObject(response);

        if (generalFun.getJsonValueStr("RESTRICT_APP", responseObj).equalsIgnoreCase("Yes")) {
            if (currentAct instanceof AppRestrictedActivity) {
                return true;
            }
            isGetDetailCall = false;
            Bundle bn = new Bundle();
            bn.putString("RESTRICT_APP", response);
            new ActUtils(currentAct).startActWithData(AppRestrictedActivity.class, bn);
            currentAct.finishAffinity();
            return true;
        }
        return false;
    }

    @Override
    public void onMessageReceived(String eventName, Object dataObj) {

        if (!(dataObj instanceof String) && !(dataObj instanceof JSONObject)) {
            return;
        }

        SocketMessageReceiver.getInstance().handleMsg(eventName, dataObj.toString());
    }

}