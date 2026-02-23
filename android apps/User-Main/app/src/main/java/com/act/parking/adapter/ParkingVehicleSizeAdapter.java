package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemParkingVehicleSizeBinding;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingVehicleSizeAdapter extends RecyclerView.Adapter<ParkingVehicleSizeAdapter.ViewHolder> {

    private final Context mContext;
    private final ArrayList<HashMap<String, String>> list;
    private final PlanOnClickListener mPlanOnClickListener;
    private int selPlanPos = -1;
    private final int appThemeColor, whiteColor;
    private GeneralFunctions generalFunc;

    public ParkingVehicleSizeAdapter(Context context, GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list, PlanOnClickListener planOnClickListener) {
        this.mContext = context;
        this.list = list;
        this.mPlanOnClickListener = planOnClickListener;
        this.generalFunc = generalFunc;
        this.appThemeColor = ContextCompat.getColor(context, R.color.appThemeColor_1);
        this.whiteColor = ContextCompat.getColor(context, R.color.white);
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        return new ViewHolder(ItemParkingVehicleSizeBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("RecyclerView")
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);
        if (position == (list.size() - 1)) {
            holder.binding.extraView.setVisibility(View.GONE);
        } else {
            holder.binding.extraView.setVisibility(View.VISIBLE);
        }

        holder.binding.mainArea.setOnClickListener(view -> {
            mPlanOnClickListener.onPlanClick(position, selPlanPos, mapData);
            selPlanPos = position;
        });
        holder.binding.txtSize.setText(mapData.get("size"));
        holder.binding.txtSizeEg.setText(generalFunc.retrieveLangLBl("", mapData.get("sizeEg")));

        /////--------------------------------------------------------------------------------------
        holder.binding.check.setVisibility(View.GONE);
        holder.binding.unCheck.setVisibility(View.GONE);

        if (position == selPlanPos) {

            holder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(appThemeColor));
            holder.binding.txtSize.setTextColor(mContext.getResources().getColor(R.color.white));
            holder.binding.txtSizeEg.setTextColor(mContext.getResources().getColor(R.color.white));
            holder.binding.check.setVisibility(View.VISIBLE);
        } else {
            holder.binding.unCheck.setVisibility(View.VISIBLE);
            holder.binding.mainArea.setBackgroundTintList(ColorStateList.valueOf(whiteColor));
            holder.binding.txtSize.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
            holder.binding.txtSizeEg.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));

        }

    }


    @Override
    public int getItemCount() {
        return list.size();
    }

    public void setSelectedSeat(int selectedSize) {
        this.selPlanPos = selectedSize;
    }

    public interface PlanOnClickListener {
        void onPlanClick(int position, int selPlanPos, HashMap<String, String> list);
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemParkingVehicleSizeBinding binding;

        public ViewHolder(ItemParkingVehicleSizeBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}