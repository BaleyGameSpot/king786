package com.general.files;

import android.annotation.SuppressLint;
import android.content.Context;

import com.countryview.model.Country;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class GetCountryList {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final ArrayList<Country> items_list = new ArrayList<>();
    @SuppressLint("StaticFieldLeak")
    private static GetCountryList getCountryListobj;

    private int imagewidth, imageHeight;

    public static synchronized GetCountryList getInstance() {
        return getCountryListobj;
    }


    public GetCountryList(Context mContext) {
        this.mContext = mContext;
        generalFunc = new GeneralFunctions(mContext);

        setCountryList();
        getCountryListobj = this;
    }


    public ArrayList<Country> getCountryList() {
        if (items_list.size() > 0) {
            return items_list;
        }
        setCountryList();
        return null;
    }


    public void setCountryList() {
        imagewidth = (int) mContext.getResources().getDimension(R.dimen._30sdp);
        imageHeight = (int) mContext.getResources().getDimension(R.dimen._20sdp);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "countryList");


        ApiHandler.execute(mContext, parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    DateTimeUtils.setDateTimeFormat(generalFunc,generalFunc.getJsonObject(responseString));
                    JSONArray countryArr = generalFunc.getJsonArray("CountryList", responseString);
                    for (int i = 0; i < countryArr.length(); i++) {
                        JSONObject tempJson = generalFunc.getJsonObject(countryArr, i);

                        String key_str = generalFunc.getJsonValueStr("key", tempJson);

                        JSONArray subListArr = generalFunc.getJsonArray("List", tempJson);
                        Country headersection = new Country(key_str, "", "", "", "", "Header", null);
                        items_list.add(headersection);
                        for (int j = 0; j < subListArr.length(); j++) {
                            JSONObject subTempJson = generalFunc.getJsonObject(subListArr, j);
                            String vCountryCode = generalFunc.getJsonValueStr("vCountryCode", subTempJson);
                            String vPhoneCode = generalFunc.getJsonValueStr("vPhoneCode", subTempJson);
                            //String iCountryId = generalFunc.getJsonValueStr("iCountryId", subTempJson);
                            String vCountry = generalFunc.getJsonValueStr("vCountry", subTempJson);
                            //String vRImage = generalFunc.getJsonValueStr("vRImage", subTempJson);
                            String vSImage = generalFunc.getJsonValueStr("vSImage", subTempJson);
                            vSImage = Utils.getResizeImgURL(mContext, vSImage, imagewidth, imageHeight);
                            String vPhoneCodeWithPlusSign = generalFunc.getJsonValueStr("vPhoneCodeWithPlusSign", subTempJson);
                            Country section = new Country(vCountry, vCountryCode, vPhoneCode, vPhoneCodeWithPlusSign, vSImage, "List", headersection);
                            items_list.add(section);
                        }
                    }
                }
            }
        });
    }
}