package com.utils;

import android.content.Context;
import android.widget.ImageView;

import androidx.annotation.NonNull;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;

import org.json.JSONArray;
import org.json.JSONObject;

public class PreLoadImages {

    // TODO : GiftCard PreLoad Data
    public static void setGiftCardImages(@NonNull GeneralFunctions generalFunc, @NonNull Context mContext, JSONObject GIFT_CARD_DATA) {
        JSONArray arr = generalFunc.getJsonArray("GIFT_CARD_IMAGES", GIFT_CARD_DATA);
        if (arr != null && arr.length() > 0) {
            for (int i = 0; i < arr.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(arr, i);
                String imageUrl = generalFunc.getJsonValueStr("vImage", obj_temp);
                if (Utils.checkText(imageUrl)) {
                    new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, imageUrl, getGiftCardImagesWidth(mContext, true), getGiftCardImagesWidth(mContext, false))), new ImageView(mContext)).build();
                }
            }
        }
    }

    public static int getGiftCardImagesWidth(@NonNull Context mContext, boolean isWidth) {
        if (isWidth) {
            return mContext.getResources().getDimensionPixelSize(R.dimen._250sdp);
        } else {
            return mContext.getResources().getDimensionPixelSize(R.dimen._141sdp);
        }
    }
}