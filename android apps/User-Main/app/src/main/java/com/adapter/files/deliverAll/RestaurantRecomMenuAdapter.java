package com.adapter.files.deliverAll;

import android.content.Context;
import android.graphics.Paint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.recyclerview.widget.RecyclerView;

import com.act.homescreen23.adapter.HomeUtils;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemResmenuGridviewBinding;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RestaurantRecomMenuAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private final OnItemClickListener mItemClickListener;
    private final ArrayList<HashMap<String, String>> list;
    private final int grayColor, _212;

    public RestaurantRecomMenuAdapter(Context mContext, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
        this.list = list;
        this.mItemClickListener = mItemClickListener;

        this.grayColor = mContext.getResources().getColor(R.color.gray);

        this._212 = mContext.getResources().getDimensionPixelSize(R.dimen._212sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new GridViewHolder(ItemResmenuGridviewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NotNull RecyclerView.ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        GridViewHolder grideholder = (GridViewHolder) holder;
        grideholder.binding.title.setText(mapData.get("vItemType"));
        grideholder.binding.title.setSelected(true);

        String eFoodType = mapData.get("eFoodType");

        if (Objects.requireNonNull(eFoodType).equalsIgnoreCase("NonVeg")) {
            grideholder.binding.nonVegImage.setVisibility(View.VISIBLE);
            grideholder.binding.vegImage.setVisibility(View.GONE);
        } else if (eFoodType.equals("Veg")) {
            grideholder.binding.nonVegImage.setVisibility(View.GONE);
            grideholder.binding.vegImage.setVisibility(View.VISIBLE);
        }

        if (Objects.requireNonNull(mapData.get("prescription_required")).equalsIgnoreCase("Yes")) {
            grideholder.binding.presImage.setVisibility(View.VISIBLE);
        } else {
            grideholder.binding.presImage.setVisibility(View.GONE);
        }

        if (Objects.requireNonNull(mapData.get("fOfferAmtNotZero")).equalsIgnoreCase("Yes")) {
            grideholder.binding.price.setText(mapData.get("StrikeoutPriceConverted"));
            grideholder.binding.price.setPaintFlags(grideholder.binding.price.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
            grideholder.binding.price.setTextColor(grayColor);
            grideholder.binding.offerPrice.setText(mapData.get("fDiscountPricewithsymbolConverted"));
            grideholder.binding.offerPrice.setVisibility(View.VISIBLE);
        } else {
            grideholder.binding.price.setVisibility(View.INVISIBLE);
            grideholder.binding.offerPrice.setText(mapData.get("StrikeoutPriceConverted"));
            grideholder.binding.offerPrice.setVisibility(View.VISIBLE);
        }

        grideholder.binding.addBtn.setText(mapData.get("LBL_ADD"));
        grideholder.binding.addBtn.setOnClickListener(v -> {
            if (mItemClickListener != null) {
                mItemClickListener.onRecomItemClickList(grideholder.binding.addBtn, position, false);
            }
        });

        imageArea(grideholder.binding.menuImage, mapData);

        grideholder.binding.menuImage.setOnClickListener(v -> {
            if (mItemClickListener != null) {
                mItemClickListener.onRecomItemClickList(grideholder.binding.menuImage, position, true);
            }
        });

        if (generalFunc.isRTLmode()) {
            grideholder.binding.tagImage.setRotation(180);
            grideholder.binding.tagTxt.setPadding(10, 15, 0, 0);
        }
        String vHighlightName = mapData.get("vHighlightName");
        if (vHighlightName != null && !vHighlightName.equals("")) {
            grideholder.binding.tagImage.setVisibility(View.VISIBLE);
            grideholder.binding.tagTxt.setVisibility(View.VISIBLE);
            grideholder.binding.tagTxt.setText(vHighlightName);
        } else {
            grideholder.binding.tagImage.setVisibility(View.GONE);
            grideholder.binding.tagTxt.setVisibility(View.GONE);
        }
        grideholder.binding.vCategoryNameTxt.setText(mapData.get("vCategoryName"));
    }

    private void imageArea(AppCompatImageView imgView, HashMap<String, String> mapData) {
        int imgW = _212;
        int imgH = (int) (imgW / 1.33333333333);
        //
        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) imgView.getLayoutParams();
        mParams.width = imgW;
        mParams.height = imgH;
        imgView.setLayoutParams(mParams);
        //
        if (!Objects.requireNonNull(mapData.get("vImage")).equalsIgnoreCase("https")) {
            HomeUtils.loadImg(mContext, imgView, mapData.get("vImage"), R.color.imageBg, true, imgW, imgH);
        } else {
            HomeUtils.loadImg(mContext, imgView, mapData.get("vImageResized"), R.color.imageBg, false, 0, 0);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class GridViewHolder extends RecyclerView.ViewHolder {

        private final ItemResmenuGridviewBinding binding;

        private GridViewHolder(ItemResmenuGridviewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onRecomItemClickList(View v, int position, boolean openGrid);
    }
}