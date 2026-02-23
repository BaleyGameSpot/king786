package com.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.view.View;
import android.widget.RelativeLayout;

import com.drawRoute.DirectionsJSONParser;
import com.general.files.GeneralFunctions;
import com.map.GeoMapLoader;
import com.map.Polyline;
import com.map.helper.PolyLineAnimator;
import com.map.helper.SphericalUtil;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.PolylineOptions;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class MapUtils {

    public static Polyline handleMapAnimation(Context con, GeneralFunctions gf, String rs, LatLng sL, LatLng dL, GeoMapLoader.GeoMap geoMap, Polyline rPolyLine, boolean isGoogle, boolean isAnimation) {

        try {
            if (isAnimation) {
                PolyLineAnimator.getInstance().stopRouteAnim();
            }

            LatLngBounds.Builder builder = new LatLngBounds.Builder();
            builder.include(sL);
            builder.include(dL);

            bindAllMarkers(con, builder, geoMap);

            JSONArray obj_routes1 = gf.getJsonArray("routes", rs);

            PolylineOptions lineOptions = null;
            if (obj_routes1 != null && obj_routes1.length() > 0) {
                lineOptions = getGoogleRouteOptions(gf, rs, 5, con.getResources().getColor(android.R.color.black), isGoogle);
            }

            if (lineOptions != null) {
                if (rPolyLine != null) {
                    rPolyLine.remove();
                    rPolyLine = null;
                }
                rPolyLine = geoMap.addPolyline(lineOptions);
                if (isAnimation) {
                    rPolyLine.remove();
                }
            }

            if (isAnimation) {
                if (rPolyLine != null && rPolyLine.getPoints().size() > 1) {
                    PolyLineAnimator.getInstance().animateRoute(geoMap, rPolyLine.getPoints(), con);
                }
            }

        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        return rPolyLine;

    }

    public static PolylineOptions getGoogleRouteOptions(GeneralFunctions generalFunc, String directionJson, int width, int color, Boolean isGoogle) {
        PolylineOptions lineOptions = new PolylineOptions();

        if (isGoogle) {

            try {
                DirectionsJSONParser parser = new DirectionsJSONParser();
                List<List<HashMap<String, String>>> routes_list = parser.parse(new JSONObject(directionJson));

                ArrayList<LatLng> points = new ArrayList<>();

                if (routes_list.size() > 0) {
                    // Fetching i-th route
                    List<HashMap<String, String>> path = routes_list.get(0);

                    // Fetching all the points in i-th route
                    for (int j = 0; j < path.size(); j++) {
                        HashMap<String, String> point = path.get(j);

                        double lat = Double.parseDouble(point.get("lat"));
                        double lng = Double.parseDouble(point.get("lng"));
                        LatLng position = new LatLng(lat, lng);

                        points.add(position);

                    }

                    lineOptions.addAll(points);
                    lineOptions.width(width);
                    lineOptions.color(color);

                    return lineOptions;
                } else {
                    Logger.d("getGoogleRouteOptionsEx", ":: null");
                    return null;

                }
            } catch (Exception e) {
                Logger.d("getGoogleRouteOptionsEx", "::" + e.toString());
                return null;
            }
        } else {

            try {
                JSONArray obj_routes1 = generalFunc.getJsonArray("routes", directionJson);

                ArrayList<LatLng> points = new ArrayList<LatLng>();

                if (obj_routes1.length() > 0) {
                    // Fetching i-th route
                    // Fetching all the points in i-th route
                    for (int j = 0; j < obj_routes1.length(); j++) {

                        JSONObject point = generalFunc.getJsonObject(obj_routes1, j);

                        LatLng position = new LatLng(GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("latitude", point).toString()), GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("longitude", point).toString()));
                        points.add(position);
                    }
                    lineOptions.addAll(points);
                    lineOptions.width(width);
                    lineOptions.color(color);
                    return lineOptions;
                } else {
                    return null;
                }
            } catch (Exception e) {
                return null;
            }
        }
    }

    // Add Markers
    public static void bindAllMarkers(Context con, LatLngBounds.Builder builder, GeoMapLoader.GeoMap geoMap) {
        try {
            LatLngBounds bounds = builder.build();
            LatLng center = bounds.getCenter();

            LatLng northEast = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
            LatLng southWest = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));

            builder.include(southWest);
            builder.include(northEast);

            int screenWidth = con.getResources().getDisplayMetrics().widthPixels;

            int padding = (int) (screenWidth * 0.15); // offset from edges of the map 15% of screen

            geoMap.setMaxZoomPreference(geoMap.getMaxZoomLevel());
            geoMap.animateCamera(builder.build(), padding);
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public static Bitmap createDrawableFromView(Context context, View view) {
        Utils.getScreenPixelHeight(context);
        view.setLayoutParams(new RelativeLayout.LayoutParams(RelativeLayout.LayoutParams.WRAP_CONTENT, RelativeLayout.LayoutParams.WRAP_CONTENT));
        view.measure((int) Utils.getScreenPixelWidth(context), (int) Utils.getScreenPixelHeight(context));
        view.layout(0, 0, (int) Utils.getScreenPixelWidth(context), (int) Utils.getScreenPixelHeight(context));
        view.buildDrawingCache();
        Bitmap bitmap = Bitmap.createBitmap(view.getMeasuredWidth(), view.getMeasuredHeight(), Bitmap.Config.ARGB_8888);
        Canvas canvas = new Canvas(bitmap);
        view.draw(canvas);

        return bitmap;
    }
}