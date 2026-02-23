package com.general.files;

import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.ServiceConnection;
import android.location.Location;
import android.os.Handler;
import android.os.IBinder;

import com.utils.DeviceSettings;
import com.utils.Logger;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

public class GetLocationUpdates implements LocationUpdateService.LocationUpdates {

    private static final String TAG = GetLocationUpdates.class.getSimpleName();
    Location mLocation;
    private static GetLocationUpdates instance;

    boolean mServiceBound = false;

    LocationUpdateService locUpdateService;

    HashMap<Object, LocationUpdatesListener> listOfListener;
    Intent locUpdateServiceIntent;

    boolean isServiceStarted = false;
    boolean isServiceAssigned = false;
    boolean isStartLocationStorage = false;
    String iTripId = "";

    public static GetLocationUpdates getInstance() {
        if (instance == null) {
            instance = new GetLocationUpdates();
        }
        return instance;
    }

    public static GetLocationUpdates retrieveInstance() {
        return instance;
    }


    public GetLocationUpdates() {

        listOfListener = new HashMap<>();

        continueExecution();
    }

    public void continueExecution() {
        if (!MyApp.isAppInstanceAvailable() || !DeviceSettings.isDeviceGPSEnabled() || locUpdateServiceIntent != null) {
            return;
        }

        //locUpdateServiceIntent = new Intent(MyApp.getInstance().getApplicationContext(), LocationUpdateService.class);

        locUpdateServiceIntent = new ActUtils(MyApp.getInstance().getApplicationContext()).startForegroundService(LocationUpdateService.class);
        MyApp.getInstance().getApplicationContext().bindService(locUpdateServiceIntent, mConnection, Context.BIND_AUTO_CREATE);
    }

    protected void showAppBadgeFloat() {
        if (locUpdateService == null) {
            return;
        }
        locUpdateService.showAppBadgeFloat(null);
    }

    protected void setOnlineState() {
        if (locUpdateService == null) {
            return;
        }
        locUpdateService.setOnlineState();
    }

    protected void setOfflineState() {
        if (locUpdateService == null) {
            return;
        }
        locUpdateService.setOfflineState();
    }

    protected void hideAppBadgeFloat() {
        if (locUpdateService == null) {
            return;
        }
        locUpdateService.hideAppBadgeFloat();
    }

    private final ServiceConnection mConnection = new ServiceConnection() {

        @Override
        public void onServiceDisconnected(ComponentName name) {
            mServiceBound = false;
        }

        @Override
        public void onServiceConnected(ComponentName name, IBinder service) {
            LocationUpdateService.LocUpdatesBinder locUpdatesBinder = (LocationUpdateService.LocUpdatesBinder) service;

            locUpdateService = locUpdatesBinder.getService();
            //locUpdateService.createLocationRequest(Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, GetLocationUpdates.this);
            locUpdateService.createLocationRequest(Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, instance);

            locUpdateService.configureDriverLocUpdates(isStartLocationStorage, isServiceAssigned, isServiceStarted, iTripId);

            mServiceBound = true;
        }
    };

    public void setTripStartValue(boolean isStartLocationStorage, boolean isServiceAssigned, boolean isServiceStarted, String iTripId) {
        this.isStartLocationStorage = isStartLocationStorage;
        this.isServiceAssigned = isServiceAssigned;
        this.isServiceStarted = isServiceStarted;
        this.iTripId = iTripId;
        if (locUpdateService != null) {
            locUpdateService.configureDriverLocUpdates(isStartLocationStorage, isServiceAssigned, isServiceStarted, iTripId);
        }

        MyApp.configProviderOnJob(isServiceAssigned);
    }

    public ArrayList<HashMap<String, String>> getListOfTripLocations() {
        if (locUpdateService == null) {
            return new ArrayList<>();
        }
        return locUpdateService.getListOfTripLocations();
    }

    public Location getLastLocation() {
        if (mLocation == null && locUpdateService != null) {
            mLocation = locUpdateService.getLastLocation();
        }
        return mLocation;
    }

    @Override
    public void onLocationUpdate(Location location) {
        this.mLocation = location;
        ArrayList<Object> keyOfListenerList = new ArrayList<>();
        for (Object currentKey : listOfListener.keySet()) {
            try {
                if (listOfListener.get(currentKey) != null) {
                    LocationUpdatesListener listener = listOfListener.get(currentKey);
                    if (listener != null) {
                        listener.onLocationUpdate(location);
                    }
                }

            } catch (Exception e) {
                try {
                    Logger.e(TAG, "onLocationUpdate:" + e.getMessage());
                    keyOfListenerList.add(currentKey);
                } catch (Exception e1) {
                    Logger.e(TAG, "onLocationUpdate >> Exception:" + e1.getMessage());
                }
            }
        }

        try {

            if (keyOfListenerList.size() > 0) {
                for (int i = 0; i < keyOfListenerList.size(); i++) {
                    listOfListener.remove(keyOfListenerList.get(i));
                }
            }
        } catch (Exception e1) {
            Logger.e(TAG, "onLocationUpdate >> Exception:" + e1.getMessage());
        }

    }

    public void startLocationUpdates(Object obj, LocationUpdatesListener locUpdatesListener) {
        if (obj != null && locUpdatesListener != null) {
            listOfListener.put(obj, locUpdatesListener);
        }

        if (mLocation != null && locUpdatesListener != null) {
            new Handler().postDelayed(() -> locUpdatesListener.onLocationUpdate(mLocation), 500);
        }
        initiateServiceCheck();
    }

    private void initiateServiceCheck() {
        if (locUpdateServiceIntent == null) {
            new Handler().postDelayed(this::continueExecution, 400);
            initializeServiceConnectionValidator();
            return;
        }
        if (!mServiceBound || locUpdateService == null) {
            new Handler().postDelayed(this::initiateServiceCheck, 400);
            return;
        }
        locUpdateService.invokeLastLocationDispatcher();
    }

    private void initializeServiceConnectionValidator() {
        new Handler().postDelayed(this::initiateServiceCheck, 800);
    }

    public void stopLocationUpdates(Object obj) {
        if (instance == null || obj == null) {
            return;
        }

        try {
            listOfListener.remove(obj);
        } catch (Exception ignored) {

        }
    }

    public void destroyLocUpdates(Object obj) {
        if (obj == null) {
            throw new RuntimeException("Object should not be null");
        }
        try {
            listOfListener.clear();
            if (locUpdateService != null && !(obj instanceof LocationUpdateService)) {
                locUpdateService.stopLocationUpdateService(obj);
            }

            if (mServiceBound) {
                MyApp.getInstance().unbindService(mConnection);
                mServiceBound = false;
            }
        } catch (Exception e) {
            Logger.e(TAG, "destroyLocUpdates >> Exception:" + e.getMessage());
        }

        try {
            Logger.d(TAG,"destroyLocUpdates :1:called::TIME::" + System.currentTimeMillis());
            instance = null;
            locUpdateService = null;
        } catch (Exception ignored) {

        }
    }

    public interface LocationUpdatesListener {
        void onLocationUpdate(Location location);
    }
}