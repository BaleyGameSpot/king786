package com.datepicker.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.datepicker.OnDateSelectedListener;
import com.datepicker.TimelineView;
import com.buddyverse.main.R;
import com.utils.MyUtils;

import java.text.DateFormatSymbols;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;

public class TimelineAdapter extends RecyclerView.Adapter<TimelineAdapter.ViewHolder> {
    private static final String TAG = "TimelineAdapter";
    private static final String[] WEEK_DAYS = DateFormatSymbols.getInstance().getShortWeekdays();
    private static final String[] MONTH_NAME = DateFormatSymbols.getInstance().getShortMonths();

    private Calendar calendar = Calendar.getInstance();
    private TimelineView timelineView;
    private Date[] deactivatedDates;
    private Context context;

    private OnDateSelectedListener listener;

    private View selectedView;
    private int selectedPosition;
    SimpleDateFormat sdf;


    public TimelineAdapter(TimelineView timelineView, int selectedPosition) {
        this.timelineView = timelineView;
        this.selectedPosition = selectedPosition;
        sdf = new SimpleDateFormat("E", new Locale("en"));
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        final View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.timeline_item_layout, parent, false);
        context = parent.getContext();
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, final int position) {
        resetCalendar();
        calendar.add(Calendar.DAY_OF_YEAR, position);

        final int year = calendar.get(Calendar.YEAR);
        final int month = calendar.get(Calendar.MONTH);
        final int dayOfWeek = calendar.get(Calendar.DAY_OF_WEEK);
        final int day = calendar.get(Calendar.DAY_OF_MONTH);
        final int weekOfMonth = calendar.get(Calendar.WEEK_OF_MONTH);


        final boolean isDisabled = holder.bind(month, day, dayOfWeek, year, position);
        holder.rootView.setTag(position);

        holder.rootView.setOnClickListener(v -> {
            if (selectedView != null) {
                selectedView.setBackground(timelineView.getResources().getDrawable(R.drawable.schedule_border_flat_line));
            }
            if (!isDisabled) {
                v.setBackground(timelineView.getResources().getDrawable(R.drawable.schedule_back_apptheme));

                selectedPosition = position;
                selectedView = v;
                if (listener != null)
                    listener.onDateSelected(year, month, day, dayOfWeek, weekOfMonth);
            } else {
                v.setBackground(timelineView.getResources().getDrawable(R.drawable.schedule_border_flat_line));
                if (listener != null)
                    listener.onDisabledDateSelected(year, month, day, dayOfWeek, isDisabled);
            }
            notifyDataSetChanged();
        });
    }

    private void resetCalendar() {
        calendar.set(timelineView.getYear(), timelineView.getMonth(), timelineView.getDate(),
                1, 0, 0);
    }

    /**
     * Set the position of selected date
     *
     * @param selectedPosition active date Position
     */
    public void setSelectedPosition(int selectedPosition) {
        this.selectedPosition = selectedPosition;
    }

    @Override
    public int getItemCount() {
        return 90;
    }

    public void disableDates(Date[] dates) {
        this.deactivatedDates = dates;
        notifyDataSetChanged();
    }

    public void setDateSelectedListener(OnDateSelectedListener listener) {
        this.listener = listener;
    }

    class ViewHolder extends RecyclerView.ViewHolder {
        private final TextView dateView, dayView;
        private View rootView;

        ViewHolder(@NonNull View itemView) {
            super(itemView);
            dateView = itemView.findViewById(R.id.dateView);
            dayView = itemView.findViewById(R.id.dayView);
            rootView = itemView.findViewById(R.id.rootView);
        }

        boolean bind(int month, int day, int dayOfWeek, int year, int position) {
            Calendar currentCal = Calendar.getInstance(MyUtils.getLocale());
            currentCal.set(Calendar.DAY_OF_WEEK, dayOfWeek);
            dayView.setText((sdf.format(currentCal.getTime())));
            dateView.setText(String.valueOf(day));

            if (selectedPosition == position) {
                dateView.setTextColor(ContextCompat.getColor(context,
                        R.color.appThemeColor_1));
                dayView.setTextColor(ContextCompat.getColor(context, R.color.white));
                dateView.setTextColor(ContextCompat.getColor(context, R.color.white));
                rootView.setBackground(timelineView.getResources().getDrawable(R.drawable.schedule_back_apptheme));
                selectedView = rootView;
            } else {
                dateView.setTextColor(ContextCompat.getColor(context,
                        R.color.black));
                dayView.setTextColor(ContextCompat.getColor(context, R.color.text23Pro_Dark));
                dateView.setTextColor(ContextCompat.getColor(context, R.color.textSub23Pro_gray));
                rootView.setBackground(timelineView.getResources().getDrawable(R.drawable.schedule_border_flat_line));
            }

            for (Date date : deactivatedDates) {
                Calendar tempCalendar = Calendar.getInstance();
                tempCalendar.setTime(date);
                if (tempCalendar.get(Calendar.DAY_OF_MONTH) == day &&
                        tempCalendar.get(Calendar.MONTH) == month &&
                        tempCalendar.get(Calendar.YEAR) == year) {
                    dateView.setTextColor(timelineView.getDisabledDateColor());
                    dayView.setTextColor(timelineView.getDisabledDateColor());

                    rootView.setBackground(null);
                    return true;
                }
            }

            return false;
        }
    }


}