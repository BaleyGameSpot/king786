package com.general.files;

import com.general.call.VOIPMsgHandler;
import com.model.ChatMsgHandler;
import com.model.SocketEvents;

import org.json.JSONObject;

public class SocketMessageReceiver {
    private static SocketMessageReceiver instance;
    GeneralFunctions generalFunc;

    public SocketMessageReceiver() {
        generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
    }

    public static SocketMessageReceiver getInstance() {
        if (instance == null) {
            instance = new SocketMessageReceiver();
        }
        return instance;
    }

    public void handleMsg(String eventName, String message_str) {
        JSONObject obj_data = generalFunc.getJsonObject(message_str);

        if (obj_data == null || MyApp.isAppKilled()) {
            (new FireTripStatusMsg(MyApp.getInstance().getApplicationContext(), "Socket")).fireTripMsg(obj_data.toString());
            return;
        }
        if (eventName.equalsIgnoreCase(SocketEvents.SERVICE_REQUEST)) {
            (new FireTripStatusMsg(MyApp.getInstance().getApplicationContext(), "Socket")).fireTripMsg(obj_data.toString());
        }
        if (eventName.equalsIgnoreCase(SocketEvents.CHAT_SERVICE)) {
            ChatMsgHandler.performAction(message_str);
        }
        if (eventName.equalsIgnoreCase(SocketEvents.VOIP_SERVICE)) {
            VOIPMsgHandler.performAction(generalFunc, message_str);
        }
    }
}
