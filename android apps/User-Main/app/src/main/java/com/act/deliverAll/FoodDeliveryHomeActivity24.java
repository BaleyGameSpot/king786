package com.act.deliverAll;

import android.content.Context;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.PersistableBundle;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.fragments.deliverall.FoodHomeScreen;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityFoodDeliveryHome24Binding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.HashMap;

public class FoodDeliveryHomeActivity24 extends ParentActivity {

    private ActivityFoodDeliveryHome24Binding binding;
    private FoodHomeScreen mFoodHomeScreen;
    public String mAddress, mLatitude, mLongitude;
    private boolean isLoadLangDone = false;

    @Override
    public void onPostCreate(@Nullable Bundle savedInstanceState, @Nullable PersistableBundle persistentState) {
        super.onPostCreate(savedInstanceState, persistentState);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_food_delivery_home_24);

        //------------------------------------------------------------------------------------
        if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && Utils.checkText(generalFunc.retrieveValue(Utils.iServiceId_KEY))) {
            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                //
                DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(generalFunc.retrieveValue(Utils.iServiceId_KEY), (allData, dataDic) -> {
                    if (isFinishing()) {
                        return;
                    }
                    //
                    HashMap<String, String> storeData = new HashMap<>();
                    storeData.put(Utils.languageLabelsKey, dataDic);
                    storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                    storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                    storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                    generalFunc.storeData(storeData);
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                    Utils.setAppLocal(MyApp.getInstance().getCurrentAct());
                    //
                    isLoadLangDone = true;
                    mFoodHomeScreen.onCreate(binding, false);
                    mFoodHomeScreen.onResume();
                });
            }, 77);
        } else {
            generalFunc.showError(true);
        }
        //------------------------------------------------------------------------------------

        mAddress = getIntent().getStringExtra("address");
        mLatitude = getIntent().getStringExtra("latitude");
        mLongitude = getIntent().getStringExtra("longitude");
        if (Utils.checkText(mAddress) && Utils.checkText(mLatitude) && Utils.checkText(mLongitude)) {

            HashMap<String, String> locStoreData = new HashMap<>();
            locStoreData.put(Utils.SELECT_ADDRESSS, mAddress);
            locStoreData.put(Utils.SELECT_LATITUDE, mLatitude);
            locStoreData.put(Utils.SELECT_LONGITUDE, mLongitude);

            locStoreData.put(Utils.CURRENT_ADDRESSS, mAddress);
            locStoreData.put(Utils.CURRENT_LATITUDE, mLatitude);
            locStoreData.put(Utils.CURRENT_LONGITUDE, mLongitude);
            generalFunc.storeData(locStoreData);

            //
            mFoodHomeScreen = new FoodHomeScreen(this, mAddress, mLatitude, mLongitude);
            mFoodHomeScreen.setListener((address, latitude, longitude) -> {
                mAddress = address;
                mLatitude = latitude;
                mLongitude = longitude;
            });
            mFoodHomeScreen.onPreCreate(binding);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        OpenNoLocationView.getInstance(this, binding.rootLayout).configView(false);
    }

    @Override
    public void onResume() {
        super.onResume();
        if (isLoadLangDone) {
            DeliverAllServiceCategory.getInstance().getLanguageLabelServiceWise(generalFunc.retrieveValue("DEFAULT_SERVICE_CATEGORY_ID"), (allData, dataDic) -> {

                HashMap<String, String> storeData = new HashMap<>();
                storeData.put(Utils.languageLabelsKey, dataDic);
                storeData.put(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", allData));
                storeData.put(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", allData));
                storeData.put(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", allData));
                generalFunc.storeData(storeData);
                GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());
                Utils.setAppLocal(MyApp.getInstance().getCurrentAct());
            });
            mFoodHomeScreen.onResume();
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        mFoodHomeScreen.onPause();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        mFoodHomeScreen.onDestroy();
    }

    public Context getActContext() {
        return FoodDeliveryHomeActivity24.this;
    }

    @Override
    public void onBackPressed() {
        if (mFoodHomeScreen.onBackPressed()) {
            super.onBackPressed();
        }
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        mFoodHomeScreen.onClickView(view.getId());
    }
}