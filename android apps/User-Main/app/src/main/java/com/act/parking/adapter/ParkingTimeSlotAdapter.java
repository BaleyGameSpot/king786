package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.act.parking.ParkingArrivalScheduleActivity;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

/**
 * Created by Admin on 09-10-2017.
 */

public class ParkingTimeSlotAdapter extends RecyclerView.Adapter<ParkingTimeSlotAdapter.ViewHolder> {

    Context mContext;
    View view;
    List selectedPos = new ArrayList();
    int selPos = -1;
    setRecentTimeSlotClickList setRecentTimeSlotClickList;

    ArrayList<HashMap<String, String>> timeSlotList;
    GeneralFunctions generalFunctions;
    String LBL_PROVIDER_NOT_AVAIL_NOTE = "";
    ArrayList<String> SelectedSlots;
    boolean isArrival = false;

    public ParkingTimeSlotAdapter(Context context, ArrayList<HashMap<String, String>> timeSlotList, ArrayList<String> SelectedSlots) {
        this.mContext = context;
        this.timeSlotList = timeSlotList;
        generalFunctions = new GeneralFunctions(mContext);
        this.SelectedSlots = SelectedSlots;
        LBL_PROVIDER_NOT_AVAIL_NOTE = generalFunctions.retrieveLangLBl("", "LBL_PROVIDER_NOT_AVAIL_NOTE");
    }


    @Override
    public ParkingTimeSlotAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view_;
        if (mContext instanceof ParkingArrivalScheduleActivity) {
            isArrival = true;
            view_ = LayoutInflater.from(mContext).inflate(R.layout.item_arrival_timeslot_view_parking, parent, false);
        } else {
            isArrival = false;
            view_ = LayoutInflater.from(mContext).inflate(R.layout.item_timeslot_view_parking, parent, false);
        }
        return new ParkingTimeSlotAdapter.ViewHolder(view_);
    }

    @SuppressLint("UseCompatLoadingForDrawables")
    @Override
    public void onBindViewHolder(final ParkingTimeSlotAdapter.ViewHolder holder, final int position) {
        HashMap<String, String> map = timeSlotList.get(position);
        String name = map.get("name");
        if (SelectedSlots.size() > 0 && !isArrival) {
            if (!SelectedSlots.contains(name)) {
                selectedPos.remove((Integer) position);
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.history_reason_border));
                holder.mainarea.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mContext, R.color.grayBorder_E6)));
            } else {
                selectedPos.add(position);
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.white));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.btn_border));
                holder.mainarea.setBackgroundTintList(null);
            }
        }
        if (isArrival) {
            if (position == selPos) {
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.white));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.btn_border));
                holder.mainarea.setBackgroundTintList(null);
            } else {
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.history_reason_border));
                holder.mainarea.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mContext, R.color.grayBorder_E6)));
            }
        }
        holder.stratTimeTxtView.setText(name);

        holder.mainarea.setOnClickListener(v -> {

            if (selectedPos.contains(position) && !isArrival) {
                selectedPos.remove(selectedPos.indexOf(position));
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.history_reason_border));
                holder.mainarea.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mContext, R.color.grayBorder_E6)));
            } else {
                selectedPos.add(position);
                holder.stratTimeTxtView.setTextColor(mContext.getResources().getColor(R.color.white));
                holder.mainarea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.btn_border));
                holder.mainarea.setBackgroundTintList(null);

            }


            if (setRecentTimeSlotClickList != null) {
                setRecentTimeSlotClickList.itemTimeSlotLocClick(position);
            }

        });

    }

    @Override
    public int getItemCount() {
        return timeSlotList.size();
    }

    public void updateSelectedSlots(ArrayList<String> SelectedSlots) {
        this.SelectedSlots = SelectedSlots;
    }

    public void setSelectedTime(int selectedTime) {
        this.selPos = selectedTime;
    }


    public void setOnClickList(setRecentTimeSlotClickList setRecentTimeSlotClickList) {
        this.setRecentTimeSlotClickList = setRecentTimeSlotClickList;
    }

    public interface setRecentTimeSlotClickList {
        void itemTimeSlotLocClick(int position);
    }

    public class ViewHolder extends RecyclerView.ViewHolder {

        MTextView stratTimeTxtView;
        LinearLayout mainarea;


        public ViewHolder(View itemView) {
            super(itemView);

            stratTimeTxtView = (MTextView) itemView.findViewById(R.id.parkingTimeTxtView);
            mainarea = (LinearLayout) itemView.findViewById(R.id.mainarea);


        }
    }


}
