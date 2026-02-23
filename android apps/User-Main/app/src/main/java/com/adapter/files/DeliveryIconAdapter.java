package com.adapter.files;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.databinding.DeliveryIconLayoutBinding;
import com.model.DeliveryIconDetails;

import java.util.ArrayList;

public class DeliveryIconAdapter extends RecyclerView.Adapter<DeliveryIconAdapter.ViewHolder> {

    private final ArrayList<DeliveryIconDetails> listData;
    private final SubCategoryItemAdapter.SubCategoryClickListener listener;

    public DeliveryIconAdapter(ArrayList<DeliveryIconDetails> listData, SubCategoryItemAdapter.SubCategoryClickListener listener) {
        this.listData = listData;
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(DeliveryIconLayoutBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        DeliveryIconDetails data = listData.get(position);
        holder.binding.headerTitleTxt.setText(data.getvCategory());
        holder.binding.subHeaderTitleTxt.setText(data.gettCategoryDesc());
        SubCategoryItemAdapter subCategoryItemAdapter = new SubCategoryItemAdapter(data.getSubDataList(), listener);

        //GridLayoutManager mLayoutManager = new GridLayoutManager(holder.itemView.getContext(), 1);
        LinearLayoutManager mLayoutManager = new LinearLayoutManager(holder.itemView.getContext(), LinearLayoutManager.VERTICAL, false);
        holder.binding.deliveryRecycleview.setLayoutManager(mLayoutManager);
        holder.binding.deliveryRecycleview.setAdapter(subCategoryItemAdapter);
    }

    @Override
    public int getItemCount() {
        return listData.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final DeliveryIconLayoutBinding binding;

        private ViewHolder(DeliveryIconLayoutBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}