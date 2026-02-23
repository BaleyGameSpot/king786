package com.act.parking.fragment;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.act.SearchLocationActivity;
import com.buddyverse.main.databinding.FragmentParkingPublishStep1Binding;
import com.act.parking.ParkingPublish;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.utils.Utils;
import com.utils.VectorUtils;

public class ParkingPublishStep1Fragment extends BaseFragment implements GeoMapLoader.OnMapReadyCallback, GetAddressFromLocation.AddressFound {

    FragmentParkingPublishStep1Binding binding;
    private ParkingPublish mActivity;
    private GeneralFunctions generalFunc;
    private Marker parkingLocationMarker;
    private LatLng currentLatLon;
    GetAddressFromLocation getAddressFromLocation;
    private GeoMapLoader.GeoMap googlemap;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_parking_publish_step_1, container, false);
        setlabels();
        setdata();
        (new GeoMapLoader(mActivity, R.id.mapParkingPublishContainer)).bindMap(this);

        new Handler(Looper.getMainLooper()).postDelayed(() -> {

            int minusValue = mActivity.mToolbar.getLayoutParams().height
                    + binding.parkingLocationArea.getMeasuredHeight()
                    + mActivity.binding.bottomAreaView.getMeasuredHeight()
                    + mActivity.getResources().getDimensionPixelSize(R.dimen._7sdp);

            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) binding.mapParkingPublishContainer.getLayoutParams();
            params.height = (int) (Utils.getScreenPixelHeight(mActivity) - minusValue);
            binding.mapParkingPublishContainer.setLayoutParams(params);
            mActivity.setPagerHeight();
        }, 50);
        return binding.getRoot();
    }

    private void setdata() {
        getAddressFromLocation = new GetAddressFromLocation(getContext(), mActivity.generalFunc);
        getAddressFromLocation.setAddressList(this);
        addToClickHandler(binding.editLocation);
        addToClickHandler(binding.userLocationBtn);
    }

    private void setlabels() {
        binding.enterParkingLocation.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_PARKING_LOCATION_TXT"));
        binding.btnMap.setText(generalFunc.retrieveLangLBl("", "LBL_MAP_TXT"));
        binding.btnSatellite.setText(generalFunc.retrieveLangLBl("", "LBL_SATELLITE_TXT"));
        binding.dragTheMapInfo.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_PLACE_MARKER_ON_MAP_INFO"));
        binding.bottomTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_PROVIDED_UPON_CONFIRMATION_INFO"));
        binding.parkingAddress.setHint(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
        binding.llMapType.setClipToOutline(true);
        addToClickHandler(binding.btnSatellite);
        addToClickHandler(binding.btnMap);


    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof ParkingPublish) {
            mActivity = (ParkingPublish) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        assert mActivity != null;
        this.googlemap = geoMap;
        googlemap.setMapTypeSatellite();
        LatLng latLng = new LatLng(Double.parseDouble(mActivity.getIntent().getStringExtra("lat")), Double.parseDouble(mActivity.getIntent().getStringExtra("long")));
        currentLatLon = latLng;
        mActivity.parkingPublishLocationData.setParkingAddress(mActivity.getIntent().getStringExtra("address"));
        mActivity.parkingPublishLocationData.setParkingLatitude(mActivity.getIntent().getStringExtra("lat"));
        mActivity.parkingPublishLocationData.setParkingLongitude(mActivity.getIntent().getStringExtra("long"));
        MarkerOptions parking_marker = new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(getContext(), R.drawable.parking_loc_pin, 0));
        parkingLocationMarker = googlemap.addMarker(parking_marker);
        googlemap.moveCamera(latLng);
        getAddressFromLocation.setLocation(latLng.latitude, latLng.longitude);
        getAddressFromLocation.setLoaderEnable(true);
        getAddressFromLocation.execute();


    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(binding.parkingAddress.getText().toString())) {
                mActivity.setPageNext();
            }
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    assert mActivity != null;
                    binding.parkingAddress.setText(data.getStringExtra("Address"));
                    mActivity.parkingPublishLocationData.setParkingAddress(data.getStringExtra("Address"));
                    mActivity.parkingPublishLocationData.setParkingLatitude(data.getStringExtra("Latitude"));
                    mActivity.parkingPublishLocationData.setParkingLongitude(data.getStringExtra("Longitude"));
                    LatLng latLng = new LatLng(Double.parseDouble(data.getStringExtra("Latitude")), Double.parseDouble(data.getStringExtra("Longitude")));
                    googlemap.moveCamera(latLng);
                    currentLatLon = latLng;
                    parkingLocationMarker.remove();
                    MarkerOptions parking_marker = new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(getContext(), R.drawable.parking_loc_pin, 0));
                    parkingLocationMarker = googlemap.addMarker(parking_marker);

                }
            });

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        binding.parkingAddress.setText(address);
        mActivity.parkingPublishLocationData.setParkingAddress(address);
        mActivity.parkingPublishLocationData.setParkingLatitude(String.valueOf(latitude));
        mActivity.parkingPublishLocationData.setParkingLongitude(String.valueOf(longitude));
    }

    @SuppressLint("ResourceType")
    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.editLocation.getId()) {
            Intent intent = new Intent(mActivity, SearchLocationActivity.class);
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, mActivity.parkingPublishLocationData.getParkingLatitude()));
            bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, mActivity.parkingPublishLocationData.getParkingLongitude()));
            intent.putExtras(bn);
            launchActivity.launch(intent);
        } else if (i == binding.btnMap.getId()) {
            binding.dragTheMapInfo.setTextColor(mActivity.getResources().getColor(R.color.white));
            binding.dragTheMapInfo.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mActivity, R.color.eme_txt_color)));
            binding.btnMap.setTextColor(mActivity.getResources().getColor(R.color.white));
            binding.btnMap.setBackground(new ColorDrawable(mActivity.getResources().getColor(R.color.appThemeColor_1)));
            binding.btnSatellite.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
            binding.btnSatellite.setBackground(new ColorDrawable(mActivity.getResources().getColor(R.color.white)));
            parkingLocationMarker.remove();
            MarkerOptions parking_marker = new MarkerOptions().position(currentLatLon).icon(VectorUtils.vectorToBitmap(getContext(), R.drawable.parking_loc_pin_dark, 0));
            parkingLocationMarker = googlemap.addMarker(parking_marker);
            googlemap.setMapTypeDefault();

        } else if (i == binding.btnSatellite.getId()) {
            binding.dragTheMapInfo.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
            binding.dragTheMapInfo.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(mActivity, R.color.white)));
            binding.btnMap.setBackground(new ColorDrawable(mActivity.getResources().getColor(R.color.white)));
            binding.btnMap.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
            binding.btnSatellite.setTextColor(mActivity.getResources().getColor(R.color.white));
            binding.btnSatellite.setBackground(new ColorDrawable(mActivity.getResources().getColor(R.color.appThemeColor_1)));
            parkingLocationMarker.remove();
            MarkerOptions parking_marker = new MarkerOptions().position(currentLatLon).icon(VectorUtils.vectorToBitmap(getContext(), R.drawable.parking_loc_pin, 0));
            parkingLocationMarker = googlemap.addMarker(parking_marker);
            googlemap.setMapTypeSatellite();

        } else if (i == binding.userLocationBtn.getId()) {
            googlemap.moveCamera(currentLatLon);
        }

    }
}
