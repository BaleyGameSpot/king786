package com.general.files;

import android.annotation.SuppressLint;
import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.provider.Settings;
import android.text.SpannableString;
import android.view.View;
import android.widget.RemoteViews;

import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;

import com.act.BookingActivity;
import com.act.HistoryDetailActivity;
import com.act.OnGoingTripDetailsActivity;
import com.act.deliverAll.OrderDetailsActivity;
import com.fontanalyzer.SystemFont;
import com.buddyverse.main.BuildConfig;
import com.buddyverse.main.R;
import com.text.style.StyleSpan;
import com.utils.DateTimeUtils;
import com.utils.IntentAction;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

/**
 * Created by Admin on 20/03/18.
 */

public class LocalNotification {
    @SuppressLint("StaticFieldLeak")
    static Context mContext;

    private static final String CHANNEL_ID = BuildConfig.APPLICATION_ID + "";
    private static NotificationManager mNotificationManager = null;
    String MsgType = "";

    public static void dispatchLocalNotification(Context context, String message, boolean onlyInBackground) {
        mContext = context;

        if (MyApp.getInstance().getCurrentAct() == null && mContext == null) {
            return;
        }

        continueDispatchNotification(message, onlyInBackground);
    }

    private static void continueDispatchNotification(String message, boolean onlyInBackground) {
        Intent intent;
        if (Utils.getPreviousIntent(mContext) != null) {
            intent = Utils.getPreviousIntent(mContext);
        } else {
            intent = mContext
                    .getPackageManager()
                    .getLaunchIntentForPackage(mContext.getPackageName());

            intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                    Intent.FLAG_ACTIVITY_NEW_TASK |
                    Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
        }

        PendingIntent contentIntent = PendingIntent.getActivity(mContext, 0, intent, IntentAction.getPendingIntentFlag());
        GeneralFunctions generalFunctions = MyApp.getInstance().getGeneralFun(mContext);
        String userProfileJson = generalFunctions.retrieveValue(Utils.USER_PROFILE_JSON);

        Uri soundUri = Settings.System.DEFAULT_NOTIFICATION_URI;
        if (Utils.checkText(generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson))) {
            if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_1.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_1);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_2.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_2);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_3.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_3);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_4.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_4);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_5.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_5);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_6.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_6);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_7.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_7);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_8.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_8);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_9.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_9);
            } else if (generalFunctions.getJsonValue("USER_NOTIFICATION", userProfileJson).equalsIgnoreCase("notification_10.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_10);
            }

        }

        if (mNotificationManager != null) {
            mNotificationManager.cancelAll();
            mNotificationManager = null;
        }

        // Receive Notifications in >26 version devices
        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            // mBuilder.setChannelId(BuildConfig.APPLICATION_ID);
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    mContext.getString(R.string.app_name),
                    NotificationManager.IMPORTANCE_HIGH
            );
            /*AudioAttributes audioAttributes = new AudioAttributes.Builder()
                    .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                    .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                    .build();*/
            // channel.setSound(soundUri, audioAttributes);

            if (mNotificationManager != null) {
                mNotificationManager.createNotificationChannel(channel);
            }
        }

        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(mContext, CHANNEL_ID)
                .setSmallIcon(R.drawable.ic_notification_logo)
                .setLargeIcon(BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_launcher))
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setContentTitle(mContext.getString(R.string.app_name))
                .setContentText(message)
                .setAutoCancel(true)
                .setContentIntent(contentIntent)
                //  .setSound(soundUri)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(message))
                .setPriority(NotificationCompat.PRIORITY_HIGH);


        if (mNotificationManager != null && onlyInBackground && MyApp.getInstance().isMyAppInBackGround()) {
//            mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
            mNotificationManager.notify(Utils.NOTIFICATION_ID, mBuilder.build());
            playNotificationSound(soundUri);
        } else if (mNotificationManager != null && !onlyInBackground) {
//            mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
            mNotificationManager.notify(Utils.NOTIFICATION_ID, mBuilder.build());
            playNotificationSound(soundUri);
        }
    }

    private RemoteViews NormalView;
    private RemoteViews expandView;

    GeneralFunctions generalFunc;
    JSONObject jsonObject;
    static String iOrderId;
    static String iTripId;
    static String eType;
    static String iServiceId;
    static String iCabBookingId = "";
    static String eFinished = "";

    public void customNotification(Context context, String Data) {
        Logger.d("customNotification", "::called::" + Data);
        mContext = context;
        generalFunc = new GeneralFunctions(mContext);

        jsonObject = generalFunc.getJsonObject(Data);
        iOrderId = generalFunc.getJsonValueStr("iOrderId", jsonObject);
        iTripId = generalFunc.getJsonValueStr("iTripId", jsonObject);
        eType = generalFunc.getJsonValueStr("eType", jsonObject);
        iServiceId = generalFunc.getJsonValueStr("iServiceId", jsonObject);

        if (MsgType.equalsIgnoreCase("")) {
            MsgType = generalFunc.getJsonValueStr("MsgType", jsonObject);
        } else if (MsgType.equalsIgnoreCase(generalFunc.getJsonValueStr("MsgType", jsonObject))) {
            return;
        } else {
            MsgType = generalFunc.getJsonValueStr("MsgType", jsonObject);

        }

        initNotification();

    }

    private void initNotification() {

        JSONArray obj_arr = generalFunc.getJsonArray("CustomMessage", jsonObject);
        if (obj_arr != null && obj_arr.length() > 0) {
            JSONObject obj_temp_ = generalFunc.getJsonObject(obj_arr, 0);
            eFinished = generalFunc.getJsonValueStr("eFinished", obj_temp_);
            iCabBookingId = generalFunc.getJsonValueStr("iCabBookingId", obj_temp_);
        }

        if (mNotificationManager != null) {
            mNotificationManager.cancelAll();
            mNotificationManager = null;
        }
        DateTimeUtils.Is24HourTime = generalFunc.getJsonValueStr("ENABLE_24_HOUR_FORMAT", generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON))).equalsIgnoreCase("Yes");


        // Receive Notifications in >26 version devices
        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
        Intent intent = null;

        if (Utils.getPreviousIntent(mContext) != null) {
            intent = Utils.getPreviousIntent(mContext);
        } else {
            intent = mContext
                    .getPackageManager()
                    .getLaunchIntentForPackage(mContext.getPackageName());

            intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                    Intent.FLAG_ACTIVITY_NEW_TASK |
                    Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
        }

        PendingIntent pendingIntent = PendingIntent.getActivity(mContext, (int) System.currentTimeMillis(), intent, IntentAction.getPendingIntentFlag());

        Intent intentrd = null;
        if (eType.equalsIgnoreCase("Ride") || eType.equalsIgnoreCase("Deliver")) {

            if (Utils.getPreviousIntent(mContext) != null) {
                intentrd = Utils.getPreviousIntent(mContext);
            } else {
                intentrd = mContext
                        .getPackageManager()
                        .getLaunchIntentForPackage(mContext.getPackageName());

                intentrd.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
            }


        } else if (eType.equalsIgnoreCase("UberX") || eType.equalsIgnoreCase("Multi-Delivery")) {
            if (eFinished != null && eFinished.equalsIgnoreCase("Yes")) {
                intentrd = new Intent(MyApp.getInstance().getApplicationContext(), HistoryDetailActivity.class);
                intentrd.putExtra("isRestart", true);
                intentrd.putExtra("eType", eType);
                intentrd.putExtra("iTripId", iTripId);
                intentrd.putExtra("iCabBookingId", iCabBookingId);
                intentrd.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);

            } else {
                intentrd = new Intent(MyApp.getInstance().getApplicationContext(), OnGoingTripDetailsActivity.class);
                intentrd.putExtra("isRestart", true);
                intentrd.putExtra("eType", eType);
                intentrd.putExtra("iTripId", iTripId);
                intentrd.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
            }

        } else {


            intentrd = new Intent(MyApp.getInstance().getApplicationContext(), OrderDetailsActivity.class);
            intentrd.putExtra("isRestart", true);
            intentrd.putExtra("fromNoti", true);
            intentrd.putExtra("isCustmNoti", true);
            intentrd.putExtra("iOrderId", iOrderId);
            intentrd.putExtra("iServiceId", iServiceId);

            intentrd.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                    Intent.FLAG_ACTIVITY_NEW_TASK |
                    Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
        }
        PendingIntent viewOrderIntent = PendingIntent.getActivity(mContext, (int) System.currentTimeMillis(), intentrd, IntentAction.getPendingIntentFlag());

        Intent intent1 = new Intent(MyApp.getInstance().getApplicationContext(), BookingActivity.class);
        intent1.putExtra("isRestart", true);
        intent1.putExtra("fromNoti", true);
        intent1.putExtra("isCustmNoti", true);
        intent1.putExtra("iOrderId", iOrderId);
        intent1.putExtra("iServiceId", iServiceId);
        Logger.d("iServiceId_ ", "iServiceId_trackOrderIntent >> " + iServiceId);
        intent1.putExtra("isOrder", true);

        intent1.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                Intent.FLAG_ACTIVITY_NEW_TASK |
                Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
        PendingIntent trackOrderIntent = PendingIntent.getActivity(mContext, (int) System.currentTimeMillis(), intent1, IntentAction.getPendingIntentFlag());

        NotificationCompat.Builder builder = new NotificationCompat.Builder(mContext, CHANNEL_ID);
        builder.setSmallIcon(R.drawable.ic_notification_logo)
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setLargeIcon(BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_launcher))
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setTicker(mContext.getString(R.string.app_name))
                .setWhen(System.currentTimeMillis())
                .setAutoCancel(true)
                .setContentIntent(pendingIntent);


        NormalView = new RemoteViews(mContext.getPackageName(), R.layout.notification_view);


        expandView = new RemoteViews(mContext.getPackageName(), generalFunc.isRTLmode() ? R.layout.notification_big_view_rtl : R.layout.notification_big_view);

        builder.setPriority(Notification.PRIORITY_MAX);
        expandView.setOnClickPendingIntent(R.id.viewdetailsTxt, viewOrderIntent);
        expandView.setOnClickPendingIntent(R.id.trackorderTxt, trackOrderIntent);
        expandView.setTextViewText(R.id.titleTxt, generalFunc.getJsonValueStr("CustomSubTitle", jsonObject));
        NormalView.setTextViewText(R.id.titleTxt, generalFunc.getJsonValueStr("CustomSubTitle", jsonObject));
        NormalView.setTextViewText(R.id.statusTxt, generalFunc.getJsonValueStr("CustomTitle", jsonObject));
        expandView.setTextViewText(R.id.statusTxt, generalFunc.getJsonValueStr("CustomTitle", jsonObject));


        if (obj_arr != null) {
            int j = obj_arr.length();
            for (int i = 0; i < obj_arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(obj_arr, i);
                if (generalFunc.isRTLmode()) {
                    j--;
                    obj_temp = generalFunc.getJsonObject(obj_arr, j);

                }
                RemoteViews newView = new RemoteViews(mContext.getPackageName(), R.layout.item_custom_notification);
                //newView.setTextViewText(R.id.carTypeTitle, generalFunc.getJsonValueStr("vStatus", obj_temp));
                String status = generalFunc.getJsonValueStr("vStatus", obj_temp);
                if (i == 0) {
                    eType = generalFunc.getJsonValueStr("eType", obj_temp);
                    eFinished = generalFunc.getJsonValueStr("eFinished", obj_temp);
                    iCabBookingId = generalFunc.getJsonValueStr("iCabBookingId", obj_temp);
                }
                // if (generalFunc.getJsonValueStr("iStatusCode", obj_temp).equalsIgnoreCase(generalFunc.getJsonValueStr("OrderCurrentStatusCode", obj_temp))) {
                if (generalFunc.getJsonValueStr("isCurrentStatus", obj_temp).equalsIgnoreCase("Yes")) {

                    manageIcon(generalFunc.getJsonValueStr("icon", obj_temp), newView, true);
                    newView.setViewVisibility(R.id.imagareaselcted, View.VISIBLE);
                    newView.setViewVisibility(R.id.imagarea, View.GONE);
                    newView.setTextViewText(R.id.carTypeTitle, generalFunc.getJsonValueStr("vStatus", obj_temp));
                    SpannableString s = new SpannableString(status);
                    s.setSpan(new StyleSpan(SystemFont.FontStyle.BOLD.font), 0, s.length(), 0);
                    newView.setTextViewText(R.id.carTypeTitle, s);


                } else {
                    manageIcon(generalFunc.getJsonValueStr("icon", obj_temp), newView, false);
                    newView.setViewVisibility(R.id.imagareaselcted, View.GONE);
                    newView.setViewVisibility(R.id.imagarea, View.VISIBLE);

                    SpannableString s = new SpannableString(status);
                    s.setSpan(new StyleSpan(SystemFont.FontStyle.REGULAR.font), 0, s.length(), 0);
                    newView.setTextViewText(R.id.carTypeTitle, s);
                }
                if (MyApp.getInstance().isDrakModeOn()) {
                    newView.setTextColor(R.id.carTypeTitle, Color.parseColor("#ffffff"));
                    NormalView.setTextColor(R.id.statusTxt, Color.parseColor("#ffffff"));
                    NormalView.setTextColor(R.id.titleTxt, Color.parseColor("#ffffff"));
                    expandView.setTextColor(R.id.statusTxt, Color.parseColor("#ffffff"));
                    expandView.setTextColor(R.id.titleTxt, Color.parseColor("#ffffff"));
                }

                if (generalFunc.getJsonValueStr("CustomViewBtn", jsonObject) != null && generalFunc.getJsonValueStr("CustomViewBtn", jsonObject).equalsIgnoreCase("Yes")) {
                    expandView.setViewVisibility(R.id.viewdetailsTxt, View.VISIBLE);
                    expandView.setTextViewText(R.id.viewdetailsTxt, generalFunc.retrieveLangLBl("View Details", "LBL_VIEW_DETAILS"));
                } else {
                    expandView.setViewVisibility(R.id.viewdetailsTxt, View.GONE);
                }

                if (generalFunc.getJsonValueStr("CustomTrackDetails", jsonObject) != null && generalFunc.getJsonValueStr("CustomTrackDetails", jsonObject).equalsIgnoreCase("Yes")) {
                    expandView.setViewVisibility(R.id.trackorderTxt, View.VISIBLE);
                    expandView.setTextViewText(R.id.trackorderTxt, generalFunc.retrieveLangLBl("Track Order", "LBL_TRACK_ORDER"));
                } else {
                    expandView.setViewVisibility(R.id.trackorderTxt, View.GONE);
                }


                expandView.addView(R.id.trackview, newView);
                NormalView.addView(R.id.trackview, newView);


            }
        }
        builder.setStyle(new NotificationCompat.DecoratedCustomViewStyle());
        builder.setCustomContentView(NormalView);
        builder.setCustomBigContentView(expandView);
        mNotificationManager.notify(75, builder.build());


    }

    public void manageIcon(String iconName, RemoteViews view, boolean isSelected) {
        int selectedimgId = 0;
        int normalimgId = 0;

        if (generalFunc.isRTLmode()) {
            selectedimgId = R.id.carTypeImgViewselctedrtl;
            normalimgId = R.id.carTypeImgViewrtl;

            view.setViewVisibility(R.id.carTypeImgViewrtl, View.VISIBLE);
            view.setViewVisibility(R.id.carTypeImgViewselctedrtl, View.VISIBLE);
            view.setViewVisibility(R.id.carTypeImgViewselcted, View.GONE);
            view.setViewVisibility(R.id.carTypeImgView, View.GONE);

        } else {
            selectedimgId = R.id.carTypeImgViewselcted;
            normalimgId = R.id.carTypeImgView;
            view.setViewVisibility(R.id.carTypeImgViewrtl, View.GONE);
            view.setViewVisibility(R.id.carTypeImgViewselctedrtl, View.GONE);
            view.setViewVisibility(R.id.carTypeImgViewselcted, View.VISIBLE);
            view.setViewVisibility(R.id.carTypeImgView, View.VISIBLE);
        }


        if (iconName.equalsIgnoreCase("Active")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_active);
        } else if (iconName.equalsIgnoreCase("Arrived")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_arrived);
        } else if (iconName.equalsIgnoreCase("Arriving")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_arriving);
        } else if (iconName.equalsIgnoreCase("cancelled") && (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) || eType.equalsIgnoreCase(Utils.CabGeneralType_UberX) ||
                eType.equals("Deliver") || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery))) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_cancelled);
        } else if (iconName.equalsIgnoreCase("finished")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_finished);
        } else if (iconName.equalsIgnoreCase("OnGoingTrip")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_ongoingtrip);
        } else if (iconName.equalsIgnoreCase("Placed")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_order_place);
        } else if (iconName.equalsIgnoreCase("AcceptedStore")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_acceptby_rest);
        } else if (iconName.equalsIgnoreCase("Pickedup")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_pickup);
        } else if (iconName.equalsIgnoreCase("Delivered")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_deliverd);
        } else if (iconName.equalsIgnoreCase("Cancelled") || iconName.equalsIgnoreCase("Declined")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_declineby_rest);
        } else if (iconName.equalsIgnoreCase("Review")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_confirm_item);
        } else if (iconName.equalsIgnoreCase("Billing") || iconName.equalsIgnoreCase("Billing-Cash")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic_cust_billing_payment);
        } else if (iconName.equalsIgnoreCase("Billing-Card")) {
            view.setImageViewResource(isSelected ? selectedimgId : normalimgId, R.drawable.ic__cust_billing_card);
        }


    }

    private static void playNotificationSound(Uri nofifyUrl) {
        try {
            Ringtone r = RingtoneManager.getRingtone(mContext, nofifyUrl);
            r.play();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public static void clearAllNotifications() {
        if (mNotificationManager != null) {
            mNotificationManager.cancelAll();
            mNotificationManager = null;
        }
    }
}