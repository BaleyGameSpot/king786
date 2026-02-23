package com.act.rideSharingPro.fragmentHome;

import static android.app.Activity.RESULT_OK;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.SearchPickupLocationActivity;
import com.act.rideSharingPro.RideBookSearchList;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.adapter.RecentPostRideAdapter;
import com.dialogs.OpenListView;
import com.fragments.BaseFragment;
import com.general.DatePicker;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.general.files.OpenNoLocationView;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRideSharingSearchBinding;
import com.buddyverse.main.databinding.SeatSelectionDialog23Binding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.DateTimeUtils;
import com.utils.LayoutDirection;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;

public class RideSharingSearchFragment extends BaseFragment implements GetLocationUpdates.LocationUpdates {

    private FragmentRideSharingSearchBinding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private GetLocationUpdates getLastLocation;
    private final Calendar dateTimeCalender = Calendar.getInstance(MyUtils.getLocale());
    private final Calendar maxDateCalender = Calendar.getInstance(MyUtils.getLocale());
    private final ArrayList<String> passengersNoList = new ArrayList<>();
    private ArrayList<String> passengersNoListRecent = new ArrayList<>();
    private MButton searchBtn, itemNextButton;
    private boolean isStartLocationClick = false;
    private String mSLatitude, mSLongitude, mELatitude, mELongitude;
    private int selCurrentPosition = -1, recentSeatSel = -1;
    private RecentPostRideAdapter mAdapter;
    private JSONArray recentDataArr = new JSONArray();
    private BottomSheetDialog seatSelectionDialog;
    private int MAX_ALLOWED_DAYS_FOR_CARPOOL = 60;

    String SELECTED_DATE;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof RideSharingProHomeActivity activity) {
            mActivity = activity;
            generalFunc = activity.generalFunc;
        }
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_sharing_search, container, false);
        if (generalFunc == null) {
            generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
        }

        if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
            generalFunc.isLocationPermissionGranted(true);
        }
        if (mActivity != null && Utils.checkText(mActivity.generalFunc.getJsonValueStr("MAX_ALLOWED_DAYS_FOR_CARPOOL", mActivity.obj_userProfile))) {
            MAX_ALLOWED_DAYS_FOR_CARPOOL = Integer.parseInt(mActivity.generalFunc.getJsonValueStr("MAX_ALLOWED_DAYS_FOR_CARPOOL", mActivity.obj_userProfile));
        }
        maxDateCalender.set(Calendar.DATE, dateTimeCalender.get(Calendar.DATE) + MAX_ALLOWED_DAYS_FOR_CARPOOL);

        initialization();
        setData();
        return binding.getRoot();
    }

    @SuppressLint("SetTextI18n")
    private void initialization() {
        if (!ServiceModule.OnlyRideSharingPro) {
            binding.backBtn.setVisibility(View.VISIBLE);
            addToClickHandler(binding.backBtn);
            if (generalFunc.isRTLmode()) {
                binding.backBtn.setRotation(0);
                binding.imgBanner.setRotationY(180);
            }
        } else {
            binding.backBtn.setVisibility(View.GONE);
        }

        binding.imageHeaderTxt.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_RIDE_HEADER_TXT"));

        //
        binding.startLocationBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_START_LOC_TXT"));
        binding.endLocationBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_END_LOC_TXT"));

        addToClickHandler(binding.startLocationBox);
        addToClickHandler(binding.endLocationBox);
        addToClickHandler(binding.locationFlipArea);

        binding.dateTimeHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_SELECT_DATE"));
        binding.dateTimeEditBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_SELECT_DATE"));
        binding.dateTimeEditBox.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, dateTimeCalender.getTime()));

        SELECTED_DATE = Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, dateTimeCalender.getTime());

        addToClickHandler(binding.dateTimeEditBox);

        binding.personHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PERSON"));
        binding.personSeatTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_AND_SELECT"));
        addToClickHandler(binding.personSeatTxt);

        searchBtn = ((MaterialRippleLayout) binding.searchBtn).getChildView();
        searchBtn.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_SEARCH"));
        searchBtn.setId(Utils.generateViewId());
        addToClickHandler(searchBtn);

        // Recently Posted
        binding.recentlyPostHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECENTLY_POSTED_RIDES_RIDE_SHARE_TXT"));
        mAdapter = new RecentPostRideAdapter(generalFunc, recentDataArr, this::OpenBottomSeatSelectionDialog);
        binding.rvRecentlyRidePostList.setAdapter(mAdapter);
    }

    private void OpenBottomSeatSelectionDialog(JSONObject itemObj) {
        passengersNoListRecent.clear();
        if (seatSelectionDialog != null && seatSelectionDialog.isShowing()) {
            return;
        }
        seatSelectionDialog = new BottomSheetDialog(mActivity);
        SeatSelectionDialog23Binding binding = SeatSelectionDialog23Binding.inflate(LayoutInflater.from(getContext()));
        View contentView = binding.getRoot();

        seatSelectionDialog.setContentView(binding.getRoot());
        seatSelectionDialog.setCancelable(false);
        BottomSheetBehavior<View> mBehavior = BottomSheetBehavior.from((View) contentView.getParent());

        binding.itemNoteText.setText(generalFunc.getJsonValueStr("PENDING_SEATS_TXT", itemObj));
        itemNextButton = ((MaterialRippleLayout) binding.itemNextBtn).getChildView();
        itemNextButton.setText(generalFunc.retrieveLangLBl("", "LBL_NEXT"));
        itemNextButton.setId(Utils.generateViewId());


        passengersNoListRecent = getSeatList(itemObj);
        if (passengersNoListRecent.size() == 1) {
            binding.itemPersonSeatTxt.setText(passengersNoListRecent.get(0));
            binding.itemPersonSeatTxt.setCompoundDrawablesWithIntrinsicBounds(0, 0, 0, 0);
        }
        binding.itemPersonSeatTxt.setOnClickListener(view -> {
            if (passengersNoListRecent.size() == 1) {
                return;
            }
            OpenListView.getInstance(mActivity, generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PASSENGER_AVAILABLE_SEAT"), passengersNoListRecent, OpenListView.OpenDirection.CENTER, true, true, position -> {
                recentSeatSel = position;
                binding.itemPersonSeatTxt.setText(passengersNoList.get(position));
                binding.seatListBoxLayout.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.card_view_23_white_shadow));
            }).show(recentSeatSel, "vTitle");

        });
        itemNextButton.setOnClickListener(view -> {
            if (!Utils.checkText(Utils.getText(binding.itemPersonSeatTxt))) {
                binding.seatListBoxLayout.setBackground(ContextCompat.getDrawable(mActivity, R.drawable.error_border_1dp));
                return;
            }
            binding.imageCancel.performClick();
            searchForRide(itemObj, Utils.getText(binding.itemPersonSeatTxt));
        });
        binding.imageCancel.setOnClickListener(view -> {
            recentSeatSel = -1;
            seatSelectionDialog.dismiss();
        });
        binding.itemHeader.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_TO_PROCEED_ADD_NUMBER_OF_SEATS"));
        binding.itemTitle.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_AND_SELECT"));
        binding.itemPersonSeatTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_AND_SELECT"));

        View bottomSheetView = seatSelectionDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(getResources().getColor(android.R.color.transparent));

        mBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);
        mBehavior.setHideable(false);
        LayoutDirection.setLayoutDirection(seatSelectionDialog);
        seatSelectionDialog.show();
    }

    private ArrayList<String> getSeatList(JSONObject itemObj) {
        JSONArray arr_msg = generalFunc.getJsonArray(generalFunc.getJsonValueStr("SEARCH_SEATS", itemObj));
        if (arr_msg != null) {
            for (int j = 0; j < arr_msg.length(); j++) {
                passengersNoListRecent.add("" + generalFunc.getJsonValue(arr_msg, j));
            }
        }
        return passengersNoListRecent;
    }

    private void searchForRide(JSONObject itemObj, String SelectedSeats) {
        Bundle bn = new Bundle();
        bn.putString("tStartLat", generalFunc.getJsonValueStr("tStartLat", itemObj));
        bn.putString("tStartLong", generalFunc.getJsonValueStr("tStartLong", itemObj));
        bn.putString("tEndLat", generalFunc.getJsonValueStr("tEndLat", itemObj));
        bn.putString("tEndLong", generalFunc.getJsonValueStr("tEndLong", itemObj));

        bn.putString("dStartDate", generalFunc.getJsonValueStr("dateFormat", itemObj));
        bn.putString("NoOfSeats", SelectedSeats);

        new ActUtils(mActivity).startActForResult(RideBookSearchList.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
    }

    private void setData() {
        assert mActivity != null;
        passengersNoList.clear();
        JSONArray arr_msg = generalFunc.getJsonArray(generalFunc.getJsonValueStr("RIDE_SHARE_PASSENGER_NOS", mActivity.obj_userProfile));
        if (arr_msg != null) {
            for (int j = 0; j < arr_msg.length(); j++) {
                Object value = generalFunc.getJsonValue(arr_msg, j);
                if (j == 0) {
                    binding.personSeatTxt.setText(value.toString());
                }
                if (value.toString().equalsIgnoreCase(binding.personSeatTxt.getText().toString())) {
                    selCurrentPosition = j;
                }
                passengersNoList.add("" + generalFunc.getJsonValue(arr_msg, j));
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        initializeLocationCheckDone();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        stopLocationUpdates();
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull @NotNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        OpenNoLocationView.getInstance(mActivity, binding.parentArea).configView(false);
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        // super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == RESULT_OK && data != null && data.getBooleanExtra("isShowRideBooking", false)) {
            assert mActivity != null;
            mActivity.setFrag(1);
        } else if (requestCode == Utils.ADD_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {
            if (isStartLocationClick) {
                binding.startLocationBox.setText(data.getStringExtra("Address"));
                mSLatitude = data.getStringExtra("Latitude");
                mSLongitude = data.getStringExtra("Longitude");
            } else {
                binding.endLocationBox.setText(data.getStringExtra("Address"));
                mELatitude = data.getStringExtra("Latitude");
                mELongitude = data.getStringExtra("Longitude");
            }
        } else {
            OpenNoLocationView.getInstance(mActivity, binding.parentArea).configView(false);
        }
    }


    public void initializeLocationCheckDone() {
        if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
            //initializeLocation();
            stopLocationUpdates();
            GetLocationUpdates.locationResolutionAsked = false;
            getLastLocation = new GetLocationUpdates(mActivity, Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
        } else if (generalFunc.isLocationPermissionGranted(false) && !generalFunc.isLocationEnabled()) {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                OpenNoLocationView.getInstance(mActivity, binding.parentArea).configView(false);
            }
        } else {
            OpenNoLocationView.getInstance(mActivity, binding.parentArea).configView(false);
        }
    }

    private void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        stopLocationUpdates();
        getNearestRide(mLastLocation);
    }

    private void getNearestRide(Location mLastLocation) {
        if (recentDataArr.length() == 0) {
            binding.loading.setVisibility(View.VISIBLE);
        }
        binding.noDataTxt.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetNearestRide");
        parameters.put("tStartLat", "" + mLastLocation.getLatitude());
        parameters.put("tStartLong", "" + mLastLocation.getLongitude());

        ApiHandler.execute(mActivity, parameters, responseString -> {
            binding.loading.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    recentDataArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    mAdapter.updateData(recentDataArr);
                } else {
                    mAdapter.updateData(null);
                    binding.noDataTxt.setVisibility(View.VISIBLE);
                    binding.noDataTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            }
        });
    }

    @SuppressLint("SetTextI18n")
    public void onClickView(View view) {
        assert mActivity != null;
        int i = view.getId();
        if (i == binding.backBtn.getId()) {
            mActivity.onBackPressed();

        } else if (i == binding.startLocationBox.getId() || i == binding.endLocationBox.getId()) {
            Bundle bndl = new Bundle();
            isStartLocationClick = i == binding.startLocationBox.getId();
            bndl.putString("IS_FROM_SELECT_LOC", "Yes");
            if (i == binding.startLocationBox.getId()) {
                if (Utils.checkText(Utils.getText(binding.startLocationBox))) {
                    bndl.putString("isPickUpLoc", "true");
                    if (Utils.checkText(mSLatitude) && Utils.checkText(mSLongitude)) {
                        bndl.putString("PickUpAddress", Utils.getText(binding.startLocationBox));
                        bndl.putString("PickUpLatitude", mSLatitude);
                        bndl.putString("PickUpLongitude", mSLongitude);
                    }
                }
            }
            if (i == binding.endLocationBox.getId()) {
                bndl.putString("locationArea", "dest");
                bndl.putBoolean("isFromGoingTo", true);
                if (Utils.checkText(Utils.getText(binding.endLocationBox))) {
                    bndl.putString("isDestLoc", "true");
                    bndl.putBoolean("isDataFilled", true);
                    if (Utils.checkText(mELatitude) && Utils.checkText(mELongitude)) {
                        bndl.putString("DestAddress", Utils.getText(binding.endLocationBox));
                        bndl.putString("DestLatitude", mELatitude);
                        bndl.putString("DestLongitude", mELongitude);
                    }
                }
            }
            new ActUtils(mActivity).startActForResult(SearchPickupLocationActivity.class, bndl, Utils.ADD_LOC_REQ_CODE);

        } else if (i == binding.locationFlipArea.getId()) {
            assert mActivity != null;

            if (!Utils.checkText(Utils.getText(binding.startLocationBox)) || !Utils.checkText(Utils.getText(binding.endLocationBox))) {
                return;
            }

            String fromAddress = Utils.getText(binding.startLocationBox);
            String fromLatitude = mSLatitude;
            String fromLongitude = mSLongitude;

            String toAddress = Utils.getText(binding.endLocationBox);
            String toLatitude = mELatitude;
            String toLongitude = mELongitude;

            binding.startLocationBox.setText(toAddress == null ? "" : toAddress);
            mSLatitude = toLatitude;
            mSLongitude = toLongitude;

            binding.endLocationBox.setText(fromAddress == null ? "" : fromAddress);
            mELatitude = fromLatitude;
            mELongitude = fromLongitude;

        } else if (i == binding.dateTimeEditBox.getId()) {

            DatePicker.show(mActivity, generalFunc, Calendar.getInstance(), maxDateCalender,
                    Utils.convertDateToFormat(DateTimeUtils.DayFormatEN, dateTimeCalender.getTime()), null, (year, monthOfYear, dayOfMonth) -> {

                        dateTimeCalender.set(Calendar.YEAR, year);
                        dateTimeCalender.set(Calendar.MONTH, monthOfYear - 1);
                        dateTimeCalender.set(Calendar.DAY_OF_MONTH, dayOfMonth);

                        SELECTED_DATE = Utils.convertDateToFormat(DateTimeUtils.serverDateTimeFormat, dateTimeCalender.getTime());
                        binding.dateTimeEditBox.setText(Utils.convertDateToFormat(DateTimeUtils.DateFormat, dateTimeCalender.getTime()));

                    });
        } else if (i == binding.personSeatTxt.getId()) {

            OpenListView.getInstance(mActivity, generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PASSENGER_AVAILABLE_SEAT"), passengersNoList, OpenListView.OpenDirection.CENTER, true, true, position -> {
                selCurrentPosition = position;
                binding.personSeatTxt.setText(passengersNoList.get(position));
            }).show(selCurrentPosition, "vTitle");

        } else if (i == searchBtn.getId()) {
            if (Utils.checkText(mSLatitude) && Utils.checkText(mSLongitude)
                    && Utils.checkText(mELatitude) && Utils.checkText(mELongitude)) {

                Bundle bn = new Bundle();
                bn.putString("tStartLat", mSLatitude);
                bn.putString("tStartLong", mSLongitude);
                bn.putString("tEndLat", mELatitude);
                bn.putString("tEndLong", mELongitude);

                bn.putString("dStartDate", SELECTED_DATE);
                bn.putString("NoOfSeats", Utils.getText(binding.personSeatTxt));

                new ActUtils(mActivity).startActForResult(RideBookSearchList.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
            } else {
                generalFunc.showMessage(binding.backBtn, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}