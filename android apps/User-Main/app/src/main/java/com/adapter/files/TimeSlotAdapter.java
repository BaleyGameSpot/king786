package com.adapter.files;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.ItemTimeslotViewBinding;

import java.util.ArrayList;
import java.util.HashMap;

public class TimeSlotAdapter extends RecyclerView.Adapter<TimeSlotAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final setRecentTimeSlotClickList setRecentTimeSlotClickList;
    @Nullable
    private final ArrayList<HashMap<String, String>> timeSlotList;
    public int isSelectedPos = -1;

    private String LBL_PROVIDER_NOT_AVAIL_NOTE = "";

    public TimeSlotAdapter(@NonNull GeneralFunctions generalFunc, @Nullable ArrayList<HashMap<String, String>> timeSlotList, @Nullable setRecentTimeSlotClickList setRecentTimeSlotClickList) {
        this.timeSlotList = timeSlotList;
        this.setRecentTimeSlotClickList = setRecentTimeSlotClickList;
        this.generalFunc = generalFunc;
        LBL_PROVIDER_NOT_AVAIL_NOTE = generalFunc.retrieveLangLBl("", "LBL_PROVIDER_NOT_AVAIL_NOTE");
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemTimeslotViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(final ViewHolder holder, final int position) {
        assert timeSlotList != null;
        HashMap<String, String> map = timeSlotList.get(position);

        String name = map.get("name");
        holder.binding.availableTimeTxt.setText(name);
        holder.binding.selTimeTxt.setText(name);
        holder.binding.unSelTimeTxt.setText(name);

        String isDriverAvailable = map.get("isDriverAvailable");
        if (isDriverAvailable != null && isDriverAvailable.equalsIgnoreCase("No")) {
            holder.binding.selArea.setVisibility(View.GONE);
            holder.binding.availableArea.setVisibility(View.GONE);
            holder.binding.unSelArea.setVisibility(View.VISIBLE);
            holder.binding.availableArea.setClickable(false);
        } else {
            holder.binding.unSelArea.setVisibility(View.GONE);
            holder.binding.availableArea.setClickable(true);
            if (isSelectedPos != -1) {
                if (isSelectedPos == position) {
                    holder.binding.selArea.setVisibility(View.VISIBLE);
                    holder.binding.availableArea.setVisibility(View.GONE);
                } else {
                    holder.binding.selArea.setVisibility(View.GONE);
                    holder.binding.availableArea.setVisibility(View.VISIBLE);
                }
            } else {
                holder.binding.selArea.setVisibility(View.GONE);
                holder.binding.availableArea.setVisibility(View.VISIBLE);
            }
        }

        holder.binding.availableArea.setOnClickListener(v -> {
            int oldItemPosition = isSelectedPos;
            isSelectedPos = position;
            if (setRecentTimeSlotClickList != null) {
                setRecentTimeSlotClickList.itemTimeSlotLocClick(position);
            }
            notifyItemChanged(oldItemPosition);
            notifyItemChanged(isSelectedPos);
        });

        holder.binding.unSelArea.setOnClickListener(v -> generalFunc.showMessage(holder.binding.unSelTimeTxt, LBL_PROVIDER_NOT_AVAIL_NOTE));
    }

    @Override
    public int getItemCount() {
        return timeSlotList != null ? timeSlotList.size() : 0;
    }

    public interface setRecentTimeSlotClickList {
        void itemTimeSlotLocClick(int position);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemTimeslotViewBinding binding;

        private ViewHolder(ItemTimeslotViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}