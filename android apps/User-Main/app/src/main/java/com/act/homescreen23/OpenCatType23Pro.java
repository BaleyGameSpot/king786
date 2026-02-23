package com.act.homescreen23;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;

import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.DonationActivity;
import com.act.MainActivity;
import com.act.RequestBidInfoActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity24;
import com.act.deliverAll.GenieDeliveryHomeActivity;
import com.act.deliverAll.RestaurantAllDetailsNewActivity;
import com.act.intercity.IntercityHomeActivity;
import com.act.nearbyservice.NearByServicesActivity;
import com.act.parking.BookParking;
import com.act.parking.ParkingPublish;
import com.act.parking.ParkingPublishAndBooking;
import com.act.rentItem.RentItemHomeActivity;
import com.act.rideSharingPro.RideMyList;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.trackService.TrackAnyList;
import com.act.trackService.TrackAnyProfileSetup;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.HashMap;
import java.util.Locale;

public class OpenCatType23Pro {

    @SuppressLint("StaticFieldLeak")
    private static OpenCatType23Pro instance;
    private Context mContext;
    private GeneralFunctions generalFunc;

    private JSONObject mDataObject;
    private String mLatitude, mLongitude, mAddress;
    private boolean isClick;

    public static OpenCatType23Pro getInstance() {
        if (instance == null) {
            instance = new OpenCatType23Pro();
        }
        return instance;
    }

    public void clickEvent(boolean isClick) {
        this.isClick = isClick;
    }

    public void initiateData(Context mContext, GeneralFunctions generalFunc, JSONObject dataObject, String latitude, String longitude, String address) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.mDataObject = dataObject;

        this.mLatitude = latitude;
        this.mLongitude = longitude;
        this.mAddress = address;

        if (isClick) {
            return;
        }
        clickEvent(true);
        execute();
    }

    private void execute() {
        String eCatType = generalFunc.getJsonValueStr("eCatType", mDataObject);
        if (eCatType != null) {
            Bundle bn = new Bundle();
            String s = eCatType.toUpperCase(Locale.US);
            generalFunc.storeData(Utils.iServiceId_KEY, "");

            if (!ServiceModule.isDeliveronly()) {
                getLatLongAddress(bn);
            }

            switch (s) {
                case "RIDE", "RIDEPOOL" -> {
                    bn.putString("selType", Utils.CabGeneralType_Ride);
                    bn.putBoolean("isRestart", false);
                    if (mDataObject.has("isHome")) {
                        bn.putBoolean("isHome", true);
                    } else if (mDataObject.has("isWork")) {
                        bn.putBoolean("isWork", true);
                    } else if (mDataObject.has("eForMedicalService")) {
                        bn.putBoolean("eForMedicalService", generalFunc.getJsonValueStr("eForMedicalService", mDataObject).equalsIgnoreCase("Yes"));
                    }
                    if (s.equalsIgnoreCase("RIDEPOOL")) {
                        bn.putBoolean("isRidePool", true);
                    }
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "FLY" -> {
                    bn.putString("selType", Utils.CabGeneralType_Ride);
                    bn.putBoolean("eFly", true);
                    bn.putBoolean("isRestart", false);
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "RIDERECENTLOCATION" -> {
                    bn.putString("selType", Utils.CabGeneralType_Ride);
                    bn.putString("vPlacesLocation", generalFunc.getJsonValueStr("tDaddress", mDataObject));
                    bn.putString("vPlacesLocationLat", generalFunc.getJsonValueStr("tEndLat", mDataObject));
                    bn.putString("vPlacesLocationLong", generalFunc.getJsonValueStr("tEndLong", mDataObject));
                    bn.putBoolean("isPlacesLocation", true);
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "MOTORIDE" -> {
                    bn.putString("selType", Utils.CabGeneralType_Ride);
                    bn.putBoolean("isRestart", false);
                    bn.putBoolean("emoto", true);
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "DELIVERY", "MOTODELIVERY" -> {
                    boolean isMulti = generalFunc.getJsonValueStr("eDeliveryType", mDataObject).equalsIgnoreCase("Multi");
                    if (isMulti) {
                        bn.putBoolean("fromMulti", true);
                    }
                    /*Single Delivery UI as Multi Delivery - SdUiAsMd*/
                    else if (generalFunc.retrieveValue("ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY").equalsIgnoreCase("Yes")) {
                        bn.putBoolean("fromMulti", true);
                        bn.putString("maxDestination", "1");
                    }
                    bn.putString("vCategory", generalFunc.getJsonValueStr("vCategory", mDataObject));
                    bn.putString("selType", Utils.CabGeneralType_Deliver);
                    bn.putBoolean("isRestart", false);
                    if (s.equalsIgnoreCase("MOTODELIVERY")) {
                        bn.putBoolean("emoto", true);
                    }
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "RENTAL", "MOTORENTAL" -> {
                    bn.putString("selType", "rental");
                    bn.putBoolean("isRestart", false);
                    if (s.equalsIgnoreCase("MOTORENTAL")) {
                        bn.putBoolean("emoto", true);
                    }
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "SERVICEPROVIDER" -> {
                    getLatLongAddress(bn);
                    bn.putBoolean("isufx", true);
                    bn.putBoolean("isCarwash", true);
                    bn.putBoolean("isVideoConsultEnable", generalFunc.getJsonValueStr("eVideoConsultEnable", mDataObject).equalsIgnoreCase("Yes"));
                    bn.putString("SelectvVehicleType", generalFunc.getJsonValueStr("SelectvVehicleType", mDataObject));
                    bn.putString("SelectedVehicleTypeId", generalFunc.getJsonValueStr("iVehicleCategoryId", mDataObject));
                    bn.putString("parentId", generalFunc.getJsonValueStr("iParentId", mDataObject));
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "TAXIBID", "MOTOBID" -> {
                    bn.putString("selType", Utils.CabGeneralType_Ride);
                    bn.putBoolean("isRestart", false);
                    bn.putBoolean("isTaxiBid", true);
                    if (s.equalsIgnoreCase("MOTOBID")) {
                        bn.putBoolean("emoto", true);
                    }
                    new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                }
                case "GENIE", "RUNNER", "ANYWHERE" -> {
                    bn.putString("vCategory", generalFunc.getJsonValueStr("vCategory", mDataObject));
                    bn.putString("eCatType", generalFunc.getJsonValueStr("eCatType", mDataObject));
                    bn.putString("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", mDataObject));
                    new ActUtils(mContext).startActWithData(GenieDeliveryHomeActivity.class, bn);
                }
                case "DELIVERALL" ->
                        goToDeliverAllScreen(generalFunc.getJsonValueStr("iServiceId", mDataObject));
                case "STORE", "ITEM" -> {
                    String iServiceId = generalFunc.getJsonValueStr("iServiceId", mDataObject);
                    HashMap<String, String> storeData = new HashMap<>();
                    storeData.put(Utils.iServiceId_KEY, iServiceId);
                    storeData.put("DEFAULT_SERVICE_CATEGORY_ID", iServiceId);
                    generalFunc.storeData(storeData);

                    //
                    bn.putString("iCompanyId", generalFunc.getJsonValueStr("iCompanyId", mDataObject));
                    bn.putString("Restaurant_Status", generalFunc.getJsonValueStr("Restaurant_Status", mDataObject));
                    bn.putString("ispriceshow", generalFunc.getJsonValueStr("ispriceshow", mDataObject));
                    bn.putString("eAvailable", generalFunc.getJsonValueStr("eAvailable", mDataObject));
                    bn.putString("timeslotavailable", generalFunc.getJsonValueStr("timeslotavailable", mDataObject));

                    bn.putString("Restaurant_Safety_Status", generalFunc.getJsonValueStr("Restaurant_Safety_Status", mDataObject));
                    bn.putString("Restaurant_Safety_Icon", generalFunc.getJsonValueStr("Restaurant_Safety_Icon", mDataObject));
                    bn.putString("Restaurant_Safety_URL", generalFunc.getJsonValueStr("Restaurant_Safety_URL", mDataObject));
                    bn.putBoolean("getLanguageLabel", true);
                    if (s.equalsIgnoreCase("STORE")) {
                        new ActUtils(mContext).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
                    } else {
                        bn.putString("iMenuItemId", generalFunc.getJsonValueStr("iMenuItemId", mDataObject));
                        bn.putString("iFoodMenuId", generalFunc.getJsonValueStr("iFoodMenuId", mDataObject));
                        new ActUtils(mContext).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
                    }
                }
                case "MOREDELIVERY" -> {
                    bn.putString("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", mDataObject));
                    bn.putString("vCategory", generalFunc.getJsonValueStr("vCategory", mDataObject));
                    if (mDataObject.has("eFor") && generalFunc.getJsonValueStr("eFor", mDataObject).equalsIgnoreCase("DeliverAllCategory")) {
                        bn.putBoolean("isDeliverAll", true);
                    }
                    new ActUtils(mContext).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                }
                case "DONATION" ->
                        new ActUtils(mContext).startActWithData(DonationActivity.class, bn);
                case "BIDDING" -> {
                    getLatLongAddress(bn);
                    bn.putString("SelectvVehicleType", generalFunc.getJsonValueStr("vCategory", mDataObject));
                    bn.putString("SelectedVehicleTypeId", generalFunc.getJsonValueStr("iBiddingId", mDataObject));
                    new ActUtils(mContext).startActWithData(RequestBidInfoActivity.class, bn);
                }
                case "RENTITEM", "RENTESTATE", "RENTCARS" -> {
                    getLatLongAddress(bn);
                    bn.putString("iCategoryId", generalFunc.getJsonValueStr("iCategoryId", mDataObject));
                    bn.putString("eType", generalFunc.getJsonValueStr("eCatType", mDataObject));
                    new ActUtils(mContext).startActWithData(RentItemHomeActivity.class, bn);
                }
                case "NEARBY" -> {
                    if (mDataObject.has("iCategoryId")) {
                        bn.putString("iCategoryId", generalFunc.getJsonValueStr("iCategoryId", mDataObject));
                    }
                    new ActUtils(mContext).startActWithData(NearByServicesActivity.class, bn);
                }
                case "RIDESHAREPUBLISH", "RIDESHAREBOOK" -> {
                    if (ServiceModule.EnableRideSharingPro) {
                        bn.putString("eCatType", generalFunc.getJsonValueStr("eCatType", mDataObject));
                        new ActUtils(mContext).startActWithData(RideSharingProHomeActivity.class, bn);
                    }
                }
                case "RIDESHAREMYRIDES" -> new ActUtils(mContext).startAct(RideMyList.class);
                case "RENTSPACE" -> {
                    getLatLongAddress(bn);
                    new ActUtils(mContext).startActWithData(ParkingPublish.class, bn);
                }
                case "MYPARKINGS" ->
                        new ActUtils(mContext).startAct(ParkingPublishAndBooking.class);
                case "BOOKPARKING" -> {
                    getLatLongAddress(bn);
                    new ActUtils(mContext).startActWithData(BookParking.class, bn);
                }
                case "TRACKSERVICE" -> new ActUtils(mContext).startAct(TrackAnyList.class);
                case "TRACKANYSERVICE" ->
                        checkProfileSetup(generalFunc.getJsonValueStr("MemberType", this.mDataObject));
                case "TRACKSERVICEADD" ->
                        new ActUtils(mContext).startAct(TrackAnyProfileSetup.class);
                case "INTERCITY" ->
                        new ActUtils(mContext).startActWithData(IntercityHomeActivity.class, bn);
                default -> {
                    clickEvent(false);
                    //generalFunc.showGeneralMessage("", "no open screen");
                    Logger.d("Action", "no open screen");
                }
            }
        }
    }

    private void checkProfileSetup(String memberType) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "checkTrackingProfileSetup");
        parameters.put("MemberType", memberType);

        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    Bundle bn = new Bundle();
                    if (generalFunc.getJsonValue("eCatType", message).equalsIgnoreCase("TrackService")) {
                        bn.putString("MemberType", memberType);
                        new ActUtils(mContext).startActWithData(TrackAnyList.class, bn);

                    } else if (generalFunc.getJsonValue("eCatType", message).equalsIgnoreCase("TRACKSERVICEADD")) {
                        bn.putString("vTitle", generalFunc.getJsonValueStr("vTitle", mDataObject));
                        bn.putString("MemberType", memberType);
                        new ActUtils(mContext).startActWithData(TrackAnyProfileSetup.class, bn);
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void goToDeliverAllScreen(String iServiceId) {
        HashMap<String, String> storeData = new HashMap<>();
        storeData.put(Utils.iServiceId_KEY, iServiceId);
        storeData.put("DEFAULT_SERVICE_CATEGORY_ID", iServiceId);
        generalFunc.storeData(storeData);

        Bundle bn = new Bundle();
        getLatLongAddress(bn);
        bn.putBoolean("isback", true);
        if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            directStoreItemsView(iServiceId, bn);
        } else {
            JSONObject obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            if (generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_24", obj_userProfile).equalsIgnoreCase("Yes")) {
                new ActUtils(mContext).startActWithData(FoodDeliveryHomeActivity24.class, bn);
            } else {
                new ActUtils(mContext).startActWithData(FoodDeliveryHomeActivity.class, bn);
            }
        }
    }

    private void directStoreItemsView(String iServiceId, Bundle bn) {
        DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(iServiceId, (allData, dataDic) -> {

            HashMap<String, String> storeData = new HashMap<>();
            storeData.put(Utils.languageLabelsKey, dataDic);
            storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
            storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
            storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
            generalFunc.storeData(storeData);
            GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
            Utils.setAppLocal(MyApp.getInstance().getCurrentAct());

            bn.putString("iCompanyId", generalFunc.getJsonValue("STORE_ID", allData));
            bn.putString("ispriceshow", generalFunc.getJsonValue("ispriceshow", allData));
            bn.putString("eAvailable", generalFunc.getJsonValue("eAvailable", allData));
            bn.putString("timeslotavailable", generalFunc.getJsonValue("timeslotavailable", allData));
            new ActUtils(mContext).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
        });
    }

    private void getLatLongAddress(Bundle bn) {
        bn.putString("address", Utils.checkText(mAddress) ? mAddress : "");

        bn.putString("latitude", Utils.checkText(mLatitude) ? mLatitude : "");
        bn.putString("lat", Utils.checkText(mLatitude) ? mLatitude : "");

        bn.putString("longitude", Utils.checkText(mLongitude) ? mLongitude : "");
        bn.putString("long", Utils.checkText(mLongitude) ? mLongitude : "");

    }
}