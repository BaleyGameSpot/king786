package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.Nullable;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.SnapHelper;

import com.act.deliverAll.FoodDeliveryHomeActivity;
import com.act.deliverAll.RestaurantAllDetailsNewActivity;
import com.activity.ParentActivity;
import com.adapter.files.CommonBanner23Adapter;
import com.adapter.files.DeliveryBannerAdapter;
import com.adapter.files.DeliveryIconAdapter;
import com.adapter.files.SubCategoryItemAdapter;
import com.dialogs.BottomInfoDialog;
import com.fragments.MyBookingFragment;
import com.fragments.MyProfileFragment;
import com.fragments.MyWalletFragment;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.AddBottomBar;
import com.general.files.AutoSlideView;
import com.general.files.GeneralFunctions;
import com.general.files.GetUserData;
import com.general.files.MyApp;
import com.general.files.OpenAdvertisementDialog;
import com.general.files.OpenCatType;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.model.DeliveryIconDetails;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class CommonDeliveryTypeSelectionActivity extends ParentActivity implements SubCategoryItemAdapter.SubCategoryClickListener, DeliveryBannerAdapter.OnBannerItemClickList {

    private ImageView backImgView;
    private RecyclerView mainRecyleView, rvBanner;
    private final ArrayList<DeliveryIconDetails> list = new ArrayList<>();
    private final ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
    private DeliveryIconAdapter deliveryIconAdapter;
    private DeliveryBannerAdapter deliveryBannerAdapter;
    private boolean iswallet = false, isClicked = false;
    private int position = -1;
    private LinearLayout bottomMenuArea;

    private MyProfileFragment myProfileFragment;
    private MyWalletFragment myWalletFragment;
    public MyBookingFragment myBookingFragment;

    private static final int SEL_CARD = 4;
    private static final int TRANSFER_MONEY = 87;
    private FrameLayout container;
    private CommonBanner23Adapter mBannerAdapter;
    private JSONArray arr;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_multi_type_selection);

        initView();
        addToClickHandler(backImgView);

        getDetails();
        bannerData();
        if (Utils.checkText(generalFunc.getMemberId())) {
            new AddBottomBar(getActContext(), obj_userProfile);
        }

        if (generalFunc.getJsonValue("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("No")) {
            if (ServiceModule.RideDeliveryProduct || ServiceModule.isDeliveronly()) {
                String advertise_banner_data = generalFunc.getJsonValueStr("advertise_banner_data", obj_userProfile);
                if (advertise_banner_data != null && !advertise_banner_data.equalsIgnoreCase("")) {

                    String image_url = generalFunc.getJsonValue("image_url", advertise_banner_data);
                    if (image_url != null && !image_url.equalsIgnoreCase("")) {
                        HashMap<String, String> map = new HashMap<>();
                        map.put("image_url", image_url);
                        map.put("tRedirectUrl", generalFunc.getJsonValue("tRedirectUrl", advertise_banner_data));
                        map.put("vImageWidth", generalFunc.getJsonValue("vImageWidth", advertise_banner_data));
                        map.put("iAdvertBannerId", generalFunc.getJsonValue("iAdvertBannerId", advertise_banner_data));
                        map.put("vImageHeight", generalFunc.getJsonValue("vImageHeight", advertise_banner_data));
                        new OpenAdvertisementDialog(getActContext(), map, generalFunc);
                    }
                }
                MyApp.getInstance().showOutsatandingdilaog(backImgView);
            }
        }

        if (ServiceModule.isDeliveronly() || ServiceModule.RideDeliveryProduct) {

            if (!generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {

                backImgView.setVisibility(View.GONE);
                bottomMenuArea.setVisibility(View.VISIBLE);
                container.setVisibility(View.VISIBLE);
                ((MTextView) findViewById(R.id.appNameTxt)).setVisibility(View.VISIBLE);
            }

        }

        if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") &&
                !generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {

            BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
            bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_TXT"),
                    R.raw.biometric, generalFunc.retrieveLangLBl("", "LBL_OK"), true);
            generalFunc.storeData("isFirstTimeSmartLoginView", "Yes");
        }

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private void bannerData() {
        mBannerAdapter = new CommonBanner23Adapter(this, generalFunc, arr);
        rvBanner.setAdapter(mBannerAdapter);
        SnapHelper mSnapHelper = new PagerSnapHelper();
        mSnapHelper.attachToRecyclerView(rvBanner);

        new AutoSlideView(5 * 1000).setAutoSlideRV(rvBanner);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getDetails() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategoryDetails");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iVehicleCategoryId", getIntent().getStringExtra("iVehicleCategoryId"));


        if (getIntent().getStringExtra("latitude") != null && !getIntent().getStringExtra("latitude").equalsIgnoreCase("")) {
            parameters.put("vLatitude", getIntent().getStringExtra("latitude"));
        }
        if (getIntent().getStringExtra("longitude") != null && !getIntent().getStringExtra("longitude").equalsIgnoreCase("")) {
            parameters.put("vLongitude", getIntent().getStringExtra("longitude"));
        }


        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);

                int dimensions = Utils.dipToPixels(getActContext(), 110);
                if (isDataAvail) {

                    String eDetailPageView = generalFunc.getJsonValueStr("eDetailPageView", responseObj);

                    JSONArray messageStr = generalFunc.getJsonArray(Utils.message_str, responseObj);
                    if (eDetailPageView.equalsIgnoreCase("Icon")) {

                        if (messageStr != null && messageStr.length() > 0) {

                            for (int i = 0; i < messageStr.length(); i++) {
                                JSONObject obj_temp = generalFunc.getJsonObject(messageStr, i);
                                DeliveryIconDetails map = new DeliveryIconDetails();
                                map.setvCategory(generalFunc.getJsonValueStr("vCategory", obj_temp));
                                map.settCategoryDesc(generalFunc.getJsonValueStr("tCategoryDesc", obj_temp));
                                map.setPos(i);

                                JSONArray subCategoryArray = generalFunc.getJsonArray("SubCategory", obj_temp);
                                if (subCategoryArray != null && subCategoryArray.length() > 0) {
                                    ArrayList<DeliveryIconDetails.SubCatData> subcatdata = new ArrayList<>();
                                    for (int j = 0; j < subCategoryArray.length(); j++) {

                                        JSONObject subobj_temp = generalFunc.getJsonObject(subCategoryArray, j);

                                        DeliveryIconDetails.SubCatData submap = new DeliveryIconDetails.SubCatData();
                                        submap.seteCatType(generalFunc.getJsonValueStr("eCatType", subobj_temp));
                                        submap.setiServiceId(generalFunc.getJsonValueStr("iServiceId", subobj_temp));
                                        submap.setvSubCategory(generalFunc.getJsonValueStr("vCategory", subobj_temp));
                                        submap.settSubCategoryDesc(generalFunc.getJsonValueStr("tCategoryDesc", subobj_temp));
                                        submap.setvImage(generalFunc.getJsonValueStr("vImage", subobj_temp));
                                        submap.seteDeliveryType(generalFunc.getJsonValueStr("eDeliveryType", subobj_temp));

                                        HashMap<String, String> hashMap = new HashMap<>();
                                        hashMap.put("vCategory", generalFunc.getJsonValueStr("vCategory", subobj_temp));
                                        hashMap.put("tCategoryDesc", generalFunc.getJsonValueStr("tCategoryDesc", subobj_temp));
                                        hashMap.put("eCatType", generalFunc.getJsonValueStr("eCatType", subobj_temp));
                                        hashMap.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", subobj_temp));
                                        hashMap.put("tBannerButtonText", generalFunc.getJsonValueStr("tBannerButtonText", subobj_temp));
                                        hashMap.put("vImage", generalFunc.getJsonValueStr("vImage", subobj_temp));

                                        String imageUrl = Utils.getResizeImgURL(getActContext(), generalFunc.getJsonValueStr("vImage", subobj_temp), dimensions, dimensions);
                                        submap.setvImage(imageUrl);

                                        hashMap.put("vImage", imageUrl);

                                        hashMap.put("eDeliveryType", generalFunc.getJsonValueStr("eDeliveryType", subobj_temp));

                                        submap.setDataMap(hashMap);

                                        subcatdata.add(submap);

                                    }
                                    map.setSubData(subcatdata);
                                }
                                list.add(map);
                            }
                        }
                        deliveryIconAdapter = new DeliveryIconAdapter(list, this);
                        mainRecyleView.setAdapter(deliveryIconAdapter);
                    } else {

                        if (messageStr != null && messageStr.length() > 0) {
                            for (int i = 0; i < messageStr.length(); i++) {
                                JSONObject obj_temp = generalFunc.getJsonObject(messageStr, i);
                                HashMap<String, String> hashMap = new HashMap<>();
                                hashMap.put("vCategory", generalFunc.getJsonValueStr("vCategory", obj_temp));
                                hashMap.put("tCategoryDesc", generalFunc.getJsonValueStr("tCategoryDesc", obj_temp));
                                hashMap.put("eCatType", generalFunc.getJsonValueStr("eCatType", obj_temp));
                                hashMap.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", obj_temp));
                                hashMap.put("tBannerButtonText", generalFunc.getJsonValueStr("tBannerButtonText", obj_temp));
                                hashMap.put("vImage", generalFunc.getJsonValueStr("vImage", obj_temp));

                                String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", obj_temp);
                                hashMap.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                                hashMap.put("eDeliveryType", generalFunc.getJsonValueStr("eDeliveryType", obj_temp));
                                list_item.add(hashMap);
                            }
                            deliveryBannerAdapter = new DeliveryBannerAdapter(getActContext(), list_item);
                            deliveryBannerAdapter.setOnItemClickList(this);


                            GridLayoutManager mLayoutManager = new GridLayoutManager(getActContext(), 2);
                            mainRecyleView.setLayoutManager(mLayoutManager);
                            mainRecyleView.setAdapter(deliveryBannerAdapter);
                            int spacingInPixels = getResources().getDimensionPixelSize(R.dimen._10sdp);
                            int spanCount = 2;
                            mainRecyleView.addItemDecoration(new SpacesItemDecoration(spanCount, spacingInPixels, true));
                            deliveryBannerAdapter.notifyDataSetChanged();
                        }

                    }
                    arr = generalFunc.getJsonArray("BANNER_DATA", responseObj);
                    mBannerAdapter.updateData(arr);
                } else {
                    final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                    generateAlert.setCancelable(false);
                    generateAlert.setBtnClickList(btn_id -> {
                        generateAlert.closeAlertBox();
                        if (btn_id == 1) {
                            finish();

                        }
                    });
                    generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"));
                    generateAlert.showAlertBox();

                }
                SkeletonViewHandler.getInstance().hideSkeletonView();
            } else {
                generalFunc.showError();
            }
        });
    }

    public void openProfileFragment() {
        isProfilefrg = true;
        isWalletfrg = false;
        isBookingfrg = false;
        container.setVisibility(View.VISIBLE);
        if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
        }
        getSupportFragmentManager().beginTransaction().replace(R.id.container, myProfileFragment).commit();
    }

    boolean isProfilefrg = false;
    boolean isWalletfrg = false;
    boolean isBookingfrg = false;

    public void openWalletFragment() {
        isProfilefrg = false;
        isWalletfrg = true;
        isBookingfrg = false;
        container.setVisibility(View.VISIBLE);
        if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
        }
        getSupportFragmentManager().beginTransaction().replace(R.id.container, myWalletFragment).commit();
    }

    public void manageHome() {
        isProfilefrg = false;
        isWalletfrg = false;
        isBookingfrg = false;
        container.setVisibility(View.GONE);
    }

    @Override
    protected void onResume() {
        super.onResume();

        isClicked = false;
        position = -1;

        if (myProfileFragment != null && isProfilefrg) {
            myProfileFragment.onResume();
        }

        if (myWalletFragment != null && isWalletfrg) {
            myWalletFragment.onResume();
        }

        if (myBookingFragment != null && isBookingfrg) {
            myBookingFragment.onResume();
        }
        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        if (iswallet) {
            iswallet = false;
        }
    }

    public void openHistoryFragment() {
        isProfilefrg = false;
        isWalletfrg = false;
        isBookingfrg = true;
        container.setVisibility(View.VISIBLE);
        if (myBookingFragment != null) {
            myBookingFragment.onDestroy();
        }
        myBookingFragment = new MyBookingFragment();
        getSupportFragmentManager().beginTransaction().replace(R.id.container, myBookingFragment).commit();
    }

    @SuppressLint("ClickableViewAccessibility")
    private void initView() {
        boolean isDeliverAll = getIntent().getBooleanExtra("isDeliverAll", false);
        backImgView = findViewById(R.id.backImgView);
        bottomMenuArea = findViewById(R.id.bottomMenuArea);
        container = findViewById(R.id.container);
        View bottomRoundfImg = findViewById(R.id.bottomRoundfImg);
        manageVectorImage(bottomRoundfImg, R.drawable.ic_dayanamic_shapl, R.drawable.ic_dayanamic_shapl_compat);
        View mainArea = findViewById(R.id.mainArea);
        rvBanner = findViewById(R.id.rvBanner);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        backImgView.setVisibility(View.VISIBLE);
        if (ServiceModule.RideDeliveryProduct) {
            if (!generalFunc.getJsonValueStr("ENABLE_RIDE_DELIVERY_NEW_FLOW", obj_userProfile).equals("Yes")) {
                titleTxt.setVisibility(View.GONE);
            }
        }
        titleTxt.setText(getIntent().getStringExtra("vCategory"));
        mainRecyleView = findViewById(R.id.mainRecyleView);
        mainRecyleView.setClipToPadding(false);

        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(mainArea, isDeliverAll ? R.layout.deliverall_commondelivery_shimmer_view : R.layout.shimmer_common_delivery);
    }

    private Activity getActContext() {
        return CommonDeliveryTypeSelectionActivity.this;
    }

    private void getLanguageLabelServiceWise() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "changelanguagelabel");
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("eSystem", Utils.eSystem_Type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    generalFunc.storeData(Utils.languageLabelsKey, generalFunc.getJsonValue(Utils.message_str, responseString));
                    generalFunc.storeData(Utils.LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vCode", responseString));
                    generalFunc.storeData(Utils.LANGUAGE_IS_RTL_KEY, generalFunc.getJsonValue("eType", responseString));
                    generalFunc.storeData(Utils.GOOGLE_MAP_LANGUAGE_CODE_KEY, generalFunc.getJsonValue("vGMapLangCode", responseString));
                    GeneralFunctions.clearAndResetLanguageLabelsData(MyApp.getInstance().getApplicationContext());

                    Utils.setAppLocal(getActContext());

                    Bundle bn = new Bundle();
                    bn.putBoolean("isback", true);

                    GetUserData getUserData = new GetUserData(generalFunc, MyApp.getInstance().getApplicationContext());
                    getUserData.GetConfigDataForLocalStorage();
                    if (generalFunc.retrieveValue("CHECK_SYSTEM_STORE_SELECTION").equalsIgnoreCase("Yes")) {
                        bn.putString("iCompanyId", generalFunc.getJsonValueStr("iCompanyId", obj_userProfile));
                        bn.putString("ispriceshow", generalFunc.getJsonValueStr("ispriceshow", obj_userProfile));
                        bn.putString("eAvailable", generalFunc.getJsonValueStr("eAvailable", obj_userProfile));
                        bn.putString("ispriceshow", generalFunc.getJsonValueStr("ispriceshow", obj_userProfile));
                        bn.putString("timeslotavailable", generalFunc.getJsonValueStr("timeslotavailable", obj_userProfile));


                        new ActUtils(getActContext()).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
                    } else {
                        new ActUtils(getActContext()).startActWithData(FoodDeliveryHomeActivity.class, bn);
                    }
                } else {
                    errorCallApi();
                }
            } else {
                errorCallApi();
            }
        });
    }

    private void errorCallApi() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
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

    @Override
    public void onItemSubCategoryClick(int position, String eDeliveryType, HashMap<String, String> dataMap) {
        dataMap.put("latitude", getIntent().getStringExtra("latitude"));
        dataMap.put("longitude", getIntent().getStringExtra("longitude"));
        dataMap.put("address", getIntent().getStringExtra("address"));
        (new OpenCatType(getActContext(), dataMap)).execute();
    }

    @Override
    public void onBannerItemClick(int position) {
        if (!isClicked && this.position != position) {
            isClicked = true;
            this.position = position;
            (new OpenCatType(getActContext(), list_item.get(position))).execute();
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        Bundle bn = new Bundle();
        bn.putString("selType", getIntent().getStringExtra("selType"));
        bn.putBoolean("isRestart", getIntent().getBooleanExtra("isRestart", false));
        if (i == R.id.backImgView) {
            onBackPressed();
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.MY_PROFILE_REQ_CODE && resultCode == RESULT_OK && data != null) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            if (myProfileFragment != null) {
                myProfileFragment.onActivityResult(requestCode, resultCode, data);
            }

        } else if (requestCode == Utils.VERIFY_INFO_REQ_CODE && resultCode == RESULT_OK && data != null) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

        } else if (requestCode == Utils.VERIFY_INFO_REQ_CODE) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == RESULT_OK && data != null) {
            iswallet = true;
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        } else if (resultCode == RESULT_OK && requestCode == SEL_CARD) {

            if (myWalletFragment != null) {
                myWalletFragment.onActivityResult(requestCode, resultCode, data);
            }
        } else if (resultCode == RESULT_OK && requestCode == TRANSFER_MONEY) {
            if (myWalletFragment != null) {
                myWalletFragment.onActivityResult(requestCode, resultCode, data);
            }
        }
    }
}