package com.model;

import com.act.MainActivity_22;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.utils.Utils;

import org.json.JSONObject;

public class ServiceModule {

    //services module
    public static boolean Taxi = false;
    public static boolean Delivery = false;
    public static boolean ServiceProvider = false;
    public static boolean DeliverAll = false;
    public static boolean Genie = false;
    public static boolean Runner = false;
    public static boolean ServiceBid = false;
    public static boolean VideoCall = false;


    //Manage AppType
    public static boolean RideProduct = false;
    public static boolean DeliveryProduct = false;
    public static boolean ServiceProviderProduct = false;
    public static boolean RideDeliveryUbexProduct = false;
    public static boolean RideDeliveryProduct = false;
    public static boolean DeliverAllProduct = false;
    public static boolean IsTrackingProvider = false;
    public static boolean IsMedicalAll = false;

    public static void configure() {
        GeneralFunctions generalFun = MyApp.getInstance().getAppLevelGeneralFunc();
        JSONObject USER_PROFILE_JSON = generalFun.getJsonObject(generalFun.retrieveValue(Utils.USER_PROFILE_JSON));

        ServiceModule.Taxi = generalFun.getJsonValueStr("RIDE_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.Delivery = generalFun.getJsonValueStr("DELIVERY_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.ServiceProvider = generalFun.getJsonValueStr("UFX_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.DeliverAll = generalFun.getJsonValueStr("DELIVERALL_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.Genie = generalFun.getJsonValueStr("GENIE_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.Runner = generalFun.getJsonValueStr("RUNNER_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.ServiceBid = generalFun.getJsonValueStr("BIDDING_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.VideoCall = generalFun.getJsonValueStr("VC_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.IsMedicalAll = generalFun.getJsonValueStr("ONLY_MEDICAL_SERVICE", USER_PROFILE_JSON).equalsIgnoreCase("Yes");

        ServiceModule.IsTrackingProvider = generalFun.getJsonValueStr("IS_TRACKING_PROVIDER", USER_PROFILE_JSON).equalsIgnoreCase("Yes");

        //Manage AppType
        if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride")) {
            ServiceModule.RideProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Delivery") ||
                generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Deliver")) {
            ServiceModule.DeliveryProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("UberX")) {
            ServiceModule.ServiceProviderProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride-Delivery-UberX")) {
            ServiceModule.RideDeliveryUbexProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride-Delivery")) {
            ServiceModule.RideDeliveryProduct = true;
        }

        ServiceModule.DeliverAllProduct = generalFun.getJsonValueStr(Utils.ONLYDELIVERALL_KEY, USER_PROFILE_JSON).equalsIgnoreCase("Yes");
    }


    public static boolean isRideOnly() {
        if (RideProduct || (Taxi && !Delivery && !ServiceProvider && !ServiceBid && !VideoCall && !Genie && !DeliverAll && !Runner)) {
            return true;
        }
        return false;
    }

    public static boolean isDeliveronly() {
        if (DeliveryProduct || (Delivery && !Taxi && !ServiceProvider && !ServiceBid && !VideoCall && !Genie && !DeliverAll && !Runner)) {
            return true;
        }
        return false;
    }

    public static boolean isRideDeliveronly() {
        if (RideDeliveryProduct || (Delivery && Taxi && !ServiceProvider && !ServiceBid && !VideoCall && !Genie && !DeliverAll && !Runner)) {
            return true;
        }
        return false;
    }

    public static boolean isServiceProviderOnly() {
        if (ServiceProviderProduct || (ServiceProvider && !Taxi && !Delivery && !VideoCall && !Genie && !DeliverAll && !Runner && !ServiceBid)) {
            //TODO : For PlusXMassage Subservice UI Misbehave
            if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22) {
                return false;
            }
            return true;
        }
        return false;
    }

    public static boolean isServiceProviderAvailable() {
        return ServiceProviderProduct || ServiceProvider || VideoCall || ServiceBid;
    }

    public static boolean isDeliverAllOnly() {
        if (DeliverAllProduct) {
            return true;
        } else if (Taxi || Delivery || ServiceProvider || ServiceBid || VideoCall) {
            return false;
        }
        return true;
    }

    public static boolean isAnyDeliverAllOptionEnable() {
        if (DeliverAll || Runner || Genie || DeliverAllProduct) {
            return true;
        }
        return false;
    }

    public static boolean isRideView() {
        boolean isRideViewVisible = false;
        if (Taxi || Delivery || Genie || Runner || DeliverAll) {
            isRideViewVisible = true;
        }
        return isRideViewVisible;
    }

    public static boolean bookingView() {
        if (DeliverAllProduct) {
            return false;
        } else if (Taxi || Delivery || ServiceProvider || IsTrackingProvider) {
            return true;
        }
        return false;
    }

    public static boolean orderView() {
        if (isAnyDeliverAllOptionEnable() && !IsTrackingProvider) {
            return true;
        }
        return false;
    }

    public static boolean bidView() {
        if (DeliverAllProduct) {
            return false;
        } else if (ServiceBid && !IsTrackingProvider) {
            return true;
        }
        return false;
    }
}