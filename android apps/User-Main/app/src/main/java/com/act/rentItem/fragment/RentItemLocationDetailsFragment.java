package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.text.InputType;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import com.act.SearchPickupLocationActivity;
import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.model.RentItemData;
import com.fragments.BaseFragment;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

public class RentItemLocationDetailsFragment extends BaseFragment {

    @Nullable
    private RentItemNewPostActivity mActivity;
    private MaterialEditText selectLocationBox, houseNoBox, addressBox;
    private RentItemData.LocationDetails mLocationDetails;
    private CheckBox showAddressCheckbox;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_item_location_details, container, false);

        setLabels(view);
        setData();

        return view;
    }

    @SuppressLint("ClickableViewAccessibility")
    private void setLabels(View view) {
        MTextView selectLocationHTxt = view.findViewById(R.id.selectLocationHTxt);
        selectLocationBox = view.findViewById(R.id.selectLocationBox);
        MTextView houseNoHTxt = view.findViewById(R.id.houseNoHTxt);
        houseNoBox = view.findViewById(R.id.houseNoBox);
        MTextView addressHTxt = view.findViewById(R.id.addressHTxt);
        addressBox = view.findViewById(R.id.addressBox);

        showAddressCheckbox = view.findViewById(R.id.showAddressCheckbox);
        showAddressCheckbox.setChecked(true);

        if (mActivity != null) {
            selectLocationHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_LOCATION_FOR_FRONT"));
            selectLocationBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_LOCATION"));
            houseNoHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_HOUSE_NO"));
            houseNoBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_HOUSE_NUMBER"));
            addressHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ADDRESS"));
            addressBox.setHint(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ENTER_ADDRESS"));

            showAddressCheckbox.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_ALLOW_USER_TO_SEE_ADDRESS"));
        }

        addressBox.setHideUnderline(true);
        addressBox.setSingleLine(false);
        addressBox.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
        addressBox.setGravity(Gravity.TOP);

        // removeInput
        Utils.removeInput(selectLocationBox);
        selectLocationBox.setOnTouchListener((View view1, MotionEvent motionEvent) -> {
            if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view1.hasFocus()) {
                view1.performClick();
            }
            return true;
        });
        addToClickHandler(selectLocationBox);
    }

    private void setData() {
        if (mActivity != null) {
            if (mActivity.mRentItemData != null) {
                RentItemData.LocationDetails locationDetails = mActivity.mRentItemData.getLocationDetails();
                if (locationDetails != null) {
                    selectLocationBox.setText(locationDetails.getvLocation());
                    houseNoBox.setText(locationDetails.getvBuildingNo());
                    addressBox.setText(locationDetails.getvAddress());
                    showAddressCheckbox.setChecked(locationDetails.isShowMyAddress());
                }
            }
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();
            if (mActivity.mRentItemData != null) {
                if (mActivity.mRentItemData.getLocationDetails() == null) {
                    mLocationDetails = new RentItemData.LocationDetails();
                } else {
                    mLocationDetails = mActivity.mRentItemData.getLocationDetails();
                }
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_LOCATION_DETAILS"));
        }
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == R.id.selectLocationBox) {
            Intent intent = new Intent(mActivity, SearchPickupLocationActivity.class);
            Bundle bndl = new Bundle();
            bndl.putString("IS_FROM_SELECT_LOC", "Yes");
            intent.putExtras(bndl);
            launchActivity.launch(intent);
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    assert mActivity != null;

                    mLocationDetails.setvLocation(data.getStringExtra("Address"));
                    mLocationDetails.setvLatitude(data.getStringExtra("Latitude"));
                    mLocationDetails.setvLongitude(data.getStringExtra("Longitude"));

                    mActivity.mRentItemData.setLocationDetails(mLocationDetails);

                    selectLocationBox.setText(data.getStringExtra("Address"));
                }
            });

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(selectLocationBox) && Utils.checkText(addressBox)) {
                mLocationDetails.setvBuildingNo(Utils.getText(houseNoBox));
                mLocationDetails.setvAddress(Utils.getText(addressBox));
                mLocationDetails.setShowMyAddress(showAddressCheckbox.isChecked());
                mActivity.setPageNext();
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}