package com.general.files;

import android.content.Context;

import com.service.handler.AppService;
import com.service.model.DataProvider;

/**
 * Created by Admin on 02-07-2016.
 */
public class GetAddressFromLocation {
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private AddressFound addressFound;
    private double latitude = 0, longitude = 0;
    boolean isLoaderEnable = false;

    public GetAddressFromLocation(Context mContext, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
    }

    public void setLocation(double latitude, double longitude) {
        this.latitude = latitude;
        this.longitude = longitude;
    }

    public void setLoaderEnable(boolean isLoaderEnable) {
        this.isLoaderEnable = isLoaderEnable;
    }

    public void execute() {
        AppService.getInstance().executeService(MyApp.getInstance().getApplicationContext(), new DataProvider.DataProviderBuilder(latitude + "", longitude + "").build(), AppService.Service.LOCATION_DATA, data -> {
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                return;
            }
            if (addressFound != null) {
                addressFound.onAddressFound(data.get("ADDRESS").toString(), GeneralFunctions.parseDoubleValue(0.0, data.get("LATITUDE").toString()), GeneralFunctions.parseDoubleValue(0.0, data.get("LONGITUDE").toString()), data.get("RESPONSE_DATA").toString());
            }
        });
    }

    public void setAddressList(AddressFound addressFound) {
        this.addressFound = addressFound;
    }

    public interface AddressFound {
        void onAddressFound(String address, double latitude, double longitude, String geocodeobject);
    }
}