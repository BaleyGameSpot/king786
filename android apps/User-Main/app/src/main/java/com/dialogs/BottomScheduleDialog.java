package com.dialogs;

import android.view.View;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;

import androidx.appcompat.app.AlertDialog;

import com.act.RequestBidInfoActivity;
import com.activity.ParentActivity;
import com.datepicker.DatePickerTimeline;
import com.datepicker.OnDateSelectedListener;
import com.datepicker.time.TimePickerDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.utils.DateTimeUtils;
import com.utils.LayoutDirection;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;
import java.util.Objects;
import java.util.concurrent.TimeUnit;

public class BottomScheduleDialog {
    private ParentActivity act;
    private GeneralFunctions generalFunc;
    public JSONObject obj_userProfile;
    private OndateSelectionListener onDateSelectedListener;
    private BottomSheetDialog hotoUseDialog;
    private Calendar tripDateTime = Calendar.getInstance();

    public BottomScheduleDialog(ParentActivity parentActivity, OndateSelectionListener onDateSelectedListener) {
        this.act = parentActivity;
        this.onDateSelectedListener = onDateSelectedListener;
        this.generalFunc = act.generalFunc;
        this.obj_userProfile = act.obj_userProfile;
    }

    public BottomScheduleDialog() {
        super();
    }

    public void showPreferenceDialog(String title, String posBtn, String negBtn, String id, boolean isReselectDate, Calendar cal) {
        AlertDialog.Builder builder = new AlertDialog.Builder(act);

        View dialogView = View.inflate(act, R.layout.design_bottom_schedule, null);
        builder.setView(dialogView);

        MTextView titleTxt = dialogView.findViewById(R.id.titleTxt);
        MTextView selectedDate = dialogView.findViewById(R.id.selectedDate);
        MTextView selectedTime = dialogView.findViewById(R.id.selectedTime);
        DatePickerTimeline datePickerTimeline = dialogView.findViewById(R.id.dateTimeline);
        MButton skipTxtArea = ((MaterialRippleLayout) dialogView.findViewById(R.id.skipTxtArea)).getChildView();
        MButton okTxt = ((MaterialRippleLayout) dialogView.findViewById(R.id.okTxt)).getChildView();
        MTextView hourselectotTxt = dialogView.findViewById(R.id.hourselectotTxt);
        MTextView minselectotTxt = dialogView.findViewById(R.id.minselectotTxt);
        MTextView AmpmselectotTxt = dialogView.findViewById(R.id.AmpmselectotTxt);

        Calendar currentCal = Calendar.getInstance(MyUtils.getLocale());
        int minMinute;
        if (MyApp.getInstance().getCurrentAct() instanceof RequestBidInfoActivity) {
            minMinute = (Integer.parseInt(generalFunc.getJsonValueStr("MINIMUM_HOURS_LATER_BIDDING", obj_userProfile)) * 60) + 1;
        } else {
            minMinute = (Integer.parseInt(generalFunc.getJsonValueStr("MINIMUM_HOURS_LATER_BOOKING", obj_userProfile))) + 1;
        }
        currentCal.add(Calendar.MINUTE, minMinute);


        Locale locale = new Locale("en");

        SimpleDateFormat sdf = new SimpleDateFormat(DateTimeUtils.DateFormat, locale);
        SimpleDateFormat timeSdf = new SimpleDateFormat(DateTimeUtils.TimeFormat, locale);
        SimpleDateFormat hourSdf = new SimpleDateFormat(DateTimeUtils.Is24HourTime ? "HH" : "hh", locale);
        SimpleDateFormat minSdf = new SimpleDateFormat("mm", locale);
        SimpleDateFormat ampmsdf = new SimpleDateFormat(DateTimeUtils.Is24HourTime ? "" : "aa", locale);


        if (Utils.checkText(title)) {
            titleTxt.setText(title);
            titleTxt.setVisibility(View.VISIBLE);
        } else {
            titleTxt.setVisibility(View.GONE);
        }

        if (Utils.checkText(posBtn)) {
            okTxt.setText(posBtn);
            okTxt.setVisibility(View.VISIBLE);
        } else {
            okTxt.setVisibility(View.GONE);
        }

        if (Utils.checkText(negBtn)) {
            skipTxtArea.setText(negBtn);
            skipTxtArea.setVisibility(View.VISIBLE);
        } else {
            skipTxtArea.setVisibility(View.INVISIBLE);
        }

        datePickerTimeline.setInitialDate(currentCal.get(Calendar.YEAR), currentCal.get(Calendar.MONTH), currentCal.get(Calendar.DAY_OF_MONTH));

        if (isReselectDate) {
            tripDateTime = cal;
            selectedDate.setText(sdf.format(tripDateTime.getTime()));
            selectedTime.setText(timeSdf.format(tripDateTime.getTime()));
            hourselectotTxt.setText(hourSdf.format(tripDateTime.getTime()));
            minselectotTxt.setText(minSdf.format(tripDateTime.getTime()));
            AmpmselectotTxt.setText(ampmsdf.format(tripDateTime.getTime()));

            datePickerTimeline.setActiveDateWithScroll(tripDateTime);
        } else {
            tripDateTime = currentCal;
            selectedDate.setText(sdf.format(currentCal.getTime()));
            selectedTime.setText(timeSdf.format(currentCal.getTime()));
            hourselectotTxt.setText(hourSdf.format(currentCal.getTime()));
            minselectotTxt.setText(minSdf.format(currentCal.getTime()));
            AmpmselectotTxt.setText(ampmsdf.format(currentCal.getTime()));
        }

        OnDateSelectedListener OnDateSelectedListener = new OnDateSelectedListener() {
            @Override
            public void onDateSelected(int year, int month, int day, int dayOfWeek, int weekOfMonth) {

                tripDateTime.set(Calendar.YEAR, year);
                tripDateTime.set(Calendar.MONTH, month);
                tripDateTime.set(Calendar.DAY_OF_MONTH, day);

                selectedDate.setText(sdf.format(tripDateTime.getTime()));
            }

            @Override
            public void onDisabledDateSelected(int year, int month, int day, int dayOfWeek, boolean isDisabled) {
                // Do Something
            }
        };

        datePickerTimeline.setOnDateSelectedListener(OnDateSelectedListener);
        View.OnClickListener timePickerListener = v -> {
            TimePickerDialog mTimePicker = TimePickerDialog.newInstance((timePickerDialog, hour, minutes, seconds) -> {

                tripDateTime.set(Calendar.HOUR_OF_DAY, hour);
                tripDateTime.set(Calendar.MINUTE, minutes);
                tripDateTime.set(Calendar.SECOND, 0);
                tripDateTime.set(Calendar.MILLISECOND, 0);

                selectedTime.setText(timeSdf.format(tripDateTime.getTime()));
                hourselectotTxt.setText(hourSdf.format(tripDateTime.getTime()));
                minselectotTxt.setText(minSdf.format(tripDateTime.getTime()));
                AmpmselectotTxt.setText(ampmsdf.format(tripDateTime.getTime()));

            }, tripDateTime.get(Calendar.HOUR_OF_DAY), tripDateTime.get(Calendar.MINUTE), DateTimeUtils.Is24HourTime);
            mTimePicker.show(act.getSupportFragmentManager(), "TimePickerDialog");
        };


        hourselectotTxt.setOnClickListener(timePickerListener);
        minselectotTxt.setOnClickListener(timePickerListener);
        if (Utils.checkText(Utils.getText(AmpmselectotTxt))) {
            AmpmselectotTxt.setVisibility(View.VISIBLE);
            AmpmselectotTxt.setOnClickListener(timePickerListener);
        } else {
            AmpmselectotTxt.setVisibility(View.GONE);
        }


        okTxt.setOnClickListener(view -> {

            if (MyApp.getInstance().getCurrentAct() instanceof RequestBidInfoActivity) {

                int minHours = Integer.parseInt(generalFunc.getJsonValueStr("MINIMUM_HOURS_LATER_BIDDING", obj_userProfile));
                int maxHours = Integer.parseInt(generalFunc.getJsonValueStr("MAXIMUM_HOURS_LATER_BIDDING", obj_userProfile));

                if (!Utils.isValidTimeSelect(tripDateTime.getTime(), TimeUnit.HOURS.toMillis(minHours)) || Utils.isValidTimeSelect(tripDateTime.getTime(), TimeUnit.HOURS.toMillis(maxHours))) {
                    generalFunc.showGeneralMessage("", generalFunc.getJsonValueStr("LBL_INVALID_BIDDING_MAX_NOTE_MSG", obj_userProfile));
                    return;
                }
                onDateSelectedListener.onScheduleSelection(Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, tripDateTime.getTime()), tripDateTime.getTime(), id);
            } else {

                int minHours = Integer.parseInt(generalFunc.getJsonValueStr("MINIMUM_HOURS_LATER_BOOKING", obj_userProfile));
                int maxHours = Integer.parseInt(generalFunc.getJsonValueStr("MAXIMUM_HOURS_LATER_BOOKING", obj_userProfile));

                if (!Utils.isValidTimeSelect(tripDateTime.getTime(), TimeUnit.MINUTES.toMillis(minHours)) || Utils.isValidTimeSelect(tripDateTime.getTime(), TimeUnit.MINUTES.toMillis(maxHours))) {
                    generalFunc.showGeneralMessage("", generalFunc.getJsonValueStr("LBL_INVALID_PICKUP_MAX_NOTE_MSG", obj_userProfile));
                    return;
                }

                onDateSelectedListener.onScheduleSelection(Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, tripDateTime.getTime()), tripDateTime.getTime(), id);

            }

            hotoUseDialog.dismiss();

        });

        skipTxtArea.setOnClickListener(view -> hotoUseDialog.dismiss());

        hotoUseDialog = new BottomSheetDialog(act);
        hotoUseDialog.setContentView(dialogView);
        View bottomSheetView = Objects.requireNonNull(hotoUseDialog.getWindow()).getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(act.getResources().getColor(android.R.color.transparent));
        BottomSheetBehavior<View> mBehavior = BottomSheetBehavior.from((View) dialogView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(600, act));

        hotoUseDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(hotoUseDialog);
        Animation a = AnimationUtils.loadAnimation(act, R.anim.bottom_up);
        a.reset();
        bottomSheetView.clearAnimation();
        bottomSheetView.startAnimation(a);
        hotoUseDialog.show();
    }

    public interface OndateSelectionListener {
        void onScheduleSelection(String selDateTime, Date date, String iCabBookingId);
    }
}