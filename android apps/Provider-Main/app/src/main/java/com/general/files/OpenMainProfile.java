package com.general.files;

import android.content.Context;
import android.os.Bundle;

import androidx.core.app.ActivityCompat;

import com.act.AccountJustifyActivity;
import com.act.AdditionalChargeActivity;
import com.act.CabRequestedActivity;
import com.act.CollectPaymentActivity;
import com.act.DriverArrivedActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.SuspendedDriver_Activity;
import com.act.TripRatingActivity;
import com.act.ViewMultiDeliveryDetailsActivity;
import com.act.WorkingtrekActivity;
import com.act.deliverAll.LiveTaskListActivity;
import com.general.ServiceRequest;
import com.model.ServiceModule;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

/**
 * Created by Admin on 29-06-2016.
 */
public class OpenMainProfile {
    private final Context mContext;
    private final GeneralFunctions generalFun;
    private final JSONObject userProfileJsonObj;
    private final String responseString;
    private boolean isnotification = false;

    public OpenMainProfile(Context mContext, boolean isCloseOnError, GeneralFunctions generalFun) {
        this.mContext = mContext;
        this.generalFun = generalFun;
        this.responseString = generalFun.retrieveValue(Utils.USER_PROFILE_JSON);
        userProfileJsonObj = generalFun.getJsonObject(this.responseString);

        ServiceModule.configure();
    }

    public OpenMainProfile(Context mContext, boolean isCloseOnError, GeneralFunctions generalFun, boolean isnotification) {
        this.mContext = mContext;
        this.generalFun = generalFun;
        this.isnotification = isnotification;
        this.responseString = generalFun.retrieveValue(Utils.USER_PROFILE_JSON);
        userProfileJsonObj = generalFun.getJsonObject(this.responseString);

        ServiceModule.configure();
    }

    public void startProcess() {
        generalFun.sendHeartBeat();
        setGeneralData();

        MyApp.getInstance().callPreLoadData();

        Bundle bn = new Bundle();

        if (ServiceModule.IsTrackingProvider) {
            if (trackKing(bn)) {
                ActivityCompat.finishAffinity(MyApp.getInstance().getCurrentAct());
                return;
            }
        }

        boolean isBidding = handleBidding(bn);
        if (isBidding) {
            ActivityCompat.finishAffinity(MyApp.getInstance().getCurrentAct());
            return;
        }

        String vTripStatus = generalFun.getJsonValueStr("vTripStatus", userProfileJsonObj);

        boolean lastTripExist = false;
        String Ratings_From_Driver = "";

        if (generalFun.getJsonValueStr("eSystem", userProfileJsonObj).equalsIgnoreCase(Utils.eSystem_Type) || ServiceModule.isDeliverAllOnly()) {
            Ratings_From_Driver = generalFun.getJsonValueStr("Ratings_From_Driver", userProfileJsonObj);

            String ratings_From_Driver_str = generalFun.getJsonValueStr("Ratings_From_Driver", userProfileJsonObj);
            if (Utils.checkText(ratings_From_Driver_str) && !ratings_From_Driver_str.equals("Done")) {
                lastTripExist = true;
            }

        } else {
            if (vTripStatus.contains("Not Active")) {

                String ratings_From_Driver_str = generalFun.getJsonValueStr("Ratings_From_Driver", userProfileJsonObj);

                if (!ratings_From_Driver_str.equals("Done")) {
                    lastTripExist = true;
                }
            }
        }

        boolean isEmailBlankAndOptional = generalFun.isEmailBlankAndOptional(generalFun, generalFun.getJsonValueStr("vEmail", userProfileJsonObj));
        if (generalFun.getJsonValue("vPhone", userProfileJsonObj).equals("") || (generalFun.getJsonValue("vEmail", userProfileJsonObj).equals("") && !isEmailBlankAndOptional)) {
            if (generalFun.getMemberId() != null && !generalFun.getMemberId().equals("")) {
                new ActUtils(mContext).startActWithData(AccountJustifyActivity.class, bn);
            }
        } else if (generalFun.getJsonValueStr("eSystem", userProfileJsonObj).equalsIgnoreCase(Utils.eSystem_Type) || ServiceModule.isDeliverAllOnly()) {
            HashMap<String, String> map = setMapData();
            if (vTripStatus.contains("Finished") && lastTripExist) {
                bn.putSerializable("TRIP_DATA", map);
                new ActUtils(mContext).startActWithData(TripRatingActivity.class, bn);
            } else if (!vTripStatus.equals("NONE") && !vTripStatus.equals("Cancelled") && (vTripStatus.trim().equals("Active") || vTripStatus.contains("On Going Trip") || vTripStatus.contains("Arrived") || lastTripExist)) {

                if (Utils.checkText(Ratings_From_Driver) && !Ratings_From_Driver.contains("Done") && lastTripExist) {
                    // Open rating page
                    bn.putSerializable("TRIP_DATA", map);
                    new ActUtils(mContext).startActWithData(TripRatingActivity.class, bn);
                } else {

                    bn.putSerializable("TRIP_DATA", map);
                    bn.putBoolean("isnotification", isnotification);
                    new ActUtils(mContext).startActWithData(LiveTaskListActivity.class, bn);
                }

            } else {

                String eStatus = generalFun.getJsonValueStr("eStatus", userProfileJsonObj);

                if (eStatus.equalsIgnoreCase("suspend")) {
                    new ActUtils(mContext).startAct(SuspendedDriver_Activity.class);
                } else {
                    if (generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj) != null && generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj).equalsIgnoreCase("Yes")) {
                        new ActUtils(mContext).startActWithData(MainActivity_22.class, bn);
                    } else {
                        new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                    }

                }
            }

        } else if (vTripStatus != null && !vTripStatus.equals("NONE") && !vTripStatus.equals("Cancelled") && (vTripStatus.trim().equals("Active") || vTripStatus.contains("On Going Trip") || vTripStatus.contains("Arrived") || lastTripExist)) {

            HashMap<String, String> map = setMapData();
            JSONObject last_trip_data = generalFun.getJsonObject("TripDetails", userProfileJsonObj);

            if (generalFun.getJsonValueStr("eType", last_trip_data).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                if (generalFun.getJsonValueStr("IS_OPEN_SIGN_VERIFY", last_trip_data).equalsIgnoreCase("Yes") && generalFun.getJsonValueStr("IS_OPEN_FOR_SENDER", last_trip_data).equalsIgnoreCase("Yes")) {
                    bn.putSerializable("TRIP_DATA", map);
                    map.put("vTripStatus", "Arrived");
                    bn.putSerializable("TripId", generalFun.getJsonValueStr("iTripId", last_trip_data));
                    bn.putString("CheckFor", "Sender");
                    new ActUtils(mContext).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);
                    return;
                } else if (generalFun.getJsonValueStr("IS_OPEN_SIGN_VERIFY", last_trip_data).equalsIgnoreCase("Yes") && generalFun.getJsonValueStr("IS_OPEN_FOR_SENDER", last_trip_data).equalsIgnoreCase("No")) {
                    bn.putSerializable("TRIP_DATA", map);
                    map.put("vTripStatus", "EN_ROUTE");
                    bn.putSerializable("TripId", generalFun.getJsonValueStr("iTripId", last_trip_data));
                    bn.putString("CheckFor", "Receipent");
                    new ActUtils(mContext).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);
                    return;
                }
            }

            if (vTripStatus.contains("Not Active") && lastTripExist) {
                // Open rating page
                bn.putSerializable("TRIP_DATA", map);

                String ePaymentCollect = generalFun.getJsonValueStr("ePaymentCollect", last_trip_data);
                String eBookingFrom = generalFun.getJsonValueStr("eBookingFrom", last_trip_data);
                if (ePaymentCollect.equals("No")) {
                    new ActUtils(mContext).startActWithData(CollectPaymentActivity.class, bn);
                } else {
                    if (Utils.checkText(eBookingFrom) && eBookingFrom.equalsIgnoreCase("Kiosk")) {
                        if (generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj) != null && generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj).equalsIgnoreCase("Yes")) {
                            new ActUtils(mContext).startActWithData(MainActivity_22.class, bn);
                        } else {
                            new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                        }
                    } else {
                        new ActUtils(mContext).startActWithData(TripRatingActivity.class, bn);
                    }
                }

            } else {

                if (vTripStatus.contains("Arrived")) {
                    // Open active trip page
                    map.put("vTripStatus", "Arrived");
                    bn.putSerializable("TRIP_DATA", map);
                    bn.putBoolean("isnotification", isnotification);

                    new ActUtils(mContext).startActWithData(WorkingtrekActivity.class, bn);
                } else if (!vTripStatus.contains("Arrived") && vTripStatus.contains("On Going Trip")) {
                    // Open active trip page

                    if (generalFun.getJsonValueStr("eType", last_trip_data).equalsIgnoreCase("UberX") &&
                            generalFun.getJsonValueStr("eServiceEnd", last_trip_data).equalsIgnoreCase("Yes") &&
                            generalFun.getJsonValueStr("eFareGenerated", last_trip_data).equalsIgnoreCase("No")) {
                        map.put("vTripStatus", "EN_ROUTE");
                        bn.putSerializable("TRIP_DATA", map);
                        bn.putSerializable("eType", generalFun.getJsonValueStr("eType", last_trip_data));
                        bn.putBoolean("isnotification", isnotification);
                        new ActUtils(mContext).startActWithData(AdditionalChargeActivity.class, bn);
                    } else if (generalFun.getJsonValueStr("eType", last_trip_data).equalsIgnoreCase("Ride") && generalFun.getJsonValueStr("eVerifyTollCharges", last_trip_data).equalsIgnoreCase("Yes")) {
                        map.put("vTripStatus", "EN_ROUTE");
                        bn.putSerializable("TRIP_DATA", map);
                        bn.putSerializable("eType", generalFun.getJsonValueStr("eType", last_trip_data));
                        bn.putBoolean("isnotification", isnotification);
                        new ActUtils(mContext).startActWithData(AdditionalChargeActivity.class, bn);
                    } else {

                        map.put("vTripStatus", "EN_ROUTE");
                        bn.putSerializable("TRIP_DATA", map);
                        bn.putSerializable("eType", generalFun.getJsonValueStr("eType", last_trip_data));
                        bn.putBoolean("isnotification", isnotification);
                        new ActUtils(mContext).startActWithData(WorkingtrekActivity.class, bn);
                    }


                } else if (!vTripStatus.contains("Arrived") && vTripStatus.contains("Active")) {
                    // Open cubejek arrived page

                    bn.putSerializable("TRIP_DATA", map);
                    bn.putBoolean("isnotification", isnotification);
                    if(MyApp.getInstance().getCurrentAct() instanceof CabRequestedActivity act){
                        if (act.isAnotherRequestAvailable) {
                            return;
                        }
                    }

                    new ActUtils(mContext).startActWithData(DriverArrivedActivity.class, bn);
                }
            }

        } else {

            String eStatus = generalFun.getJsonValueStr("eStatus", userProfileJsonObj);

            if (eStatus.equalsIgnoreCase("suspend")) {
                new ActUtils(mContext).startAct(SuspendedDriver_Activity.class);
            } else {
                if (generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj) != null && generalFun.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_22", userProfileJsonObj).equalsIgnoreCase("Yes")) {
                    new ActUtils(mContext).startActWithData(MainActivity_22.class, bn);
                } else {
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }

            }

        }

        try {
            ActivityCompat.finishAffinity(MyApp.getInstance().getCurrentAct());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    private boolean trackKing(Bundle bn) {
        JSONObject trackingTripDetails = generalFun.getJsonObject("TrackingTripDetails", userProfileJsonObj);
        if (trackingTripDetails == null) {
            return false;
        }
        String TripStatus = generalFun.getJsonValueStr("TripStatus", trackingTripDetails);

        HashMap<String, String> map = new HashMap<>();
        Iterator<String> keysItr = trackingTripDetails.keys();
        while (keysItr.hasNext()) {
            String key = keysItr.next();
            String value = generalFun.getJsonValueStr(key, trackingTripDetails);
            map.put(key, value);
            if (key.equalsIgnoreCase("orgPhone")) {
                map.put("PPhone", value);
            }
        }
        map.put("REQUEST_TYPE", generalFun.getJsonValueStr("eType", trackingTripDetails));
        map.put("eType", generalFun.getJsonValueStr("eType", trackingTripDetails));
        map.put("eBookingFrom", "");

        switch (TripStatus) {
            case "Active", "Onboarding", "OnGoingTrip" -> {
                bn.putSerializable("TRIP_DATA", map);
                bn.putBoolean("isnotification", isnotification);
                new ActUtils(mContext).startActWithData(DriverArrivedActivity.class, bn);
                return true;
            }
        }
        return false;
    }

    private boolean handleBidding(Bundle bn) {
        String vTaskStatus = generalFun.getJsonValueStr("vTaskStatus", userProfileJsonObj);
        JSONObject last_task_data = generalFun.getJsonObject("TaskDetails", userProfileJsonObj);
        HashMap<String, String> map = setBiddingMapData(last_task_data);
        switch (vTaskStatus) {
            case "Active" -> {
                bn.putSerializable("TRIP_DATA", map);
                bn.putBoolean("isnotification", isnotification);
                new ActUtils(mContext).startActWithData(DriverArrivedActivity.class, bn);
                return true;
            }
            case "Arrived" -> {
                map.put("vTripStatus", "Arrived");
                bn.putSerializable("TRIP_DATA", map);
                bn.putBoolean("isnotification", isnotification);
                new ActUtils(mContext).startActWithData(WorkingtrekActivity.class, bn);
                return true;
            }
            case "Ongoing" -> {
                map.put("vTripStatus", "EN_ROUTE");
                bn.putSerializable("TRIP_DATA", map);
                bn.putSerializable("eType", generalFun.getJsonValueStr("eType", last_task_data));
                bn.putBoolean("isnotification", isnotification);
                new ActUtils(mContext).startActWithData(WorkingtrekActivity.class, bn);
                return true;
            }
            case "Finished" -> {
                if (!generalFun.getJsonValueStr("Ratings_From_Driver", last_task_data).equals("Done")) {
                    bn.putSerializable("TRIP_DATA", map);
                    bn.putBoolean("isBiddingView", true);
                    if (generalFun.getJsonValueStr("ePaymentCollect", last_task_data).equalsIgnoreCase("No")) {
                        new ActUtils(mContext).startActWithData(CollectPaymentActivity.class, bn);
                    } else {
                        new ActUtils(mContext).startActWithData(TripRatingActivity.class, bn);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    private HashMap<String, String> setBiddingMapData(JSONObject last_task_data) {
        HashMap<String, String> map = new HashMap<>();

        // DriverArrivedActivity
        map.put("eFly", "");
        map.put("eBookingFrom", "");
        map.put("eAskCodeToUser", generalFun.getJsonValueStr("eAskCodeToUser", last_task_data));
        map.put("vText", generalFun.getJsonValueStr("vText", last_task_data));

        map.put("ePoolRide", "");
        map.put("REQUEST_TYPE", generalFun.getJsonValueStr("eType", last_task_data));
        map.put("eType", generalFun.getJsonValueStr("eType", last_task_data));

        map.put("TripId", generalFun.getJsonValueStr("iBiddingPostId", last_task_data));
        map.put("iTripId", generalFun.getJsonValueStr("iBiddingPostId", last_task_data));
        map.put("vRideNo", generalFun.getJsonValueStr("vBiddingPostNo", last_task_data));

        map.put("PassengerId", generalFun.getJsonValueStr("iUserId", last_task_data));
        map.put("PName", generalFun.getJsonValueStr("vName", last_task_data));
        map.put("vLastName", generalFun.getJsonValueStr("vLastName", last_task_data));
        map.put("iGcmRegId_U", generalFun.getJsonValueStr("iGcmRegId", last_task_data));
        map.put("PPicName", generalFun.getJsonValueStr("vImgName", last_task_data));
        map.put("PRating", generalFun.getJsonValueStr("vAvgRating", last_task_data));
        map.put("PPhone", generalFun.getJsonValueStr("vPhone", last_task_data));

        map.put("sourceLatitude", generalFun.getJsonValueStr("sourceLatitude", last_task_data));
        map.put("sourceLongitude", generalFun.getJsonValueStr("sourceLongitude", last_task_data));
        map.put("tSaddress", generalFun.getJsonValueStr("tSaddress", last_task_data));

        map.put("DestLocLatitude", generalFun.getJsonValueStr("tEndLat", last_task_data));
        map.put("DestLocLongitude", generalFun.getJsonValueStr("tEndLong", last_task_data));

        // WorkingtrekActivity
        map.put("eHailTrip", "");
        map.put("eAfterUpload", "");
        map.put("eBeforeUpload", "");
        map.put("eFareType", "");

        map.put("LastOrderId", "");

        return map;
    }

    private HashMap<String, String> setMapData() {
        JSONObject last_trip_data = generalFun.getJsonObject("TripDetails", userProfileJsonObj);
        JSONObject passenger_data = generalFun.getJsonObject("PassengerDetails", userProfileJsonObj);
        HashMap<String, String> map = new HashMap<>();

        map.put("TotalSeconds", generalFun.getJsonValueStr("TotalSeconds", userProfileJsonObj));
        map.put("TimeState", generalFun.getJsonValueStr("TimeState", userProfileJsonObj));
        map.put("iTripTimeId", generalFun.getJsonValueStr("iTripTimeId", userProfileJsonObj));

        map.put("Message", "CabRequested");
        map.put("sourceLatitude", generalFun.getJsonValueStr("tStartLat", last_trip_data));
        map.put("sourceLongitude", generalFun.getJsonValueStr("tStartLong", last_trip_data));
        map.put("eBookingFrom", generalFun.getJsonValueStr("eBookingFrom", last_trip_data));

        map.put("tSaddress", generalFun.getJsonValueStr("tSaddress", last_trip_data));
        map.put("eRental", generalFun.getJsonValueStr("eRental", last_trip_data));
        map.put("ePoolRide", generalFun.getJsonValueStr("ePoolRide", last_trip_data));
        map.put("eTransit", generalFun.getJsonValueStr("eTransit", last_trip_data));

        map.put("eAskCodeToUser", generalFun.getJsonValueStr("eAskCodeToUser", last_trip_data));
        map.put("vRandomCode", generalFun.getJsonValueStr("vRandomCode", last_trip_data));
        map.put("vText", generalFun.getJsonValueStr("vText", last_trip_data));

        map.put("drivervName", generalFun.getJsonValue("vName", responseString));
        map.put("drivervLastName", generalFun.getJsonValue("vLastName", responseString));

        map.put("PassengerId", generalFun.getJsonValueStr("iUserId", last_trip_data));
        map.put("PName", generalFun.getJsonValueStr("vName", passenger_data));
        map.put("vLastName", generalFun.getJsonValueStr("vLastName", passenger_data));
        map.put("iGcmRegId_U", generalFun.getJsonValueStr("iGcmRegId", passenger_data));
        map.put("vPhone_U", generalFun.getJsonValueStr("vPhone", passenger_data));
        map.put("PPicName", generalFun.getJsonValueStr("vImgName", passenger_data));
        map.put("PFId", generalFun.getJsonValueStr("vFbId", passenger_data));
        map.put("PRating", generalFun.getJsonValueStr("vAvgRating", passenger_data));
        map.put("PPhone", generalFun.getJsonValueStr("vPhone", passenger_data));
        map.put("PPhoneC", generalFun.getJsonValueStr("vPhoneCode", passenger_data));
        map.put("PAppVersion", generalFun.getJsonValueStr("iAppVersion", passenger_data));
        map.put("eFly", generalFun.getJsonValueStr("eFly", last_trip_data));
        map.put("isVideoCall", generalFun.getJsonValueStr("isVideoCall", last_trip_data));

        /*Deliver All Fields*/
        map.put("iOrderId", generalFun.getJsonValueStr("iOrderId", last_trip_data));
        map.put("LastOrderAmount", generalFun.getJsonValueStr("LastOrderAmount", userProfileJsonObj));
        map.put("LastOrderUserName", generalFun.getJsonValueStr("LastOrderUserName", userProfileJsonObj));
        map.put("LastOrderNo", generalFun.getJsonValueStr("LastOrderNo", userProfileJsonObj));
        map.put("LastOrderId", generalFun.getJsonValueStr("LastOrderId", userProfileJsonObj));


        map.put("TripId", generalFun.getJsonValueStr("iTripId", last_trip_data));
        map.put("DestLocLatitude", generalFun.getJsonValueStr("tEndLat", last_trip_data));
        map.put("DestLocLongitude", generalFun.getJsonValueStr("tEndLong", last_trip_data));
        map.put("DestLocAddress", generalFun.getJsonValueStr("tDaddress", last_trip_data));
        map.put("REQUEST_TYPE", generalFun.getJsonValueStr("eType", last_trip_data));
        map.put("eFareType", generalFun.getJsonValueStr("eFareType", last_trip_data));
        map.put("iTripId", generalFun.getJsonValueStr("iTripId", last_trip_data));
        map.put("fVisitFee", generalFun.getJsonValueStr("fVisitFee", last_trip_data));
        map.put("eHailTrip", generalFun.getJsonValueStr("eHailTrip", last_trip_data));
        map.put("iActive", generalFun.getJsonValueStr("iActive", last_trip_data));
        map.put("eTollSkipped", generalFun.getJsonValueStr("eTollSkipped", last_trip_data));
        // map.put("vVehicleType", generalFun.getJsonValueStr("vVehicleType", last_trip_data));
        map.put("vVehicleType", generalFun.getJsonValueStr("eIconType", last_trip_data));
        map.put("eType", generalFun.getJsonValueStr("eType", last_trip_data));
        map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFun.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", last_trip_data));
        map.put("vFaceMaskVerifyImage", generalFun.getJsonValueStr("vFaceMaskVerifyImage", last_trip_data));

        map.put("eAfterUpload", generalFun.getJsonValueStr("eAfterUpload", last_trip_data));
        map.put("eBeforeUpload", generalFun.getJsonValueStr("eBeforeUpload", last_trip_data));

        /*Multi StopOver*/
        map.put("currentStopOverPoint", generalFun.getJsonValueStr("currentStopOverPoint", last_trip_data));
        map.put("totalStopOverPoint", generalFun.getJsonValueStr("totalStopOverPoint", last_trip_data));
        map.put("iStopId", generalFun.getJsonValueStr("iStopId", last_trip_data));
        /*Multi StopOver*/

        map.put("vDeliveryConfirmCode", generalFun.getJsonValueStr("vDeliveryConfirmCode", last_trip_data));
        map.put("SITE_TYPE", generalFun.getJsonValueStr("SITE_TYPE", userProfileJsonObj));

        if (ServiceModule.ServiceProviderProduct) {
            map.put("tUserComment", generalFun.getJsonValueStr("tUserComment", last_trip_data));
        }

        // Multi Delivery Data
        map.put("Running_Delivery_Txt", generalFun.getJsonValueStr("Running_Delivery_Txt", last_trip_data));
        map.put("vReceiverName", generalFun.getJsonValueStr("vReceiverName", last_trip_data));
        map.put("vReceiverMobile", generalFun.getJsonValueStr("vReceiverMobile", last_trip_data));
        map.put("iTripDeliveryLocationId", generalFun.getJsonValueStr("iTripDeliveryLocationId", last_trip_data));
        map.put("ePaymentByReceiver", generalFun.getJsonValueStr("ePaymentByReceiverForDelivery", last_trip_data));
        map.put("vRideNo", generalFun.getJsonValueStr("vRideNo", last_trip_data));
        map.put("vTripPaymentMode", generalFun.getJsonValueStr("vTripPaymentMode", last_trip_data));
        map.put("ePayWallet", generalFun.getJsonValueStr("ePayWallet", last_trip_data));
        map.put("eApproveRequestSentByDriver", generalFun.getJsonValueStr("eApproveRequestSentByDriver", last_trip_data));
        String vChargesDetailData = generalFun.getJsonValueStr("vChargesDetailData", last_trip_data);
        JSONObject vChargesDetailDataObj = generalFun.getJsonObject(vChargesDetailData);
        map.put("vChargesDetailDataAvailable", vChargesDetailDataObj != null && vChargesDetailDataObj.length() > 0 ? "Yes" : "No");
        map.put("fMaterialFee", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("fMaterialFee", vChargesDetailDataObj) : "");
        map.put("fMiscFee", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("fMiscFee", vChargesDetailDataObj) : "");
        map.put("fDriverDiscount", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("fDriverDiscount", vChargesDetailDataObj) : "");
        map.put("vConfirmationCode", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("vConfirmationCode", vChargesDetailDataObj) : "");
        map.put("fTollPrice", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("fTollPrice", vChargesDetailDataObj) : "");
        map.put("fOtherCharges", vChargesDetailDataObj != null ? generalFun.getJsonValueStr("fOtherCharges", vChargesDetailDataObj) : "");


        if (generalFun.getJsonValueStr("tUserComment", last_trip_data) != null && !generalFun.getJsonValueStr("tUserComment", last_trip_data).equalsIgnoreCase("")) {
            map.put("tUserComment", generalFun.getJsonValueStr("tUserComment", last_trip_data));
        }

        map.put("TotalFareUberX", generalFun.getJsonValueStr("TotalFareUberX", userProfileJsonObj));
        map.put("TotalFareUberXValue", generalFun.getJsonValueStr("TotalFareUberXValue", userProfileJsonObj));
        map.put("UberXFareCurrencySymbol", generalFun.getJsonValueStr("UberXFareCurrencySymbol", userProfileJsonObj));


        /*InterCity Key*/

        map.put("isInterCity", generalFun.getJsonValueStr("eIsInterCity", last_trip_data));
        map.put("isInterCityRoundTrip", generalFun.getJsonValueStr("eRoundTrip", last_trip_data));
        map.put("eInterCityTripLogStatus", generalFun.getJsonValueStr("eInterCityTripLogStatus", last_trip_data));
        map.put("eInterCityButtonBgColor", generalFun.getJsonValueStr("eInterCityButtonBgColor", last_trip_data));

        /*InterCity Key*/

        return map;

    }


    public void setGeneralData() {
        new SetGeneralData(generalFun, userProfileJsonObj);

        ArrayList<String> removeData = new ArrayList<>();
        removeData.add("userHomeLocationLatitude");
        removeData.add("userHomeLocationLongitude");
        removeData.add("userHomeLocationAddress");
        removeData.add("userWorkLocationLatitude");
        removeData.add("userWorkLocationLongitude");
        removeData.add("userWorkLocationAddress");
        generalFun.removeValue(removeData);

        HashMap<String, String> storeData = new HashMap<>();
        JSONArray userFavouriteAddressArr = generalFun.getJsonArray("UserFavouriteAddress", responseString);
        if (userFavouriteAddressArr != null && userFavouriteAddressArr.length() > 0) {

            for (int i = 0; i < userFavouriteAddressArr.length(); i++) {
                JSONObject dataItem = generalFun.getJsonObject(userFavouriteAddressArr, i);

                if (generalFun.getJsonValueStr("eType", dataItem).equalsIgnoreCase("HOME")) {
                    storeData.put("userHomeLocationLatitude", generalFun.getJsonValueStr("vLatitude", dataItem));
                    storeData.put("userHomeLocationLongitude", generalFun.getJsonValueStr("vLongitude", dataItem));
                    storeData.put("userHomeLocationAddress", generalFun.getJsonValueStr("vAddress", dataItem));
                } else if (generalFun.getJsonValueStr("eType", dataItem).equalsIgnoreCase("WORK")) {
                    storeData.put("userWorkLocationLatitude", generalFun.getJsonValueStr("vLatitude", dataItem));
                    storeData.put("userWorkLocationLongitude", generalFun.getJsonValueStr("vLongitude", dataItem));
                    storeData.put("userWorkLocationAddress", generalFun.getJsonValueStr("vAddress", dataItem));
                }
            }
        }
        generalFun.storeData(storeData);
    }
}
