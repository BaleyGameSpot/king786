package com.map.models;

import com.map.BitmapDescriptorFactory;

public class MarkerOptions {
    public BitmapDescriptorFactory.BitmapDescriptor iconDescriptor;
    public LatLng position;

    public float u = 0.5f;
    public float v = 1.0f;
    public String title = "", snippet = "";
    public boolean isFlat = false;

    public MarkerOptions position(LatLng position) {
        this.position = position;
        return this;
    }

    public MarkerOptions icon(BitmapDescriptorFactory.BitmapDescriptor iconDescriptor) {
        this.iconDescriptor = iconDescriptor;
        return this;
    }

    public MarkerOptions anchor(float u, float v) {
        this.u = u;
        this.v = v;
        return this;
    }

    public MarkerOptions title(String title) {
        this.title = title;
        return this;
    }

    public MarkerOptions snippet(String title) {
        this.title = title;
        return this;
    }

    public MarkerOptions flat(boolean isFlat) {
        this.isFlat = isFlat;
        return this;
    }

}
