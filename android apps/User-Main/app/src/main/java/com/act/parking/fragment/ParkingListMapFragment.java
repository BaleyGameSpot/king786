package com.act.parking.fragment;

import static com.utils.MapUtils.createDrawableFromView;

import android.app.Activity;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.fragments.BaseFragment;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ParkingListMapFragmentLayoutBinding;
import com.act.parking.AvailableParkingSpacesActivity;
import com.act.parking.ParkingDetailsActivity;
import com.act.parking.ReviewOrCancelParkingBookingActivity;
import com.act.rentItem.adapter.PhotosAdapter;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingListMapFragment extends BaseFragment implements GeoMapLoader.OnMapReadyCallback {
    public ParkingListMapFragmentLayoutBinding binding;
    private AvailableParkingSpacesActivity mActivity;
    private GeneralFunctions generalFunc;
    private Marker parkingLocationMarker;
    private GeoMapLoader.GeoMap googlemap;
    LatLng finalLatLon;
    int markerPosition = -1;
    private HashMap<String, String> mapData;
    private PhotosAdapter photoAdapter;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.parking_list_map_fragment_layout, container, false);
        (new GeoMapLoader(mActivity, R.id.mapParkingBookingContainer)).bindMap(this);
        initialize();
        return binding.getRoot();
    }

    private void initialize() {
        addToClickHandler(binding.cancel);
        addToClickHandler(binding.detailsArea);
        addToClickHandler(binding.listItem.itemArea);
        addToClickHandler(binding.listItem.parkingPhotosViewPager);
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof AvailableParkingSpacesActivity) {
            mActivity = (AvailableParkingSpacesActivity) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap geoMap) {
        googlemap = geoMap;

        for (int i = 0; i < mActivity.listData.size(); i++) {

            mapData = mActivity.listData.get(i);
            LatLng latLng = new LatLng(Double.parseDouble(mapData.get("vLatitude")), Double.parseDouble(mapData.get("vLongitude")));
            finalLatLon = latLng;
            View markerView = ((LayoutInflater) mActivity.getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.item_book_parking_marker, null);


            MTextView priceTxt = (MTextView) markerView.findViewById(R.id.priceTxt);
            priceTxt.setText(mapData.get("tPrice"));
            MTextView perHour = (MTextView) markerView.findViewById(R.id.perHour);
            perHour.setText(mapData.get("tPriceShortSubText"));
            MarkerOptions parking_marker = new MarkerOptions().position(latLng).icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(mActivity, markerView))).anchor(0.00f, 0.20f);
            geoMap.setOnMarkerClickListener(marker -> {
                markerPosition = Integer.parseInt((String) marker.getTag());
                mapData = mActivity.listData.get(markerPosition);
                binding.listItem.priceTxt.setText(mapData.get("tPrice"));
                binding.listItem.priceMsgTxt.setText(mapData.get("tPriceSubText"));
                binding.listItem.parkingAddress.setText(mapData.get("tAddress"));
                binding.listItem.ratingTxt.setText(mapData.get("vAvgRating"));
                binding.listItem.distanceTxt.setText(mapData.get("distance") + " " + mapData.get("DistanceSubText"));
                binding.listItem.noOfRating.setText(mapData.get("TotalRatings"));
                binding.listItem.btnBookNow.setText(generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW"));
                int bannerWidth = (int) (Utils.getScreenPixelWidth(mActivity) - mActivity.getResources().getDimensionPixelSize(R.dimen._25sdp));
                int bannerHeight = mActivity.getResources().getDimensionPixelSize(R.dimen._150sdp);
                addToClickHandler(binding.listItem.btnBookNow);
                JSONArray photoArray = generalFunc.getJsonArray(mapData.get("ParkingSpaceImages"));
                ArrayList<HashMap<String, String>> photoData = new ArrayList<>();
                MyUtils.createArrayListJSONArray(generalFunc, photoData, photoArray);
                photoAdapter = new PhotosAdapter(photoData, bannerWidth, bannerHeight, (pos, finalImageUrl) -> {
                    binding.listItem.itemArea.performClick();
//                    (new ActUtils(mActivity)).openURL(finalImageUrl);
                }, false, true);
                binding.listItem.parkingPhotosViewPager.setEnabled(false);
                binding.listItem.parkingPhotosViewPager.setAdapter(photoAdapter);
                if (photoData.size() > 1) {
                    if (generalFunc.isRTLmode()) {
                        binding.listItem.parkingPhotosViewPager.setCurrentItem(photoData.size() - 1);
                    }
                    binding.listItem.dotsIndicator.setViewPager(binding.listItem.parkingPhotosViewPager);
                    binding.listItem.dotsIndicator.setVisibility(View.VISIBLE);
                    binding.listItem.dotsArea.setVisibility(View.VISIBLE);
                } else {
                    binding.listItem.dotsArea.setVisibility(View.GONE);
                }
                binding.detailsArea.setVisibility(View.VISIBLE);
                return false;
            });
            parkingLocationMarker = geoMap.addMarker(parking_marker);
            parkingLocationMarker.setTag(i + "");
            geoMap.moveCamera(latLng);

        }

    }
    @Override
    public void onResume() {
        HashMap<String, String> mapData = mActivity.listData.get(1);
        finalLatLon = new LatLng(Double.parseDouble(mapData.get("vLatitude")), Double.parseDouble(mapData.get("vLongitude")));
        googlemap.moveCamera(finalLatLon);
        super.onResume();
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.cancel.getId()) {
            binding.detailsArea.setVisibility(View.GONE);
        } else if (i == binding.listItem.btnBookNow.getId()) {
            Bundle bn = new Bundle();
            mapData = mActivity.listData.get(markerPosition);
            bn.putString("CallApi", "yes");
            bn.putString("parkingSpacesId", mapData.get("iParkingSpaceId"));
            bn.putString("duration", mActivity.getIntent().getStringExtra("duration"));
            bn.putString("bookingLatitude", mapData.get("vLatitude"));
            bn.putString("bookingLongitude", mapData.get("vLongitude"));
            bn.putString("ArrivalDate", mActivity.getIntent().getStringExtra("dateTime"));
            bn.putString("iParkingVehicleSizeId", mActivity.getIntent().getStringExtra("parkingId"));
            bn.putSerializable("vehicleSizes", mActivity.getIntent().getSerializableExtra("vehicleSizes"));
            new ActUtils(mActivity).startActWithData(ReviewOrCancelParkingBookingActivity.class, bn);
        } else if (i == binding.listItem.itemArea.getId()) {
            Bundle bn = new Bundle();
            bn.putString("parkingSpacesId", mapData.get("iParkingSpaceId"));
            bn.putString("duration", mActivity.getIntent().getStringExtra("duration"));
            bn.putString("bookingLatitude", mapData.get("vLatitude"));
            bn.putString("bookingLongitude", mapData.get("vLongitude"));
            bn.putString("ArrivalDate", mActivity.getIntent().getStringExtra("dateTime"));
            bn.putString("iParkingVehicleSizeId", mActivity.getIntent().getStringExtra("parkingId"));
            bn.putSerializable("vehicleSizes", mActivity.getIntent().getSerializableExtra("vehicleSizes"));
            new ActUtils(mActivity).startActWithData(ParkingDetailsActivity.class, bn);
        }
    }

}
