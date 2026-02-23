package com.general.files;

import android.content.Context;
import android.location.Location;

import com.act.OnGoingTripDetailsActivity;
import com.act.deliverAll.TrackOrderActivity;
import com.buddyverse.main.R;
import com.map.GeoMapLoader;
import com.map.Polyline;
import com.map.helper.MapDirectionResult;
import com.map.models.PolylineOptions;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Locale;

/**
 * Created by Admin on 02-08-2017.
 */

//public class UpdateDirections implements GetLocationUpdates.LocationUpdates, RecurringTask.OnTaskRunCalled {
public class UpdateDirections implements RecurringTask.OnTaskRunCalled {

    public GeoMapLoader.GeoMap geoMap;
    public Location destinationLocation;
    public Context mcontext;
    public Location userLocation;

    GeneralFunctions generalFunctions;

    String serverKey;
    Polyline route_polyLine;

    RecurringTask updateFreqTask;

    String gMapLngCode = "en";
    String userProfileJson = "";
    String eUnit = "KMs";
    int DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = 3;

    public UpdateDirections(Context mcontext, GeoMapLoader.GeoMap geoMap, Location userLocation, Location destinationLocation) {
        this.geoMap = geoMap;
        this.destinationLocation = destinationLocation;
        this.mcontext = mcontext;
        this.userLocation = userLocation;

        generalFunctions = MyApp.getInstance().getGeneralFun(mcontext);

        serverKey = generalFunctions.retrieveValue(Utils.GOOGLE_SERVER_ANDROID_PASSENGER_APP_KEY);

        gMapLngCode = generalFunctions.retrieveValue(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY);

        userProfileJson = generalFunctions.retrieveValue(Utils.USER_PROFILE_JSON);
        eUnit = generalFunctions.getJsonValue("eUnit", userProfileJson);
        DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = GeneralFunctions.parseIntegerValue(3, generalFunctions.getJsonValue("DRIVER_ARRIVED_MIN_TIME_PER_MINUTE", userProfileJson));
    }

    public void scheduleDirectionUpdate() {


        String DESTINATION_UPDATE_TIME_INTERVAL = generalFunctions.retrieveValue("DESTINATION_UPDATE_TIME_INTERVAL");
        updateFreqTask = new RecurringTask((int) (GeneralFunctions.parseDoubleValue(2, DESTINATION_UPDATE_TIME_INTERVAL) * 60 * 1000));
        updateFreqTask.setTaskRunListener(this);
        updateFreqTask.startRepeatingTask();

    }

    public void releaseTask() {
        Logger.d("Task", ":: releaseTask called");
        if (updateFreqTask != null) {
            updateFreqTask.stopRepeatingTask();
            updateFreqTask = null;
        }

        // Utils.runGC();
    }


    public void updateDirections() {

        if (userLocation == null || destinationLocation == null) {
            return;
        }


        if (userProfileJson != null && !generalFunctions.getJsonValue("ENABLE_DIRECTION_SOURCE_DESTINATION_USER_APP", userProfileJson).equalsIgnoreCase("Yes")) {


            double distance = Utils.CalculationByLocation(userLocation.getLatitude(), userLocation.getLongitude(), destinationLocation.getLatitude(), destinationLocation.getLongitude(), "");

            if (userProfileJson != null && !generalFunctions.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
                distance = distance * 0.621371;
            } else {
                distance = distance * 0.99999969062399994;
            }

            distance = generalFunctions.round(distance, 2);
            int time = ((int) (distance * DRIVER_ARRIVED_MIN_TIME_PER_MINUTE));

            if (time < 1) {
                time = 1;
            }
            if (mcontext instanceof OnGoingTripDetailsActivity) {
                OnGoingTripDetailsActivity onGoingTripDetailsActivity = (OnGoingTripDetailsActivity) mcontext;
                onGoingTripDetailsActivity.setTimetext(generalFunctions.formatUpto2Digit(distance) + "", getTimeTxt((time % 3600) / 60));
            }

            if (mcontext instanceof TrackOrderActivity) {
                TrackOrderActivity trackOrderActivity = (TrackOrderActivity) mcontext;
                trackOrderActivity.setEta("" + getTimeTxt((time % 3600) / 60), "" + distance, generalFunctions.getJsonValue("eUnit", userProfileJson));
            }

            return;
        }


        String originLoc = userLocation.getLatitude() + "," + userLocation.getLongitude();
        String destLoc = destinationLocation.getLatitude() + "," + destinationLocation.getLongitude();

        // String directionURL = "https://maps.googleapis.com/maps/api/directions/json?origin=" + originLoc + "&destination=" + destLoc + "&sensor=true&key=" + serverKey + "&language=" + gMapLngCode + "&sensor=true";

        HashMap<String, String> hashMap = new HashMap<>();


        String trip_data = generalFunctions.getJsonValue("TripDetails", userProfileJson);

        String eTollSkipped = generalFunctions.getJsonValue("eTollSkipped", trip_data);
        boolean istollSkip = eTollSkipped.equals("Yes");

        AppService.getInstance().executeService(mcontext, new DataProvider.DataProviderBuilder(userLocation.getLatitude() + "", userLocation.getLongitude() + "").setDestLatitude(destinationLocation.getLatitude() + "").setDestLongitude(destinationLocation.getLongitude() + "").setTollAccess(istollSkip).setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunctions.showGeneralMessage("", generalFunctions.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
            String responseString = data.get("RESPONSE_DATA").toString();

            if (responseString != null && !responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {

                JSONArray obj_routes = generalFunctions.getJsonArray("routes", responseString);
                if (obj_routes != null && obj_routes.length() > 0) {
                    JSONObject obj_legs = generalFunctions.getJsonObject(generalFunctions.getJsonArray("legs", generalFunctions.getJsonObject(obj_routes, 0).toString()), 0);


                    String distance = "" + generalFunctions.getJsonValue("value",
                            generalFunctions.getJsonValue("distance", obj_legs.toString()).toString());
                    String time = "" + generalFunctions.getJsonValue("value",
                            generalFunctions.getJsonValue("duration", obj_legs.toString()).toString());

                    double distance_final = generalFunctions.parseDoubleValue(0.0, distance);


                    if (userProfileJson != null && !generalFunctions.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
                        distance_final = distance_final * 0.000621371;
                    } else {
                        distance_final = distance_final * 0.00099999969062399994;
                    }

                    distance_final = generalFunctions.round(distance_final, 2);

                    if (mcontext instanceof OnGoingTripDetailsActivity) {
                        OnGoingTripDetailsActivity onGoingTripDetailsActivity = (OnGoingTripDetailsActivity) mcontext;
                        onGoingTripDetailsActivity.setTimetext(String.format(Locale.ENGLISH, "%.2f", (float) distance_final) + "", getTimeTxt(GeneralFunctions.parseIntegerValue(0, time)));
                    }

                    if (mcontext instanceof TrackOrderActivity) {
                        TrackOrderActivity trackOrderActivity = (TrackOrderActivity) mcontext;
                        trackOrderActivity.setEta(getTimeTxt((GeneralFunctions.parseIntegerValue(0, time) % 3600) / 60), "" + distance_final, generalFunctions.getJsonValue("eUnit", userProfileJson));
                    }
                }


                if (geoMap != null) {

                    PolylineOptions lineOptions = MapDirectionResult.getRouteOptions(responseString, 5, mcontext.getResources().getColor(R.color.black));

                    if (lineOptions != null) {
                        if (route_polyLine != null) {
                            route_polyLine.remove();
                        }
                        route_polyLine = geoMap.addPolyline(lineOptions);

                    }
                }


            } else {

                Logger.d("RESULTDATA", "::" + data);

                double distance_final = GeneralFunctions.parseDoubleValue(0.0, data.get("DISTANCE").toString());

                if (userProfileJson != null && !generalFunctions.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
                    distance_final = distance_final * 0.000621371;
                } else {
                    distance_final = distance_final * 0.00099999969062399994;
                }
                distance_final = generalFunctions.round(distance_final, 2);

                String time = data.get("DURATION").toString();


                String timeToreach = "1";

                int duration = (int) Math.round((GeneralFunctions.parseDoubleValue(0.0,
                        time) / 60));


                timeToreach = getTimeTxt(duration);


                if (mcontext instanceof OnGoingTripDetailsActivity) {
                    OnGoingTripDetailsActivity onGoingTripDetailsActivity = (OnGoingTripDetailsActivity) mcontext;
                    onGoingTripDetailsActivity.setTimetext(String.format(Locale.ENGLISH, "%.2f", (float) distance_final) + "", timeToreach);
                }
                if (mcontext instanceof TrackOrderActivity) {
                    TrackOrderActivity trackOrderActivity = (TrackOrderActivity) mcontext;
                    trackOrderActivity.setEta(timeToreach, "" + distance_final, generalFunctions.getJsonValue("eUnit", userProfileJson));
                }


                if (geoMap != null) {
                    Logger.d("RESULTDATA", "::routes" + data.get("ROUTES"));
                    HashMap<String, Object> data_dict = new HashMap<>();
                    data_dict.put("routes", data.get("ROUTES"));


                    PolylineOptions lineOptions = MapDirectionResult.getRouteOptions(data_dict.toString(), 5, mcontext.getResources().getColor(R.color.black));

                    if (lineOptions != null) {
                        if (route_polyLine != null) {
                            route_polyLine.remove();
                        }
                        route_polyLine = geoMap.addPolyline(lineOptions);

                    }
                }

            }


        });

    }


    public String getTimeTxt(int duration) {

        if (duration < 1) {
            duration = 1;
        }
        String durationTxt = "";
        String timeToreach = duration == 0 ? "--" : "" + duration;

        timeToreach = duration >= 60 ? formatHoursAndMinutes(duration) : timeToreach;


        durationTxt = (duration < 60 ? generalFunctions.retrieveLangLBl("", "LBL_MINS_SMALL") : generalFunctions.retrieveLangLBl("", "LBL_HOUR_TXT"));

        durationTxt = duration == 1 ? generalFunctions.retrieveLangLBl("", "LBL_MIN_SMALL") : durationTxt;
        durationTxt = duration > 120 ? generalFunctions.retrieveLangLBl("", "LBL_HOURS_TXT") : durationTxt;

        Logger.d("durationTxt", "::" + durationTxt);
        return timeToreach + " " + durationTxt;
    }

    public static String formatHoursAndMinutes(int totalMinutes) {
        String minutes = Integer.toString(totalMinutes % 60);
        minutes = minutes.length() == 1 ? "0" + minutes : minutes;
        return (totalMinutes / 60) + ":" + minutes;
    }

    @Override
    public void onTaskRun(RecurringTask instance) {
        Utils.runGC();
        updateDirections();
    }

    public void changeUserLocation(Location location) {
        if (location != null) {
            this.userLocation = location;
        }
    }


}
