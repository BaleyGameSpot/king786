package com.utils;
import java.util.ArrayList;
public class CommonUtilities {
    public static final String idNo = "";
    public static final String bNumber = "";
    public static final String pName = "";
    public static final String SERVER = "https://buddyverse.africa/";
    public static final String DATA_REQ_KEY = "SFWNOF8185YTOSTLLWJOKSHBHBDZ30WXHYXGLXOJXFDMXCVLZQEDDDLM32NRGP5852FAXBKHARQRC944USQDM06NSDZINLNZLFE593LTLSZKEMYKOH60DOOCMOCHHAQCYLLTGQZOIDNTTP65EEHRBBDP";
    public static final String APP_REQ_KEY = "L4:DY:BU:2G:3L:UY:2R:O9:RD:QY:LV:OJ:5Z:OQ:UV:5N:DZ:8L:BB:IO";
    public static final String TOLLURL = "https://fleet.api.here.com/2/calculateroute.json?apiKey=";
    public static final String SERVER_FOLDER_PATH = "";
    public static final String WEBSERVICE = "webservice_shark.php";
    public static final String SERVER_WEBSERVICE_PATH = SERVER_FOLDER_PATH + WEBSERVICE + "?";
    public static final String SERVER_URL = SERVER + SERVER_FOLDER_PATH;
    public static final String SERVER_URL_WEBSERVICE = SERVER + SERVER_WEBSERVICE_PATH + "?";
    public static final String SERVER_URL_PHOTOS = SERVER_URL + "webimages/";
    public static final String LINKEDINLOGINLINK = SERVER + "linkedin-login/linkedin-app.php";
    public static final String USER_PHOTO_PATH = CommonUtilities.SERVER_URL_PHOTOS + "upload/Passenger/";
    public static final String PROVIDER_PHOTO_PATH = CommonUtilities.SERVER_URL_PHOTOS + "upload/Driver/";
    public static final String COMPANY_PHOTO_PATH = CommonUtilities.SERVER_URL_PHOTOS + "upload/Company/";
    public static final String BUCKET_NAME = "system_" + pName + "_" + bNumber;
    public static final String BUCKET_FILE_NAME = "ANDROID_USER_" + pName + "_" + bNumber + ".txt";
    public static final String BUCKET_PATH = "https://storage.googleapis.com/" + BUCKET_NAME + "/" + BUCKET_FILE_NAME;
    public static ArrayList<String> ageRestrictServices = new ArrayList<>();
}