package com.data.models;

import android.content.Context;
import android.os.Handler;

import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.JSONUtils;
import com.utils.PreLoadImages;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class StaticData {
    private static StaticData instance;

    private final Context mContext;
    private final GeneralFunctions generalFunc;

    private final HashMap<String, Object> dataMap = new HashMap<>();

    private final ArrayList<DataRequestModel> listOfHandlers = new ArrayList<>();

    boolean isStaticDataLoaded = false;

    public static StaticData getInstance() {
        if (instance == null) {
            instance = new StaticData();
        }
        return instance;
    }

    public StaticData() {
        mContext = MyApp.getInstance().getApplicationContext();
        generalFunc = new GeneralFunctions(mContext);
    }

    protected void loadData() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadStaticInfo");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        parameters.put("vCurrency", generalFunc.retrieveValue(Utils.DEFAULT_CURRENCY_VALUE));
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));

        ApiHandler.execute(mContext, parameters, response -> {
            JSONObject obj_data = JSONUtils.toJsonObj(response);
            if (GeneralFunctions.checkDataAvail(Utils.action_str, obj_data)) {
                isStaticDataLoaded = true;
                DateTimeUtils.setDateTimeFormat(generalFunc, obj_data);

                JSONObject obj_msg = JSONUtils.getJSONObj(Utils.message_str, obj_data);

                if (JSONUtils.isKeyExist("GIFT_CARD_DATA", obj_msg)) {
                    JSONObject GIFT_CARD_DATA = JSONUtils.getJSONObj("GIFT_CARD_DATA", obj_msg);
                    dataMap.put("GIFT_CARD_DATA", GIFT_CARD_DATA);
                    PreLoadImages.setGiftCardImages(generalFunc, mContext, GIFT_CARD_DATA);
                }
                if (JSONUtils.isKeyExist("DRIVER_FEEDBACK_QUESTIONS", obj_msg)) {
                    dataMap.put("DRIVER_FEEDBACK_QUESTIONS", generalFunc.getJsonArray("DRIVER_FEEDBACK_QUESTIONS", obj_msg));
                }
                if (JSONUtils.isKeyExist("LIST_LANGUAGES", obj_msg)) {
                    dataMap.put("LIST_LANGUAGES", generalFunc.getJsonArray("LIST_LANGUAGES", obj_msg));
                    generalFunc.storeData(Utils.LANGUAGE_LIST_KEY, generalFunc.getJsonValueStr("LIST_LANGUAGES", obj_msg));
                }
                if (JSONUtils.isKeyExist("LIST_CURRENCY", obj_msg)) {
                    dataMap.put("LIST_CURRENCY", generalFunc.getJsonArray("LIST_CURRENCY", obj_msg));
                    generalFunc.storeData(Utils.CURRENCY_LIST_KEY, generalFunc.getJsonValueStr("LIST_CURRENCY", obj_msg));
                }

                for (DataRequestModel dataReqModel : listOfHandlers) {
                    retrieve(dataReqModel.dataType, dataReqModel.handler);
                    dataReqModel.dataType = null;
                    dataReqModel.handler = null;
                    dataReqModel = null;
                }

                listOfHandlers.clear();

            } else {
                new Handler().postDelayed(StaticData.this::loadData, 1000);
            }
        });
    }

    public void retrieve(DataPreLoad.DataType dataType, DataHandler handler) {

        if (!isStaticDataLoaded) {
            listOfHandlers.add(new DataRequestModel(dataType, handler));
            return;
        }

        switch (dataType) {
            case GIFT_CARD:
                handler.onDataFound(dataMap.get("GIFT_CARD_DATA"));
                break;
            case FOOD_RATING_DRIVER_FEEDBACK_QUESTIONS:
                handler.onDataFound(dataMap.get("DRIVER_FEEDBACK_QUESTIONS"));
                break;
            case LANGUAGES:
                handler.onDataFound(dataMap.get("LIST_LANGUAGES"));
                break;
            case CURRENCIES:
                handler.onDataFound(dataMap.get("LIST_CURRENCY"));
                break;
        }

    }

    public interface DataHandler {
        void onDataFound(Object dataObj);
    }

    private static class DataRequestModel {
        private DataPreLoad.DataType dataType;
        private DataHandler handler;

        public DataRequestModel(DataPreLoad.DataType dataType, DataHandler handler) {
            this.dataType = dataType;
            this.handler = handler;
        }
    }
}