package com.act.rideSharingPro.fragment;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.act.SearchPickupLocationActivity;
import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.model.RideProPublishData;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishStep1Binding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.Polyline;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.model.ServiceModule;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.utils.MapUtils;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.Objects;

public class RidePublishStep1Fragment extends BaseFragment implements GeoMapLoader.OnMapReadyCallback {

    private FragmentRidePublishStep1Binding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private View marker_view;
    private GeneralFunctions generalFunc;
    private ArrayList<RideProPublishData.MultiStopData> mMultiStopData;
    private GeoMapLoader.GeoMap geoMap;
    private Marker startMarker, endMarker, startMarker_dot, endMarker_dot;

    private boolean isStartLocationClick = false;
    private Polyline route_polyLine;
    private String etaval = "--";
    private MTextView addressTxt;
    private MTextView etaTxt;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish_step_1, container, false);

        setLabels();
        setData();

        new GeoMapLoader(mActivity, binding.mapRidePublishContainer.getId()).bindMap(this);
        return binding.getRoot();
    }

    private void setLabels() {

        binding.startLocationBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_START_LOC_TXT"));
        binding.endLocationBox.setHint(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_END_LOC_TXT"));

        addToClickHandler(binding.startLocationBox);
        addToClickHandler(binding.endLocationBox);
        addToClickHandler(binding.locationFlipArea);
    }

    private void setData() {
        if (mActivity != null) {
            ArrayList<RideProPublishData.MultiStopData> multiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
            if (multiStopData != null) {
                binding.startLocationBox.setText(multiStopData.get(0).getDestAddress());
                binding.endLocationBox.setText(multiStopData.get(multiStopData.size() - 1).getDestAddress());
            }
        }
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            mActivity = (RideSharingProHomeActivity) requireActivity();
            generalFunc = mActivity.generalFunc;

            if (mActivity.rsPublishFragment != null && mActivity.rsPublishFragment.mPublishData != null) {
                if (mActivity.rsPublishFragment.mPublishData.getMultiStopData() == null) {
                    mMultiStopData = new ArrayList<>();
                    setBlankData("Yes");
                    setBlankData("");
                } else {
                    mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
                }
            }
        }
    }

    private void setBlankData(String IsFromLoc) {
        RideProPublishData.MultiStopData stopData = new RideProPublishData.MultiStopData();
        stopData.setDestLat(0.0);
        stopData.setDestLong(0.0);
        stopData.setHintLable(generalFunc.retrieveLangLBl("", "LBL_MULTI_ADD_NEW_DESTINATION"));
        stopData.setDestAddress("");
        stopData.setIsFromLoc(IsFromLoc);
        mMultiStopData.add(stopData);
    }

    private void setMarker(boolean isStartLocation, double latitude, double longitude) {
        LatLng latLng = new LatLng(latitude, longitude);
        geoMap.moveCamera(latLng);
        assert mActivity != null;

        if (marker_view == null) {
            marker_view = ((LayoutInflater) mActivity.getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.custom_marker, null);
            addressTxt = marker_view.findViewById(R.id.addressTxt);
            etaTxt = marker_view.findViewById(R.id.etaTxt);
        }
        if (isStartLocation) {
            etaTxt.setVisibility(View.VISIBLE);
            etaTxt.setText(etaval);
            addressTxt.setText(mMultiStopData.get(0).getDestAddress());

            if (startMarker != null) {
                startMarker.remove();
            }
            if (startMarker_dot != null) {
                startMarker_dot.remove();
            }

            startMarker_dot =geoMap.addMarker(new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(mActivity, R.drawable.marker_square, 0)));
            MarkerOptions marker_opt_source = new MarkerOptions().position(latLng).icon(BitmapDescriptorFactory.fromBitmap(MapUtils.createDrawableFromView(mActivity, marker_view))).anchor(0.1f, 1.40f);
            startMarker = geoMap.addMarker(marker_opt_source);
        } else {
            etaTxt.setVisibility(View.GONE);
            addressTxt.setText(mMultiStopData.get(mMultiStopData.size() - 1).getDestAddress());

            if (endMarker != null) {
                endMarker.remove();
            }
            if (endMarker_dot != null) {
                endMarker_dot.remove();
            }

            endMarker_dot =geoMap.addMarker(new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(mActivity, R.drawable.dot_filled_new, 0)));
            MarkerOptions marker_opt_source = new MarkerOptions().position(latLng).icon(BitmapDescriptorFactory.fromBitmap(MapUtils.createDrawableFromView(mActivity, marker_view))).anchor(0.1f, 1.40f);
            endMarker = geoMap.addMarker(marker_opt_source);
        }
    }

    private void findRoute() {

        if (startMarker_dot == null || endMarker_dot == null) {
            return;
        }
        assert mActivity != null;
        double sLat = mMultiStopData.get(0).getDestLat();
        double sLong = mMultiStopData.get(0).getDestLong();

        double eLat = mMultiStopData.get(mMultiStopData.size() - 1).getDestLat();
        double eLong = mMultiStopData.get(mMultiStopData.size() - 1).getDestLong();

        AppService.getInstance().executeService(mActivity, new DataProvider.DataProviderBuilder("" + sLat, "" + sLong)
                .setDestLatitude("" + eLat).setDestLongitude("" + eLong).setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {

            mActivity.rsPublishFragment.mDistance = String.valueOf(data.get("DISTANCE"));
            mActivity.rsPublishFragment.mDuration = String.valueOf(data.get("DURATION"));
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }
            if (Utils.checkText(mActivity.rsPublishFragment.mDistance)) {
                double distance_final = generalFunc.parseDoubleValue(0.0, mActivity.rsPublishFragment.mDistance);
                String eunit;
                if (generalFunc.getJsonValueStr("eUnit", mActivity.obj_userProfile).equalsIgnoreCase("KMs")) {
                    distance_final = distance_final * 0.00099999969062399994;
                    eunit = generalFunc.retrieveLangLBl("", "LBL_DISPLAY_KMS");
                } else {
                    distance_final = distance_final * 0.000621371;
                    eunit = generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT");
                }
                distance_final = generalFunc.round(distance_final, 2);
                etaval = distance_final + "\n" + eunit;
                setMarker(true, sLat, sLong);
                setMarker(false, eLat, eLong);
            }

            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            String responseString = data.get("RESPONSE_DATA").toString();

            if (responseString.equalsIgnoreCase("")) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            LatLng sourceLocation = new LatLng(sLat, sLong);
            LatLng destLocation = new LatLng(eLat, eLong);

            if (!responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {

                JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
                if (obj_routes != null && obj_routes.length() > 0) {
                    responseString = Objects.requireNonNull(data.get("ROUTES")).toString();
                    route_polyLine = MapUtils.handleMapAnimation(mActivity, generalFunc, responseString, sourceLocation, destLocation, geoMap, route_polyLine, true, false);
                } else {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("", "LBL_ERROR_TXT"), generalFunc.retrieveLangLBl("", "LBL_GOOGLE_DIR_NO_ROUTE"));
                }
            } else {
                HashMap<String, Object> data_dict = new HashMap<>();
                data_dict.put("routes", data.get("ROUTES"));
                responseString = data_dict.toString();
                route_polyLine = MapUtils.handleMapAnimation(mActivity, generalFunc, responseString, sourceLocation, destLocation, geoMap, route_polyLine, false, false);
            }
        });
    }

    private void setMapHeight() {
        assert mActivity != null;
        int minusValue = mActivity.rsPublishFragment.binding.toolbarInclude.toolbar.getLayoutParams().height
                + binding.rideStartEndLocationArea.getMeasuredHeight()
                + mActivity.rsPublishFragment.binding.bottomAreaView.getMeasuredHeight()
                + mActivity.getResources().getDimensionPixelSize(R.dimen._30sdp);

        if (ServiceModule.OnlyRideSharingPro) {
            minusValue = minusValue + mActivity.binding.bottomArea.getMeasuredHeight();
        }

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) binding.mapRidePublishContainer.getLayoutParams();
        params.height = (int) (Utils.getScreenPixelHeight(mActivity) - minusValue);
        binding.mapRidePublishContainer.setLayoutParams(params);
        mActivity.rsPublishFragment.setPagerHeight();
    }

    @Override
    public void onMapReady(GeoMapLoader.GeoMap googleMap) {
        assert mActivity != null;
        this.geoMap = googleMap;
        setMapHeight();
        googleMap.setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        Location myLocation = MyApp.getInstance().currentLocation;
        if (myLocation != null) {
            LatLng latLng = new LatLng(myLocation.getLatitude(), myLocation.getLongitude());
            geoMap.moveCamera(latLng);
            //startMarker = geoMap.addMarker(new MarkerOptions().position(latLng));
        }

        ArrayList<RideProPublishData.MultiStopData> multiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
        if (multiStopData != null) {
            binding.startLocationBox.setText(multiStopData.get(0).getDestAddress());
            binding.endLocationBox.setText(multiStopData.get(multiStopData.size() - 1).getDestAddress());
            setMarker(true, multiStopData.get(0).getDestLat(), multiStopData.get(0).getDestLong());
            setMarker(false, multiStopData.get(multiStopData.size() - 1).getDestLat(), multiStopData.get(multiStopData.size() - 1).getDestLong());
            findRoute();
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        assert mActivity != null;

        if (mActivity.rsPublishFragment.mPublishData.getMultiStopData() != null) {

            int endPos = mMultiStopData.size() - 1;

            if (Utils.getText(binding.startLocationBox).equalsIgnoreCase(mMultiStopData.get(0).getDestAddress()) &&
                    Utils.getText(binding.endLocationBox).equalsIgnoreCase(mMultiStopData.get(endPos).getDestAddress())) {
                return;
            }

            mMultiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();

            setMarker(true, mMultiStopData.get(0).getDestLat(), mMultiStopData.get(0).getDestLong());
            setMarker(false, mMultiStopData.get(endPos).getDestLat(), mMultiStopData.get(endPos).getDestLong());

            binding.startLocationBox.setText(mMultiStopData.get(0).getDestAddress());
            binding.endLocationBox.setText(mMultiStopData.get(endPos).getDestAddress());
            findRoute();
        }

    }

    public void setReturnRideFrag(ArrayList<RideProPublishData.MultiStopData> multiStopData) {
        String fromAddress = multiStopData.get(0).getDestAddress();
        double fromLatitude = multiStopData.get(0).getDestLat();
        double fromLongitude = multiStopData.get(0).getDestLong();

        int last = multiStopData.size() - 1;

        String toAddress = multiStopData.get(last).getDestAddress();
        double toLatitude = multiStopData.get(last).getDestLat();
        double toLongitude = multiStopData.get(last).getDestLong();

        if (binding != null) {
            binding.startLocationBox.setText(toAddress);
            binding.endLocationBox.setText(fromAddress);
        }
        multiStopData.get(last).setDestAddress(fromAddress);
        multiStopData.get(last).setDestLat(fromLatitude);
        multiStopData.get(last).setDestLong(fromLongitude);

        multiStopData.get(0).setDestAddress(toAddress);
        multiStopData.get(0).setDestLat(toLatitude);
        multiStopData.get(0).setDestLong(toLongitude);
        // To rearrange the list to reverse order for return MultiStop ride.
        if (multiStopData.size() > 2) {
            Collections.reverse(multiStopData);
        }

        assert mActivity != null;
        mMultiStopData = multiStopData;
        mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(), result -> {
                Intent data = result.getData();
                if (result.getResultCode() == Activity.RESULT_OK && data != null) {
                    assert mActivity != null;

                    if (isStartLocationClick) {
                        binding.startLocationBox.setText(data.getStringExtra("Address"));
                        mMultiStopData.get(0).setDestAddress(data.getStringExtra("Address"));
                        mMultiStopData.get(0).setDestLat(Double.parseDouble(data.getStringExtra("Latitude")));
                        mMultiStopData.get(0).setDestLong(Double.parseDouble(data.getStringExtra("Longitude")));

                    } else {
                        int last = mMultiStopData.size() - 1;
                        binding.endLocationBox.setText(data.getStringExtra("Address"));
                        mMultiStopData.get(last).setDestAddress(data.getStringExtra("Address"));
                        mMultiStopData.get(last).setDestLat(Double.parseDouble(data.getStringExtra("Latitude")));
                        mMultiStopData.get(last).setDestLong(Double.parseDouble(data.getStringExtra("Longitude")));
                    }
                    mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);

                    setMarker(isStartLocationClick, Double.parseDouble(data.getStringExtra("Latitude")), Double.parseDouble(data.getStringExtra("Longitude")));
                    findRoute();
                }
            });

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.startLocationBox.getId() || i == binding.endLocationBox.getId()) {

            isStartLocationClick = i == binding.startLocationBox.getId();
            Intent intent = new Intent(mActivity, SearchPickupLocationActivity.class);
            Bundle bndl = new Bundle();
            bndl.putString("IS_FROM_SELECT_LOC", "Yes");
            if (i == binding.startLocationBox.getId()) {
                if (Utils.checkText(Utils.getText(binding.startLocationBox))) {
                    bndl.putString("isPickUpLoc", "true");
                    if (mMultiStopData != null && mMultiStopData.size() > 0) {
                        bndl.putString("PickUpAddress", mMultiStopData.get(0).getDestAddress());
                        bndl.putString("PickUpLatitude", mMultiStopData.get(0).getDestLat() + "");
                        bndl.putString("PickUpLongitude", mMultiStopData.get(0).getDestLong() + "");
                    }
                }
            }
//            PickUpLatitude / PickUpLongitude / PickUpAddress == DestLatitude / DestLongitude / DestAddress
            if (i == binding.endLocationBox.getId()) {
                bndl.putString("locationArea", "dest");
                bndl.putBoolean("isFromGoingTo", true);
                if (Utils.checkText(Utils.getText(binding.endLocationBox))) {
                    bndl.putString("isDestLoc", "true");
                    bndl.putBoolean("isDataFilled", true);
                    if (mMultiStopData != null && mMultiStopData.size() > 0) {
                        int last = mMultiStopData.size() - 1;
                        bndl.putString("DestAddress", mMultiStopData.get(last).getDestAddress());
                        bndl.putString("DestLatitude", mMultiStopData.get(last).getDestLat() + "");
                        bndl.putString("DestLongitude", mMultiStopData.get(last).getDestLong() + "");
                    }
                }
            }
            intent.putExtras(bndl);
            launchActivity.launch(intent);

        } else if (i == binding.locationFlipArea.getId()) {
            assert mActivity != null;

            if (!Utils.checkText(Utils.getText(binding.startLocationBox)) || !Utils.checkText(Utils.getText(binding.endLocationBox))) {
                return;
            }

            String fromAddress = mMultiStopData.get(0).getDestAddress();
            double fromLatitude = mMultiStopData.get(0).getDestLat();
            double fromLongitude = mMultiStopData.get(0).getDestLong();

            int last = mMultiStopData.size() - 1;

            String toAddress = mMultiStopData.get(last).getDestAddress();
            double toLatitude = mMultiStopData.get(last).getDestLat();
            double toLongitude = mMultiStopData.get(last).getDestLong();

            binding.startLocationBox.setText(toAddress);
            binding.endLocationBox.setText(fromAddress);

            mMultiStopData.get(last).setDestAddress(fromAddress);
            mMultiStopData.get(last).setDestLat(fromLatitude);
            mMultiStopData.get(last).setDestLong(fromLongitude);

            mMultiStopData.get(0).setDestAddress(toAddress);
            mMultiStopData.get(0).setDestLat(toLatitude);
            mMultiStopData.get(0).setDestLong(toLongitude);

            mActivity.rsPublishFragment.mPublishData.setMultiStopData(mMultiStopData);
            findRoute();
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(binding.startLocationBox.getText().toString()) && Utils.checkText(binding.endLocationBox.getText().toString())) {
                mActivity.rsPublishFragment.onRecommendedPrice();
            } else {
                generalFunc.showMessage(binding.rideStartEndLocationArea, generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
            }
        }
    }
}