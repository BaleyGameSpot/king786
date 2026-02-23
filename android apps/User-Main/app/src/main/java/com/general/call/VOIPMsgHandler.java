package com.general.call;

import com.general.files.GeneralFunctions;
import com.general.files.LocalNotification;
import com.general.files.MyApp;
import com.utils.Utils;

import org.json.JSONObject;

public class VOIPMsgHandler {

    public static void performAction(GeneralFunctions generalFunc, String message_str) {
        JSONObject obj_data = generalFunc.getJsonObject(message_str);
        if (obj_data == null) {
            return;
        }

        //
        String rtcData = generalFunc.getJsonValue("RTC_DATA", message_str);
        if (MyApp.getInstance().getCurrentAct() != null && MyApp.getInstance().getCurrentAct() instanceof VOIPActivity activity) {

            if (generalFunc.getJsonValue("type", rtcData).equalsIgnoreCase("offer")) {
                activity.voipViewModel.busyCall(generalFunc.getJsonObject("MEMBER_DATA", obj_data));
            } else if (generalFunc.getJsonValue("type", rtcData).equalsIgnoreCase("answer")) {
                activity.voipViewModel.inComingAnswer(rtcData);
            } else if (generalFunc.getJsonValue("type", rtcData).equalsIgnoreCase("candidate")) {
                activity.voipViewModel.addIceCandidate(rtcData);
            } else if (generalFunc.getJsonValue("type", rtcData).equalsIgnoreCase("cameraSwitched")) {
                activity.voipViewModel.cameraSwitched(rtcData);
            } else if (generalFunc.getJsonValueStr("RTC_DATA", obj_data).equalsIgnoreCase("CALL_END")) {
                activity.finish();
            } else if (generalFunc.getJsonValueStr("RTC_DATA", obj_data).equalsIgnoreCase("ON_ANOTHER_CALL")) {
                activity.voipViewModel.onAnotherCall(generalFunc.getJsonObject("MEMBER_DATA", obj_data));
            }
        } else {

            //------------------------------------------------------------------------------------
            if (generalFunc.getJsonValue("type", rtcData).equalsIgnoreCase("offer")) {
                String vTitle = generalFunc.getJsonValueStr("vTitle", obj_data);
                String iFromMemberType = generalFunc.getJsonValueStr("iFromMemberType", obj_data);

                if (!iFromMemberType.equalsIgnoreCase(Utils.app_type)) {
                    LocalNotification.dispatchLocalNotification(MyApp.getInstance().getApplicationContext(), vTitle, true);
                }

                generalFunc.storeData("rtcOfferDescription", rtcData);
                CommunicationManager.getInstance().incomingCommunicate(MyApp.getInstance().getApplicationContext(), generalFunc, null, generalFunc.getJsonObject("MEMBER_DATA", obj_data));
            }
        }
    }
}