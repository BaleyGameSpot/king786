package com.fragments.deliverall;

import android.content.Context;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class DeliverAllServiceCategory {

    private static DeliverAllServiceCategory instance;

    private final Context mContext;
    private final GeneralFunctions generalFunc;

    public static DeliverAllServiceCategory getInstance() {
        if (instance == null) {
            instance = new DeliverAllServiceCategory();
        }
        return instance;
    }

    public DeliverAllServiceCategory() {
        mContext = MyApp.getInstance().getApplicationContext();
        generalFunc = new GeneralFunctions(mContext);
    }

    private StringBuilder getServiceIds(StringBuilder strBuilder) {
        JSONArray serviceArrayLbl = generalFunc.getJsonArray("ServiceCategories", generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        if (serviceArrayLbl != null && serviceArrayLbl.length() > 0) {
            for (int i = 0; i < serviceArrayLbl.length(); i++) {
                if (i == 0) {
                    strBuilder.append(generalFunc.getJsonValueStr("iServiceId", generalFunc.getJsonObject(serviceArrayLbl, i)));
                } else {
                    strBuilder.append(",").append(generalFunc.getJsonValueStr("iServiceId", generalFunc.getJsonObject(serviceArrayLbl, i)));
                }
            }
        }
        return strBuilder;
    }

    public void loadRestaurantsAllData(@NonNull String latitude, @NonNull String longitude) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadAvailableRestaurantsAll");
        parameters.put("DEFAULT_SERVICE_CATEGORY_ID_ALL", getServiceIds(new StringBuilder()).toString());
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("PassengerLat", latitude);
        parameters.put("PassengerLon", longitude);
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("eSystem", Utils.eSystem_Type);
        parameters.put("UserType", Utils.app_type);

        parameters.put("fOfferType", "NO");
        parameters.put("eFavStore", "NO");

        ApiHandler.execute(mContext, parameters, responseString -> {
            // data get Done
            generalFunc.storeData("SubcategoryForAllDeliver", responseString);
        });
    }

    public void getLanguageLabelAllServiceWise(@Nullable CallBack callBack) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("iServicesIDS", getServiceIds(new StringBuilder()).toString());
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(mContext, parameters, callBack != null, false, generalFunc, responseString -> {
            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    generalFunc.storeData("LanguagesForAllServiceType", responseString);
                    if (callBack != null) {
                        callBack.onDataFound(true);
                    }
                } else {
                    errorCallApi(callBack);
                }
            } else {
                errorCallApi(callBack);
            }
        });
    }

    private void errorCallApi(@Nullable CallBack callBack) {
        if (callBack != null) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"),
                    generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"), btn_id -> {
                        if (btn_id == 1) {
                            getLanguageLabelAllServiceWise(callBack);
                        }
                    });
        }
    }

    public void getLanguageLabelServiceWise(@NonNull String iServiceId, @Nullable DataHandler dataHandler) {
        if (!Utils.checkText(iServiceId)) {
            return;
        }
        boolean isServiceIdMatch = false;
        String LBLresponseString = generalFunc.retrieveValue("LanguagesForAllServiceType");
        if (Utils.checkText(LBLresponseString)) {

            JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, LBLresponseString);
            if (messageArray != null) {
                boolean isLangCodeMatch = generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY).equalsIgnoreCase(generalFunc.getJsonValue("vCode", LBLresponseString));
                for (int i = 0; i < messageArray.length(); i++) {
                    JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);

                    if (iServiceId.equalsIgnoreCase(generalFunc.getJsonValueStr("iServiceId", obj_temp))) {

                        if (isLangCodeMatch) {
                            if (dataHandler != null) {
                                dataHandler.onDataFound(LBLresponseString, generalFunc.getJsonValueStr("dataDic", obj_temp));
                            }
                            isServiceIdMatch = true;
                            break;
                        }
                    }
                }
            }
        }

        if (!isServiceIdMatch) {
            DeliverAllServiceCategory.getInstance().getLanguageLabelAllServiceWise(isSuccess -> {
                if (isSuccess) {
                    getLanguageLabelServiceWise(iServiceId, dataHandler);
                }
            });
        }
    }

    public interface CallBack {
        void onDataFound(boolean isSuccess);
    }

    public interface DataHandler {
        void onDataFound(String allData, String dataDic);
    }
}