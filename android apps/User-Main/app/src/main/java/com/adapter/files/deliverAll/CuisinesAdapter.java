package com.adapter.files.deliverAll;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemCuisinesBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class CuisinesAdapter extends RecyclerView.Adapter<CuisinesAdapter.ViewHolder> {

    private final Context mContext;
    private final ArrayList<HashMap<String, String>> list;
    private final CuisinesOnClickListener CuisinesOnClickListener;

    public CuisinesAdapter(Context mContext, ArrayList<HashMap<String, String>> list, CuisinesOnClickListener CuisinesOnClickListener) {
        this.mContext = mContext;
        this.list = list;
        this.CuisinesOnClickListener = CuisinesOnClickListener;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int i) {
        return new ViewHolder(ItemCuisinesBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(ViewHolder holder, int position) {

        holder.binding.cuisinesTxt.setText(list.get(position).get("cuisineName"));

        String imageUrl = list.get(position).get("vImage");
        if (Utils.checkText(imageUrl)) {
            holder.binding.cuisinesImg.setVisibility(View.VISIBLE);
            new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, imageUrl,
                    holder.binding.cuisinesImg.getWidth(), holder.binding.cuisinesImg.getHeight())), holder.binding.cuisinesImg).build();
        } else {
            holder.binding.cuisinesImg.setVisibility(View.GONE);
        }

        holder.binding.cuisinesTxt.setTextColor(mContext.getResources().getColor(R.color.black));
        holder.binding.mainArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.card_view_23_food_cuisin_bg_gray));

        if (list.get(position).containsKey("isCheck")) {
            if (Objects.requireNonNull(list.get(position).get("isCheck")).equalsIgnoreCase("Yes")) {
                holder.binding.cuisinesTxt.setTextColor(mContext.getResources().getColor(R.color.white));
                holder.binding.mainArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.card_view_23_food_cuisin_bg_food_theme));
            }
        }
        holder.binding.mainArea.setOnClickListener(view -> CuisinesOnClickListener.setOnCuisinesClick(position));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemCuisinesBinding binding;

        private ViewHolder(ItemCuisinesBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface CuisinesOnClickListener {
        void setOnCuisinesClick(int position);
    }
}