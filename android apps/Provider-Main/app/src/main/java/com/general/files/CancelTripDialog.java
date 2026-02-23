package com.general.files;

import android.content.Context;
import android.location.Location;

import com.act.WorkingtrekActivity;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;

import java.util.HashMap;

/**
 * Created by Admin on 21-07-2016.
 */
public class CancelTripDialog {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final HashMap<String, String> data_trip;
    private final Location userLocation;

    public CancelTripDialog(Context mContext, HashMap<String, String> data_trip, GeneralFunctions generalFunc, String iCancelReasonId, String comment, boolean isTripStart, String reason, Location userLocation) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.data_trip = data_trip;
        this.userLocation = userLocation;

        if (!isTripStart) {
            cancelTrip(iCancelReasonId, comment, reason);
        } else {
            ((WorkingtrekActivity) mContext).cancelTrip(reason, comment);
        }

    }

    private void cancelTrip(String iCancelReasonId, String comment, String reason) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "cancelTrip");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("iUserId", data_trip.get("PassengerId"));
        parameters.put("iTripId", data_trip.get("TripId"));
        parameters.put("UserType", Utils.app_type);
        parameters.put("Reason", reason);
        parameters.put("Comment", comment);
        parameters.put("iCancelReasonId", iCancelReasonId);
        if (userLocation != null) {
            parameters.put("vLatitude", "" + userLocation.getLatitude());
            parameters.put("vLongitude", "" + userLocation.getLongitude());
        }

        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {
            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    MyUtils.setIsVideoCallGenerated("No");
                    generalFunc.saveGoOnlineInfo();
                    MyApp.getInstance().refreshView(MyApp.getInstance().getCurrentAct(), responseString);
                } else {
                    if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY) || message.equals("LBL_SERVER_COMM_ERROR")) {
                        MyApp.getInstance().restartWithGetDataApp();
                    } else {
                        generalFunc.showGeneralMessage("", message);
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }
}