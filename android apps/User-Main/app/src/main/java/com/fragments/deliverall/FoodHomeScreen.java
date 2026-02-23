package com.fragments.deliverall;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;
import androidx.recyclerview.widget.SnapHelper;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.act.SearchLocationActivity;
import com.act.deliverAll.EditCartActivity;
import com.act.deliverAll.FoodRatingActivity;
import com.act.deliverAll.RestaurantAllDetailsNewActivity;
import com.act.deliverAll.RestaurantsSearchActivity;
import com.activity.ParentActivity;
import com.adapter.files.CommonBanner23Adapter;
import com.adapter.files.deliverAll.CuisinesAdapter24;
import com.adapter.files.deliverAll.StoreAdapter24;
import com.adapter.files.deliverAll.StoreCategoryAdapter24;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.AutoSlideView;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.ManageScroll;
import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.google.android.material.appbar.AppBarLayout;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityFoodDeliveryHome24Binding;
import com.buddyverse.main.databinding.DeliverAllDialogFilterBinding;
import com.realmModel.Cart;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.LayoutDirection;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Objects;

import io.realm.RealmResults;

public class FoodHomeScreen implements GetLocationUpdates.LocationUpdates, GetAddressFromLocation.AddressFound, SwipeRefreshLayout.OnRefreshListener, AppBarLayout.OnOffsetChangedListener, StoreAdapter24.StoreOnClickListener {

    private final ParentActivity act;
    private final GeneralFunctions generalFunc;
    private ActivityFoodDeliveryHome24Binding binding;
    @Nullable
    private onCallBack listener;
    private String address, latitude, longitude;
    private ServerTask currentWebTask;

    private GetLocationUpdates getLastLocation;
    private GetAddressFromLocation getAddressFromLocation;
    private boolean isFirstTime = true, isApiCall = false, isUfxaddress = false, isFilter = true;

    //
    private final ArrayList<HashMap<String, String>> sortByList = new ArrayList<>();
    private int filterPosition = -1;
    private String iCategoryId = "", selectedFilterId = "", isOfferApply = "No", isOfferCheck = "No", isFavCheck = "No";

    //
    private CommonBanner23Adapter mBannerAdapter;
    private JSONArray bannerListArray;
    @Nullable
    private AutoSlideView mAutoSlideView;

    //
    private CuisinesAdapter24 cuisinesAdapter;
    private JSONArray cuisineArray;

    //
    private StoreCategoryAdapter24 mStoreCategoryAdapter24;
    private StoreAdapter24 mStoreAdapter;
    private JSONArray categoryWiseStoreArr;

    private String nextPageStr = "", isPriceShow = "";

    private BottomSheetDialog filterDialog;

    public FoodHomeScreen(@NonNull ParentActivity parentActivity, @NonNull String address, @NonNull String latitude, @NonNull String longitude) {
        this.act = parentActivity;
        this.address = address;
        this.latitude = latitude;
        this.longitude = longitude;
        this.generalFunc = parentActivity.generalFunc;
    }

    public void setListener(@Nullable onCallBack listener) {
        this.listener = listener;
    }

    public void onPreCreate(@NonNull ActivityFoodDeliveryHome24Binding bin) {
        this.binding = bin;

        binding.topArea.setVisibility(View.GONE);
        binding.cartFabIcon.setVisibility(View.GONE);
        binding.ratingArea.getRoot().setVisibility(View.GONE);

        this.isUfxaddress = false;
        if (Utils.checkText(latitude) && Utils.checkText(longitude)) {
            if (!latitude.equalsIgnoreCase("0.0") && !longitude.equalsIgnoreCase("0.0")) {
                isUfxaddress = true;
            }
        }

        setSortList();
        allNewDataMangeArea();
    }

    public void onCreate(@NonNull ActivityFoodDeliveryHome24Binding bin, boolean isBackHide) {
        onPreCreate(bin);
        binding.topArea.setVisibility(View.VISIBLE);

        if (!isUfxaddress) {
            generalFunc.isLocationPermissionGranted(true);
        }

        initializeUi(isBackHide);
        bannerData();
        cuisineData();
        categoryWishStoreData();
    }

    @SuppressLint("SetTextI18n")
    private void initializeUi(boolean isBackHide) {
        if (generalFunc.isRTLmode()) {
            binding.backImgView.setRotation(180);
        }
        act.addToClickHandler(binding.backImgView);
        binding.backImgView.setVisibility(isBackHide ? View.GONE : View.VISIBLE);

        binding.noLocationView.outAreaTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUT_OF_DELIVERY_AREA"));
        binding.noLocationView.deliveryAreaTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_AREA_NOTE"));
        binding.noLocationView.editLocationBtn.setText(generalFunc.retrieveLangLBl("", "LBL_EDIT_LOCATION"));
        act.addToClickHandler(binding.noLocationView.editLocationBtn);

        binding.headerAddressTxt.setHint(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"));
        act.addToClickHandler(binding.headerAddressTxt);

        act.addToClickHandler(binding.searchArea);
        binding.searchTxtView.setText(generalFunc.getJsonValueStr("ENABLE_ITEM_SEARCH_STORE_ORDER", act.obj_userProfile).equalsIgnoreCase("Yes") ? generalFunc.retrieveLangLBl("", "LBL_SEARCH_RESTAURANT") : generalFunc.retrieveLangLBl("", "LBL_RESTAURANT_SEARCH"));

        act.addToClickHandler(binding.filterArea);
        binding.swipeRefresh.setOnRefreshListener(this);

        binding.noOfServiceTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_TXT") + " " + generalFunc.retrieveLangLBl("", "LBL_RESTAURANT_TXT"));
        binding.filterTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_FILTER"));
        act.addToClickHandler(binding.filterTxtView);

        act.addToClickHandler(binding.cartFabIcon);

        String ratingsFromDeliverAll = generalFunc.getJsonValueStr("Ratings_From_DeliverAll", act.obj_userProfile);
        if (Utils.checkText(ratingsFromDeliverAll) && !ratingsFromDeliverAll.equalsIgnoreCase("Done")) {
            binding.ratingArea.getRoot().setVisibility(View.VISIBLE);
            binding.ratingArea.orderHotelName.setText(generalFunc.getJsonValueStr("LastOrderCompanyName", act.obj_userProfile));
            if (generalFunc.getJsonValueStr("LastOrderFoodDetailRatingShow", act.obj_userProfile).equalsIgnoreCase("Yes")) {
                binding.ratingArea.ratingBar.setPressedFillColor(ContextCompat.getColor(act, R.color.white));
            }
            binding.ratingArea.ratingCancel.setOnClickListener(v -> binding.ratingArea.getRoot().setVisibility(View.GONE));
            binding.ratingArea.ratingBar.setOnClickListener(v -> {
                Bundle bn = new Bundle();
                bn.putFloat("rating", binding.ratingArea.ratingBar.getRating());
                bn.putBoolean("IS_NEW", generalFunc.getJsonValueStr("LastOrderFoodDetailRatingShow", act.obj_userProfile).equalsIgnoreCase("Yes"));
                bn.putString("iOrderId", generalFunc.getJsonValueStr("LastOrderId", act.obj_userProfile));
                bn.putString("vOrderNo", generalFunc.getJsonValueStr("LastOrderNo", act.obj_userProfile));
                bn.putString("driverName", generalFunc.getJsonValueStr("LastOrderDriverName", act.obj_userProfile));
                bn.putString("vCompany", generalFunc.getJsonValueStr("LastOrderCompanyName", act.obj_userProfile));
                bn.putString("eTakeaway", generalFunc.getJsonValueStr("LastOrderTakeaway", act.obj_userProfile));
                new ActUtils(act).startActWithData(FoodRatingActivity.class, bn);
                binding.ratingArea.getRoot().setVisibility(View.GONE);
            });
        } else {
            binding.ratingArea.getRoot().setVisibility(View.GONE);
        }
    }

    private void allNewDataMangeArea() {
        isApiCall = false;
        binding.noLocationView.getRoot().setVisibility(View.GONE);
        binding.swipeRefresh.setVisibility(View.GONE);
        binding.errorView.setVisibility(View.GONE);
        binding.loading.setVisibility(View.VISIBLE);
        binding.loadingData.setVisibility(View.GONE);
        binding.noDataTxt.setVisibility(View.GONE);
    }

    private void setSortList() {
        sortByList.clear();
        HashMap<String, String> relevanceMap = new HashMap<>();
        relevanceMap.put("label", generalFunc.retrieveLangLBl("", "LBL_RELEVANCE"));
        relevanceMap.put("value", "relevance");
        sortByList.add(relevanceMap);
        filterPosition = 0;

        HashMap<String, String> ratingMap = new HashMap<>();
        ratingMap.put("label", generalFunc.retrieveLangLBl("", "LBL_RATING"));
        ratingMap.put("value", "rating");
        sortByList.add(ratingMap);

        HashMap<String, String> timeMap = new HashMap<>();
        timeMap.put("label", generalFunc.retrieveLangLBl("", "LBL_TIME"));
        timeMap.put("value", "time");
        sortByList.add(timeMap);

        if (generalFunc.getServiceId().equalsIgnoreCase("1")) {
            HashMap<String, String> costlTohMap = new HashMap<>();
            costlTohMap.put("label", generalFunc.retrieveLangLBl("", "LBL_COST_LTOH"));
            costlTohMap.put("value", "costlth");
            sortByList.add(costlTohMap);

            HashMap<String, String> costhTolMap = new HashMap<>();
            costhTolMap.put("label", generalFunc.retrieveLangLBl("", "LBL_COST_HTOL"));
            costhTolMap.put("value", "costhtl");
            sortByList.add(costhTolMap);
        }
    }

    @SuppressLint("SetTextI18n")
    private void bannerData() {
        mBannerAdapter = new CommonBanner23Adapter(act, generalFunc, bannerListArray);
        binding.rvBanner.setAdapter(mBannerAdapter);
        SnapHelper mSnapHelper = new PagerSnapHelper();
        mSnapHelper.attachToRecyclerView(binding.rvBanner);

        mAutoSlideView = new AutoSlideView(5 * 1000);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void cuisineData() {
        binding.cuisinesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CUISINES"));
        cuisinesAdapter = new CuisinesAdapter24(act, cuisineArray, (position, itemObj) -> {
            nextPageStr = "";
            selectedFilterId = generalFunc.getJsonValueStr("cuisineId", itemObj);
            isApiCall = false;
            getRestaurantList(true, false);
            binding.rvCuisinesList.scrollToPosition(position);

            mStoreCategoryAdapter24.updateData(null);
            mStoreAdapter.updateData(null);
        });
        binding.rvCuisinesList.setAdapter(cuisinesAdapter);
    }

    private void categoryWishStoreData() {
        mStoreCategoryAdapter24 = new StoreCategoryAdapter24(act, categoryWiseStoreArr, this);
        binding.rvDataList.setAdapter(mStoreCategoryAdapter24);
        binding.rvDataList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                MyUtils.isShadow = recyclerView.canScrollVertically(-1);
            }
        });

        //
        mStoreAdapter = new StoreAdapter24(act, categoryWiseStoreArr, LinearLayoutManager.VERTICAL, this);
        binding.rvSeeAllDataList.setAdapter(mStoreAdapter);
        binding.rvSeeAllDataList.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(recyclerView.getLayoutManager()).getChildCount();
                    int totalItemCount = recyclerView.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) recyclerView.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    if (lastInScreen == totalItemCount && !isApiCall && Utils.checkText(nextPageStr)) {
                        getRestaurantList(false, true);
                    }
                }
            }
        });
    }

    private void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
    }

    private void initializeLocationCheckDone() {
        if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
            stopLocationUpdates();
            if (isUfxaddress) {
                if (isFirstTime) {
                    Location temploc = new Location("PickupLoc");
                    temploc.setLatitude(Double.parseDouble(latitude));
                    temploc.setLongitude(Double.parseDouble(longitude));
                    onLocationUpdate(temploc);
                }
            } else {
                GetLocationUpdates.locationResolutionAsked = false;
                getLastLocation = new GetLocationUpdates(act, 150, true, this);
            }
        } else if (generalFunc.isLocationPermissionGranted(false) && !generalFunc.isLocationEnabled()) {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                OpenNoLocationView.getInstance(act, binding.rootLayout).configView(false);
            }
        } else if (isUfxaddress) {
            OpenNoLocationView.getInstance(act, binding.rootLayout).configView(false);
        }
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        OpenNoLocationView.getInstance(act, binding.rootLayout).configView(false);
        boolean isSameLoc = (mLastLocation.getLatitude() + "").equals(latitude) && (mLastLocation.getLongitude() + "").equals(longitude);

        //
        latitude = String.valueOf(mLastLocation.getLatitude());
        longitude = String.valueOf(mLastLocation.getLongitude());
        if (listener != null) {
            listener.onSetLocation(address, latitude, longitude);
        }

        if (isFirstTime) {
            if (act.getIntent().getStringExtra("latitude") != null && Utils.checkText(act.getIntent().getStringExtra("latitude"))) {
                latitude = act.getIntent().getStringExtra("latitude");
                longitude = act.getIntent().getStringExtra("longitude");
                address = act.getIntent().getStringExtra("address");
                if (listener != null && address != null) {
                    listener.onSetLocation(address, latitude, longitude);
                }
                onAddressFound(address, GeneralFunctions.parseDoubleValue(0, latitude), GeneralFunctions.parseDoubleValue(0, longitude), "");

                JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, generalFunc.retrieveValue("SubcategoryForAllDeliver"));
                boolean iServiceCategoryIdMatch = false;
                if (messageArray != null) {
                    for (int i = 0; i < messageArray.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                        String iServiceId = generalFunc.getJsonValueStr("iServiceId", obj_temp);

                        if (iServiceId.equalsIgnoreCase(generalFunc.retrieveValue("DEFAULT_SERVICE_CATEGORY_ID"))) {
                            manageData(obj_temp, false, false);
                            iServiceCategoryIdMatch = true;
                            break;
                        }
                    }
                }
                if (!iServiceCategoryIdMatch) {
                    getRestaurantList(false, false);
                }
                isFilter = true;
                isFirstTime = false;
                return;
            }
        }

        if (isSameLoc) {
            onAddressFound(generalFunc.retrieveValue(Utils.CURRENT_ADDRESSS), GeneralFunctions.parseDoubleValue(0, latitude), GeneralFunctions.parseDoubleValue(0, longitude), "");
        } else {
            if (getAddressFromLocation == null) {
                getAddressFromLocation = new GetAddressFromLocation(act, generalFunc);
            }
            getAddressFromLocation.setLocation(mLastLocation.getLatitude(), mLastLocation.getLongitude());
            getAddressFromLocation.setAddressList(this);
            getAddressFromLocation.execute();
        }

        //
        getRestaurantList(false, false);
        isFilter = true;
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (Utils.checkText(address)) {
            this.address = address;
            this.latitude = String.valueOf(latitude);
            this.longitude = String.valueOf(longitude);
            if (listener != null) {
                listener.onSetLocation(address, this.latitude, this.longitude);
            }

            binding.headerAddressTxt.setText(address);

            HashMap<String, String> storeData = new HashMap<>();
            storeData.put(Utils.SELECT_ADDRESSS, address);
            storeData.put(Utils.SELECT_LATITUDE, latitude + "");
            storeData.put(Utils.SELECT_LONGITUDE, longitude + "");

            storeData.put(Utils.CURRENT_ADDRESSS, address);
            storeData.put(Utils.CURRENT_LATITUDE, latitude + "");
            storeData.put(Utils.CURRENT_LONGITUDE, longitude + "");
            generalFunc.storeData(storeData);
        }
    }

    @Override
    public void onRefresh() {
        getRestaurantList(true, false);
    }

    private void getRestaurantList(boolean onRefresh, boolean isLoadMore) {
        if (isApiCall) {
            return;
        }
        isApiCall = true;
        manageView(onRefresh, isLoadMore, true);

        binding.filterImage.setVisibility(isOfferCheck.equalsIgnoreCase("Yes") || isFavCheck.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

        //
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadAvailableRestaurants");
        parameters.put("PassengerLat", latitude);
        parameters.put("PassengerLon", longitude);
        parameters.put("fOfferType", isOfferCheck);
        parameters.put("eFavStore", isFavCheck);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("vLang", generalFunc.retrieveValue(Utils.LANGUAGE_CODE_KEY));
        parameters.put("UserType", Utils.app_type);

        if (Utils.checkText(iCategoryId)) {
            parameters.put("iCategoryId", iCategoryId);
            mStoreCategoryAdapter24.updateData(null);
        }

        if (Utils.checkText(Utils.getText(binding.headerAddressTxt))) {
            parameters.put("vAddress", Utils.getText(binding.headerAddressTxt));
        }
        parameters.put("cuisineId", selectedFilterId);
        if (-1 < filterPosition) {
            parameters.put("sortby", sortByList.get(filterPosition).get("value"));
        }

        if (isLoadMore) {
            parameters.put("page", nextPageStr);
        }
        parameters.put("eSystem", Utils.eSystem_Type);

        if (currentWebTask != null) {
            currentWebTask.cancel(true);
            currentWebTask = null;
        }
        currentWebTask = ApiHandler.execute(act, parameters, responseString -> {
            isApiCall = false;
            currentWebTask = null;
            if (act.isFinishing()) {
                return;
            }
            manageData(generalFunc.getJsonObject(responseString), onRefresh, isLoadMore);
        });
    }

    private void manageView(boolean onRefresh, boolean isLoadMore, boolean isApiCall) {
        binding.errorView.setVisibility(View.GONE);
        binding.storeCounterArea.setVisibility(Utils.checkText(iCategoryId) ? View.VISIBLE : View.GONE);
        if (isApiCall) {
            binding.loading.setVisibility(onRefresh || isLoadMore ? View.GONE : View.VISIBLE);
            binding.loadingData.setVisibility(onRefresh && !isLoadMore ? View.VISIBLE : View.GONE);
            binding.footerInclude.progressContainer.setVisibility(isLoadMore ? View.VISIBLE : View.GONE);
        } else {
            binding.swipeRefresh.setRefreshing(false);
            binding.swipeRefresh.setVisibility(View.VISIBLE);
            binding.loading.setVisibility(View.GONE);
            binding.footerInclude.progressContainer.setVisibility(View.GONE);
            binding.loadingData.setVisibility(View.GONE);
            binding.noDataTxt.setVisibility(View.GONE);

            binding.topSearchFilterArea.setVisibility(View.VISIBLE);
            binding.noLocationView.getRoot().setVisibility(View.GONE);
        }
    }

    @SuppressLint("SetTextI18n")
    private void manageData(@Nullable JSONObject responseObj, boolean onRefresh, boolean isLoadMore) {
        manageView(onRefresh, isLoadMore, false);
        if (responseObj != null) {

            //add banner handling
            bannerListArray = generalFunc.getJsonArray("banner_data", responseObj);
            mBannerAdapter.updateData(bannerListArray);
            binding.rvBanner.setVisibility(mBannerAdapter.getItemCount() == 0 ? View.GONE : View.VISIBLE);
            if (mAutoSlideView != null) {
                mAutoSlideView.removeAll();
            }
            if (mAutoSlideView != null && 1 < mBannerAdapter.getItemCount()) {
                mAutoSlideView.setAutoSlideRV(binding.rvBanner);
            }

            if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {
                new ManageScroll(binding.collapsingToolbar).scroll(true);
                isPriceShow = generalFunc.getJsonValueStr("ispriceshow", responseObj);

                if (isFilter) {
                    isFilter = false;
                    // remove cuisine
                    cuisinesAdapter.updateData(null);

                    //handling cuisine
                    String getCuisineListData = generalFunc.getJsonValueStr("getCuisineList", responseObj);
                    isOfferApply = generalFunc.getJsonValue("isOfferApply", getCuisineListData);
                    cuisineArray = generalFunc.getJsonArray("CuisineList", getCuisineListData);
                    cuisinesAdapter.updateData(cuisineArray);
                    binding.cuisineArea.setVisibility(cuisinesAdapter.getItemCount() > 1 ? View.VISIBLE : View.GONE);
                }

                //
                if (!isLoadMore) {
                    mStoreCategoryAdapter24.updateData(null);
                    mStoreAdapter.updateData(null);
                    binding.appBarLayout.setExpanded(true, false);
                }
                JSONArray cateDataArr = generalFunc.getJsonArray("CategoryWiseStores", responseObj);
                if (generalFunc.retrieveValue("ENABLE_CATEGORY_WISE_STORES").equalsIgnoreCase("Yes") && !Utils.checkText(iCategoryId) && cateDataArr != null && cateDataArr.length() > 0) {
                    categoryWiseStoreArr = new JSONArray();
                    for (int i = 0; i < cateDataArr.length(); i++) {
                        JSONObject item = new JSONObject();
                        try {
                            item.put("isHeader", "Yes");
                        } catch (JSONException e) {
                            throw new RuntimeException(e);
                        }
                        categoryWiseStoreArr.put(item);
                        categoryWiseStoreArr.put(act.generalFunc.getJsonObject(cateDataArr, i));
                    }
                    mStoreCategoryAdapter24.updateData(categoryWiseStoreArr);
                } else {
                    binding.swipeRefresh.setEnabled(false);
                    if (!Utils.checkText(nextPageStr) || categoryWiseStoreArr == null) {
                        categoryWiseStoreArr = act.generalFunc.getJsonArray(Utils.message_str, responseObj);
                    } else {
                        JSONArray dataArr = act.generalFunc.getJsonArray(Utils.message_str, responseObj);
                        if (dataArr != null) {
                            for (int i = 0; i < dataArr.length(); i++) {
                                categoryWiseStoreArr.put(act.generalFunc.getJsonObject(dataArr, i));
                            }
                        }
                    }
                    mStoreAdapter.updateData(categoryWiseStoreArr);

                    nextPageStr = generalFunc.getJsonValueStr("NextPage", responseObj);
                    if (nextPageStr.equalsIgnoreCase("0")) {
                        nextPageStr = "";
                    }
                    String totalStore = generalFunc.getJsonValueStr("totalStore", responseObj);
                    binding.noOfServiceTxt.setText(generalFunc.convertNumberWithRTL(totalStore) + " " + generalFunc.retrieveLangLBl("", "LBL_RESTAURANTS"));
                }
            } else {
                new ManageScroll(binding.collapsingToolbar).scroll(false);
                String messageStr = generalFunc.getJsonValueStr(Utils.message_str, responseObj);
                String messageStrOne = generalFunc.getJsonValueStr(Utils.message_str_one, responseObj);
                if (Utils.checkText(messageStrOne)) {
                    binding.noDataTxt.setVisibility(View.VISIBLE);
                    binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", messageStrOne));
                } else if (Utils.checkText(messageStr)) {
                    if (messageStr.equalsIgnoreCase("LBL_NO_RESTAURANT_FOUND_TXT")) {
                        binding.topSearchFilterArea.setVisibility(View.GONE);
                        binding.noLocationView.getRoot().setVisibility(View.VISIBLE);
                    }
                }

                mStoreAdapter.updateData(null);
                binding.noOfServiceTxt.setText(generalFunc.convertNumberWithRTL("0") + " " + generalFunc.retrieveLangLBl("", "LBL_RESTAURANTS"));
            }
        } else {
            binding.swipeRefresh.setVisibility(View.GONE);
            binding.loading.setVisibility(View.GONE);
            generalFunc.generateErrorView(binding.errorView, "LBL_ERROR_TXT", "LBL_NO_INTERNET_TXT");
            binding.errorView.setVisibility(View.VISIBLE);
            binding.errorView.setOnRetryListener(() -> {
                allNewDataMangeArea();
                getRestaurantList(false, false);
                isFilter = true;
            });
        }

    }

    @Override
    public void onOffsetChanged(AppBarLayout appBarLayout, int verticalOffset) {
        if (verticalOffset == 0) {
            binding.swipeRefresh.setEnabled(true);
        } else if (verticalOffset < -100) {
            binding.swipeRefresh.setEnabled(false);
        }
    }

    public void onResume() {
        binding.appBarLayout.addOnOffsetChangedListener(this);
        initializeLocationCheckDone();

        //
        RealmResults<Cart> realmCartList = MyApp.getRealmInstance().where(Cart.class).findAll();
        if (realmCartList != null && !realmCartList.isEmpty()) {
            binding.cartFabIcon.setVisibility(View.VISIBLE);

            int cnt = 0;
            for (int i = 0; i < realmCartList.size(); i++) {
                if (realmCartList.get(i) != null && Utils.checkText(realmCartList.get(i).getQty())) {
                    cnt = cnt + GeneralFunctions.parseIntegerValue(0, realmCartList.get(i).getQty());
                }
            }
            binding.cartFabIcon.setCount(cnt);
        } else {
            binding.cartFabIcon.setVisibility(View.GONE);
        }
    }

    public void onPause() {
        binding.appBarLayout.removeOnOffsetChangedListener(this);
    }

    public void onDestroy() {
        if (mAutoSlideView != null) {
            mAutoSlideView.removeAll();
        }
        stopLocationUpdates();
        if (getLastLocation != null) {
            getLastLocation = null;
        }
        if (getAddressFromLocation != null) {
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        }
    }

    private void openFilterDialog() {
        int height = act.getResources().getDimensionPixelSize(R.dimen._190sdp);
        if (filterDialog != null && filterDialog.isShowing()) {
            return;
        }
        filterDialog = new BottomSheetDialog(act);
        DeliverAllDialogFilterBinding fBinding = DeliverAllDialogFilterBinding.inflate(LayoutInflater.from(act));

        fBinding.menuTitle.setText(generalFunc.retrieveLangLBl("", "LBL_SHOW_RESTAURANTS_WITH"));

        fBinding.closeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CLOSE_TXT"));
        fBinding.closeTxt.setOnClickListener(v -> filterDialog.dismiss());
        //
        fBinding.resetTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RESET"));
        fBinding.resetTxt.setVisibility(isOfferCheck.equalsIgnoreCase("Yes") || isFavCheck.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
        fBinding.resetTxt.setOnClickListener(v -> {
            filterDialog.dismiss();
            isOfferCheck = "No";
            isFavCheck = "No";
            fBinding.resetTxt.setVisibility(View.GONE);
            categoryWiseStoreArr = null;
            getRestaurantList(true, false);
        });

        //
        MButton applyRatingBtn = ((MaterialRippleLayout) fBinding.btnType2).getChildView();
        applyRatingBtn.setText(generalFunc.retrieveLangLBl("", "LBL_APPLY_FILTER"));
        applyRatingBtn.setBackgroundColor(ContextCompat.getColor(act, R.color.defaultColor));
        applyRatingBtn.setEnabled(false);

        //
        if (isOfferApply.equalsIgnoreCase("Yes")) {
            fBinding.offerTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_OFFER"));
            fBinding.offerArea.setVisibility(View.VISIBLE);
            fBinding.offerArea.setOnClickListener(v -> fBinding.offerchkBox.performClick());
            fBinding.offerchkBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                isOfferCheck = isChecked ? "Yes" : "No";
                applyBtnView(fBinding, applyRatingBtn);
            });
        } else {
            fBinding.offerArea.setVisibility(View.GONE);
        }
        if (isOfferCheck.equalsIgnoreCase("Yes")) {
            fBinding.offerchkBox.setChecked(true);
        }

        //
        if (Utils.checkText(generalFunc.getMemberId()) && generalFunc.getJsonValueStr("ENABLE_FAVORITE_STORE_MODULE", act.obj_userProfile).equalsIgnoreCase("Yes")) {
            fBinding.favTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_FAVOURITE_STORE"));
            fBinding.favview.setVisibility(View.VISIBLE);
            fBinding.favArea.setVisibility(View.VISIBLE);
            fBinding.favArea.setOnClickListener(v -> fBinding.favchkBox.performClick());
            fBinding.favchkBox.setOnCheckedChangeListener((buttonView, isChecked) -> {
                isFavCheck = isChecked ? "Yes" : "No";
                applyBtnView(fBinding, applyRatingBtn);
            });
        } else {
            fBinding.favview.setVisibility(View.GONE);
            fBinding.favArea.setVisibility(View.GONE);
        }
        if (isFavCheck.equalsIgnoreCase("Yes")) {
            fBinding.favchkBox.setChecked(true);
        }

        //
        applyRatingBtn.setOnClickListener(v -> {
            filterDialog.dismiss();
            binding.rvSeeAllDataList.scrollToPosition(0);
            categoryWiseStoreArr = null;
            getRestaurantList(true, false);
        });

        //
        filterDialog.setContentView(fBinding.getRoot(), new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, height));
        filterDialog.setCancelable(false);
        BottomSheetBehavior<View> mBehavior = BottomSheetBehavior.from((View) fBinding.getRoot().getParent());
        mBehavior.setPeekHeight(height);

        LayoutDirection.setLayoutDirection(filterDialog);
        filterDialog.show();
    }

    private void applyBtnView(@NonNull DeliverAllDialogFilterBinding fBinding, @NonNull MButton applyRatingBtn) {
        if (fBinding.offerchkBox.isChecked() || fBinding.favchkBox.isChecked()) {
            applyRatingBtn.setEnabled(true);
            applyRatingBtn.setBackgroundColor(ContextCompat.getColor(act, R.color.appThemeColor_1));
        } else {
            applyRatingBtn.setEnabled(false);
            applyRatingBtn.setBackgroundColor(ContextCompat.getColor(act, R.color.defaultColor));
        }
    }

    public boolean onBackPressed() {
        if (Utils.checkText(iCategoryId)) {
            binding.swipeRefresh.setEnabled(true);
            nextPageStr = "";
            iCategoryId = "";
            isOfferCheck = "No";
            isFavCheck = "No";
            binding.rvDataList.scrollToPosition(0);
            binding.appBarLayout.setExpanded(true, false);
            allNewDataMangeArea();
            getRestaurantList(false, false);
            return false;
        } else {
            return true;
        }
    }

    public void onClickView(int id) {
        if (id == binding.backImgView.getId()) {
            act.onBackPressed();

        } else if (id == binding.headerAddressTxt.getId() || id == binding.noLocationView.editLocationBtn.getId()) {
            Intent intent = new Intent(act, SearchLocationActivity.class);
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            bn.putBoolean("isaddressview", true);
            bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
            bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
            bn.putString("address", Utils.getText(binding.headerAddressTxt));
            bn.putString("eSystem", Utils.eSystem_Type);
            intent.putExtras(bn);
            act.launchActForResult(78, intent, (i, result) -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    isUfxaddress = true;
                    allNewDataMangeArea();
                    onAddressFound(data.getStringExtra("Address"), GeneralFunctions.parseDoubleValue(0, data.getStringExtra("Latitude")), GeneralFunctions.parseDoubleValue(0, data.getStringExtra("Longitude")), "");

                    stopLocationUpdates();
                    Location temploc = new Location("");
                    temploc.setLatitude(GeneralFunctions.parseDoubleValue(0, data.getStringExtra("Latitude")));
                    temploc.setLongitude(GeneralFunctions.parseDoubleValue(0, data.getStringExtra("Longitude")));
                    onLocationUpdate(temploc);
                }
            });

        } else if (id == binding.searchArea.getId()) {
            Intent intent = new Intent(act, RestaurantsSearchActivity.class);
            Bundle bn = new Bundle();
            bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
            bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
            bn.putString("address", Utils.getText(binding.headerAddressTxt));
            intent.putExtras(bn);
            act.launchActForResult(79, intent, (i, result) -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    getRestaurantList(false, false);
                    isFilter = true;
                }
            });

        } else if (id == binding.filterArea.getId()) {
            OpenListView.getInstance(act, generalFunc.retrieveLangLBl("", "LBL_SORT_BY"), sortByList, OpenListView.OpenDirection.BOTTOM, true, position -> {
                filterPosition = position;
                isApiCall = false;
                getRestaurantList(true, false);
            }).show(filterPosition, "label");

        } else if (id == binding.filterTxtView.getId()) {
            if (cuisinesAdapter.getItemCount() > 0) {
                openFilterDialog();
            }
        } else if (id == binding.cartFabIcon.getId()) {
            new ActUtils(act).startAct(EditCartActivity.class);
        }
    }

    @Override
    public void onItemStoreClick(@NonNull JSONObject itemObj) {
        if (itemObj.has("iCategoryId")) {
            nextPageStr = "";
            iCategoryId = generalFunc.getJsonValueStr("iCategoryId", itemObj);
            binding.swipeRefresh.setEnabled(false);
            binding.rvSeeAllDataList.scrollToPosition(0);
            binding.appBarLayout.setExpanded(true, false);
            allNewDataMangeArea();
            getRestaurantList(false, false);
        } else {
            Bundle bundle = new Bundle();
            Iterator<String> keysItr = itemObj.keys();
            while (keysItr.hasNext()) {
                String key = keysItr.next();
                String value = generalFunc.getJsonValueStr(key, itemObj);
                bundle.putString(key, value);
            }
            //
            bundle.putString("ispriceshow", isPriceShow);
            bundle.putString("lat", latitude);
            bundle.putString("long", longitude);
            new ActUtils(act).startActForResult(RestaurantAllDetailsNewActivity.class, bundle, 111);
        }
    }

    public interface onCallBack {
        void onSetLocation(@NonNull String address, @NonNull String latitude, @NonNull String longitude);
    }
}