package com.adapter.files.deliverAll;

import android.annotation.SuppressLint;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemCuisines24Binding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.jetbrains.annotations.Nullable;
import org.json.JSONArray;
import org.json.JSONObject;

public class CuisinesAdapter24 extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final ParentActivity act;
    private final GeneralFunctions generalFunc;
    @Nullable
    private JSONArray mArray;
    @Nullable
    private final CuisinesOnClickListener CuisinesOnClickListener;
    private final int width;

    private ItemCuisines24Binding mBinding;
    private int mPosition = -1;

    public CuisinesAdapter24(@NonNull ParentActivity act, @Nullable JSONArray mArray, @Nullable CuisinesOnClickListener CuisinesOnClickListener) {
        this.act = act;
        this.generalFunc = act.generalFunc;
        this.mArray = mArray;
        this.CuisinesOnClickListener = CuisinesOnClickListener;

        this.width = act.getResources().getDimensionPixelSize(R.dimen._55sdp);
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemCuisines24Binding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, @SuppressLint("RecyclerView") int position) {
        if (holder instanceof ViewHolder vHolder) {
            JSONObject itemObj = generalFunc.getJsonObject(mArray, position);
            vHolder.binding.cuisinesTxt.setText(generalFunc.getJsonValueStr("cuisineName", itemObj));

            String imageUrl = generalFunc.getJsonValueStr("vImage", itemObj);
            if (Utils.checkText(imageUrl)) {
                vHolder.binding.cuisinesImg.setVisibility(View.VISIBLE);
                imageUrl = Utils.getResizeImgURL(act, imageUrl, width, width);
                new LoadImage.builder(LoadImage.bind(imageUrl), vHolder.binding.cuisinesImg).build();
            } else {
                vHolder.binding.cuisinesImg.setVisibility(View.GONE);
            }

            colorSelectedView(vHolder.binding, mPosition == position);
            vHolder.binding.mainArea.setOnClickListener(view -> {
                if (mPosition != position) {
                    //
                    if (mBinding != null) {
                        if (CuisinesOnClickListener != null) {
                            CuisinesOnClickListener.setOnCuisinesClick(position, itemObj);
                        }
                    }
                    if (!vHolder.binding.equals(mBinding)) {
                        selItem(vHolder.binding);
                        mPosition = position;
                    }
                }
            });
            if (mBinding == null && position == 0) {
                vHolder.binding.mainArea.performClick();
            }
        }
    }

    private void colorSelectedView(@NonNull ItemCuisines24Binding binding, boolean isCheck) {
        if (isCheck) {
            binding.imgBgArea.setBackground(ContextCompat.getDrawable(act, R.drawable.card_view_23_food_cuisin_bg_food_theme));
            binding.cuisinesImg.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(act, R.color.white)));
        } else {
            binding.imgBgArea.setBackground(ContextCompat.getDrawable(act, R.drawable.card_view_23_food_cuisin_bg_gray));
            binding.cuisinesImg.setImageTintList(ColorStateList.valueOf(ContextCompat.getColor(act, R.color.black)));
        }
    }

    private void selItem(@NonNull ItemCuisines24Binding binding) {
        if (mBinding != null) {
            colorSelectedView(mBinding, false);
        }
        mBinding = binding;
        colorSelectedView(binding, true);
    }

    @Override
    public int getItemCount() {
        return mArray != null ? mArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(@Nullable JSONArray mArray) {
        this.mArray = mArray;
        notifyDataSetChanged();
    }

    /////////////////////////////-----------------
    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemCuisines24Binding binding;

        private ViewHolder(ItemCuisines24Binding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface CuisinesOnClickListener {
        void setOnCuisinesClick(int position, @NonNull JSONObject itemObj);
    }
}