package com.map.models;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;

public class RoundCap {
    GoogleMap gMap;
    HuaweiMap hMap;

    public RoundCap() {
    }

    public RoundCap(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public RoundCap(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public Object getCap() {
        if (gMap != null) {
            return new com.google.android.gms.maps.model.RoundCap();
        } else if (hMap != null) {
            return new com.huawei.hms.maps.model.RoundCap();
        }

        return null;
    }
}
