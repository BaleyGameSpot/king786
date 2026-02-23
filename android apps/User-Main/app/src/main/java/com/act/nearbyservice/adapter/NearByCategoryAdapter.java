package com.act.nearbyservice.adapter;

import android.annotation.SuppressLint;
import android.graphics.drawable.GradientDrawable;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemNearByCategoryBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class NearByCategoryAdapter extends RecyclerView.Adapter<NearByCategoryAdapter.DataViewHolder> {

    private final ArrayList<HashMap<String, String>> list;
    private final CategoryOnClickListener mCategoryOnClickListener;
    private int selCatPos = -1;

    public NearByCategoryAdapter(ArrayList<HashMap<String, String>> list, CategoryOnClickListener categoryOnClickListener) {
        this.list = list;
        this.mCategoryOnClickListener = categoryOnClickListener;
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new DataViewHolder(ItemNearByCategoryBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull NearByCategoryAdapter.DataViewHolder holder, @SuppressLint("RecyclerView") int position) {
        HashMap<String, String> mapData = list.get(position);

        holder.binding.txtCategoryName.setText(mapData.get("vTitle"));

        if (mapData.containsKey("isSelect")) {
            if (Objects.requireNonNull(mapData.get("isSelect")).equalsIgnoreCase("Yes")) {
                selCatPos = position;
            }
        }

        GradientDrawable drawable = (GradientDrawable) holder.binding.mainArea.getBackground();
        if (position == selCatPos) {

            drawable.setStroke(Utils.dipToPixels(holder.itemView.getContext(), 2), ContextCompat.getColor(holder.itemView.getContext(), R.color.appThemeColor_1));
            drawable.setColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.appThemeColor_1));

            holder.binding.txtCategoryName.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.white));
        } else {

            drawable.setStroke(Utils.dipToPixels(holder.itemView.getContext(), 2), ContextCompat.getColor(holder.itemView.getContext(), R.color.white));
            drawable.setColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.white));

            holder.binding.txtCategoryName.setTextColor(ContextCompat.getColor(holder.itemView.getContext(), R.color.black));
        }

        String imageUrl = mapData.get("vListLogo");

        if (!Utils.checkText(imageUrl)) {
            imageUrl = "Temp";
        } else {
            imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), imageUrl, holder.binding.imgCategory.getWidth(), holder.binding.imgCategory.getHeight());
        }
        new LoadImage.builder(LoadImage.bind(imageUrl), holder.binding.imgCategory).setPlaceholderImagePath(R.drawable.ic_circle_image_bg).setErrorImagePath(R.drawable.ic_circle_image_bg).build();

        holder.binding.mainArea.setOnClickListener(view -> {
            mCategoryOnClickListener.setOnCategoryClick(position, selCatPos);
            selCatPos = position;
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {
        private final ItemNearByCategoryBinding binding;

        private DataViewHolder(ItemNearByCategoryBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface CategoryOnClickListener {
        void setOnCategoryClick(int position, int selCatPos);
    }
}