package com.general.files;

import android.content.Context;
import android.os.Bundle;
import android.os.Handler;

import com.act.CommonDeliveryTypeSelectionActivity;
import com.act.DonationActivity;
import com.act.MainActivity;
import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.GenieDeliveryHomeActivity;
import com.act.deliverAll.RestaurantAllDetailsNewActivity;
import com.act.nearbyservice.NearByServicesActivity;
import com.act.parking.BookParking;
import com.act.parking.ParkingPublish;
import com.act.parking.ParkingPublishAndBooking;
import com.act.rentItem.RentItemHomeActivity;
import com.act.rideSharingPro.RideMyList;
import com.act.trackService.TrackAnyList;
import com.act.trackService.TrackAnyProfileSetup;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Locale;

public class OpenCatType {
    Context mContext;
    HashMap<String, String> mapData;
    GeneralFunctions generalFunc;
    String userProfileJson = "";
    boolean isServiceIdMatch = false;

    public OpenCatType(Context mContext, HashMap<String, String> mapData) {
        this.mContext = mContext;
        this.mapData = mapData;

        generalFunc = new GeneralFunctions(mContext);
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

    }


    public void execute() {
        if (mapData.get("eCatType") != null) {
            Bundle bn = new Bundle();
            String s = mapData.get("eCatType").toUpperCase(Locale.US);
            generalFunc.storeData(Utils.iServiceId_KEY, "");

            if (!ServiceModule.isDeliveronly()) {
                String latitude = mapData.get("latitude");
                String longitude = mapData.get("longitude");
                String address = mapData.get("address");

                if (latitude == null || latitude.equalsIgnoreCase("0.0")) {
                    bn.putString("latitude", "");
                } else {
                    bn.putString("latitude", latitude);
                }
                if (longitude == null || longitude.equalsIgnoreCase("0.0")) {
                    bn.putString("longitude", "");
                } else {
                    bn.putString("longitude", longitude);
                }

                if (address == null || address.equalsIgnoreCase("")) {
                    bn.putString("address", "");
                } else {
                    bn.putString("address", address);
                }

            }


            if ("RIDE".equals(s)) {
                bn.putString("selType", Utils.CabGeneralType_Ride);
                bn.putBoolean("isRestart", false);
                if (mapData.containsKey("isHome")) {
                    bn.putBoolean("isHome", true);
                } else if (mapData.containsKey("isWork")) {
                    bn.putBoolean("isWork", true);
                } else if (mapData.containsKey("eForMedicalService")) {
                    bn.putBoolean("eForMedicalService", mapData.get("eForMedicalService").equalsIgnoreCase("Yes"));
                }
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("FLY".equals(s)) {
                bn.putString("selType", Utils.CabGeneralType_Ride);
                bn.putBoolean("eFly", true);
                bn.putBoolean("isRestart", false);
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("MOTORIDE".equals(s)) {

                bn.putString("selType", Utils.CabGeneralType_Ride);
                bn.putBoolean("isRestart", false);
                bn.putBoolean("emoto", true);
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("DELIVERY".equals(s) || "MOTODELIVERY".equals(s)) {
                boolean isMulti = mapData.get("eDeliveryType") != null && mapData.get("eDeliveryType").equalsIgnoreCase("Multi");
                if (isMulti) {
                    bn.putBoolean("fromMulti", true);
                }
                /*Single Delivery UI as Multi Delivery - SdUiAsMd*/
                else if (generalFunc.retrieveValue("ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY").equalsIgnoreCase("Yes")) {
                    bn.putBoolean("fromMulti", true);
                    bn.putString("maxDestination", "1");
                }
                bn.putString("vCategory", mapData.get("vCategory") == null ? "" : mapData.get("vCategory"));
                bn.putString("selType", Utils.CabGeneralType_Deliver);
                bn.putBoolean("isRestart", false);
                if (s.equalsIgnoreCase("MOTODELIVERY")) {
                    bn.putBoolean("emoto", true);
                }
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("RENTAL".equals(s)) {
                bn.putString("selType", "rental");
                bn.putBoolean("isRestart", false);
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("MOTORENTAL".equals(s)) {
                bn.putString("selType", "rental");
                bn.putBoolean("isRestart", false);
                bn.putBoolean("emoto", true);
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
                return;
            } else if ("Genie".equalsIgnoreCase(s)) {
                bn.putString("vCategory", mapData.get("vCategory"));
                bn.putString("eCatType", mapData.get("eCatType"));
                bn.putString("iVehicleCategoryId", mapData.get("iVehicleCategoryId"));
                new ActUtils(mContext).startActWithData(GenieDeliveryHomeActivity.class, bn);

                return;

            } else if ("Runner".equalsIgnoreCase(s)) {
                bn.putString("vCategory", mapData.get("vCategory"));
                bn.putString("eCatType", mapData.get("eCatType"));
                bn.putString("iVehicleCategoryId", mapData.get("iVehicleCategoryId"));
                new ActUtils(mContext).startActWithData(GenieDeliveryHomeActivity.class, bn);
                return;
            } else if ("Anywhere".equalsIgnoreCase(s)) {
                bn.putString("vCategory", mapData.get("vCategory"));
                bn.putString("eCatType", mapData.get("eCatType"));
                bn.putString("iVehicleCategoryId", mapData.get("iVehicleCategoryId"));
                new ActUtils(mContext).startActWithData(GenieDeliveryHomeActivity.class, bn);
                return;
            } else if ("DELIVERALL".equals(s)) {
                //CommonUtilities.ageRestrictServices.clear();
                goToDeliverAllScreen(mapData.get("iServiceId"));
                return;
            } else if ("RENTITEM".equals(s) || "RENTESTATE".equals(s) || "RENTCARS".equals(s)) {
                bn = getLatLongAddress();
                if (mapData.containsKey("iCategoryId")) {
                    bn.putString("iCategoryId", mapData.get("iCategoryId"));
                }
                if (mapData.containsKey("eType")) {
                    bn.putString("eType", mapData.get("eType"));
                }
                new ActUtils(mContext).startActWithData(RentItemHomeActivity.class, bn);
                return;
            } else if ("MOREDELIVERY".equals(s)) {
                bn.putString("iVehicleCategoryId", mapData.get("iVehicleCategoryId"));
                bn.putString("vCategory", mapData.get("vCategory"));
                if (mapData.get("eFor") != null && mapData.get("eFor").equalsIgnoreCase("DeliverAllCategory")) {
                    bn.putBoolean("isDeliverAll", true);
                }
                new ActUtils(mContext).startActWithData(CommonDeliveryTypeSelectionActivity.class, bn);
                return;
            } else if ("Donation".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startActWithData(DonationActivity.class, bn);
                return;
            } else if ("SERVICEPROVIDER".equalsIgnoreCase(s)) {
                String latitude = mapData.get("latitude");
                String longitude = mapData.get("longitude");
                String address = mapData.get("address");
                Bundle bundle = new Bundle();
                bundle.putBoolean("isufx", true);
                bundle.putString("latitude", latitude);
                bundle.putString("longitude", longitude);
                bundle.putString("address", address);
                bundle.putString("SelectvVehicleType", mapData.get("SelectvVehicleType"));
                bundle.putString("SelectedVehicleTypeId", mapData.get("iVehicleCategoryId"));
                bundle.putString("parentId", mapData.get("iParentId"));
                bundle.putBoolean("isCarwash", true);
                Logger.d("Search Timer", "::" + "Execute Started");
                new ActUtils(mContext).startActWithData(MainActivity.class, bundle);
                Logger.d("Search Timer", "::" + "Execute Done");
            } else if ("RIDEPOOL".equalsIgnoreCase(s)) {
                bn.putString("selType", Utils.CabGeneralType_Ride);
                bn.putBoolean("isRestart", false);
                if (mapData.containsKey("isHome")) {
                    bn.putBoolean("isHome", true);
                } else if (mapData.containsKey("isWork")) {
                    bn.putBoolean("isWork", true);
                }
                bn.putBoolean("isRidePool", true);
                new ActUtils(mContext).startActWithData(MainActivity.class, bn);
            } else if ("RENTSPACE".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(ParkingPublish.class);
            } else if ("MYPARKINGS".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(ParkingPublishAndBooking.class);
            } else if ("BOOKPARKING".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(BookParking.class);
            } else if ("RIDESHAREMYRIDES".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(RideMyList.class);
            } else if ("TRACKSERVICE".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(TrackAnyList.class);
            } else if ("TRACKSERVICEADD".equalsIgnoreCase(s)) {
                new ActUtils(mContext).startAct(TrackAnyProfileSetup.class);
            } else if ("NEARBY".equals(s)) {
                if (mapData.containsKey("iCategoryId")) {
                    bn.putString("iCategoryId", mapData.get("iCategoryId"));
                }
                new ActUtils(mContext).startActWithData(NearByServicesActivity.class, bn);
            }
        }
    }

    private void goToDeliverAllScreen(String iServiceId) {
        if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && generalFunc != null) {
            generalFunc.removeValue(Utils.iServiceId_KEY);
        }
        HashMap<String, String> storeData = new HashMap<>();
        storeData.put(Utils.iServiceId_KEY, iServiceId);
        storeData.put("DEFAULT_SERVICE_CATEGORY_ID", iServiceId);
        generalFunc.storeData(storeData);


        getLanguageLabelServiceWise();
    }

    private void getLanguageLabelServiceWise() {
        new Handler().post(() -> {
            String LBLresponseString;
            LBLresponseString = generalFunc.retrieveValue("LanguagesForAllServiceType");
            if (LBLresponseString != null && !LBLresponseString.equalsIgnoreCase("")) {

                JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, LBLresponseString);
                if (messageArray != null) {
                    isServiceIdMatch = false;
                    for (int i = 0; i < messageArray.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                        String iServiceId = generalFunc.getJsonValueStr("iServiceId", obj_temp);
                        if (iServiceId.equalsIgnoreCase(generalFunc.retrieveValue("DEFAULT_SERVICE_CATEGORY_ID"))) {

                            if (generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY).equalsIgnoreCase(generalFunc.getJsonValue("vCode", LBLresponseString))) {
                                manageView(LBLresponseString, generalFunc.getJsonValueStr("dataDic", obj_temp));
                                isServiceIdMatch = true;

                                break;
                            }
                        }
                    }
                    if (!isServiceIdMatch) {

                        getLBLServiceIDWise();

                        if (!(generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY).equalsIgnoreCase(generalFunc.getJsonValue("vCode", LBLresponseString)))) {
                            DeliverAllServiceCategory.getInstance().getLanguageLabelAllServiceWise(null);
                        }
                    }
                }
            }

        });


    }

    private void getLBLServiceIDWise() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(mContext, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    manageView(responseString, generalFunc.getJsonValue(Utils.message_str, responseString));
                } else {
                    errorCallApi();
                }
            } else {
                errorCallApi();

            }
        });
    }

    private void manageView(String responseString, String lblkey) {
        generalFunc.storeData(Utils.languageLabelsKey, lblkey);
        generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", responseString));
        generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));

        generalFunc.storeData(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", responseString));
        GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
        Utils.setAppLocal(mContext);

        Bundle bn = getLatLongAddress();
        bn.putBoolean("isback", true);

        if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
            bn.putString("iCompanyId", generalFunc.getJsonValue("STORE_ID", responseString));
            bn.putString("ispriceshow", generalFunc.getJsonValue("ispriceshow", responseString));
            bn.putString("eAvailable", generalFunc.getJsonValue("eAvailable", responseString));
            bn.putString("timeslotavailable", generalFunc.getJsonValue("timeslotavailable", responseString));
            new ActUtils(mContext).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
        } else {
            new ActUtils(mContext).startActWithData(FoodDeliveryHomeActivity.class, bn);
        }
    }

    private Bundle getLatLongAddress() {
        Bundle bn = new Bundle();
        String latitude = mapData.get("latitude");
        String longitude = mapData.get("longitude");
        String address = mapData.get("address");
        if (latitude == null || latitude.equalsIgnoreCase("0.0")) {
            bn.putString("latitude", "");
            bn.putString("lat", "");
        } else {
            bn.putString("latitude", latitude);
            bn.putString("lat", latitude);
        }
        if (longitude == null || longitude.equalsIgnoreCase("0.0")) {
            bn.putString("longitude", "");
            bn.putString("long", "");
        } else {
            bn.putString("longitude", longitude.toString());
            bn.putString("long", longitude.toString());
        }

        if (address == null || address.equalsIgnoreCase("")) {
            bn.putString("address", "");
        } else {
            bn.putString("address", address);
        }
        return bn;
    }

    private void errorCallApi() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(mContext);
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id != 0) {
                getLanguageLabelServiceWise();
            }
            generateAlert.closeAlertBox();
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_RETRY_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        generateAlert.showAlertBox();
    }
}