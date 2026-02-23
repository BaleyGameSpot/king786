package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.act.homescreen23.adapter.HomeUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemCommon23BannerBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.jetbrains.annotations.Nullable;
import org.json.JSONArray;
import org.json.JSONObject;

public class CommonBanner23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    @Nullable
    private JSONArray mBannerArray;
    private final int v7sdp, v11sdp, v15sdp, v50sdp;

    public CommonBanner23Adapter(@NonNull Context context, @NonNull GeneralFunctions generalFunc, @Nullable JSONArray mBannerArray) {
        this.mContext = context;
        this.generalFunc = generalFunc;
        this.mBannerArray = mBannerArray;

        this.v7sdp = mContext.getResources().getDimensionPixelSize(R.dimen._7sdp);
        this.v11sdp = mContext.getResources().getDimensionPixelSize(R.dimen._11sdp);
        this.v15sdp = mContext.getResources().getDimensionPixelSize(R.dimen._15sdp);
        this.v50sdp = mContext.getResources().getDimensionPixelSize(R.dimen._50sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new NormalViewHolder(ItemCommon23BannerBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mBannerObject = generalFunc.getJsonObject(mBannerArray, position);

        if (holder instanceof NormalViewHolder vHolder) {

            ///
            int sWidth = (int) Utils.getScreenPixelWidth(mContext);
            if (mBannerArray != null && mBannerArray.length() == 1) {
                sWidth = sWidth - (v15sdp * 2);
            } else {
                sWidth = sWidth - v50sdp;
            }

            int sHeight = (int) (sWidth / 2.33);

            ////
            LinearLayout.LayoutParams bannerLayoutParams = (LinearLayout.LayoutParams) vHolder.binding.bannerImgView.getLayoutParams();
            bannerLayoutParams.width = sWidth;
            bannerLayoutParams.height = sHeight;
            vHolder.binding.bannerImgView.setLayoutParams(bannerLayoutParams);

            String mUrl = generalFunc.getJsonValueStr("vImage", mBannerObject);
            HomeUtils.loadImg(mContext, vHolder.binding.bannerImgView, mUrl, R.color.imageBg, true, sWidth, sHeight);
            HomeUtils.imgCorner(generalFunc, null, vHolder.binding.bannerImgView, v7sdp);

            // item Space
            HomeUtils.itemSpace(generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);
        }
    }

    @Override
    public int getItemCount() {
        return mBannerArray != null ? mBannerArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(@Nullable JSONArray mBannerArray) {
        this.mBannerArray = mBannerArray;
        notifyDataSetChanged();
    }

    /////////////////////////////-----------------
    private static class NormalViewHolder extends RecyclerView.ViewHolder {
        private final ItemCommon23BannerBinding binding;

        private NormalViewHolder(ItemCommon23BannerBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}