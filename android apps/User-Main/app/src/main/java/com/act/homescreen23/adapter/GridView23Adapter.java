package com.act.homescreen23.adapter;

import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
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
import com.general.files.GeneralFunctions;
import com.google.android.material.imageview.ShapeableImageView;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23GridBinding;
import com.buddyverse.main.databinding.Item23GridItemBinding;
import com.buddyverse.main.databinding.Item23ServiceListItemBinding;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class GridView23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_SERVICE = 0;
    private final int TYPE_BOX = 1;
    private final int TYPE_BOX_IMAGE = 2;

    private final UberXHomeActivity mActivity;
    private final JSONObject mItemObject;
    @Nullable
    private JSONArray mServicesArr;
    private final JSONObject layoutDetailsObj;
    @Nullable
    private final OnClickListener listener;

    private boolean isReSize;
    private int displayCount;
    private final int v6sdp;

    public GridView23Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @Nullable OnClickListener listener) {
        this.mActivity = activity;
        this.mItemObject = itemObject;
        if (itemObject.has("servicesArr")) {
            this.mServicesArr = mActivity.generalFunc.getJsonArray("servicesArr", itemObject);
        }
        this.layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);
        this.listener = listener;

        this.v6sdp = mActivity.getResources().getDimensionPixelSize(R.dimen._6sdp);
    }

    public void setItemSize(boolean isReSize, int displayCount) {
        this.isReSize = isReSize;
        this.displayCount = displayCount;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_BOX) {
            return new BoxViewHolder(Item23GridItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_BOX_IMAGE) {
            return new BoxImageViewHolder(Item23GridBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else {
            return new SimpleGridViewHolder(Item23ServiceListItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mServiceObject = mActivity.generalFunc.getJsonObject(mServicesArr, position);

        if (holder instanceof BoxViewHolder vHolder) {

            //
            if (ServiceModule.isServiceProviderOnly() || (mActivity.generalFunc.isDeliverOnlyEnabled() && ServiceModule.DeliverAll)) {
                vHolder.binding.boxView.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.grid_bg_sp));

                String vBgColor = mActivity.generalFunc.getJsonValueStr("vBgColor", layoutDetailsObj);
                if (Utils.checkText(vBgColor)) {
                    GradientDrawable drawable = (GradientDrawable) vHolder.binding.boxView.getBackground();
                    drawable.setColor(Color.parseColor(vBgColor));
                }
            }

            // Box View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mServiceObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);

            HomeUtils.manageTextView(mActivity, vHolder.binding.txtCategoryName, vTitle, vTitleFont, vTxtTitleColor);

            // item Space
            boolean isFirst = (position % 3 == 0);
            boolean isLast = (position % 3 == 2);
            int topSpace = 0;
            if (position > 2) {
                topSpace = (int) (v6sdp * 1.5);
            }
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, true, isFirst, isLast, v6sdp, topSpace, 0);

            //
            int sWidth = vHolder.binding.imgCategory.getMeasuredWidth();
            int sHeight = vHolder.binding.imgCategory.getMeasuredHeight();
            //
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.imgCategory, mUrl, R.drawable.ic_circle_image_bg, true, sWidth, sHeight);

            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof BoxImageViewHolder vHolder) {

            // Box Image View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mServiceObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
            HomeUtils.manageTextView(mActivity, vHolder.binding.txtCategoryName, vTitle, vTitleFont, vTxtTitleColor);

            //
            int sWidth = (int) Utils.getScreenPixelWidth(mActivity);
            sWidth = sWidth / mServicesArr.length();
            sWidth = sWidth - v6sdp * mServicesArr.length();

            LinearLayout.LayoutParams iParams = (LinearLayout.LayoutParams) vHolder.binding.gridImage.getLayoutParams();
            iParams.width = sWidth;
            iParams.height = sWidth;
            vHolder.binding.gridImage.setLayoutParams(iParams);

            //
            String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
            HomeUtils.loadImg(mActivity, vHolder.binding.gridImage, mUrl, R.color.imageBg, true, iParams.width, iParams.height);
            vHolder.binding.gridImage.setShapeAppearanceModel(vHolder.binding.gridImage.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, v6sdp).build());

            //
            RelativeLayout.LayoutParams vParams = (RelativeLayout.LayoutParams) vHolder.binding.viewArea.getLayoutParams();
            vParams.width = iParams.width;
            vParams.height = iParams.height;
            vHolder.binding.viewArea.setLayoutParams(vParams);
            vHolder.binding.viewArea.setAlpha(GeneralFunctions.parseFloatValue(1.0f, mActivity.generalFunc.getJsonValueStr("vBgOpacity", layoutDetailsObj)));
            vHolder.binding.viewArea.setShapeAppearanceModel(vHolder.binding.gridImage.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, v6sdp).build());

            // item Space
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, true, position == 0, position == (mServicesArr.length() - 1), v6sdp, 0, 0);
            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof SimpleGridViewHolder vHolder) {

            // Service View
            String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mServiceObject);
            String vTitleFont = mActivity.generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
            String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);

            HomeUtils.manageTextView(mActivity, vHolder.binding.txtServiceName, vTitle, vTitleFont, vTxtTitleColor);

            // Service Description View
            if (mActivity.generalFunc.getJsonValueStr("isBoxDetailView", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                vHolder.binding.txtServiceDesc.setVisibility(View.VISIBLE);

                String vSubTitle = mActivity.generalFunc.getJsonValueStr("vSubTitle", mServiceObject);
                String vSubTitleFont = mActivity.generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);
                String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);

                HomeUtils.manageTextView(mActivity, vHolder.binding.txtServiceDesc, vSubTitle, vSubTitleFont, vTxtSubTitleColor);

                // item Space
                HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, true, position == 0, position == (mServicesArr.length() - 1), v6sdp, 0, 0);

                vHolder.binding.txtServiceName.setTextAlignment(View.TEXT_ALIGNMENT_TEXT_START);
            } else {
                vHolder.binding.txtServiceName.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
                vHolder.binding.txtServiceDesc.setVisibility(View.GONE);

            }
            if (mActivity.generalFunc.getJsonValueStr("showIconBorder", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setPadding(0, 0, 0, (int) (v6sdp * 1.7));
                vHolder.binding.serviceImg.setPadding(v6sdp, v6sdp, v6sdp, v6sdp);
            }

            //
            setReSize(vHolder.binding.txtServiceName, vHolder.binding.imgBgArea, vHolder.binding.serviceImg, mServiceObject);

            if (mActivity.generalFunc.getJsonValueStr("eLayoutType", mItemObject).equalsIgnoreCase("vertical")) {
                // item Space
                boolean isFirst = (position % 4 == 0);
                boolean isLast = (position % 4 == 2);
                int topSpace = 0;
                if (position > 3) {
                    topSpace = (int) (v6sdp * 1.5);
                }
                HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, true, isFirst, isLast, v6sdp, topSpace, 0);
            }

            //
            HomeUtils.imgRadiusAndBGShadow(mActivity, vHolder.binding.serviceImg, vHolder.binding.imgBgArea, mServiceObject, layoutDetailsObj, v6sdp);

            //
            vHolder.binding.mainArea.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onServiceItemClick(position, mServiceObject);
                }
            });
        }
    }

    private void setReSize(MTextView txtServiceName, View imgBgArea, ShapeableImageView sImg, JSONObject mServiceObject) {
        int bWidth = 0, bHeight = 0, mMaxWidth = 0;

        if (isReSize) {
            if (displayCount == 1) {
                bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._35sdp);
                bHeight = mActivity.getResources().getDimensionPixelSize(R.dimen._35sdp);

            } else if (displayCount == 2) {
                bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._25sdp);
                bHeight = mActivity.getResources().getDimensionPixelSize(R.dimen._25sdp);
            }
            mMaxWidth = bWidth * 2;

        } else {
            if (mActivity.generalFunc.getJsonValueStr("isBoxDetailView", layoutDetailsObj).equalsIgnoreCase("Yes")
                    || mActivity.generalFunc.getJsonValueStr("isBoxImageView", layoutDetailsObj).equalsIgnoreCase("Yes")) {
                int sWidth = (int) Utils.getScreenPixelWidth(mActivity);
                bWidth = sWidth / ((mServicesArr.length() > 0 && mServicesArr.length() < 3) ? 3 : mServicesArr.length());
                bWidth = bWidth - v6sdp * ((mServicesArr.length() > 0 && mServicesArr.length() < 3) ? 3 : mServicesArr.length());

                bHeight = bWidth;
                mMaxWidth = (int) (bWidth * 2);

            } else {
                bWidth = mActivity.getResources().getDimensionPixelSize(R.dimen._47sdp);
                bHeight = mActivity.getResources().getDimensionPixelSize(R.dimen._47sdp);
                mMaxWidth = (int) (bWidth * 1.5);
            }
        }

        LinearLayout.LayoutParams imageParams = (LinearLayout.LayoutParams) sImg.getLayoutParams();
        imageParams.width = bWidth;
        imageParams.height = bHeight;
        sImg.setLayoutParams(imageParams);

        //
        txtServiceName.setWidth(mMaxWidth);

        String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mServiceObject);
        HomeUtils.loadImg(mActivity, sImg, mUrl, isReSize ? R.drawable.ic_circle_image_bg : R.color.imageBg, true, bWidth, bHeight);
    }

    @Override
    public int getItemViewType(int position) {
        JSONObject itemObject = mActivity.generalFunc.getJsonObject(mServicesArr, position);
        if (itemObject == null) {
            return 0;
        }

        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);
        if (mActivity.generalFunc.getJsonValueStr("isBoxStructure", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            return TYPE_BOX;

        } else if (mActivity.generalFunc.getJsonValueStr("isBoxImageView", layoutDetailsObj).equalsIgnoreCase("Yes")) {
            return TYPE_BOX_IMAGE;

        } else {
            return TYPE_SERVICE;
        }
    }

    @Override
    public int getItemCount() {
        return mServicesArr != null ? mServicesArr.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class BoxViewHolder extends RecyclerView.ViewHolder {
        private final Item23GridItemBinding binding;

        private BoxViewHolder(Item23GridItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class BoxImageViewHolder extends RecyclerView.ViewHolder {
        private final Item23GridBinding binding;

        private BoxImageViewHolder(Item23GridBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class SimpleGridViewHolder extends RecyclerView.ViewHolder {
        private final Item23ServiceListItemBinding binding;

        private SimpleGridViewHolder(Item23ServiceListItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onServiceItemClick(int position, JSONObject jsonObject);
    }
}