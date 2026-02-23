package com.act.intercity.Models;

public class TripConfigModel {

    private String SLatitude = "", SLongitude = "", ELatitude = "", ELongitude = "", SAddress = "", EAddress = "", pickupDateTime = "", dropOffDateTime = "";

    public TripConfigModel() {
    }

    public TripConfigModel(String SLatitude, String SLongitude, String ELatitude, String ELongitude, String SAddress, String EAddress, String pickupDateTime, String dropOffDateTime) {
        this.SLatitude = SLatitude;
        this.SLongitude = SLongitude;
        this.ELatitude = ELatitude;
        this.ELongitude = ELongitude;
        this.SAddress = SAddress;
        this.EAddress = EAddress;
        this.pickupDateTime = pickupDateTime;
        this.dropOffDateTime = dropOffDateTime;
    }

    public String getSLatitude() {
        return SLatitude;
    }

    public void setSLatitude(String SLatitude) {
        this.SLatitude = SLatitude;
    }

    public String getSLongitude() {
        return SLongitude;
    }

    public void setSLongitude(String SLongitude) {
        this.SLongitude = SLongitude;
    }

    public String getELatitude() {
        return ELatitude;
    }

    public void setELatitude(String ELatitude) {
        this.ELatitude = ELatitude;
    }

    public String getELongitude() {
        return ELongitude;
    }

    public void setELongitude(String ELongitude) {
        this.ELongitude = ELongitude;
    }

    public String getSAddress() {
        return SAddress;
    }

    public void setSAddress(String SAddress) {
        this.SAddress = SAddress;
    }

    public String getEAddress() {
        return EAddress;
    }

    public void setEAddress(String EAddress) {
        this.EAddress = EAddress;
    }

    public String getPickupDateTime() {
        return pickupDateTime;
    }

    public void setPickupDateTime(String pickupDateTime) {
        this.pickupDateTime = pickupDateTime;
    }

    public String getDropOffDateTime() {
        return dropOffDateTime;
    }

    public void setDropOffDateTime(String dropOffDateTime) {
        this.dropOffDateTime = dropOffDateTime;
    }
}
