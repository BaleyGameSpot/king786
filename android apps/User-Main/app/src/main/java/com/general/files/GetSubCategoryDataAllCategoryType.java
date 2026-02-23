package com.general.files;

import android.content.Context;
import android.text.TextUtils;

import androidx.annotation.NonNull;

import com.service.handler.ApiHandler;

import java.util.ArrayList;
import java.util.HashMap;

public class GetSubCategoryDataAllCategoryType {

    private final GeneralFunctions mGeneralFunc;
    private final Context mContext;
    private final String iServiceId;
    private final String latitude;
    private final String longitude;
    private ArrayList<ArrayList<String>> allIdList;

    public GetSubCategoryDataAllCategoryType(@NonNull Context context, @NonNull GeneralFunctions generalFunc, ArrayList<ArrayList<String>> allIdList, String latitude, String longitude) {
        this.mContext = context;
        this.mGeneralFunc = generalFunc;
        this.iServiceId = "";
        this.allIdList = allIdList;
        this.latitude = latitude;
        this.longitude = longitude;
        getCategoryWiseAllList(true, false, 0);
    }

    private void getCategoryWiseAllList(boolean isBidding, boolean isVideoConsult, int i) {
        if (allIdList == null || allIdList.size() == 0 || allIdList.get(i).size() == 0) {
            return;
        }
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("parentId", TextUtils.join(",", allIdList.get(i)));
        parameters.put("userId", mGeneralFunc.getMemberId());
        parameters.put("vLatitude", latitude);
        parameters.put("vLongitude", longitude);

        if (isBidding) {
            parameters.put("eCatType", "Bidding");
        } else if (isVideoConsult) {
            parameters.put("eForVideoConsultation", "Yes");
        }

        ApiHandler.execute(mContext, parameters, responseString -> {
            // data get Done
            if (isBidding) {
                mGeneralFunc.storeData("SubcategoryForBiddingCategory", responseString);
                getCategoryWiseAllList(false, true, 1);
            } else if (isVideoConsult) {
                mGeneralFunc.storeData("SubcategoryForVideoConsultCategory", responseString);
                getCategoryWiseAllList(false, false, 2);
            } else {
                mGeneralFunc.storeData("SubcategoryForAllCategory", responseString);
            }
        });
    }

    public GetSubCategoryDataAllCategoryType(@NonNull Context context, @NonNull GeneralFunctions generalFunc, String iServiceId, String latitude, String longitude, Boolean isBidding) {
        this.mContext = context;
        this.mGeneralFunc = generalFunc;
        this.iServiceId = iServiceId;
        this.latitude = latitude;
        this.longitude = longitude;

        if (isBidding) {
            getBiddingSubcateGoryListCategoryeWise();
        } else {
            getSubcateGoryListCategoryeWise();
        }
    }

    private void getSubcateGoryListCategoryeWise() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("parentId", iServiceId);
        parameters.put("userId", mGeneralFunc.getMemberId());
        parameters.put("vLatitude", latitude);
        parameters.put("vLongitude", longitude);

        ApiHandler.execute(mContext, parameters, false, false, mGeneralFunc, responseString -> {
            // data get Done
            mGeneralFunc.storeData("SubcategoryForAllCategory", responseString);
        });
    }

    private void getBiddingSubcateGoryListCategoryeWise() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("parentId", iServiceId);
        parameters.put("userId", mGeneralFunc.getMemberId());
        parameters.put("vLatitude", latitude);
        parameters.put("vLongitude", longitude);

        parameters.put("eCatType", "Bidding");

        ApiHandler.execute(mContext, parameters, false, false, mGeneralFunc, responseString -> {
            // data get Done
            mGeneralFunc.storeData("SubcategoryForBiddingCategory", responseString);
        });
    }
}