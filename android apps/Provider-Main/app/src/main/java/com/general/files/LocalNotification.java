package com.general.files;

import android.annotation.SuppressLint;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.graphics.BitmapFactory;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.provider.Settings;

import androidx.core.app.NotificationCompat;
import androidx.core.content.ContextCompat;

import com.buddyverse.providers.BuildConfig;
import com.buddyverse.providers.R;
import com.utils.IntentAction;
import com.utils.Logger;
import com.utils.Utils;

public class LocalNotification {
    private static final String CHANNEL_ID = BuildConfig.APPLICATION_ID;
    @SuppressLint("StaticFieldLeak")
    private static Context mContext;
    private static NotificationManager mNotificationManager = null;

    private static final int EVENT_NOTIFICATION_ID = Utils.NOTIFICATION_ID;

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
            intent = mContext.getPackageManager().getLaunchIntentForPackage(mContext.getPackageName());

            if (intent != null) {
                intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT |
                        Intent.FLAG_ACTIVITY_NEW_TASK |
                        Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
            }
        }
        PendingIntent contentIntent = PendingIntent.getActivity(mContext, 0, intent, IntentAction.getPendingIntentFlag());
        GeneralFunctions generalFunctions = MyApp.getInstance().getGeneralFun(mContext);
        String userProfileJson = generalFunctions.retrieveValue(Utils.USER_PROFILE_JSON);


        /*if (mNotificationManager != null) {
            mNotificationManager.cancelAll();
            mNotificationManager = null;
        }*/

        clearAllNotifications();

        // Receive Notifications in >26 version devices
        mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            // mBuilder.setChannelId(BuildConfig.APPLICATION_ID);
            NotificationChannel channel = new NotificationChannel(
                    CHANNEL_ID,
                    mContext.getString(R.string.app_name),
                    NotificationManager.IMPORTANCE_HIGH
            );

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
                // .setSound(soundUri)
                .setStyle(new NotificationCompat.BigTextStyle().bigText(message))
                .setPriority(NotificationCompat.PRIORITY_HIGH);


        if (mNotificationManager != null && onlyInBackground && MyApp.getInstance().isMyAppInBackGround()) {
//            mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
            mNotificationManager.notify(EVENT_NOTIFICATION_ID, mBuilder.build());
            playNotificationSound(generalFunctions.getJsonValue("PROVIDER_NOTIFICATION", userProfileJson));
        } else if (mNotificationManager != null && !onlyInBackground) {
//            mNotificationManager = (NotificationManager) mContext.getSystemService(Context.NOTIFICATION_SERVICE);
            mNotificationManager.notify(EVENT_NOTIFICATION_ID, mBuilder.build());
            playNotificationSound(generalFunctions.getJsonValue("PROVIDER_NOTIFICATION", userProfileJson));
        }


    }

    public static void playNotificationSound(String sound) {

        try {
            Ringtone r = RingtoneManager.getRingtone(mContext, SoundUri(sound));
            r.play();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public static void clearAllNotifications() {
        if (mNotificationManager != null) {
            mNotificationManager.cancel(EVENT_NOTIFICATION_ID);
            mNotificationManager = null;
        }
    }

    public static Uri SoundUri(String sound) {

        Uri soundUri = Settings.System.DEFAULT_NOTIFICATION_URI;

        if (Utils.checkText(sound)) {
            if (sound.equalsIgnoreCase("notification_1.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_1);
            } else if (sound.equalsIgnoreCase("notification_2.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_2);
            } else if (sound.equalsIgnoreCase("notification_3.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_3);
            } else if (sound.equalsIgnoreCase("notification_4.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_4);
            } else if (sound.equalsIgnoreCase("notification_5.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_5);
            } else if (sound.equalsIgnoreCase("notification_6.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_6);
            } else if (sound.equalsIgnoreCase("notification_7.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_7);
            } else if (sound.equalsIgnoreCase("notification_8.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_8);
            } else if (sound.equalsIgnoreCase("notification_9.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_9);
            } else if (sound.equalsIgnoreCase("notification_10.mp3")) {
                soundUri = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + mContext.getPackageName() + "/" + R.raw.notification_10);
            }
        }
        return soundUri;
    }

}
