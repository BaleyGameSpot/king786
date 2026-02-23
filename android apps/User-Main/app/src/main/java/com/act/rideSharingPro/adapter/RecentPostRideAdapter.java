package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.ItemRideProRecentPostRidesBinding;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class RecentPostRideAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private final GeneralFunctions generalFunc;
    @Nullable
    private JSONArray mRecentLocArr;
    private final OnClickListener listener;

    public RecentPostRideAdapter(@NonNull GeneralFunctions generalFunctions, @NonNull JSONArray jsonArray, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunctions;
        this.mRecentLocArr = jsonArray;
        this.listener = listener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemRideProRecentPostRidesBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        ViewHolder bHolder = (ViewHolder) holder;

        JSONObject mItemObj = generalFunc.getJsonObject(mRecentLocArr, position);

        bHolder.binding.recentStartLocationTxt.setText(generalFunc.getJsonValueStr("tStartLocation", mItemObj));
        bHolder.binding.recentEndLocationTxt.setText(generalFunc.getJsonValueStr("tEndLocation", mItemObj));
        StringBuilder sb = new StringBuilder();
        sb.append(generalFunc.getJsonValueStr("tDisplayDate", mItemObj));
        sb.append(" | ");
        sb.append(generalFunc.getJsonValueStr("AVAILABLE_SEATS_TXT", mItemObj));
        bHolder.binding.recentLocationSubTxt.setText(sb);

        if (generalFunc.isRTLmode()) {
//            bHolder.binding.ivLocArrow.setRotation(180);
            bHolder.binding.ivDirectionArrow.setRotation(180);
        }

        ///////////////////////////////////////////////////////////////-----------------------------
        bHolder.binding.contentArea.setOnClickListener(v -> listener.onRecentLocationItemClick(mItemObj));
    }

    @Override
    public int getItemCount() {
        return mRecentLocArr != null ? mRecentLocArr.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray servicesArr) {
        this.mRecentLocArr = servicesArr;
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemRideProRecentPostRidesBinding binding;

        private ViewHolder(ItemRideProRecentPostRidesBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onRecentLocationItemClick(JSONObject itemObj);
    }
}