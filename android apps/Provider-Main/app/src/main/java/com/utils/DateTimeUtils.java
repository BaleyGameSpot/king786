package com.utils;

import com.general.files.GeneralFunctions;

import org.json.JSONObject;

public class DateTimeUtils {

    public static boolean Is24HourTime = false;
    public static String WithoutDayFormat = "dd MMM, yyyy";
    public static String DateFormat = "";
    public static String TimeFormat = "";
    public static String DateTimeFormat = "";
    public static String DayFormatEN = "yyyy-MM-dd";

    public static void setDateTimeFormat(GeneralFunctions generalFunc, JSONObject response) {
        DateFormat = generalFunc.getJsonValueStr("DateFormat", response);
        TimeFormat = generalFunc.getJsonValueStr("TimeFormat", response);
        DateTimeFormat = generalFunc.getJsonValueStr("DateTimeFormat", response);
    }

}