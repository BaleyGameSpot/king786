package com.map;

import com.general.files.MyApp;
import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;
import com.like.Utils;
import com.map.models.LatLng;
import com.map.models.PatternItem;
import com.map.models.PolylineOptions;
import com.map.models.RoundCap;

import java.util.ArrayList;
import java.util.List;

public class Polyline {

    GoogleMap gMap;
    HuaweiMap hMap;

    ArrayList<LatLng> polylinePoints = new ArrayList<>();

    Object polyLineObj;

    public Polyline() {

    }

    public Polyline(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public Polyline(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public Polyline addPolyline(PolylineOptions options) {
        polylinePoints.clear();
        polylinePoints.addAll(options.points);

        if (gMap != null) {
            com.google.android.gms.maps.model.PolylineOptions gOptions = new com.google.android.gms.maps.model.PolylineOptions();
            for (LatLng point : options.points) {
                gOptions.add(new com.google.android.gms.maps.model.LatLng(point.latitude, point.longitude));
            }

            List<com.google.android.gms.maps.model.PatternItem> patternList = new ArrayList<>();

            for (PatternItem pattern : options.patternList) {
                if (pattern.patternType == PatternItem.Pattern.Gap) {
                    patternList.add(new com.google.android.gms.maps.model.Gap(pattern.length));
                } else if (pattern.patternType == PatternItem.Pattern.Dash) {
                    patternList.add(new com.google.android.gms.maps.model.Dash(pattern.length));
                }
            }

            gOptions.width(Utils.dipToPixels(MyApp.getInstance().getApplicationContext(), options.width)).color(options.color).geodesic(options.geodesic).jointType(options.jointType);

            if (patternList.size() > 0) {
                gOptions.pattern(patternList);
            }

            polyLineObj = gMap.addPolyline(gOptions);
        }
        if (hMap != null) {
            com.huawei.hms.maps.model.PolylineOptions hOptions = new com.huawei.hms.maps.model.PolylineOptions();
            for (LatLng point : options.points) {
                hOptions.add(new com.huawei.hms.maps.model.LatLng(point.latitude, point.longitude));
            }

            List<com.huawei.hms.maps.model.PatternItem> patternList = new ArrayList<>();

            for (PatternItem pattern : options.patternList) {
                if (pattern.patternType == PatternItem.Pattern.Gap) {
                    patternList.add(new com.huawei.hms.maps.model.Gap(pattern.length));
                } else if (pattern.patternType == PatternItem.Pattern.Dash) {
                    patternList.add(new com.huawei.hms.maps.model.Dash(pattern.length));
                }
            }

            hOptions.width(options.width).color(options.color).geodesic(options.geodesic).jointType(options.jointType);

            if (patternList.size() > 0) {
                hOptions.pattern(patternList);
            }

            polyLineObj = hMap.addPolyline(hOptions);
        }

        return this;
    }

    public void setStartCap(RoundCap cap) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Polyline) polyLineObj).setStartCap((com.google.android.gms.maps.model.Cap) cap.getCap());
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Polyline) polyLineObj).setStartCap((com.huawei.hms.maps.model.Cap) cap.getCap());
        }
    }

    public void setEndCap(RoundCap cap) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Polyline) polyLineObj).setStartCap((com.google.android.gms.maps.model.Cap) cap.getCap());
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Polyline) polyLineObj).setStartCap((com.huawei.hms.maps.model.Cap) cap.getCap());
        }
    }

    public void setColor(int color) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Polyline) polyLineObj).setColor(color);
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Polyline) polyLineObj).setColor(color);
        }
    }

    public void remove() {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Polyline) polyLineObj).remove();
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Polyline) polyLineObj).remove();
        }
    }

    public void setPoints(ArrayList<LatLng> points) {
        polylinePoints.clear();
        polylinePoints.addAll(points);

        if (gMap != null) {
            List<com.google.android.gms.maps.model.LatLng> list_points = new ArrayList<>();
            for (LatLng points_tmp : points) {
                list_points.add(new com.google.android.gms.maps.model.LatLng(points_tmp.latitude, points_tmp.longitude));
            }

            ((com.google.android.gms.maps.model.Polyline) polyLineObj).setPoints(list_points);
        } else if (hMap != null) {

            List<com.huawei.hms.maps.model.LatLng> list_points = new ArrayList<>();
            for (LatLng points_tmp : points) {
                list_points.add(new com.huawei.hms.maps.model.LatLng(points_tmp.latitude, points_tmp.longitude));
            }

            ((com.huawei.hms.maps.model.Polyline) polyLineObj).setPoints(list_points);
        }
    }

    public ArrayList<LatLng> getPoints() {
        return polylinePoints;
    }
}
