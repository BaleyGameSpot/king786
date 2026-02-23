package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRentItemCategoryBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentCategoryAdapter extends RecyclerView.Adapter<RentCategoryAdapter.DataViewHolder> {

    private final ArrayList<HashMap<String, String>> list;
    private final CategoryOnClickListener mCategoryOnClickListener;
    private final int sWidth;
    private int selCatPos = -1;

    public RentCategoryAdapter(Context context, ArrayList<HashMap<String, String>> list, CategoryOnClickListener categoryOnClickListener) {
        this.list = list;
        this.mCategoryOnClickListener = categoryOnClickListener;
        this.sWidth = (int) ((int) Utils.getScreenPixelWidth(context) / 3.7);
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new DataViewHolder(ItemRentItemCategoryBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull DataViewHolder holder, @SuppressLint("RecyclerView") int position) {
        HashMap<String, String> mapData = list.get(position);

        RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) holder.binding.mainArea.getLayoutParams();
        bannerLayoutParams.width = sWidth;
        holder.binding.mainArea.setLayoutParams(bannerLayoutParams);

        holder.binding.txtCategoryName.setText(mapData.get("vTitle"));

        if (mapData.containsKey("isCheck")) {
            if (Objects.requireNonNull(mapData.get("isCheck")).equalsIgnoreCase("Yes")) {
                selCatPos = position;
            }
        }

        String imageUrl;

        if (position == selCatPos) {
            imageUrl = mapData.get("vImage1");
            holder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(holder.itemView.getContext(), R.color.appThemeColor_1)));
            holder.binding.txtCategoryName.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.white));
        } else {
            imageUrl = mapData.get("vImage");
            holder.binding.mainArea.setBackgroundTintList(null);
            holder.binding.txtCategoryName.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.text23Pro_Dark));
        }

        if (!Utils.checkText(imageUrl)) {
            imageUrl = "Temp";
        } else {
            imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, holder.binding.imgCategory.getWidth(), holder.binding.imgCategory.getHeight());
        }
        new LoadImage.builder(LoadImage.bind(imageUrl), holder.binding.imgCategory).setPlaceholderImagePath(R.color.imageBg).setErrorImagePath(R.color.imageBg).build();

        holder.binding.mainArea.setOnClickListener(view -> {
            mCategoryOnClickListener.setOnCategoryClick(position, selCatPos);
            selCatPos = position;
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    public interface CategoryOnClickListener {
        void setOnCategoryClick(int position, int selCatPos);
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemRentItemCategoryBinding binding;

        private DataViewHolder(ItemRentItemCategoryBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}