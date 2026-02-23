package com.general.files;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Handler;
import android.os.Looper;
import android.os.PowerManager;
import android.view.View;

import com.act.BiddingViewDetailsActivity;
import com.act.BookingsActivity;
import com.act.CabRequestedActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.deliverAll.LiveTrackOrderDetailActivity;
import com.general.ServiceRequest;
import com.general.call.CommunicationManager;
import com.model.ChatMsgHandler;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONException;
import org.json.JSONObject;
import org.json.JSONTokener;

import java.io.File;
import java.util.concurrent.TimeUnit;

/**
 * Created by Admin on 21/03/18.
 */

public class FireTripStatusMsg {

    private final String TAGS = FireTripStatusMsg.class.getSimpleName();
    private Context mContext;
    private static String tmp_msg_chk = "";

    public FireTripStatusMsg() {
        // TODO: 13-06-2022 | Do not delete this Constructor | Socket Message not come
    }

    public FireTripStatusMsg(Context mContext, String receivedBy) {
        this.mContext = mContext;
    }

    public void fireTripMsg(String message) {

        if (!Utils.checkText(message) || tmp_msg_chk.equals(message)) {
            Logger.d(TAGS, "MsgReceived :: return");
            return;
        }
        tmp_msg_chk = message;

        Logger.e(TAGS, "MsgReceived::" + message);
        String finalMsg = message;

        if (!GeneralFunctions.isJsonObj(finalMsg)) {
            try {
                finalMsg = new JSONTokener(message).nextValue().toString();
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

            if (!GeneralFunctions.isJsonObj(finalMsg)) {
                finalMsg = finalMsg.replaceAll("(?:^\")|(?:\"$)", "");
                if (!GeneralFunctions.isJsonObj(finalMsg)) {
                    finalMsg = message.replaceAll("\\\\", "");
                    finalMsg = finalMsg.replaceAll("(?:^\")|(?:\"$)", "");
                    if (!GeneralFunctions.isJsonObj(finalMsg)) {
                        finalMsg = message.replace("\\\"", "\"").replaceAll("(?:^\")|(?:\"$)", "");
                    }
                    finalMsg = finalMsg.replace("\\\\\"", "\\\"");
                }
            }
        }

        try {
            JSONObject json = new JSONObject(finalMsg);
            if (json.has("Message") && json.getString("Message").equalsIgnoreCase("CabRequested") && json.has("MsgCode")) {
                ServiceRequest.sendEvent(mContext, json.getString("MsgCode"), "Received");
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

        if (MyApp.isAppKilled()) {
            if (mContext != null) {
                dispatchNotification(finalMsg);
            }
            return;
        }

        if (MyApp.getInstance().getCurrentAct() != null) {
            mContext = MyApp.getInstance().getCurrentAct();
        }

        if (mContext == null) {
            dispatchNotification(finalMsg);
            return;
        }

        GeneralFunctions generalFunc = MyApp.getInstance().getGeneralFun(mContext);
        JSONObject obj_msg = generalFunc.getJsonObject(finalMsg);
        String tSessionId = generalFunc.getJsonValueStr("tSessionId", obj_msg);

        if (!tSessionId.equals("") && !tSessionId.equals(generalFunc.retrieveValue(Utils.SESSION_ID_KEY))) {
            return;
        }

        if (!generalFunc.isUserLoggedIn() && !Utils.checkText(generalFunc.getMemberId())) {
            return;
        }

        if (!GeneralFunctions.isJsonObj(finalMsg)) {
            String passMessage = generalFunc.convertNumberWithRTL(message);
            LocalNotification.dispatchLocalNotification(mContext, passMessage, true);
            generalFunc.showGeneralMessage("", passMessage);
            return;
        }

        boolean isMsgExist = ServiceRequest.isTripStatusMsgExist(generalFunc, finalMsg, mContext);
        Logger.d(TAGS, "MsgReceived:: MsgExist-> " + isMsgExist);

        if (isMsgExist) {
            return;
        }

        if (msgHandling(generalFunc, obj_msg) || taxiBidHandle(generalFunc, obj_msg)) {
            return;
        }

        if (mContext instanceof Activity) {
            ((Activity) mContext).runOnUiThread(() -> continueDispatchMsg(generalFunc, obj_msg));
        } else {
            dispatchNotification(finalMsg);
        }
    }

    private void continueDispatchMsg(GeneralFunctions generalFunc, JSONObject obj_msg) {
        String messageStr = generalFunc.getJsonValueStr("Message", obj_msg);
        if (messageStr.equals("")) {

            String msgTypeStr = generalFunc.getJsonValueStr("MsgType", obj_msg);
            String vTitle = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_msg));

            if (msgTypeStr.equalsIgnoreCase("CHAT")) {

                ChatMsgHandler.performAction(obj_msg.toString());

                return;
            } else if (msgTypeStr.equalsIgnoreCase("VOIP")) {
                if (MyApp.getInstance().isMyAppInBackGround()) {
                    String Msg = generalFunc.getJsonValueStr("Msg", obj_msg);
                    if (!Msg.equalsIgnoreCase("Call Ended")) {
                        LocalNotification.dispatchLocalNotification(mContext, generalFunc.getJsonValueStr("vTitle", obj_msg), true);
                    }
                }
            } else {
                LocalNotification.dispatchLocalNotification(mContext, vTitle, true);

                generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> doOperations());
            }

        } else {
            String vTitle = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_msg));

            if (messageStr.equalsIgnoreCase("TripCancelled") || messageStr.equalsIgnoreCase("DestinationAdded") || messageStr.equalsIgnoreCase("OrderCancelByAdmin") || messageStr.equalsIgnoreCase("RewardProgramCancelled")) {
                if (messageStr.equalsIgnoreCase("TripCancelled") || messageStr.equalsIgnoreCase("OrderCancelByAdmin")) {
                    generalFunc.saveGoOnlineInfo();
                }
                generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> MyApp.getInstance().restartWithGetDataApp());

            } else if (messageStr.equalsIgnoreCase("CabRequested")) {
                if (((MyApp.getInstance().mainAct != null || MyApp.getInstance().main22Act != null) && MyApp.getInstance().driverArrivedAct == null && MyApp.getInstance().activeTripAct == null) || generalFunc.getJsonValueStr("ePoolRequest", obj_msg).equalsIgnoreCase("Yes") || generalFunc.getJsonValueStr("eAcceptTripRequest", obj_msg).equalsIgnoreCase("Yes")) {
                    dispatchCabRequest(generalFunc, obj_msg.toString());
                } else if (((MyApp.getInstance().mainAct == null || MyApp.getInstance().main22Act == null) && MyApp.getInstance().driverArrivedAct == null && MyApp.getInstance().activeTripAct == null) || generalFunc.getJsonValueStr("ePoolRequest", obj_msg).equalsIgnoreCase("Yes") || generalFunc.getJsonValueStr("eAcceptTripRequest", obj_msg).equalsIgnoreCase("Yes")) {
                    dispatchCabRequest(generalFunc, obj_msg.toString());
                }

            } else if (messageStr.equalsIgnoreCase("OrderItemsReviewed") || messageStr.equalsIgnoreCase("OrderPaymentByUser")) {
                if (MyApp.getInstance().getCurrentAct() instanceof LiveTrackOrderDetailActivity activity) {
                    activity.pubnubmsg(vTitle);
                    LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                }
            } else if (messageStr.equalsIgnoreCase("GoPayVerifyAmount")) {
                generalFunc.showGeneralMessage("", vTitle);
                LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
            } else if (messageStr.equalsIgnoreCase("rideLaterBookingRequest") || messageStr.equalsIgnoreCase("NewScheduleBooking") || messageStr.equalsIgnoreCase("ActivateServiceProvider")) {

                if (messageStr.equalsIgnoreCase("ActivateServiceProvider")) {
                    LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                    if (mContext instanceof MainActivity_22 mainActivity_22) {
                        mainActivity_22.showAccountActivated(obj_msg);
                    }
                    return;
                }
                MyUtils.setPendingBookingsCount(generalFunc.getJsonValueStr("PendingRideRequestCount", obj_msg));
                if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22) {
                    MainActivity_22 mainActivity_22 = (MainActivity_22) mContext;
                    mainActivity_22.showBookingNotification(obj_msg);
                }
            } else {
                LocalNotification.dispatchLocalNotification(mContext, vTitle, false);
            }
        }
    }

    private void doOperations() {
//        MyApp.getInstance().restartWithGetDataApp()
    }

    private void dispatchCabRequest(GeneralFunctions generalFunc, String message) {
        if (generalFunc.containsKey(Utils.DRIVER_REQ_COMPLETED_MSG_CODE_KEY + (generalFunc.getJsonValue("MsgCode", message)))) {
            return;
        }
        if (MyApp.getInstance().ispoolRequest) {
            return;
        }

        generalFunc.storeData(Utils.DRIVER_ACTIVE_REQ_MSG_KEY, message);


        String notification_msg = generalFunc.retrieveLangLBl("", "LBL_TRIP_USER_WAITING");
        if (generalFunc.getJsonValue("REQUEST_TYPE", message) != null) {
            if (generalFunc.getJsonValue("REQUEST_TYPE", message).equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                notification_msg = generalFunc.retrieveLangLBl("", "LBL_TRIP_USER_WAITING");
            } else if (generalFunc.getJsonValue("REQUEST_TYPE", message).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                notification_msg = generalFunc.retrieveLangLBl("", "LBL_USER_WAITING");
            } else {
                notification_msg = generalFunc.retrieveLangLBl("", "LBL_DELIVERY_SENDER_WAITING");
            }
        }

        if (MyApp.isAppKilled() && mContext != null) {

            generalFunc.removeValue(ServiceRequest.getServiceRequestKey(generalFunc, message, mContext));
            tmp_msg_chk = "";

            Intent launchInt = Utils.getLauncherIntent(mContext, true);
            launchInt.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            this.mContext.startActivity(launchInt);

        } else if (!MyApp.isAppKilled()) {

            userPowerManagerWakeup(MyApp.getInstance().getCurrentAct());

            if (!(MyApp.getInstance().getCurrentAct() instanceof CabRequestedActivity)) {
                LocalNotification.dispatchLocalNotification(mContext, notification_msg, true);
            }
            Intent cabReqAct = new Intent(MyApp.getInstance().getApplicationContext(), CabRequestedActivity.class);
            cabReqAct.putExtra("Message", message);
            MyApp.getInstance().getCurrentAct().startActivity(cabReqAct);

        } else {
            LocalNotification.dispatchLocalNotification(mContext, notification_msg, true);
        }
    }

    private static void userPowerManagerWakeup(Context mContext) {
        try {
            PowerManager pm = (PowerManager) mContext.getSystemService(Context.POWER_SERVICE);
            PowerManager.WakeLock wakelock = pm.newWakeLock(PowerManager.FULL_WAKE_LOCK | PowerManager.ACQUIRE_CAUSES_WAKEUP, ":Request");
            wakelock.acquire(TimeUnit.SECONDS.toMillis(4));
        } catch (Exception e) {

        }
    }

    private void dispatchNotification(String message) {

        Context mLocContext = this.mContext;

        if (mLocContext == null && MyApp.getInstance() != null && MyApp.getInstance().getCurrentAct() == null) {
            mLocContext = MyApp.getInstance().getApplicationContext();
        }

        if (mLocContext != null) {
            GeneralFunctions generalFunc = MyApp.getInstance().getGeneralFun(mLocContext);

            if (!GeneralFunctions.isJsonObj(message)) {
                LocalNotification.dispatchLocalNotification(mLocContext, message, true);
                return;
            }

            JSONObject obj_msg = generalFunc.getJsonObject(message);

            if (msgHandling(generalFunc, obj_msg)) {
                return;
            }

            String message_str = generalFunc.getJsonValueStr("Message", obj_msg);
            if (message_str.equals("")) {
                String msgType_str = generalFunc.getJsonValueStr("MsgType", obj_msg);

                switch (msgType_str) {
                    case "CHAT":
                        generalFunc.storeData(ChatMsgHandler.OPEN_CHAT, obj_msg.toString());
                        String tMessage = generalFunc.getJsonValueStr("tMessage", obj_msg);
                        String tMsgNotification = generalFunc.getJsonValueStr("tMsgNotification", obj_msg);
                        LocalNotification.dispatchLocalNotification(mLocContext, tMsgNotification.trim().equalsIgnoreCase("") ? tMessage : tMsgNotification, false);
                        break;
                    case "VOIP":
                        //TODO Do not remove this code
                        String tMessage1 = generalFunc.getJsonValueStr("tMessage", obj_msg);
                        String Msg = generalFunc.getJsonValueStr("Msg", obj_msg);
                        if (!Msg.equalsIgnoreCase("Call Ended") || Utils.checkText(tMessage1)) {
                            LocalNotification.dispatchLocalNotification(mLocContext, generalFunc.getJsonValueStr("vTitle", obj_msg), true);
                            //
                            downloadRTCData(mLocContext, generalFunc, obj_msg);
                        }
                        break;
                    default:
                        LocalNotification.dispatchLocalNotification(mLocContext, generalFunc.getJsonValueStr("vTitle", obj_msg), false);
                }
            } else {
                String title_msg = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_msg));
                switch (message_str) {
                    case "TripCancelled", "OrderCancelByAdmin" -> {
                        generalFunc.saveGoOnlineInfo();
                        LocalNotification.dispatchLocalNotification(mLocContext, title_msg, false);
                    }
                    case "DestinationAdded" -> LocalNotification.dispatchLocalNotification(mLocContext, title_msg, false);
                    case "CabRequested" -> dispatchCabRequest(generalFunc, message);
                }
            }
        }
    }

    private void downloadRTCData(Context context, GeneralFunctions generalFunc, JSONObject obj_msg) {
        JSONObject tMessageObj = generalFunc.getJsonObject("tMessage", obj_msg);
        String RTC_DATA = generalFunc.getJsonValueStr("RTC_DATA", tMessageObj);
        generalFunc.storeData("RTC_DATA_offer", "");
        if (RTC_DATA.startsWith("http")) {
            ApiHandler.downloadFile(context, RTC_DATA, new ServerTask.FileDataResponse() {
                @Override
                public void onDownload(File file) {
                    try {
                        tMessageObj.put("RTC_DATA", MyApp.getInstance().readFromFile(file));
                        generalFunc.storeData("RTC_DATA_offer", tMessageObj.toString());
                        new Handler(Looper.getMainLooper()).postDelayed(() -> {
                            //
                            generalFunc.storeData("RTC_DATA_offer", "");
                        }, 30 * 1000);
                    } catch (JSONException e) {
                        throw new RuntimeException(e);
                    }
                }

                @Override
                public void onDownloadError(String s) {

                }
            }).execute();
        } else {
            generalFunc.storeData("RTC_DATA_offer", RTC_DATA);
            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                //
                generalFunc.storeData("RTC_DATA_offer", "");
            }, 30 * 1000);
        }
    }

    private boolean msgHandling(GeneralFunctions generalFunc, JSONObject obj_msg) {
        String MsgType = generalFunc.getJsonValueStr("MsgType", obj_msg);
        if (Utils.checkText(MsgType)) {
            String vTitle = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_msg));

            switch (MsgType) {
                case "TwilioVideocall" -> {
                    CommunicationManager.getInstance().incomingCommunicate(mContext, generalFunc, null, obj_msg);
                    return true;
                }
                case "BiddingTaskCancelled", "BiddingTaskDeclined", "BiddingTaskAcceptedOther" -> {
                    LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                    generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> {
                        if (btn_id == 1) {
                            if (MyApp.getInstance().driverArrivedAct != null || MyApp.getInstance().activeTripAct != null) {
                                if (MsgType.equalsIgnoreCase("BiddingTaskCancelled")) {
                                    generalFunc.restartApp();
                                }
                                return;
                            }
                            if (MyApp.getInstance().getCurrentAct() instanceof BiddingViewDetailsActivity activity) {
                                activity.finish();
                            }
                        }
                    });
                    return true;
                }
                case "BiddingTaskReoffered", "BiddingTaskAccepted" -> {
                    LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                    generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> {
                        if (btn_id == 1) {
                            if (MyApp.getInstance().driverArrivedAct != null || MyApp.getInstance().activeTripAct != null) {
                                return;
                            }
                            if (MyApp.getInstance().getCurrentAct() instanceof BiddingViewDetailsActivity activity) {
                                new Handler(Looper.getMainLooper()).post(activity::getBiddingViewDetailsList);
                            } else {
                                Intent bidActInt = new Intent(MyApp.getInstance().getApplicationContext(), BiddingViewDetailsActivity.class);
                                bidActInt.putExtra("iBiddingPostId", generalFunc.getJsonValueStr("iBiddingPostId", obj_msg));
                                bidActInt.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                                MyApp.getInstance().getApplicationContext().startActivity(bidActInt);
                            }
                        }
                    });
                    return true;
                }
                case "BiddingTaskReceived" -> {
                    LocalNotification.dispatchLocalNotification(mContext, vTitle, true);
                    generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), btn_id -> {
                        if (btn_id == 1) {
                            if (MyApp.getInstance().driverArrivedAct != null || MyApp.getInstance().activeTripAct != null) {
                                return;
                            }
                            if (MyApp.getInstance().getCurrentAct() instanceof MainActivity mainActivity) {
                                mainActivity.checkBiddingView(2);
                            } else if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22 mainActivity_22) {
                                mainActivity_22.checkBiddingView(2);
                            } else {
                                if (MyApp.getInstance().getCurrentAct() instanceof BookingsActivity bookingsActivity) {
                                    bookingsActivity.setFrag(2);
                                } else {
                                    Intent booksActInt = new Intent(MyApp.getInstance().getApplicationContext(), BookingsActivity.class);
                                    if (obj_msg != null) {
                                        booksActInt.putExtras(generalFunc.createChatBundle(obj_msg));
                                    }
                                    booksActInt.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

                                    booksActInt.putExtra("viewPos", 2);
                                    MyApp.getInstance().getApplicationContext().startActivity(booksActInt);
                                }
                            }
                        }
                    });
                    return true;
                }
            }
        }
        return false;
    }

    private boolean taxiBidHandle(GeneralFunctions generalFunc, JSONObject obj_msg) {
        String messageStr = generalFunc.getJsonValueStr("Message", obj_msg);
        if (messageStr.equalsIgnoreCase("TripCancelled") || messageStr.equalsIgnoreCase("OrderCancelByAdmin")) {
            return false;
        }
        if (MyApp.getInstance().getCurrentAct() instanceof CabRequestedActivity activity && !activity.isFinishing()) {
            String taxiContRequest = generalFunc.getJsonValue("TAXI_BID_CONTINUOUS_REQUEST", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            if (taxiContRequest.equalsIgnoreCase("No")) {
                if (activity.afterAcceptTaxiBidOfferArea != null && activity.afterAcceptTaxiBidOfferArea.getVisibility() == View.VISIBLE) {
                    activity.pubNubMsgArrived(obj_msg.toString());
                    return true;
                }
            }
        }
        return false;
    }


}