package com.act;

import androidx.annotation.NonNull;

import com.general.files.FireTripStatusMsg;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

public class MyFirebaseMessagingService extends FirebaseMessagingService {

    @Override
    public void onNewToken(@NonNull String s) {
        super.onNewToken(s);
    }

    @Override
    public void onMessageReceived(@NonNull RemoteMessage remoteMessage) {
        new FireTripStatusMsg().fireTripMsg(remoteMessage.getData().get("message"));
    }
}