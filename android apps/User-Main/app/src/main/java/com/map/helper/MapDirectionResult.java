package com.map.helper;

import com.drawRoute.DirectionsJSONParser;
import com.general.files.GeneralFunctions;
import com.map.models.LatLng;
import com.map.models.PolylineOptions;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class MapDirectionResult {
    public static PolylineOptions getRouteOptions(String directionJson, int width, int color) {
        PolylineOptions lineOptions = new PolylineOptions();

        try {
            DirectionsJSONParser parser = new DirectionsJSONParser();
            List<List<HashMap<String, String>>> routes_list = parser.parse(new JSONObject(directionJson));

            ArrayList<LatLng> points = new ArrayList<LatLng>();

            if (routes_list.size() > 0) {
                // Fetching i-th route
                List<HashMap<String, String>> path = routes_list.get(0);

                // Fetching all the points in i-th route
                for (int j = 0; j < path.size(); j++) {
                    HashMap<String, String> point = path.get(j);

                    double lat = GeneralFunctions.parseDoubleValue(0.0, point.get("lat"));
                    double lng = GeneralFunctions.parseDoubleValue(0.0, point.get("lng"));
                    LatLng position = new LatLng(lat, lng);

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
