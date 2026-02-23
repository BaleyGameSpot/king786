package com.act.intercity;

import android.content.Context;
import android.os.Bundle;
import android.view.View;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.datepicker.time.TimePickerDialog;
import com.general.DatePicker;
import com.general.files.ActUtils;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityIntercityDateTimeSelectorBinding;
import com.utils.DateTimeUtils;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.Calendar;

public class IntercityDateTimeSelectorActivity extends ParentActivity {
    ActivityIntercityDateTimeSelectorBinding binding;
    private MButton continueButton;
    private Calendar dateTimeCalender = Calendar.getInstance(MyUtils.getLocale());
    private final Calendar maxCalender = Calendar.getInstance(MyUtils.getLocale());
    private final Calendar minCalender = Calendar.getInstance(MyUtils.getLocale());
    private boolean isFromPickup, isReserveRadioChecked = false;
    int MAX_INTERCITY_DAYS, MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY, INTERCITY_MINIMUM_HOURS_LATER_BOOKING;
    private String lastSelectedDateTime;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_intercity_date_time_selector);
        initializeUi();
    }

    private void initializeUi() {

        isFromPickup = getIntent().getBooleanExtra("isPickup", false);

        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_SELECT_DATE_TIME"));

        binding.leaveNowTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_LEAVE_NOW_TXT"));
        binding.leaveNowSubTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_GET_TRIP_TXT"));

        if (Utils.checkText(generalFunc.getJsonValueStr("MAX_INTERCITY_DAYS", obj_userProfile))) {
            MAX_INTERCITY_DAYS = Integer.parseInt(generalFunc.getJsonValueStr("MAX_INTERCITY_DAYS", obj_userProfile));
        }
        if (Utils.checkText(generalFunc.getJsonValueStr("MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY", obj_userProfile))) {
            MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY = Integer.parseInt(generalFunc.getJsonValueStr("MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY", obj_userProfile));
        }

        if (Utils.checkText(generalFunc.getJsonValueStr("INTERCITY_MINIMUM_HOURS_LATER_BOOKING", generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON))))) {
            INTERCITY_MINIMUM_HOURS_LATER_BOOKING = (Integer.parseInt(generalFunc.getJsonValueStr("INTERCITY_MINIMUM_HOURS_LATER_BOOKING", obj_userProfile)) * 60) + 1;
        }
        if (Utils.checkText(getIntent().getStringExtra("lastSelectedDateTime"))) {
            lastSelectedDateTime = getIntent().getStringExtra("lastSelectedDateTime");
        }


        binding.reserveTripTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_RESERVE_TRIP_TXT"));
        binding.reserveTripSubTextView.setText(getNote(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_BOOK_TRIP_ADVANCE_TXT"), MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY));

        binding.dateHeaderTextview.setText(generalFunc.retrieveLangLBl("", "LBL_DATE_TXT"));
        binding.dateValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_SELECT_DATE"));

        binding.timeHeaderTextview.setText(generalFunc.retrieveLangLBl("", "LBL_TIME_TXT"));
        binding.timeValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_TIME"));

        binding.matchDriverTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_MATCH_WITH_DRIVER_NOTE"));

        binding.dropOffNoteTextView.setText(getNote(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_KEEP_CAR_NOTE"), MAX_INTERCITY_DAYS));

        continueButton = ((MaterialRippleLayout) binding.continueButton).getChildView();
        continueButton.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_CONTINUE_TXT"));
        continueButton.setId(Utils.generateViewId());

        if (isFromPickup) {
            maxCalender.set(Calendar.DATE, dateTimeCalender.get(Calendar.DATE) + MAX_PICKUP_DAYS_ALLOWED_FOR_INTERCITY);
            dateTimeCalender.add(Calendar.MINUTE, INTERCITY_MINIMUM_HOURS_LATER_BOOKING);
            minCalender.add(Calendar.MINUTE, INTERCITY_MINIMUM_HOURS_LATER_BOOKING - 1);

            binding.leaveNowRadioButton.setChecked(true);
            isReserveRadioChecked = false;
            binding.leaveNowParentLayout.setVisibility(View.VISIBLE);
            binding.reserveTripParentLayout.setVisibility(View.VISIBLE);
            binding.dropOffNoteParentLayout.setVisibility(View.GONE);
            binding.titleTextVIew.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_WANT_TO_LEAVE"));
            binding.subTitleTextVIew.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_FROM").concat(": ").concat(getIntent().getStringExtra("pickUpAddress")));
        } else {
            minCalender.setTime(Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, getIntent().getStringExtra("minCalenderDate")));
            maxCalender.set(minCalender.get(Calendar.YEAR), minCalender.get(Calendar.MONTH), minCalender.get(Calendar.DATE) + MAX_INTERCITY_DAYS, minCalender.get(Calendar.HOUR_OF_DAY), minCalender.get(Calendar.MINUTE), minCalender.get(Calendar.SECOND));
            dateTimeCalender = (Calendar) minCalender.clone();
            binding.leaveNowParentLayout.setVisibility(View.GONE);
            binding.reserveTripRadioButton.setChecked(true);
            binding.reserveTripParentLayout.setVisibility(View.GONE);
            binding.reserveTripExtendLayout.setVisibility(View.VISIBLE);
            binding.dropOffNoteParentLayout.setVisibility(View.VISIBLE);
            binding.titleTextVIew.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_WANT_TO_BACK"));
            binding.subTitleTextVIew.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_AT").concat(": ").concat(getIntent().getStringExtra("pickUpAddress")));
        }
        if (Utils.checkText(lastSelectedDateTime)) {
            binding.dateValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, lastSelectedDateTime)));
            binding.timeValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.TimeFormat, Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, lastSelectedDateTime)));

            dateTimeCalender.setTime(Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, lastSelectedDateTime));
        }

        clickHandles();
        if (getIntent().hasExtra("isReserveTrip") && getIntent().getBooleanExtra("isReserveTrip", false)) {
            binding.reserveTripParentLayout.performClick();
        }
        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
    }

    private String getNote(String LBL_VALUE, int days) {
        return Utils.checkText(LBL_VALUE) ? LBL_VALUE.replace("####", String.valueOf(days)) : "";
    }

    private void clickHandles() {
        addToClickHandler(binding.leaveNowParentLayout);
        addToClickHandler(binding.reserveTripParentLayout);
        addToClickHandler(binding.leaveNowRadioButton);
        addToClickHandler(binding.reserveTripRadioButton);
        addToClickHandler(binding.dateSelectParentBox);
        addToClickHandler(binding.timeSelectParentBox);
        addToClickHandler(binding.toolbarInclude.backImgView);
        addToClickHandler(continueButton);
    }

    @Override
    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.toolbarInclude.backImgView.getId()) {
            getOnBackPressedDispatcher().onBackPressed();
        } else if (i == binding.leaveNowParentLayout.getId() || i == binding.leaveNowRadioButton.getId()) {
            makeTripSelection(binding.leaveNowParentLayout);
        } else if (i == binding.reserveTripParentLayout.getId() || i == binding.reserveTripRadioButton.getId()) {
            makeTripSelection(binding.reserveTripParentLayout);
        } else if (i == binding.dateSelectParentBox.getId()) {
            openDatePicker();
        } else if (i == binding.timeSelectParentBox.getId()) {
            openTimePicker();
        } else if (i == continueButton.getId()) {
            returnResult();
        }
    }

    private void returnResult() {
        Bundle bundle = new Bundle();
        bundle.putBoolean("isPickup", isFromPickup);
        if (binding.leaveNowRadioButton.isChecked()) {
            bundle.putString("dateTime", Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, Calendar.getInstance(MyUtils.getLocale()).getTime()));
        } else if (binding.reserveTripRadioButton.isChecked()) {
            if (Utils.checkText(Utils.getText(binding.dateValueTextView)) && Utils.checkText(Utils.getText(binding.timeValueTextView))) {
                bundle.putString("dateTime", Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, dateTimeCalender.getTime()));
//                if (isFromPickup) {
//                    bundle.putBoolean("matchDriverChecked", binding.matchDriverCheckbox.isChecked());
//                }
            } else {
                generalFunc.showMessage(binding.timeSelectParentBox, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                return;
            }
        }
        bundle.putBoolean("isReserveRadioChecked", isReserveRadioChecked);
        new ActUtils(getActContext()).setOkResult(bundle);
        finish();
    }

    private Context getActContext() {
        return IntercityDateTimeSelectorActivity.this;
    }

    private void openTimePicker() {
        TimePickerDialog mTimePicker = TimePickerDialog.newInstance((timePickerDialog, hour, minutes, seconds) -> {
            setCalender(hour, minutes);

            if (Utils.checkText(Utils.getText(binding.dateValueTextView))) {
                if (dateTimeCalender.getTimeInMillis() < minCalender.getTimeInMillis()) {
                    setCalender(minCalender.get(Calendar.HOUR_OF_DAY), minCalender.get(Calendar.MINUTE));
                    generalFunc.showMessage(binding.timeValueTextView, generalFunc.retrieveLangLBl("", "LBL_INTERCITY_MIN_SELECTED_TIME_MSG"));
                    binding.timeValueTextView.setText("");
                } else if (dateTimeCalender.getTimeInMillis() > maxCalender.getTimeInMillis()) {
                    setCalender(maxCalender.get(Calendar.HOUR_OF_DAY), minCalender.get(Calendar.MINUTE));
                    binding.timeValueTextView.setText("");
                    generalFunc.showMessage(binding.timeValueTextView, generalFunc.retrieveLangLBl("", "LBL_INTERCITY_MAX_SELECTED_TIME_MSG"));
                } else {
                    binding.timeValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.TimeFormat, dateTimeCalender.getTime()));
                }
            } else {
                binding.timeValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.TimeFormat, dateTimeCalender.getTime()));
            }
        }, dateTimeCalender.get(Calendar.HOUR_OF_DAY), dateTimeCalender.get(Calendar.MINUTE), DateTimeUtils.Is24HourTime);
        mTimePicker.show(getSupportFragmentManager(), "TimePickerDialog");
    }

    private void setCalender(int hour, int min) {
        dateTimeCalender.set(Calendar.HOUR_OF_DAY, hour);
        dateTimeCalender.set(Calendar.MINUTE, min);
        dateTimeCalender.set(Calendar.SECOND, 0);
        dateTimeCalender.set(Calendar.MILLISECOND, 0);
    }

    private void openDatePicker() {
        String selectedDate = "";
        if (Utils.checkText(lastSelectedDateTime)) {
            selectedDate = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, lastSelectedDateTime));
        } else {
            selectedDate = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, dateTimeCalender.getTime());
        }

        DatePicker.show(this, generalFunc, minCalender, maxCalender, selectedDate, null, (year, monthOfYear, dayOfMonth) -> {

            dateTimeCalender.set(Calendar.YEAR, year);
            dateTimeCalender.set(Calendar.MONTH, monthOfYear - 1);
            dateTimeCalender.set(Calendar.DAY_OF_MONTH, dayOfMonth);

            if (Utils.checkText(Utils.getText(binding.timeValueTextView))) {
                if (dateTimeCalender.getTimeInMillis() < minCalender.getTimeInMillis()) {
                    setCalender(minCalender.get(Calendar.HOUR_OF_DAY), minCalender.get(Calendar.MINUTE));
                    generalFunc.showMessage(binding.timeValueTextView, generalFunc.retrieveLangLBl("LBL_INTERCITY_MIN_SELECTED_TIME_MSG", "LBL_INTERCITY_MIN_SELECTED_TIME_MSG"));
                    binding.timeValueTextView.setText("");
                } else if (dateTimeCalender.getTimeInMillis() > maxCalender.getTimeInMillis()) {
                    setCalender(maxCalender.get(Calendar.HOUR_OF_DAY), minCalender.get(Calendar.MINUTE));
                    binding.timeValueTextView.setText("");
                    generalFunc.showMessage(binding.timeValueTextView, generalFunc.retrieveLangLBl("LBL_INTERCITY_MAX_SELECTED_TIME_MSG", "LBL_INTERCITY_MAX_SELECTED_TIME_MSG"));
                }
            } else {
                openTimePicker();
            }


            binding.dateValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, dateTimeCalender.getTime()));

        });
    }

    private void makeTripSelection(View view) {
        if (view.getId() == binding.leaveNowParentLayout.getId()) {
            binding.leaveNowRadioButton.setChecked(true);
            binding.reserveTripRadioButton.setChecked(false);
            binding.reserveTripExtendLayout.setVisibility(View.GONE);
            isReserveRadioChecked = false;
        } else if (view.getId() == binding.reserveTripParentLayout.getId()) {
            binding.reserveTripRadioButton.setChecked(true);
            binding.leaveNowRadioButton.setChecked(false);
            binding.reserveTripExtendLayout.setVisibility(View.VISIBLE);
            isReserveRadioChecked = true;
        }
    }
}