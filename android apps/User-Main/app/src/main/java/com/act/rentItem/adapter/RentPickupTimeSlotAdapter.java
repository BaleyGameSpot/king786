package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rentItem.RentItemNewPostActivity;
import com.datepicker.time.TimePickerDialog;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.DateTimeUtils;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;

public class RentPickupTimeSlotAdapter extends RecyclerView.Adapter<RentPickupTimeSlotAdapter.ViewHolder> {

    private final RentItemNewPostActivity activity;
    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> timeSlotsList;

    public RentPickupTimeSlotAdapter(RentItemNewPostActivity activity, ArrayList<HashMap<String, String>> timeSlotsList) {
        this.activity = activity;
        this.generalFunc = activity.generalFunc;
        this.timeSlotsList = timeSlotsList;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new ViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_rent_pickup_time_slot, parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull final ViewHolder holder, @SuppressLint("RecyclerView") final int position) {
        HashMap<String, String> timeSlotsData = timeSlotsList.get(position);

        holder.SlotDayNameTxtView.setText(timeSlotsData.get("dayname"));
        holder.fromTimeSlotVTxt.setText(timeSlotsData.get("FromSlot"));
        holder.toTimeSlotVTxt.setText(timeSlotsData.get("ToSlot"));

        holder.fromSLotArea.setOnClickListener(v -> selectTimeSlot(holder, true, position));
        holder.toSLotArea.setOnClickListener(v -> selectTimeSlot(holder, false, position));
    }

    @SuppressLint("NotifyDataSetChanged")
    private void selectTimeSlot(ViewHolder holder, boolean isFromSlot, int position) {

        String fromTime = getMyTime("", Utils.getText(holder.fromTimeSlotVTxt));
        String toTime = getMyTime("", Utils.getText(holder.toTimeSlotVTxt));

        if (!isFromSlot && GeneralFunctions.parseIntegerValue(-1, fromTime.replace(":", "")) < 0) {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_ADD_FROM_TIME"));
            return;
        }

        MTextView selectedTextView = isFromSlot ? holder.fromTimeSlotVTxt : holder.toTimeSlotVTxt;

        String preSelectedTime = getMyTime(null, Utils.getText(selectedTextView));

        String[] separatedTime = preSelectedTime.split(":");
        TimePickerDialog mTimePicker = TimePickerDialog.newInstance((timePickerDialog, hour, minutes, seconds) -> {

            String time = hour + ":" + minutes;
            int selectedTimeLong = GeneralFunctions.parseIntegerValue(0, time.replace(":", ""));
            int fromTimeLong = GeneralFunctions.parseIntegerValue(0, fromTime.replace(":", ""));

            if (!isFromSlot && fromTimeLong > selectedTimeLong) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_RENT_TO_GREATER_FROM_TXT"));
                return;
            } else if (isFromSlot && fromTimeLong > 1 && Utils.checkText(toTime)) {
                if (GeneralFunctions.parseIntegerValue(0, toTime.replace(":", "")) < selectedTimeLong) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_RENT_TO_GREATER_FROM_TXT"));
                    return;
                }
            }
            String selectedTime = Utils.formatDate(DateTimeUtils.time24Format, DateTimeUtils.TimeFormat, time);
            selectedTextView.setText(selectedTime);
            timeSlotsList.get(position).put(isFromSlot ? "FromSlot" : "ToSlot", selectedTime);
            notifyDataSetChanged();

        }, Integer.parseInt(separatedTime[0]), Integer.parseInt(separatedTime[1]), DateTimeUtils.Is24HourTime);
        mTimePicker.show(activity.getSupportFragmentManager(), "TimePickerDialog");
    }

    public String getMyTime(String time, String textDate) {
        try {
            Date calStart = new SimpleDateFormat(DateTimeUtils.TimeFormat).parse(textDate);
            time = Utils.convertDateToFormat(DateTimeUtils.time24Format, calStart);
        } catch (ParseException e) {
            Logger.e("Exception", "::" + e.getMessage());
            if (time == null) {
                time = Utils.convertDateToFormat(DateTimeUtils.time24Format, Calendar.getInstance(MyUtils.getLocale()).getTime());
            }
        }
        return time;
    }

    @Override
    public int getItemCount() {
        return timeSlotsList.size();
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final CardView toSLotArea, fromSLotArea;
        private final MTextView SlotDayNameTxtView, fromTimeSlotVTxt, toTimeSlotVTxt;

        public ViewHolder(View itemView) {
            super(itemView);

            SlotDayNameTxtView = (MTextView) itemView.findViewById(R.id.SlotDayNameTxtView);
            fromTimeSlotVTxt = (MTextView) itemView.findViewById(R.id.fromTimeSlotVTxt);
            toTimeSlotVTxt = (MTextView) itemView.findViewById(R.id.toTimeSlotVTxt);
            toSLotArea = (CardView) itemView.findViewById(R.id.toSLotArea);
            fromSLotArea = (CardView) itemView.findViewById(R.id.fromSLotArea);
        }
    }
}