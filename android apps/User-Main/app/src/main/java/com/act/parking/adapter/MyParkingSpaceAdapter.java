package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.ItemMyParkingSpaceListBinding;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class MyParkingSpaceAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnClickListener onClickListener;
    Context mcontext;

    public MyParkingSpaceAdapter(Context context, GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.onClickListener = listener;
        this.mcontext = context;
    }

    @NotNull
    @Override
    public DataViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int i) {
        return new DataViewHolder(ItemMyParkingSpaceListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        DataViewHolder viewHolder = (DataViewHolder) holder;
        HashMap<String, String> mapData = list.get(position);

        boolean isBooking = Utils.checkText(mapData.get("vParkingSpaceBookingNo"));

        viewHolder.binding.bookNoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_REF_NO_TXT") + "#" + (isBooking ? mapData.get("vParkingSpaceBookingNo") : mapData.get("vParkingSpaceNo")));
        String date = mapData.get("BookingDate");
        if (isBooking) {
            viewHolder.binding.timeTxt.setText(date.substring(11));
        } else {
            viewHolder.binding.timeTxt.setVisibility(View.GONE);
        }

        viewHolder.binding.dateTxt.setText(isBooking ? date.substring(0, 10) : mapData.get("dAddedDate"));
        viewHolder.binding.bkAddress.setText(mapData.get("tAddress"));
        viewHolder.binding.bkStatus.setText(mapData.get("status"));
        viewHolder.binding.priceTxt.setText(mapData.get("Price"));
        viewHolder.binding.priceMsgTxt.setText(mapData.get("PriceSubText"));
        if (Utils.checkText(mapData.get("StatusBgcolor"))) {
            viewHolder.binding.bkStatus.setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(mapData.get("StatusBgcolor"))));
        }
        viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemMyParkingSpaceListBinding binding;

        private DataViewHolder(@NonNull ItemMyParkingSpaceListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, HashMap<String, String> mapData);
    }
}