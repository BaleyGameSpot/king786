package com.map.models;

import com.utils.Utils;

public class LatLng {
    public double latitude;
    public double longitude;
    public float zoom;

    public LatLng(double latitude, double longitude, float zoom) {
        this.latitude = latitude;
        this.longitude = longitude;
        this.zoom = zoom;
    }

    public LatLng(double latitude, double longitude) {
        this.latitude = latitude;
        this.longitude = longitude;
        this.zoom = Utils.defaultZomLevel;
    }

    public LatLng zoom(float zoom){
        this.zoom =zoom;
        return this;
    }
}
