package com.adapter.files;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemIconLayoutBinding;
import com.model.DeliveryIconDetails;
import com.utils.LoadImageGlide;

import java.util.ArrayList;
import java.util.HashMap;

public class SubCategoryItemAdapter extends RecyclerView.Adapter<SubCategoryItemAdapter.ViewHolder> {

    private final ArrayList<DeliveryIconDetails.SubCatData> listData;
    private final SubCategoryClickListener listener;

    public SubCategoryItemAdapter(ArrayList<DeliveryIconDetails.SubCatData> listData, SubCategoryClickListener listener) {
        this.listData = listData;
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemIconLayoutBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        DeliveryIconDetails.SubCatData data = listData.get(position);

        holder.binding.boxTitleTxt.setText(data.getvSubCategory());
        holder.binding.boxDescTxt.setText(data.gettSubCategoryDesc());

        new LoadImageGlide.builder(holder.itemView.getContext(), LoadImageGlide.bind(data.getvImage()), holder.binding.boxImage).setErrorImagePath(R.drawable.ic_circle_image_bg).setPlaceholderImagePath(R.drawable.ic_circle_image_bg).build();

        holder.binding.linearlayout.setOnClickListener(view -> listener.onItemSubCategoryClick(position, data.geteDeliveryType(), data.getDataMap()));
    }

    @Override
    public int getItemCount() {
        return listData.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemIconLayoutBinding binding;
        private ViewHolder(ItemIconLayoutBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface SubCategoryClickListener {
        void onItemSubCategoryClick(int position, String eDeliveryType, HashMap<String, String> dataMap);
    }
}