package com.map.models;

import android.graphics.Color;

import androidx.annotation.NonNull;

import java.util.ArrayList;
import java.util.List;

public class PolylineOptions {
    public float width = 10.0F;
    public int color = Color.BLACK;
    public boolean geodesic = false;
    public int jointType = JointType.DEFAULT;

    public ArrayList<LatLng> points = new ArrayList<LatLng>();

    public List<PatternItem> patternList = new ArrayList<>();

    public PolylineOptions add(LatLng points) {
        this.points.add(points);
        return this;
    }

    public PolylineOptions addAll(ArrayList<LatLng> points) {
        this.points.addAll(points);
        return this;
    }

    @NonNull
    public PolylineOptions width(float width) {
        this.width = width;
        return this;
    }

    @NonNull
    public PolylineOptions color(int color) {
        this.color = color;
        return this;
    }

    @NonNull
    public PolylineOptions geodesic(boolean geodesic) {
        this.geodesic = geodesic;
        return this;
    }

    @NonNull
    public PolylineOptions jointType(int jointType) {
        this.jointType = jointType;
        return this;
    }

    @NonNull
    public PolylineOptions pattern(List<PatternItem> patternList) {
        this.patternList = patternList;
        return this;
    }
}
