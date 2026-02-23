package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemParkingDocumentBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class ParkingDocumentAdapter extends RecyclerView.Adapter<ParkingDocumentAdapter.ViewHolder> {

    private final ArrayList<HashMap<String, String>> list;
    private final OnItemClickListener mItemClickListener;
    private String documentIds = "";

    public ParkingDocumentAdapter(ArrayList<HashMap<String, String>> list, OnItemClickListener mItemClickListener) {
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemParkingDocumentBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        holder.binding.txtDocName.setText(mapData.get("doc_name"));

        if (Objects.requireNonNull(mapData.get("isUploaded")).equalsIgnoreCase("Yes")) {
            if (!Utils.checkText(documentIds)) {
                documentIds = mapData.get("doc_id");
            } else {
                documentIds = documentIds + "," + mapData.get("doc_id");
            }
            holder.binding.imgArrow.setImageResource(R.drawable.ic_edit_pencil);
        } else {
            holder.binding.imgArrow.setImageResource(R.drawable.ic_upload);
        }

        holder.binding.imgArrow.setOnClickListener(v -> mItemClickListener.onItemClickList(mapData));

        if (position == (getItemCount() - 1)) {
            holder.binding.viewLine.setVisibility(View.GONE);
            mItemClickListener.onUpdateDocumentIds(documentIds);
        } else {
            holder.binding.viewLine.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData() {
        documentIds = "";
        notifyDataSetChanged();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemParkingDocumentBinding binding;

        private ViewHolder(ItemParkingDocumentBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(HashMap<String, String> position);

        void onUpdateDocumentIds(String documentIds);
    }
}