package com.general;

import android.content.Context;

import androidx.annotation.NonNull;

import com.general.files.GeneralFunctions;
import com.general.files.LocalNotification;
import com.general.files.MyApp;
import com.google.gson.Gson;
import com.model.SocketEvents;
import com.service.handler.AppService;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class ServiceRequest {
    public static String DRIVER_REQUEST_STATUS = "DRIVER_REQUEST_STATUS";
    private static GeneralFunctions generalFunc;

    private static void initializeFunc(Context mContext) {
        if (generalFunc != null) {
            return;
        }

        if (!MyApp.isAppKilled()) {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        } else if (mContext != null) {
            generalFunc = new GeneralFunctions(mContext);
        }
    }

    public static void forceRestObj() {
        generalFunc = null;
        initializeFunc(null);
    }

    public static void sendEvent(Context mContext, @NonNull String msgCode, @NonNull String status) {
        initializeFunc(mContext);
        sendEvent(msgCode, status);
    }

    public static void sendEvent(@NonNull String msgCode, @NonNull String status) {
        initializeFunc(null);

        HashMap<String, String> dataMap = new HashMap<>();
        dataMap.put("iDriverId", generalFunc.getMemberId());
        dataMap.put("vMsgCode", msgCode);
        dataMap.put("vCurrentTime", "" + System.currentTimeMillis());
        dataMap.put("tTimeZone", "" + generalFunc.getTimezone());
        dataMap.put("eStatus", status);

        sendEvent(0, dataMap, null);
    }

    public static void sendEvent(int repeatCount, HashMap<String, String> dataMap, DataCallbackListener listener) {
        if (MyApp.isAppKilled() || repeatCount > 3 || generalFunc == null) {
            storeEventData(dataMap);
            return;
        }

        AppService.getInstance().sendMessage(SocketEvents.SERVICE_REQUEST, (new Gson()).toJson(dataMap), 10000, (name, errorObj, dataObj) -> {
            if (errorObj != null) {
                int rCount = repeatCount + 1;
                sendEvent(rCount, dataMap, listener);
            } else if (dataObj != null) {
                deleteEventData(dataMap);
                if (listener != null) {
                    listener.onSuccess();
                }
            }
        });
    }

    private static void storeEventData(HashMap<String, String> dataMap) {
        if (generalFunc == null) {
            return;
        }

        try {
            String key = DRIVER_REQUEST_STATUS + "_" + dataMap.get("vMsgCode") + "_" + dataMap.get("eStatus");
            generalFunc.storeData(key, (new Gson()).toJson(dataMap));
        } catch (Exception ignored) {

        }
    }

    private static void deleteEventData(HashMap<String, String> dataMap) {
        if (generalFunc == null) {
            return;
        }

        try {
            String key = DRIVER_REQUEST_STATUS + "_" + dataMap.get("vMsgCode") + "_" + dataMap.get("eStatus");
            generalFunc.removeValue(key);
        } catch (Exception ignored) {

        }
    }

    public interface DataCallbackListener {
        void onSuccess();
    }

    public static boolean isTripStatusMsgExist(GeneralFunctions generalFunc, String msg, Context mContext) {

        JSONObject obj_tmp = generalFunc.getJsonObject(msg);

        if (obj_tmp != null) {
            String message = generalFunc.getJsonValueStr("Message", obj_tmp);

            if (!message.equals("")) {
                String iTripId = "";
                String iBiddingPostId = "";

                if (generalFunc.getJsonValue("eSystem", msg).equalsIgnoreCase(Utils.eSystem_Type)) {
                    if (!message.equalsIgnoreCase("CabRequested")) {
                        iTripId = Utils.checkText(generalFunc.getJsonValueStr("iOrderId", obj_tmp)) ? generalFunc.getJsonValueStr("iOrderId", obj_tmp) : generalFunc.getJsonValueStr("iTripId", obj_tmp);
                    }
                } else {
                    iTripId = generalFunc.getJsonValueStr("iTripId", obj_tmp);
                }
                if (generalFunc.getJsonValue("iBiddingPostId", obj_tmp) != null && !generalFunc.getJsonValue("iBiddingPostId", obj_tmp).equals("")) {
                    iBiddingPostId = generalFunc.getJsonValueStr("iBiddingPostId", obj_tmp);
                }
                // String iTripId = getJsonValueStr("iTripId", obj_tmp);
                String iTripDeliveryLocationId = generalFunc.getJsonValueStr("iTripDeliveryLocationId", obj_tmp);

                if (!iTripId.equals("")) {
                    String vTitle = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_tmp));
                    String time = generalFunc.getJsonValueStr("time", obj_tmp);
                    String key = "";
                    if (generalFunc.getJsonValue("eType", msg).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                        key = Utils.TRIP_REQ_CODE_PREFIX_KEY + iTripId + "_" + iTripDeliveryLocationId + "_" + message;
                    } else {
                        key = Utils.TRIP_REQ_CODE_PREFIX_KEY + iTripId + "_" + message;
                    }
                    if (message.equals("DestinationAdded")) {
                        long newMsgTime = GeneralFunctions.parseLongValue(0, time);

                        String destKeyValueStr = GeneralFunctions.retrieveValue(key, mContext);
                        if (!destKeyValueStr.equals("")) {

                            long destKeyValue = GeneralFunctions.parseLongValue(0, destKeyValueStr);
                            if (newMsgTime > destKeyValue) {
                                generalFunc.removeValue(key);
                            } else {
                                return true;
                            }
                        }
                    }

                    String data = generalFunc.retrieveValue(key);

                    if (data.equals("")) {
                        if (!message.equalsIgnoreCase("CabRequested")) {
                            LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                        }
                        if (time.equals("")) {
                            generalFunc.storeData(key, "" + System.currentTimeMillis());
                        } else {
                            generalFunc.storeData(key, "" + time);
                        }
                        return false;
                    } else {
                        return true;
                    }
                } else if (!message.equals("") && (message.equalsIgnoreCase("CabRequested") || message.equalsIgnoreCase("rideLaterBookingRequest"))) {
                    String msgCode = generalFunc.getJsonValueStr("MsgCode", obj_tmp);
                    String key = Utils.DRIVER_REQ_CODE_PREFIX_KEY + msgCode;

                    String data = generalFunc.retrieveValue(key);

                    if (data.equals("")) {
                        generalFunc.storeData(key, "" + System.currentTimeMillis());
                        return false;
                    } else if (message.equalsIgnoreCase("rideLaterBookingRequest")) {
                        return true;
                    }
                } else if (!iBiddingPostId.equalsIgnoreCase("")) {
                    String time = generalFunc.getJsonValueStr("time", obj_tmp);
                    String key = "";
                    key = Utils.TRIP_REQ_CODE_PREFIX_KEY + iBiddingPostId + "_" + message + "" + time;
                    String data = generalFunc.retrieveValue(key);
                    if (data.equals("")) {
                        if (time.equals("")) {
                            generalFunc.storeData(key, "" + System.currentTimeMillis());
                        } else {
                            generalFunc.storeData(key, "" + time);
                        }
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    String msgType = generalFunc.getJsonValueStr("MsgType", obj_tmp);
                    if (msgType != null) {
                        String key, data, tRandomValue = "";
                        switch (msgType) {
                            case "TwilioVideocall":
                                tRandomValue = generalFunc.getJsonValueStr("tRandomCode", obj_tmp);
                                break;
                        }
                        if (Utils.checkText(tRandomValue)) {
                            key = Utils.TRIP_REQ_CODE_PREFIX_KEY + tRandomValue + "_" + msgType;
                            data = generalFunc.retrieveValue(key);
                            generalFunc.storeData(key, "" + System.currentTimeMillis());
                            return !data.equals("");
                        }
                    }
                }
            } else {
                String msgType = generalFunc.getJsonValueStr("MsgType", obj_tmp);
                if (Utils.checkText(msgType)) {
                    String key, data, tRandomValue = "";
                    switch (msgType) {
                        case "Notification" ->
                                tRandomValue = generalFunc.getJsonValueStr("tRandomCode", obj_tmp);
                        case "VOIP" ->
                                tRandomValue = generalFunc.getJsonValueStr("MsgCode", obj_tmp);
                    }
                    if (Utils.checkText(tRandomValue)) {
                        key = Utils.TRIP_REQ_CODE_PREFIX_KEY + tRandomValue + "_" + msgType;
                        data = generalFunc.retrieveValue(key);
                        generalFunc.storeData(key, "" + System.currentTimeMillis());
                        return !data.equals("");
                    }
                } else {
                    String tMessage = generalFunc.getJsonValueStr("tMessage", obj_tmp);
                    if (Utils.checkText(tMessage)) {
                        String iMsgCode = generalFunc.getJsonValueStr("iMsgCode", obj_tmp);
                        String key = tMessage + "_" + iMsgCode;
                        if (generalFunc.retrieveValue(key).equals("")) {
                            generalFunc.storeData(key, String.valueOf(System.currentTimeMillis()));
                            return false;
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static String getServiceRequestKey(GeneralFunctions generalFunc, String msg, Context mContext) {

        String requestKey = "";

        JSONObject obj_tmp = generalFunc.getJsonObject(msg);

        if (obj_tmp != null) {
            String message = generalFunc.getJsonValueStr("Message", obj_tmp);

            if (!message.equals("")) {
                String iTripId = "";
                String iBiddingPostId = "";

                if (generalFunc.getJsonValue("eSystem", msg).equalsIgnoreCase(Utils.eSystem_Type)) {
                    if (!message.equalsIgnoreCase("CabRequested")) {
                        iTripId = Utils.checkText(generalFunc.getJsonValueStr("iOrderId", obj_tmp)) ? generalFunc.getJsonValueStr("iOrderId", obj_tmp) : generalFunc.getJsonValueStr("iTripId", obj_tmp);
                    }
                } else {
                    iTripId = generalFunc.getJsonValueStr("iTripId", obj_tmp);
                }
                if (generalFunc.getJsonValue("iBiddingPostId", obj_tmp) != null && !generalFunc.getJsonValue("iBiddingPostId", obj_tmp).equals("")) {
                    iBiddingPostId = generalFunc.getJsonValueStr("iBiddingPostId", obj_tmp);
                }
                // String iTripId = getJsonValueStr("iTripId", obj_tmp);
                String iTripDeliveryLocationId = generalFunc.getJsonValueStr("iTripDeliveryLocationId", obj_tmp);

                if (!iTripId.equals("")) {

                    if (generalFunc.getJsonValue("eType", msg).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                        requestKey = Utils.TRIP_REQ_CODE_PREFIX_KEY + iTripId + "_" + iTripDeliveryLocationId + "_" + message;
                    } else {
                        requestKey = Utils.TRIP_REQ_CODE_PREFIX_KEY + iTripId + "_" + message;
                    }

                } else if (!message.equals("") && message.equalsIgnoreCase("CabRequested")) {
                    String msgCode = generalFunc.getJsonValueStr("MsgCode", obj_tmp);
                    requestKey = Utils.DRIVER_REQ_CODE_PREFIX_KEY + msgCode;

                } else if (!iBiddingPostId.equalsIgnoreCase("")) {
                    String time = generalFunc.getJsonValueStr("time", obj_tmp);
                    requestKey = Utils.TRIP_REQ_CODE_PREFIX_KEY + iBiddingPostId + "_" + message + "" + time;

                } else {
                    String msgType = generalFunc.getJsonValueStr("MsgType", obj_tmp);
                    if (msgType != null) {
                        String tRandomValue = "";
                        switch (msgType) {
                            case "TwilioVideocall":
                                tRandomValue = generalFunc.getJsonValueStr("tRandomCode", obj_tmp);
                                break;
                        }
                        if (Utils.checkText(tRandomValue)) {
                            requestKey = Utils.TRIP_REQ_CODE_PREFIX_KEY + tRandomValue + "_" + msgType;
                        }
                    }
                }
            }
        }
        return requestKey;
    }
}
