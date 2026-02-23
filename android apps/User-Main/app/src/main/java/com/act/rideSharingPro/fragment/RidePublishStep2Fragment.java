package com.act.rideSharingPro.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.text.InputFilter;
import android.text.InputType;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.adapter.EditPriceSerSeatAdapter;
import com.datepicker.time.TimePickerDialog;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.DatePicker;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishStep2Binding;
import com.utils.DateTimeUtils;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Calendar;

public class RidePublishStep2Fragment extends BaseFragment {

    private FragmentRidePublishStep2Binding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private final Calendar dateTimeCalender = Calendar.getInstance(MyUtils.getLocale());
    private final Calendar minDateCalender = Calendar.getInstance(MyUtils.getLocale());
    private final Calendar maxDateCalender = Calendar.getInstance(MyUtils.getLocale());
    private int selCurrentPosition = -1;

    private EditPriceSerSeatAdapter mAdapter;
    private JSONArray pointRecommendedPriceArr = new JSONArray();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish_step_2, container, false);

        assert mActivity != null;
        int MAX_ALLOWED_DAYS_FOR_CARPOOL = GeneralFunctions.parseIntegerValue(60, mActivity.generalFunc.getJsonValueStr("MAX_ALLOWED_DAYS_FOR_CARPOOL", mActivity.obj_userProfile));
        int RIDE_SHARE_ADVANCED_BOOKING_HOURS = (GeneralFunctions.parseIntegerValue(1, mActivity.generalFunc.getJsonValueStr("RIDE_SHARE_ADVANCED_BOOKING_HOURS", mActivity.obj_userProfile)));

        maxDateCalender.set(Calendar.DATE, dateTimeCalender.get(Calendar.DATE) + MAX_ALLOWED_DAYS_FOR_CARPOOL);
        minDateCalender.add(Calendar.HOUR, RIDE_SHARE_ADVANCED_BOOKING_HOURS);
        dateTimeCalender.add(Calendar.HOUR, RIDE_SHARE_ADVANCED_BOOKING_HOURS);
        dateTimeCalender.add(Calendar.MINUTE, 1);
        setLabels();

        if (mActivity.generalFunc.isRTLmode()) {
            binding.currencyTxt.setBackgroundDrawable(ContextCompat.getDrawable(mActivity, R.drawable.right_radius_rtl));
        }

        setEditPrice();
        return binding.getRoot();
    }

    @SuppressLint({"ClickableViewAccessibility", "SetTextI18n"})
    private void setLabels() {
        binding.recommendedPriceEditText.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        binding.recommendedPriceEditText.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

        if (mActivity != null) {
            binding.dateTimeHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DATE_TIME_TXT"));
            binding.dateTimeEditBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADD_DATE_TIME_TXT"));
            binding.pricePerSeatHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PASSENGER_AVAILABLE_SEAT"));
            binding.recommendedPriceHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PRICE_PER_SEAT_TXT"));

            binding.pricePerSeatTxt.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_AND_SELECT"));
            binding.recommendedPriceEditText.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ENATER_AMOUNT"));
            binding.currencyTxt.setText(mActivity.generalFunc.getJsonValueStr("vCurrencyPassenger", mActivity.obj_userProfile));

        }

        addToClickHandler(binding.pricePerSeatTxt);
        addToClickHandler(binding.dateTimeEditBox);
    }

    private void setEditPrice() {
        assert mActivity != null;
        binding.editPricePerSeatHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_EDIT_STOP_OVER_POINT_PRICE_RIDE_SHARE_TEXT"));
        mAdapter = new EditPriceSerSeatAdapter(mActivity.generalFunc, mActivity.generalFunc.getJsonValueStr("vCurrencyPassenger", mActivity.obj_userProfile), pointRecommendedPriceArr, mActivity);
        binding.rvEditPriceList.addItemDecoration(new SpacesItemDecoration(1, mActivity.getResources().getDimensionPixelSize(R.dimen._10sdp), false));
        binding.rvEditPriceList.setAdapter(mAdapter);
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            mActivity = (RideSharingProHomeActivity) requireActivity();
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            binding.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PRICE_AVAILABILITY_TITLE"));
            mActivity.rsPublishFragment.binding.headerHTxt.setText(Utils.getText(binding.selectServiceTxt));
            if (mActivity != null) {
                binding.recommendedPriceEditText.setText(mActivity.rsPublishFragment.mPublishData.getRecommendedPrice());
                binding.recommendedHTxt.setText(mActivity.rsPublishFragment.mPublishData.getRecommdedPriceText());
                binding.recommendedPriceVTxt.setText(mActivity.rsPublishFragment.mPublishData.getRecommdedPriceRange());
                if (Utils.checkText(mActivity.rsPublishFragment.mPublishData.getRecommdedPriceRange())) {
                    binding.recommendedArea.setVisibility(View.VISIBLE);
                } else {
                    binding.recommendedArea.setVisibility(View.GONE);
                }

                pointRecommendedPriceArr = mActivity.generalFunc.getJsonArray(mActivity.rsPublishFragment.mPublishData.getPointRecommendedPrice());
                mAdapter.updateData(pointRecommendedPriceArr);
                if (pointRecommendedPriceArr != null && pointRecommendedPriceArr.length() > 0) {
                    binding.editPricePerSeatHTxt.setVisibility(View.VISIBLE);
                } else {
                    binding.editPricePerSeatHTxt.setVisibility(View.GONE);
                }
            }
        }
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.dateTimeEditBox.getId()) {
            if (mActivity != null) {
                DatePicker.show(mActivity, mActivity.generalFunc, Calendar.getInstance(), maxDateCalender,
                        Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, dateTimeCalender.getTime()), null, (year, monthOfYear, dayOfMonth) -> {

                            dateTimeCalender.set(Calendar.YEAR, year);
                            dateTimeCalender.set(Calendar.MONTH, monthOfYear - 1);
                            dateTimeCalender.set(Calendar.DAY_OF_MONTH, dayOfMonth);

                            TimePickerDialog mTimePicker = TimePickerDialog.newInstance((timePickerDialog, hour, minutes, seconds) -> {
                                dateTimeCalender.set(Calendar.HOUR_OF_DAY, hour);
                                dateTimeCalender.set(Calendar.MINUTE, minutes);
                                dateTimeCalender.set(Calendar.SECOND, 0);
                                dateTimeCalender.set(Calendar.MILLISECOND, 0);

                                if (minDateCalender.getTimeInMillis() <= dateTimeCalender.getTimeInMillis()) {
                                    binding.dateTimeEditBox.setText(Utils.convertDateToFormat(DateTimeUtils.DateTimeFormat, dateTimeCalender.getTime()));
                                } else {
                                    mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_INVALID_PUBLISH_TIME_MSG"));
                                }
                            }, dateTimeCalender.get(Calendar.HOUR_OF_DAY), dateTimeCalender.get(Calendar.MINUTE), DateTimeUtils.Is24HourTime);
                            mTimePicker.show(mActivity.getSupportFragmentManager(), "TimePickerDialog");
                        });
            }
        } else if (i == binding.pricePerSeatTxt.getId()) {
            assert mActivity != null;

            ArrayList<String> arrayList = new ArrayList<>();
            JSONArray arr_msg = mActivity.generalFunc.getJsonArray(mActivity.rsPublishFragment.mPublishData.getPassengerNo());
            if (arr_msg != null) {
                for (int j = 0; j < arr_msg.length(); j++) {
                    Object value = mActivity.generalFunc.getJsonValue(arr_msg, j);
                    if (value.toString().equalsIgnoreCase(binding.pricePerSeatTxt.getText().toString())) {
                        selCurrentPosition = j;
                    }
                    arrayList.add("" + mActivity.generalFunc.getJsonValue(arr_msg, j));
                }
            }

            OpenListView.getInstance(mActivity, mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PASSENGER_AVAILABLE_SEAT"), arrayList, OpenListView.OpenDirection.CENTER, true, true, position -> {
                selCurrentPosition = position;
                binding.pricePerSeatTxt.setText(arrayList.get(position));
            }).show(selCurrentPosition, "vTitle");
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            boolean isPerSeat = GeneralFunctions.parseIntegerValue(0, binding.pricePerSeatTxt.getText().toString()) >= 1;
            boolean isRPrice = GeneralFunctions.parseDoubleValue(0.00, binding.recommendedPriceEditText.getText().toString()) >= 1.00;
            String dateTime = Utils.convertDateToFormat(DateTimeUtils.DayFormatEN + " " + DateTimeUtils.TimeFormat, dateTimeCalender.getTime());
            if (Utils.checkText(binding.dateTimeEditBox.getText().toString()) && Utils.checkText(dateTime) && isPerSeat && isRPrice) {
                mActivity.rsPublishFragment.mPublishData.setDateTime(dateTime);
                mActivity.rsPublishFragment.mPublishData.setPerSeat(binding.pricePerSeatTxt.getText().toString());
                mActivity.rsPublishFragment.mPublishData.setRecommendedPrice(binding.recommendedPriceEditText.getText().toString());
                pointRecommendedPriceArr = mActivity.generalFunc.getJsonArray(mActivity.rsPublishFragment.mPublishData.getPointRecommendedPrice());
                mActivity.rsPublishFragment.mPublishData.setPointRecommendedPrice(pointRecommendedPriceArr == null ? "" : pointRecommendedPriceArr.toString());
                if (pointRecommendedPriceArr != null) {
                    for (int i = 0; i < pointRecommendedPriceArr.length(); i++) {
                        try {
                            if (!Utils.checkText(mActivity.generalFunc.getJsonValueStr("recommended_price", (JSONObject) pointRecommendedPriceArr.get(i))) || Double.parseDouble(mActivity.generalFunc.getJsonValueStr("recommended_price", (JSONObject) pointRecommendedPriceArr.get(i))) <= 0) {
                                mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PRICE_NOTE"));
                                return;
                            }
                        } catch (JSONException e) {
                            throw new RuntimeException(e);
                        }
                    }
                }
                mActivity.rsPublishFragment.setPageNext();
            } else {
                boolean isPriceEmpty = Utils.checkText(binding.dateTimeEditBox.getText().toString()) && Utils.checkText(dateTime) && isPerSeat && Utils.checkText(binding.recommendedPriceEditText.getText().toString());
                mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", isPriceEmpty ? "LBL_RIDE_SHARE_PRICE_NOTE" : "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}