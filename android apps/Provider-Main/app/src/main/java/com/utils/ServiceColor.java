package com.utils;

import android.graphics.Color;

public enum ServiceColor {

    RIDE("#FE6059"), PARCEL_DELIVERY("#004E89"), VIDEO_CONSULTING("#F58426"), UFX("#8c820b"), BIDDING("#36715D");

    public static String[] UI_COLORS = {"#FE6059", "#004E89", "#8c820b", "#F58426", "#36715D", "#003973", "#00AE64"};
    public static final String[] UI_TEXT_COLORS = {"#FFFFFF", "#000000"};

    public final int color;

    ServiceColor(String colorStr) {
        this.color = Color.parseColor(colorStr);
    }


}
