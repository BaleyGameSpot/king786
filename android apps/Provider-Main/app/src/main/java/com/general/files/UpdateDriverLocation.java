package com.general.files;

import android.content.Context;
import android.location.Location;

import com.google.common.reflect.TypeToken;
import com.google.gson.Gson;
import com.google.gson.JsonElement;
import com.model.SocketEvents;
import com.service.handler.AppService;
import com.service.model.EventInformation;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.lang.reflect.Type;
import java.util.ArrayList;
import java.util.HashMap;

class UpdateDriverLocation implements RecurringTask.OnTaskRunCalled {

    private final String TAG = UpdateDriverLocation.class.getSimpleName();

    private final GeneralFunctions generalFunc;
    private final Context mContext;

    private Location driverLocation, lastPublishedLocation, lastPublishedLoc = null;
    private double PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT = 5;
    private DispatchDemoLocations dispatchDemoLoc;

    private RecurringTask updateDriverStatusTask, updateTripLocationsTask;

    private boolean isDataUploading = false, isStartLocationStorage, isServiceStarted, isServiceAssigned;
    private JSONObject userProfileJsonObj;
    private String iTripId;

    private long lastLocationUpdateTimeInMill = 0, waitingTime = 0;
    private static final long MIN_TIME_BW_UPDATES = 1000 * (long) 15;

    private final OnDemoLocationListener onDemoLocationListener;

    private int LOCATION_POST_MIN_DISTANCE_IN_METERS;
    private int LOCATION_BATCH_TASK_DURATION;
    private int PROVIDER_STATUS_TASK_DURATION;

    private final String UPDATE_SERVICE_LOCATION_TAG = "UPDATE_SERVICE_LOCATION_TASK";
    private final String UPDATE_PROVIDER_STATUS_TAG = "UPDATE_STATUS_TASK";

    private final ArrayList<HashMap<String, String>> allLocationsList = new ArrayList<>();
    private ArrayList<Location> listOfTMPLocations;

    protected UpdateDriverLocation(GeneralFunctions generalFunc, Context mContext, boolean isStartLocationStorage, boolean isServiceAssigned, boolean isServiceStarted, String iTripId, OnDemoLocationListener onDemoLocationListener) {
        this.generalFunc = generalFunc;
        this.mContext = mContext;
        this.isStartLocationStorage = isStartLocationStorage;
        this.isServiceAssigned = isServiceAssigned;
        this.isServiceStarted = isServiceStarted;
        this.iTripId = iTripId;
        this.onDemoLocationListener = onDemoLocationListener;
    }


    public void executeProcess() {
        if (mContext == null) {
            return;
        }

        HashMap<String, String> data = new HashMap<>();
        data.put(Utils.PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT, "");
        data.put("LOCATION_ACCURACY_METERS", "");
        data.put(Utils.USER_PROFILE_JSON, "");
        data = generalFunc.retrieveValue(data);

        PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT = GeneralFunctions.parseDoubleValue(5, data.get(Utils.PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT));

        userProfileJsonObj = generalFunc.getJsonObject(data.get(Utils.USER_PROFILE_JSON));

        LOCATION_POST_MIN_DISTANCE_IN_METERS = GeneralFunctions.parseIntegerValue(5, generalFunc.getJsonValueStr("LOCATION_POST_MIN_DISTANCE_IN_METERS", userProfileJsonObj));
        LOCATION_BATCH_TASK_DURATION = GeneralFunctions.parseIntegerValue(14, generalFunc.getJsonValueStr("LOCATION_BATCH_TASK_DURATION", userProfileJsonObj));
        PROVIDER_STATUS_TASK_DURATION = GeneralFunctions.parseIntegerValue(14, generalFunc.getJsonValueStr("PROVIDER_STATUS_TASK_DURATION", userProfileJsonObj));

        setTripStartValue(isStartLocationStorage, isServiceAssigned, isServiceStarted, iTripId);
    }

    public void setTripStartValue(boolean isStartLocationStorage, boolean isServiceAssigned, boolean isServiceStarted, String iTripId) {
        this.isStartLocationStorage = isStartLocationStorage;
        this.isServiceAssigned = isServiceAssigned;
        this.isServiceStarted = isServiceStarted;
        this.iTripId = iTripId;

        if (dispatchDemoLoc != null) {
            dispatchDemoLoc.stopDispatchingDemoLocations();
            dispatchDemoLoc = null;
        }

        Logger.e(TAG, "::isServiceStarted::" + isServiceStarted);

        if (isStartLocationStorage) {
            UpdateDirections.setRouteFoundListener(new UpdateDirections.RouteFoundListener() {
                @Override
                public void onRouteFound(ArrayList<Location> listOfLocations) {
                    if (listOfTMPLocations == null) {
                        listOfTMPLocations = new ArrayList<>();
                        listOfTMPLocations.addAll(listOfLocations);

                        if (dispatchDemoLoc != null && dispatchDemoLoc.listOfTMPLocations == null) {
                            dispatchDemoLoc.listOfTMPLocations = listOfTMPLocations;
                        }
                    } else {
                        UpdateDirections.clearAllListeners(this);
                    }
                }
            });

            boolean isDummyLocEnable = generalFunc.getJsonValueStr("eEnableDemoLocDispatch", userProfileJsonObj).equalsIgnoreCase("Yes");
            boolean isAutoDummyLocEnable = generalFunc.getJsonValueStr("eEnableDemoLocAutoDispatch", userProfileJsonObj).equalsIgnoreCase("Yes");

            if (isDummyLocEnable || isAutoDummyLocEnable) {
                dispatchDemoLoc = new DispatchDemoLocations(mContext);
                dispatchDemoLoc.startDispatchingLocations(isAutoDummyLocEnable ? DispatchDemoLocations.DATA_TYPE.DIRECTION : DispatchDemoLocations.DATA_TYPE.DUMMY, (position, latitude, longitude) -> {

                    Location loc_tmp = new Location("gps");
                    loc_tmp.setLatitude(latitude);
                    loc_tmp.setLongitude(longitude);

                    onLocationUpdate(loc_tmp);

                    if (onDemoLocationListener != null) {
                        onDemoLocationListener.onReceiveDemoLocation(loc_tmp);
                    }
                });
            }
        } else {
            if (dispatchDemoLoc != null) {
                dispatchDemoLoc.stopDispatchingDemoLocations();
                dispatchDemoLoc = null;
            }
        }

        if (updateDriverStatusTask != null) {
            updateDriverStatusTask.stopRepeatingTask();
            updateDriverStatusTask = null;
        }

        if (updateTripLocationsTask != null) {
            updateTripLocationsTask.stopRepeatingTask();
            updateTripLocationsTask = null;
        }

        if (isStartLocationStorage || generalFunc.getJsonValueStr("ENABLE_PROVIDER_INSURANCE_LOCATIONS", userProfileJsonObj).equalsIgnoreCase("Yes")) {
            updateTripLocationsTask = new RecurringTask(LOCATION_BATCH_TASK_DURATION * 1000);
            updateTripLocationsTask.setTag(UPDATE_SERVICE_LOCATION_TAG);
            updateTripLocationsTask.setTaskRunListener(this);
            updateTripLocationsTask.startRepeatingTask();

            allLocationsList.clear();
            JSONArray lastLocArr = generalFunc.getJsonArray(generalFunc.retrieveValue("PROVIDER_STATUS_MODE_LAST_DATA"));
            if (lastLocArr != null) {
                for (int i = 0; i < lastLocArr.length(); i++) {
                    JSONObject obj = generalFunc.getJsonObject(lastLocArr, i);
                    HashMap<String, String> dataMap = new HashMap<>();
                    dataMap.put("Latitude", generalFunc.getJsonValueStr("Latitude", obj));
                    dataMap.put("Longitude", generalFunc.getJsonValueStr("Longitude", obj));
                    dataMap.put("providerStatusMode", generalFunc.getJsonValueStr("providerStatusMode", obj));
                    allLocationsList.add(dataMap);
                }
            }
        }
        updateDriverStatusTask = new RecurringTask(PROVIDER_STATUS_TASK_DURATION * 1000);
        updateDriverStatusTask.setTag(UPDATE_PROVIDER_STATUS_TAG);
        updateDriverStatusTask.setTaskRunListener(this);
        updateDriverStatusTask.startRepeatingTask();
    }

    public void onLocationUpdate(Location location) {
        if (location == null) {
            return;
        }

        if (generalFunc.getJsonValueStr("ENABLE_PROVIDER_INSURANCE_LOCATIONS", userProfileJsonObj).equalsIgnoreCase("Yes")) {
            if (MyApp.isDriverOnline() || Utils.checkText(iTripId) || isStartLocationStorage) {
                if (driverLocation == null || location.distanceTo(driverLocation) > LOCATION_POST_MIN_DISTANCE_IN_METERS) {
                    HashMap<String, String> dataMap = new HashMap<>();
                    dataMap.put("Latitude", "" + location.getLatitude());
                    dataMap.put("Longitude", "" + location.getLongitude());
                    dataMap.put("providerStatusMode", generalFunc.retrieveValue("PROVIDER_STATUS_MODE"));
                    allLocationsList.add(dataMap);
                }
            }
        } else if (isStartLocationStorage) {
            if (driverLocation == null || location.distanceTo(driverLocation) > LOCATION_POST_MIN_DISTANCE_IN_METERS) {
                HashMap<String, String> dataMap = new HashMap<>();
                dataMap.put("Latitude", "" + location.getLatitude());
                dataMap.put("Longitude", "" + location.getLongitude());
                dataMap.put("providerStatusMode", generalFunc.retrieveValue("PROVIDER_STATUS_MODE"));
                allLocationsList.add(dataMap);
            }
        }

        this.driverLocation = location;

        if (isServiceStarted) {
            if (lastLocationUpdateTimeInMill == 0) {
                lastLocationUpdateTimeInMill = System.currentTimeMillis();
            } else {
                long currentTimeInMill = System.currentTimeMillis();
                if ((currentTimeInMill - lastLocationUpdateTimeInMill) > MIN_TIME_BW_UPDATES) {
                    waitingTime = waitingTime + (currentTimeInMill - lastLocationUpdateTimeInMill);
                    lastLocationUpdateTimeInMill = currentTimeInMill;
                }
                generalFunc.storeData(Utils.DriverWaitingTime, "" + waitingTime);
            }
        }

        if (driverLocation != null) {

            if (lastPublishedLocation == null || (lastPublishedLocation.distanceTo(driverLocation) > 2)) {
                lastPublishedLocation = driverLocation;
                if (lastPublishedLoc != null) {

                    if (driverLocation.distanceTo(lastPublishedLoc) < PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT) {
                        return;
                    } else {
                        lastPublishedLoc = driverLocation;
                    }

                } else {
                    lastPublishedLoc = driverLocation;
                }

                ArrayList<String> channelName = new ArrayList<>();
                channelName.add(generalFunc.getLocationUpdateChannel());
                AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).setMessage(isServiceStarted ? generalFunc.buildLocationJson(driverLocation, "LocationUpdateOnTrip") : generalFunc.buildLocationJson(driverLocation)).build(), AppService.Event.PUBLISH);
            }
        }
    }

    public void stopProcess() {
        if (dispatchDemoLoc != null) {
            dispatchDemoLoc.stopDispatchingDemoLocations();
            dispatchDemoLoc = null;
        }

        if (updateDriverStatusTask != null) {
            updateDriverStatusTask.stopRepeatingTask();
            updateDriverStatusTask = null;
        }

        if (updateTripLocationsTask != null) {
            updateTripLocationsTask.stopRepeatingTask();
            updateTripLocationsTask = null;
        }
    }

    @Override
    public void onTaskRun(RecurringTask instance) {

        switch (instance.getTag()) {
            case UPDATE_SERVICE_LOCATION_TAG:
                sendServiceLocations(0);
                break;
            case UPDATE_PROVIDER_STATUS_TAG:
                updateProviderStatus(0);
                break;
        }
    }

    private void sendServiceLocations(int repeatCount) {
        if (allLocationsList.isEmpty() || isDataUploading || repeatCount > 3) {
            return;
        }
        isDataUploading = true;

        ArrayList<HashMap<String, String>> tmp_list = new ArrayList<>();
        int count = 0;
        for (HashMap<String, String> loc : allLocationsList) {
            if (count >= 50) {
                break;
            }
            tmp_list.add(loc);
            count++;
        }

        Type type = new TypeToken<ArrayList<HashMap<String, String>>>() {
        }.getType();
        JsonElement element = new Gson().toJsonTree(tmp_list, type);

        HashMap<String, Object> parameters = new HashMap<>();
        parameters.put("iTripId", iTripId);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("ServiceLocations", element.getAsJsonArray());

        AppService.getInstance().sendMessage(SocketEvents.SERVICE_LOCATIONS, (new Gson()).toJson(parameters), 30000, (name, errorObj, dataObj) -> {
            isDataUploading = false;
            if (errorObj != null) {
                int rCount = repeatCount + 1;
                sendServiceLocations(rCount);
            } else if (dataObj != null) {
                if (!tmp_list.isEmpty()) {
                    generalFunc.storeData("PROVIDER_STATUS_MODE_LAST_DATA", "");
                }
                for (int i = 0; i < tmp_list.size(); i++) {
                    if (i >= 50) {
                        break;
                    }
                    if (!allLocationsList.isEmpty()) {
                        allLocationsList.remove(0);
                    }
                }
            }
        });
    }

    private void updateProviderStatus(int repeatCount) {
        if (repeatCount > 3) {
            return;
        }

        HashMap<String, Object> parameters = new HashMap<>();
        parameters.put("iMemberId", generalFunc.getMemberId());

        if (driverLocation != null) {
            parameters.put("vLatitude", "" + driverLocation.getLatitude());
            parameters.put("vLongitude", "" + driverLocation.getLongitude());
        }

        parameters.put("vAvailability", MyApp.isDriverOnline() ? "Available" : "Not Available");

        AppService.getInstance().sendMessage(SocketEvents.PROVIDER_STATUS, (new Gson()).toJson(parameters), 10000, (name, errorObj, dataObj) -> {

            if (errorObj != null) {
                int rCount = repeatCount + 1;
                updateProviderStatus(rCount);
            }
        });
    }

    public ArrayList<HashMap<String, String>> getListOfLocations() {
        return allLocationsList;
    }

    protected interface OnDemoLocationListener {
        void onReceiveDemoLocation(Location location);
    }
}
