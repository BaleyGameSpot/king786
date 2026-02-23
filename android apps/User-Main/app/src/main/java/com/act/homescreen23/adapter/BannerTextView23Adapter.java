package com.act.homescreen23.adapter;

import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23BannerItemBinding;
import com.buddyverse.main.databinding.Item23BannerTextViewBottomRightBinding;
import com.buddyverse.main.databinding.Item23BannerTextViewCenterLeftBinding;
import com.buddyverse.main.databinding.Item23BannerTextViewCenterRightBinding;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class BannerTextView23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_CENTER_LEFT = 1;
    private final int TYPE_CENTER_RIGHT = 2;
    private final int TYPE_CENTER_TOP = 3;
    private final int TYPE_CENTER_BOTTOM = 4;

    private final int TYPE_BOTTOM_RIGHT = 5;
    private final int TYPE_BOTTOM_LEFT = 6;
    private final int TYPE_TOP_RIGHT = 7;
    private final int TYPE_TOP_LEFT = 8;

    private final UberXHomeActivity mActivity;
    private final JSONObject mItemObject;
    @Nullable
    private JSONArray mBannerArray;
    private final OnClickListener listener;

    private final int v11sdp, v15sdp;

    public BannerTextView23Adapter(@NonNull UberXHomeActivity activity, @NonNull JSONObject itemObject, @NonNull OnClickListener listener) {
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
        if (viewType == TYPE_CENTER_LEFT) {
            return new CenterLeftVH(Item23BannerTextViewCenterLeftBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_CENTER_RIGHT) {
            return new CenterRightVH(Item23BannerTextViewCenterRightBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_CENTER_TOP) {
            return new CenterTopVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_CENTER_BOTTOM) {
            return new CenterBottomVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_BOTTOM_RIGHT) {
            return new BottomRightVH(Item23BannerTextViewBottomRightBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_BOTTOM_LEFT) {
            return new BottomLeftVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_TOP_RIGHT) {
            return new TopRightVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else if (viewType == TYPE_TOP_LEFT) {
            return new TopLeftVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));

        } else {
            return new TopLeftVH(Item23BannerItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        JSONObject mBannerObject = mActivity.generalFunc.getJsonObject(mBannerArray, position);
        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);

        if (holder instanceof CenterLeftVH vHolder) {
            ////
            int bHeight = cardImageView(position, vHolder.binding.cardViewBanner, null, mBannerObject, layoutDetailsObj);
            //
            imageView(bHeight, vHolder.binding.sImgView, mBannerObject, true);
            //
            HomeUtils.imageSpace(mActivity, vHolder.binding.sImgView, layoutDetailsObj);
            //
            textViewArea(position, vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, mBannerObject, layoutDetailsObj);
            //
            bookNowArea(vHolder.binding.txtBookNow, mBannerObject, layoutDetailsObj);
            //
            HomeUtils.promotionalTagArea(mActivity, vHolder.binding.txtPromotionalTag, mBannerObject);
            //
            setMaxLines(vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, bHeight);
            //
            HomeUtils.textAreaSpace(mActivity, vHolder.binding.llTextArea, mBannerObject, layoutDetailsObj, true);
            // item Space
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);

            //===========
            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof CenterRightVH vHolder) {
            ////
            int bHeight = cardImageView(position, vHolder.binding.cardViewBanner, null, mBannerObject, layoutDetailsObj);
            //
            imageView(bHeight, vHolder.binding.sImgView, mBannerObject, true);
            //
            HomeUtils.imageSpace(mActivity, vHolder.binding.sImgView, layoutDetailsObj);
            //
            textViewArea(position, vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, mBannerObject, layoutDetailsObj);
            //
            bookNowArea(vHolder.binding.txtBookNow, mBannerObject, layoutDetailsObj);
            //
            HomeUtils.promotionalTagArea(mActivity, vHolder.binding.txtPromotionalTag, mBannerObject);
            //
            setMaxLines(vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, bHeight);
            //
            HomeUtils.textAreaSpace(mActivity, vHolder.binding.llTextArea, layoutDetailsObj, layoutDetailsObj, false);
            // item Space
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);

            //===========
            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

            /////////------------------------------------------------------------------------------

        } else if (holder instanceof BottomRightVH vHolder) {
            ////
            int bHeight = cardImageView(position, vHolder.binding.cardViewBanner, vHolder.binding.anchorView, mBannerObject, layoutDetailsObj);
            //
            imageView(bHeight, vHolder.binding.sImgView, mBannerObject, false);
            //
            HomeUtils.imageSpace(mActivity, vHolder.binding.sImgView, layoutDetailsObj);
            //
            textViewArea(position, vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, mBannerObject, layoutDetailsObj);
            //
            HomeUtils.promotionalTagArea(mActivity, vHolder.binding.txtPromotionalTag, mBannerObject);
            // item Space
            HomeUtils.itemSpace(mActivity.generalFunc, vHolder.binding.mainArea, false, position == 0, position == (mBannerArray.length() - 1), v11sdp, 0, 0);

            //
            String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", mBannerObject);
            LinearLayout.LayoutParams subTAreaParams = (LinearLayout.LayoutParams) vHolder.binding.subTextArea.getLayoutParams();
            if (Utils.checkText(vSubtitle)) {
                subTAreaParams.weight = 8f;
            } else {
                subTAreaParams.weight = 2f;
            }
            vHolder.binding.subTextArea.setLayoutParams(subTAreaParams);

            //
            setMaxLines(vHolder.binding.txtTitle, vHolder.binding.txtSubTitle, bHeight);

            //===========
            if (mActivity.generalFunc.getJsonValueStr("isClickable", mBannerObject).equalsIgnoreCase("Yes")) {
                vHolder.binding.mainArea.setOnClickListener(v -> listener.onBannerItemClick(position, mBannerObject));
            }

            /////////------------------------------------------------------------------------------

        }
    }

    private void setMaxLines(MTextView txtTitle, MTextView txtSubTitle, int bHeight) {
        txtSubTitle.getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
            @Override
            public void onGlobalLayout() {
                txtSubTitle.getViewTreeObserver().removeOnGlobalLayoutListener(this);
                try {
                    new Handler(Looper.getMainLooper()).postDelayed(() -> {
                        int titleHeight = txtTitle.getMeasuredHeight();
                        int myHeight = txtSubTitle.getMeasuredHeight();
                        int totalTextHeight = titleHeight + myHeight;

                        totalTextHeight = totalTextHeight + mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp);

                        if (bHeight <= totalTextHeight) {
                            int extraHeight = bHeight - titleHeight;

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

    private int cardImageView(int pos, View cardView, @Nullable View anchorView, JSONObject mBannerObject, JSONObject layoutDetailsObj) {
        int displayCount = GeneralFunctions.parseIntegerValue(0, mActivity.generalFunc.getJsonValueStr("displayCount", layoutDetailsObj));
        double bRatio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("bannerViewRatio", layoutDetailsObj));

        int bWidth = (int) Utils.getScreenPixelWidth(mActivity);
        bWidth = HomeUtils.getExtraSpace(mActivity, layoutDetailsObj, bWidth, v15sdp);
        int bHeight = 0;
        if (bRatio > 0) {
            bHeight = (int) (bWidth / bRatio);
        }

        if (displayCount > 0) {
            bWidth = bWidth / displayCount;
        }

        RelativeLayout.LayoutParams bParams = (RelativeLayout.LayoutParams) cardView.getLayoutParams();
        bParams.width = bWidth;
        bParams.height = bHeight;
        cardView.setLayoutParams(bParams);

        if (anchorView != null) {
            anchorView.setVisibility(View.GONE);
            if (mActivity.generalFunc.getJsonValueStr("isShowShadow", mBannerObject).equalsIgnoreCase("Yes")) {
                cardView.setClipToOutline(true);
                cardView.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_white_line_7sdp));
                if (mActivity.generalFunc.getJsonValueStr("eShowLeadingAnchor", mBannerObject).equalsIgnoreCase("Yes")) {
                    anchorView.setVisibility(View.VISIBLE);
                    String bColor = mActivity.generalFunc.getJsonValueStr("leftBorderColor", mBannerObject);
                    if (Utils.checkText(bColor)) {
                        anchorView.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(bColor)));
                    }
                }
            }
        } else {
            HomeUtils.bannerBg(mActivity, pos, cardView, layoutDetailsObj);
        }

        return bHeight;
    }

    private void imageView(int bHeight, AppCompatImageView sImgView, JSONObject mBannerObject, boolean isImgSetParam) {
        double ivRatio = GeneralFunctions.parseDoubleValue(0.0, mActivity.generalFunc.getJsonValueStr("vImageRatio", mBannerObject));
        int ivWidth = (int) (bHeight * ivRatio);
        int ivHeight = (int) (ivWidth / ivRatio);

        if (isImgSetParam) {
            int sHalf = ((int) Utils.getScreenPixelWidth(mActivity) / 2);
            if (ivWidth > sHalf) {
                ivWidth = sHalf - v15sdp;
                ivHeight = (int) (ivWidth / ivRatio);
            }

            LinearLayout.LayoutParams ivParams = (LinearLayout.LayoutParams) sImgView.getLayoutParams();
            ivParams.width = ivWidth;
            ivParams.height = ivHeight;
            sImgView.setLayoutParams(ivParams);
        }


        String mUrl = mActivity.generalFunc.getJsonValueStr("vImage", mBannerObject);
        HomeUtils.loadImg(mActivity, sImgView, mUrl, 0, true, ivWidth, ivHeight);

        if (mActivity.generalFunc.isRTLmode()) {
            sImgView.setRotationY(180);
        }
    }

    private void textViewArea(int pos, MTextView txtTitle, MTextView txtSubTitle, JSONObject mBannerObject, JSONObject layoutDetailsObj) {
        String vTitle = mActivity.generalFunc.getJsonValueStr("vTitle", mBannerObject);
        String vSubtitle = mActivity.generalFunc.getJsonValueStr("vSubtitle", mBannerObject);

        String vTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTitleFont", pos), layoutDetailsObj);
        String vSubTitleFont = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vSubTitleFont", pos), layoutDetailsObj);

        String vTxtTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtTitleColor", pos), layoutDetailsObj);
        String vTxtSubTitleColor = mActivity.generalFunc.getJsonValueStr(HomeUtils.getKey("vTxtSubTitleColor", pos), layoutDetailsObj);

        HomeUtils.manageTextView(mActivity, txtTitle, vTitle, vTitleFont, vTxtTitleColor);
        HomeUtils.manageTextView(mActivity, txtSubTitle, vSubtitle, vSubTitleFont, vTxtSubTitleColor);
    }

    private void bookNowArea(MTextView txtBookNow, JSONObject mBannerObject, JSONObject layoutDetailsObj) {
        String vBookBtnTxt = mActivity.generalFunc.getJsonValueStr("vBookBtnTxt", mBannerObject);
        String bookBtnTxtFont = mActivity.generalFunc.getJsonValueStr("bookBtnTxtFont", layoutDetailsObj);
        String bookBtnTxtColor = mActivity.generalFunc.getJsonValueStr("bookBtnTxtColor", layoutDetailsObj);
        String bookBtnBorderColor = mActivity.generalFunc.getJsonValueStr("bookBtnBorderColor", layoutDetailsObj);
        String bookBtnBgColor = mActivity.generalFunc.getJsonValueStr("bookBtnBgColor", layoutDetailsObj);

        HomeUtils.manageTextView(mActivity, txtBookNow, vBookBtnTxt, bookBtnTxtFont, bookBtnTxtColor);

        GradientDrawable drawable = (GradientDrawable) txtBookNow.getBackground();
        if (Utils.checkText(bookBtnBorderColor)) {
            drawable.setStroke(Utils.dipToPixels(mActivity, 1), Color.parseColor(bookBtnBorderColor));
        }
        if (Utils.checkText(bookBtnBgColor)) {
            drawable.setColor(Color.parseColor(bookBtnBgColor));
        }
    }

    @Override
    public int getItemViewType(int position) {
        JSONObject itemObject = mActivity.generalFunc.getJsonObject(mBannerArray, position);
        if (itemObject == null) {
            return 0;
        }

        JSONObject layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", mItemObject);
        String imagePosition = mActivity.generalFunc.getJsonValueStr("imagePosition", layoutDetailsObj);

        if (imagePosition.equalsIgnoreCase("center-left")) {
            return TYPE_CENTER_LEFT;

        } else if (imagePosition.equalsIgnoreCase("center-right")) {
            return TYPE_CENTER_RIGHT;

        } else if (imagePosition.equalsIgnoreCase("center-top")) {
            return TYPE_CENTER_TOP;

        } else if (imagePosition.equalsIgnoreCase("center-bottom")) {
            return TYPE_CENTER_BOTTOM;

        } else if (imagePosition.equalsIgnoreCase("bottom-right")) {
            return TYPE_BOTTOM_RIGHT;

        } else if (imagePosition.equalsIgnoreCase("bottom-left")) {
            return TYPE_BOTTOM_LEFT;

        } else if (imagePosition.equalsIgnoreCase("top-right")) {
            return TYPE_TOP_RIGHT;

        } else if (imagePosition.equalsIgnoreCase("top-left")) {
            return TYPE_TOP_LEFT;

        } else {
            return 0;
        }
    }

    @Override
    public int getItemCount() {
        return mBannerArray != null ? mBannerArray.length() : 0;
    }

    /////////////////////////////-----------------//////////////////////////////////////////////
    private static class CenterLeftVH extends RecyclerView.ViewHolder {
        private final Item23BannerTextViewCenterLeftBinding binding;

        private CenterLeftVH(Item23BannerTextViewCenterLeftBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class CenterRightVH extends RecyclerView.ViewHolder {
        private final Item23BannerTextViewCenterRightBinding binding;

        private CenterRightVH(Item23BannerTextViewCenterRightBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class CenterTopVH extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private CenterTopVH(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class CenterBottomVH extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private CenterBottomVH(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class BottomRightVH extends RecyclerView.ViewHolder {
        private final Item23BannerTextViewBottomRightBinding binding;

        private BottomRightVH(Item23BannerTextViewBottomRightBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class BottomLeftVH extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private BottomLeftVH(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class TopRightVH extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private TopRightVH(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class TopLeftVH extends RecyclerView.ViewHolder {
        private final Item23BannerItemBinding binding;

        private TopLeftVH(Item23BannerItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onBannerItemClick(int position, JSONObject jsonObject);
    }
}