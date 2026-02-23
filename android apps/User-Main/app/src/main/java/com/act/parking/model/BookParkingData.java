package com.act.parking.model;

import androidx.annotation.Nullable;

public class BookParkingData {

    @Nullable
    private LocationDetails locationDetails;

    @Nullable
    private String dateTime = "";

    @Nullable
    private String PricePerHour = "";
    @Nullable
    private String parkingSpaceNo = "";
    @Nullable
    private String FullAddress = "";

    @Nullable
    private String vehicleSize = "";

    @Nullable
    private String documentIds;
    @Nullable
    private String ImageIds;

    @Nullable
    private String instructions;

    ///////////////////////////////////////////////////////////////////////////////////////////////

    @Nullable
    public LocationDetails getLocationDetails() {
        return locationDetails;
    }

    public void setLocationDetails(@Nullable LocationDetails locationDetails) {
        this.locationDetails = locationDetails;
    }

    @Nullable
    public String getDateTime() {
        return dateTime;
    }

    public void setDateTime(@Nullable String dateTime) {
        this.dateTime = dateTime;
    }

    @Nullable
    public String getFullAddress() {
        return FullAddress;
    }

    public void setFullAddress(@Nullable String FullAddress) {
        this.FullAddress = FullAddress;
    }

    @Nullable
    public String getVehicleSize() {
        return vehicleSize;
    }

    public void setVehicleSize(@Nullable String vehicleSize) {
        this.vehicleSize = vehicleSize;
    }

    @Nullable
    public String getPricePerHour() {
        return PricePerHour;
    }

    public void setPricePerHour(@Nullable String PricePerHour) {
        this.PricePerHour = PricePerHour;
    }

    @Nullable
    public String getParkingSpaceNo() {
        return parkingSpaceNo;
    }

    public void setParkingSpaceNo(@Nullable String parkingSpaceNo) {
        this.parkingSpaceNo = parkingSpaceNo;
    }

    @Nullable
    public String getParkingInstructions() {
        return instructions;
    }

    public void setParkingInstructions(@Nullable String instructions) {
        this.instructions = instructions;
    }

    @Nullable
    public String getDocumentIds() {
        return documentIds;
    }

    public void setDocumentIds(@Nullable String documentIds) {
        this.documentIds = documentIds;
    }

    @Nullable
    public String getParkingImageIds() {
        return ImageIds;
    }

    public void setParkingImageIds(@Nullable String ImageIds) {
        this.ImageIds = ImageIds;
    }
    /////////////////////////////////////////////////////////

    public static class LocationDetails {

        @Nullable
        private String BookingAddress;

        @Nullable
        private String BookingLatitude;

        @Nullable
        private String BookingLongitude;


        @Nullable
        public String getBookingAddress() {
            return BookingAddress;
        }

        public void setBookingAddress(@Nullable String bookingAddress) {
            this.BookingAddress = bookingAddress;
        }

        @Nullable
        public String getBookingLatitude() {
            return BookingLatitude;
        }

        public void setBookingLatitude(@Nullable String fromLatitude) {
            this.BookingLatitude = fromLatitude;
        }

        @Nullable
        public String getBookingLongitude() {
            return BookingLongitude;
        }

        public void setBookingLongitude(@Nullable String fromLongitude) {
            this.BookingLongitude = fromLongitude;
        }


    }
}