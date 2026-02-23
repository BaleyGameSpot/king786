package com.model;

import com.service.handler.AppService;
import com.utils.Utils;

public class SocketEvents {
    public static String MEMBER_EVENT = "";
    public static String SERVICE_REQUEST_STATUS = "";
    public static String TRACKING_SERVICE = "";
    public static String CHAT_SERVICE = "";
    public static String VOIP_SERVICE = "";

    public static void buildEvents(String memberId) {
        MEMBER_EVENT = Utils.userType.toUpperCase() + "_" + memberId;
        SERVICE_REQUEST_STATUS = "SERVICE_REQUEST_STATUS_" + memberId;
        TRACKING_SERVICE = "TRACKING_SERVICE_DATA_" + memberId;
        CHAT_SERVICE = "CHAT_SERVICE_" + memberId;
        VOIP_SERVICE = "VOIP_SERVICE_" + memberId;

        AppService.listOfEvents.clear();
        AppService.listOfEvents.add(SocketEvents.MEMBER_EVENT);
        AppService.listOfEvents.add(SocketEvents.SERVICE_REQUEST_STATUS);
        AppService.listOfEvents.add(SocketEvents.TRACKING_SERVICE);
        AppService.listOfEvents.add(SocketEvents.CHAT_SERVICE);
        AppService.listOfEvents.add(SocketEvents.VOIP_SERVICE);
    }
}