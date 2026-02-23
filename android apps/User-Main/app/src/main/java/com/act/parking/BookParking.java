package com.act.parking;

import android.app.Activity;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.buddyverse.main.R;
import com.act.SearchLocationActivity;
import com.buddyverse.main.databinding.ActivityBookParkingBinding;
import com.act.parking.adapter.VehicleSizeAdapter;
import com.act.parking.model.BookParkingData;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class BookParking extends ParentActivity implements GeoMapLoader.OnMapReadyCallback, GetAddressFromLocation.AddressFound, VehicleSizeAdapter.OnItemClickList {

    public ActivityBookParkingBinding binding;
    public Toolbar mToolbar;
    public BookParkingData.LocationDetails bookParkingLocationData;
    public BookParkingData bookParkingData;
    private Marker parkingLocationMarker;
    int height;
    private VehicleSizeAdapter vehicleSizeAdapter;
    private ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
    private RecyclerView rvVehicleSize;

    private LatLng currentLatLon;
    private GetAddressFromLocation getAddressFromLocation;
    private GeoMapLoader.GeoMap googlemap;
    private MButton btn_type2;
    private BottomSheetBehavior bottomSheetBehavior;
    private String duration_data = "";
    private String parkingId = "";
    private MTextView selectVehicleSize;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_book_parking);
        (new GeoMapLoader(this, R.id.mapParkingPublishContainer)).bindMap(this);
        bottomsheetconfigure();
        initialization();
        getVehicleSizes();
        setlabels();
        setdata();
    }

    private void bottomsheetconfigure() {
        RelativeLayout bottomSheet = findViewById(R.id.bottom_sheet_behavior_id);
        bottomSheetBehavior = BottomSheetBehavior.from(bottomSheet);
        DisplayMetrics displaymetrics = new DisplayMetrics();
        getWindowManager().getDefaultDisplay().getMetrics(displaymetrics);
        height = displaymetrics.heightPixels;
        height = (int) (height * 0.5);
        manageBottomSheeetDefaultHeight(height);


        bottomSheetBehavior.setBottomSheetCallback(new BottomSheetBehavior.BottomSheetCallback() {
            @Override
            public void onStateChanged(View view, int i) {
                // do something when state changes
                if (i == 1) {
                    selectVehicleSize.setVisibility(View.GONE);
                    binding.llBtnType2.setVisibility(View.GONE);
                }
                switch (i) {
                    case BottomSheetBehavior.STATE_EXPANDED:
                        selectVehicleSize.setVisibility(View.GONE);
                        binding.llBtnType2.setVisibility(View.GONE);
                        break;
                    case BottomSheetBehavior.STATE_SETTLING:
                        break;
                    case BottomSheetBehavior.STATE_COLLAPSED:
                        selectVehicleSize.setVisibility(View.VISIBLE);
                        binding.llBtnType2.setVisibility(View.VISIBLE);
                        break;
                    case BottomSheetBehavior.STATE_HIDDEN:
                        break;
                }

            }

            @Override
            public void onSlide(View view, float v) {
                // do something when slide happens

            }
        });

    }

    private void manageBottomSheeetDefaultHeight(int height) {
        bottomSheetBehavior.setPeekHeight(height);
    }

    private void getVehicleSizes() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetParkingVehicleSize");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    list_item.clear();
                    JSONArray arr_data = generalFunc.getJsonArray(Utils.message_str, responseString);
                    duration_data = responseString;
                    if (arr_data != null) {
                        for (int i = 0; i < arr_data.length(); i++) {
                            JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);
                            HashMap<String, String> mapdata = new HashMap<>();
                            mapdata.put("size", generalFunc.getJsonValueStr("tTitle", obj_tmp));
                            mapdata.put("sizeEg", generalFunc.getJsonValueStr("tSubtitle", obj_tmp));
                            mapdata.put("vImage", generalFunc.getJsonValueStr("vImage", obj_tmp));
                            mapdata.put("iParkingVehicleSizeId", generalFunc.getJsonValueStr("iParkingVehicleSizeId", obj_tmp));
                            list_item.add(mapdata);
                        }
                        parkingId = list_item.get(0).get("iParkingVehicleSizeId");
                        vehicleSizeAdapter.setSelectedVehicleTypeId(list_item.get(0).get("iParkingVehicleSizeId"));
                        vehicleSizeAdapter.notifyDataSetChanged();
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }

            } else {
                generalFunc.showError();
            }

        });
    }

    private void initialization() {
        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        rvVehicleSize = findViewById(R.id.vehicleSizeRecyclerView);

        vehicleSizeAdapter = new VehicleSizeAdapter(this, list_item, generalFunc);
        vehicleSizeAdapter.setOnItemClickList(this);
        rvVehicleSize.setAdapter(vehicleSizeAdapter);
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        bookParkingLocationData = new BookParkingData.LocationDetails();
        bookParkingData = new BookParkingData();
        selectVehicleSize = (MTextView) findViewById(R.id.selectVehicleSizeHTxt);
        selectVehicleSize.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_CHOOSE_VEHICLE_SIZE_TXT"));
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_RENT_YOUR_SPACE_TXT"));

    }

    private void setdata() {
        getAddressFromLocation = new GetAddressFromLocation(this, generalFunc);
        getAddressFromLocation.setAddressList(this);
        btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_NEXT"));
        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(btn_type2);
        addToClickHandler(binding.editLocation);
        addToClickHandler(binding.userLocationBtn);
        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) (binding.llLocationBtn).getLayoutParams();
        params.bottomMargin = height + this.getResources().getDimensionPixelSize(R.dimen._20sdp);
        binding.llLocationBtn.requestLayout();
    }

    private void setlabels() {
        binding.enterParkingLocation.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_PARKING_LOCATION_TXT"));
        binding.btnMap.setText(generalFunc.retrieveLangLBl("", "LBL_MAP_TXT"));
        binding.btnSatellite.setText(generalFunc.retrieveLangLBl("", "LBL_SATELLITE_TXT"));
        binding.parkingAddress.setHint(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
        binding.llMapType.setClipToOutline(true);
        addToClickHandler(binding.btnSatellite);
        addToClickHandler(binding.btnMap);
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == binding.editLocation.getId()) {
            Intent intent = new Intent(this, SearchLocationActivity.class);
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, bookParkingLocationData.getBookingLatitude()));
            bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, bookParkingLocationData.getBookingLongitude()));
            intent.putExtras(bn);
            launchActivity.launch(intent);
        } else if (i == binding.btnMap.getId()) {
            binding.btnMap.setTextColor(getResources().getColor(R.color.white));
            binding.btnMap.setBackground(new ColorDrawable(getResources().getColor(R.color.appThemeColor_1)));
            binding.btnSatellite.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            binding.btnSatellite.setBackground(new ColorDrawable(getResources().getColor(R.color.white)));
            parkingLocationMarker.remove();
            MarkerOptions parking_marker = new MarkerOptions().position(currentLatLon).icon(VectorUtils.vectorToBitmap(this, R.drawable.parking_loc_pin_dark, 0));
            parkingLocationMarker = googlemap.addMarker(parking_marker);
            googlemap.setMapTypeDefault();

        } else if (i == binding.btnSatellite.getId()) {
            binding.btnMap.setBackground(new ColorDrawable(getResources().getColor(R.color.white)));
            binding.btnMap.setTextColor(getResources().getColor(R.color.text23Pro_Dark));
            binding.btnSatellite.setTextColor(getResources().getColor(R.color.white));
            binding.btnSatellite.setBackground(new ColorDrawable(getResources().getColor(R.color.appThemeColor_1)));
            parkingLocationMarker.remove();
            MarkerOptions parking_marker = new MarkerOptions().position(currentLatLon).icon(VectorUtils.vectorToBitmap(this, R.drawable.parking_loc_pin, 0));
            parkingLocationMarker = googlemap.addMarker(parking_marker);
            googlemap.setMapTypeSatellite();

        } else if (i == binding.userLocationBtn.getId()) {
            googlemap.moveCamera(currentLatLon);
        } else if (i == btn_type2.getId()) {

            if (Utils.checkText(bookParkingLocationData.getBookingAddress())) {
                Bundle bn = new Bundle();
                bn.putString("Duration", duration_data);
                bn.putString("parkingId", parkingId);
                bn.putString("bookingAddress", bookParkingLocationData.getBookingAddress());
                bn.putString("bookingLatitude", bookParkingLocationData.getBookingLatitude());
                bn.putString("bookingLongitude", bookParkingLocationData.getBookingLongitude());
                bn.putSerializable("vehicleSizes", list_item);
                new ActUtils(BookParking.this).startActWithData(ParkingArrivalScheduleActivity.class, bn);
            } else {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_SELECT_PARKING_LOCATION_HINT_TXT"), buttonId -> {

                });

            }
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        Intent data = result.getData();
        if (result.getResultCode() == Activity.RESULT_OK && data != null) {
            binding.parkingAddress.setText(data.getStringExtra("Address"));
            bookParkingLocationData.setBookingAddress(data.getStringExtra("Address"));
            bookParkingLocationData.setBookingLatitude(data.getStringExtra("Latitude"));
            bookParkingLocationData.setBookingLongitude(data.getStringExtra("Longitude"));
            LatLng latLng = new LatLng(Double.parseDouble(data.getStringExtra("Latitude")), Double.parseDouble(data.getStringExtra("Longitude")));
            googlemap.moveCamera(latLng);
            currentLatLon = latLng;
            parkingLocationMarker.remove();
            MarkerOptions parking_marker = new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(this, R.drawable.parking_loc_pin, 0));
            parkingLocationMarker = googlemap.addMarker(parking_marker);

        }
    });

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        binding.parkingAddress.setText(address);
        bookParkingLocationData.setBookingAddress(address);
        bookParkingLocationData.setBookingLatitude(String.valueOf(latitude));
        bookParkingLocationData.setBookingLongitude(String.valueOf(longitude));
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        this.googlemap = geoMap;
        googlemap.setMapTypeSatellite();
        googlemap.setPadding(0, 0, 0, height);
        LatLng latLng = new LatLng(Double.parseDouble(getIntent().getStringExtra("lat")), Double.parseDouble(getIntent().getStringExtra("long")));
        bookParkingLocationData.setBookingAddress(getIntent().getStringExtra("address"));
        bookParkingLocationData.setBookingLatitude(getIntent().getStringExtra("lat"));
        bookParkingLocationData.setBookingLongitude(getIntent().getStringExtra("long"));
        currentLatLon = latLng;
        MarkerOptions parking_marker = new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(this, R.drawable.parking_loc_pin, 0));
        parkingLocationMarker = googlemap.addMarker(parking_marker);
        googlemap.moveCamera(latLng);
        getAddressFromLocation.setLocation(latLng.latitude, latLng.longitude);
        getAddressFromLocation.setLoaderEnable(true);
        getAddressFromLocation.execute();
    }

    @Override
    public void onItemClick(int position) {
        binding.llBtnType2.setVisibility(View.VISIBLE);
        bottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED);
        parkingId = list_item.get(position).get("iParkingVehicleSizeId");
        vehicleSizeAdapter.setSelectedVehicleTypeId(list_item.get(position).get("iParkingVehicleSizeId"));
        vehicleSizeAdapter.notifyDataSetChanged();

    }

    @Override
    public void onBackPressed() {
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PUBLISHED_RIDE_EXIT_TXT"), generalFunc.retrieveLangLBl("", "LBL_NO"),
                generalFunc.retrieveLangLBl("", "LBL_YES"), buttonId -> {
                    if (buttonId == 1) {
                        super.onBackPressed();
                    }
                });
    }

}