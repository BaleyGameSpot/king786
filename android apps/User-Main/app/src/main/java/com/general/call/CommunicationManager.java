package com.general.call;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.telecom.Call;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.act.ChatActivity;
import com.dialogs.CommunicationCallTypeDialog;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class CommunicationManager {

    private static CommunicationManager instance;
    public final CommunicationCallTypeDialog mCommunicationCallTypeDialog;

    public static String IS_INCOMING_VIEW = "IS_INCOMING_VIEW";
    public static String MY_DATA = "MY_DATA";

    public static TYPE COMM_TYPE = TYPE.NONE;
    public static MEDIA MEDIA_TYPE = MEDIA.DEFAULT;
    private GeneralFunctions mGeneralFunc;
    private String mName, mImage;

    public enum TYPE {
        PHONE_CALL,
        CHAT,
        VIDEO_CALL,
        VOIP_CALL,
        BOTH_CALL,
        NONE,
        OTHER
    }

    public enum MEDIA {
        SINCH,
        TWILIO,
        LOCAL,
        DEFAULT
    }

    public static CommunicationManager getInstance() {
        if (instance == null) {
            instance = new CommunicationManager();
        }
        return instance;
    }

    public CommunicationManager() {
        this.mCommunicationCallTypeDialog = new CommunicationCallTypeDialog();
        this.mCommunicationCallTypeDialog.setListener(this::continueCallAction);
    }

    public void initiateService(@Nullable GeneralFunctions generalFunc, @NonNull String jsonValue) {
        // some time generalFunc is null CALLING_METHOD issue
        if (generalFunc == null) { // NOSONAR
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        this.mGeneralFunc = generalFunc;
        setConfiguration(generalFunc, jsonValue);

        mName = generalFunc.getJsonValue(Utils.app_type.equalsIgnoreCase("Company") ? "vCompany" : "vName", jsonValue);
        mImage = generalFunc.getJsonValue(Utils.app_type.equalsIgnoreCase("Passenger") ? "vImgName" : "vImage", jsonValue);
    }

    private void setConfiguration(@Nullable GeneralFunctions generalFunc, @NonNull String jsonValue) {
        // some time generalFunc is null CALLING_METHOD issue
        if (generalFunc == null) { // NOSONAR
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        this.mGeneralFunc = generalFunc;

        // Set Audio/Video Calling Method for Apps
        final String audioCallingMethod = generalFunc.getJsonValue("AUDIO_CALLING_METHOD", jsonValue);
        switch (audioCallingMethod.toUpperCase()) {
            case "SINCH":
                MEDIA_TYPE = MEDIA.SINCH;
                break;
            case "TWILIO":
                MEDIA_TYPE = MEDIA.TWILIO;
                break;
            case "LOCAL":
                MEDIA_TYPE = MEDIA.LOCAL;
                break;
        }

        // Calling Method
        final String callingMethod = generalFunc.getJsonValue("RIDE_DRIVER_CALLING_METHOD", jsonValue);
        switch (callingMethod.toUpperCase()) {
            case "VOIP":
                COMM_TYPE = TYPE.VOIP_CALL;
                break;
            case "VIDEOCALL":
                COMM_TYPE = TYPE.VIDEO_CALL;
                break;
            case "VOIP-VIDEOCALL":
                COMM_TYPE = TYPE.BOTH_CALL;
                break;
            case "NORMAL":
                COMM_TYPE = TYPE.NONE;
                MEDIA_TYPE = MEDIA.DEFAULT;
                break;
        }
    }

    public void communicate(Context mContext, MediaDataProvider dataProvider, TYPE type) {
        if (type == TYPE.CHAT) {
            openChat(mContext, dataProvider);
        } else {
            toCall(mContext, dataProvider);
        }
    }

    private void toCall(Context mContext, MediaDataProvider dataProvider) {
        if (mGeneralFunc == null) {
            mGeneralFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        setConfiguration(mGeneralFunc, mGeneralFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        switch (dataProvider.media) {
            case SINCH:
            case TWILIO:
            case LOCAL:
                if (COMM_TYPE == TYPE.BOTH_CALL) {
                    mCommunicationCallTypeDialog.showPreferenceDialog(mContext, dataProvider);
                } else {
                    mCommunicationCallTypeDialog.btnClick = true;
                    mCommunicationCallTypeDialog.checkPermissions(mContext, COMM_TYPE, dataProvider);
                }
                break;
            case DEFAULT:
                DefaultCommunicationHandler.getInstance().executeAction(mContext, TYPE.PHONE_CALL, dataProvider);
                break;
        }
    }

    public void communicateOnlyVideo(Context mContext, MediaDataProvider dataProvider) {
        final String audioCallingMethod = mGeneralFunc.getJsonValue("AUDIO_CALLING_METHOD", mGeneralFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        switch (audioCallingMethod.toUpperCase()) {
            case "SINCH":
                MEDIA_TYPE = MEDIA.SINCH;
                break;
            case "TWILIO":
                MEDIA_TYPE = MEDIA.TWILIO;
                break;
            case "LOCAL":
                MEDIA_TYPE = MEDIA.LOCAL;
                break;
        }
        dataProvider.media = MEDIA_TYPE;
        mCommunicationCallTypeDialog.btnClick = true;
        mCommunicationCallTypeDialog.checkPermissions(mContext, TYPE.VIDEO_CALL, dataProvider);
    }

    public void communicatePhoneOrVideo(Context mContext, MediaDataProvider dataProvider, TYPE type) {
        final String audioCallingMethod = mGeneralFunc.getJsonValue("AUDIO_CALLING_METHOD", mGeneralFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        switch (audioCallingMethod.toUpperCase()) {
            case "SINCH":
                MEDIA_TYPE = MEDIA.SINCH;
                break;
            case "TWILIO":
                MEDIA_TYPE = MEDIA.TWILIO;
                break;
            case "LOCAL":
                MEDIA_TYPE = MEDIA.LOCAL;
                break;
        }
        dataProvider.media = MEDIA_TYPE;
        if (type == TYPE.BOTH_CALL) {
            mCommunicationCallTypeDialog.showPreferenceDialog(mContext, dataProvider);
        } else {
            mCommunicationCallTypeDialog.btnClick = true;
            mCommunicationCallTypeDialog.checkPermissions(mContext, type, dataProvider);
        }
    }

    private void continueCallAction(Context mContext, TYPE communication_type, MediaDataProvider dataProvider) {
        if (communication_type == TYPE.VIDEO_CALL) {
            dataProvider.isVideoCall = true;
        }
        dataProvider.fromMemberId = mGeneralFunc.getMemberId();
        dataProvider.fromMemberType = Utils.userType;
        dataProvider.fromMemberName = mName;
        dataProvider.fromMemberImage = mImage;

        switch (dataProvider.media) {
            case SINCH:
                break;
            case TWILIO:
                break;
            case LOCAL:
                break;
            case DEFAULT:
                DefaultCommunicationHandler.getInstance().executeAction(mContext, TYPE.PHONE_CALL, dataProvider);
                return;
        }
        openCallScreen(mContext, dataProvider);
        sendNotification(mContext, dataProvider, "No");
    }

    private void sendNotification(Context mContext, MediaDataProvider dataProvider, String isCallEnded) {
        if (MEDIA_TYPE == MEDIA.LOCAL) {
            return;
        }
        if (mGeneralFunc == null) {
            mGeneralFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SendTripMessageNotification");
        parameters.put("UserType", Utils.userType);
        parameters.put("eSystem", dataProvider.toMemberType.equalsIgnoreCase(Utils.CALLTOSTORE) ? "DeliverAll" : "");

        parameters.put("iFromMemberId", mGeneralFunc.getMemberId());
        parameters.put("iToMemberId", dataProvider.toMemberType + "_" + dataProvider.toMemberId);

        parameters.put("isForVoip", "Yes");
        parameters.put("isCallEnded", isCallEnded);

        ApiHandler.execute(mContext, parameters, responseString -> {
        });

    }

    private void openCallScreen(Context mContext, MediaDataProvider dataProvider) {
        Intent callScreen = new Intent(mContext, VOIPActivity.class);
        callScreen.putExtra(MY_DATA, dataProvider);
        callScreen.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_EXCLUDE_FROM_RECENTS);
        mContext.startActivity(callScreen);
    }

    private void openChat(Context mContext, MediaDataProvider dataProvider) {
        if (dataProvider.media == MEDIA.DEFAULT && !Utils.checkText(dataProvider.iTripId) && !Utils.checkText(dataProvider.iOrderId)) {
            DefaultCommunicationHandler.getInstance().executeAction(mContext, TYPE.CHAT, dataProvider);
        } else {
            Bundle bnChat = new Bundle();
            bnChat.putString("vBookingNo", dataProvider.vBookingNo);

            if (dataProvider.isForPickupPhotoRequest != null) {
                bnChat.putString("isForPickupPhotoRequest", dataProvider.isForPickupPhotoRequest);
            }

            if (dataProvider.isBid) {
                bnChat.putString("iBiddingPostId", dataProvider.iTripId);
                bnChat.putString("iToMemberId", dataProvider.toMemberId);
                bnChat.putString("iToMemberType", Utils.app_type.equalsIgnoreCase("Passenger") ? Utils.CALLTODRIVER : Utils.CALLTOPASSENGER);
            } else {
                bnChat.putString("iTripId", dataProvider.iTripId);
                bnChat.putString("iOrderId", dataProvider.iOrderId);
                bnChat.putString("iToMemberType", dataProvider.toMemberType);
            }

            new ActUtils(mContext).startActWithData(ChatActivity.class, bnChat);
        }
    }

    public void incomingCommunicate(Context mContext, GeneralFunctions generalFunc, Call mCall, JSONObject obj_msg) {
        MediaDataProvider mDataProvider = null;
        final String audioCallingMethod = mGeneralFunc.getJsonValue("AUDIO_CALLING_METHOD", mGeneralFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        switch (audioCallingMethod.toUpperCase()) {
            case "SINCH":
                MEDIA_TYPE = MEDIA.SINCH;
                break;
            case "TWILIO":
                MEDIA_TYPE = MEDIA.TWILIO;
                break;
            case "LOCAL":
                MEDIA_TYPE = MEDIA.LOCAL;
                break;
        }
        switch (MEDIA_TYPE) {
            case SINCH:
                break;
            case TWILIO:
                break;
            case LOCAL:
                mDataProvider = new MediaDataProvider.Builder()
                        .setFromMemberId(generalFunc.getJsonValueStr("iToMemberId", obj_msg))
                        .setFromMemberType(generalFunc.getJsonValueStr("iToMemberType", obj_msg))
                        .setFromMemberName(generalFunc.getJsonValueStr("iToMemberName", obj_msg))
                        .setFromMemberImage(generalFunc.getJsonValueStr("iToMemberImage", obj_msg))

                        .setToMemberId(generalFunc.getJsonValueStr("iFromMemberId", obj_msg))
                        .setToMemberType(generalFunc.getJsonValueStr("iFromMemberType", obj_msg))
                        .setToMemberName(generalFunc.getJsonValueStr("iFromMemberName", obj_msg))
                        .setToMemberImage(generalFunc.getJsonValueStr("iFromMemberImage", obj_msg))

                        .setVideoCall(generalFunc.getJsonValueStr("isVideoCall", obj_msg).equalsIgnoreCase("Yes"))
                        .build();
        }
        if (MyApp.getInstance().getCurrentAct() != null) {
            if (MyApp.getInstance().getCurrentAct() instanceof VOIPActivity) {
                return;
            }
        }
        Intent callScreen = new Intent(mContext, VOIPActivity.class);
        callScreen.putExtra(CommunicationManager.IS_INCOMING_VIEW, true);
        callScreen.putExtra(CommunicationManager.MY_DATA, mDataProvider);
        callScreen.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_EXCLUDE_FROM_RECENTS);
        mContext.startActivity(callScreen);
    }
}