package com.utils;

import com.general.files.GeneralFunctions;

import org.json.JSONObject;

public class DateTimeUtils {

    public static boolean Is24HourTime = false;
    // Time
    public static String time24Format = "HH:mm";
    // Date
    public static String DayFormatEN = "yyyy-MM-dd";

    public static String serverDateTimeFormat = DayFormatEN + " " + "HH:mm:ss";
    public static String DateFormat = "";
    public static String TimeFormat = "";
    public static String DateTimeFormat = DateFormat + " " + TimeFormat;
    public static String getDetailDateFormatWise(String dateFormat, GeneralFunctions generalFunc) {
        String atStr = generalFunc.retrieveLangLBl("at", "LBL_AT_TXT");
        return dateFormat.concat(" '").concat(atStr).concat("' ").concat(TimeFormat);
    }

    public static void setDateTimeFormat(GeneralFunctions generalFunc, JSONObject response) {
        DateFormat = generalFunc.getJsonValueStr("DateFormat", response);
        TimeFormat = generalFunc.getJsonValueStr("TimeFormat", response);
        DateTimeFormat = generalFunc.getJsonValueStr("DateTimeFormat", response);
    }
}