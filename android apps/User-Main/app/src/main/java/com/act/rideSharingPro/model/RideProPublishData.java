package com.act.rideSharingPro.model;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.general.files.GeneralFunctions;

import org.json.JSONArray;

import java.util.ArrayList;

public class RideProPublishData {

    @Nullable
    private ArrayList<MultiStopData> multiStopData;

    @Nullable
    private String dateTime;

    @Nullable
    private String perSeat;

    @Nullable
    private String recommendedPrice;
    @Nullable
    private String passengerNo;
    @Nullable
    private String carDetails;

    @Nullable
    private String recommdedPriceText;
    @Nullable
    private String recommdedPriceRange;
    @Nullable
    private String pointRecommendedPrice;

    @Nullable
    private JSONArray dynamicDetailsArray;

    @Nullable
    private String startCity;

    @Nullable
    private String endCity;

    @Nullable
    private String documentIds;

    ///////////////////////////////////////////////////////////////////////////////////////////////

    @Nullable
    public ArrayList<MultiStopData> getMultiStopData() {
        return multiStopData;
    }

    public void setMultiStopData(@Nullable ArrayList<MultiStopData> multiStopData) {
        this.multiStopData = multiStopData;
    }

    @Nullable
    public String getDateTime() {
        return dateTime;
    }

    public void setDateTime(@Nullable String dateTime) {
        this.dateTime = dateTime;
    }

    @Nullable
    public String getPerSeat() {
        return perSeat;
    }

    public void setPerSeat(@Nullable String perSeat) {
        this.perSeat = perSeat;
    }

    @Nullable
    public String getRecommendedPrice() {
        return GeneralFunctions.parseDoubleValue(0.0, recommendedPrice) >= 1 ? recommendedPrice : "";
    }

    public void setRecommendedPrice(@Nullable String recommendedPrice) {
        this.recommendedPrice = recommendedPrice;
    }

    @Nullable
    public String getPassengerNo() {
        return passengerNo;
    }

    public void setPassengerNo(@Nullable String passengerNo) {
        this.passengerNo = passengerNo;
    }

    @Nullable
    public String getCarDetails() {
        return carDetails;
    }

    public void setCarDetails(@Nullable String carDetails) {
        this.carDetails = carDetails;
    }

    @Nullable
    public String getRecommdedPriceText() {
        return recommdedPriceText;
    }

    public void setRecommdedPriceText(@Nullable String recommendedMessage) {
        this.recommdedPriceText = recommendedMessage;
    }

    @Nullable
    public String getRecommdedPriceRange() {
        return recommdedPriceRange;
    }

    public void setRecommdedPriceRange(@Nullable String recommendedMessage) {
        this.recommdedPriceRange = recommendedMessage;
    }

    @Nullable
    public String getPointRecommendedPrice() {
        return pointRecommendedPrice;
    }

    public void setPointRecommendedPrice(@Nullable String pointRecommendedPrice) {
        this.pointRecommendedPrice = pointRecommendedPrice;
    }

    @Nullable
    public JSONArray getDynamicDetailsArray() {
        return dynamicDetailsArray;
    }

    public void setDynamicDetailsArray(@Nullable JSONArray dynamicDetailsArray) {
        this.dynamicDetailsArray = dynamicDetailsArray;
    }

    @Nullable
    public String getStartCity() {
        return startCity;
    }

    public void setStartCity(@Nullable String startCity) {
        this.startCity = startCity;
    }

    @Nullable
    public String getEndCity() {
        return endCity;
    }

    public void setEndCity(@Nullable String endCity) {
        this.endCity = endCity;
    }

    @Nullable
    public String getDocumentIds() {
        return documentIds;
    }

    public void setDocumentIds(@Nullable String documentIds) {
        this.documentIds = documentIds;
    }
    /////////////////////////////////////////////////////////

    public static class MultiStopData {

        @Nullable
        private String isFromLoc;

        @NonNull
        public String getIsFromLoc() {
            return isFromLoc != null ? isFromLoc : "";
        }

        public void setIsFromLoc(@NonNull String isFromLoc) {
            this.isFromLoc = isFromLoc;
        }

        @Nullable
        private String hintLable;

        @NonNull
        public String getHintLable() {
            return hintLable != null ? hintLable : "";
        }

        public void setHintLable(@NonNull String hintLable) {
            this.hintLable = hintLable;
        }

        @Nullable
        private String destAddress;

        @NonNull
        public String getDestAddress() {
            return destAddress != null ? destAddress : "";
        }

        public void setDestAddress(@NonNull String destAddress) {
            this.destAddress = destAddress;
        }

        private double destLat = 0.0;

        public double getDestLat() {
            return destLat;
        }

        public void setDestLat(double destLat) {
            this.destLat = destLat;
        }

        private double destLong = 0.0;

        public double getDestLong() {
            return destLong;
        }

        public void setDestLong(double destLong) {
            this.destLong = destLong;
        }
    }
}