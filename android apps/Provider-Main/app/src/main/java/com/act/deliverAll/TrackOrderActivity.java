package com.act.deliverAll;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.location.Location;
import android.media.MediaPlayer;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.SeekBar;

import androidx.appcompat.widget.AppCompatImageView;
import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;

import com.act.NavigationMapActivity;
import com.act.WayBillActivity;
import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.general.PermissionHandlers;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.UpdateDirections;
import com.google.android.gms.maps.CameraUpdate;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.CameraPosition;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.LatLngBounds;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.maps.android.SphericalUtil;
import com.buddyverse.providers.R;
import com.model.deliverAll.liveTaskListDataModel;
import com.model.deliverAll.orderDetailDataModel;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.MarkerAnim;
import com.utils.NavigationSensor;
import com.utils.Utils;
import com.view.MTextView;
import com.view.MyProgressDialog;
import com.view.SelectableRoundedImageView;
import com.view.simpleratingbar.SimpleRatingBar;

import java.io.IOException;
import java.text.DecimalFormat;
import java.text.NumberFormat;
import java.util.HashMap;

public class TrackOrderActivity extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener, NavigationSensor.DeviceAngleChangeListener {


    public Location userLocation;
    public MTextView timeTxt, distanceTxt;
    MTextView titleTxt;
    String iOrderId = "";
    String tripId = "";
    SupportMapFragment map;
    GoogleMap gMap;

    MTextView addressTxt;
    boolean isDestinationAdded = false;
    double destLocLatitude = 0.0;
    double destLocLongitude = 0.0;
    double placeLocLatitude = 0.0;
    double placeLocLongitude = 0.0;
    Marker placeLocMarker = null;


    String required_str = "";
    String invalid_str = "";
    Menu menu;
    UpdateDirections updateDirections;
    Marker driverMarker;
    Marker destMapMarker;
    boolean isnotification = false;
    InternetConnection intCheck;
    // Gps Dialoge inside view
    MarkerAnim MarkerAnim;
    boolean isCurrentLocationFocused = false;

    private String selectedType = "";
    private String vPhoneNo = "";
    private String vName = "";
    private HashMap<String, String> data_trip;
    private LatLng sourceLatLng;
    private LatLng destLatLng;
    Polyline route_polyLine;
    RelativeLayout wayBillImgView;
    MTextView pickupNameTxt;
    SimpleRatingBar ratingBar;
    AppCompatImageView callArea, chatArea, navigateAreaUP;
    liveTaskListDataModel currentTaskData;
    orderDetailDataModel currentTaskData1;
    int icon;
    String address;
    String RestaurantLongitude = "", RestaurantLattitude = "", RestaurantAddress = "", RestaurantName = "", RestaurantNumber = "";
    String UserLongitude = "", UserLattitude = "", UserAddress = "", UserName = "", UserNumber = "";
    MTextView timeTxtplay;
    MTextView textVoiceinstruction, voiceTitle;
    ImageView playBtn;
    SelectableRoundedImageView storeImg;
    SeekBar seekbar;
    LinearLayout Playarea;
    RelativeLayout playTitleArea;
    String voiceDirectionFileUrl = "";
    boolean wasPlaying = false;
    MediaPlayer mediaPlayer;
    boolean isPause = false;
    boolean iscomplete = false;
    private boolean intialStage = true;
    AppCompatImageView userLocBtnImgView;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_track_driver_location);
        Toolbar toolbar = findViewById(R.id.toolbar);
        generalFunc.setOverflowButtonColor(toolbar, getResources().getColor(R.color.white));
        Playarea = findViewById(R.id.Playarea);
        playTitleArea = findViewById(R.id.playTitleArea);
        if (getIntent().getBooleanExtra("isAudio", false)) {
            if (getIntent().getStringExtra("voiceDirectionFileUrl") != null && !getIntent().getStringExtra("voiceDirectionFileUrl").equalsIgnoreCase("")) {
                voiceDirectionFileUrl = getIntent().getStringExtra("voiceDirectionFileUrl");
                Playarea.setVisibility(View.VISIBLE);
                playTitleArea.setVisibility(View.VISIBLE);
            }
        }

        playBtn = findViewById(R.id.playBtn);
        userLocBtnImgView = findViewById(R.id.userLocBtnImgView);
        voiceTitle = findViewById(R.id.voiceTitle);
        textVoiceinstruction = findViewById(R.id.textVoiceinstruction);
        textVoiceinstruction.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_INS"));
        voiceTitle.setText(generalFunc.retrieveLangLBl("", "LBL_VOICE_DIRECTION_TXT"));
        seekbar = findViewById(R.id.seekbar);
        timeTxtplay = findViewById(R.id.timeTxtplay);
        storeImg = findViewById(R.id.storeImg);
        addToClickHandler(userLocBtnImgView);
        userLocBtnImgView.setVisibility(View.GONE);

        String image_url;
        if (getIntent().getBooleanExtra("isStore", false)) {
            image_url = CommonUtilities.COMPANY_PHOTO_PATH + getIntent().getStringExtra("callid") + "/" + getIntent().getStringExtra("vImage");
        } else {
            image_url = CommonUtilities.USER_PHOTO_PATH + getIntent().getStringExtra("callid") + "/" + getIntent().getStringExtra("vImage");
        }
        int size = (int) getResources().getDimension(R.dimen._30sdp);

        new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(this, image_url, size, size)), storeImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();


        playBtn.setOnClickListener(view -> {
            if (mediaPlayer != null && mediaPlayer.isPlaying()) {
                isPause = true;
                pauseMediaPlayer();
                playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_play_arrow_24));
                return;
            }

            if (isPause) {
                mediaPlayer.start();
                pauseplay();
                playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_pause_24));
                isPause = false;

            } else if (!wasPlaying) {

                if (intialStage) {
                    playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_pause_24));
                    seekbar.setProgress(0);
                    new Player()
                            .execute(voiceDirectionFileUrl);
                } else {
                    playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_pause_24));
                    try {
                        mediaPlayer.prepare();
                    } catch (IOException e) {
                    }
                    mediaPlayer.start();
                    pauseplay();
                }


            }
            wasPlaying = false;


            seekbar.setOnSeekBarChangeListener(new SeekBar.OnSeekBarChangeListener() {
                @Override
                public void onStartTrackingTouch(SeekBar seekBar) {


                }

                @Override
                public void onProgressChanged(SeekBar seekBar, int progress, boolean fromTouch) {

                    int x = (int) Math.ceil(progress / 1000f);
                    if (x < 10) {
                        timeTxtplay.setText("00:0" + x);
                    } else if (x >= 60) {

                        long minutes = x / 60;
                        long seconds = x % 60;
                        NumberFormat f = new DecimalFormat("00");
                        timeTxtplay.setText(f.format(minutes) + ":" + f.format(seconds));
                    } else {
                        timeTxtplay.setText("00:" + x);
                    }


                    if (progress > 0 && mediaPlayer != null && !mediaPlayer.isPlaying()) {
                        if (!isPause || iscomplete) {
                            iscomplete = false;
                            playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_play_arrow_24));
                            seekBar.setProgress(0);
                        }


                    }
                    if (mediaPlayer != null && progress == mediaPlayer.getDuration()) {
                        playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_play_arrow_24));
                        seekBar.setProgress(0);
                        isPause = true;


                    }

                }

                @Override
                public void onStopTrackingTouch(SeekBar seekBar) {
                    if (mediaPlayer != null && mediaPlayer.isPlaying()) {
                        mediaPlayer.seekTo(seekBar.getProgress());
                    }
                }
            });

        });

        if (getIntent().getSerializableExtra("currentTaskData") instanceof liveTaskListDataModel) {
            currentTaskData = (liveTaskListDataModel) getIntent().getSerializableExtra("currentTaskData");
        } else {
            currentTaskData1 = (orderDetailDataModel) getIntent().getSerializableExtra("currentTaskData");
        }


        RestaurantLongitude = (currentTaskData != null ? currentTaskData.getRestaurantLongitude() : currentTaskData1.getRestaurantLongitude());
        UserLongitude = (currentTaskData != null ? currentTaskData.getUserLongitude() : currentTaskData1.getUserLongitude());
        UserLattitude = (currentTaskData != null ? currentTaskData.getUserLattitude() : currentTaskData1.getUserLatitude());
        RestaurantLattitude = (currentTaskData != null ? currentTaskData.getRestaurantLattitude() : currentTaskData1.getRestaurantLattitude());
        UserAddress = (currentTaskData != null ? currentTaskData.getUserAddress() : currentTaskData1.getUserAddress());
        RestaurantAddress = (currentTaskData != null ? currentTaskData.getRestaurantAddress() : currentTaskData1.getRestaurantAddress());
        RestaurantName = (currentTaskData != null ? currentTaskData.getRestaurantName() : currentTaskData1.getRestaurantName());
        UserName = (currentTaskData != null ? currentTaskData.getUserName() : currentTaskData1.getUserName());
        UserNumber = (currentTaskData != null ? currentTaskData.getUserNumber() : currentTaskData1.getUserPhone());
        RestaurantNumber = (currentTaskData != null ? currentTaskData.getRestaurantNumber() : currentTaskData1.getRestaurantNumber());

        Toolbar mToolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);

        MarkerAnim = new MarkerAnim();
        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

        MarkerAnim = new MarkerAnim();


        isnotification = getIntent().getBooleanExtra("isnotification", isnotification);

        intCheck = new InternetConnection(getActContext());

        PermissionHandlers.getInstance().initiatePermissionHandler();
        initView();
        setLabels();
        setData();

        map.getMapAsync(this);


        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) titleTxt.getLayoutParams();
        params.setMargins(Utils.dipToPixels(getActContext(), 50), 0, 0, 0);
        // titleTxt.setLayoutParams(params);

        titleTxt.setPaddingRelative(Utils.dipToPixels(getActContext(), 10), 0, 0, 0);


        if (savedInstanceState != null) {
            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }

        GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("iTripId"));
    }


    class Player extends AsyncTask<String, Void, Boolean> {
        private MyProgressDialog progress;

        @Override
        protected Boolean doInBackground(String... params) {
            // TODO Auto-generated method stub
            Boolean prepared;
            try {
                mediaPlayer = new MediaPlayer();
                mediaPlayer.setDataSource(voiceDirectionFileUrl);

                mediaPlayer.setOnCompletionListener(mp -> {
                    // TODO Auto-generated method stub
                    mediaPlayer.pause();
                    isPause = true;
                    iscomplete = true;

                });
                mediaPlayer.prepare();
                prepared = true;
            } catch (IllegalArgumentException e) {
                // TODO Auto-generated catch block
                Log.d("IllegarArgument", e.getMessage());
                prepared = false;
                Logger.e("Exception", "::" + e.getMessage());
            } catch (SecurityException e) {
                // TODO Auto-generated catch block
                prepared = false;
                Logger.e("Exception", "::" + e.getMessage());
            } catch (IllegalStateException e) {
                // TODO Auto-generated catch block
                prepared = false;
                Logger.e("Exception", "::" + e.getMessage());
            } catch (IOException e) {
                // TODO Auto-generated catch block
                prepared = false;
                Logger.e("Exception", "::" + e.getMessage());
            }
            return prepared;
        }

        @Override
        protected void onPostExecute(Boolean result) {
            // TODO Auto-generated method stub
            super.onPostExecute(result);
            try {
                progress.close();
            } catch (Exception e) {

            }
            Log.d("Prepared", "//" + result);
            play();

            intialStage = false;
        }

        public Player() {
            progress = new MyProgressDialog(getActContext(), false, generalFunc.retrieveLangLBl("Loading", "LBL_LOADING_TXT"));
        }

        @Override
        protected void onPreExecute() {
            // TODO Auto-generated method stub
            super.onPreExecute();
            //   this.progress.setMessage("Buffering...");
            this.progress.show();

        }
    }

    private void clearMediaPlayer() {
        if (mediaPlayer != null) {
            mediaPlayer.stop();
            mediaPlayer.release();
            mediaPlayer = null;
        }
    }

    private void pauseplay() {
        if (mediaPlayer != null) {
            seekbar.setMax(mediaPlayer.getDuration());
            new Thread(this::run).start();
        }
    }

    private void play() {
        try {
            Log.d("wasPlaying", "::00::");
            mediaPlayer.start();
            seekbar.setMax(mediaPlayer.getDuration());
            new Thread(this::run).start();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private void run() {

        int currentPosition = mediaPlayer.getCurrentPosition();
        int total = mediaPlayer.getDuration();


        while (mediaPlayer != null && mediaPlayer.isPlaying() && currentPosition < total) {
            try {
                Thread.sleep(1000);
                currentPosition = mediaPlayer.getCurrentPosition();
            } catch (InterruptedException e) {
                return;
            } catch (Exception e) {
                return;
            }


            seekbar.setProgress(currentPosition);

        }
    }

    private void initView() {
        //gps view declaration end
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        addressTxt = (MTextView) findViewById(R.id.addressTxt);
        map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);
        timeTxt = (MTextView) findViewById(R.id.timeTxt);
        distanceTxt = (MTextView) findViewById(R.id.distanceTxt);
        ratingBar = (SimpleRatingBar) findViewById(R.id.ratingBar);
        (findViewById(R.id.backImgView)).setVisibility(View.VISIBLE);
        addToClickHandler((findViewById(R.id.backImgView)));

        pickupNameTxt = (MTextView) findViewById(R.id.nameTxt);

        callArea = (AppCompatImageView) findViewById(R.id.callArea);
//        callArea.setBackground(getRoundBG("#3cca59"));
        addToClickHandler(callArea);

        chatArea = (AppCompatImageView) findViewById(R.id.chatArea);
//        chatArea.setBackground(getRoundBG("#027bff"));
        addToClickHandler(chatArea);

        navigateAreaUP = (AppCompatImageView) findViewById(R.id.navigateAreaUP);
//        navigateAreaUP.setBackground(getRoundBG("#ffa60a"));
        addToClickHandler(navigateAreaUP);

        wayBillImgView = (RelativeLayout) findViewById(R.id.wayBillImgView);
        addToClickHandler(wayBillImgView);
    }

    public void setTimetext(String distance, String time) {
        try {
            String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

            timeTxt.setVisibility(View.VISIBLE);
            distanceTxt.setVisibility(View.VISIBLE);

            if (userProfileJson != null && !generalFunc.getJsonValue("eUnit", userProfileJson).equalsIgnoreCase("KMs")) {
                distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " ");
                timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            } else {
                distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " ");
                timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
            }

        } catch (Exception e) {

        }
    }

    private void handleNoLocationDial() {
        if (generalFunc.isLocationEnabled()) {
            resetData();
        }
    }

    private void resetData() {
        if (intCheck.isNetworkConnected() && intCheck.check_int() && addressTxt.getText().equals(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"))) {
            setData();
        }

        if (!isCurrentLocationFocused) {
            setData();
            checkUserLocation();
        } else {
            checkUserLocation();
        }

        if (gMap == null && map != null && intCheck.isNetworkConnected() && intCheck.check_int())
            map.getMapAsync(this);
    }

    private void checkUserLocation() {

        if (generalFunc.isLocationEnabled() && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0)) {

            showprogress();

        } else {

            hideprogress();
        }
    }

    private void showprogress() {
        isCurrentLocationFocused = false;
        findViewById(R.id.errorLocArea).setVisibility(View.VISIBLE);
        findViewById(R.id.mProgressBar).setVisibility(View.VISIBLE);
        ((ProgressBar) findViewById(R.id.mProgressBar)).setIndeterminate(true);
        ((ProgressBar) findViewById(R.id.mProgressBar)).getIndeterminateDrawable().setColorFilter(
                getActContext().getResources().getColor(R.color.appThemeColor_1), android.graphics.PorterDuff.Mode.SRC_IN);
    }

    private void hideprogress() {
        findViewById(R.id.errorLocArea).setVisibility(View.GONE);
        if (findViewById(R.id.mProgressBar) != null) {
            findViewById(R.id.mProgressBar).setVisibility(View.GONE);
        }
    }

    private void setLabels() {
        titleTxt.setText(generalFunc.retrieveLangLBl("En Route", "LBL_EN_ROUTE_TXT"));
        timeTxt.setText("--" + generalFunc.retrieveLangLBl("to reach", "LBL_REACH_TXT"));
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        invalid_str = generalFunc.retrieveLangLBl("Invalid value", "LBL_DIGIT_REQUIRE");

    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;

        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(true);
        } else {
            getMap().setMyLocationEnabled(false);
        }
        getMap().setOnCameraMoveStartedListener(reason -> {

            if (reason == 1) {
                userLocBtnImgView.setVisibility(View.VISIBLE);
            }
        });

        if (generalFunc.isRTLmode()) {
            getMap().setPadding(13, 0, 0, 0);
        } else {
            getMap().setPadding(13, 0, 150, 0);
        }


        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);


        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        checkUserLocation();

        if (userLocation != null && route_polyLine == null) {
            if (updateDirections != null) {
                Location destLoc = new Location("gps");
                destLoc.setLatitude(destLocLatitude);
                destLoc.setLongitude(destLocLongitude);
                updateDirections.changeUserLocation(destLoc);
            }
        }

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("iTripId"));
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
    }

    private void setMapMarkerIcons() {

        if (getMap() == null) {
            return;
        }

        if (placeLocMarker != null) {
            placeLocMarker.remove();
        }

        if (destMapMarker != null) {
            destMapMarker.remove();
        }

        boolean isRest = selectedType.equalsIgnoreCase("trackRest");

        String address1 = isRest ? RestaurantAddress : UserAddress;
        int icon1 = isRest ? R.mipmap.ic_track_restaurant : R.mipmap.ic_track_user;

        destLatLng = new LatLng(destLocLatitude, destLocLongitude);
        sourceLatLng = new LatLng(placeLocLatitude, placeLocLongitude);


        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View placeMarkerView = inflater.inflate(R.layout.deliverall_marker_view, null);

        ImageView placePinImgView = (ImageView) placeMarkerView.findViewById(R.id.pinImgView);
        placePinImgView.setImageResource(icon1);
        MTextView placeMarkerTxtView = (MTextView) placeMarkerView.findViewById(R.id.addressTxtView);
        placeMarkerTxtView.setText(address1);


        View userMarkerView = inflater.inflate(R.layout.deliverall_marker_view, null);
        ImageView userPinImgView = (ImageView) userMarkerView.findViewById(R.id.pinImgView);
        userPinImgView.setImageResource(icon);

        MTextView userMarkerTxtView = (MTextView) userMarkerView.findViewById(R.id.addressTxtView);
        userMarkerTxtView.setText(address);

        userMarkerTxtView.setVisibility(View.GONE);

        Marker sourceMarker = gMap.addMarker(new MarkerOptions().position(sourceLatLng).icon(BitmapDescriptorFactory.fromBitmap(Utils.getBitmapFromView(placeMarkerView))));
        placeLocMarker = sourceMarker;


        Marker destMarker = gMap.addMarker(new MarkerOptions().position(destLatLng).icon(BitmapDescriptorFactory.fromBitmap(Utils.getBitmapFromView(userMarkerView))));
        destMapMarker = destMarker;

        LatLngBounds.Builder builder = new LatLngBounds.Builder();
        builder.include(sourceMarker.getPosition());
        if (destMarker != null) {
            builder.include(destMarker.getPosition());
        }

        gMap.animateCamera(getCameraUpdateFactory(builder));

    }

    private CameraUpdate getCameraUpdateFactory(LatLngBounds.Builder builder) {
        LatLngBounds bounds = builder.build();
        LatLng center = bounds.getCenter();
        LatLng northEast = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
        LatLng southWest = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));
        builder.include(southWest);
        builder.include(northEast);

        DisplayMetrics metrics = new DisplayMetrics();
        getWindowManager().getDefaultDisplay().getMetrics(metrics);
        int width = metrics.widthPixels;
        int height = metrics.heightPixels;
        int padding = (int) (width * 0.32); // offset from edges of the map 10% of screen

        return CameraUpdateFactory.newLatLngBounds(bounds, width, height, padding);
    }

    private GoogleMap getMap() {
        return this.gMap;
    }

    private void setData() {


        this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        iOrderId = data_trip.get("iOrderId");
        tripId = data_trip.get("iTripId");


        if (getIntent().hasExtra("type")) {
            selectedType = getIntent().getStringExtra("type");

            boolean isRest = selectedType.equalsIgnoreCase("trackRest");

            double vLattitude = GeneralFunctions.parseDoubleValue(0.0, isRest ? UserLattitude : RestaurantLattitude);
            double vLongitude = GeneralFunctions.parseDoubleValue(0.0, isRest ? UserLongitude : RestaurantLongitude);
            address = !isRest ? UserAddress : RestaurantAddress;
            vName = !isRest ? UserName : RestaurantName;
            vPhoneNo = !isRest ? UserNumber : RestaurantNumber;
            icon = isRest ? R.mipmap.ic_track_user : R.mipmap.ic_track_restaurant;

            pickupNameTxt.setText(vName);
            pickupNameTxt.setVisibility(View.VISIBLE);

            placeLocLatitude = GeneralFunctions.parseDoubleValue(0.0, isRest ? RestaurantLattitude : UserLattitude);
            placeLocLongitude = GeneralFunctions.parseDoubleValue(0.0, isRest ? RestaurantLongitude : UserLongitude);

            if (vLattitude != 0 && vLongitude != 0) {
                setDestinationPoint("" + vLattitude, "" + vLongitude, address, true);
            }
        }

        setMapMarkerIcons();

        if (currentTaskData != null) {
            if (currentTaskData.geteBuyAnyService().equalsIgnoreCase("Yes")) {
                callArea.setVisibility(View.GONE);
                chatArea.setVisibility(View.GONE);
            }
        }
    }

    @Override
    public void onLocationUpdate(Location location) {

        if (location == null) {
            return;
        }

        Logger.d("Api", "User's Current location" + location);

        if (obj_userProfile != null && generalFunc.getJsonValueStr("ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP", obj_userProfile).equalsIgnoreCase("Yes")) {
            if (this.userLocation == null || !isCurrentLocationFocused) {
                isCurrentLocationFocused = true;
                this.userLocation = location;
                //   CameraPosition cameraPosition = cameraForUserPosition(true);
                // getMap().moveCamera(CameraUpdateFactory.newCameraPosition(cameraPosition));
                getMap().moveCamera(generalFunc.getCameraPosition(location, gMap));
            } else {
                isCurrentLocationFocused = true;
                //   CameraPosition cameraPosition = cameraForUserPosition(false);
                //  getMap().animateCamera(CameraUpdateFactory.newCameraPosition(cameraPosition), 1500, null);
                getMap().animateCamera(generalFunc.getCameraPosition(location, gMap), 1500, null);
            }
        } else {

            try {
                this.userLocation = location;
                LatLngBounds.Builder builder = new LatLngBounds.Builder();
                builder.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
                builder.include(new LatLng(placeLocLatitude, placeLocLongitude));
                builder.include(new LatLng(destLocLatitude, destLocLongitude));

                getMap().animateCamera(getCameraUpdateFactory(builder));
            } catch (Exception e) {
                Logger.d("Exception", "::" + e.toString());

            }

        }

        updateDriverMarker(new LatLng(location.getLatitude(), location.getLongitude()));

        this.userLocation = location;
        checkUserLocation();

        if (updateDirections == null) {
            Location destLoc = new Location("gps");
            destLoc.setLatitude(placeLocLatitude);
            destLoc.setLongitude(placeLocLongitude);
            updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
            updateDirections.setMarkers(placeLocMarker, destMapMarker);
            updateDirections.isDeliverAll(selectedType.equalsIgnoreCase("trackRest"));
            updateDirections.scheduleDirectionUpdate();

        }

        if (updateDirections != null) {
            updateDirections.changeUserLocation(location);
            updateDirections.setMarkers(placeLocMarker, destMapMarker);
        }

        if (sourceLatLng == null || destMapMarker == null) {
            setMapMarkerIcons();
        }

    }

    private void updateDriverMarker(final LatLng newLocation) {

        if (driverMarker == null) {

            int iconId = R.mipmap.car_driver_main;

            MarkerOptions markerOptions_driver = new MarkerOptions();
            markerOptions_driver.position(newLocation);
            markerOptions_driver.icon(BitmapDescriptorFactory.fromResource(iconId)).anchor(0.5f, 0.5f).flat(true);
            driverMarker = gMap.addMarker(markerOptions_driver);
            driverMarker.setTitle(generalFunc.getMemberId());
        }


        if (this.userLocation != null && newLocation != null) {
            LatLng currentLatLng = new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude());
            float rotation;

            if (MarkerAnim.currentLng != null) {
                rotation = (float) MarkerAnim.bearingBetweenLocations(MarkerAnim.currentLng, newLocation);
            } else {
                rotation = (float) MarkerAnim.bearingBetweenLocations(currentLatLng, newLocation);
            }

            if (driverMarker != null) {
                driverMarker.setTitle(generalFunc.getMemberId());
            }

            HashMap<String, String> previousItemOfMarker = MarkerAnim.getLastLocationDataOfMarker(driverMarker);

            HashMap<String, String> data_map = new HashMap<>();
            data_map.put("vLatitude", "" + newLocation.latitude);
            data_map.put("vLongitude", "" + newLocation.longitude);
            data_map.put("iDriverId", "" + generalFunc.getMemberId());
            data_map.put("RotationAngle", "" + rotation);
            data_map.put("LocTime", "" + System.currentTimeMillis());

            Location location = new Location("marker");
            location.setLatitude(newLocation.latitude);
            location.setLongitude(newLocation.longitude);


            if (MarkerAnim.toPositionLat.get("" + newLocation.latitude) == null && MarkerAnim.toPositionLat.get("" + newLocation.longitude) == null) {
                if (previousItemOfMarker.get("LocTime") != null && !previousItemOfMarker.get("LocTime").equals("")) {

                    long previousLocTime = GeneralFunctions.parseLongValue(0, previousItemOfMarker.get("LocTime"));
                    long newLocTime = GeneralFunctions.parseLongValue(0, data_map.get("LocTime"));

                    if (previousLocTime != 0 && newLocTime != 0) {

                        if ((newLocTime - previousLocTime) > 0 && !MarkerAnim.driverMarkerAnimFinished) {
                            MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                        } else if ((newLocTime - previousLocTime) > 0) {
                            MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                        }

                    } else if ((previousLocTime == 0 || newLocTime == 0) && !MarkerAnim.driverMarkerAnimFinished) {
                        MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                    } else {
                        MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                    }
                } else if (!MarkerAnim.driverMarkerAnimFinished) {
                    MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                } else {
                    MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 1200, iOrderId, data_map.get("LocTime"));
                }
            }
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.REQUEST_CODE_GPS_ON) {
            handleNoLocationDial();
        }
    }

    public boolean onCreateOptionsMenu(Menu menu) {
        this.menu = menu;

        MenuInflater menuInflater = getMenuInflater();
        menuInflater.inflate(R.menu.trip_accept_menu, menu);

        menu.findItem(R.id.menu_message).setTitle(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_ACTIVE_TRIP"));

        menu.findItem(R.id.menu_specialInstruction).setTitle(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"));

        menu.findItem(R.id.menu_sos).setTitle(generalFunc.retrieveLangLBl("Emergency or SOS", "LBL_EMERGENCY_SOS_TXT"));
        //menu.findItem(R.id.menu_waybill_trip).setTitle(generalFunc.retrieveLangLBl("Way Bill", "LBL_MENU_WAY_BILL"));

        menu.findItem(R.id.menu_call).setTitle(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
        if (currentTaskData != null) {
            if (currentTaskData.geteBuyAnyService().equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.menu_call).setVisible(false);
                menu.findItem(R.id.menu_message).setVisible(false);
            } else {
                menu.findItem(R.id.menu_call).setVisible(true);
                menu.findItem(R.id.menu_message).setVisible(true);
            }
        }
        if (callArea.getVisibility() == View.VISIBLE) {
            menu.findItem(R.id.menu_call).setVisible(false);
            menu.findItem(R.id.menu_message).setVisible(false);
        }

        menu.findItem(R.id.menu_passenger_detail).setVisible(false);
        menu.findItem(R.id.menu_sos).setVisible(false);
        menu.findItem(R.id.menu_cancel_trip).setVisible(false);
        menu.findItem(R.id.menu_specialInstruction).setVisible(false);
        if (generalFunc.getJsonValue("WAYBILL_ENABLE", obj_userProfile) != null && generalFunc.getJsonValueStr("WAYBILL_ENABLE", obj_userProfile).equalsIgnoreCase("yes")) {
            menu.findItem(R.id.menu_waybill_trip).setTitle(generalFunc.retrieveLangLBl("Way Bill", "LBL_MENU_WAY_BILL")).setVisible(true);
        } else {
            menu.findItem(R.id.menu_waybill_trip).setTitle(generalFunc.retrieveLangLBl("Way Bill", "LBL_MENU_WAY_BILL")).setVisible(false);
        }


        Utils.setMenuTextColor(menu.findItem(R.id.menu_passenger_detail), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_cancel_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_waybill_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_sos), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_call), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_message), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_specialInstruction), getResources().getColor(R.color.black));
        return true;
    }

    private void pauseMediaPlayer() {
        if (mediaPlayer != null) {
            isPause = true;
            mediaPlayer.pause();
            //   audioRecording.mMediaPlayer.release();
            playBtn.setImageDrawable(ContextCompat.getDrawable(TrackOrderActivity.this, R.drawable.ic_baseline_play_arrow_24));

        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        pauseMediaPlayer();
        int itemId = item.getItemId();
        if (itemId == R.id.menu_passenger_detail) {
            return true;
        } else if (itemId == R.id.menu_cancel_trip) {
            return true;
        } else if (itemId == R.id.menu_waybill_trip) {
            Bundle bn4 = new Bundle();
            bn4.putSerializable("iOrderId", iOrderId);
            bn4.putSerializable("tripId", tripId);
            bn4.putString("eSystem", "yes");
            new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn4);
            return true;
        } else if (itemId == R.id.menu_sos) {
            return true;
        } else if (itemId == R.id.menu_call) {
            callArea.performClick();
            return true;
        } else if (itemId == R.id.menu_message) {
            chatArea.performClick();
            return true;
        } else if (itemId == R.id.menu_specialInstruction) {
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private Context getActContext() {
        return TrackOrderActivity.this; // Must be context of activity not application
    }

    private void setDestinationPoint(String latitude, String longitude, String address, boolean isDestinationAdded) {
        double dest_lat = GeneralFunctions.parseDoubleValue(0.0, latitude);
        double dest_lon = GeneralFunctions.parseDoubleValue(0.0, longitude);

        (findViewById(R.id.navigationViewArea)).setVisibility(View.VISIBLE);

        if (address.equals("")) {
            addressTxt.setText(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"));
            GetAddressFromLocation getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
            getAddressFromLocation.setLocation(dest_lat, dest_lon);
            getAddressFromLocation.setAddressList((address1, latitude1, longitude1, geocodeobject) -> addressTxt.setText(address1));
            getAddressFromLocation.execute();
        } else {
            addressTxt.setText(address);
        }

        this.isDestinationAdded = isDestinationAdded;
        this.destLocLatitude = dest_lat;
        this.destLocLongitude = dest_lon;
        setMapMarkerIcons();
    }

    @Override
    public void onBackPressed() {
        clearMediaPlayer();
        super.onBackPressed();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        pauseMediaPlayer();
        stopProcess();
    }

    private void stopProcess() {
        if (updateDirections != null) {
            updateDirections.releaseTask();
            updateDirections = null;
        }

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.retrieveInstance().stopLocationUpdates(this);
        }
        Utils.runGC();
    }

    @Override
    protected void onResume() {
        super.onResume();

        NavigationSensor.getInstance().configSensor(true);
    }

    @Override
    protected void onPause() {
        super.onPause();
        pauseMediaPlayer();

        NavigationSensor.getInstance().configSensor(false);
    }

    private void openNavigationView(final String dest_lat, final String dest_lon, final String address) {
        Bundle bn = new Bundle();
        bn.putString("dest_lat", dest_lat);
        bn.putString("dest_lon", dest_lon);
        bn.putString("address", address);
        new ActUtils(getActContext()).startActWithData(NavigationMapActivity.class, bn);
    }

    private void openNavigationDialog(final String dest_lat, final String dest_lon) {
        MyCommonDialog.navigationDialog(getActContext(), generalFunc, () -> {
            try {
                String uri = "https://play.google.com/store/apps/details?id=com.google.android.apps.maps";
                if (MyCommonDialog.isPackageInstalled("com.google.android.apps.maps", getActContext().getPackageManager())) {
                    uri = "http://maps.google.com/maps?daddr=" + dest_lat + "," + dest_lon;
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
                } else {
                    commonCustomDialog(uri, R.drawable.ic_google_map,
                            generalFunc.retrieveLangLBl("", "LBL_GOOGLE_MAPS_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_GOOGLE_MAPS"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                }
            } catch (Exception e) {
                generalFunc.showMessage(timeTxt, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
            }
        }, () -> {
            try {
                String uri = "https://play.google.com/store/apps/details?id=com.waze";
                if (MyCommonDialog.isPackageInstalled("com.waze", getActContext().getPackageManager())) {
                    uri = "waze://?ll=" + dest_lat + "," + dest_lon + "&navigate=yes";
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
                } else {
                    commonCustomDialog(uri, R.drawable.ic_waze_map,
                            generalFunc.retrieveLangLBl("", "LBL_WAZE_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_WAZE"),
                            generalFunc.retrieveLangLBl("", "LBL_INSTALL_TXT"),
                            generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                }
            } catch (Exception e) {
                generalFunc.showMessage(timeTxt, generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
            }
        }, () -> {
            if (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_DELIVERALL", obj_userProfile).equalsIgnoreCase("Yes")) {
                openNavigationView(dest_lat, dest_lon, addressTxt.getText().toString().trim());
            } else {
                openNavigationDialog(dest_lat, dest_lon);
            }
        }, generalFunc.getJsonValueStr("GOOGLE_NAV_OPTION", obj_userProfile), obj_userProfile);
    }
    private void commonCustomDialog(String uri, int img, String title, String message, String positiveBtnTxt, String negativeBtnTxt) {
        CustomDialog customDialog = new CustomDialog(getActContext());
        customDialog.setDetails(title, message, positiveBtnTxt, negativeBtnTxt, false, img, false, 1, false);
        customDialog.createDialog();
        customDialog.setNegativeButtonClick(() -> {
        });
        customDialog.setPositiveButtonClick(() -> {
            //
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(uri)));
        });
        customDialog.show();
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        pauseMediaPlayer();
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == navigateAreaUP.getId()) {
            openNavigationDialog("" + placeLocLatitude, "" + placeLocLongitude);

        } else if (i == callArea.getId()) {

            boolean isStore = getIntent().getBooleanExtra("isStore", false);
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(getIntent().getStringExtra("callid"))
                    .setPhoneNumber(vPhoneNo)
                    .setToMemberType(isStore ? Utils.CALLTOSTORE : Utils.CALLTOPASSENGER)
                    .setToMemberName(isStore ? RestaurantName : UserName)
                    .setToMemberImage(getIntent().getStringExtra("vImage"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(tripId)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.OTHER);

        } else if (i == chatArea.getId()) {

            boolean isStore = getIntent().getBooleanExtra("isStore", false);
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(getIntent().getStringExtra("callid"))
                    .setPhoneNumber(vPhoneNo)
                    .setToMemberType(isStore ? Utils.CALLTOSTORE : Utils.CALLTOPASSENGER)
                    .setToMemberName(isStore ? RestaurantName : UserName)
                    .setToMemberImage(getIntent().getStringExtra("vImage"))
                    .setBookingNo(isStore ? currentTaskData.getOrderNumber() : currentTaskData1.getvOrderNo())
                    .setOrderId(isStore ? currentTaskData.getiOrderId() : currentTaskData1.getOrderID())
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .setTripId(tripId)
                    .build();
            CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.CHAT);

        } else if (i == wayBillImgView.getId()) {
            Bundle bn4 = new Bundle();
            bn4.putSerializable("iOrderId", iOrderId);
            bn4.putSerializable("tripId", tripId);
            bn4.putString("eSystem", "yes");
            new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn4);
        } else if (i == userLocBtnImgView.getId()) {
            userLocBtnImgView.setVisibility(View.GONE);
            LatLngBounds.Builder builder = new LatLngBounds.Builder();
            builder.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
            builder.include(new LatLng(placeLocLatitude, placeLocLongitude));
            getMap().animateCamera(getCameraUpdateFactory(builder));
        }
    }

    private GradientDrawable getRoundBG(String color) {

        int strokeWidth = 2;
        int strokeColor = Color.parseColor("#CCCACA");
        int fillColor = Color.parseColor(color);
        GradientDrawable gD = new GradientDrawable();
        gD.setColor(fillColor);
        gD.setShape(GradientDrawable.RECTANGLE);
        gD.setCornerRadius(100);
        gD.setStroke(strokeWidth, strokeColor);
        return gD;
    }

    @Override
    public void onDeviceAngleChanged(float azimuth) {

        if (gMap == null) {
            return;
        }

        getMap().animateCamera(CameraUpdateFactory.newCameraPosition(CameraPosition.builder(getMap().getCameraPosition()).bearing(azimuth).build()));
    }
}
