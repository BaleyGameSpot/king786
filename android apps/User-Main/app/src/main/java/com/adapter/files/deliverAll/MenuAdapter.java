package com.adapter.files.deliverAll;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.databinding.ItemMenuBinding;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class MenuAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;

    public MenuAdapter(ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new GridViewHolder(ItemMenuBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        GridViewHolder grideholder = (GridViewHolder) holder;

        grideholder.binding.nameTxt.setVisibility(View.GONE);
        grideholder.binding.SelnameTxt.setVisibility(View.GONE);
        grideholder.binding.viewLine.setVisibility(View.GONE);

        if (Objects.requireNonNull(mapData.get("isSelect")).equalsIgnoreCase("Yes")) {
            grideholder.binding.nameTxt.setVisibility(View.GONE);
            grideholder.binding.SelnameTxt.setVisibility(View.VISIBLE);
            grideholder.binding.viewLine.setVisibility(View.VISIBLE);
        } else {
            grideholder.binding.nameTxt.setVisibility(View.VISIBLE);
            grideholder.binding.SelnameTxt.setVisibility(View.GONE);
            grideholder.binding.viewLine.setVisibility(View.GONE);
        }
        grideholder.binding.nameTxt.setText(mapData.get("name"));
        grideholder.binding.SelnameTxt.setText(mapData.get("name"));


        grideholder.binding.nameTxt.setOnClickListener(v -> {
            if (mItemClickListener != null) {
                mItemClickListener.onMenuItemClickList(grideholder.binding.nameTxt, position);
            }
        });
        grideholder.binding.SelnameTxt.setOnClickListener(v -> {
            if (mItemClickListener != null) {
                mItemClickListener.onMenuItemClickList(grideholder.binding.nameTxt, position);
            }
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class GridViewHolder extends RecyclerView.ViewHolder {

        private final ItemMenuBinding binding;

        private GridViewHolder(ItemMenuBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onMenuItemClickList(View v, int position);
    }
}