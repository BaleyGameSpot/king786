package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.location.Location;
import android.os.Looper;
import android.provider.Settings;

import com.google.android.gms.common.api.ApiException;
import com.google.android.gms.common.api.ResolvableApiException;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.LocationSettingsRequest;
import com.google.android.gms.location.LocationSettingsStatusCodes;
import com.google.android.gms.location.Priority;
import com.google.android.gms.location.SettingsClient;
import com.act.MainActivity;
import com.act.UberXActivity;
import com.utils.Logger;
import com.utils.Utils;

/**
 * Created by Admin on 27-06-2016.
 */

public class GetLocationUpdates extends LocationCallback {
    /**
     * Constant used in the location settings dialog.
     */
    private static final int REQUEST_CHECK_SETTINGS = 126;

    Context mContext;

    GeneralFunctions generalFunc;
    private int UPDATE_INTERVAL = 1000;
    private int FASTEST_INTERVAL = 1000;
    private int DISPLACEMENT = 8;
    private LocationUpdates locationsUpdates;

    boolean isPermissionDialogShown = false;

    Location mLastLocation;

    boolean isContinuousLocUpdates = false;

    boolean isInitializeFromNonActivityClass = false;

    /**
     * Provides access to the Fused Location Provider API.
     */
    private FusedLocationProviderClient mFusedLocationClient;
    private com.huawei.hms.location.FusedLocationProviderClient mHFusedLocationClient;

    /**
     * Provides access to the Location Settings API.
     */
    private SettingsClient mSettingsClient;
    private com.huawei.hms.location.SettingsClient mHSettingsClient;

    /**
     * Stores parameters for requests to the FusedLocationProviderApi.
     */
    private LocationRequest mLocationRequest;
    private com.huawei.hms.location.LocationRequest mHLocationRequest;

    /**
     * Stores the types of location services the client is interested in using. Used for checking
     * settings to determine if the device has optimal location settings.
     */
    private LocationSettingsRequest mLocationSettingsRequest;
    private com.huawei.hms.location.LocationSettingsRequest mHLocationSettingsRequest;

    public static boolean locationResolutionAsked = false;

    HLocationCallbacks hLocationCallbacks = new HLocationCallbacks();

    boolean isHMSOnly = false;


    public GetLocationUpdates(Context context, int displacement, boolean isContinuousLocUpdates, LocationUpdates locationsUpdates) {
        // TODO Auto-generated constructor stub
        this.mContext = context;
        this.DISPLACEMENT = displacement;
        this.locationsUpdates = locationsUpdates;
        this.isContinuousLocUpdates = isContinuousLocUpdates;

        if (MyApp.getInstance().isHMSOnly()) {
            isHMSOnly = true;
        }

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);


        if (isHMSOnly) {
            mHFusedLocationClient = com.huawei.hms.location.LocationServices.getFusedLocationProviderClient(this.mContext);
            mHSettingsClient = com.huawei.hms.location.LocationServices.getSettingsClient(this.mContext);
        } else {
            mFusedLocationClient = LocationServices.getFusedLocationProviderClient(this.mContext);
            mSettingsClient = LocationServices.getSettingsClient(this.mContext);
        }

        createLocationRequest();
        buildLocationSettingsRequest();

        startLocationUpdates(isContinuousLocUpdates);
    }

    public GetLocationUpdates(Context context, int displacement, boolean isContinuousLocUpdates, boolean isInitializeFromNonActivityClass, LocationUpdates locationsUpdates) {
        // TODO Auto-generated constructor stub
        this.mContext = context;
        this.isInitializeFromNonActivityClass = isInitializeFromNonActivityClass;
        this.DISPLACEMENT = displacement;
        this.locationsUpdates = locationsUpdates;
        this.isContinuousLocUpdates = isContinuousLocUpdates;

        if (MyApp.getInstance().isHMSOnly()) {
            isHMSOnly = true;
        }

        generalFunc = MyApp.getInstance().getGeneralFun(mContext);

        if (isHMSOnly) {
            mHFusedLocationClient = com.huawei.hms.location.LocationServices.getFusedLocationProviderClient(this.mContext);
            mHSettingsClient = com.huawei.hms.location.LocationServices.getSettingsClient(this.mContext);
        } else {
            mFusedLocationClient = LocationServices.getFusedLocationProviderClient(this.mContext);
            mSettingsClient = LocationServices.getSettingsClient(this.mContext);
        }

        createLocationRequest();
        buildLocationSettingsRequest();

        startLocationUpdates(isContinuousLocUpdates);
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
    private void createLocationRequest() {
//        mLocationRequest = new LocationRequest();

        // Sets the desired interval for active location updates. This interval is
        // inexact. You may not receive updates at all if no location sources are available, or
        // you may receive them slower than requested. You may also receive updates faster than
        // requested if other applications are requesting location at a faster interval.
//        mLocationRequest.setInterval(UPDATE_INTERVAL);

        // Sets the fastest rate for active location updates. This interval is exact, and your
        // application will never receive updates faster than this value.
//        mLocationRequest.setFastestInterval(FATEST_INTERVAL);

//        mLocationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
//        mLocationRequest.setSmallestDisplacement(DISPLACEMENT);

        if (isHMSOnly) {
            mHLocationRequest = new com.huawei.hms.location.LocationRequest();
            mHLocationRequest.setInterval(UPDATE_INTERVAL);
            mHLocationRequest.setFastestInterval(FASTEST_INTERVAL);
            mHLocationRequest.setPriority(com.huawei.hms.location.LocationRequest.PRIORITY_HIGH_ACCURACY);
            mHLocationRequest.setSmallestDisplacement(DISPLACEMENT);
        } else {
            mLocationRequest = new LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, UPDATE_INTERVAL)
                    .setWaitForAccurateLocation(false)
                    .setMinUpdateIntervalMillis(UPDATE_INTERVAL)
                    .setMaxUpdateDelayMillis(FASTEST_INTERVAL)
                    .setMinUpdateDistanceMeters(DISPLACEMENT)
                    .build();
        }
    }

    /**
     * Uses a {@link com.google.android.gms.location.LocationSettingsRequest.Builder} to build
     * a {@link com.google.android.gms.location.LocationSettingsRequest} that is used for checking
     * if a device has the needed location settings.
     */
    private void buildLocationSettingsRequest() {

        if (isHMSOnly) {
            com.huawei.hms.location.LocationSettingsRequest.Builder builder = new com.huawei.hms.location.LocationSettingsRequest.Builder();
            builder.addLocationRequest(mHLocationRequest);
            mHLocationSettingsRequest = builder.build();
        } else {
            LocationSettingsRequest.Builder builder = new LocationSettingsRequest.Builder();
            builder.addLocationRequest(mLocationRequest);
            mLocationSettingsRequest = builder.build();
        }
    }

    public void startLocationUpdates(boolean isContinuousLocUpdates) {

        this.isContinuousLocUpdates = isContinuousLocUpdates;

        boolean isLocationPermissionGranted = MyApp.getInstance().getGeneralFun(mContext).checkLocationPermission(isPermissionDialogShown);

        if (isLocationPermissionGranted) {

            if (isHMSOnly) {
                mHFusedLocationClient.requestLocationUpdates(mHLocationRequest, hLocationCallbacks, Looper.myLooper());
                mHSettingsClient.checkLocationSettings(mHLocationSettingsRequest)
                        .addOnSuccessListener(locationSettingsResponse -> {

                        })
                        .addOnFailureListener(e -> {
                            int statusCode = ((com.huawei.hms.common.ApiException) e).getStatusCode();
                            //  mFusedLocationClient.requestLocationUpdates(mLocationRequest, this, Looper.myLooper());

                            Logger.e("RESOLUTION_REQUIRED", "::" + statusCode);
                            switch (statusCode) {
                                case LocationSettingsStatusCodes.RESOLUTION_REQUIRED:
                                    try {
                                        // Show the dialog by calling startResolutionForResult(), and check the
                                        // result in onActivityResult().
                                        if (!isInitializeFromNonActivityClass && (mContext instanceof MainActivity || mContext instanceof UberXActivity)) {

                                            if (!locationResolutionAsked) {
                                                com.huawei.hms.common.ResolvableApiException rae = (com.huawei.hms.common.ResolvableApiException) e;
                                                rae.startResolutionForResult((Activity) mContext, REQUEST_CHECK_SETTINGS);
                                            }

                                            locationResolutionAsked = true;
                                        }
                                    } catch (Exception sie) {
                                        Logger.e("Error", "PendingIntent unable to execute request.");
                                    }
                                    break;
                            }
                        });
            } else {

                mFusedLocationClient.requestLocationUpdates(mLocationRequest, this, Looper.myLooper());

                mSettingsClient.checkLocationSettings(mLocationSettingsRequest)
                        .addOnSuccessListener(locationSettingsResponse -> {

                        })
                        .addOnFailureListener(e -> {
                            int statusCode = ((ApiException) e).getStatusCode();
                            //  mFusedLocationClient.requestLocationUpdates(mLocationRequest, this, Looper.myLooper());

                            Logger.e("RESOLUTION_REQUIRED", "::" + statusCode);
                            switch (statusCode) {
                                case LocationSettingsStatusCodes.RESOLUTION_REQUIRED:
                                    try {
                                        // Show the dialog by calling startResolutionForResult(), and check the
                                        // result in onActivityResult().
                                        if (!isInitializeFromNonActivityClass && (mContext instanceof MainActivity || mContext instanceof UberXActivity)) {

                                            if (!locationResolutionAsked) {
                                                ResolvableApiException rae = (ResolvableApiException) e;
                                                rae.startResolutionForResult((Activity) mContext, REQUEST_CHECK_SETTINGS);
                                            }

                                            locationResolutionAsked = true;


                                        }
                                    } catch (Exception sie) {
                                        Logger.e("Error", "PendingIntent unable to execute request.");
                                    }
                                    break;
                            }
                        });
            }

        } else {
            isPermissionDialogShown = true;
        }

    }


    public void stopLocationUpdates() {
        try {
            if (isHMSOnly) {
                if (mHFusedLocationClient != null) {
                    mHFusedLocationClient.removeLocationUpdates(hLocationCallbacks);
                }
            } else {
                if (mFusedLocationClient != null) {
                    mFusedLocationClient.removeLocationUpdates(this);
                }
            }

        } catch (Exception e) {

        }
    }

    public Location getLastLocation() {
        try {
            if (generalFunc.checkLocationPermission(true)) {
                if (isHMSOnly) {
                    if (mHFusedLocationClient != null) {
                        return mHFusedLocationClient.getLastLocation().getResult();
                    }
                } else {
                    if (mFusedLocationClient != null) {
                        return mFusedLocationClient.getLastLocation().getResult();
                    }
                }
            }
        } catch (Exception e) {
        }

        return this.mLastLocation;

    }

    private boolean isMockSettingsON(Context context) {
        // returns true if mock location enabled, false if not enabled.
        if (Settings.Secure.getString(context.getContentResolver(),
                Settings.Secure.ALLOW_MOCK_LOCATION).equals("0"))
            return false;
        else
            return true;
    }

    @Override
    public void onLocationResult(LocationResult locationResult) {
        super.onLocationResult(locationResult);

        Location mCurrentLocation = locationResult.getLastLocation();
        continueDispatchingLocation(mCurrentLocation);

    }

    private void continueDispatchingLocation(Location mCurrentLocation) {
        if (mCurrentLocation == null
                || (!Utils.SKIP_MOCK_LOCATION_CHECK && mCurrentLocation.isFromMockProvider())
                || (Utils.SKIP_MOCK_LOCATION_CHECK && !mCurrentLocation.isFromMockProvider())
                || isMockSettingsON(mContext)) {
            return;
        }

        if (locationsUpdates != null) {
            locationsUpdates.onLocationUpdate(mCurrentLocation);
        }

        this.mLastLocation = mCurrentLocation;
        MyApp.getInstance().currentLocation = mLastLocation;

        if (!isContinuousLocUpdates) {
            stopLocationUpdates();
        }
    }

    public Location getLocation() {
        return this.mLastLocation;
    }

    public interface LocationUpdates {
        void onLocationUpdate(Location location);
    }


    class HLocationCallbacks extends com.huawei.hms.location.LocationCallback {

        @Override
        public void onLocationResult(com.huawei.hms.location.LocationResult locationResult) {
            super.onLocationResult(locationResult);

            Location mCurrentLocation = locationResult.getLastLocation();
            continueDispatchingLocation(mCurrentLocation);
        }
    }
}