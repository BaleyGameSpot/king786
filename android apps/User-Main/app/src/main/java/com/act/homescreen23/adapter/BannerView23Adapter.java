package com.act.homescreen23.adapter;

import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.act.UberXHomeActivity;
import com.buddyverse.main.databinding.Item23BannerItemBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class BannerView23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_BANNER_NORMAL = 0;
    private final int TYPE_BANNER_SCROLL = 1;

    private final UberXHomeActivity mActivity;
    private final JSONObject mItemObject;
    @Nullable
    private JSONArray mBannerArray;
    private final OnClickListener listener;

    private final int v11sdp, v15sdp;

    public BannerView23Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @NonNull OnClickListener listener) {
        this.mActivity = activity;
        this.mItemObject = itemObject;
        if (itemObject.has("imagesArr")) {
            this.mBannerArray = mActivity.generalFunc.getJsonArray("imagesArr", itemObject);
        }
        this.listener = listener;

        this.v11sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._11sdp);
        this.v15sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_BANNER_SCROLL) {
            return new ScrollViewHolder(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new NormalViewHolder(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mBannerObject = mActivity.generalFunc.getJsonObject(mBannerArray, position);
        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);

        if (holder instanceof NormalViewHolder vHolder) {

            ////
            double ratio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("vImageRatio", mBannerObject));
            int bWidth = (int) Utils.getScreenPixelWidth(mActivity);
            bWidth = HomeUtils.getExtraSpace(mActivity, layoutDetailsObj, bWidth, v15sdp);
            int bHeight = (int) (bWidth / ratio);

            RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) vHolder.binding.bannerImgView.getLayoutParams();
            bannerLayoutParams.width = bWidth;
            bannerLayoutParams.height = bHeight;
            vHolder.binding.bannerImgView.setLayoutParams(bannerLayoutParams);

            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mBannerObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.bannerImgView, mUrl, 0, true, bWidth, bHeight);

            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof ScrollViewHolder vHolder) {

            ////
            double ratio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("vImageRatio", mBannerObject));
            int sWidth = (int) Utils.getScreenPixelWidth(mActivity);

            if (mBannerArray != null && mBannerArray.length() == 1) {
                sWidth = sWidth - (v15sdp * 2);
            } else {
                sWidth = sWidth - mActivity.getResources().getDimensionPixelSize(R.dimen._50sdp);
            }

            int sHeight = (int) (sWidth / ratio);

            RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) vHolder.binding.bannerImgView.getLayoutParams();
            bannerLayoutParams.width = sWidth;
            bannerLayoutParams.height = sHeight;
            vHolder.binding.bannerImgView.setLayoutParams(bannerLayoutParams);

            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mBannerObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.bannerImgView, mUrl, R.color.imageBg, true, sWidth, sHeight);
            HomeUtils.imgCorner(mActivity.generalFunc, layoutDetailsObj, vHolder.binding.bannerImgView, mActivity.getResources().getDimensionPixelSize(R.dimen._6sdp));

            // item Space
            if (mActivity.generalFunc.isRTLmode()) {
                if (position == 0 || position == (mBannerArray.length() - 1)) {
                    HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.bannerImgView, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);
                } else {
                    HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, false, false, v11sdp, 0, 0);
                }
            } else {
                HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);
            }

            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

        }
    }

    @Override
    public int getItemViewType(int position) {
        JSONObject itemObject = mActivity.generalFunc.getJsonObject(mBannerArray, position);
        if (itemObject == null) {
            return 0;
        }

        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);
        int displayCount = GeneralFunctions.parseIntegerValue(0, mActivity.generalFunc.getJsonValueStr("displayCount", layoutDetailsObj));
        String isCarousel = mActivity.generalFunc.getJsonValueStr("isCarousel", layoutDetailsObj);

        if (displayCount == 1 && isCarousel.equalsIgnoreCase("Yes")) {
            return TYPE_BANNER_SCROLL;
        } else {
            return TYPE_BANNER_NORMAL;
        }
    }

    @Override
    public int getItemCount() {
        return mBannerArray != null ? mBannerArray.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class NormalViewHolder extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private NormalViewHolder(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class ScrollViewHolder extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private ScrollViewHolder(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onBannerItemClick(int position, JSONObject jsonObject);
    }
}