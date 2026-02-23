package com.general.files;

import android.annotation.SuppressLint;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.PorterDuff;
import android.graphics.PorterDuffXfermode;
import android.view.View;
import android.widget.RemoteViews;

import androidx.annotation.Nullable;
import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.target.Target;
import com.buddyverse.main.BuildConfig;
import com.act.LauncherActivity;
import com.buddyverse.main.R;
import com.utils.CommonUtilities;
import com.utils.IntentAction;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONObject;

import io.reactivex.rxjava3.annotations.NonNull;

public class LiveActivityNotification {

    @SuppressLint("StaticFieldLeak")
    private static LiveActivityNotification instance;
    @SuppressLint("StaticFieldLeak")
    private static Context mContext;
    private GeneralFunctions generalFunc;

    private static final String CHANNEL_ID = BuildConfig.APPLICATION_ID + "";
    private static NotificationManager mNotificationManager = null;
    private static boolean isLiveActivity = false;
    private static String imageUrl = "Temp";

    public static LiveActivityNotification getInstance() {
        if (instance == null) {
            instance = new LiveActivityNotification();
            GeneralFunctions generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
            String obj_userProfile = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
            isLiveActivity = generalFunc.getJsonValue("ENABLE_NOTIFICATION_LIVE_ACTIVITY", obj_userProfile).equalsIgnoreCase("Yes");

            imageUrl = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", obj_userProfile);
        }
        return instance;
    }

    public void liveNotificationCancelAll() {
        if (mNotificationManager != null) {
            mNotificationManager.cancelAll();
            mNotificationManager = null;
        }
    }

    public void liveNotification(Context context, String Data) {
        mContext = context;
        generalFunc = new GeneralFunctions(context);

        JSONObject mDataObj = generalFunc.getJsonObject("LiveActivityData", generalFunc.getJsonObject(Data));
        String APP_TYPE = generalFunc.getJsonValueStr("APP_TYPE", mDataObj);

        if (isLiveActivity && (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) || APP_TYPE.equalsIgnoreCase("DELIVERALL"))) {

            initNotification(mDataObj);
        }

    }

    private void initNotification(JSONObject mData) {
        // Receive Notifications in >26 version devices
        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);

        Intent intent = new Intent(mContext, LauncherActivity.class);
        PendingIntent pendingIntent = PendingIntent.getActivity(mContext, 0, intent, IntentAction.getPendingIntentFlag());

        NotificationCompat.Builder builder = new NotificationCompat.Builder(mContext, CHANNEL_ID);
        builder.setSmallIcon(R.drawable.ic_notification_logo)
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setLargeIcon(BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_launcher))
                .setTicker(mContext.getString(R.string.app_name))
                .setColor(ContextCompat.getColor(mContext, R.color.appThemeColor_1))
                .setWhen(System.currentTimeMillis())
                .setAutoCancel(false)
                .setStyle(new NotificationCompat.DecoratedCustomViewStyle())
                .setOngoing(true)
                .setContentIntent(pendingIntent);
        builder.setPriority(Notification.PRIORITY_MAX);

        RemoteViews liveActivityViewSmall = new RemoteViews(mContext.getPackageName(), R.layout.notification_live_activity_view_small);
        RemoteViews liveActivityView = new RemoteViews(mContext.getPackageName(), R.layout.notification_live_activity_view);

        liveActivityView.setTextViewText(R.id.appNameTxt, generalFunc.getJsonValueStr("APP_NAME", mData));
        liveActivityView.setTextViewText(R.id.ETASubTitleTxt, generalFunc.getJsonValueStr("ETA_SUBTITLE", mData));
        liveActivityView.setViewVisibility(R.id.vehicleTypeImgView, View.GONE);
        liveActivityView.setViewVisibility(R.id.myAvtarImgView, View.GONE);

        liveActivityViewSmall.setTextViewText(R.id.ETASubTitleTxt, generalFunc.getJsonValueStr("ETA_SUBTITLE", mData));
        String vStatus = generalFunc.getJsonValueStr("STATUS", mData);
        switch (vStatus) {
            case "CabRequestAccepted":
            case "Arrived":
                liveActivityView.setTextViewText(R.id.ETAminTxt, generalFunc.getJsonValueStr("ETA", mData));
                liveActivityView.setTextViewText(R.id.ETATitleTxt, generalFunc.getJsonValueStr("ETA_TITLE", mData));
                liveActivityView.setTextViewText(R.id.driverNumberPlateTxt, generalFunc.getJsonValueStr("DRIVER_LICENSE_PLATE", mData));
                liveActivityView.setTextViewText(R.id.driverVehicleTxt, generalFunc.getJsonValueStr("DRIVER_VEHICLE", mData));

                liveActivityViewSmall.setTextViewText(R.id.ETATitleTxt, generalFunc.getJsonValueStr("ETA_TITLE", mData));
                liveActivityViewSmall.setTextViewText(R.id.ETAminTxt, generalFunc.getJsonValueStr("ETA", mData));
                liveActivityViewSmall.setTextViewText(R.id.driverNumberPlateTxt, generalFunc.getJsonValueStr("DRIVER_LICENSE_PLATE", mData));
                liveActivityViewSmall.setTextViewText(R.id.driverVehicleTxt, generalFunc.getJsonValueStr("DRIVER_VEHICLE", mData));
                /// -------------------------------------------------------------------
                String vehicleImgUrl = generalFunc.getJsonValueStr("VEHICLE_TYPE_IMG", mData);
                if (!Utils.checkText(vehicleImgUrl)) {
                    vehicleImgUrl = "Temp";
                }
                try {
                    Glide.with(mContext).asBitmap().load(vehicleImgUrl).listener(new RequestListener<Bitmap>() {
                        @Override
                        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Bitmap> target, boolean isFirstResource) {
                            liveActivityView.setImageViewResource(R.id.vehicleTypeImgView, R.color.transparent_full);
                            return false;
                        }

                        @Override
                        public boolean onResourceReady(Bitmap bitmap, Object model, Target<Bitmap> target, DataSource dataSource, boolean isFirstResource) {
                            liveActivityView.setImageViewBitmap(R.id.vehicleTypeImgView, bitmap);
                            liveActivityView.setViewVisibility(R.id.vehicleTypeImgView, View.VISIBLE);
                            return false;
                        }
                    }).submit(512, 512).get();

                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                    liveActivityView.setImageViewResource(R.id.vehicleTypeImgView, R.color.transparent_full);
                }
                //liveActivityView.setViewVisibility(R.id.vehicleTypeImgView, View.VISIBLE);
                liveActivityView.setViewVisibility(R.id.myAvtarImgView, View.VISIBLE);
                // ------
                try {
                    Glide.with(mContext).asBitmap().load(imageUrl).listener(new RequestListener<Bitmap>() {
                        @Override
                        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Bitmap> target, boolean isFirstResource) {
                            Bitmap bitmap = BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_no_pic_user);
                            liveActivityView.setImageViewBitmap(R.id.myAvtarImgView, getRoundedCornerBitmap(bitmap));
                            return false;
                        }

                        @Override
                        public boolean onResourceReady(Bitmap bitmap, Object model, Target<Bitmap> target, DataSource dataSource, boolean isFirstResource) {
                            liveActivityView.setImageViewBitmap(R.id.myAvtarImgView, getRoundedCornerBitmap(bitmap));
                            liveActivityView.setViewVisibility(R.id.myAvtarImgView, View.VISIBLE);
                            return false;
                        }
                    }).submit(512, 512).get();

                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                    Bitmap bitmap = BitmapFactory.decodeResource(mContext.getResources(), R.mipmap.ic_no_pic_user);
                    liveActivityView.setImageViewBitmap(R.id.myAvtarImgView, getRoundedCornerBitmap(bitmap));
                }
                /// -------------------------------------------------------------------
                if (vStatus.equalsIgnoreCase("Arrived")) {
                    liveActivityView.setViewVisibility(R.id.ETASubTitleTxt, View.GONE);
                    liveActivityView.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.black));
                    liveActivityViewSmall.setViewVisibility(R.id.ETASubTitleTxt, View.GONE);
                    liveActivityViewSmall.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.black));
                    if (MyApp.getInstance().isDrakModeOn()) {
                        liveActivityView.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.white));
                        liveActivityViewSmall.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.white));
                    }
                }
                break;
            case "OnGoingTrip":
                liveActivityView.setTextViewText(R.id.ETAminTxt, generalFunc.getJsonValueStr("ETA_TITLE", mData));
                liveActivityView.setTextViewText(R.id.ETATitleTxt, generalFunc.getJsonValueStr("ETA", mData));
                liveActivityView.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.black));
                liveActivityView.setTextColor(R.id.ETATitleTxt, mContext.getResources().getColor(R.color.red));
                liveActivityView.setViewVisibility(R.id.ETASubTitleTxt, View.VISIBLE);

                liveActivityViewSmall.setTextViewText(R.id.ETAminTxt, generalFunc.getJsonValueStr("ETA_TITLE", mData));
                liveActivityViewSmall.setTextViewText(R.id.ETATitleTxt, generalFunc.getJsonValueStr("ETA", mData));
                liveActivityViewSmall.setTextColor(R.id.ETAminTxt, mContext.getResources().getColor(R.color.black));
                liveActivityViewSmall.setTextColor(R.id.ETATitleTxt, mContext.getResources().getColor(R.color.red));
                liveActivityViewSmall.setViewVisibility(R.id.ETASubTitleTxt, View.VISIBLE);

                if (MyApp.getInstance().isDrakModeOn()) {
                    liveActivityView.setTextColor(R.id.ETAminTxt, Color.parseColor("#007AFF"));
                    liveActivityViewSmall.setTextColor(R.id.ETAminTxt, Color.parseColor("#007AFF"));
                }
                break;
        }

        int DISTANCE_STEP = Integer.parseInt(generalFunc.getJsonValueStr("DISTANCE_STEP", mData));
        liveActivityView.setProgressBar(R.id.liveTrackTripProgressbar, 11, DISTANCE_STEP, false);
        setLiveCar(liveActivityView, DISTANCE_STEP);

        if (MyApp.getInstance().isDrakModeOn()) {
            liveActivityView.setTextColor(R.id.appNameTxt, mContext.getResources().getColor(R.color.white));
            liveActivityView.setTextColor(R.id.ETASubTitleTxt, mContext.getResources().getColor(R.color.white));
            liveActivityView.setTextColor(R.id.ETATitleTxt, mContext.getResources().getColor(R.color.white));
            liveActivityView.setTextColor(R.id.driverNumberPlateTxt, mContext.getResources().getColor(R.color.white));
            liveActivityView.setTextColor(R.id.driverVehicleTxt, mContext.getResources().getColor(R.color.white));

            liveActivityViewSmall.setTextColor(R.id.ETASubTitleTxt, mContext.getResources().getColor(R.color.white));
            liveActivityViewSmall.setTextColor(R.id.ETATitleTxt, mContext.getResources().getColor(R.color.white));
            liveActivityViewSmall.setTextColor(R.id.driverNumberPlateTxt, mContext.getResources().getColor(R.color.white));
            liveActivityViewSmall.setTextColor(R.id.driverVehicleTxt, mContext.getResources().getColor(R.color.white));
        }

        /// ===
        builder.setStyle(new NotificationCompat.DecoratedCustomViewStyle());
        builder.setCustomContentView(liveActivityViewSmall);
        builder.setCustomBigContentView(liveActivityView);
        mNotificationManager.notify(78, builder.build());
    }

    @SuppressLint("DiscouragedApi")
    private void setLiveCar(@NonNull RemoteViews liveActivityView, int DISTANCE_STEP) {
       /* liveActivityView.setViewVisibility(R.id.liveTrackImg_0, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_1, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_2, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_3, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_4, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_5, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_6, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_7, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_8, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_9, View.INVISIBLE);
        liveActivityView.setViewVisibility(R.id.liveTrackImg_10, View.INVISIBLE);*/

        int resourceId = mContext.getResources().getIdentifier("liveTrackImg_" + DISTANCE_STEP, "id", mContext.getPackageName());
        liveActivityView.setViewVisibility(resourceId, View.VISIBLE);

        /*switch (DISTANCE_STEP){
            case 0:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_0, View.VISIBLE);
                break;
            case 1:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_1, View.VISIBLE);
                break;
            case 2:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_2, View.VISIBLE);
                break;
            case 3:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_3, View.VISIBLE);
                break;
            case 4:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_4, View.VISIBLE);
                break;
            case 5:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_5, View.VISIBLE);
                break;
            case 6:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_6, View.VISIBLE);
                break;
            case 7:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_7, View.VISIBLE);
                break;
            case 8:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_8, View.VISIBLE);
                break;
            case 9:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_9, View.VISIBLE);
                break;
            case 10:
                liveActivityView.setViewVisibility(R.id.liveTrackImg_10, View.VISIBLE);
                break;
        }*/

    }

    private static Bitmap getRoundedCornerBitmap(@NonNull Bitmap bitmap) {
        int w = bitmap.getWidth();
        int h = bitmap.getHeight();

        int radius = Math.min(h / 2, w / 2);
        Bitmap output = Bitmap.createBitmap(w + 8, h + 8, Bitmap.Config.ARGB_8888);

        Paint p = new Paint();
        p.setAntiAlias(true);

        Canvas c = new Canvas(output);
        c.drawARGB(0, 0, 0, 0);
        p.setStyle(Paint.Style.FILL);

        c.drawCircle((w / 2f) + 4, (h / 2f) + 4, radius, p);

        p.setXfermode(new PorterDuffXfermode(PorterDuff.Mode.SRC_IN));

        c.drawBitmap(bitmap, 4, 4, p);
        p.setXfermode(null);
        p.setStyle(Paint.Style.STROKE);
        p.setColor(Color.WHITE);
        p.setStrokeWidth(3);
        c.drawCircle((w / 2f) + 4, (h / 2f) + 4, radius, p);
        return output;
    }
}