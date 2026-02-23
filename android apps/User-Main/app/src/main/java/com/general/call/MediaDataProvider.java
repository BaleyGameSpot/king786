package com.general.call;

import androidx.annotation.Nullable;

import java.io.Serializable;

public class MediaDataProvider implements Serializable {

    //
    public String fromMemberId;
    public String fromMemberType;
    public String fromMemberName;
    public String fromMemberImage;

    //
    public String toMemberId;
    public String toMemberType;
    public String toMemberName;
    public String toMemberImage;

    //
    public String roomName;
    public String phoneNumber;
    public String iTripId;
    public String iOrderId;
    public String vBookingNo;
    public boolean isBid;
    public boolean isVideoCall;

    @Nullable
    public String isForPickupPhotoRequest;
    public CommunicationManager.MEDIA media;

    private MediaDataProvider(Builder builder) {
        this.fromMemberId = builder.fromMemberId;
        this.fromMemberType = builder.fromMemberType;
        this.fromMemberName = builder.fromMemberName;
        this.fromMemberImage = builder.fromMemberImage;

        this.toMemberId = builder.toMemberId;
        this.toMemberType = builder.toMemberType;
        this.toMemberName = builder.toMemberName;
        this.toMemberImage = builder.toMemberImage;

        this.phoneNumber = builder.phoneNumber;
        this.iTripId = builder.iTripId;
        this.iOrderId = builder.iOrderId;
        this.vBookingNo = builder.vBookingNo;
        this.isBid = builder.isBid;
        this.isVideoCall = builder.isVideoCall;

        this.isForPickupPhotoRequest = builder.isForPickupPhotoRequest;
        this.media = builder.media;

        this.roomName = builder.roomName;
    }

    public static class Builder {

        String fromMemberId;
        String fromMemberType;
        String fromMemberName;
        String fromMemberImage;

        String toMemberId;
        String toMemberType;
        String toMemberName;
        String toMemberImage;

        String roomName;
        String phoneNumber;
        String iTripId;
        String iOrderId;
        String vBookingNo;
        boolean isBid = false;
        boolean isVideoCall;

        CommunicationManager.MEDIA media;
        String isForPickupPhotoRequest;

        //-------------------------------------------------------------------------------------
        public MediaDataProvider build() {
            return new MediaDataProvider(this);
        }
        //-------------------------------------------------------------------------------------

        public Builder setFromMemberId(String fromMemberId) {
            this.fromMemberId = fromMemberId;
            return this;
        }

        public Builder setFromMemberType(String fromMemberType) {
            this.fromMemberType = fromMemberType;
            return this;
        }

        public Builder setFromMemberName(String fromMemberName) {
            this.fromMemberName = fromMemberName;
            return this;
        }

        public Builder setFromMemberImage(String fromMemberImage) {
            this.fromMemberImage = fromMemberImage;
            return this;
        }

        public Builder setToMemberId(String toMemberId) {
            this.toMemberId = toMemberId;
            return this;
        }

        public Builder setToMemberType(String toMemberType) {
            this.toMemberType = toMemberType;
            return this;
        }

        public Builder setToMemberName(String toMemberName) {
            this.toMemberName = toMemberName;
            return this;
        }

        public Builder setToMemberImage(String toMemberImage) {
            this.toMemberImage = toMemberImage;
            return this;
        }

        public Builder setPhoneNumber(String phoneNumber) {
            this.phoneNumber = phoneNumber;
            return this;
        }

        public Builder setTripId(String iTripId) {
            this.iTripId = iTripId;
            return this;
        }

        public Builder setOrderId(String iOrderId) {
            this.iOrderId = iOrderId;
            return this;
        }

        public Builder setBookingNo(String vBookingNo) {
            this.vBookingNo = vBookingNo;
            return this;
        }

        public Builder setBid(boolean bid) {
            this.isBid = bid;
            return this;
        }

        public Builder setVideoCall(boolean videoCall) {
            isVideoCall = videoCall;
            return this;
        }

        public Builder setMedia(CommunicationManager.MEDIA media) {
            this.media = media;
            return this;
        }

        public Builder isForPickupPhotoRequest(@Nullable String isForPickupPhotoRequest) {
            this.isForPickupPhotoRequest = isForPickupPhotoRequest;
            return this;
        }

        public Builder setRoomName(String roomName) {
            this.roomName = roomName;
            return this;
        }
    }
}