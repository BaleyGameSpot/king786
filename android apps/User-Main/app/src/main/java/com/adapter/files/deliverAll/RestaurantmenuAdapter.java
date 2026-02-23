package com.adapter.files.deliverAll;

import android.content.Context;
import android.graphics.Paint;
import android.graphics.drawable.Drawable;
import android.os.SystemClock;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.style.ImageSpan;
import android.transition.TransitionManager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.act.homescreen23.adapter.HomeUtils;
import com.general.files.GeneralFunctions;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemMenuHeaderviewBinding;
import com.buddyverse.main.databinding.ItemMenuListBinding;
import com.buddyverse.main.databinding.ItemResmenuGridviewBinding;
import com.utils.Logger;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RestaurantmenuAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final int TYPE_HEADER = 1;
    private final int TYPE_GRID = 2;

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final OnItemClickListener mItemClickListener;

    private final ArrayList<HashMap<String, String>> list;
    private final int grayColor, imageBackColor, mHeight, parentImageHeight;

    public RestaurantmenuAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, OnItemClickListener mItemClickListener) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.mItemClickListener = mItemClickListener;
        this.list = list;

        this.grayColor = mContext.getResources().getColor(R.color.gray);
        this.imageBackColor = mContext.getResources().getColor(R.color.appThemeColor_1);

        mHeight = (int) Utils.getScreenPixelHeight(mContext);
        parentImageHeight = Utils.getHeightOfBanner(mContext, (int) mContext.getResources().getDimension(R.dimen._35sdp), "4:3");
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_HEADER) {
            return new ViewHolder(ItemMenuHeaderviewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else if (viewType == TYPE_GRID) {
            return new GridViewHolder(ItemResmenuGridviewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ListViewHolder(ItemMenuListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        HashMap<String, String> mapData = list.get(position);


        if (holder instanceof ViewHolder vHolder) {
            vHolder.binding.menuHeaderTxt.setText(mapData.get("menuName"));

        } else if (holder instanceof GridViewHolder vHolder) {
            vHolder.binding.title.setText(mapData.get("vItemType"));

            String eFoodType = mapData.get("eFoodType");
            assert eFoodType != null;
            if (eFoodType.equalsIgnoreCase("NonVeg")) {
                vHolder.binding.nonVegImage.setVisibility(View.VISIBLE);
                vHolder.binding.vegImage.setVisibility(View.GONE);
            } else if (eFoodType.equals("Veg")) {
                vHolder.binding.nonVegImage.setVisibility(View.GONE);
                vHolder.binding.vegImage.setVisibility(View.VISIBLE);
            }

            if (Objects.requireNonNull(mapData.get("prescription_required")).equalsIgnoreCase("Yes")) {
                vHolder.binding.presImage.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.presImage.setVisibility(View.GONE);
            }
            String StrikeoutPriceConverted = mapData.get("StrikeoutPriceConverted");
            if (Objects.requireNonNull(mapData.get("fOfferAmtNotZero")).equalsIgnoreCase("Yes")) {
                vHolder.binding.price.setText(StrikeoutPriceConverted);
                vHolder.binding.price.setTextColor(grayColor);
            } else {
                vHolder.binding.price.setText(StrikeoutPriceConverted);
                vHolder.binding.price.setPaintFlags(0);
            }
            if (!Objects.requireNonNull(mapData.get("vImage")).equalsIgnoreCase("https")) {
                HomeUtils.loadImg(mContext, vHolder.binding.menuImage, mapData.get("vImage"), R.color.imageBg, true, 0, 0);
            } else {
                HomeUtils.loadImg(mContext, vHolder.binding.menuImage, mapData.get("vImageResized"), R.color.imageBg, false, 0, 0);
            }

            LinearLayout.LayoutParams layoutParams = (LinearLayout.LayoutParams) vHolder.binding.menuImage.getLayoutParams();
            layoutParams.height = GeneralFunctions.parseIntegerValue(0, mapData.get("heightOfImage"));
            vHolder.binding.menuImage.setLayoutParams(layoutParams);
            vHolder.binding.addBtn.setText(mapData.get("LBL_ADD"));
            vHolder.binding.addBtn.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(vHolder.binding.addBtn, position);
                }
            });
            if (generalFunc.isRTLmode()) {
                vHolder.binding.tagImage.setRotation(180);
                vHolder.binding.tagTxt.setPadding(10, 15, 0, 0);
            }
            String vHighlightName = mapData.get("vHighlightName");
            if (vHighlightName != null && !vHighlightName.equals("")) {
                vHolder.binding.tagImage.setVisibility(View.VISIBLE);
                vHolder.binding.tagTxt.setVisibility(View.VISIBLE);
                vHolder.binding.tagTxt.setText(vHighlightName);
            } else {
                vHolder.binding.tagImage.setVisibility(View.GONE);
                vHolder.binding.tagTxt.setVisibility(View.GONE);
            }
            vHolder.binding.vCategoryNameTxt.setText(mapData.get("vCategoryName"));

        } else if (holder instanceof ListViewHolder vHolder) {
            String isFromSearch = mapData.get("isFromSearch");
            boolean isFromSearchAvail = Utils.checkText(isFromSearch);
            if (Utils.checkText(isFromSearch)) {

                vHolder.binding.storeTitle.setTransformationMethod(null);
                vHolder.binding.storeTitleEx.setTransformationMethod(null);

                Drawable image = ContextCompat.getDrawable(mContext, R.drawable.ic_star_color1_24dp);
                assert image != null;
                image.setBounds(0, 0, image.getIntrinsicWidth(), image.getIntrinsicHeight());

                // Replace blank spaces with image icon
                String myText = mapData.get("vCompany") + "(";
                int textLength = myText.length();
                SpannableString sb = new SpannableString(myText + "  " + mapData.get("vAvgRatingConverted") + ")");
                ImageSpan imageSpan = new ImageSpan(image, ImageSpan.ALIGN_BASELINE);
                sb.setSpan(imageSpan, textLength, textLength + 1, Spanned.SPAN_INCLUSIVE_EXCLUSIVE);
                vHolder.binding.storeTitle.setText(sb);
                vHolder.binding.storeTitleEx.setText(sb);

                vHolder.binding.storeTitle.setSelected(true);
                vHolder.binding.storeTitleEx.setSelected(true);
                vHolder.binding.title.setTextColor(imageBackColor);
                vHolder.binding.titleEx.setTextColor(imageBackColor);
            }
            vHolder.binding.title.setText(mapData.get("vItemType"));
            vHolder.binding.title.setSelected(true);
            vHolder.binding.titleEx.setText(mapData.get("vItemType"));
            vHolder.binding.titleEx.setSelected(true);
            vHolder.binding.desc.setText(mapData.get("vItemDesc"));
            vHolder.binding.descEx.setText(mapData.get("vItemDesc"));


            if (Objects.requireNonNull(mapData.get("vItemDesc")).equalsIgnoreCase("")) {
                vHolder.binding.desc.setVisibility(View.GONE);
                vHolder.binding.descEx.setVisibility(View.GONE);
            } else {
                vHolder.binding.desc.setVisibility(View.VISIBLE);
                vHolder.binding.descEx.setVisibility(View.VISIBLE);
            }

            int padding = (int) mContext.getResources().getDimension(R.dimen._5sdp);
            AppCompatImageView imageView = isFromSearchAvail ? vHolder.binding.searchExpandImg : vHolder.binding.expandImg;
            AppCompatImageView imageView1 = isFromSearchAvail ? vHolder.binding.searchMenuImg : vHolder.binding.menuImg;

            vHolder.binding.expandImg.setVisibility(View.GONE);
            vHolder.binding.searchMenuImg.setVisibility(View.GONE);
            if (isFromSearchAvail) {
                vHolder.binding.menuImg.setVisibility(View.GONE);
                vHolder.binding.searchMenuImg.setVisibility(View.INVISIBLE);
            } else {
                vHolder.binding.menuImg.setVisibility(View.INVISIBLE);
                vHolder.binding.searchMenuImg.setVisibility(View.GONE);
            }

            final long[] mLastClickTime = {0};

            imageView.setOnClickListener(v -> {

                if (SystemClock.elapsedRealtime() - mLastClickTime[0] < 800) {
                    return;
                }
                mLastClickTime[0] = SystemClock.elapsedRealtime();

                try {
                    TransitionManager.beginDelayedTransition(vHolder.binding.parent);
                    int height = vHolder.binding.parent.getHeight();
                    int width = vHolder.binding.parent.getWidth();
                    int ht = imageView.getMeasuredHeight();

                    if (ht >= height || ht > mHeight / 4 || ht > Utils.dpToPx(120, mContext)) {
                        setMargins(imageView, padding, padding, padding, padding);
                        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) imageView.getLayoutParams();
                        params.width = (int) mContext.getResources().getDimension(isFromSearchAvail ? R.dimen._80sdp : R.dimen._80sdp);
                        params.height = (int) mContext.getResources().getDimension(isFromSearchAvail ? R.dimen._60sdp : R.dimen._60sdp);
                        imageView.requestLayout();

                        generalFunc.slideAnimView(vHolder.binding.expandTempImg, vHolder.binding.expandDetailArea, vHolder.binding.expandTempImg.getHeight(), 0, 400);

                        vHolder.binding.expandDetailArea.setVisibility(View.GONE);
                        imageView1.setVisibility(View.INVISIBLE);
                        vHolder.binding.mainDetailArea.setVisibility(View.VISIBLE);
                        vHolder.binding.addBtnArea.setVisibility(View.VISIBLE);

                        HashMap<String, String> hashMap = list.get(position);
                        hashMap.put("isExpand", "No");
                        list.set(position, hashMap);
                    } else {
                        //expanded
                        int pd = (int) mContext.getResources().getDimension(R.dimen._minus5sdp);
                        setMargins(imageView, 0, pd, 0, pd);

                        HashMap<String, String> hashMap = list.get(position);
                        hashMap.put("isExpand", "Yes");

                        vHolder.binding.descEx.setTag(null);
                        vHolder.binding.descEx.setText(GeneralFunctions.fromHtml(GeneralFunctions.fromHtml(hashMap.get("vItemDesc")) + ""));
                        generalFunc.makeTextViewResizable(vHolder.binding.descEx, 3, "...+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);

                        list.set(position, hashMap);
                        LinearLayout.LayoutParams layoutParams = (LinearLayout.LayoutParams) vHolder.binding.expandTempImg.getLayoutParams();
                        layoutParams.width = width;
                        layoutParams.height = parentImageHeight;
                        vHolder.binding.expandTempImg.requestLayout();
                        vHolder.binding.expandTempImg.setVisibility(View.INVISIBLE);

                        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) imageView.getLayoutParams();
                        params.width = width;
                        params.height = parentImageHeight;
                        imageView.requestLayout();

                        vHolder.binding.expandDetailArea.setVisibility(View.VISIBLE);
                        vHolder.binding.mainDetailArea.setVisibility(View.GONE);
                        vHolder.binding.addBtnArea.setVisibility(View.GONE);
                        imageView1.setVisibility(View.GONE);

                        TransitionManager.beginDelayedTransition(vHolder.binding.expandDetailArea);
                        LinearLayout.LayoutParams lp = (LinearLayout.LayoutParams) vHolder.binding.expandDetailArea.getLayoutParams();
                        lp.width = LinearLayout.LayoutParams.MATCH_PARENT;
                        lp.height = LinearLayout.LayoutParams.WRAP_CONTENT;
                        vHolder.binding.expandDetailArea.requestLayout();
                    }
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            });

            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) imageView.getLayoutParams();

            if (!Objects.requireNonNull(list.get(position).get("isExpand")).equalsIgnoreCase("Yes")) {

                setMargins(imageView, padding, padding, padding, padding);

                params.width = (int) mContext.getResources().getDimension(isFromSearchAvail ? R.dimen._80sdp : R.dimen._80sdp);
                params.height = (int) mContext.getResources().getDimension(isFromSearchAvail ? R.dimen._60sdp : R.dimen._60sdp);
                imageView.requestLayout();
                generalFunc.slideAnimView(vHolder.binding.expandTempImg, vHolder.binding.expandDetailArea, vHolder.binding.expandTempImg.getHeight(), 0, 400);

                vHolder.binding.expandDetailArea.setVisibility(View.GONE);
                imageView1.setVisibility(View.INVISIBLE);
                vHolder.binding.mainDetailArea.setVisibility(View.VISIBLE);
                vHolder.binding.addBtnArea.setVisibility(View.VISIBLE);

            } else {

                vHolder.binding.descEx.setTag(null);
                vHolder.binding.descEx.setText(GeneralFunctions.fromHtml(GeneralFunctions.fromHtml(list.get(position).get("vItemDesc")) + ""));
                generalFunc.makeTextViewResizable(vHolder.binding.descEx, 3, "...+ " + generalFunc.retrieveLangLBl("View More", "LBL_VIEW_MORE_TXT"), true, R.color.appThemeColor_1, R.dimen.txt_size_10);

                TransitionManager.beginDelayedTransition(vHolder.binding.parent);
                int width = vHolder.binding.parent.getWidth();

                if (width < 1) {
                    width = Utils.getWidthOfBanner(mContext, (int) mContext.getResources().getDimension(R.dimen._5sdp));
                }

                int pd = (int) mContext.getResources().getDimension(R.dimen._minus5sdp);
                setMargins(imageView, 0, pd, 0, 0);

                LinearLayout.LayoutParams layoutParams = (LinearLayout.LayoutParams) vHolder.binding.expandTempImg.getLayoutParams();
                layoutParams.width = width;
                layoutParams.height = parentImageHeight;
                vHolder.binding.expandTempImg.requestLayout();
                vHolder.binding.expandTempImg.setVisibility(View.INVISIBLE);

                params.width = width;
                params.height = parentImageHeight;
                imageView.requestLayout();

                vHolder.binding.expandDetailArea.setVisibility(View.VISIBLE);
                vHolder.binding.mainDetailArea.setVisibility(View.GONE);
                vHolder.binding.addBtnArea.setVisibility(View.GONE);
                imageView1.setVisibility(View.GONE);


                TransitionManager.beginDelayedTransition(vHolder.binding.expandDetailArea);
                LinearLayout.LayoutParams lp = (LinearLayout.LayoutParams) vHolder.binding.expandDetailArea.getLayoutParams();
                lp.width = LinearLayout.LayoutParams.MATCH_PARENT;
                lp.height = LinearLayout.LayoutParams.WRAP_CONTENT;

                vHolder.binding.expandDetailArea.requestLayout();


            }

            imageView.setVisibility(View.VISIBLE);
            HomeUtils.loadImg(mContext, imageView, mapData.get("vImage"), 0, true, params.width, params.height);
            vHolder.binding.expandImg.setShapeAppearanceModel(vHolder.binding.expandImg.getShapeAppearanceModel().toBuilder().setAllCorners(CornerFamily.ROUNDED, mContext.getResources().getDimensionPixelSize(R.dimen._5sdp)).build());

            String eFoodType = mapData.get("eFoodType");

            if (eFoodType != null) {
                if (eFoodType.equalsIgnoreCase("NonVeg")) {
                    vHolder.binding.nonVegImage.setVisibility(View.VISIBLE);
                    vHolder.binding.nonVegImageEx.setVisibility(View.VISIBLE);
                    vHolder.binding.vegImage.setVisibility(View.GONE);
                    vHolder.binding.vegImageEx.setVisibility(View.GONE);
                } else if (eFoodType.equals("Veg")) {
                    vHolder.binding.nonVegImage.setVisibility(View.GONE);
                    vHolder.binding.nonVegImageEx.setVisibility(View.GONE);
                    vHolder.binding.vegImage.setVisibility(View.VISIBLE);
                    vHolder.binding.vegImageEx.setVisibility(View.VISIBLE);
                }
            }

            if (Objects.requireNonNull(mapData.get("prescription_required")).equalsIgnoreCase("Yes")) {
                vHolder.binding.presImage.setVisibility(View.VISIBLE);
                vHolder.binding.presImageEx.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.presImage.setVisibility(View.GONE);
                vHolder.binding.presImageEx.setVisibility(View.GONE);
            }
            String StrikeoutPriceConverted = mapData.get("StrikeoutPriceConverted");
            if (Objects.requireNonNull(mapData.get("fOfferAmtNotZero")).equalsIgnoreCase("Yes")) {
                vHolder.binding.price.setText(StrikeoutPriceConverted);
                vHolder.binding.price.setPaintFlags(vHolder.binding.price.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
                vHolder.binding.price.setTextColor(grayColor);
                vHolder.binding.priceEx.setText(StrikeoutPriceConverted);
                String fDiscountPricewithsymbolConverted = mapData.get("fDiscountPricewithsymbolConverted");
                vHolder.binding.priceEx.setPaintFlags(vHolder.binding.price.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
                vHolder.binding.priceEx.setTextColor(grayColor);
                vHolder.binding.offerPrice.setText(fDiscountPricewithsymbolConverted);
                vHolder.binding.offerPrice.setVisibility(View.VISIBLE);
                vHolder.binding.offerPriceEx.setText(fDiscountPricewithsymbolConverted);
                vHolder.binding.offerPriceEx.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.price.setTextColor(imageBackColor);
                vHolder.binding.price.setText(StrikeoutPriceConverted);
                vHolder.binding.price.setPaintFlags(0);
                vHolder.binding.priceEx.setTextColor(imageBackColor);
                vHolder.binding.priceEx.setText(StrikeoutPriceConverted);
                vHolder.binding.priceEx.setPaintFlags(0);
                vHolder.binding.offerPrice.setVisibility(View.GONE);
                vHolder.binding.offerPriceEx.setVisibility(View.GONE);
            }
            String LBL_ADD = mapData.get("LBL_ADD");
            vHolder.binding.addBtn.setText(LBL_ADD);
            vHolder.binding.addBtnEx.setText(LBL_ADD);

            vHolder.binding.addBtn.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(vHolder.binding.addBtn, position);
                }
            });

            vHolder.binding.addBtnEx.setOnClickListener(v -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(vHolder.binding.addBtnEx, position);
                }
            });

            String vHighlightName = mapData.get("vHighlightName");
            if (vHighlightName != null && !vHighlightName.equals("") && !isFromSearchAvail) {
                vHolder.binding.tagArea.setVisibility(View.VISIBLE);
                vHolder.binding.tagTxt.setText(vHighlightName);
            } else {
                vHolder.binding.tagArea.setVisibility(View.GONE);
            }

            if (isFromSearchAvail) {
                vHolder.binding.storeTitle.setVisibility(View.VISIBLE);
                vHolder.binding.storeTitleEx.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.storeTitle.setVisibility(View.GONE);
                vHolder.binding.storeTitleEx.setVisibility(View.GONE);
            }

            String isLastLine = mapData.get("isLastLine");
            vHolder.binding.bottomLine.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemViewType(int position) {
        String rowType = list.get(position).get("Type");
        if (Objects.requireNonNull(rowType).equalsIgnoreCase("HEADER")) {
            return TYPE_HEADER;
        } else if (rowType.equalsIgnoreCase("GRID")) {
            return TYPE_GRID;
        } else {
            return 0;
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    private void setMargins(View view, int left, int top, int right, int bottom) {
        if (view.getLayoutParams() instanceof ViewGroup.MarginLayoutParams) {
            ViewGroup.MarginLayoutParams p = (ViewGroup.MarginLayoutParams) view.getLayoutParams();
            p.setMargins(left, top, right, bottom);
            view.requestLayout();
        }
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemMenuHeaderviewBinding binding;

        private ViewHolder(ItemMenuHeaderviewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class GridViewHolder extends RecyclerView.ViewHolder {

        private final ItemResmenuGridviewBinding binding;

        private GridViewHolder(ItemResmenuGridviewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class ListViewHolder extends RecyclerView.ViewHolder {

        private final ItemMenuListBinding binding;

        private ListViewHolder(ItemMenuListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position);
    }
}