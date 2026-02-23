package com.act.homescreen24.adapter;

import android.content.res.ColorStateList;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.act.homescreen23.adapter.HomeUtils;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item24IconTextViewBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class IconTextView24Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final UberXHomeActivity mActivity;
    @Nullable
    private JSONArray mServicesArr;
    private final JSONObject layoutDetailsObj;
    @Nullable
    private final OnClickListener listener;
    private final int v6sdp;

    public IconTextView24Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @Nullable OnClickListener listener) {
        this.mActivity = activity;
        if (itemObject.has("imagesArr")) {
            this.mServicesArr = mActivity.generalFunc.getJsonArray("imagesArr", itemObject);
        }
        this.layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", itemObject);
        this.listener = listener;

        this.v6sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._6sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new BoxIconTextViewHolder(Item24IconTextViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mServiceObject = mActivity.generalFunc.getJsonObject(mServicesArr, position);

        if (holder instanceof BoxIconTextViewHolder vHolder) {
            // Icon Text View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mServiceObject);
            String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", mServiceObject);

            String vTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTitleFont", position), layoutDetailsObj);
            String vSubTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vSubTitleFont", position), layoutDetailsObj);

            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtTitleColor", position), layoutDetailsObj);
            String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtSubTitleColor", position), layoutDetailsObj);

            HomeUtils.manageTextView(mActivity, vHolder.binding.titleTxt, vTitle, vTitleFont, vTxtTitleColor);
            HomeUtils.manageTextView(mActivity, vHolder.binding.subTitleTxt, vSubtitle, vSubTitleFont, vTxtSubTitleColor);

            // item Space
            boolean isFirst = (position % 3 == 0);
            boolean isLast = (position % 3 == 2);
            int topSpace = 0;
            if (position > 2) {
                topSpace = (int) (v6sdp * 1.5);
            }
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, true, isFirst, isLast, v6sdp, topSpace, 0);

            HomeUtils.mainArea(position, mActivity, vHolder.binding.boxView, layoutDetailsObj);

            //
            if (mActivity.generalFunc.getJsonValueStr("showBackgroundShadow", mServiceObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.boxView.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_white_shadow));
                String vBgColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vBgColor", position), layoutDetailsObj);
                if (Utils.checkText(vBgColor) && !vBgColor.equalsIgnoreCase("#ffffff")) {
                    vHolder.binding.boxView.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(vBgColor)));
                }
            }

            //
            HomeUtils.imageSizeWish(mActivity, vHolder.binding.imgView, R.drawable.ic_circle_image_bg, layoutDetailsObj, mServiceObject);

            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });

            /////////------------------------------------------------------------------------------

        }
    }

    @Override
    public int getItemCount() {
        return mServicesArr != null ? mServicesArr.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class BoxIconTextViewHolder extends RecyclerView.ViewHolder {
        private final Item24IconTextViewBinding binding;

        private BoxIconTextViewHolder(Item24IconTextViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onServiceItemClick(int position, JSONObject jsonObject);
    }
}