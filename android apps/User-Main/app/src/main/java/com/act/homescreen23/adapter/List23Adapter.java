package com.act.homescreen23.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23ListBinding;
import com.buddyverse.main.databinding.Item23ListProDeliveryOnlyBinding;
import com.buddyverse.main.databinding.Item24BoxListBinding;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class List23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_LIST_1 = 0;
    private final int TYPE_LIST_2 = 1;
    private final int TYPE_LIST_3 = 2;
    private final UberXHomeActivity mActivity;
    private final OnClickListener listener;

    private final JSONObject mItemObject;
    @Nullable
    private final JSONArray mImagesArr;
    private final JSONObject layoutDetailsObj;

    private final int v31sdp;

    public List23Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @NonNull OnClickListener listener) {
        this.mActivity = activity;
        this.mItemObject = itemObject;
        this.mImagesArr = mActivity.generalFunc.getJsonArray("imagesArr", itemObject);
        this.layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", itemObject);
        this.listener = listener;

        this.v31sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._31sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_LIST_3) {
            return new ListViewHolderBoxItem(Item24BoxListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else if (viewType == TYPE_LIST_2) {
            return new ListViewHolderDeliveryOnly(Item23ListProDeliveryOnlyBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ListViewHolder(Item23ListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mServiceObject = mActivity.generalFunc.getJsonObject(mImagesArr, position);

        if (holder instanceof ListViewHolder vHolder) {

            //
            textViewArea(vHolder.binding.titleTxt, vHolder.binding.descriptionTxt, mServiceObject);
            // image
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.listImage, mUrl, R.color.imageBg, true, v31sdp, v31sdp);

            //-----------------------------
            vHolder.binding.mainArea.setOnClickListener(v -> listener.onListItemClick(position, mServiceObject));

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof ListViewHolderDeliveryOnly vHolder) {

            //
            textViewArea(vHolder.binding.titleTxt, vHolder.binding.descriptionTxt, mServiceObject);
            // image
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.listImage, mUrl, R.color.imageBg, true, v31sdp, v31sdp);

            /////////------------------------------------------------------------------------------
        } else if (holder instanceof ListViewHolderBoxItem vHolder) {

            //
            textViewArea(vHolder.binding.titleTxt, vHolder.binding.descriptionTxt, mServiceObject);
            // image
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.listImage, mUrl, R.drawable.ic_circle_image_bg, true, v31sdp, v31sdp);

            if (mActivity.generalFunc.isRTLmode()) {
                vHolder.binding.listImage.setRotation(180);
            }
            vHolder.binding.viewLine.setVisibility(position == ((mImagesArr != null ? mImagesArr.length() : 0) - 1) ? View.INVISIBLE : View.VISIBLE);

            //-----------------------------
            vHolder.binding.mainArea.setOnClickListener(v -> listener.onListItemClick(position, mServiceObject));

            /////////------------------------------------------------------------------------------
        }
    }

    private void textViewArea(MTextView txtTitle, MTextView txtSubTitle, JSONObject mServiceObject) {
        String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mServiceObject);
        String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", mServiceObject);

        String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
        String vSubTitleFont = mActivity.generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);

        String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
        String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);

        HomeUtils.manageTextView(mActivity, txtTitle, vTitle, vTitleFont, vTxtTitleColor);
        HomeUtils.manageTextView(mActivity, txtSubTitle, vSubtitle, vSubTitleFont, vTxtSubTitleColor);
    }

    @Override
    public int getItemViewType(int position) {
        if (mActivity.generalFunc.getJsonValueStr("eViewType", mItemObject).equalsIgnoreCase("ListBtnView")) {
            return TYPE_LIST_3;

        } else if (mActivity.generalFunc.getJsonValueStr("isListViewCompact", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            return TYPE_LIST_2;

        } else {
            return TYPE_LIST_1;
        }
    }

    @Override
    public int getItemCount() {
        return mImagesArr != null ? mImagesArr.length() : 0;
    }

    private static class ListViewHolder extends RecyclerView.ViewHolder {
        private final Item23ListBinding binding;

        private ListViewHolder(Item23ListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class ListViewHolderDeliveryOnly extends RecyclerView.ViewHolder {
        private final Item23ListProDeliveryOnlyBinding binding;

        private ListViewHolderDeliveryOnly(Item23ListProDeliveryOnlyBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class ListViewHolderBoxItem extends RecyclerView.ViewHolder {
        private final Item24BoxListBinding binding;

        private ListViewHolderBoxItem(Item24BoxListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onListItemClick(int position, JSONObject jsonObject);
    }
}