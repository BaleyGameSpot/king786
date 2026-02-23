package com.general.files;

import com.general.call.VOIPMsgHandler;
import com.model.ChatMsgHandler;
import com.model.SocketEvents;

import org.json.JSONObject;

public class SocketMessageReceiver {
    private static SocketMessageReceiver instance;
    private final GeneralFunctions generalFunc;

    public static SocketMessageReceiver getInstance() {
        if (instance == null) {
            instance = new SocketMessageReceiver();
        }
        return instance;
    }

    public SocketMessageReceiver() {
        this.generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
    }

    public void handleMsg(String eventName, String message_str) {
        JSONObject obj_data = generalFunc.getJsonObject(message_str);
        if (obj_data == null || MyApp.isAppKilled()) {
            return;
        }

        if (eventName.equalsIgnoreCase(SocketEvents.SERVICE_REQUEST_STATUS)) {
            new FireTripStatusMsg(MyApp.getInstance().getApplicationContext()).fireTripMsg(message_str);
        }
        if (eventName.equalsIgnoreCase(SocketEvents.TRACKING_SERVICE)) {
            new FireTripStatusMsg(MyApp.getInstance().getApplicationContext()).fireTripMsg(message_str);
        }
        if (eventName.equalsIgnoreCase(SocketEvents.CHAT_SERVICE)) {
            ChatMsgHandler.performAction(message_str);
        }
        if (eventName.equalsIgnoreCase(SocketEvents.VOIP_SERVICE)) {
            VOIPMsgHandler.performAction(generalFunc, message_str);
        }
    }
}