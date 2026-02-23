package com.act.intercity.fragment;

import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.databinding.DataBindingUtil;

import com.act.intercity.IntercityHomeActivity;
import com.act.intercity.Models.TripConfigModel;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRoundTripBinding;
import com.utils.DateTimeUtils;
import com.utils.Utils;

public class RoundTripFragment extends BaseFragment {

    private IntercityHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private FragmentRoundTripBinding binding;


    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_round_trip, container, false);
        initializeUi();
        return binding.getRoot();
    }

    private void initializeUi() {
        binding.enterSourceHeaderTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ENTER_SOURCE"));
        binding.enterSourceValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ENTER_SOURCE"));
        binding.enterDestinationHeaderTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ENTER_DESTINATION"));
        binding.enterDestinationValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ENTER_DESTINATION"));
        binding.pickupDateTimeHeaderTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_PICKUP_DATE_TIME"));
        binding.pickupDateTimeValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_SELECT_PICKUP_DATE_TIME"));
        binding.dropOffDateTimeValueTextView.setHint(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_SELECT_DROPOFF_DATE_TIME"));
        binding.dropOffDateTimeHeaderTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_DROPOFF_DATE_TIME"));
        binding.roundTripNoteTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ROUND_TRIP_NOTE"));

        addToClickHandler(binding.enterSourceParentBox);
        addToClickHandler(binding.enterDestinationParentBox);
        addToClickHandler(binding.pickupDateTimeParentBox);
        addToClickHandler(binding.dropOffDateTimeParentBox);

    }

    @Override
    protected void onClickView(View view) {

        int i = view.getId();
        if (i == binding.enterDestinationParentBox.getId()) {
            mActivity.moveToSearchActivityForResult("destination");
        } else if (i == binding.enterSourceParentBox.getId()) {
            mActivity.moveToSearchActivityForResult("source");
        } else if (i == binding.pickupDateTimeParentBox.getId()) {
            mActivity.moveToInterCityDateTimeActivity(true);
        } else if (i == binding.dropOffDateTimeParentBox.getId()) {
            if (Utils.checkText(Utils.getText(binding.pickupDateTimeValueTextView))) {
                mActivity.moveToInterCityDateTimeActivity(false);
            } else {
                generalFunc.showMessage(binding.getRoot(), generalFunc.retrieveLangLBl("", "LBL_INTERCITY_SELECT_DATE_TIME_FIRST"));
            }
        }
    }

    @Override
    public void onAttach(@NonNull Context context) {
        super.onAttach(context);
        if (context instanceof IntercityHomeActivity) {
            mActivity = (IntercityHomeActivity) context;
        } else {
            mActivity = (IntercityHomeActivity) requireActivity();
        }

        if (mActivity.generalFunc != null) {
            generalFunc = mActivity.generalFunc;
        } else {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }
    }

    @Override
    public void onDetach() {
        super.onDetach();
        mActivity = null;
    }

    @Override
    public void onResume() {
        super.onResume();
        setDataToField();
    }

    private void setDataToField() {
        if (mActivity != null && mActivity.getModel() != null) {
            TripConfigModel model = mActivity.getModel();
            binding.enterSourceValueTextView.setText(model.getSAddress());
            binding.enterDestinationValueTextView.setText(model.getEAddress());
            if (Utils.checkText(model.getPickupDateTime())) {
                binding.pickupDateTimeValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.getDetailDateFormatWise(DateTimeUtils.DateFormat, generalFunc), Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, model.getPickupDateTime())));
            }
            if (Utils.checkText(model.getDropOffDateTime())) {
                binding.dropOffDateTimeValueTextView.setText(Utils.convertDateToFormat(DateTimeUtils.getDetailDateFormatWise(DateTimeUtils.DateFormat, generalFunc), Utils.convertStringToDate(DateTimeUtils.serverDateTimeFormat, model.getDropOffDateTime())));
            } else {
                binding.dropOffDateTimeValueTextView.setText("");
            }
            if (Utils.checkText(Utils.getText(binding.enterDestinationValueTextView))) {
                binding.enterDestinationEditIconIV.setVisibility(View.VISIBLE);
            }
        }
    }

    public void validateView() {
        if (mActivity != null) {
            if (mActivity.isLocationWithinCityAra()) {
                if (Utils.checkText(Utils.getText(binding.enterSourceValueTextView)) &&
                        Utils.checkText(Utils.getText(binding.enterDestinationValueTextView)) &&
                        Utils.checkText(Utils.getText(binding.pickupDateTimeValueTextView)) &&
                        Utils.checkText(Utils.getText(binding.dropOffDateTimeValueTextView))) {
                    if (mActivity != null) {
                        mActivity.moveToMainAct(true,false);
                    }
                } else {
                    generalFunc.showMessage(binding.getRoot(), generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                }
            }
        }
    }

}