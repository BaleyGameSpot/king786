package com.model;

import com.service.handler.AppService;
import com.utils.Utils;

public class SocketEvents {
    public static String MEMBER_EVENT = "";
    public static String SERVICE_REQUEST = "";
    public static String SERVICE_AVAILABILITY = "";
    public static String SERVICE_LOCATIONS = "";
    public static String PROVIDER_STATUS = "";
    public static String CHAT_SERVICE = "";
    public static String VOIP_SERVICE = "";

    public static void buildEvents(String memberId) {
        MEMBER_EVENT = Utils.userType.toUpperCase() + "_" + memberId;
        SERVICE_REQUEST = "SERVICE_REQUEST_" + memberId;
        SERVICE_AVAILABILITY = "SERVICE_AVAILABILITY_" + memberId;
        SERVICE_LOCATIONS = "SERVICE_LOCATIONS_" + memberId;
        PROVIDER_STATUS = "PROVIDER_STATUS_" + memberId;
        CHAT_SERVICE = "CHAT_SERVICE_" + memberId;
        VOIP_SERVICE = "VOIP_SERVICE_" + memberId;

        AppService.listOfEvents.clear();
        AppService.listOfEvents.add(SocketEvents.MEMBER_EVENT);
        AppService.listOfEvents.add(SocketEvents.SERVICE_REQUEST);
        AppService.listOfEvents.add(SocketEvents.SERVICE_AVAILABILITY);
        AppService.listOfEvents.add(SocketEvents.SERVICE_LOCATIONS);
        AppService.listOfEvents.add(SocketEvents.PROVIDER_STATUS);
        AppService.listOfEvents.add(SocketEvents.CHAT_SERVICE);
        AppService.listOfEvents.add(SocketEvents.VOIP_SERVICE);
    }
}