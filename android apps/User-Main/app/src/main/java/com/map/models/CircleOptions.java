package com.map.models;

import android.graphics.Color;

public class CircleOptions {
    public LatLng centerPosition = new LatLng(0.0, 0.0);
    public double radius = 0.0;
    public int strokeColor = Color.BLACK;
    public int fillColor = Color.WHITE;
    public float strokeWidth = 2;

    public CircleOptions center(LatLng position) {
        this.centerPosition = position;
        return this;
    }

    public CircleOptions radius(double radius) {
        this.radius = radius;
        return this;
    }

    public CircleOptions strokeColor(int strokeColor) {
        this.strokeColor = strokeColor;
        return this;
    }

    public CircleOptions fillColor(int fillColor) {
        this.fillColor = fillColor;
        return this;
    }

    public CircleOptions strokeWidth(float strokeWidth) {
        this.strokeWidth = strokeWidth;
        return this;
    }
}
