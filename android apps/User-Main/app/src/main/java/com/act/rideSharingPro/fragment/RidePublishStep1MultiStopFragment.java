package com.act.rideSharingPro.fragment;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.act.SearchPickupLocationActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.adapter.MultiStopAdapter;
import com.act.rideSharingPro.model.RideProPublishData;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishStep1MutiStopBinding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.MarkerOptions;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.MapUtils;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.Objects;

public class RidePublishStep1MultiStopFragment extends BaseFragment implements GeoMapLoader.OnMapReadyCallback {

    private FragmentRidePublishStep1MutiStopBinding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private GeneralFunctions generalFunc;
    private GeoMapLoader.GeoMap geoMap;

    private MultiStopAdapter destAdapter;
    private View marker_view;
    private MTextView pin_text;
    private ImageView pin_image;
    private String LBL_MULTI_FR_TXT, LBL_MULTI_TO_TXT;
    private LatLngBounds.Builder builder;
    private int selectPos = -1;


    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish_step_1_muti_stop, container, false);

        binding.multiStopHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_STOP_OVER_POINTS_TEXT"));
        addToClickHandler(binding.ivCurrentLocation);

        assert mActivity != null;
        new Handler(Looper.getMainLooper()).postDelayed(() -> {

            LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) binding.mainContainer.getLayoutParams();
            params.height = binding.mainContainer.getHeight();
            binding.mainContainer.setLayoutParams(params);
            mActivity.rsPublishFragment.setPagerHeight();

            (new GeoMapLoader(mActivity, R.id.mapRidePublishMultiStopContainer)).bindMap(this);
        }, 50);

        destAdapter = new MultiStopAdapter(mActivity.rsPublishFragment.mPublishData.getMultiStopData(), new MultiStopAdapter.OnItemClickListener() {
            @Override
            public void onItemClickList(int position, String type, RideProPublishData.MultiStopData itemData) {
                if (type.equalsIgnoreCase("Select")) {

                    selectPos = position;

                    Intent intent = new Intent(mActivity, SearchPickupLocationActivity.class);
                    Bundle bndl = new Bundle();
                    bndl.putString("isDestLoc", "true");
                    bndl.putString("IS_FROM_SELECT_LOC", "Yes");
                    bndl.putString("locationArea", "dest");
                    if (itemData.getDestLat() == 0.0 || itemData.getDestLong() == 0.0 || !Utils.checkText(itemData.getDestAddress())) {
                        if (!itemData.getIsFromLoc().equalsIgnoreCase("Yes")) {
                            bndl.putBoolean("isFromGoingTo", true);
                        }
                    }
                    bndl.putString("DestAddress", itemData.getDestAddress());
                    bndl.putString("DestLatitude", "" + (itemData.getDestLat() == 0.0 ? MyApp.getInstance().currentLocation == null ? "" : MyApp.getInstance().currentLocation.getLatitude() : itemData.getDestLat()));
                    bndl.putString("DestLongitude", "" + (itemData.getDestLong() == 0.0 ? MyApp.getInstance().currentLocation == null ? "" : MyApp.getInstance().currentLocation.getLongitude() : itemData.getDestLong()));
                    intent.putExtras(bndl);
                    launchActivityEdit.launch(intent);

                } else if (type.equalsIgnoreCase("Add")) {
                    addStopView();
                } else if (type.equalsIgnoreCase("Remove")) {
                    removeStopView(itemData, true);
                }
            }

            @Override
            public void onLastPosition() {
                ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
                if (mMultiStopData != null && mMultiStopData.size() > 0) {
                    binding.rvRidePublishMultiStopList.smoothScrollToPosition(Objects.requireNonNull(binding.rvRidePublishMultiStopList.getAdapter()).getItemCount());
                    geoMap.clear();
                    if (builder != null) {
                        builder = null;
                    }
                    builder = new LatLngBounds.Builder();
                    for (int j = 0; j < mMultiStopData.size(); j++) {
                        addSourceMarker(j, mMultiStopData);
                    }
                    MapUtils.bindAllMarkers(mActivity, builder, geoMap);
                    findRoute(mMultiStopData);
                }
            }
        });
        binding.rvRidePublishMultiStopList.setAdapter(destAdapter);

        return binding.getRoot();
    }

    private void removeStopView(@NonNull RideProPublishData.MultiStopData itemData, boolean isResumeCall) {
        assert mActivity != null;
        ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
        if (mMultiStopData != null) {
            mMultiStopData.remove(itemData);
            mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);
            if (isResumeCall) {
                onResume();
            }
        }
    }

    private void addStopView() {
        assert mActivity != null;
        ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
        if (mMultiStopData != null) {
            int secLastPos = mMultiStopData.size() - 2;
            if (mMultiStopData.get(secLastPos).getDestLat() == 0.0 || mMultiStopData.get(secLastPos).getDestLong() == 0.0 || !Utils.checkText(mMultiStopData.get(secLastPos).getDestAddress())) {
                return;
            }
            RideProPublishData.MultiStopData stopData = new RideProPublishData.MultiStopData();
            stopData.setHintLable(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_ADD_STOP_TEXT"));
            if (mMultiStopData.size() == 2) {
                mMultiStopData.add(1, stopData);
            } else {
                mMultiStopData.add(mMultiStopData.size() - 1, stopData);
            }
            mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);
            onResume();
        }
    }

    ActivityResultLauncher<Intent> launchActivityEdit = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        Intent data = result.getData();
        if (result.getResultCode() == Activity.RESULT_OK && data != null) {
            assert mActivity != null;

            ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
            if (mMultiStopData != null) {
                RideProPublishData.MultiStopData stopData = mMultiStopData.get(selectPos);
                stopData.setDestAddress(data.getStringExtra("Address"));
                stopData.setDestLat(Double.parseDouble(data.getStringExtra("Latitude")));
                stopData.setDestLong(Double.parseDouble(data.getStringExtra("Longitude")));
                mMultiStopData.set(selectPos, stopData);
                mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);
                onResume();
            }
        }
        selectPos = -1;
    });

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            mActivity = (RideSharingProHomeActivity) requireActivity();
            generalFunc = mActivity.generalFunc;

            if (generalFunc == null) {
                generalFunc = MyApp.getInstance().getAppLevelGeneralFunc();
            }

            LBL_MULTI_FR_TXT = generalFunc.retrieveLangLBl("FR", "LBL_MULTI_FR_TXT");
            LBL_MULTI_TO_TXT = generalFunc.retrieveLangLBl("TO", "LBL_MULTI_TO_TXT");

            marker_view = ((LayoutInflater) mActivity.getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.custom_marker_parcel_delivery, null);

            pin_image = marker_view.findViewById(R.id.pin_image);
            pin_text = marker_view.findViewById(R.id.pintext);
        }
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap googleMap) {
        assert mActivity != null;
        this.geoMap = googleMap;
        googleMap.setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });
    }

    @Override
    public void onResume() {
        super.onResume();
        assert mActivity != null;
        addStopView();

        ArrayList<RideProPublishData.MultiStopData> multiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
        destAdapter.updateData(multiStopData);

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) binding.listArea.getLayoutParams();
        if (multiStopData != null && multiStopData.size() >= 3) {
            if (multiStopData.size() == 3) {
                params.weight = 4f;
            } else if (multiStopData.size() == 4) {
                params.weight = 5f;
            } else if (multiStopData.size() == 5) {
                params.weight = 6f;
            } else if (multiStopData.size() == 6) {
                params.weight = 7f;
            } else {
                params.weight = 8f;
            }
        } else {
            params.weight = 8f;
        }
        binding.listArea.setLayoutParams(params);
    }

    private void findRoute(ArrayList<RideProPublishData.MultiStopData> mMultiStopData) {
        assert mActivity != null;
        int lastPos = mMultiStopData.size() - 1;

        ArrayList<String> data_waypoints = new ArrayList<>();
        /*for (int k = 0; k < mMultiStopData.size(); k++) {
            if (k == 0 || k == lastPos) {
                //
            } else {
                data_waypoints.add(mMultiStopData.get(k).getDestLat() + "," + mMultiStopData.get(k).getDestLong());
            }
        }*/

        AppService.getInstance().executeService(mActivity, new DataProvider.DataProviderBuilder(mMultiStopData.get(0).getDestLat() + "", mMultiStopData.get(0).getDestLong() + "").setDestLatitude("" + mMultiStopData.get(lastPos).getDestLat()).setDestLongitude("" + mMultiStopData.get(lastPos).getDestLong()).setWayPoints(MyApp.getInstance().GetStringArray(data_waypoints)).build(), AppService.Service.DIRECTION, data -> {
            mActivity.rsPublishFragment.mDistance = String.valueOf(data.get("DISTANCE"));
            mActivity.rsPublishFragment.mDuration = String.valueOf(data.get("DURATION"));
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                return;
            }

            String responseString = data.get("RESPONSE_DATA").toString();
            if (responseString.equalsIgnoreCase("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
        });
    }

    private void addSourceMarker(int pos, ArrayList<RideProPublishData.MultiStopData> mMultiStopData) {
        assert mActivity != null;

        if (mMultiStopData.get(pos).getDestLat() == 0.0 || mMultiStopData.get(pos).getDestLong() == 0.0 || !Utils.checkText(mMultiStopData.get(pos).getDestAddress())) {
            return;
        }
        int pinIcon;
        String pinText;
        if (pos == 0) {
            pinIcon = R.drawable.pin_dest_green_multi;
            pinText = LBL_MULTI_FR_TXT;
        } else if (pos == mMultiStopData.size() - 1) {
            pinIcon = R.drawable.pin_dest_select_multi;
            int secLastPos = mMultiStopData.size() - 2;
            if (mMultiStopData.get(secLastPos).getDestLat() == 0.0 || mMultiStopData.get(secLastPos).getDestLong() == 0.0 || !Utils.checkText(mMultiStopData.get(secLastPos).getDestAddress())) {
                if (mMultiStopData.size() == 3) {
                    pinText = LBL_MULTI_TO_TXT;
                } else {
                    pinText = mMultiStopData.size() > 2 ? "" + (pos - 1) : LBL_MULTI_TO_TXT;
                }
            } else {
                pinText = mMultiStopData.size() > 2 ? "" + pos : LBL_MULTI_TO_TXT;
            }
        } else {
            pinIcon = R.drawable.pin_dest_stop_point_multi;
            pinText = "" + pos;
        }

        pin_image.setImageDrawable(ContextCompat.getDrawable(mActivity, pinIcon));
        pin_text.setText(pinText);

        Marker source_marker = geoMap.addMarker(new MarkerOptions().position(new LatLng(mMultiStopData.get(pos).getDestLat(), mMultiStopData.get(pos).getDestLong())).icon(BitmapDescriptorFactory.fromBitmap(MapUtils.createDrawableFromView(mActivity, marker_view))));
        builder.include(source_marker.getPosition());
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.ivCurrentLocation.getId()) {
            int padding = (int) (Utils.getScreenPixelWidth(mActivity) * 0.15); // offset from edges of the map 15% of screen
            if (builder != null) {
                geoMap.moveCamera(builder.build(), padding);
            }
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            ArrayList<RideProPublishData.MultiStopData> mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
            if (mMultiStopData != null && mMultiStopData.size() >= 2) {
                boolean isAllAddressDone = true;
                RideProPublishData.MultiStopData itemData = null;
                for (int j = 0; j < mMultiStopData.size(); j++) {
                    if (mMultiStopData.get(j).getDestLat() == 0.0 || mMultiStopData.get(j).getDestLong() == 0.0 || !Utils.checkText(mMultiStopData.get(j).getDestAddress())) {
                        isAllAddressDone = false;
                        itemData = mMultiStopData.get(j);
                    }
                }
                if (!isAllAddressDone) {
                    removeStopView(itemData, false);
                }
                mActivity.rsPublishFragment.onRecommendedPrice();
                /*if (isAllAddressDone) {
                    mActivity.rsPublishFragment.onRecommendedPrice();
                } else {
                    generalFunc.showMessage(binding.ivCurrentLocation, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                }*/
            } else {
                generalFunc.showMessage(binding.ivCurrentLocation, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}