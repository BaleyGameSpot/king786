package com.act.intercity;

import android.app.Dialog;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.Window;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;
import androidx.viewpager.widget.ViewPager;

import com.act.MainActivity;
import com.act.SearchLocationActivity;
import com.act.intercity.Models.TripConfigModel;
import com.act.intercity.fragment.OneWayTripFragment;
import com.act.intercity.fragment.RoundTripFragment;
import com.activity.ParentActivity;
import com.adapter.files.ViewPagerAdapter;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityIntercityHomeBinding;
import com.buddyverse.main.databinding.DialogIntercityDistanceBinding;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import java.util.ArrayList;

public class IntercityHomeActivity extends ParentActivity {

    ActivityIntercityHomeBinding binding;
    DialogIntercityDistanceBinding dialogBinding;
    private final ArrayList<Fragment> fragmentList = new ArrayList<>();
    private final ArrayList<String> titleList = new ArrayList<>();
    private CharSequence[] titles;
    private OneWayTripFragment oneWayTripFragment;
    private RoundTripFragment roundTripFragment;
    private ViewPagerAdapter adapter;
    private MButton findTripButton;
    private ActivityResultLauncher<Intent> searchActResultLauncher, dateTimePickResultLauncher;
    private String clickArea = "", INTERCITY_RADIUS = "";
    private TripConfigModel model;
    private boolean isReserveTrip = false;
    private Dialog locationErrorDialog;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_intercity_home);
        initializeUi();
    }

    private void initializeUi() {
        binding.toolbarInclude.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PLAN_YOUR_TRIP_TXT"));
        findTripButton = ((MaterialRippleLayout) binding.findTripsButton).getChildView();
        findTripButton.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_FIND_TRIPS"));
        findTripButton.setId(Utils.generateViewId());

        INTERCITY_RADIUS = generalFunc.getJsonValueStr("INTERCITY_RADIUS", obj_userProfile);

        titleList.add(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ONE_WAY"));
        titleList.add(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_ROUND_TRIP"));
        fragmentList.add(generateOneWayFragment());
        fragmentList.add(generateRoundTripFragment());

        titles = titleList.toArray(new CharSequence[2]);
        adapter = new ViewPagerAdapter(getSupportFragmentManager(), titles, fragmentList);
        binding.tripCategoryViewPager.setOffscreenPageLimit(2);
        binding.tripCategoryViewPager.setAdapter(adapter);
        binding.tabArea.materialTabs.setupWithViewPager(binding.tripCategoryViewPager);

        binding.tripCategoryViewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
            }

            @Override
            public void onPageSelected(int position) {
            }

            @Override
            public void onPageScrollStateChanged(int state) {
            }
        });
        clickHandle();
        if (getIntent() != null && getIntent().getExtras() != null) {
            updateModelData(getIntent());
        }

        resultLaunchers();

        if (generalFunc.isRTLmode()) {
            binding.toolbarInclude.backImgView.setRotation(180);
        }
    }

    private void resultLaunchers() {
        searchActResultLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (result != null && result.getData() != null) {
                if (clickArea.equalsIgnoreCase("source")) {
                    model.setSLatitude(result.getData().getStringExtra("Latitude"));
                    model.setSLongitude(result.getData().getStringExtra("Longitude"));
                    model.setSAddress(result.getData().getStringExtra("Address"));
                } else if (clickArea.equalsIgnoreCase("destination")) {
                    model.setELatitude(result.getData().getStringExtra("Latitude"));
                    model.setELongitude(result.getData().getStringExtra("Longitude"));
                    model.setEAddress(result.getData().getStringExtra("Address"));
                }

                if (Utils.checkText(model.getSAddress()) && Utils.checkText(model.getEAddress())) {
                    isLocationWithinCityAra();
                }
            }
        });
        dateTimePickResultLauncher = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
            if (result != null && result.getData() != null) {
                if (result.getData().hasExtra("dateTime")) {
                    if (result.getData().hasExtra("isPickup")) {
                        if (result.getData().getBooleanExtra("isPickup", false)) {
                            model.setPickupDateTime(result.getData().getStringExtra("dateTime"));
                            model.setDropOffDateTime("");
                            isReserveTrip = result.getData().getBooleanExtra("isReserveRadioChecked", false);
                        } else {
                            model.setDropOffDateTime(result.getData().getStringExtra("dateTime"));
                        }
                    }
                }
            }
        });
    }

    public void moveToInterCityDateTimeActivity(boolean isPickup) {
        Intent intent = new Intent(this, IntercityDateTimeSelectorActivity.class);
        intent.putExtra("isPickup", isPickup);
        intent.putExtra("pickUpAddress", model.getSAddress());
        intent.putExtra("isReserveTrip", isReserveTrip);
        if (!isPickup) {
            intent.putExtra("minCalenderDate", model.getPickupDateTime());
            if (Utils.checkText(model.getDropOffDateTime())) {
                intent.putExtra("lastSelectedDateTime", model.getDropOffDateTime());
            }
        } else {
            if (isReserveTrip) {
                intent.putExtra("lastSelectedDateTime", model.getPickupDateTime());
            }
        }
        dateTimePickResultLauncher.launch(intent);
    }

    public void moveToSearchActivityForResult(String clickedArea) {
        clickArea = clickedArea;
        Intent intent = new Intent(this, SearchLocationActivity.class);
        intent.putExtra("isInterCity", true);

        if (MyApp.getInstance().currentLocation != null) {
            intent.putExtra("lat", MyApp.getInstance().currentLocation.getLatitude());
            intent.putExtra("long", MyApp.getInstance().currentLocation.getLongitude());
        }
        intent.putExtra("address", "");

        if (clickedArea.equalsIgnoreCase("source")) {
            intent.putExtra("locationArea", "source");
            if (Utils.checkText(model.getSLatitude()) && Utils.checkText(model.getSLongitude())) {
                intent.putExtra("lat", GeneralFunctions.parseDoubleValue(0.0, model.getSLatitude()));
                intent.putExtra("long", GeneralFunctions.parseDoubleValue(0.0, model.getSLongitude()));
            }
            intent.putExtra("address", model.getSAddress());
        } else {
            intent.putExtra("locationArea", "dest");
            if (Utils.checkText(model.getELatitude()) && Utils.checkText(model.getELongitude())) {
                intent.putExtra("lat", GeneralFunctions.parseDoubleValue(0.0, model.getELatitude()));
                intent.putExtra("long", GeneralFunctions.parseDoubleValue(0.0, model.getELongitude()));
            }
            intent.putExtra("address", model.getEAddress());
        }
        searchActResultLauncher.launch(intent);
    }

    private void updateModelData(Intent data) {
        if (data != null && data.getExtras() != null) {
            model = new TripConfigModel(data.getStringExtra("latitude"), data.getStringExtra("longitude"), "", "", data.getStringExtra("address"), "", "", "");
        }
    }

    public TripConfigModel getModel() {
        return model;
    }

    private void clickHandle() {
        addToClickHandler(binding.toolbarInclude.backImgView);
        addToClickHandler(findTripButton);
    }

    private Fragment generateOneWayFragment() {
        oneWayTripFragment = new OneWayTripFragment();
        return oneWayTripFragment;
    }

    private Fragment generateRoundTripFragment() {
        roundTripFragment = new RoundTripFragment();
        return roundTripFragment;
    }

    private Fragment getOneWayFragment() {
        return oneWayTripFragment;
    }

    private Fragment getRoundTripFragment() {
        return roundTripFragment;
    }

    @Override
    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.toolbarInclude.backImgView.getId()) {
            finish();
        } else if (i == findTripButton.getId()) {
            validateFieldsInFragment();
        }
    }

    private void validateFieldsInFragment() {
        if (fragmentList.get(binding.tripCategoryViewPager.getCurrentItem()) instanceof OneWayTripFragment fragment) {
            fragment.validateView();
        } else if (fragmentList.get(binding.tripCategoryViewPager.getCurrentItem()) instanceof RoundTripFragment fragment) {
            fragment.validateView();
        }
    }

    public void moveToMainAct(boolean isRoundTrip, boolean isFromChooseOtherTrip) {
        Bundle bn = new Bundle();
        bn.putString("selType", Utils.CabGeneralType_Ride);

        //Source Location Data
        bn.putString("address", model.getSAddress());
        bn.putString("latitude", model.getSLatitude());
        bn.putString("lat", model.getSLatitude());
        bn.putString("longitude", model.getSLongitude());
        bn.putString("long", model.getSLongitude());

        //only for disable marker click in mainActivity
        bn.putBoolean("isFromChooseTrip", isFromChooseOtherTrip);

        if (isFromChooseOtherTrip) {
            if (isReserveTrip) {
                bn.putBoolean("isRestart", false);
                bn.putBoolean("isWhereTo", false);
                bn.putBoolean("isShowSchedule", true);
                bn.putBoolean("isAddStop", false);
            } else {
                //Destination Location Data
                bn.putString("vPlacesLocation", model.getEAddress());
                bn.putString("vPlacesLocationLat", model.getELatitude());
                bn.putString("vPlacesLocationLong", model.getELongitude());
                bn.putBoolean("isPlacesLocation", true);
                bn.putBoolean("isRoundTrip", false);
                bn.putBoolean("isInterCity", false);
            }
        } else {
            //Schedule
            bn.putString("isInterCitySchedule", isReserveTrip ? "Yes" : "No");

            //Destination Location Data
            bn.putString("vPlacesLocation", model.getEAddress());
            bn.putString("vPlacesLocationLat", model.getELatitude());
            bn.putString("vPlacesLocationLong", model.getELongitude());
            bn.putBoolean("isPlacesLocation", true);

            //Pickup DropOff time Data
            bn.putString("pickupDateTime", model.getPickupDateTime());
            bn.putString("dropOffDateTime", model.getDropOffDateTime());

            //other validation data
            bn.putBoolean("isRoundTrip", isRoundTrip);
            bn.putBoolean("isInterCity", true);
        }
        new ActUtils(this).startActWithData(MainActivity.class, bn);
    }

    public boolean isLocationWithinCityAra() {

        Location loc1 = new Location("");
        loc1.setLatitude(GeneralFunctions.parseDoubleValue(0.0, model.getSLatitude()));
        loc1.setLongitude(GeneralFunctions.parseDoubleValue(0.0, model.getSLongitude()));

        Location loc2 = new Location("");
        loc2.setLatitude(GeneralFunctions.parseDoubleValue(0.0, model.getELatitude()));
        loc2.setLongitude(GeneralFunctions.parseDoubleValue(0.0, model.getELongitude()));

        long distanceInMeters = (long) loc1.distanceTo(loc2);  // direct distance

        if (Utils.checkText(INTERCITY_RADIUS)) {

            if (distanceInMeters < GeneralFunctions.parseLongValue(0, INTERCITY_RADIUS)) {
                openLocationErrorDialog();
                return false;
            }
        }
        return true;
    }

    private void openLocationErrorDialog() {
        Utils.hideKeyboard(this);
        if (locationErrorDialog != null && !locationErrorDialog.isShowing()) {
            locationErrorDialog.show();
            return;
        }

        locationErrorDialog = new Dialog(this, R.style.NoActionBar);
        locationErrorDialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        locationErrorDialog.getWindow().setBackgroundDrawable(new ColorDrawable(android.graphics.Color.TRANSPARENT));
        locationErrorDialog.getWindow().setStatusBarColor(ContextCompat.getColor(this, R.color.appThemeColor_1));

        dialogBinding = DialogIntercityDistanceBinding.inflate(LayoutInflater.from(this));

        locationErrorDialog.setContentView(dialogBinding.getRoot());
        locationErrorDialog.setCancelable(false);
        locationErrorDialog.setCanceledOnTouchOutside(false);

        dialogBinding.locationErrorTitleTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_LOCATION_ERROR"));

        dialogBinding.noServiceWithingCityTextView.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_OUTSERVICE_MSG"));

        MButton chooseOtherTripBtn = ((MaterialRippleLayout) dialogBinding.chooseOtherTripBtn).getChildView();
        MButton editLocationBtn = ((MaterialRippleLayout) dialogBinding.editLocationBtn).getChildView();
        chooseOtherTripBtn.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_CHOOSE_TRIP_OPTIONS"));
        editLocationBtn.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_EDIT_LOCATION"));

        dialogBinding.closeDialogImageView.setOnClickListener(view -> locationErrorDialog.dismiss());

        chooseOtherTripBtn.setOnClickListener(view -> {
            locationErrorDialog.dismiss();
            moveToMainAct(false, true);
        });

        editLocationBtn.setOnClickListener(view -> {
            locationErrorDialog.dismiss();
            moveToSearchActivityForResult("destination");
        });

        LayoutDirection.setLayoutDirection(locationErrorDialog);
        locationErrorDialog.show();
    }
}