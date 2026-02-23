package com.model;

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
    public static boolean isCubeXApp = false;
    public static boolean isDeliveryKingApp = false;

    //Manage AppType
    public static boolean RideProduct = false;
    public static boolean DeliveryProduct = false;
    public static boolean ServiceProviderProduct = false;
    public static boolean RideDeliveryUbexProduct = false;
    public static boolean RideDeliveryProduct = false;
    public static boolean DeliverAllProduct = false;
    private static boolean OnlyMedicalServiceProduct = false;
    private static boolean OnlyBuySellRentProduct = false;
    public static boolean rideShare = false;
    public static boolean EnableRideSharingPro = false;
    public static boolean OnlyRideSharingPro = false;
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
        ServiceModule.isCubeXApp = generalFun.getJsonValueStr("IS_CUBEX_APP", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.isDeliveryKingApp = generalFun.getJsonValueStr("IS_DELIVERYKING_APP", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.IsMedicalAll = generalFun.getJsonValueStr("ONLY_MEDICAL_SERVICE", USER_PROFILE_JSON).equalsIgnoreCase("Yes");

        //Manage AppType
        if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride")) {
            ServiceModule.RideProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Delivery") || generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Deliver")) {
            ServiceModule.DeliveryProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("UberX")) {
            ServiceModule.ServiceProviderProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride-Delivery-UberX")) {
            ServiceModule.RideDeliveryUbexProduct = true;
        } else if (generalFun.getJsonValueStr("APP_TYPE", USER_PROFILE_JSON).equalsIgnoreCase("Ride-Delivery")) {
            ServiceModule.RideDeliveryProduct = true;
        }

        ServiceModule.DeliverAllProduct = generalFun.getJsonValueStr(Utils.ONLYDELIVERALL_KEY, USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.OnlyMedicalServiceProduct = generalFun.getJsonValueStr("ONLY_MEDICAL_SERVICE", USER_PROFILE_JSON).equalsIgnoreCase("Yes");

        ServiceModule.rideShare = generalFun.getJsonValueStr("RIDE_SHARE_ENABLED", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.EnableRideSharingPro = generalFun.getJsonValueStr("ENABLE_RIDE_SHARING_PRO", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.OnlyRideSharingPro = EnableRideSharingPro && generalFun.getJsonValueStr("ONLY_ENABLE_RIDE_SHARING_PRO", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
        ServiceModule.OnlyBuySellRentProduct = generalFun.getJsonValueStr("ONLY_ENABLE_BUY_SELL_RENT_PRO", USER_PROFILE_JSON).equalsIgnoreCase("Yes");
    }

    public static boolean isDeliverAllOnly() {
        if (Taxi || Delivery || ServiceProvider || ServiceBid || VideoCall || rideShare || OnlyBuySellRentProduct) {
            return false;
        }
        return true;
    }

    public static boolean isRideOnly() {
        if (RideProduct || (Taxi && !Delivery && !ServiceProvider && !ServiceBid && !VideoCall && !Genie && !DeliverAll && !Runner)) {
            return true;
        }
        return false;
    }

    public static boolean isServiceProviderOnly() {
        if (ServiceProviderProduct || (ServiceProvider && !Taxi && !Delivery && !VideoCall && !Genie && !DeliverAll && !Runner && !ServiceBid)) {
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

    public static boolean isAnyDeliverAllOptionEnable() {
        if (DeliverAll || Runner || Genie || DeliverAllProduct) {
            return true;
        }
        return false;
    }

    public static boolean isOnlyMedicalServiceEnable() {
        return OnlyMedicalServiceProduct;
    }

    public static boolean isOnlyBuySellRentEnable() {
        return OnlyBuySellRentProduct;
    }
}