package com.adapter.files;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.EmergencyContactItemBinding;

import java.util.ArrayList;
import java.util.HashMap;

public class EmergencyContactRecycleAdapter extends RecyclerView.Adapter<EmergencyContactRecycleAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    @Nullable
    private final OnItemClickList onItemClickList;
    private final ArrayList<HashMap<String, String>> list_item;

    public EmergencyContactRecycleAdapter(@NonNull GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list_item, @Nullable OnItemClickList onItemClickList) {
        this.generalFunc = generalFunc;
        this.list_item = list_item;
        this.onItemClickList = onItemClickList;
    }

    @NonNull
    @Override
    public EmergencyContactRecycleAdapter.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(EmergencyContactItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(ViewHolder viewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);

        viewHolder.binding.contactName.setText(item.get("ContactName"));
        viewHolder.binding.contactPhone.setText(item.get("ContactPhone"));

        if (generalFunc.isRTLmode()) {
            viewHolder.binding.layoutShape.setBackgroundResource(R.drawable.ic_shape_rtl);
        }

        viewHolder.binding.imgDelete.setOnClickListener(view -> {
            if (onItemClickList != null) {
                onItemClickList.onItemClick(position);
            }
        });
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    public interface OnItemClickList {
        void onItemClick(int position);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final EmergencyContactItemBinding binding;

        private ViewHolder(EmergencyContactItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}