package com.map.models;

import androidx.annotation.NonNull;

import java.util.ArrayList;


public class LatLngBounds {
    ArrayList<LatLng> listOfPosition;
    public LatLng southwest;
    public LatLng northeast;
    public LatLng centroid;

    public LatLngBounds(ArrayList<LatLng> listOfPosition) {
        this.listOfPosition = listOfPosition;

        calculateBounds();
    }

    private void calculateBounds() {
        com.google.android.gms.maps.model.LatLngBounds.Builder latLngBoundBuilder = com.google.android.gms.maps.model.LatLngBounds.builder();

        for (LatLng latLng : listOfPosition) {
            if (latLng == null) {
                continue;
            }
            latLngBoundBuilder.include(new com.google.android.gms.maps.model.LatLng(latLng.latitude, latLng.longitude));
        }

        com.google.android.gms.maps.model.LatLngBounds latLngBounds = latLngBoundBuilder.build();

        southwest = new LatLng(latLngBounds.southwest.latitude, latLngBounds.southwest.longitude);
        northeast = new LatLng(latLngBounds.northeast.latitude, latLngBounds.northeast.longitude);
        com.google.android.gms.maps.model.LatLng latLon = latLngBounds.getCenter();
        centroid = new LatLng(latLon.latitude, latLon.longitude);
    }

    public LatLng getCenter() {
        return centroid;
    }

    public static final class Builder {
        ArrayList<LatLng> listOfPosition = new ArrayList<>();

        public Builder include(@NonNull LatLng position) {
            listOfPosition.add(position);

            return this;
        }

        public LatLngBounds build() {
            return new LatLngBounds(listOfPosition);
        }
    }

    public ArrayList<LatLng> getPoints() {
        return listOfPosition;
    }

}
