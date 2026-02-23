package com.act.homescreen23;

import android.annotation.SuppressLint;
import android.text.TextUtils;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.ViewDataBinding;
import androidx.recyclerview.widget.PagerSnapHelper;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.act.homescreen23.adapter.UFXSubCategory23ProAdapter;
import com.adapter.files.CommonBanner23Adapter;
import com.general.files.AutoSlideView;
import com.general.files.GeneralFunctions;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityUberXhome23Binding;
import com.buddyverse.main.databinding.ActivityUberXhome24Binding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Objects;

public class UFXServices23ProView {

    private final UberXHomeActivity mActivity;
    private final GeneralFunctions generalFunc;
    @Nullable
    private ActivityUberXhome23Binding binding23;
    @Nullable
    private ActivityUberXhome24Binding binding24;
    private final UFXSubCategory23ProAdapter ufxCatAdapter;

    private CommonBanner23Adapter commonBanner23Adapter;
    private final OnUFXServiceViewListener listener;
    private PagerSnapHelper mSnapHelper = null;

    private JSONArray mServiceArray = new JSONArray();

    private boolean isBidding = false, isVideoConsultEnable = false, isReadyData = false;
    private JSONObject mDataObject;
    private JSONArray mBannerArray;
    private String iVehicleCategoryId;

    public UFXServices23ProView(@NonNull UberXHomeActivity activity, @NonNull GeneralFunctions generalFunc, @NonNull ViewDataBinding viewDataBinding, JSONObject dataObject, OnUFXServiceViewListener listener) {
        this.mActivity = activity;
        this.generalFunc = generalFunc;
        this.listener = listener;

        if (dataObject.has("imagesArr")) {
            this.mBannerArray = mActivity.generalFunc.getJsonArray("imagesArr", dataObject);
        } else if (dataObject.has("BANNER_DATA")) {
            this.mBannerArray = mActivity.generalFunc.getJsonArray("BANNER_DATA", dataObject);
        }

        if (viewDataBinding instanceof ActivityUberXhome23Binding binding) {
            this.binding23 = binding;
            bannerAdapterView(binding.UFX23ProArea.rvBanner);
        } else if (viewDataBinding instanceof ActivityUberXhome24Binding binding) {
            this.binding24 = binding;
            bannerAdapterView(binding.UFX23ProArea.rvBanner);
        }

        this.ufxCatAdapter = new UFXSubCategory23ProAdapter(activity, mServiceArray);
        if (binding24 != null) {
            binding24.UFX23ProArea.rvUFXServices.addItemDecoration(new SpacesItemDecoration(1, mActivity.getResources().getDimensionPixelSize(R.dimen._12sdp), false));
            binding24.UFX23ProArea.rvUFXServices.setAdapter(ufxCatAdapter);
        } else if (binding23 != null) {
            binding23.UFX23ProArea.rvUFXServices.addItemDecoration(new SpacesItemDecoration(1, mActivity.getResources().getDimensionPixelSize(R.dimen._12sdp), false));
            binding23.UFX23ProArea.rvUFXServices.setAdapter(ufxCatAdapter);
        }

        //
        MButton btn_type2 = ((MaterialRippleLayout) (binding24 != null ? binding24.UFX23ProArea.btnType2 : Objects.requireNonNull(binding23).UFX23ProArea.btnType2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        btn_type2.setOnClickListener(v -> {

            if ((!Utils.checkText(mActivity.mSelectedLatitude) && mActivity.mSelectedLatitude.equalsIgnoreCase("0.0"))
                    || (!Utils.checkText(mActivity.mSelectedLongitude) && mActivity.mSelectedLongitude.equalsIgnoreCase("0.0"))) {
                generalFunc.showMessage(binding24 != null ? binding24.topArea : Objects.requireNonNull(binding23).topArea, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            } else {
                String SelectedVehicleTypeId, SelectedCategoryName;
                if (!ufxCatAdapter.multiServiceSelect.isEmpty()) {
                    SelectedVehicleTypeId = TextUtils.join(",", ufxCatAdapter.multiServiceSelect);
                    SelectedCategoryName = TextUtils.join(",", ufxCatAdapter.multiServiceCategorySelect);
                } else {
                    generalFunc.showMessage(binding24 != null ? binding24.topArea : Objects.requireNonNull(binding23).topArea, generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE_TXT"));
                    return;
                }
                listener.onSubmitButtonClick(SelectedVehicleTypeId, iVehicleCategoryId, SelectedCategoryName, isVideoConsultEnable, isBidding);
            }
        });

        //
        initializeView(dataObject);
    }

    private void bannerAdapterView(@NonNull RecyclerView rvBanner) {
        this.commonBanner23Adapter = new CommonBanner23Adapter(mActivity, generalFunc, mBannerArray);
        if (mSnapHelper == null) {
            if (rvBanner.getOnFlingListener() != null) {
                rvBanner.setOnFlingListener(null);
            }
            mSnapHelper = new PagerSnapHelper();
            mSnapHelper.attachToRecyclerView(rvBanner);
        }
        rvBanner.setAdapter(commonBanner23Adapter);

        new AutoSlideView(5 * 1000).setAutoSlideRV(rvBanner);
    }

    public void initializeView(JSONObject dataObject) {

        mDataObject = dataObject;

        if (binding24 != null) {
            binding24.Main23ProArea.setVisibility(View.GONE);
            binding24.UFX23ProArea.getRoot().setVisibility(View.VISIBLE);

            binding24.UFX23ProArea.headerAddressTxt.setText(binding24.headerAddressTxt.getText().toString());
            binding24.UFX23ProArea.selectServiceTxt.setVisibility(View.GONE);

        } else if (binding23 != null) {

            binding23.Main23ProArea.setVisibility(View.GONE);
            binding23.UFX23ProSPArea.getRoot().setVisibility(View.GONE);
            binding23.UFX23ProArea.getRoot().setVisibility(View.VISIBLE);

            binding23.UFX23ProArea.headerAddressTxt.setText(binding23.headerAddressTxt.getText().toString());
            binding23.UFX23ProArea.selectServiceTxt.setVisibility(View.GONE);
        }

        ufxCatAdapter.multiServiceSelect.clear();
        ufxCatAdapter.multiServiceCategorySelect.clear();

        if (mServiceArray != null) {
            mServiceArray = null;
            mServiceArray = new JSONArray();
            ufxCatAdapter.updateList(mServiceArray, false, false);
        }

        initializeView();
    }

    public void initializeView() {
        mActivity.getWindow().setStatusBarColor(ContextCompat.getColor(mActivity, R.color.appThemeColor_1));
        getSubCateGoryListCateGoryWise();
    }

    private void getSubCateGoryListCateGoryWise() {
        this.iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", mDataObject);

        String parentId = iVehicleCategoryId;

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getServiceCategories");
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", mActivity.mSelectedLatitude);
        parameters.put("vLongitude", mActivity.mSelectedLongitude);

        String eCatType = generalFunc.getJsonValueStr("eCatType", mDataObject);
        isBidding = eCatType != null && eCatType.equalsIgnoreCase("Bidding");
        if (isBidding) {
            parentId = generalFunc.getJsonValueStr("iBiddingId", mDataObject);
            parameters.put("eCatType", eCatType);
        }
        parameters.put("parentId", parentId);

        isVideoConsultEnable = mDataObject.has("isVideoConsultEnable") && generalFunc.getJsonValueStr("isVideoConsultEnable", mDataObject).equalsIgnoreCase("Yes");
        if (isVideoConsultEnable) {
            parameters.put("eForVideoConsultation", "Yes");
        }
        (binding24 != null ? binding24.UFX23ProArea.btnArea : Objects.requireNonNull(binding23).UFX23ProArea.btnArea).setVisibility(View.GONE);

        JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, generalFunc.retrieveValue("SubcategoryForAllCategory"));
        if (isBidding) {
            messageArray = generalFunc.getJsonArray(Utils.message_str, generalFunc.retrieveValue("SubcategoryForBiddingCategory"));
        } else if (isVideoConsultEnable) {
            messageArray = generalFunc.getJsonArray(Utils.message_str, generalFunc.retrieveValue("SubcategoryForVideoConsultCategory"));
        }
        isReadyData = false;
        if (messageArray != null) {
            for (int i = 0; i < messageArray.length(); i++) {
                JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp);
                if (iVehicleCategoryId.equalsIgnoreCase(parentId)) {
                    isReadyData = true;
                    responseHandling(true, obj_temp.toString());
                    break;
                }
            }
        }
        if (!isReadyData) {
            listener.onProcess(true);
        }

        ApiHandler.execute(mActivity, parameters, responseString -> {
            listener.onProcess(false);
            // data get Done

            responseHandling(false, responseString);

            listener.onProcess(false);
        });
    }

    @SuppressLint("SetTextI18n")
    private void responseHandling(boolean readyData, String responseString) {
        if (Utils.checkText(responseString)) {
            if (readyData || GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                if (binding24 != null) {
                    binding24.UFX23ProArea.btnArea.setVisibility(View.VISIBLE);
                    binding24.UFX23ProArea.selectServiceTxt.setVisibility(View.VISIBLE);
                    binding24.UFX23ProArea.selectServiceTxt.setText(generalFunc.getJsonValue("vParentCategoryName", responseString));

                } else if (binding23 != null) {
                    binding23.UFX23ProArea.btnArea.setVisibility(View.VISIBLE);
                    binding23.UFX23ProArea.selectServiceTxt.setVisibility(View.VISIBLE);
                    binding23.UFX23ProArea.selectServiceTxt.setText(generalFunc.getJsonValue("vParentCategoryName", responseString));
                }

                JSONObject mResponseObject = generalFunc.getJsonObject(responseString);
                if (mResponseObject != null) {
                    if (mResponseObject.has("imagesArr")) {
                        this.mBannerArray = mActivity.generalFunc.getJsonArray("imagesArr", mResponseObject);
                    } else if (mResponseObject.has("BANNER_DATA")) {
                        this.mBannerArray = mActivity.generalFunc.getJsonArray("BANNER_DATA", mResponseObject);
                    }
                }
                commonBanner23Adapter.updateData(mBannerArray);
                mServiceArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                if (mServiceArray != null) {

                    boolean isMultiSelect = false, isRadioSelection = false;
                    if (generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", mActivity.obj_userProfile).equalsIgnoreCase("PROVIDER")) {
                        isMultiSelect = true;
                    }
                    if (isBidding || isVideoConsultEnable) {
                        isMultiSelect = false;
                        isRadioSelection = true;
                    }
                    ufxCatAdapter.updateList(mServiceArray, isMultiSelect, isRadioSelection);
                    if (mServiceArray.length() == 0) {
                        if (binding24 != null) {
                            binding24.UFX23ProArea.servicesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_SERVICE_AVAIL"));
                        } else if (binding23 != null) {
                            binding23.UFX23ProArea.servicesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_SERVICE_AVAIL"));
                        }
                    }
                }
            } else {
                if (!isReadyData) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        } else {
            if (!isReadyData) {
                generalFunc.showError();
            }
        }
    }

    public interface OnUFXServiceViewListener {
        void onProcess(boolean isLoadingView);

        void onSubmitButtonClick(String selectedVehicleTypeId, String selectedCategoryName, String categoryName, boolean isVideoConsultEnable, boolean isBidding);
    }
}