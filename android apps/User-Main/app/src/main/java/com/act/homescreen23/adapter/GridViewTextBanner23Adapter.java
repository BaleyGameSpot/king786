package com.act.homescreen23.adapter;

import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23GridTextBannerViewBinding;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class GridViewTextBanner23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final UberXHomeActivity mActivity;
    private final JSONObject mItemObject;
    @Nullable
    private JSONArray mBannerArray;
    private final OnClickListener listener;

    private final int v11sdp, v15sdp, v50sdp;

    public GridViewTextBanner23Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @NonNull OnClickListener listener) {
        this.mActivity = activity;
        this.mItemObject = itemObject;
        if (itemObject.has("imagesArr")) {
            this.mBannerArray = mActivity.generalFunc.getJsonArray("imagesArr", itemObject);
        }
        this.listener = listener;

        this.v11sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._11sdp);
        this.v15sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._15sdp);
        this.v50sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._50sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new SimpleGridViewHolder(Item23GridTextBannerViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mBannerObject = mActivity.generalFunc.getJsonObject(mBannerArray, position);
        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);

        if (holder instanceof SimpleGridViewHolder vHolder) {
            int displayCount = GeneralFunctions.parseIntegerValue(0, mActivity.generalFunc.getJsonValueStr("displayCount", layoutDetailsObj));
            ///
            int bHeight = cardImageView(position, vHolder.binding.cardViewBanner, displayCount, layoutDetailsObj);
            // Text View
            textViewArea(position, vHolder.binding.txtTitle, vHolder.binding.subTextArea, vHolder.binding.subTextFullArea, displayCount, mBannerObject, layoutDetailsObj);
            // item Space
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);
            //
            if (vHolder.binding.subTextArea.getVisibility() == View.VISIBLE) {
                setMaxLines(vHolder.binding.txtTitle, vHolder.binding.subTextArea, vHolder.binding.rvServices, bHeight);
            } else if (vHolder.binding.subTextFullArea.getVisibility() == View.VISIBLE) {
                setMaxLines(vHolder.binding.txtTitle, vHolder.binding.subTextFullArea, vHolder.binding.rvServices, bHeight);
            }

            // Image
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mBannerObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.imgCategory, mUrl, 0, true, v50sdp, v50sdp);

            // Service List
            if (mActivity.generalFunc.getJsonArray("servicesArr", mBannerObject).length() > 0) {
                vHolder.binding.rvServices.setLayoutManager(new GridLayoutManager(mActivity, mActivity.generalFunc.getJsonArray("servicesArr", mBannerObject).length()));
                GridView23Adapter adapter = new GridView23Adapter(mActivity, mBannerObject, listener::onBannerItemClick);
                adapter.setItemSize(true, displayCount);
                vHolder.binding.rvServices.setAdapter(adapter);
            }

            //===========
            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

            /////////------------------------------------------------------------------------------
        }
    }

    private void setMaxLines(MTextView txtTitle, MTextView txtSubTitle, RecyclerView rvServices, int bHeight) {
        txtSubTitle.getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
            @Override
            public void onGlobalLayout() {
                txtSubTitle.getViewTreeObserver().removeOnGlobalLayoutListener(this);
                try {
                    new Handler(Looper.getMainLooper()).postDelayed(() -> {
                        int titleHeight = txtTitle.getMeasuredHeight();
                        int myHeight = txtSubTitle.getMeasuredHeight();
                        int rvHeight = rvServices.getMeasuredHeight();
                        int totalTextHeight = titleHeight + myHeight + rvHeight;

                        totalTextHeight = totalTextHeight + mActivity.getResources().getDimensionPixelSize(R.dimen._5sdp);

                        if (bHeight <= totalTextHeight) {
                            int extraHeight = bHeight - titleHeight - rvHeight;

                            int maxLines = 50;
                            for (int i = 1; i < maxLines; i++) {
                                if (extraHeight <= (txtSubTitle.getLineHeight() * i)) {
                                    maxLines = i;
                                    break;
                                }
                            }
                            maxLines = maxLines - 2;
                            if (maxLines > 0) {
                                txtSubTitle.setMaxLines(maxLines);
                            }
                        }

                    }, 100);
                } catch (Exception e) {
                    throw new RuntimeException(e);
                }
            }
        });
    }

    private int cardImageView(int pos, View cardView, int displayCount, JSONObject layoutDetailsObj) {
        double bRatio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("bannerViewRatio", layoutDetailsObj));

        int bWidth = (int) Utils.getScreenPixelWidth(mActivity);
        bWidth = HomeUtils.getExtraSpace(mActivity, layoutDetailsObj, bWidth, v15sdp);
        int bHeight = (int) (bWidth / bRatio);

        bWidth = bWidth / displayCount;

        RelativeLayout.LayoutParams bParams = (RelativeLayout.LayoutParams) cardView.getLayoutParams();
        bParams.width = bWidth;
        bParams.height = bHeight;
        cardView.setLayoutParams(bParams);

        HomeUtils.bannerBg(mActivity, pos, cardView, layoutDetailsObj);

        return bHeight;
    }

    private void textViewArea(int pos, MTextView txtTitle, MTextView txtCategoryOtherDesc, MTextView txtCategoryDesc, int displayCount, JSONObject mBannerObject, JSONObject layoutDetailsObj) {

        String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mBannerObject);
        String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", mBannerObject);

        String vTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTitleFont", pos), layoutDetailsObj);
        String vSubTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vSubTitleFont", pos), layoutDetailsObj);

        String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtTitleColor", pos), layoutDetailsObj);
        String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtSubTitleColor", pos), layoutDetailsObj);

        HomeUtils.manageTextView(mActivity, txtTitle, vTitle, vTitleFont, vTxtTitleColor);
        HomeUtils.manageTextView(mActivity, txtCategoryOtherDesc, vSubtitle, vSubTitleFont, vTxtSubTitleColor);
        HomeUtils.manageTextView(mActivity, txtCategoryDesc, vSubtitle, vSubTitleFont, vTxtSubTitleColor);

        if (displayCount == 1) {
            txtCategoryOtherDesc.setVisibility(View.VISIBLE);
            txtCategoryDesc.setVisibility(View.GONE);
        } else {
            txtCategoryOtherDesc.setVisibility(View.GONE);
            txtCategoryDesc.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public int getItemCount() {
        return mBannerArray != null ? mBannerArray.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////

    private static class SimpleGridViewHolder extends RecyclerView.ViewHolder {
        private final Item23GridTextBannerViewBinding binding;

        private SimpleGridViewHolder(Item23GridTextBannerViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onBannerItemClick(int position, JSONObject jsonObject);
    }
}