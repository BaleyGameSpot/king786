package com.model;

import android.content.Intent;
import android.os.Bundle;

import com.act.ChatActivity;
import com.general.files.GeneralFunctions;
import com.general.files.LocalNotification;
import com.general.files.MyApp;
import com.utils.Utils;

import org.json.JSONObject;

public class ChatMsgHandler {

    public static final String OPEN_CHAT = "OPEN_CHAT";
    public static final String PICKUP_REPLY_VIEW = "PICKUP_REPLY_VIEW";

    static GeneralFunctions generalFunc;

    public static void performAction(String message_str) {

        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }

        JSONObject obj_data = generalFunc.getJsonObject(message_str);
        if (obj_data == null) {
            return;
        }

        if (MyApp.getInstance().getCurrentAct() instanceof ChatActivity chatAct) {
            chatAct.handleIncomingMessages(obj_data);

        } else {
            if (generalFunc.getJsonValueStr("isForPickupPhotoRequest", obj_data).equalsIgnoreCase("Yes")) {
                generalFunc.storeData(ChatMsgHandler.PICKUP_REPLY_VIEW, "Yes");
            }
            if (generalFunc.retrieveValue(ChatMsgHandler.PICKUP_REPLY_VIEW).equalsIgnoreCase("Yes")) {
                if (!Utils.checkText(generalFunc.getJsonValueStr("vFile", obj_data))) {
                    if (MyApp.getInstance().mainAct != null && MyApp.getInstance().mainAct.driverDetailFrag != null) {
                        MyApp.getInstance().mainAct.driverDetailFrag.dialogPickupPhotoReplyView(obj_data);
                    }
                }
            } else {
                openChatAct(obj_data);
            }
        }

        String tMessage = generalFunc.getJsonValueStr("tMessage", obj_data);
        String tMsgNotification = generalFunc.getJsonValueStr("tMsgNotification", obj_data);
        String iFromMemberType = generalFunc.getJsonValueStr("iFromMemberType", obj_data);

        if (!iFromMemberType.equalsIgnoreCase(Utils.app_type)) {
            LocalNotification.dispatchLocalNotification(MyApp.getInstance().getApplicationContext(), Utils.checkText(tMsgNotification) ? tMsgNotification : tMessage, true);
        }
    }

    public static void openChatAct(JSONObject obj_data) {

        generalFunc.removeValue(ChatMsgHandler.OPEN_CHAT);

        String iBiddingPostId = generalFunc.getJsonValueStr("iBiddingPostId", obj_data);
        String iTripId = generalFunc.getJsonValueStr("iTripId", obj_data);
        String iOrderId = generalFunc.getJsonValueStr("iOrderId", obj_data);

        String vBookingNo = generalFunc.getJsonValueStr("vBookingNo", obj_data);
        String vRideNo = generalFunc.getJsonValueStr("vRideNo", obj_data);

        String iToMemberType = generalFunc.getJsonValueStr("iToMemberType", obj_data);
        String iToMemberId = generalFunc.getJsonValueStr("iToMemberId", obj_data);

        String isOpenMediaDialog = generalFunc.getJsonValueStr("isOpenMediaDialog", obj_data);

        Intent chatActInt = new Intent(MyApp.getInstance().getApplicationContext(), ChatActivity.class);

        Bundle bn = new Bundle();
        bn.putString("iBiddingPostId", iBiddingPostId);
        bn.putString("iOrderId", iOrderId);

        bn.putString("iToMemberType", iToMemberType);
        bn.putString("iToMemberId", iToMemberId);

        bn.putString("iTripId", iTripId);
        bn.putString("vBookingNo", Utils.checkText(vBookingNo) ? vBookingNo : vRideNo);

        bn.putString("isOpenMediaDialog", isOpenMediaDialog);

        chatActInt.putExtras(bn);

        MyApp.getInstance().getCurrentAct().startActivity(chatActInt);
    }
}