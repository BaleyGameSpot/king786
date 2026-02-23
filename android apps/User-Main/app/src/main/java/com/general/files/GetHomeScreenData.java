package com.general.files;

import android.content.Context;

import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;

public class GetHomeScreenData {

    private Context context;
    private GeneralFunctions generalFunc;
    private JSONObject obj_userProfile;

    public void getHomeScreenData(Context context, GeneralFunctions generalFunc, JSONObject obj_userProfile) {
        this.context = context;
        this.generalFunc = generalFunc;
        this.obj_userProfile = obj_userProfile;

        boolean isNewHome_23 = generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_NEW_HOME_SCREEN_LAYOUT_APP_23", obj_userProfile).equalsIgnoreCase("Yes");

        boolean isNewHome_24 = generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile) != null && generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equalsIgnoreCase("Yes");

        homeScreenApiCall(isNewHome_23 || isNewHome_24);

    }

    private void homeScreenApiCall(boolean isNewHome) {
        HashMap<String, String> parameters = new HashMap<>();
        if (isNewHome) {
            parameters.put("type", "getServiceCategoriesPro");
        } else {
            parameters.put("type", "getServiceCategories");
        }
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("parentId", generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile));
        parameters.put("vLatitude", "");
        parameters.put("vLongitude", "");
        parameters.put("eForVideoConsultation", "");

        ApiHandler.execute(context, parameters, responseString -> {
            if (isNewHome) {
                generalFunc.storeData("SERVICE_HOME_DATA_23", responseString);
            } else {
                generalFunc.storeData("SERVICE_HOME_DATA", responseString);
            }
        });
    }
}