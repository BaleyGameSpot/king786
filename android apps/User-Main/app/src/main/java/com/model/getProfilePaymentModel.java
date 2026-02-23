package com.model;

import android.content.Context;

import com.general.files.GeneralFunctions;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class getProfilePaymentModel {


    static Context mcontext;
    static GeneralFunctions generalFun;
    static profileDelegate profileObj;

    static public JSONObject objProfilePaymentDetails;

    public static void getProfilePayment(String etype, Context context, profileDelegate profileObject, boolean isContactlessDelivery, boolean isTakeAway) {

        mcontext = context;
        generalFun = new GeneralFunctions(mcontext);
        profileObj = profileObject;

        if (generalFun.getMemberId().equalsIgnoreCase("")) {
            return;
        }
        final HashMap<String, String> parameters = new HashMap<String, String>();

        parameters.put("type", "GetProfilePaymentDetails");
        parameters.put("eType", etype);

        if (isContactlessDelivery) {

            parameters.put("isContactlessDelivery", "Yes");
            parameters.put("eSystem", etype);
        }
        if (isTakeAway) {
            parameters.put("eTakeAway", "Yes");
            parameters.put("eSystem", etype);
        }


        ApiHandler.execute(mcontext, parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail == true) {
                    objProfilePaymentDetails = generalFun.getJsonObject(generalFun.getJsonValue(Utils.message_str, responseString));
                    if (profileObject != null) {
                        profileObj.notifyProfileInfoInfo();
                    }
                }
            }
        });


    }

    public static JSONObject getProfileInfo() {
        return objProfilePaymentDetails;
    }


}


