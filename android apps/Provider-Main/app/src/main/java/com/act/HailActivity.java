package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.view.Gravity;
import android.view.View;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import com.activity.ParentActivity;
import com.fragments.CabSelectionFragment;
import com.general.files.ActUtils;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.google.android.gms.maps.CameraUpdate;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.buddyverse.providers.R;
import com.utils.Logger;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;

public class HailActivity extends ParentActivity implements GetLocationUpdates.LocationUpdatesListener, OnMapReadyCallback, GetAddressFromLocation.AddressFound {

    public Location userLocation, destLocation;
    public String pickupaddress = "", Destinationaddress = "", destlat = "", destlong = "";
    public ArrayList<String> cabTypesArrList = new ArrayList<>();
    public CabSelectionFragment cabSelectionFrag;
    public View toolbararea;
    public LinearLayout destarea;
    public boolean isVerticalCabscroll = false;
    private MTextView destLocHTxt, destLocTxt;
    public MTextView titleTxt;
    private GoogleMap gMap;
    private ImageView pinImgView, addDestLocImgView;
    private GetAddressFromLocation getAddressFromLocation;
    private ProgressBar progressBar;
    private boolean isAddressEnable, isdstination = false;
    private final static int RENTAL_REQ_CODE = 1234;
    private Location tempLoc = null;
    private FrameLayout mainContent;
    public BottomSheetBehavior cabBottomSheetBehavior;
    public int lastPanelHeight = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_hail);


        SupportMapFragment map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);

        destLocation = new Location("dest");

        if (generalFunc.getJsonValueStr("VEHICLE_TYPE_SHOW_METHOD", obj_userProfile) != null &&
                generalFunc.getJsonValueStr("VEHICLE_TYPE_SHOW_METHOD", obj_userProfile).equalsIgnoreCase("Vertical")) {
            isVerticalCabscroll = true;
        }


        toolbararea = findViewById(R.id.toolbararea);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        ImageView backImgView = (ImageView) findViewById(R.id.backImgView);
        addToClickHandler(backImgView);


        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);


        destarea = (LinearLayout) findViewById(R.id.destarea);
        destLocHTxt = (MTextView) findViewById(R.id.destLocHTxt);

        addToClickHandler(destarea);
        destLocTxt = (MTextView) findViewById(R.id.destLocTxt);
        pinImgView = (ImageView) findViewById(R.id.pinImgView);
        addDestLocImgView = (ImageView) findViewById(R.id.addDestLocImgView1);
        mainContent = (FrameLayout) findViewById(R.id.mainContent);

        intCheck = new InternetConnection(this);

        View imagemarkerdest2 = (View) findViewById(R.id.imagemarkerdest2);
        if (map != null) {
            map.getMapAsync(HailActivity.this);
        }

        setLabel();
        destarea.setEnabled(false);
        new CreateRoundedView(getActContext().getResources().getColor(R.color.pickup_req_later_btn), Utils.dipToPixels(getActContext(), 6), 2,
                getActContext().getResources().getColor(R.color.pickup_req_later_btn), imagemarkerdest2);
        progressBar = (ProgressBar) findViewById(R.id.mProgressBar);
        showprogress();
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
    }

    private void showprogress() {
        progressBar.setVisibility(View.VISIBLE);
        progressBar.setIndeterminate(true);
        progressBar.getIndeterminateDrawable().setColorFilter(getActContext().getResources().getColor(R.color.appThemeColor_2), android.graphics.PorterDuff.Mode.SRC_IN);
    }

    public void enableDisableBottomSheetDrag(boolean value) {
        cabBottomSheetBehavior.setDraggable(value);
    }

    public void hideprogress() {
        if (progressBar != null) {
            progressBar.setVisibility(View.GONE);
        }
    }

    public void setPanelHeight(int value) {
        Logger.d("setPanelHeight", "::" + value);

        int FragHeight = value;
        if (cabSelectionFrag != null) {
            if (isVerticalCabscroll) {
                lastPanelHeight = value;
                updateMapHeight(value);
            } else {
                gMap.setPadding(0, 0, 0, Utils.dipToPixels(getActContext(), value + 5f));
            }
        }
        if (cabBottomSheetBehavior != null) {
            cabBottomSheetBehavior.setPeekHeight(FragHeight);
        }
    }

    public void updateMapHeight(int panelHeight) {
        int rentValue = 0;
        if (cabSelectionFrag != null) {
            if (cabSelectionFrag.rentalarea.getVisibility() == View.VISIBLE) {
                rentValue = cabSelectionFrag.rentalAreaHeight + getResources().getDimensionPixelSize(R.dimen._30sdp);
            } else {
                rentValue = getResources().getDimensionPixelSize(R.dimen._30sdp);
            }
        }
        RelativeLayout.LayoutParams flParams = (RelativeLayout.LayoutParams) mainContent.getLayoutParams();
        flParams.height = (int) (Utils.getScreenPixelHeight(this) - panelHeight - toolbararea.getMeasuredHeight() + rentValue);
        mainContent.setLayoutParams(flParams);

    }

    public void OpenCardPaymentAct(boolean fromcabselection) {
        Bundle bn = new Bundle();
        bn.putBoolean("fromcabselection", fromcabselection);
        new ActUtils(getActContext()).startActForResult(CardPaymentActivity.class, bn, Utils.CARD_PAYMENT_REQ_CODE);
    }

    public Context getActContext() {
        return HailActivity.this;
    }

    private void setLabel() {
        titleTxt.setText(generalFunc.retrieveLangLBl("Taxi Hail", "LBL_TAXI_HAIL"));
        destLocHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DROP_AT"));
        destLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));
        addDestLocImgView.setImageResource(R.mipmap.plus);
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (pickupaddress.equals("") || pickupaddress.length() == 0) {
            destarea.setEnabled(true);
            pickupaddress = address;
            hideprogress();
        }
        if (isdstination) {
            isdstination = false;

            destLocTxt.setText(address);
            addDestLocImgView.setImageResource(R.drawable.ic_pencil_edit_button);
            Destinationaddress = address;
            destlat = latitude + "";
            destlong = longitude + "";

            destLocation.setLatitude(latitude);
            destLocation.setLongitude(longitude);

        }
    }

    @Override
    public void onLocationUpdate(Location location) {

        this.userLocation = location;
        if (pickupaddress.equals("") || pickupaddress.length() == 0) {
            if (tempLoc != null && tempLoc.getLatitude() == location.getLatitude()) {
                return;
            }

            if (getAddressFromLocation != null) {
                getAddressFromLocation.setLocation(location.getLatitude(), location.getLongitude());
                getAddressFromLocation.execute();
            }
        }
        tempLoc = location;

        if (!Destinationaddress.equals("")) {
            return;
        }

        CameraUpdate cameraPosition = generalFunc.getCameraPosition(userLocation, gMap);


        if (cameraPosition != null)
            getMap().moveCamera(cameraPosition);


        String isGoOnline = generalFunc.retrieveValue(Utils.GO_ONLINE_KEY);

        if ((isGoOnline != null && isGoOnline.equals("Yes"))) {

            HashMap<String, String> storeData = new HashMap<>();
            storeData.put(Utils.GO_ONLINE_KEY, "No");
            storeData.put(Utils.LAST_FINISH_TRIP_TIME_KEY, "0");
            generalFunc.storeData(storeData);

        }
    }

    private GoogleMap getMap() {
        return this.gMap;
    }

    @Override
    protected void onResume() {
        super.onResume();
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
    }

    @Override
    protected void onPause() {
        super.onPause();

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        removeLocationUpdates();
    }

    private void removeLocationUpdates() {

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        if (getAddressFromLocation != null) {
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        }

        if (gMap != null) {
            this.gMap.setOnCameraChangeListener(null);
            this.gMap = null;
        }

        this.userLocation = null;
    }

    @SuppressLint("PotentialBehaviorOverride")
    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;

        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(true);
            if (isVerticalCabscroll) {
                /** Because programmatically map size is _30sdp below behind the panel height, we need to give padding _30sdp bottom to get the google logo top of panel*/
                //TODO if you are going to change padding of map, please check method [[updatePinImageViewMargin()]] to point out PinImageView at proper location
                getMap().setPadding(generalFunc.isRTLmode() ? 0 : getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp), getActContext().getResources().getDimensionPixelSize(R.dimen._30sdp), generalFunc.isRTLmode() ? getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp) : 0, getActContext().getResources().getDimensionPixelSize(R.dimen._30sdp));
            } else {
                getMap().setPadding(0, 0, 0, Utils.dipToPixels(getActContext(), 15));
            }
            getMap().getUiSettings().setTiltGesturesEnabled(false);
            getMap().getUiSettings().setZoomControlsEnabled(false);
            getMap().getUiSettings().setCompassEnabled(false);
            getMap().getUiSettings().setMyLocationButtonEnabled(false);
        }

        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        GetLocationUpdates.getInstance().startLocationUpdates(this, this);

    }

    public void removeImage(View v) {
        if (cabSelectionFrag != null) {
            cabSelectionFrag.removeImage(v);
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.SEARCH_DEST_LOC_REQ_CODE) {

            if (resultCode == RESULT_OK && data != null && gMap != null) {

                isdstination = true;
                isAddressEnable = true;

                String Latitude = data.getStringExtra("Latitude");
                String Longitude = data.getStringExtra("Longitude");
                String Address = data.getStringExtra("Address");

                LatLng placeLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, Latitude), GeneralFunctions.parseDoubleValue(0.0, Longitude));
                destlat = Latitude;
                destlong = Longitude;


                Destinationaddress = Address;
                destLocTxt.setText(Address);
                addDestLocImgView.setImageResource(R.drawable.ic_pencil_edit_button);


                gMap.setOnCameraChangeListener(new onGoogleMapCameraChangeList());
                CameraUpdate cu = CameraUpdateFactory.newLatLngZoom(placeLocation, 14.0f);

                addcabselectionFragment();
                if (gMap != null) {
                    gMap.clear();
                    gMap.moveCamera(cu);
                }

                destlat = Latitude;
                destlong = Longitude;
                pinImgView.setVisibility(View.VISIBLE);
                updatePinImageViewMargin();
            }
        } else if (requestCode == RENTAL_REQ_CODE) {

            if (resultCode == RESULT_OK) {

                if (cabSelectionFrag != null) {
                    if (data != null && data.getStringExtra("iRentalPackageId") != null)
                        cabSelectionFrag.iRentalPackageId = data.getStringExtra("iRentalPackageId");
                    cabSelectionFrag.RentalTripHandle();
                }
            }
        }
    }

    private void addcabselectionFragment() {
        if (cabSelectionFrag == null) {
            cabSelectionFrag = new CabSelectionFragment();
            Bundle bundle = new Bundle();
            cabSelectionFrag.setArguments(bundle);
            pinImgView.setVisibility(View.VISIBLE);
        }
        super.onPostResume();
        getSupportFragmentManager().beginTransaction().replace(R.id.dragView, cabSelectionFrag).commit();
    }

    private void updatePinImageViewMargin() {
        new Handler().postDelayed(() -> {

            FrameLayout.LayoutParams lp = new FrameLayout.LayoutParams(getActContext().getResources().getDimensionPixelSize(R.dimen._40sdp), getActContext().getResources().getDimensionPixelSize(R.dimen._40sdp));
            lp.bottomMargin = (pinImgView.getMeasuredHeight() / 2);
            lp.gravity = Gravity.CENTER;
            pinImgView.setLayoutParams(lp);

        }, 100);
    }

    public void handleImgUploadResponse(String responseString, String imageUploadedType) {

        if (cabSelectionFrag != null) {
            cabSelectionFrag.handleImgUploadResponse(responseString);
        }
    }

    private class onGoogleMapCameraChangeList implements GoogleMap.OnCameraChangeListener {

        @Override
        public void onCameraChange(CameraPosition cameraPosition) {
            LatLng center = gMap.getCameraPosition().target;

            if (!isAddressEnable) {
                getAddressFromLocation.setLocation(center.latitude, center.longitude);
                getAddressFromLocation.execute();
                destLocTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECTING_LOCATION_TXT"));
                addDestLocImgView.setImageResource(R.drawable.ic_pencil_edit_button);
                destlat = center.latitude + "";
                destlong = center.longitude + "";
            } else {
                isAddressEnable = false;
            }

            if (cabSelectionFrag != null) {
                isdstination = true;
                showprogress();
                cabSelectionFrag.findRoute();
            }
        }
    }

    @Override
    public void onBackPressed() {
        if (cabSelectionFrag != null && cabSelectionFrag.design_linear_layout_car_details.getVisibility() == View.VISIBLE) {
            cabSelectionFrag.animateCarView(View.GONE);
            return;
        }
        if (isVerticalCabscroll && cabSelectionFrag != null && cabBottomSheetBehavior != null && cabBottomSheetBehavior.getState() == BottomSheetBehavior.STATE_EXPANDED) {
            cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED);
            return;
        }
        super.onBackPressed();
    }

    public void onClick(View view) {
        int id = view.getId();
        Utils.hideKeyboard(HailActivity.this);
        if (view.getId() == R.id.backImgView) {
            onBackPressed();
        } else if (id == destarea.getId()) {

            if (userLocation == null) {
                return;
            }
            Bundle bn = new Bundle();
            bn.putString("locationArea", "dest");
            bn.putDouble("lat", userLocation.getLatitude());
            bn.putDouble("long", userLocation.getLongitude());
            new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class, bn, Utils.SEARCH_DEST_LOC_REQ_CODE);
        }
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        if (cabSelectionFrag != null) {
            cabSelectionFrag.onFileSelected(mFileUri, mFilePath, mFileType);
        }
    }
}