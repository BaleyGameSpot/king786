package com.general.files;

import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.view.View;

import com.act.DriverArrivedActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.WorkingtrekActivity;
import com.act.deliverAll.TrackOrderActivity;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;
import com.google.maps.android.SphericalUtil;
import com.buddyverse.providers.R;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

/**
 * Created by Admin on 02-08-2017.
 */

public class UpdateDirections implements RecurringTask.OnTaskRunCalled {

    private final Context mcontext;
    private final GeneralFunctions generalFunctions;
    private final JSONObject userProfileJsonObj;

    public Intent data;
    private final GoogleMap googleMap;
    public Location destinationLocation, userLocation;

    private RecurringTask updateFreqTask;
    private Polyline route_polyLine;

    int DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = 3;

    /*Deliver all*/
    Marker placeMarker, driverMarker;
    private boolean eFly;
    boolean isCalledFromDeliverAll, isGoogle = false;
    public List<Double> lattitudeList = new ArrayList<>();
    public List<Double> longitudeList = new ArrayList<>();
    private final static double DEFAULT_CURVE_ROUTE_CURVATURE = 0.5f;
    private final static int DEFAULT_CURVE_POINTS = 60;
    ArrayList<Location> listOfLoc = new ArrayList<>();
    static ArrayList<RouteFoundListener> listOfListeners = new ArrayList<>();

    public UpdateDirections(Context mcontext, GoogleMap googleMap, Location userLocation, Location destinationLocation) {
        this.googleMap = googleMap;
        this.destinationLocation = destinationLocation;
        this.mcontext = mcontext;
        this.userLocation = userLocation;

        generalFunctions = MyApp.getInstance().getGeneralFun(mcontext);
        userProfileJsonObj = generalFunctions.getJsonObject(generalFunctions.retrieveValue(Utils.USER_PROFILE_JSON));

        DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = GeneralFunctions.parseIntegerValue(3, generalFunctions.getJsonValueStr("DRIVER_ARRIVED_MIN_TIME_PER_MINUTE", userProfileJsonObj));
        lattitudeList.clear();
        longitudeList.clear();
    }

    public void iseFly(boolean eFly, LatLng sourceLocation) {
        this.eFly = eFly;
    }

    public void isDeliverAll(boolean isFromDeliverAll) {
        this.isCalledFromDeliverAll = isFromDeliverAll;
    }

    public void setMarkers(Marker placeMarker, Marker driverMarker) {
        this.driverMarker = driverMarker;
        this.placeMarker = placeMarker;
    }

    public void scheduleDirectionUpdate() {
        releaseTask();
        String DESTINATION_UPDATE_TIME_INTERVAL = generalFunctions.retrieveValue("DESTINATION_UPDATE_TIME_INTERVAL");
        updateFreqTask = new RecurringTask((int) (GeneralFunctions.parseDoubleValue(2, DESTINATION_UPDATE_TIME_INTERVAL) * 60 * 1000));
        updateFreqTask.setTaskRunListener(this);
        updateFreqTask.startRepeatingTask();
    }

    public void releaseTask() {
        Logger.d("Task", "::releaseTask called");
        if (updateFreqTask != null) {
            updateFreqTask.stopRepeatingTask();
            updateFreqTask = null;
        }
        Utils.runGC();
    }

    public void changeDestLoc(Location destinationLocation) {
        this.destinationLocation = destinationLocation;
    }

    public static String formatHoursAndMinutes(int totalMinutes) {
        String minutes = Integer.toString(totalMinutes % 60);
        minutes = minutes.length() == 1 ? "0" + minutes : minutes;
        return (totalMinutes / 60) + ":" + minutes;
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
        return timeToreach + " " + durationTxt;
    }

    public void updateDirections() {

        if (userLocation == null || destinationLocation == null || MyApp.getInstance().isGetDetailCall) {
            return;
        }

        if (userProfileJsonObj != null && (!generalFunctions.getJsonValueStr("ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP", userProfileJsonObj).equalsIgnoreCase("Yes") || eFly)) {
            if (destinationLocation != null) {
                if (mcontext instanceof MainActivity activity) {
                    activity.isRouteDrawn();
                } else if (mcontext instanceof MainActivity_22 activity) {
                    activity.isRouteDrawn();
                }
                double distance = GeneralFunctions.calculationByLocation(userLocation.getLatitude(), userLocation.getLongitude(), destinationLocation.getLatitude(), destinationLocation.getLongitude(), "KM");
                Logger.d("Checkdistance", "::" + distance);
                if (!generalFunctions.getJsonValueStr("eUnit", userProfileJsonObj).equalsIgnoreCase("KMs")) {
                    distance = distance * 0.621371;
                }

                distance = generalFunctions.round(distance, 2);

                int lowestTime = ((int) (distance * DRIVER_ARRIVED_MIN_TIME_PER_MINUTE));

                if (lowestTime < 1) {
                    lowestTime = 1;
                }
                if (mcontext instanceof DriverArrivedActivity activity) {
                    activity.setTimetext(generalFunctions.formatUpto2Digit(distance) + "", getTimeTxt(lowestTime));
                    activity.getMap().setPadding(15, 15, 15, 15);
                } else if (mcontext instanceof WorkingtrekActivity activity) {
                    if (destinationLocation.getLatitude() > 0) {
                        activity.setTimetext(generalFunctions.formatUpto2Digit(distance) + "", getTimeTxt(lowestTime));
                        activity.getMap().setPadding(15, 15, 15, 15);
                    }
                } else if (mcontext instanceof TrackOrderActivity activity) {
                    activity.setTimetext(generalFunctions.formatUpto2Digit(distance) + "", getTimeTxt(lowestTime));
                }
            }
            if (!eFly) {
                return;
            }
        }

        Logger.d("CheckFly", "::" + eFly);
        if (eFly) {
            PolylineOptions lineOptions = createCurveRoute(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()), new LatLng(destinationLocation.getLatitude(), destinationLocation.getLongitude()));
            lineOptions.width(Utils.dipToPixels(mcontext, 4));
            lineOptions.color(R.color.black);

            if (route_polyLine != null) {
                route_polyLine.remove();
            }
            route_polyLine = googleMap.addPolyline(lineOptions);
            route_polyLine.setColor(R.color.black);
            return;
        }


        if (destinationLocation != null && destinationLocation.getLatitude() == 0.0) {
            return;
        }

        String trip_data = generalFunctions.getJsonValueStr("TripDetails", userProfileJsonObj);

        String eTollSkipped = generalFunctions.getJsonValue("eTollSkipped", trip_data);

        boolean istollSkip = eTollSkipped.equalsIgnoreCase("Yes");

        if (mcontext instanceof MainActivity || mcontext instanceof MainActivity_22) {
            //loader show
            AppService.getInstance().executeService(mcontext, new DataProvider.DataProviderBuilder(userLocation.getLatitude() + "", userLocation.getLongitude() + "")
                    .setDestLatitude(destinationLocation.getLatitude() + "")
                    .setDestLongitude(destinationLocation.getLongitude() + "")
                    .setWayPoints(new JSONArray())
                    .setTollAccess(istollSkip)
                    .setLoader(true)
                    .build(), AppService.Service.DIRECTION, this::manageResult);
        } else {
            //loader Hide
            AppService.getInstance().executeService(mcontext, new DataProvider.DataProviderBuilder(userLocation.getLatitude() + "", userLocation.getLongitude() + "")
                    .setDestLatitude(destinationLocation.getLatitude() + "")
                    .setDestLongitude(destinationLocation.getLongitude() + "")
                    .setWayPoints(new JSONArray())
                    .setTollAccess(istollSkip)
                    .build(), AppService.Service.DIRECTION, this::manageResult);
        }
    }

    public void manageResult(HashMap<String, Object> data) {
        if (data == null) {
            return;
        }

        if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
            generalFunctions.showGeneralMessage("", generalFunctions.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
            return;
        }
        if (data.get("ROUTES") == null) {
            if (mcontext instanceof MainActivity activity) {
                activity.removeEODTripData(true);
            } else if (mcontext instanceof MainActivity_22 activity) {
                activity.removeEODTripData(true);
            }
            generalFunctions.showGeneralMessage("", generalFunctions.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
            return;
        }
        String responseString = data.get("RESPONSE_DATA").toString();


        if (responseString != null && !responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {
            isGoogle = true;
            JSONArray obj_routes = generalFunctions.getJsonArray("routes", responseString);
            if (obj_routes != null && obj_routes.length() > 0) {
                JSONObject obj_legs = generalFunctions.getJsonObject(generalFunctions.getJsonArray("legs", generalFunctions.getJsonObject(obj_routes, 0).toString()), 0);


                if (mcontext instanceof MainActivity activity) {
                    activity.isRouteDrawn();
                } else if (mcontext instanceof MainActivity_22 activity) {
                    activity.isRouteDrawn();
                }
                String distance = "" + generalFunctions.getJsonValue("value", generalFunctions.getJsonValue("distance", obj_legs.toString()).toString());
                String time = "" + generalFunctions.getJsonValue("value", generalFunctions.getJsonValue("duration", obj_legs.toString()).toString());

                double distance_final = GeneralFunctions.parseDoubleValue(0.0, distance);

                if (userProfileJsonObj != null && !generalFunctions.getJsonValueStr("eUnit", userProfileJsonObj).equalsIgnoreCase("KMs")) {
                    distance_final = distance_final * 0.000621371;
                } else {
                    distance_final = distance_final * 0.00099999969062399994;
                }
                distance_final = generalFunctions.round(distance_final, 2);


                if (mcontext instanceof DriverArrivedActivity activity) {
                    activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt((int) (GeneralFunctions.parseDoubleValue(0, time) / 60)));
                } else if (mcontext instanceof WorkingtrekActivity activity) {
                    activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt((int) (GeneralFunctions.parseDoubleValue(0, time) / 60)));
                } else if (mcontext instanceof TrackOrderActivity activity) {
                    activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt((int) (GeneralFunctions.parseDoubleValue(0, time) / 60)));
                }
            }

            if (googleMap != null) {
                PolylineOptions lineOptions = generalFunctions.getGoogleRouteOptions(responseString, Utils.dipToPixels(mcontext, 5), mcontext.getResources().getColor(R.color.black));
                if (lineOptions != null) {
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                    }
                    route_polyLine = googleMap.addPolyline(lineOptions);
                    if (mcontext instanceof MainActivity activity) {
                        if (activity.mProgressBarEOD != null) {
                            activity.mProgressBarEOD.setVisibility(View.GONE);
                        }
                        if (activity.slideButtonEOD != null) {
                            activity.slideButtonEOD.setVisibility(View.VISIBLE);
                        }
                        lattitudeList = new ArrayList<>();
                        longitudeList = new ArrayList<>();
                        for (int i = 0; i < lineOptions.getPoints().size(); i++) {
                            lattitudeList.add(lineOptions.getPoints().get(i).latitude);
                            longitudeList.add(lineOptions.getPoints().get(i).longitude);
                        }

                    } else if (mcontext instanceof MainActivity_22 activity) {
                        if (activity.mProgressBarEOD != null) {
                            activity.mProgressBarEOD.setVisibility(View.GONE);
                        }
                        if (activity.slideButtonEOD != null) {
                            activity.slideButtonEOD.setVisibility(View.VISIBLE);
                        }
                        lattitudeList = new ArrayList<>();
                        longitudeList = new ArrayList<>();
                        for (int i = 0; i < lineOptions.getPoints().size(); i++) {
                            lattitudeList.add(lineOptions.getPoints().get(i).latitude);
                            longitudeList.add(lineOptions.getPoints().get(i).longitude);
                        }

                    }
                }
            }


        } else {
            isGoogle = false;
            if (mcontext instanceof MainActivity activity) {
                activity.isRouteDrawn();
            } else if (mcontext instanceof MainActivity_22 activity) {
                activity.isRouteDrawn();
            }
            double distance_final = GeneralFunctions.parseDoubleValue(0.0, data.get("DISTANCE").toString());

            if (userProfileJsonObj != null && !generalFunctions.getJsonValueStr("eUnit", userProfileJsonObj).equalsIgnoreCase("KMs")) {
                distance_final = distance_final * 0.000621371;
            } else {
                distance_final = distance_final * 0.00099999969062399994;
            }
            distance_final = generalFunctions.round(distance_final, 2);

            String time = data.get("DURATION").toString();

            int duration = (int) Math.round((GeneralFunctions.parseDoubleValue(0.0, time) / 60));
            if (mcontext instanceof DriverArrivedActivity activity) {
                activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt(duration));
            } else if (mcontext instanceof WorkingtrekActivity activity) {
                activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt(duration));
            } else if (mcontext instanceof TrackOrderActivity activity) {
                activity.setTimetext(generalFunctions.formatUpto2Digit(distance_final) + "", getTimeTxt(duration));
            }


            if (googleMap != null) {

                PolylineOptions lineOptions;
                if (isGoogle) {
                    lineOptions = generalFunctions.getGoogleRouteOptions(responseString, Utils.dipToPixels(mcontext, 5), mcontext.getResources().getColor(R.color.black));
                } else {

                    HashMap<String, Object> data_dict = new HashMap<>();
                    data_dict.put("routes", data.get("ROUTES"));
                    Logger.d("CheckRoute", "::" + data_dict);
                    lineOptions = getGoogleRouteOptionsHandle(data_dict.toString(), Utils.dipToPixels(mcontext, 5), mcontext.getResources().getColor(R.color.black));
                }

                if (lineOptions != null) {
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                    }
                    route_polyLine = googleMap.addPolyline(lineOptions);

                    if (mcontext instanceof MainActivity activity) {
                        if (activity.mProgressBarEOD != null) {
                            activity.mProgressBarEOD.setVisibility(View.GONE);
                        }
                        if (activity.slideButtonEOD != null) {
                            activity.slideButtonEOD.setVisibility(View.VISIBLE);
                        }
                        lattitudeList = new ArrayList<>();
                        longitudeList = new ArrayList<>();
                        for (int i = 0; i < lineOptions.getPoints().size(); i++) {
                            lattitudeList.add(lineOptions.getPoints().get(i).latitude);
                            longitudeList.add(lineOptions.getPoints().get(i).longitude);
                        }

                    } else if (mcontext instanceof MainActivity_22 activity) {
                        if (activity.mProgressBarEOD != null) {
                            activity.mProgressBarEOD.setVisibility(View.GONE);
                        }
                        if (activity.slideButtonEOD != null) {
                            activity.slideButtonEOD.setVisibility(View.VISIBLE);
                        }
                        lattitudeList = new ArrayList<>();
                        longitudeList = new ArrayList<>();
                        for (int i = 0; i < lineOptions.getPoints().size(); i++) {
                            lattitudeList.add(lineOptions.getPoints().get(i).latitude);
                            longitudeList.add(lineOptions.getPoints().get(i).longitude);
                        }

                    }
                }
            }
        }
    }

    @Override
    public void onTaskRun(RecurringTask instance) {
        Utils.runGC();
        Logger.d("Task", "::onTask called");
        updateDirections();
    }

    public void changeUserLocation(Location location) {
        if (location != null) {
            this.userLocation = location;
        }
    }

    public void setIntentData(Intent data) {
        this.data = data;
    }

    private PolylineOptions createCurveRoute(LatLng origin, LatLng dest) {

        double distance = SphericalUtil.computeDistanceBetween(origin, dest);
        double heading = SphericalUtil.computeHeading(origin, dest);
        double halfDistance = distance > 0 ? (distance / 2) : (distance * DEFAULT_CURVE_ROUTE_CURVATURE);

        // Calculate midpoint position
        LatLng midPoint = SphericalUtil.computeOffset(origin, halfDistance, heading);

        // Calculate position of the curve center point
        double sqrCurvature = DEFAULT_CURVE_ROUTE_CURVATURE * DEFAULT_CURVE_ROUTE_CURVATURE;
        double extraParam = distance / (4 * DEFAULT_CURVE_ROUTE_CURVATURE);
        double midPerpendicularLength = (1 - sqrCurvature) * extraParam;
        double r = (1 + sqrCurvature) * extraParam;

        LatLng circleCenterPoint = SphericalUtil.computeOffset(midPoint, midPerpendicularLength, heading + 90.0);

        // Calculate heading between circle center and two points
        double headingToOrigin = SphericalUtil.computeHeading(circleCenterPoint, origin);

        // Calculate positions of points on the curve
        double step = Math.toDegrees(Math.atan(halfDistance / midPerpendicularLength)) * 2 / DEFAULT_CURVE_POINTS;
        //Polyline options
        PolylineOptions options = new PolylineOptions();

        for (int i = 0; i < DEFAULT_CURVE_POINTS; ++i) {
            LatLng pi = SphericalUtil.computeOffset(circleCenterPoint, r, headingToOrigin + i * step);
            options.add(pi);
        }
        return options;
    }


    public String getRouteDetails(HashMap<String, String> directionlist) {
        HashMap<String, String> routeMap = new HashMap<>();
        routeMap.put("routes", directionlist.get("routes"));
        return routeMap.toString();
    }

    private PolylineOptions getGoogleRouteOptionsHandle(String directionJson, int width, int color) {
        PolylineOptions lineOptions = new PolylineOptions();
        try {
            JSONArray obj_routes1 = generalFunctions.getJsonArray("routes", directionJson);
            ArrayList<LatLng> points = new ArrayList<>();
            if (obj_routes1.length() > 0) {
                // Fetching i-th route
                // Fetching all the points in i-th route

                listOfLoc.clear();

                for (int j = 0; j < obj_routes1.length(); j++) {
                    JSONObject point = generalFunctions.getJsonObject(obj_routes1, j);

                    double latitude = GeneralFunctions.parseDoubleValue(0, generalFunctions.getJsonValueStr("latitude", point));
                    double longitude = GeneralFunctions.parseDoubleValue(0, generalFunctions.getJsonValueStr("longitude", point));

                    Location loc_tmp = new Location("gps");
                    loc_tmp.setLatitude(latitude);
                    loc_tmp.setLongitude(longitude);

                    points.add(new LatLng(latitude, longitude));

                    listOfLoc.add(loc_tmp);
                }

                if (!listOfListeners.isEmpty()) {
                    for (RouteFoundListener listener : listOfListeners) {
                        try {
                            if (listener != null) {
                                listener.onRouteFound(listOfLoc);
                            }
                        } catch (Exception e) {
                            Logger.d("Exception", "::" + e.getMessage());
                        }
                    }
                }

                lineOptions.addAll(points);
                lineOptions.width(width);
                lineOptions.color(color);
                return lineOptions;
            } else {
                return null;
            }
        } catch (Exception e) {
            Logger.d("Exception", "::" + e.getMessage());
            return null;
        }
    }

    public static void clearAllListeners() {
        listOfListeners.clear();
    }

    public static void clearAllListeners(RouteFoundListener routeListener) {
        listOfListeners.remove(routeListener);
    }

    public static void setRouteFoundListener(RouteFoundListener routeListener) {
        if (!listOfListeners.isEmpty()) {
            for (RouteFoundListener listener : listOfListeners) {
                if (!listener.getClass().getSimpleName().equalsIgnoreCase(routeListener.getClass().getSimpleName())) {
                    listOfListeners.add(routeListener);
                }
            }
        } else {
            listOfListeners.add(routeListener);
        }
    }

    public interface RouteFoundListener {
        void onRouteFound(ArrayList<Location> listOfLocations);
    }
}