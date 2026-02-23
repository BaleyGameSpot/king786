package com.map;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;
import com.map.models.CircleOptions;
import com.map.models.LatLng;

public class Circle {

    GoogleMap gMap;
    HuaweiMap hMap;

    Object circleObj;

    public Circle() {

    }

    public Circle(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public Circle(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public Circle addCircle(CircleOptions options) {
        if (gMap != null) {
            circleObj = gMap.addCircle((new com.google.android.gms.maps.model.CircleOptions()).center(new com.google.android.gms.maps.model.LatLng(options.centerPosition.latitude, options.centerPosition.longitude)).strokeColor(options.strokeColor).strokeWidth(options.strokeWidth).fillColor(options.fillColor).radius(options.radius));
        }
        if (hMap != null) {
            circleObj = hMap.addCircle((new com.huawei.hms.maps.model.CircleOptions()).center(new com.huawei.hms.maps.model.LatLng(options.centerPosition.latitude, options.centerPosition.longitude)).strokeColor(options.strokeColor).strokeWidth(options.strokeWidth).fillColor(options.fillColor).radius(options.radius));
        }

        return this;
    }

    public void setRadius(double radius) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Circle) circleObj).setRadius(radius);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Circle) circleObj).setRadius(radius);
        }
    }

    public void setFillColor(int color) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Circle) circleObj).setFillColor(color);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Circle) circleObj).setFillColor(color);
        }
    }

    public void setStrokeColor(int color) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Circle) circleObj).setStrokeColor(color);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Circle) circleObj).setStrokeColor(color);
        }
    }

    public void remove() {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Circle) circleObj).remove();
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Circle) circleObj).remove();
        }
    }
}
