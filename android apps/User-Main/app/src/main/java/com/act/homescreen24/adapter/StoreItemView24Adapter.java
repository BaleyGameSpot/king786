package com.act.homescreen24.adapter;

import android.graphics.Color;
import android.graphics.Paint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.act.homescreen23.adapter.HomeUtils;
import com.general.files.GeneralFunctions;
import com.google.android.material.imageview.ShapeableImageView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item24StoreBinding;
import com.buddyverse.main.databinding.Item24StoreItemsBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class StoreItemView24Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_STORE = 0;
    private final int TYPE_STORE_ITEM = 1;

    private final UberXHomeActivity mActivity;
    private final JSONObject mItemObject;
    @Nullable
    private JSONArray mServicesArr;
    private final JSONObject layoutDetailsObj;
    @Nullable
    private final OnClickListener listener;

    private final int spanCount, hWidth, hHeight, vWidth, vHeight, v6sdp, mSpace;

    public StoreItemView24Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, int spanCount, @Nullable OnClickListener listener) {
        this.mActivity = activity;
        this.mItemObject = itemObject;
        if (itemObject.has("servicesArr")) {
            this.mServicesArr = mActivity.generalFunc.getJsonArray("servicesArr", itemObject);
        }
        this.layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);
        this.spanCount = spanCount;
        this.listener = listener;

        this.v6sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._6sdp);
        this.mSpace = (int) (v6sdp * 1.5);

        // horizontal
        this.hWidth = (int) ((int) Utils.getScreenPixelWidth(mActivity) / 2.7);
        this.hHeight = (int) (hWidth / 1.33);

        // vertical
        this.vWidth = ((int) Utils.getScreenPixelWidth(mActivity) / spanCount);
        this.vHeight = (int) (vWidth / 1.33);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_STORE_ITEM) {
            return new StoreItemViewHolder(Item24StoreItemsBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new StoreViewHolder(Item24StoreBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mServiceObject = mActivity.generalFunc.getJsonObject(mServicesArr, position);
        if (holder instanceof StoreViewHolder vHolder) {
            // image Card Area
            imageCardArea(position, vHolder.binding.mainArea, vHolder.binding.boxView, vHolder.binding.imgView, mServiceObject);

            //Text View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vCompany", mServiceObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.titleTxt, vTitle, vTitleFont, vTxtTitleColor);

            String vSubTitleFont = mActivity.generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);
            String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);

            //
            String vAvgRating = mActivity.generalFunc.getJsonValueStr("vAvgRating", mServiceObject);
            if (Utils.checkText(vAvgRating) && !vAvgRating.equalsIgnoreCase("0")) {
                HomeUtils.manageTextView(mActivity, vHolder.binding.restaurantRateTxt, vAvgRating, vSubTitleFont, vTxtSubTitleColor);
                vHolder.binding.ratingArea.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.viewArea.setVisibility(View.GONE);
                vHolder.binding.ratingArea.setVisibility(View.GONE);
            }

            //
            String restaurantOrderPrepareTime = mActivity.generalFunc.getJsonValueStr("Restaurant_OrderPrepareTime", mServiceObject);
            if (Utils.checkText(restaurantOrderPrepareTime)) {
                HomeUtils.manageTextView(mActivity, vHolder.binding.deliveryTimeTxt, restaurantOrderPrepareTime, vSubTitleFont, vTxtSubTitleColor);
                vHolder.binding.deliveryTimeTxt.setVisibility(View.VISIBLE);
                vHolder.binding.deliveryTimeImg.setVisibility(View.VISIBLE);
                vHolder.binding.deliveryTimeImg.setColorFilter(Color.parseColor(vTxtSubTitleColor));
            } else {
                vHolder.binding.viewArea.setVisibility(View.GONE);
                vHolder.binding.deliveryTimeTxt.setVisibility(View.GONE);
                vHolder.binding.deliveryTimeImg.setVisibility(View.GONE);
            }

            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof StoreItemViewHolder vHolder) {

            // image Card Area
            imageCardArea(position, vHolder.binding.mainArea, vHolder.binding.boxView, vHolder.binding.imgView, mServiceObject);

            //Text View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vItemType", mServiceObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.titleTxt, vTitle, vTitleFont, vTxtTitleColor);

            String strikeoutPrice = mActivity.generalFunc.getJsonValueStr("StrikeoutPrice", mServiceObject);
            String vSubTitleFont = mActivity.generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);
            String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.priceTxt, strikeoutPrice, vSubTitleFont, vTxtSubTitleColor);
            if (GeneralFunctions.parseDoubleValue(0, mActivity.generalFunc.getJsonValueStr("fOfferAmt", mServiceObject)) > 0) {
                vHolder.binding.priceTxt.setPaintFlags(vHolder.binding.priceTxt.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
                vHolder.binding.priceTxt.setTextColor(ContextCompat.getColor(mActivity, R.color.gray));

                String fDiscountPricewithsymbol = mActivity.generalFunc.getJsonValueStr("fDiscountPricewithsymbol", mServiceObject);
                HomeUtils.manageTextView(mActivity, vHolder.binding.offerPriceTxt, fDiscountPricewithsymbol, vSubTitleFont, vTxtSubTitleColor);
                vHolder.binding.offerPriceTxt.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.priceTxt.setPaintFlags(0);
                vHolder.binding.offerPriceTxt.setVisibility(View.GONE);
            }

            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });

            /////////------------------------------------------------------------------------------
        }

    }

    private void imageCardArea(int position, RelativeLayout mainArea, LinearLayout boxView, ShapeableImageView imgView, JSONObject mServiceObject) {
        boolean isVertical = mActivity.generalFunc.getJsonValueStr("eLayoutType", mItemObject).equalsIgnoreCase("vertical");

        //
        RelativeLayout.LayoutParams cParams = (RelativeLayout.LayoutParams) boxView.getLayoutParams();
        cParams.width = isVertical ? vWidth : hWidth;
        boxView.setLayoutParams(cParams);
        //
        LinearLayout.LayoutParams imgParams = (LinearLayout.LayoutParams) imgView.getLayoutParams();
        if (isVertical) {
            int eMerging;
            if (spanCount == 2) {
                eMerging = v6sdp * (spanCount + 4);
            } else {
                eMerging = v6sdp * (spanCount + 2);
            }
            imgParams.width = vWidth - eMerging;
            imgParams.height = vHeight - eMerging;
        } else {
            int eMerging = v6sdp * 2;
            imgParams.width = hWidth - eMerging;
            imgParams.height = hHeight - eMerging;
        }
        imgView.setLayoutParams(imgParams);

        //
        String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
        HomeUtils.loadImg(mActivity, imgView, mUrl, R.color.imageBg, true, imgParams.width, imgParams.height);
        HomeUtils.imgCorner(mActivity.generalFunc, layoutDetailsObj, imgView, v6sdp);

        if (isVertical) {
            // item Space
            boolean isFirst = (position % spanCount == 0);
            boolean isLast = (position % spanCount == (spanCount - 1));
            int topSpace = 0;
            if (position > (spanCount - 1)) {
                topSpace = mSpace;
            }
            HomeUtils.itemSpace(mActivity.generalFunc, mainArea, true, isFirst, isLast, mSpace / 2, topSpace, 0);
        } else {
            if (mServicesArr != null) {
                if (mActivity.generalFunc.isRTLmode()) {
                    if (position == 0 || position == (mServicesArr.length() - 1)) {
                        HomeUtils.itemSpace(mActivity.generalFunc, boxView, false, position == 0, position == (mServicesArr.length() - 1), mSpace, 0, 0);
                    } else {
                        HomeUtils.itemSpace(mActivity.generalFunc, mainArea, false, false, false, mSpace, 0, 0);
                    }
                } else {
                    HomeUtils.itemSpace(mActivity.generalFunc, mainArea, false, position == 0, position == (mServicesArr.length() - 1), mSpace, 0, 0);
                }
            }
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (mActivity.generalFunc.getJsonValueStr("eServiceType", mItemObject).equalsIgnoreCase("DeliverAllItems")) {
            return TYPE_STORE_ITEM;
        } else {
            return TYPE_STORE;
        }
    }

    @Override
    public int getItemCount() {
        return mServicesArr != null ? mServicesArr.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class StoreViewHolder extends RecyclerView.ViewHolder {
        private final Item24StoreBinding binding;

        private StoreViewHolder(Item24StoreBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class StoreItemViewHolder extends RecyclerView.ViewHolder {
        private final Item24StoreItemsBinding binding;

        private StoreItemViewHolder(Item24StoreItemsBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onServiceItemClick(int position, JSONObject jsonObject);
    }
}