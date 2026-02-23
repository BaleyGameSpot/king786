package com.datepicker;

public interface OnDateSelectedListener {
    void onDateSelected(int year, int month, int day, int dayOfWeek,int weekOfMonth);

    void onDisabledDateSelected(int year, int month, int day, int dayOfWeek, boolean isDisabled);
}
