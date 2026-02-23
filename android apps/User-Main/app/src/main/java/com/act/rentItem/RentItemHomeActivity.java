package com.act.rentItem;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;

import androidx.core.content.ContextCompat;
import androidx.core.widget.NestedScrollView;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.SnapHelper;

import com.activity.ParentActivity;
import com.adapter.files.CommonBanner23Adapter;
import com.general.files.ActUtils;
import com.general.files.AutoSlideView;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.buddyverse.main.R;
import com.act.SearchLocationActivity;
import com.buddyverse.main.databinding.ActivityRentItemHomeBinding;
import com.act.rentItem.adapter.RentCategoryAdapter;
import com.act.rentItem.adapter.RentItemDataRecommendedAdapter;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemHomeActivity extends ParentActivity implements GetLocationUpdates.LocationUpdates, GetAddressFromLocation.AddressFound {

    private static final int DATA_UPDATE_REQ = 787848, DATA_FILTER_REQ = 787849;
    private ActivityRentItemHomeBinding binding;

    private GetLocationUpdates getLastLocation;
    private GetAddressFromLocation getAddressFromLocation;
    private String mAddress = "", mLatitude = "0.0", mLongitude = "0.0";
    private boolean isFirst = false, isFilter = true;

    private String iCategoryId = "", eType = "", mSearchText = "", subcategoryId = "", optionId = "";

    private CommonBanner23Adapter mBannerAdapter;
    private JSONArray bannerListArray;

    private RentCategoryAdapter rentCategoryAdapter;
    private final ArrayList<HashMap<String, String>> rentCategoryList = new ArrayList<>();

    private RentItemDataRecommendedAdapter mRentItemDataRecommendedAdapter;
    private final ArrayList<HashMap<String, String>> rentItemDataList = new ArrayList<>();
    private ServerTask currentWebTask;
    private String isBuySell;
    private String next_page_str = "1";
    boolean mIsLoading = false, isNextPageAvailable = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_rent_item_home);

        iCategoryId = getIntent().getStringExtra("iCategoryId");
        eType = getIntent().getStringExtra("eType");

        mLatitude = getIntent().getStringExtra("latitude");
        mLongitude = getIntent().getStringExtra("longitude");
        mAddress = getIntent().getStringExtra("address");

        setBuyRent();

        if (Utils.checkText(mLatitude) && Utils.checkText(mLongitude) && Utils.checkText(mAddress)) {
            isFirst = true;
            Location temploc = new Location("source");
            temploc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, mLatitude));
            temploc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, mLongitude));
            onLocationUpdate(temploc);
            binding.headerAddressTxt.setText(mAddress);
        } else {
            binding.headerAddressTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
            getLastLocation = new GetLocationUpdates(this, Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, false, this);
        }
        topAreaDataSet();
        bannerData();
        mainData();
    }

    private void setBuyRent() {
        binding.buySaleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BUY_TXT"));
        binding.rentTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_TXT"));
        addToClickHandler(binding.buySaleTxt);
        addToClickHandler(binding.rentTxt);
        setBuyRent(true);
    }

    private void setBuyRent(boolean isBuy) {
        if (isBuy) {
            isBuySell = "Yes";
            binding.buySaleTxt.setBackground(ContextCompat.getDrawable(this, generalFunc.isRTLmode() ? R.drawable.shadow_5dp_r : R.drawable.shadow_5dp_l));
            binding.buySaleTxt.setTextColor(getResources().getColor(R.color.white));

            binding.rentTxt.setBackground(null);
            binding.rentTxt.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
        } else {
            isBuySell = "No";
            binding.buySaleTxt.setBackground(null);
            binding.buySaleTxt.setTextColor(getResources().getColor(R.color.text23Pro_Dark));

            binding.rentTxt.setBackground(ContextCompat.getDrawable(this, generalFunc.isRTLmode() ? R.drawable.shadow_5dp_l : R.drawable.shadow_5dp_r));
            binding.rentTxt.setTextColor(getResources().getColor(R.color.white));
        }
    }

    private void topAreaDataSet() {
        addToClickHandler(binding.headerAddressTxt);

        if (generalFunc.isRTLmode()) {
            binding.backImgView.setRotation(180);
        }

        addToClickHandler(binding.backImgView);
        addToClickHandler(binding.filterArea);
        addToClickHandler(binding.imageCancel);
        binding.imageCancel.setVisibility(View.GONE);
        binding.loaderView.setVisibility(View.INVISIBLE);

        binding.searchTxtView.setHint(generalFunc.retrieveLangLBl("", "LBL_RENT_SEARCH"));
        binding.searchTxtView.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                mSearchText = s.toString();
                if (mSearchText.length() >= 3) {
                    binding.imageCancel.setVisibility(View.VISIBLE);
                    binding.loaderView.setVisibility(View.VISIBLE);
                } else {
                    if (mSearchText.length() == 0) {
                        binding.imageCancel.setVisibility(View.GONE);
                    } else {
                        binding.imageCancel.setVisibility(View.VISIBLE);
                    }
                    binding.loaderView.setVisibility(View.INVISIBLE);
                }
                if (mSearchText.length() == 0 || mSearchText.length() >= 3) {
                    getRentPostList(false, false, true);
                }
            }
        });
    }

    @SuppressLint("SetTextI18n")
    private void bannerData() {
        mBannerAdapter = new CommonBanner23Adapter(this, generalFunc, bannerListArray);
        binding.rvBanner.setAdapter(mBannerAdapter);
        SnapHelper mSnapHelper = new PagerSnapHelper();
        mSnapHelper.attachToRecyclerView(binding.rvBanner);

        new AutoSlideView(5 * 1000).setAutoSlideRV(binding.rvBanner);

        binding.itemBuySellRent.titleTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_SELL_ITEM_TXT"));
        binding.itemBuySellRent.newListingButton.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_NEW_LISTING_TXT"));
        addToClickHandler(binding.itemBuySellRent.newListingButton);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void mainData() {
        binding.swipeRefreshLayout.setOnRefreshListener(() -> {
            binding.swipeRefreshLayout.setRefreshing(false);
            next_page_str = "1";
            getRentPostList(false, false, true);
        });

        // category list
        rentCategoryAdapter = new RentCategoryAdapter(this, rentCategoryList, (position, selCatPos) -> {
            iCategoryId = rentCategoryList.get(position).get("iCategoryId");

            HashMap<String, String> updateData1 = rentCategoryList.get(position);
            updateData1.put("isCheck", "Yes");
            rentCategoryList.set(position, updateData1);

            if (selCatPos != position && selCatPos != -1) {
                HashMap<String, String> updateData = rentCategoryList.get(selCatPos);
                updateData.put("isCheck", "No");
                rentCategoryList.set(selCatPos, updateData);
                next_page_str = "1";
                getRentPostList(false, true, false);
            }
            rentCategoryAdapter.notifyDataSetChanged();
            binding.rvRentItemCategory.scrollToPosition(position);
        });
        binding.rvRentItemCategory.setAdapter(rentCategoryAdapter);


        /// main data
        if (eType.equalsIgnoreCase("RentEstate")) {
            binding.rentItemPostListHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ALL_REALESTATE_TXT"));
        } else if (eType.equalsIgnoreCase("RentCars")) {
            binding.rentItemPostListHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ALL_CARS_TXT"));
        } else if (eType.equalsIgnoreCase("RentItem")) {
            binding.rentItemPostListHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ALL_GENERALITEMS_TXT"));
        }
        mRentItemDataRecommendedAdapter = new RentItemDataRecommendedAdapter(this, generalFunc, 2, rentItemDataList, (position, mapData) -> {
            Utils.hideKeyboard(this);
            Bundle bn = new Bundle();
            bn.putBoolean("isFavouriteView", true);
            bn.putBoolean("isOwnerView", !generalFunc.getMemberId().equalsIgnoreCase(mapData.get("iUserId")));
            bn.putSerializable("rentEditHashMap", mapData);
            new ActUtils(this).startActForResult(RentItemReviewPostActivity.class, bn, DATA_UPDATE_REQ);
        });
        binding.rvRentItemRecommendation.setAdapter(mRentItemDataRecommendedAdapter);
        binding.nestedscrollview.setOnScrollChangeListener((NestedScrollView.OnScrollChangeListener) (v, scrollX, scrollY, oldScrollX, oldScrollY) -> {
            View getChild = v.getChildAt(v.getChildCount() - 1);
            if (getChild != null) {
                if ((scrollY >= (getChild.getMeasuredHeight() - v.getMeasuredHeight())) && scrollY > oldScrollY) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvRentItemRecommendation.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvRentItemRecommendation.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvRentItemRecommendation.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
                        binding.footerLoader.setVisibility(View.VISIBLE);
                        binding.rvRentItemRecommendation.stopScroll();
                        getRentPostList(false, false, false);

                    } else if (!isNextPageAvailable) {
                        binding.footerLoader.setVisibility(View.GONE);
                    }
                }
            }
        });
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        mLatitude = mLastLocation.getLatitude() + "";
        mLongitude = mLastLocation.getLongitude() + "";

        if (isFirst) {
            isFirst = false;
            next_page_str = "1";
            getRentPostList(true, false, false);
        } else {
            getAddressFromLocation = new GetAddressFromLocation(this, generalFunc);
            getAddressFromLocation.setLocation(mLastLocation.getLatitude(), mLastLocation.getLongitude());
            getAddressFromLocation.setAddressList(this);
            getAddressFromLocation.execute();
        }
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (Utils.checkText(address)) {
            mLatitude = latitude + "";
            mLongitude = longitude + "";
            binding.headerAddressTxt.setText(address);
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getRentPostList(boolean isLocationChanges, boolean isDataUpdate, boolean isSearch) {
        mIsLoading = false;
        if (isLocationChanges) {
            binding.mainDataArea.setVisibility(View.GONE);
            binding.loading.setVisibility(View.VISIBLE);

        }
        if (isDataUpdate) {
            binding.listArea.setVisibility(View.GONE);
            binding.DataLoadingArea.setVisibility(View.VISIBLE);
        }
        if (isSearch) {
            binding.loading.setVisibility(View.VISIBLE);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetRentPostForAllUser");
        parameters.put("iMemberId", generalFunc.getMemberId());

        parameters.put("iCategoryId", iCategoryId);
        parameters.put("search_keyword", mSearchText);

        parameters.put("passengerLat", "" + mLatitude);
        parameters.put("passengerLon", "" + mLongitude);
        parameters.put("eType", eType);

        parameters.put("iSubCategoryIds", subcategoryId);
        parameters.put("iSelectedOptionIds", optionId);

        parameters.put("isBuySell", isBuySell);
        parameters.put("page", next_page_str);

        if (currentWebTask != null) {
            currentWebTask.cancel(true);
            currentWebTask = null;
        }
        currentWebTask = ApiHandler.execute(this, parameters, responseString -> {
            String nextPage = generalFunc.getJsonValue("NextPage", responseString);

            currentWebTask = null;

            binding.swipeRefreshLayout.setRefreshing(false);

            binding.loaderView.setVisibility(View.INVISIBLE);
            binding.loading.setVisibility(View.GONE);
            if (Utils.checkText(mSearchText)) {
                Utils.hideKeyboard(this);
            }

            if (Utils.checkText(responseString)) {

                binding.mainDataArea.setVisibility(View.VISIBLE);
                binding.listArea.setVisibility(View.VISIBLE);
                binding.DataLoadingArea.setVisibility(View.GONE);
                binding.NoDataTxt.setVisibility(View.GONE);

                bannerListArray = generalFunc.getJsonArray("BANNER_DATA", responseString);
                mBannerAdapter.updateData(bannerListArray);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                    binding.rentItemCategoryHTxt.setText(generalFunc.getJsonValue("MainCatTitle", message));

                    //handling category
                    if (isFilter) {
                        isFilter = false;
                        JSONArray categoryArray = generalFunc.getJsonArray("CategoriesData", message);
                        rentCategoryList.clear();
                        if (categoryArray != null) {

                            for (int i = 0; i < categoryArray.length(); i++) {
                                JSONObject obj_tmp = generalFunc.getJsonObject(categoryArray, i);

                                HashMap<String, String> mapData = new HashMap<>();
                                MyUtils.createHashMap(generalFunc, mapData, obj_tmp);
                                if (i == 0) {
                                    mapData.put("isCheck", "Yes");
                                }

                                rentCategoryList.add(mapData);
                            }
                            binding.rentItemCategoryArea.setVisibility(View.VISIBLE);
                            rentCategoryAdapter.notifyDataSetChanged();
                        } else {
                            binding.rentItemCategoryArea.setVisibility(View.GONE);
                        }
                    }

                    //handling category Rent Item Data
                    JSONArray rentItemDataArray = generalFunc.getJsonArray("RentItemData", message);
                    if (next_page_str.equals("1")) {
                        rentItemDataList.clear();
                    }
                    if (rentItemDataArray != null && rentItemDataArray.length() > 0) {
                        MyUtils.createArrayListJSONArray(generalFunc, rentItemDataList, rentItemDataArray);
                        if (!nextPage.equals("") && !nextPage.equals("0")) {
                            next_page_str = nextPage;
                            isNextPageAvailable = true;
                        } else {
                            removeNextPageConfig();
                        }
                    } else {
                        removeNextPageConfig();
                        binding.NoDataTxt.setVisibility(View.VISIBLE);
                        binding.NoDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, message)));
                    }
                    mRentItemDataRecommendedAdapter.notifyDataSetChanged();
                } else {
                    if (rentCategoryList.size() == 0) {
                        if (!generalFunc.getJsonValue(Utils.message_str_one, responseString).equalsIgnoreCase("")) {
                            binding.NoDataTxt.setVisibility(View.VISIBLE);
                            binding.NoDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str_one, responseString)));
                        }
                        rentCategoryList.clear();
                    }
                    mRentItemDataRecommendedAdapter.notifyDataSetChanged();
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void removeNextPageConfig() {
        next_page_str = "1";
        isNextPageAvailable = false;
        mIsLoading = false;
        binding.footerLoader.setVisibility(View.GONE);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
        try {
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {

            binding.headerAddressTxt.setText(data.getStringExtra("Address"));
            mLatitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            mLongitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");

            if (!mLatitude.equalsIgnoreCase("0.0") && !mLongitude.equalsIgnoreCase("0.0")) {
                next_page_str = "1";
                getRentPostList(true, false, false);
                isFilter = true;
            }
        } else if (requestCode == 111 && resultCode == RESULT_OK) {
            next_page_str = "1";
            getRentPostList(true, false, false);
        } else if (requestCode == DATA_UPDATE_REQ) {
            next_page_str = "1";
            getRentPostList(false, resultCode == RESULT_OK, false);
        } else if (requestCode == DATA_FILTER_REQ && resultCode == RESULT_OK && data != null) {
            subcategoryId = data.getStringExtra("subcategoryId");
            optionId = data.getStringExtra("optionId");
            next_page_str = "1";
            getRentPostList(false, true, false);
        }
    }

    @SuppressLint("NonConstantResourceId")
    public void onClick(View view) {
        Utils.hideKeyboard(this);
        int i = view.getId();
        if (i == binding.backImgView.getId()) {
            onBackPressed();

        } else if (i == binding.headerAddressTxt.getId()) {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            bn.putBoolean("isaddressview", true);
            bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, mLatitude));
            bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, mLongitude));
            bn.putString("address", mAddress);
            bn.putString("eSystem", Utils.eSystem_Type);
            new ActUtils(this).startActForResult(SearchLocationActivity.class, bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);

        } else if (i == binding.filterArea.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iCategoryId", iCategoryId);
            bn.putString("eType", eType);
            bn.putString("subcategoryId", subcategoryId);
            bn.putString("optionId", optionId);
            new ActUtils(this).startActForResult(RentItemFilterActivity.class, bn, DATA_FILTER_REQ);

        } else if (i == binding.imageCancel.getId()) {
            binding.searchTxtView.setText("");

        } else if (i == binding.itemBuySellRent.newListingButton.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iCategoryId", iCategoryId);
            bn.putString("eType", eType);
            new ActUtils(this).startActWithData(RentItemNewPostActivity.class, bn);

        } else if (i == binding.rentTxt.getId()) {
            setBuyRent(false);
            next_page_str = "1";
            getRentPostList(false, true, false);
        } else if (i == binding.buySaleTxt.getId()) {
            setBuyRent(true);
            next_page_str = "1";
            getRentPostList(false, true, false);
        }
    }


}