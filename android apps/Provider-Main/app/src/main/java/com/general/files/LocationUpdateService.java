package com.general.files;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.content.pm.ServiceInfo;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.PixelFormat;
import android.location.Location;
import android.os.Binder;
import android.os.Build;
import android.os.IBinder;
import android.os.Looper;
import android.provider.Settings;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.WindowManager;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.app.ActivityCompat;
import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;

import com.google.android.gms.common.api.ApiException;
import com.google.android.gms.common.api.ResolvableApiException;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.LocationSettingsRequest;
import com.google.android.gms.location.LocationSettingsStatusCodes;
import com.google.android.gms.location.SettingsClient;
import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.utils.IntentAction;
import com.utils.Logger;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

public class LocationUpdateService extends Service implements UpdateDriverLocation.OnDemoLocationListener {
    private WindowManager mWindowManager;
    private View mAppBgHeadView;

    private static final String LOG_TAG = "LocationUpdateService";

    private static final int FOREGROUND_SERVICE_ID = 126;
    private static final int ONLINE_NOTIFICATION_ID = 169;

    /**
     * Constant used in the location settings dialog.
     */
    private static final int REQUEST_CHECK_SETTINGS = 126;

    Context mContext;
    GeneralFunctions generalFunc;

    boolean isPermissionDialogShown = false;

    private int UPDATE_INTERVAL = 2000;
    private int FATEST_INTERVAL = 1000;
    private int DISPLACEMENT = 8;


    boolean isResolutionFirstTime = true;

    /**
     * Provides access to the Fused Location Provider API.
     */
    private FusedLocationProviderClient mFusedLocationClient;

    /**
     * Provides access to the Location Settings API.
     */
    private SettingsClient mSettingsClient;

    /**
     * Stores parameters for requests to the FusedLocationProviderApi.
     */
    private LocationRequest mLocationRequest;

    /**
     * Stores the types of location services the client is interested in using. Used for checking
     * settings to determine if the device has optimal location settings.
     */
    private LocationSettingsRequest mLocationSettingsRequest;

    LocationUpdates locationsUpdates;

    Location mLastLocation;

    private IBinder mBinder = new LocUpdatesBinder();
    NotificationManager mNotificationManager = null;

    UpdateDriverLocation updateDriverLoc;

    String iTripId = "";
    private final String channelId = "location_service_channel_5485625";
    private float lastPositionOfXVertex = 15;
    private float lastPositionOfYVertex = 100;

    boolean isConfiguredDummyLoc = false;

    public class LocUpdatesBinder extends Binder {
        public LocationUpdateService getService() {
            return LocationUpdateService.this;
        }
    }

    @Override
    public IBinder onBind(Intent intent) {
        Logger.d(LOG_TAG, "in onBind");
        return mBinder;
    }

    @Override
    public void onRebind(Intent intent) {
        Logger.d(LOG_TAG, "in onRebind");
        super.onRebind(intent);
    }

    @Override
    public boolean onUnbind(Intent intent) {
        Logger.d(LOG_TAG, "in onUnbind");
        return true;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        mContext = this.getApplicationContext();
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();

        mFusedLocationClient = LocationServices.getFusedLocationProviderClient(this.mContext);
        mSettingsClient = LocationServices.getSettingsClient(this.mContext);

        runAsForeground();
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Logger.d(LOG_TAG, "On Start Command");
        return Service.START_STICKY;
    }

    protected void configureDriverLocUpdates(boolean isStartLocationStorage, boolean isServiceAssigned, boolean isServiceStarted, String iTripId) {
        this.iTripId = iTripId;

        isConfiguredDummyLoc = false;

        if (updateDriverLoc == null) {
            updateDriverLoc = new UpdateDriverLocation(generalFunc, this, isStartLocationStorage, isServiceAssigned, isServiceStarted, iTripId, this);
            updateDriverLoc.executeProcess();
        } else {
            updateDriverLoc.setTripStartValue(isStartLocationStorage, isServiceAssigned, isServiceStarted, iTripId);
        }
    }

    protected ArrayList<HashMap<String, String>> getListOfTripLocations() {
        if (updateDriverLoc == null) {
            return new ArrayList<>();
        }
        return updateDriverLoc.getListOfLocations();
    }

    /**
     * Sets up the location request. Android has two location request settings:
     * {@code ACCESS_COARSE_LOCATION} and {@code ACCESS_FINE_LOCATION}. These settings control
     * the accuracy of the current location. This sample uses ACCESS_FINE_LOCATION, as defined in
     * the AndroidManifest.xml.
     * <p/>
     * When the ACCESS_FINE_LOCATION setting is specified, combined with a fast update
     * interval (5 seconds), the Fused Location Provider API returns location updates that are
     * accurate to within a few feet.
     * <p/>
     * These settings are appropriate for mapping applications that show real-time location
     * updates.
     */
    public void createLocationRequest(int displacement, LocationUpdates locationsUpdates) {
        this.locationsUpdates = locationsUpdates;
        this.DISPLACEMENT = displacement;
        mLocationRequest = new LocationRequest();

        // Sets the desired interval for active location updates. This interval is
        // inexact. You may not receive updates at all if no location sources are available, or
        // you may receive them slower than requested. You may also receive updates faster than
        // requested if other applications are requesting location at a faster interval.
        mLocationRequest.setInterval(UPDATE_INTERVAL);

        // Sets the fastest rate for active location updates. This interval is exact, and your
        // application will never receive updates faster than this value.
        mLocationRequest.setFastestInterval(FATEST_INTERVAL);

        mLocationRequest.setMaxWaitTime(UPDATE_INTERVAL);

        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
        mLocationRequest.setSmallestDisplacement(DISPLACEMENT);

        buildLocationSettingsRequest();
    }

    /**
     * Uses a {@link LocationSettingsRequest.Builder} to build
     * a {@link LocationSettingsRequest} that is used for checking
     * if a device has the needed location settings.
     */
    private void buildLocationSettingsRequest() {
        LocationSettingsRequest.Builder builder = new LocationSettingsRequest.Builder();
        builder.addLocationRequest(mLocationRequest);

        mLocationSettingsRequest = builder.build();

        startLocationUpdateService();

    }

    LocationCallback mLocationCallback = new LocationCallback() {
        @Override
        public void onLocationResult(@NonNull LocationResult locationResult) {
            super.onLocationResult(locationResult);

            if (isConfiguredDummyLoc) {
                return;
            }

            Location mCurrentLocation = locationResult.getLastLocation();
            continueDispatchingLocation(mCurrentLocation);
        }
    };

    private void continueDispatchingLocation(Location mCurrentLocation) {
        if (mCurrentLocation == null
                || (!Utils.SKIP_MOCK_LOCATION_CHECK && mCurrentLocation.isFromMockProvider())
                || (Utils.SKIP_MOCK_LOCATION_CHECK && !mCurrentLocation.isFromMockProvider())
                || isMockSettingsON(mContext)) {
            return;
        }

        mLastLocation = mCurrentLocation;

        if (updateDriverLoc != null) {
            updateDriverLoc.onLocationUpdate(mCurrentLocation);
        }

        if (locationsUpdates != null) {
            locationsUpdates.onLocationUpdate(mCurrentLocation);
        }

    }

    @SuppressLint("MissingPermission")
    private void startLocationUpdateService() {
        if (MyApp.getInstance() == null || MyApp.getInstance().getCurrentAct() == null) {
            return;
        }

        if (MyApp.getInstance().locationPermissionReq(false)) {

            mFusedLocationClient.requestLocationUpdates(mLocationRequest, mLocationCallback, Looper.myLooper());

            invokeLastLocationDispatcher();

            mSettingsClient.checkLocationSettings(mLocationSettingsRequest)
                    .addOnSuccessListener(locationSettingsResponse -> {

                    })
                    .addOnFailureListener(e -> {
                        int statusCode = ((ApiException) e).getStatusCode();
                        //  mFusedLocationClient.requestLocationUpdates(mLocationRequest, this, Looper.myLooper());

                        if (statusCode == LocationSettingsStatusCodes.RESOLUTION_REQUIRED) {
                            try {
                                // Show the dialog by calling startResolutionForResult(), and check the
                                // result in onActivityResult().
                                if (isResolutionFirstTime/*(mContext instanceof MainActivity || mContext instanceof DriverArrivedActivity || mContext instanceof ActiveTripActivity || mContext instanceof HailActivity)*/) {
                                    Activity currentAct = MyApp.getInstance().getCurrentAct();
                                    if (currentAct != null) {
                                        ResolvableApiException rae = (ResolvableApiException) e;
                                        rae.startResolutionForResult(currentAct, REQUEST_CHECK_SETTINGS);
                                    }

                                    isResolutionFirstTime = false;
                                }
                            } catch (Exception sie) {
                                Logger.e(LOG_TAG, "PendingIntent unable to execute request." + sie.getMessage());
                            }
                        }
                    });


        } else {
            isPermissionDialogShown = true;
        }

    }

    private boolean isMockSettingsON(Context context) {
        // returns true if mock location enabled, false if not enabled.
        if (Settings.Secure.getString(context.getContentResolver(),
                Settings.Secure.ALLOW_MOCK_LOCATION).equals("0"))
            return false;
        else
            return true;
    }

    @SuppressLint("MissingPermission")
    public void invokeLastLocationDispatcher() {
        if (!MyApp.getInstance().locationPermissionReq(false)) {
            return;
        }

        if (mFusedLocationClient != null) {
            mFusedLocationClient.getLastLocation().addOnSuccessListener(this::continueDispatchingLocation);
        }
    }

    @SuppressLint("MissingPermission")
    public Location getLastLocation() {
        try {

            if (MyApp.getInstance().locationPermissionReq(false)) {
                if (mFusedLocationClient != null) {
                    return mFusedLocationClient.getLastLocation().getResult();
                }
            }
        } catch (Exception ignored) {
        }
        return this.mLastLocation;
    }

    public void stopLocationUpdateService(Object obj) {
        Logger.d(LOG_TAG, "::stopLocationUpdateService");
        try {
            if (mFusedLocationClient != null) {
                mFusedLocationClient.removeLocationUpdates(mLocationCallback);
            }

            if (updateDriverLoc != null) {
                updateDriverLoc.stopProcess();
            }

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                this.stopForeground(STOP_FOREGROUND_DETACH);
            }

            hideAppBadgeFloat();
            if (mNotificationManager != null) {
                mNotificationManager.cancelAll();
                mNotificationManager = null;
            }
            this.stopSelf();
        } catch (Exception e) {
            Logger.d(LOG_TAG, ":runAsForeground: Exception " + e.getMessage());
        }
    }

    private void runAsForeground() {
        Logger.e(LOG_TAG, "runAsForeground Called");
        Context mContext = this;

        Intent intent;
        if (Utils.getPreviousIntent(mContext) != null && !MyApp.isAppKilled()) {
            Logger.e(LOG_TAG, "runAsForeground Previous Intent");
            intent = Utils.getPreviousIntent(mContext);
        } else {
            Logger.e(LOG_TAG, "runAsForeground New Intent");
            intent = mContext.getPackageManager().getLaunchIntentForPackage(mContext.getPackageName());
            if (intent != null) {
                intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
            }
        }

        PendingIntent contentIntent = PendingIntent.getActivity(mContext, 0, intent, IntentAction.getPendingIntentFlag());


        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(mContext, BuildConfig.APPLICATION_ID)
                .setSmallIcon(R.drawable.ic_notification_logo)
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setLargeIcon(BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_launcher))
                .setContentTitle(mContext.getString(R.string.app_name))
                .setContentText(generalFunc.retrieveLangLBl("Using Location", "LBL_USING_LOC"))
                .setAutoCancel(false)
                .setDefaults(Notification.DEFAULT_ALL)
                .setOngoing(true)
                .setForegroundServiceBehavior(NotificationCompat.FOREGROUND_SERVICE_IMMEDIATE)
                .setContentIntent(contentIntent)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(generalFunc.retrieveLangLBl("Using Location", "LBL_USING_LOC")))
                .setPriority(NotificationCompat.PRIORITY_MAX);

        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            mBuilder.setChannelId(channelId);
            NotificationChannel channel = new NotificationChannel(
                    channelId,
                    mContext.getString(R.string.app_name),
                    NotificationManager.IMPORTANCE_HIGH
            );

            channel.setDescription(generalFunc.retrieveLangLBl("Using Location", "LBL_USING_LOC"));
            channel.enableLights(true);
            channel.setLightColor(Color.BLUE);
            channel.setLockscreenVisibility(Notification.VISIBILITY_PUBLIC);
            channel.enableVibration(true);

            if (mNotificationManager != null) {
                mNotificationManager.createNotificationChannel(channel);
            }
        }

        if (ActivityCompat.checkSelfPermission(mContext, Manifest.permission.FOREGROUND_SERVICE_LOCATION) == PackageManager.PERMISSION_GRANTED
                || ActivityCompat.checkSelfPermission(mContext, Manifest.permission.FOREGROUND_SERVICE) == PackageManager.PERMISSION_GRANTED) {
            try {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                    startForeground(FOREGROUND_SERVICE_ID, mBuilder.build(), ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION);
                } else {
                    startForeground(FOREGROUND_SERVICE_ID, mBuilder.build());
                }
            } catch (Exception ignored) {
            }
        }

        if (MyApp.getInstance().isMyAppInBackGround()) {
            showAppBadgeFloat(intent);
        }
    }

    public void setOfflineState() {
        try {
            if (mNotificationManager == null) {
                return;
            }
            mNotificationManager.cancel(ONLINE_NOTIFICATION_ID);
        } catch (Exception ignored) {
        }
    }

    public void setOnlineState() {
        try {
            if (mNotificationManager == null) {
                return;
            }
            mNotificationManager.cancel(ONLINE_NOTIFICATION_ID);
        } catch (Exception ignored) {
        }
        if (!MyApp.isDriverOnline()) {
            return;
        }

        Logger.e(LOG_TAG, "showAsOnline Called");
        Context mContext = this;

        Intent intent;
        if (Utils.getPreviousIntent(mContext) != null && !MyApp.isAppKilled()) {
            Logger.e(LOG_TAG, "showAsOnline Previous Intent");

            intent = Utils.getPreviousIntent(mContext);
        } else {
            Logger.e(LOG_TAG, "showAsOnline NEW Intent");
            intent = mContext.getPackageManager().getLaunchIntentForPackage(mContext.getPackageName());
            if (intent != null) {
                intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
            }
        }

        PendingIntent contentIntent = PendingIntent.getActivity(mContext, 0, intent, IntentAction.getPendingIntentFlag());


        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(mContext, BuildConfig.APPLICATION_ID)
                .setSmallIcon(R.drawable.ic_notification_logo)
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setLargeIcon(BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_launcher))
                .setContentTitle(mContext.getString(R.string.app_name))
                .setContentText(generalFunc.retrieveLangLBl("You are Online", "LBL_SHOW_AS_ONLINE"))
                .setAutoCancel(false)
                .setDefaults(Notification.DEFAULT_ALL)
                .setOngoing(true)
                .setContentIntent(contentIntent)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(generalFunc.retrieveLangLBl("You are Online", "LBL_SHOW_AS_ONLINE")))
                .setPriority(NotificationCompat.PRIORITY_MAX);

//        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            mBuilder.setChannelId(channelId);
            NotificationChannel channel = new NotificationChannel(
                    channelId,
                    mContext.getString(R.string.app_name),
                    NotificationManager.IMPORTANCE_HIGH
            );

            channel.setDescription(generalFunc.retrieveLangLBl("You are Online", "LBL_SHOW_AS_ONLINE"));
            channel.enableLights(true);
            channel.setLightColor(Color.BLUE);
            channel.setLockscreenVisibility(Notification.VISIBILITY_PUBLIC);
            channel.enableVibration(true);

            if (mNotificationManager != null) {
                mNotificationManager.createNotificationChannel(channel);

                mNotificationManager.notify(ONLINE_NOTIFICATION_ID, mBuilder.build());
            }
        }

    }

    @SuppressLint("ClickableViewAccessibility")
    protected void showAppBadgeFloat(Intent intent) {
        try {
            if (mContext != null) {
                WakeLocker.releaseWakeLock(mContext);
            }
        } catch (Exception ignored) {
        }

        if (!generalFunc.canDrawOverlayViews(mContext) || MyApp.isDestroyAllServices()) {
            return;
        }

        if (intent == null) {
            intent = Utils.getPreviousIntent(mContext);
            if (intent != null) {
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

            } else {
                if (mContext != null) {
                    intent = mContext
                            .getPackageManager()
                            .getLaunchIntentForPackage(mContext.getPackageName());
                }

                if (intent != null) {
                    intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                            Intent.FLAG_ACTIVITY_NEW_TASK |
                            Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
                }
            }
        }

        hideAppBadgeFloat();

        mAppBgHeadView = LayoutInflater.from(this).inflate(R.layout.design_float_bg, null);

        int LAYOUT_FLAG;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            LAYOUT_FLAG = WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY;
        } else {
            LAYOUT_FLAG = WindowManager.LayoutParams.TYPE_PHONE;
        }

        final WindowManager.LayoutParams params = new WindowManager.LayoutParams(
                WindowManager.LayoutParams.WRAP_CONTENT,
                WindowManager.LayoutParams.WRAP_CONTENT,
                LAYOUT_FLAG,
                WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE | WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON
                        | WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON,
                PixelFormat.TRANSLUCENT);

        params.gravity = Gravity.TOP | Gravity.LEFT;

        params.x = (int) lastPositionOfXVertex;
        params.y = (int) lastPositionOfYVertex;

        params.windowAnimations = android.R.style.Animation_Translucent;

        mWindowManager = (WindowManager) getSystemService(WINDOW_SERVICE);

        if (mWindowManager == null) {
            return;
        }

        if (MyApp.getInstance().getCurrentAct() != null) {
            MyApp.getInstance().getCurrentAct().runOnUiThread(() -> {
                try {
                    mWindowManager.addView(mAppBgHeadView, params);
                } catch (Exception e) {
                    Logger.e(LOG_TAG, "Exception ::" + e.getMessage());
                }
            });
        }
        final ImageView appBgHeadImgView = (ImageView) mAppBgHeadView.findViewById(R.id.appBgHeadImgView);
        Intent finalIntent = intent;

        appBgHeadImgView.setOnTouchListener(new View.OnTouchListener() {
            private int initialX;
            private int initialY;
            private float initialTouchX;
            private float initialTouchY;
            private float startX;
            private float startY;

            @Override
            public boolean onTouch(View v, MotionEvent event) {

                switch (event.getAction()) {
                    case MotionEvent.ACTION_DOWN -> {
                        initialX = params.x;
                        initialY = params.y;
                        startX = event.getX();
                        startY = event.getY();
                        initialTouchX = event.getRawX();
                        initialTouchY = event.getRawY();
                        return true;
                    }
                    case MotionEvent.ACTION_UP -> {
                        float endX = event.getX();
                        float endY = event.getY();
                        if (shouldClickActionWork(startX, endX, startY, endY)) {
                            LocationUpdateService.this.startActivity(finalIntent);
                        }
                        return true;
                    }
                    case MotionEvent.ACTION_MOVE -> {
                        params.x = initialX + (int) (event.getRawX() - initialTouchX);
                        params.y = initialY + (int) (event.getRawY() - initialTouchY);
                        lastPositionOfXVertex = params.x;
                        lastPositionOfYVertex = params.y;
                        if (mWindowManager != null) {
                            mWindowManager.updateViewLayout(mAppBgHeadView, params);
                        }
                        return true;
                    }
                }
                return false;
            }
        });

        try {
            if (mContext != null) {
                WakeLocker.acquireWakeLock(mContext);
            }
        } catch (Exception ignored) {

        }
    }

    protected void hideAppBadgeFloat() {
        if (mWindowManager != null && mAppBgHeadView != null) {
            if (mAppBgHeadView.findViewById(R.id.appBgHeadImgView) != null) {
                (mAppBgHeadView.findViewById(R.id.appBgHeadImgView)).setOnTouchListener(null);
            }
            mAppBgHeadView.setVisibility(View.GONE);
            try {
                mWindowManager.removeView(mAppBgHeadView);
            } catch (Exception e) {
                Logger.e(LOG_TAG, ":Exception:" + e.getMessage());
            }
            mAppBgHeadView = null;
            mWindowManager = null;
        }

        try {
            if (mContext != null) {
                WakeLocker.releaseWakeLock(mContext);
            }
        } catch (Exception ignored) {

        }
    }

    private boolean shouldClickActionWork(float startX, float endX, float startY, float endY) {
        float CLICK_ACTION_THRESHOLD = 5;

        float differenceX = Math.abs(startX - endX);
        float differenceY = Math.abs(startY - endY);
        return !(differenceX > CLICK_ACTION_THRESHOLD/* =5 */ || differenceY > CLICK_ACTION_THRESHOLD);

    }

    public void restartServiceNotifications() {

        Logger.e(LOG_TAG, "restartServiceNotifications Called");
        runAsForeground();
        setOnlineState();
    }

    @Override
    public void onTaskRemoved(Intent rootIntent) {
        Logger.e(LOG_TAG, ":onTaskRemoved:");
//        stopLocationUpdateService(LocationUpdateService.this);
        if (MyApp.isDestroyAllServices()) {
            stopLocationUpdateService(LocationUpdateService.this);
        } else {
            restartServiceNotifications();
        }
        super.onTaskRemoved(rootIntent);
    }

    public interface LocationUpdates {
        void onLocationUpdate(Location location);
    }

    @Override
    public void onTrimMemory(int level) {
        super.onTrimMemory(level);
    }

    @Override
    public void onDestroy() {
        Logger.e(LOG_TAG, ":onDestroy:");
        if (MyApp.isDestroyAllServices()) {
            stopLocationUpdateService(LocationUpdateService.this);
        } else {
            restartServiceNotifications();
        }
        super.onDestroy();
    }

    @Override
    public void onReceiveDemoLocation(Location location) {
        if (location != null && locationsUpdates != null) {
            isConfiguredDummyLoc = true;
            locationsUpdates.onLocationUpdate(location);
        }
    }
}