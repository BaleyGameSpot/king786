package com.general.files;

import static com.activity.ParentActivity.LOCATION_PERMISSIONS_REQUEST;

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
import android.location.Location;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.PowerManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.WindowManager;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.multidex.MultiDex;

import com.act.AddAddressActivity;
import com.act.AppRestrictedActivity;
import com.act.DriverArrivedActivity;
import com.act.LauncherActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.NetworkChangeReceiver;
import com.act.WorkingtrekActivity;
import com.act.deliverAll.LiveTaskListActivity;
import com.data.models.DataPreLoad;
import com.datepicker.DateUtils;
import com.facebook.appevents.AppEventsLogger;
import com.general.PermissionHandlers;
import com.general.ServiceRequest;
import com.general.call.CommunicationManager;
import com.general.call.VOIPActivity;
import com.general.features.SafetyTools;
import com.google.common.reflect.TypeToken;
import com.google.gson.Gson;
import com.google.gson.JsonElement;
import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.LayoutSessionLoaderViewBinding;
import com.model.SocketEvents;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.handler.EventService;
import com.utils.CommonUtilities;
import com.utils.DeviceSettings;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.NavigationSensor;
import com.utils.Utils;
import com.view.GenerateAlertBox;

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
import java.util.Map;


public class MyApp extends Application implements EventService.AppDataListener {

    private String TAG = "MY_APP_LOGS";
    private GeneralFunctions generalFun;

    private ActivityLifecycleCallbacks lifecycleCallBacks;
    private GpsReceiver mGpsReceiver = null;
    private ActRegisterReceiver actRegisterReceiver;
    private static MyApp mMyApp;
    public static boolean isJSEnabled = true;

    public static synchronized MyApp getInstance() {
        return mMyApp;
    }

    boolean isAppInBackground = true;
    public boolean isGetDetailCall = false;
    public boolean ispoolRequest = false;

    private Activity currentAct = null, initialAct;
    public Activity lastAct;

    public MainActivity mainAct;
    public MainActivity_22 main22Act;
    public DriverArrivedActivity driverArrivedAct;
    public AddAddressActivity addAddressAct;
    public WorkingtrekActivity activeTripAct;
    public LiveTaskListActivity liveTaskListAct;
    private NetworkChangeReceiver mNetWorkReceiver = null;

    private GenerateAlertBox generateSessionAlert;

    private static boolean isDriverOnline = false;
    private static boolean isOnJob = false;
    private static boolean isAppKilled = false;

    RecurringTask serviceReqEventTimer;

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
        isDriverOnline = false;

        mMyApp = (MyApp) this.getApplicationContext();
        generalFun = new GeneralFunctions(this);
        AppUtils.initializeApp();

        configProviderOnJob(false);

        ServiceRequest.forceRestObj();

        UpdateDirections.clearAllListeners();

        ApiHandler.listOfTypes.clear();
        ApiHandler.listOfTypes.add("loadStaticInfo");
        ApiHandler.listOfTypes.add("GetCancelReasons");
//        ApiHandler.listOfTypes.add("getFAQ");
        ApiHandler.listOfTypes.add("staticPage");
        ApiHandler.listOfTypes.add("getAppImages");
        ApiHandler.listOfTypes.add("getMessageHistory");
        ApiHandler.listOfTypes.add("getCabRequestAddress");

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
            Logger.d("FBError", "::" + e.getMessage());
        }


        if (mGpsReceiver == null) {
            registerReceiver(); //NOSONAR
        }

        if (actRegisterReceiver == null) {
            registerActReceiver();
        }

    }

    public void callPreLoadData() {
        Utils.APP_SERVICE_URL = generalFun.retrieveValue(Utils.APP_SERVICE_URL_KEY);
        if (Utils.checkText(Utils.APP_SERVICE_URL)) {
            DataPreLoad.getInstance().execute();
        }
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
            } catch (IOException e) {
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

        } catch (FileNotFoundException e) {
            Log.e("File not found: ", e.toString());
        } catch (IOException e) {
            Log.e("Can not read file: ", e.toString());
        } finally {
            try {
                if (bufferedReader != null) {
                    bufferedReader.close();
                }
            } catch (IOException e) {
            }
        }

        return ret;
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
            String noteValue = generalFun.retrieveLangLBl("Locating you", Utils.app_type.equalsIgnoreCase("Passenger") ? "LBL_FINDING_SERVICES_NEARBY_TXT" : "LBL_LOCATING_YOU_TXT");
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
        //  Logger.d("Api", "Object Destroyed >> MYAPP onLowMemory");
    }

    @Override
    public void onTrimMemory(int level) {
        super.onTrimMemory(level);
        // Logger.d(TAG, "Object Destroyed >> MYAPP onTrimMemory");
    }

    @Override
    public void onTerminate() {
        super.onTerminate();
        Logger.d(TAG, "Object Destroyed >> MYAPP onTerminate");

        isAppKilled = true;

        terminateAppServices();
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

    private void removeLocationUpdates() {
        try {
            if (GetLocationUpdates.retrieveInstance() != null) {
                GetLocationUpdates.getInstance().destroyLocUpdates(MyApp.this);
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private void releaseGpsReceiver() {
        if (mGpsReceiver != null)
            this.unregisterReceiver(mGpsReceiver);
        this.mGpsReceiver = null;

    }

    private void releaseactReceiver() {

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

        mIntentFilter.addAction(PowerManager.ACTION_POWER_SAVE_MODE_CHANGED);
        mIntentFilter.addAction("miui.intent.action.POWER_SAVE_MODE_CHANGED");
        mIntentFilter.addAction("huawei.intent.action.POWER_MODE_CHANGED_ACTION");

        this.mGpsReceiver = new GpsReceiver();
        registerSysReceiver(this.mGpsReceiver, mIntentFilter);

        //demo for Button Register
        IntentFilter btnmIntentFilter = new IntentFilter();
        btnmIntentFilter.addAction("BUTTONHANDLING");
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
                this.unregisterReceiver(mNetWorkReceiver);
                this.mNetWorkReceiver = null;
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

    }

    private void RegisterActivity() {
        sendBroadcast(new Intent(String.format("%s%s%s%s%s", "Act", "ivi", "tyR", "egis", "ter")));
    }

    private void setScreenOrientation() {
        if (lifecycleCallBacks == null) {
            lifecycleCallBacks = new ActivityLifecycleCallbacks() {

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

                    if (activity instanceof LauncherActivity) {
                        resetLocationService();
                    }

                    if ((activity instanceof MainActivity || activity instanceof MainActivity_22 || activity instanceof DriverArrivedActivity || activity instanceof WorkingtrekActivity || activity instanceof LiveTaskListActivity) && initialAct == null) {
                        //Reset PubNub instance
                        configureAppServices();
                    }

                    setCurrentAct(activity);
                    activity.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_HIDDEN);

                    if (!(activity instanceof MainActivity || activity instanceof MainActivity_22)) {
                        activity.getWindow().addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON);
                    }

                }

                @Override
                public void onActivityStarted(Activity activity) {
                    Utils.runGC();
                }

                @Override
                public void onActivityResumed(Activity activity) {
                    openSessionLoaderView(activity);
                    Utils.runGC();

                    if (mGpsReceiver == null) {
                        registerReceiver(); // NOSONAR
                    }
                    setCurrentAct(activity);

                    DateUtils.ok_str = generalFun.retrieveLangLBl("Ok", "LBL_OK");
                    DateUtils.cancel_str = generalFun.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT");

                    Logger.d("CheckAppBackGround", "::" + isAppInBackground + " | currentAct-> " + currentAct);
                    isAppInBackground = false;
                    Utils.sendBroadCast(getApplicationContext(), Utils.BACKGROUND_APP_RECEIVER_INTENT_ACTION);
                    LocalNotification.clearAllNotifications();

                    configureAppBadgeFloat();
                }

                @Override
                public void onActivityPaused(Activity activity) {
                    Utils.runGC();
                    isAppInBackground = true;
                    Utils.sendBroadCast(getApplicationContext(), Utils.BACKGROUND_APP_RECEIVER_INTENT_ACTION);

                    configureAppBadgeFloat();
                }

                @Override
                public void onActivityStopped(Activity activity) {
                    Logger.d("AppBackground", "onStop");
                    Utils.runGC();
                }

                @Override
                public void onActivitySaveInstanceState(Activity activity, Bundle bundle) {
                    generalFun.storeData("APP_RESTART_EVENT", "Yes");
                    if (!DeviceSettings.isBatterySaverDisabled()) {
                        generalFun.storeData(PermissionHandlers.BATTERY_SETTINGS_KEY, "Yes");
                    }
                    removeAllRunningInstances();
                }

                @Override
                public void onActivityDestroyed(Activity activity) {

                    Utils.runGC();
                    Utils.hideKeyboard(activity);
                    CameraResultService.stopService(activity);

                    if (initialAct != null && initialAct == activity) {
                        Logger.e(TAG, "App Destroyed");
                        onTerminate();
                    }

                    if (activity instanceof DriverArrivedActivity && activity == driverArrivedAct) {
                        driverArrivedAct = null;
                    }
                    if (activity instanceof MainActivity && activity == mainAct) {
                        mainAct = null;
                    }
                    if (activity instanceof MainActivity_22 && activity == main22Act) {
                        main22Act = null;
                    }
                    if (activity instanceof WorkingtrekActivity && activity == activeTripAct) {
                        activeTripAct = null;
                    }
                    if (activity instanceof AddAddressActivity && activity == addAddressAct) {
                        addAddressAct = null;
                    }

                    if (activity instanceof LiveTaskListActivity && activity == liveTaskListAct) {
                        liveTaskListAct = null;
                    }
                }
            };
            registerActivityLifecycleCallbacks(lifecycleCallBacks);
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
        main22Act = null;
        driverArrivedAct = null;
        activeTripAct = null;
        addAddressAct = null;
        liveTaskListAct = null;
    }

    private void setCurrentAct(Activity currentAct) {

        if ((!(currentAct instanceof LauncherActivity) || initialAct == null) && isAppKilled()) {
            onCreate();
        }

        this.currentAct = currentAct;

        WakeLocker.getInstance().release(this.currentAct);
        if (isDriverOnline || currentAct instanceof DriverArrivedActivity || currentAct instanceof WorkingtrekActivity || currentAct instanceof VOIPActivity) {
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
            activeTripAct = null;
            addAddressAct = null;
            driverArrivedAct = null;
            liveTaskListAct = null;
            mainAct = (MainActivity) currentAct;
            main22Act = null;
        }
        if (currentAct instanceof MainActivity_22) {
            mainAct = null;
            activeTripAct = null;
            addAddressAct = null;
            driverArrivedAct = null;
            liveTaskListAct = null;
            main22Act = (MainActivity_22) currentAct;
        }

        if (currentAct instanceof DriverArrivedActivity) {
            mainAct = null;
            main22Act = null;
            activeTripAct = null;
            addAddressAct = null;
            liveTaskListAct = null;
            driverArrivedAct = (DriverArrivedActivity) currentAct;
        }

        if (currentAct instanceof WorkingtrekActivity) {
            mainAct = null;
            main22Act = null;
            driverArrivedAct = null;
            liveTaskListAct = null;
            addAddressAct = null;
            activeTripAct = (WorkingtrekActivity) currentAct;
        }
        if (currentAct instanceof LiveTaskListActivity) {
            activeTripAct = null;
            driverArrivedAct = null;
            mainAct = null;
            main22Act = null;
            addAddressAct = null;
            liveTaskListAct = (LiveTaskListActivity) currentAct;
        }
        if (currentAct instanceof AddAddressActivity) {
            activeTripAct = null;
            driverArrivedAct = null;
            mainAct = null;
            main22Act = null;
            liveTaskListAct = null;
            addAddressAct = (AddAddressActivity) currentAct;
        }
        connectReceiver(true);
    }

    private void resetLocationService() {
        if (GetLocationUpdates.retrieveInstance() == null) {
            return;
        }
        //GetLocationUpdates.getInstance().restartService();
        GetLocationUpdates.getInstance().destroyLocUpdates(this);
    }

    private void configureAppServices() {
        Logger.d(TAG, "Service Initialized");

        SocketEvents.buildEvents(generalFun.getMemberId());

        AppService.getInstance().resetAppServices();
        AppService.getInstance().setAppDataListener(this);

        if (Utils.IS_APP_IN_DEBUG_MODE.equalsIgnoreCase("Yes")) {
            AppService.getInstance().enableDebugging();
        }

        startServiceReqEventTimer();

        new Handler(Looper.getMainLooper()).postDelayed(() -> CommunicationManager.getInstance().initiateService(generalFun, generalFun.retrieveValue(Utils.USER_PROFILE_JSON)), 2000);
    }

    private void startServiceReqEventTimer() {

        stopServiceReqEventTimer();

        RecurringTask serviceReqEventTimer = new RecurringTask(120000);
//        serviceReqEventTimer.avoidFirstRun();
        serviceReqEventTimer.setTaskRunListener(instance -> {
            if (isAppKilled()) {
                stopServiceReqEventTimer();
                return;
            }
            sendServiceReqStatusIfAny();
        });

        serviceReqEventTimer.startRepeatingTask();

        this.serviceReqEventTimer = serviceReqEventTimer;

    }

    private void sendServiceReqStatusIfAny() {
        ArrayList<HashMap<String, String>> reqStatusList = new ArrayList<>();
        try {
            Map<String, ?> keys = generalFun.retrieveAllData();
            for (Map.Entry<String, ?> entry : keys.entrySet()) {
                if (entry.getKey().startsWith(ServiceRequest.DRIVER_REQUEST_STATUS)) {
                    String value = entry.getValue().toString();

                    reqStatusList.add(new Gson().fromJson(value, new TypeToken<HashMap<String, String>>() {
                    }.getType()));
                    break;
                }
            }
        } catch (Exception e) {

        }

        sendServiceDataToServer(0, reqStatusList);
    }

    private void sendServiceDataToServer(int position, ArrayList<HashMap<String, String>> reqStatusList) {

        if (reqStatusList.size() > position) {
            ServiceRequest.sendEvent(0, reqStatusList.get(position), () -> sendServiceDataToServer(position + 1, reqStatusList));
        }
    }

    private void stopServiceReqEventTimer() {

        if (serviceReqEventTimer != null) {
            serviceReqEventTimer.stopRepeatingTask();
            serviceReqEventTimer = null;
        }
    }

    private void forceDestroyServices() {
        continueDestroyServices();
    }

    public void resetAppServices() {
       /*AppService.destroy();

       new Handler(Looper.getMainLooper()).postDelayed(this::configureAppServices, 2000);*/
    }


    public void terminateAppServices() {
        if (GetLocationUpdates.retrieveInstance() != null) {
            JsonElement element = new Gson().toJsonTree(GetLocationUpdates.getInstance().getListOfTripLocations(), new TypeToken<ArrayList<HashMap<String, String>>>() {
            }.getType());
            generalFun.storeData("PROVIDER_STATUS_MODE_LAST_DATA", element.getAsJsonArray().toString());
        }

        SafetyTools.getInstance().dismissAllDialog();

        if (!isDestroyAllServices()) {

            return;
        }
        continueDestroyServices();
    }

    private void continueDestroyServices() {
        AppService.destroy();
        releaseGpsReceiver();
        releaseactReceiver();
        removeAllRunningInstances();
        removeLocationUpdates();

        NotificationManager nMgr = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        nMgr.cancelAll();

        NavigationSensor.destroySensor();

        stopServiceReqEventTimer();
        releaseAllAct();
    }

    public void restartWithGetDataApp() {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct());
        objRefresh.getData();
    }

    public void refreshWithConfigData() {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct());
        objRefresh.GetConfigData();
    }

    public void restartWithGetDataApp(boolean releaseCurrActInstance) {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct(), releaseCurrActInstance);
        objRefresh.getData();
    }

    public void restartApp(boolean releaseCurrActInstance) {
        GetUserData objRefresh = new GetUserData(generalFun, MyApp.getInstance().getCurrentAct(), releaseCurrActInstance);
        objRefresh.getData();
    }

    public void refreshView(Activity context, String responseString) {
        generalFun.storeData(Utils.USER_PROFILE_JSON, generalFun.getJsonValue("USER_DATA", responseString));
        new OpenMainProfile(context, true, generalFun).startProcess();
    }

    private void configureAppBadgeFloat() {
        if (GetLocationUpdates.retrieveInstance() == null) {
            return;
        }

        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (GetLocationUpdates.retrieveInstance() != null) {
                if (isMyAppInBackGround()) {
                    GetLocationUpdates.retrieveInstance().showAppBadgeFloat();
                } else {
                    GetLocationUpdates.retrieveInstance().hideAppBadgeFloat();
                }
            }
        }, 1000);
    }

    public void notifySessionTimeOut() {
        if (generateSessionAlert != null) {
            return;
        }

        forceDestroyServices();
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
                JSONObject responseStringObject = generalFun.getJsonObject(responseString);

                if (responseStringObject != null) {

                    if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObject)) {
                        forceLogoutRemoveData();
                    } else {
                        if (isForceLogout) {
                            generalFun.showGeneralMessage("", generalFun.retrieveLangLBl("", generalFun.getJsonValueStr(Utils.message_str, responseStringObject)), buttonId -> (new GeneralFunctions(getCurrentAct())).restartApp());
                        } else {
                            generalFun.showGeneralMessage("", generalFun.retrieveLangLBl("", generalFun.getJsonValueStr(Utils.message_str, responseStringObject)));
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
        isOnJob = false;
        isDriverOnline = false;

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
        MyApp.getInstance().writeToFile("", this);
        generalFun.logOutUser(MyApp.this);
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

    public boolean locationPermissionReq(boolean isOpen) {
        ArrayList<String> requestPermissions = new ArrayList<>();
        requestPermissions.add(Manifest.permission.ACCESS_FINE_LOCATION);
        requestPermissions.add(Manifest.permission.ACCESS_COARSE_LOCATION);
        if (android.os.Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
            requestPermissions.add(Manifest.permission.ACCESS_BACKGROUND_LOCATION);
        }
        return generalFun.isAllPermissionGranted(isOpen, requestPermissions, LOCATION_PERMISSIONS_REQUEST);
    }

    public void setOfflineState() {
        isDriverOnline = false;
        WakeLocker.getInstance().release();
        GetLocationUpdates.getInstance().setOfflineState();
        setAvailabilityStatus(false, 0);
    }

    public void setOnlineState() {
        isDriverOnline = true;
        WakeLocker.getInstance().acquire();

        GetLocationUpdates.getInstance().setOnlineState();

        setAvailabilityStatus(true, 0);
    }

    public static boolean isDriverOnline() {
        return isDriverOnline;
    }

    public static boolean isProviderOnJob() {
        return isOnJob;
    }

    public static void configProviderOnJob(boolean isOnJob) {
        MyApp.isOnJob = isOnJob;
    }

    public static boolean isDestroyAllServices() {
        if (isDriverOnline() || isProviderOnJob()) {
            return false;
        }
        return true;
    }

    private void setAvailabilityStatus(boolean isAvailable, int repeatCount) {
        if (generalFun == null || repeatCount > 6) {
            return;
        }

        HashMap<String, String> dataMap = new HashMap<>();
        dataMap.put("iDriverId", generalFun.getMemberId());
        dataMap.put("vCurrentTime", "" + System.currentTimeMillis());
        dataMap.put("tTimeZone", "" + generalFun.getTimezone());
        dataMap.put("vAvailability", isAvailable ? "Available" : "Not Available");

        dataMap.put("iMemberId", generalFun.getMemberId());

        Location providerLoc = GetLocationUpdates.getInstance().getLastLocation();
        if (providerLoc != null) {
            dataMap.put("vLatitude", "" + providerLoc.getLatitude());
            dataMap.put("vLongitude", "" + providerLoc.getLongitude());
        }

        AppService.getInstance().sendMessage(SocketEvents.SERVICE_AVAILABILITY, (new Gson()).toJson(dataMap), 10000, (name, errorObj, dataObj) -> {
            if (errorObj != null) {
                int rCount = repeatCount + 1;
                new Handler(Looper.getMainLooper()).postDelayed(() -> setAvailabilityStatus(isAvailable, rCount), 5000);
            }
        });
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