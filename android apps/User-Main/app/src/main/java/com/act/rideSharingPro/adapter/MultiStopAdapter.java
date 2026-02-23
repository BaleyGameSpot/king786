package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.main.databinding.ItemRideProMultiStopBinding;
import com.act.rideSharingPro.model.RideProPublishData;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;

public class MultiStopAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    @Nullable
    private ArrayList<RideProPublishData.MultiStopData> list;
    private final OnItemClickListener mItemClickListener;
    private int pos = -1;

    public MultiStopAdapter(@Nullable ArrayList<RideProPublishData.MultiStopData> list, OnItemClickListener mItemClickListener) {
        this.list = list;
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemRideProMultiStopBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        if (list == null) {
            return;
        }

        if (holder instanceof final ViewHolder viewHolder) {
            int listSize = list.size() - 1;

            final RideProPublishData.MultiStopData item = list.get(position);

            if (item.getIsFromLoc().equalsIgnoreCase("Yes")) {
                viewHolder.binding.toValueTxt.setText(item.getDestAddress());
                viewHolder.binding.removeAdd.setVisibility(View.GONE);
            } else {
                if (pos == -1) {
                    pos = listSize;
                }
                viewHolder.binding.toValueTxt.setHint(item.getHintLable());
                viewHolder.binding.removeAdd.setVisibility(View.VISIBLE);
            }

            viewHolder.binding.squareImgView.setVisibility(position == 0 || position == listSize ? View.GONE : View.VISIBLE);
            viewHolder.binding.ivLocImg.setVisibility(position == 0 ? View.VISIBLE : View.GONE);
            viewHolder.binding.ivRound.setVisibility(position == listSize ? View.VISIBLE : View.GONE);
            viewHolder.binding.aboveLine.setVisibility(position == 0 ? View.INVISIBLE : View.VISIBLE);
            viewHolder.binding.lowerLine.setVisibility(position == listSize ? View.INVISIBLE : View.VISIBLE);

            boolean isAddDestAllowed = position == listSize;
            if (position == (listSize - 1)) {
                isAddDestAllowed = true;
            }

            viewHolder.binding.ivAdd.setVisibility(/*isAddDestAllowed ? View.VISIBLE : */View.GONE);
            viewHolder.binding.ivRemove.setVisibility(isAddDestAllowed ? View.GONE : View.VISIBLE);

            if (viewHolder.binding.ivAdd.getVisibility() == View.GONE && viewHolder.binding.ivRemove.getVisibility() == View.GONE) {
                viewHolder.binding.removeAdd.setVisibility(View.GONE);
            }

            viewHolder.binding.toValueTxt.setText(item.getDestAddress());

            viewHolder.binding.ivAdd.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "Add", item);
                }
            });

            viewHolder.binding.ivRemove.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onItemClickList(position, "Remove", item);
                }
            });

            viewHolder.binding.mainSelectionArea.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    if (pos != position) {
                        pos = position;
                        //notifyDataSetChanged();
                    } else {
                        mItemClickListener.onItemClickList(position, "Select", item);
                    }
                }
            });


            if (position == (list.size() - 1)) {
                mItemClickListener.onLastPosition();
            }
        }
    }

    @Override
    public int getItemCount() {
        return list != null ? list.size() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(ArrayList<RideProPublishData.MultiStopData> list) {
        this.list = list;
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemRideProMultiStopBinding binding;

        private ViewHolder(ItemRideProMultiStopBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(int position, String type, RideProPublishData.MultiStopData item);

        void onLastPosition();
    }
}