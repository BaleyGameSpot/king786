package com.map;

import android.graphics.Point;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;
import com.map.models.LatLng;

public class Projection {
    GoogleMap gMap;
    HuaweiMap hMap;

    public Projection() {
    }

    public Projection(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public Projection(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public Point toScreenLocation(LatLng location){
        if(gMap != null){
            return gMap.getProjection().toScreenLocation(new com.google.android.gms.maps.model.LatLng(location.latitude,location.longitude));
        }if(hMap != null){
            return hMap.getProjection().toScreenLocation(new com.huawei.hms.maps.model.LatLng(location.latitude,location.longitude));
        }

        return new Point(0, 0);
    }

    public void getVisibleRegion(){

    }
}
