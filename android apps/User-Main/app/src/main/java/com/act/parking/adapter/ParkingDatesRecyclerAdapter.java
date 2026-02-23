package com.act.parking.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.ColorStateList;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.view.MTextView;

import java.util.ArrayList;

/**
 * Created by tarwindersingh on 06/01/18.
 */

public class ParkingDatesRecyclerAdapter extends RecyclerView.Adapter<ParkingDatesRecyclerAdapter.ViewHolder> {

    GeneralFunctions generalFunc;
    ArrayList<String> listData;
    Context mContext;
    OnDateSelectListener onDateSelectListener;
    private int selectedPos = -1;

    String selectedDate;


    public ParkingDatesRecyclerAdapter(GeneralFunctions generalFunc, ArrayList<String> listData, Context mContext, String selectedDate) {
        this.generalFunc = generalFunc;
        this.listData = listData;
        this.mContext = mContext;
        this.selectedDate = selectedDate;
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_dates_design_parking, parent, false);

        ViewHolder viewHolder = new ViewHolder(view);
        return viewHolder;
    }

    public void setSelectedDate(String selectedDate) {
        this.selectedDate = selectedDate;
    }

    public void setOnDateSelectListener(OnDateSelectListener onDateSelectListener) {
        this.onDateSelectListener = onDateSelectListener;
    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final ViewHolder holder, @SuppressLint("RecyclerView") final int position) {

        holder.dayTxtView.setText(listData.get(position));
        if (!listData.get(position).equals(selectedDate)) {
            selectedPos = position;
            holder.dayTxtView.setTextColor(mContext.getResources().getColor(R.color.text23Pro_Dark));
            holder.ll_day.setBackgroundTintList(null);
        } else {
            holder.dayTxtView.setTextColor(mContext.getResources().getColor(R.color.white));
            holder.ll_day.setBackgroundTintList(ColorStateList.valueOf(mContext.getResources().getColor(R.color.appThemeColor_1)));
        }
        holder.containerView.setOnClickListener(view -> {
            if (onDateSelectListener != null) {
                onDateSelectListener.onDateSelect(position);
            }
        });

    }

    public void setSelectedSeat(int selectedSeat) {
        this.selectedPos = selectedSeat;
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        return listData.size();
    }


    public interface OnDateSelectListener {
        void onDateSelect(int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {


        private MTextView dayTxtView;
        private LinearLayout ll_day;
        private View containerView;

        public ViewHolder(View view) {
            super(view);

            containerView = view;
            dayTxtView = (MTextView) view.findViewById(R.id.dayTxtView);
            ll_day = (LinearLayout) view.findViewById(R.id.ll_day);

        }
    }
}
