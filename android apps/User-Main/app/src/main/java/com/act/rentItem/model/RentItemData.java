package com.act.rentItem.model;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import org.json.JSONArray;

public class RentItemData {

    @Nullable
    private String eStatusOrg;

    @Nullable
    private String iRentItemPostId;

    @Nullable
    private String iTmpRentItemPostId;

    @Nullable
    private String iItemCategoryId;

    @Nullable
    private String iItemSubCategoryId;

    @Nullable
    private JSONArray dynamicDetailsArray;

    @Nullable
    private String iRentImageId;

    @Nullable
    private LocationDetails locationDetails;

    @Nullable
    private String fAmount;
    @Nullable
    private String fAmountWithoutSymbol;

    @Nullable
    private String eRentItemDuration;

    @Nullable
    private JSONArray pickupTimeSlot;

    private boolean showCallMe = true;

    @Nullable
    private String iPaymentPlanId;

    @Nullable
    private String isBuySell;

    ///////////////////////////////////////////////////////////////////////////////////////////////

    @NonNull
    public String geteStatusOrg() {
        return eStatusOrg == null ? "" : eStatusOrg;
    }

    public void seteStatusOrg(@Nullable String eStatusOrg) {
        this.eStatusOrg = eStatusOrg;
    }

    @Nullable
    public String getiRentItemPostId() {
        return iRentItemPostId;
    }

    public void setiRentItemPostId(@Nullable String iRentItemPostId) {
        this.iRentItemPostId = iRentItemPostId;
    }

    @Nullable
    public String getiTmpRentItemPostId() {
        return iTmpRentItemPostId;
    }

    public void setiTmpRentItemPostId(@Nullable String iTmpRentItemPostId) {
        this.iTmpRentItemPostId = iTmpRentItemPostId;
    }

    @Nullable
    public String getiItemCategoryId() {
        return iItemCategoryId;
    }

    public void setiItemCategoryId(@Nullable String iItemCategoryId) {
        this.iItemCategoryId = iItemCategoryId;
    }

    @Nullable
    public String getiItemSubCategoryId() {
        return iItemSubCategoryId == null ? "" : iItemSubCategoryId;
    }

    public void setiItemSubCategoryId(@Nullable String iItemSubCategoryId) {
        this.iItemSubCategoryId = iItemSubCategoryId;
    }

    @Nullable
    public JSONArray getDynamicDetailsArray() {
        return dynamicDetailsArray;
    }

    public void setDynamicDetailsArray(@Nullable JSONArray dynamicDetailsArray) {
        this.dynamicDetailsArray = dynamicDetailsArray;
    }

    @Nullable
    public String getiRentImageId() {
        return iRentImageId;
    }

    public void setiRentImageId(@Nullable String iRentImageId) {
        this.iRentImageId = iRentImageId;
    }

    @Nullable
    public LocationDetails getLocationDetails() {
        return locationDetails;
    }

    public void setLocationDetails(@Nullable LocationDetails locationDetails) {
        this.locationDetails = locationDetails;
    }

    @Nullable
    public String getfAmount() {
        return fAmount;
    }

    public void setfAmount(@Nullable String fAmount) {
        this.fAmount = fAmount;
    }

    @Nullable
    public String getfAmountWithoutSymbol() {
        return fAmountWithoutSymbol;
    }

    public void setfAmountWithoutSymbol(@Nullable String fAmountWithoutSymbol) {
        this.fAmountWithoutSymbol = fAmountWithoutSymbol;
    }

    @Nullable
    public String geteRentItemDuration() {
        return eRentItemDuration;
    }

    public void seteRentItemDuration(@Nullable String eRentItemDuration) {
        this.eRentItemDuration = eRentItemDuration;
    }

    @Nullable
    public JSONArray getPickupTimeSlot() {
        return pickupTimeSlot;
    }

    public void setPickupTimeSlot(@Nullable JSONArray pickupTimeSlot) {
        this.pickupTimeSlot = pickupTimeSlot;
    }

    public boolean isShowCallMe() {
        return showCallMe;
    }

    public void setShowCallMe(boolean showCallMe) {
        this.showCallMe = showCallMe;
    }

    @Nullable
    public String getiPaymentPlanId() {
        return iPaymentPlanId;
    }

    public void setiPaymentPlanId(@Nullable String iPaymentPlanId) {
        this.iPaymentPlanId = iPaymentPlanId;
    }

    @Nullable
    public String getisBuySell() {
        return isBuySell;
    }

    public void setisBuySell(@Nullable String isBuySell) {
        this.isBuySell = isBuySell;
    }
    /////////////////////////////////////////////////////////

    public static class LocationDetails {

        @Nullable
        private String vLocation;

        @Nullable
        private String vLatitude;

        @Nullable
        private String vLongitude;

        @Nullable
        private String vBuildingNo;

        @Nullable
        private String vAddress;

        private boolean showMyAddress = true;

        @Nullable
        public String getvLocation() {
            return vLocation;
        }

        public void setvLocation(@Nullable String vLocation) {
            this.vLocation = vLocation;
        }

        @Nullable
        public String getvLatitude() {
            return vLatitude;
        }

        public void setvLatitude(@Nullable String vLatitude) {
            this.vLatitude = vLatitude;
        }

        @Nullable
        public String getvLongitude() {
            return vLongitude;
        }

        public void setvLongitude(@Nullable String vLongitude) {
            this.vLongitude = vLongitude;
        }

        @Nullable
        public String getvBuildingNo() {
            return vBuildingNo;
        }

        public void setvBuildingNo(@Nullable String vBuildingNo) {
            this.vBuildingNo = vBuildingNo;
        }

        @Nullable
        public String getvAddress() {
            return vAddress;
        }

        public void setvAddress(@Nullable String vAddress) {
            this.vAddress = vAddress;
        }

        public boolean isShowMyAddress() {
            return showMyAddress;
        }

        public void setShowMyAddress(boolean showMyAddress) {
            this.showMyAddress = showMyAddress;
        }
    }
}