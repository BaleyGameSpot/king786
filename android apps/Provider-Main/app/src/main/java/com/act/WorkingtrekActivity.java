package com.act;

import static android.view.MotionEvent.ACTION_DOWN;
import static android.view.MotionEvent.ACTION_MOVE;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.GradientDrawable;
import android.location.Location;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.Editable;
import android.text.InputType;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.GestureDetector;
import android.view.Gravity;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.animation.AlphaAnimation;
import android.view.animation.Animation;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.appcompat.widget.Toolbar;
import androidx.core.view.GestureDetectorCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.adapter.files.OnGoingTripDetailAdapter;
import com.dialogs.MyCommonDialog;
import com.dialogs.OpenListView;
import com.fragments.RTMPServiceFragment;
import com.general.PermissionHandler;
import com.general.PermissionHandlers;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.CancelTripDialog;
import com.general.files.CustomDialog;
import com.general.files.FileSelector;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.OpenPassengerDetailDialog;
import com.general.files.RecurringTask;
import com.general.files.SlideButton;
import com.general.files.UpdateDirections;
import com.general.files.UploadProfileImage;
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
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.DesginMaskVerificationBinding;
import com.model.ChatMsgHandler;
import com.mukesh.OtpView;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.MarkerAnim;
import com.utils.MyUtils;
import com.utils.NavigationSensor;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.MyProgressDialog;
import com.view.SelectableRoundedImageView;
import com.view.editBox.MaterialEditText;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

@SuppressLint("all")
public class WorkingtrekActivity extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener, GestureDetector.OnGestureListener,
        GestureDetector.OnDoubleTapListener {

    public Location userLocation;
    public ImageView emeTapImgView;
    public MTextView timeTxt, distanceTxt;
    MTextView titleTxt;
    String tripId = "";
    String eType = "";
    public HashMap<String, String> data_trip;
    SupportMapFragment map;
    GoogleMap gMap;
    MTextView addressTxt;
    boolean isDestinationAdded = false;
    double destLocLatitude = 0.0;
    double destLocLongitude = 0.0;
    Marker destLocMarker = null;
    Polyline route_polyLine;
    LinearLayout timerarea;
    boolean isTripCancelPressed = false;
    boolean isTripStart = false;
    String reason = "";
    String comment = "";
    String REQUEST_TYPE = "";
    String deliveryVerificationCode = "";
    AlertDialog deliveryEndDialog;
    String SITE_TYPE = "";
    String imageType = "";
    String isFrom = "";
    Dialog uploadServicePicAlertBox = null;
    LinearLayout destLocSearchArea;
    RecurringTask timerrequesttask;
    ArrayList<HashMap<String, String>> list;
    ArrayList<HashMap<String, String>> tripDetail;
    HashMap<String, String> tempMap;
    OnGoingTripDetailAdapter onGoingTripDetailAdapter;
    RecyclerView onGoingTripsDetailListRecyclerView;
    SimpleRatingBar ratingBar_ufx;
    SelectableRoundedImageView user_img;
    ArrayList<Double> additonallist = new ArrayList<>();
    String currencetprice = "0.00";
    MTextView userNameTxt, userAddressTxt, progressHinttext, timerHinttext, tollTxtView;
    MTextView txt_TimerHour, txt_TimerMinute, txt_TimerSecond;
    LinearLayout timerlayoutarea;
    RelativeLayout timerlayoutMainarea;
    String required_str = "";
    String invalid_str = "";
    boolean isresume = false;
    int i = 0;
    NestedScrollView scrollview;
    Menu menu;
    boolean isendslide = false;
    UpdateDirections updateDirections;
    Marker driverMarker;
    boolean isnotification = false;
    InternetConnection intCheck;
    private MTextView tvHour, tvMinute, tvSecond, btntimer;
    //# Transit Shoppping System
    private MTextView newtvHour, newtvMinute, newtvSecond, newbtn_timer, RatingTxt;
    LinearLayout holdWaitArea;
    //# Transit Shoppping System

    private String selectedImagePath = "";
    private String safetyselectedImagePath = "";
    private String TripTimeId = "";
    MarkerAnim MarkerAnim;

    boolean isCurrentLocationFocused = false;

    String eConfirmByUser = "No";
    String payableAmount = "";

    String latitude = "";
    String longitirude = "";
    String address = "";
    double tollamount = 0.0;
    String tollcurrancy = "";
    boolean istollIgnore = false;
    AlertDialog tolltax_dialog;

    String eTollConfirmByUser = "";

    String ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP = "";

    AlertDialog alertDialog_surgeConfirm;

    /*Multi Delivery View*/
    LinearLayout deliveryDetailsArea, RatingHArea;
    MTextView pickupTxt;
    MTextView pickupNameTxt, recipientTxt;
    private String iTripDeliveryLocationId;

    AlertDialog dialog_declineOrder;
    String vImage = "";
    String vName = "";
    boolean isPoolRide = false;

    boolean isFirstMapMove = true;
    /*Multistop over*/
    boolean isDropAll = false;
    private RelativeLayout dropAllIconArea;
    Animation anim;
    int currentStopOverPoint;
    int totalStopOverPoint;
    /*Multistop over*/

    String LBL_WAIT = "", LBL_REACH_TXT = "";
    String LBL_CONFIRM_STOPOVER_1 = "", LBL_CONFIRM_STOPOVER_2 = "";
    String LBL_BTN_SLIDE_END_TRIP_TXT = "";
    String LBL_BTN_SLIDE_INTERCITY_MARKED_AS_REACH = "";
    String LBL_BTN_SLIDE_INTERCITY_START_TO_RETURN = "";
    String APP_TYPE;
    private boolean eFly;

    SlideButton startTripSlideButton, endTripSlideButton;
    RelativeLayout wayBillImgView;
    ImageView dropAllAreaUP, dropCancel;
    AppCompatImageView callArea, chatArea, navigateAreaUP, deliveryInfoView, userLocBtnImgView;

    ProgressBar ufx_loading;
    RelativeLayout buttonlayouts;


    AlertDialog uploadImgAlertDialog;
    ImageView clearImg;
    LinearLayout maskVerificationUploadImgArea;
    LinearLayout mCardView;
    private boolean isFaceMaskVerification = false;
    private Dialog dialog_verify_via_otp;
    boolean isOtpVerified = false;
    boolean isOtpVerificationDenied = false;

    boolean isVideoCall = false;
    RelativeLayout videoCallArea;
    MTextView videoIntroTxt;
    RelativeLayout navigationViewArea;
    boolean isInterCity, isInterCityRoundTrip;
    private String eInterCityButtonBgColor = "";
    private String ufxtripstatus = "";

    //
    @Nullable
    private RTMPServiceFragment rtmpFrag;
    private GestureDetectorCompat gestureDetector;
    private RelativeLayout rtmpFragContainer;
    private DisplayMetrics metrics;
    private int oldPositionX = 0, oldPositionY = 0;
    private boolean isExpandedVideoView = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_active_trip);
        if (savedInstanceState != null) {

            // Restore value of members from saved state
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");
            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
                return;
            }
        }
        this.data_trip = (HashMap<String, String>) getIntent().getSerializableExtra("TRIP_DATA");
        if (data_trip == null) {
            if (!MyApp.getInstance().isGetDetailCall) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_TRY_AGAIN_TXT"), "", generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), i -> finish());
            }
            return;
        }
        Toolbar toolbar = findViewById(R.id.toolbar);
        generalFunc.setOverflowButtonColor(toolbar, getResources().getColor(R.color.white));

        //INTERCITY Values
        isInterCity = Utils.checkText(data_trip.get("isInterCity")) && data_trip.get("isInterCity").equalsIgnoreCase("yes");
        isInterCityRoundTrip = Utils.checkText(data_trip.get("isInterCityRoundTrip")) && data_trip.get("isInterCityRoundTrip").equalsIgnoreCase("yes");

        ufxtripstatus = data_trip.get("vTripStatus");


        MarkerAnim = new MarkerAnim();
        LBL_WAIT = generalFunc.retrieveLangLBl("Wait", "LBL_START_WAITING_TIMER");
        MarkerAnim = new MarkerAnim();
        APP_TYPE = generalFunc.retrieveValue(Utils.APP_TYPE);

        //MyApp.getInstance().setOfflineState();

        ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP = generalFunc.getJsonValueStr("ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP", obj_userProfile);
        gestureDetector = new GestureDetectorCompat(this, this);
        gestureDetector.setOnDoubleTapListener(this);


        defaultAddtionalprice();

        intCheck = new InternetConnection(getActContext());


        isnotification = getIntent().getBooleanExtra("isnotification", isnotification);
        currentStopOverPoint = GeneralFunctions.parseIntegerValue(0, data_trip.get("currentStopOverPoint"));
        totalStopOverPoint = GeneralFunctions.parseIntegerValue(0, data_trip.get("totalStopOverPoint"));
        distanceTxt = (MTextView) findViewById(R.id.distanceTxt);
        eFly = data_trip.get("eFly").equalsIgnoreCase("Yes");

        buttonlayouts = (RelativeLayout) findViewById(R.id.buttonlayouts);
        scrollview = (NestedScrollView) findViewById(R.id.scrollview);
        ufx_loading = (ProgressBar) findViewById(R.id.ufx_loading);
        titleTxt = (MTextView) findViewById(R.id.titleTxt);
        navigationViewArea = (RelativeLayout) findViewById(R.id.navigationViewArea);
        addToClickHandler(navigationViewArea);
        onGoingTripsDetailListRecyclerView = (RecyclerView) findViewById(R.id.onGoingTripsDetailListRecyclerView);
        userNameTxt = (MTextView) findViewById(R.id.userNameTxt);
        userAddressTxt = (MTextView) findViewById(R.id.userAddressTxt);
        ratingBar_ufx = (SimpleRatingBar) findViewById(R.id.ratingBar_ufx);
        tvHour = (MTextView) findViewById(R.id.txtTimerHour);
        tvMinute = (MTextView) findViewById(R.id.txtTimerMinute);
        tvSecond = (MTextView) findViewById(R.id.txtTimerSecond);
        RatingTxt = (MTextView) findViewById(R.id.RatingTxt);

        //# Transit Shoppping System
        newtvHour = (MTextView) findViewById(R.id.newtxtTimerHour);
        newtvMinute = (MTextView) findViewById(R.id.newtxtTimerMinute);
        newtvSecond = (MTextView) findViewById(R.id.newtxtTimerSecond);
        newbtn_timer = (MTextView) findViewById(R.id.newbtn_timer);
        newbtn_timer.setOnClickListener(new setOnClickAct());
        //#Transit Shoppping System
        addressTxt = (MTextView) findViewById(R.id.addressTxt);
        progressHinttext = (MTextView) findViewById(R.id.progressHinttext);
        timerHinttext = (MTextView) findViewById(R.id.timerHinttext);
        btntimer = (MTextView) findViewById(R.id.btn_timer);
        btntimer.setOnClickListener(new setOnClickAct());
        holdWaitArea = (LinearLayout) findViewById(R.id.holdWaitArea);
        RatingHArea = (LinearLayout) findViewById(R.id.RatingHArea);

        map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);

        timerarea = (LinearLayout) findViewById(R.id.timerarea);
        timerlayoutarea = (LinearLayout) findViewById(R.id.timerlayoutarea);
        timerlayoutMainarea = (RelativeLayout) findViewById(R.id.timerlayoutMainarea);

        destLocSearchArea = (LinearLayout) findViewById(R.id.destLocSearchArea);
        timeTxt = (MTextView) findViewById(R.id.timeTxt);
        timeTxt.setVisibility(View.GONE);

        txt_TimerHour = (MTextView) findViewById(R.id.txt_TimerHour);
        txt_TimerMinute = (MTextView) findViewById(R.id.txt_TimerMinute);
        txt_TimerSecond = (MTextView) findViewById(R.id.txt_TimerSecond);
        tollTxtView = (MTextView) findViewById(R.id.tollTxtView);

        user_img = (SelectableRoundedImageView) findViewById(R.id.user_img);

        emeTapImgView = (ImageView) findViewById(R.id.emeTapImgView);
        emeTapImgView.setOnClickListener(new setOnClickList());

        isVideoCall = data_trip.get("isVideoCall") != null && data_trip.get("isVideoCall").equalsIgnoreCase("yes");
        if (isVideoCall) {
            if (menu != null) {
                onCreateOptionsMenu(menu);
            }
            setVideoConsult();
        }

        (findViewById(R.id.backImgView)).setVisibility(View.GONE);

        callArea = (AppCompatImageView) findViewById(R.id.callArea);
        chatArea = (AppCompatImageView) findViewById(R.id.chatArea);
        dropCancel = (ImageView) findViewById(R.id.dropCancel);
        navigateAreaUP = (AppCompatImageView) findViewById(R.id.navigateAreaUP);
        dropAllAreaUP = (ImageView) findViewById(R.id.dropAllAreaUP);
        wayBillImgView = (RelativeLayout) findViewById(R.id.wayBillImgView);
        deliveryInfoView = (AppCompatImageView) findViewById(R.id.deliveryInfoView);
        userLocBtnImgView = (AppCompatImageView) findViewById(R.id.userLocBtnImgView);


//        callArea.setBackground(getRoundBG("#3cca59"));
//        chatArea.setBackground(getRoundBG("#027bff"));
//        navigateAreaUP.setBackground(getRoundBG("#ffa60a"));

//        dropCancel.setBackground(getRoundBG("#d20000"));
//        dropAllAreaUP.setBackground(getRoundBG("#d20000"));

        callArea.setOnClickListener(new setOnClickAct());
        chatArea.setOnClickListener(new setOnClickAct());
        userLocBtnImgView.setOnClickListener(new setOnClickAct());

        // Multi delivery View
        deliveryDetailsArea = (LinearLayout) findViewById(R.id.deliveryDetailsArea);
        pickupTxt = (MTextView) findViewById(R.id.pickupTxt);
        pickupNameTxt = (MTextView) findViewById(R.id.pickupNameTxt);
        recipientTxt = (MTextView) findViewById(R.id.recipientTxt);
        generalFunc.storeData(Utils.IsTripStarted, "No");
        generalFunc.storeData("PROVIDER_STATUS_MODE", "arrived");
        metrics = getResources().getDisplayMetrics();

        currencetprice = data_trip.get("fVisitFee");

        new CreateRoundedView(getResources().getColor(android.R.color.transparent), Utils.dipToPixels(getActContext(), 15), 0, Color.parseColor("#00000000"), user_img);

        /*Multitop over*/
        dropAllIconArea = (RelativeLayout) findViewById(R.id.dropAllArea);
        dropAllIconArea.setOnClickListener(new setOnClickList());
        /*Multistop over*/


        PermissionHandlers.getInstance().initiatePermissionHandler();
        setTripButton();
        setLabels();
        setData();


        String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
        if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            if (isendslide || getPasggerTripStatus().equals("On Going Trip")) {
                wayBillImgView.setVisibility(View.VISIBLE);
            } else {
                Toolbar mToolbar = (Toolbar) findViewById(R.id.toolbar);
                setSupportActionBar(mToolbar);
            }
            deliveryInfoView.setVisibility(View.VISIBLE);
            wayBillImgView.setOnClickListener(v -> {
                Bundle bn4 = new Bundle();
                bn4.putSerializable("data_trip", data_trip);
                new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn4);
            });

            chatArea.setVisibility(View.GONE);

        } else {

            if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(generalFunc.getJsonValue("iStopId", last_trip_data))) {
                deliveryInfoView.setVisibility(View.VISIBLE);
            }
            Toolbar mToolbar = (Toolbar) findViewById(R.id.toolbar);
            setSupportActionBar(mToolbar);
        }


        deliveryInfoView.setOnClickListener(v -> {

            if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId"))) {
                Bundle bn = new Bundle();
                bn.putString("TripId", data_trip.get("TripId"));
                bn.putString("Status", "activeTrip");
                bn.putSerializable("TRIP_DATA", data_trip);
                new ActUtils(getActContext()).startActWithData(ViewStopOverDetailsActivity.class, bn);

            } else {
                Bundle bn = new Bundle();
                bn.putString("TripId", data_trip.get("TripId"));
                bn.putString("Status", "activeTrip");
                bn.putSerializable("TRIP_DATA", data_trip);
                new ActUtils(getActContext()).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);
            }

        });

        map.getMapAsync(this);

        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) titleTxt.getLayoutParams();
        params.setMargins(Utils.dipToPixels(getActContext(), 20), 0, 0, 0);
        titleTxt.setLayoutParams(params);

        String OPEN_CHAT = generalFunc.retrieveValue(ChatMsgHandler.OPEN_CHAT);
        if (Utils.checkText(OPEN_CHAT)) {
            JSONObject OPEN_CHAT_DATA_OBJ = generalFunc.getJsonObject(OPEN_CHAT);
            if (OPEN_CHAT_DATA_OBJ != null) {
                ChatMsgHandler.performAction(OPEN_CHAT_DATA_OBJ.toString());
            }
        }

        GetLocationUpdates.getInstance().setTripStartValue(!data_trip.get("vTripStatus").equals("Arrived"), true, true, data_trip.get("TripId"));

        boolean isKiosk = data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk");
        boolean isUser = Utils.checkText(data_trip.get("iGcmRegId_U"));
        /*if (isKiosk || !isUser) {
            chatArea.setVisibility(View.GONE);
        }*/
    }

    private void setVideoConsult() {
        videoCallArea = findViewById(R.id.videoCallArea);
        videoIntroTxt = findViewById(R.id.videoIntroTxt);
        videoIntroTxt.setText(GeneralFunctions.fromHtml(generalFunc.retrieveLangLBl("", "LBL_VIDEO_CONSULT_DESC")));
        MTextView videoBtn = findViewById(R.id.videoBtn);
        videoBtn.setText(generalFunc.retrieveLangLBl("", "LBL_VIDEO_CONSULT"));
        View videoBtnArea = findViewById(R.id.videoBtnArea);
        videoBtnArea.setOnClickListener(v -> {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(data_trip.get("PassengerId"))
                    .setPhoneNumber(data_trip.get("vPhone_U"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(vName)
                    .setToMemberImage(data_trip.get("PPicName"))
                    .setMedia((data_trip.get("eBookingFrom").equalsIgnoreCase("Admin") || data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk")) ? CommunicationManager.MEDIA.DEFAULT : CommunicationManager.MEDIA_TYPE)
                    .setTripId(data_trip.get("iTripId"))
                    .setBookingNo(data_trip.get("vRideNo"))
                    .setBid(eType.equals("Bidding"))
                    .build();
            CommunicationManager.getInstance().communicateOnlyVideo(MyApp.getInstance().getCurrentAct(), mDataProvider);
        });
        manageViewForVideo();
    }

    public void isVideoCallGenerate() {
        if (isVideoCall) {
            MyUtils.setIsVideoCallGenerated("Yes");
        }
    }

    private void manageViewForVideo() {
        emeTapImgView.setVisibility(View.GONE);
        userAddressTxt.setVisibility(View.GONE);
        timerlayoutarea.setVisibility(View.GONE);
        timeTxt.setVisibility(View.GONE);
        newbtn_timer.setVisibility(View.GONE);
        progressHinttext.setVisibility(View.GONE);
        onGoingTripsDetailListRecyclerView.setVisibility(View.GONE);
        btntimer.setVisibility(View.GONE);
        videoCallArea.setVisibility(View.VISIBLE);
        timerlayoutMainarea.setVisibility(View.GONE);
        findViewById(R.id.mapV2).setVisibility(View.GONE);

        scrollview.setVisibility(View.VISIBLE);
    }

    private String getPasggerTripStatus() {
        JSONObject passenger_data = generalFunc.getJsonObject("PassengerDetails", obj_userProfile);

        return generalFunc.getJsonValueStr("vTripStatus", passenger_data);
    }

    @Override
    protected void onPostResume() {
        super.onPostResume();
    }

    private void setTripButton() {
        startTripSlideButton = findViewById(R.id.startTripSlideButton);
        endTripSlideButton = findViewById(R.id.endTripSlideButton);
        if (generalFunc.isRTLmode()) {
            startTripSlideButton.setTextDirection(View.TEXT_DIRECTION_RTL);
            endTripSlideButton.setTextDirection(View.TEXT_DIRECTION_RTL);
        }

        startTripSlideButton.setBackgroundColor(getResources().getColor(R.color.appThemeColor_1));
        endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));

        startTripSlideButton.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
                    if (findViewById(R.id.permissionPagerView).getVisibility() == View.VISIBLE) {
                        return;
                    }
                    if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && generalFunc.getJsonValueStr("ENABLE_PROVIDER_CAMERA_REC", obj_userProfile).equalsIgnoreCase("Yes")) {
                        ArrayList<String> requestPermissions = MyApp.getInstance().checkCameraWithMicPermission(true, false);
                        if (!generalFunc.isAllPermissionGranted(false, requestPermissions)) {
                            if (isCompleted) {
                                showNoPermissionV();
                            }
                            return;
                        }
                    }

                    String eAskCodeToUser = data_trip.get("eAskCodeToUser");
                    if (Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified) {
                        if (isOtpVerificationDenied) {
                            isOtpVerificationDenied = false;
                            return;
                        }
                        openEnterOtpView();
                    } else {
                        if (isCompleted) {
                            if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
                                updateBiddingTaskStatus("Ongoing");
                            } else {
                                startTrip();
                            }
                        }
                    }
                }
        );


        endTripSlideButton.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
            if (findViewById(R.id.permissionPagerView).getVisibility() == View.VISIBLE) {
                return;
            }

            if (isCompleted) {
                if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
                    String eAskCodeToUser = data_trip.get("eAskCodeToUser");
                    if (Utils.checkText(eAskCodeToUser) && eAskCodeToUser.equalsIgnoreCase("Yes") && !isOtpVerified) {
                        openEnterOtpView();
                    } else {
                        updateBiddingTaskStatus("Finished");
                    }
                } else if (REQUEST_TYPE.equals("Deliver")) {
                    buildMsgOnDeliveryEnd();
                } else {
                    if (data_trip != null && data_trip.get("eAfterUpload").equalsIgnoreCase("Yes")) {
                        takeAndUploadPic(getActContext(), "after");
                    } else {
                        endTrip();
                    }
                }
            }
        });
    }

    private void showNoPermissionV() {
        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application requires some permission to be granted to work. Please allow it.", "LBL_ALLOW_PERMISSIONS_APP"),
                generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"), generalFunc.retrieveLangLBl("Allow All", "LBL_SETTINGS"), buttonId -> {
                    startTripSlideButton.resetButtonView(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TRIP_TXT"));
                    if (buttonId == 1) {
                        generalFunc.openSettings();
                    }
                });
    }

    private void updateBiddingTaskStatus(String vTaskStatus) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "UpdateDriverBiddingTaskStatus");
        parameters.put("iBiddingPostId", data_trip.get("TripId"));
        parameters.put("vTaskStatus", vTaskStatus);

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> {
                    if (vTaskStatus.equalsIgnoreCase("Ongoing")) {
                        startTripResponse(responseString);
                    } else if (vTaskStatus.equalsIgnoreCase("Finished")) {
                        generalFunc.saveGoOnlineInfo();
                        if (timerrequesttask != null) {
                            try {
                                timerrequesttask.stopRepeatingTask();
                                timerrequesttask = null;
                            } catch (Exception e) {
                                Logger.e("Exception", "::" + e.getMessage());
                            }
                        }
                        closeuploadServicePicAlertBox();
                        stopProcess();

                        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");
                        // MyApp.getInstance().restartWithGetDataApp();
                        MyApp.getInstance().refreshView(this, responseString);
                    }
                });
        exeWebServer.setCancelAble(false);
    }

    private void startTrip() {
        if (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_RIDE").equalsIgnoreCase("Yes") ||
                (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_DELIVERY").equalsIgnoreCase("Yes") ||
                        (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_UFX").equalsIgnoreCase("Yes")))) {
            if ((generalFunc.retrieveValue("ENABLE_FACE_MASK_VERIFICATION").equalsIgnoreCase("Yes") && !isFaceMaskVerification) || (generalFunc.retrieveValue("ENABLE_RESTRICT_PASSENGER_LIMIT").equalsIgnoreCase("Yes") && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride))) {
                showSafetyDialog();
            } else {
                if (data_trip != null && data_trip.get("eBeforeUpload").equalsIgnoreCase("Yes")) {
                    takeAndUploadPic(getActContext(), "before");
                } else {
                    setTripStart();
                }
            }

        } else {
            if (data_trip != null && data_trip.get("eBeforeUpload").equalsIgnoreCase("Yes")) {
                takeAndUploadPic(getActContext(), "before");
            } else {
                setTripStart();
            }
        }
    }

    public void setTimetext(String distance, String time) {
        try {
            JSONObject userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));

            if (!APP_TYPE.equalsIgnoreCase("UberX") && !APP_TYPE.equalsIgnoreCase("Bidding")) {

                timeTxt.setVisibility(View.VISIBLE);
                if (userProfileJsonObj != null && !generalFunc.getJsonValueStr("eUnit", userProfileJsonObj).equalsIgnoreCase("KMs")) {
                    distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT") + " ");
                    timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
                } else {
                    distanceTxt.setText(generalFunc.convertNumberWithRTL(distance) + " " + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT") + " ");
                    timeTxt.setText(generalFunc.convertNumberWithRTL(time) + " ");
                }

            } else {
                if (data_trip.get("eFareType").equalsIgnoreCase(Utils.CabFaretypeRegular)) {
                    timeTxt.setVisibility(View.VISIBLE);
                } else {
                    timeTxt.setVisibility(View.GONE);
                }
            }
        } catch (Exception e) {

        }

    }

    public void handleNoLocationDial() {
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
    }

    public void internetIsBack() {
        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }
    }

    public void checkUserLocation() {


        if (generalFunc.isLocationEnabled() && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0)) {
            showprogress();
        } else {
            hideprogress();
        }
    }

    public void showprogress() {
        new Handler().postDelayed(this::continueShowProgress, 2000);
    }

    public void continueShowProgress() {

        if (!(generalFunc.isLocationEnabled() && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0))) {
            hideprogress();
            return;
        }
        isCurrentLocationFocused = false;

        if (!data_trip.get("REQUEST_TYPE").equalsIgnoreCase(Utils.CabGeneralType_UberX) || !data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
            findViewById(R.id.errorLocArea).setVisibility(View.VISIBLE);
            findViewById(R.id.mProgressBar).setVisibility(View.VISIBLE);
            ((ProgressBar) findViewById(R.id.mProgressBar)).setIndeterminate(true);
            ((ProgressBar) findViewById(R.id.mProgressBar)).getIndeterminateDrawable().setColorFilter(
                    getActContext().getResources().getColor(R.color.appThemeColor_1), PorterDuff.Mode.SRC_IN);

        }

    }

    public void hideprogress() {

        findViewById(R.id.errorLocArea).setVisibility(View.GONE);

        if (findViewById(R.id.mProgressBar) != null) {
            findViewById(R.id.mProgressBar).setVisibility(View.GONE);
        }

    }

    private void setLabels() {

        LBL_REACH_TXT = generalFunc.retrieveLangLBl("to reach", "LBL_REACH_TXT");
        LBL_CONFIRM_STOPOVER_1 = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_STOPOVER_1");
        LBL_CONFIRM_STOPOVER_2 = generalFunc.retrieveLangLBl("", "LBL_CONFIRM_STOPOVER_2");
        LBL_BTN_SLIDE_END_TRIP_TXT = generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_TRIP_TXT");

        LBL_BTN_SLIDE_INTERCITY_MARKED_AS_REACH = generalFunc.retrieveLangLBl("", "LBL_INTERCITY_MARKED_AS_REACH");
        LBL_BTN_SLIDE_INTERCITY_START_TO_RETURN = generalFunc.retrieveLangLBl("", "LBL_INTERCITY_START_TO_RETURN");

        titleTxt.setText(generalFunc.retrieveLangLBl("En Route", "LBL_EN_ROUTE_TXT"));
        timeTxt.setText("--" + LBL_REACH_TXT);
        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        invalid_str = generalFunc.retrieveLangLBl("Invalid value", "LBL_DIGIT_REQUIRE");

        ((MTextView) findViewById(R.id.placeTxtView)).setText(generalFunc.retrieveLangLBl("", "LBL_ADD_DESTINATION_BTN_TXT"));

        timerHinttext.setText(generalFunc.retrieveLangLBl("JOB TIMER", "LBL_JOB_TIMER_HINT"));
        progressHinttext.setText(generalFunc.retrieveLangLBl("JOB PROGRESS", "LBL_JOB_PROGRESS"));

        txt_TimerHour.setText(generalFunc.retrieveLangLBl("", "LBL_HOUR_TXT"));
        txt_TimerMinute.setText(generalFunc.retrieveLangLBl("", "LBL_MINUTES_TXT"));
        txt_TimerSecond.setText(generalFunc.retrieveLangLBl("", "LBL_SECONDS_TXT"));

        tollTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_TOLL_SKIP_HELP"));
        if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {

            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TRIP_TXT"));

            if (Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
                endTripSlideButton.setButtonText(LBL_CONFIRM_STOPOVER_1 + " " + LBL_CONFIRM_STOPOVER_2 + " " + generalFunc.convertNumberWithRTL(data_trip.get("currentStopOverPoint")));
            } else {
                if (isInterCity && isInterCityRoundTrip) {
                    if (Utils.checkText(data_trip.get("eInterCityTripLogStatus"))) {
                        endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", data_trip.get("eInterCityTripLogStatus")));
                        eInterCityButtonBgColor = data_trip.get("eInterCityButtonBgColor");
                        if (Utils.checkText(eInterCityButtonBgColor)) {
                            endTripSlideButton.setBackgroundColor(Color.parseColor(eInterCityButtonBgColor));
                        }
                    } else {
                        endTripSlideButton.setButtonText(LBL_BTN_SLIDE_END_TRIP_TXT);
                        endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                    }
                } else {
                    endTripSlideButton.setButtonText(LBL_BTN_SLIDE_END_TRIP_TXT);
                    endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                }
            }
        } else if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_JOB_TXT"));
            endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_JOB_TXT"));
        } else if (eType.equalsIgnoreCase("Bidding")) {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TASK_TXT"));
            endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_TASK_TXT"));
        } else {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_SLIDE_BEGIN_DELIVERY"));
            if (Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
                endTripSlideButton.setButtonText((LBL_CONFIRM_STOPOVER_1 + " " + LBL_CONFIRM_STOPOVER_2 + " " + generalFunc.convertNumberWithRTL(data_trip.get("currentStopOverPoint"))));
            } else {
                endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_SLIDE_END_DELIVERY"));
            }
        }

        setButtonName();
        ((MTextView) findViewById(R.id.errorTitleTxt)).setText(generalFunc.retrieveLangLBl("Waiting for your location.", "LBL_LOCATION_FATCH_ERROR_TXT"));

        ((MTextView) findViewById(R.id.errorSubTitleTxt)).setText(generalFunc.retrieveLangLBl("Try to fetch  your accurate location. \"If you still face the problem, go to open sky instead of closed area\".", "LBL_NO_LOC_GPS_TXT"));
    }

    private void setButtonName() {
        String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
        currentStopOverPoint = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("currentStopOverPoint", last_trip_data));
        totalStopOverPoint = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("totalStopOverPoint", last_trip_data));
        if (generalFunc.getJsonValue("eServiceLocation", last_trip_data) != null && generalFunc.getJsonValue("eServiceLocation", last_trip_data).equalsIgnoreCase("Driver")) {
            (findViewById(R.id.navigationViewArea)).setVisibility(View.GONE);
        }


        if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery) || REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(generalFunc.getJsonValue("iStopId", last_trip_data))) {

            if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(generalFunc.getJsonValue("iStopId", last_trip_data))) {
                pickupTxt.setVisibility(View.VISIBLE);
                pickupTxt.setText(vName + " ");
                pickupNameTxt.setText(generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_TITLE_TXT") + " " +
                        generalFunc.convertNumberWithRTL("" + currentStopOverPoint)
                        + " " + generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_OUT_OF") + " " + generalFunc.convertNumberWithRTL("" + totalStopOverPoint));

                pickupNameTxt.setVisibility(View.VISIBLE);
                RatingTxt.setText(data_trip.get("PRating"));
                RatingHArea.setVisibility(View.VISIBLE);

            }
        } else if (generalFunc.getJsonValue("ePoolRide", last_trip_data).equalsIgnoreCase("Yes")) {
            pickupTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ADMIN_DROPOFF"));
            pickupTxt.setVisibility(View.VISIBLE);
            pickupNameTxt.setText(vName + " " + data_trip.get("vLastName"));
            isPoolRide = true;
            deliveryDetailsArea.setVisibility(View.VISIBLE);
            AppService.getInstance().executeService(AppService.Event.SUBSCRIBE, AppService.EventAction.CAB_REQUEST);

        }

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TRIP_TXT"));
            if (Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
                endTripSlideButton.setButtonText(LBL_CONFIRM_STOPOVER_1 + " " + LBL_CONFIRM_STOPOVER_2 + " " + generalFunc.convertNumberWithRTL(data_trip.get("currentStopOverPoint")));
            } else {
                if (isInterCity && isInterCityRoundTrip) {
                    if (Utils.checkText(data_trip.get("eInterCityTripLogStatus"))) {
                        endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", data_trip.get("eInterCityTripLogStatus")));
                        eInterCityButtonBgColor = data_trip.get("eInterCityButtonBgColor");
                        if (Utils.checkText(eInterCityButtonBgColor)) {
                            endTripSlideButton.setBackgroundColor(Color.parseColor(eInterCityButtonBgColor));
                        }
                    } else {
                        endTripSlideButton.setButtonText(LBL_BTN_SLIDE_END_TRIP_TXT);
                        endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                    }
                } else {
                    endTripSlideButton.setButtonText(LBL_BTN_SLIDE_END_TRIP_TXT);
                    endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));
                }
            }
        } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_JOB_TXT"));
            endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_JOB_TXT"));
        } else if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_BEGIN_TASK_TXT"));
            endTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_BTN_SLIDE_END_TASK_TXT"));
        } else {
            startTripSlideButton.setButtonText(generalFunc.retrieveLangLBl("", "LBL_SLIDE_BEGIN_DELIVERY"));
        }
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        this.gMap = googleMap;
        if (generalFunc.checkLocationPermission(true)) {
            getMap().setMyLocationEnabled(false);
        }
        getMap().getUiSettings().setTiltGesturesEnabled(false);
        getMap().getUiSettings().setCompassEnabled(false);
        getMap().getUiSettings().setMyLocationButtonEnabled(false);

        if (isDestinationAdded) {
            addDestinationMarker();
        }

        if (isDestinationAdded && userLocation != null && route_polyLine == null) {
//            drawRoute("" + destLocLatitude, "" + destLocLongitude);
            if (updateDirections != null) {
                Location destLoc = new Location("gps");
                destLoc.setLatitude(destLocLatitude);
                destLoc.setLongitude(destLocLongitude);
                updateDirections.changeUserLocation(destLoc);
            }
        }
        getMap().setOnCameraMoveStartedListener(reason -> {

            if (reason == 1) {
                userLocBtnImgView.setVisibility(View.VISIBLE);
            }
        });

        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        checkUserLocation();
        startLocationTracker();
    }

    private void startLocationTracker() {
        if (this.getMap() == null) {
            return;
        }

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }
        GetLocationUpdates.getInstance().setTripStartValue(!data_trip.get("vTripStatus").equals("Arrived"), true, true, data_trip.get("TripId"));
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);
    }

    public void addDestinationMarker() {
        try {
            if (getMap() == null) {
                return;
            }
            if (destLocMarker != null) {
                destLocMarker.remove();

            }
            if (route_polyLine != null) {
                route_polyLine.remove();
            }

            MarkerOptions markerOptions_destLocation = new MarkerOptions();
            markerOptions_destLocation.position(new LatLng(destLocLatitude, destLocLongitude));
            markerOptions_destLocation.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_dest_marker)).anchor(0.5f,
                    0.5f);
            destLocMarker = getMap().addMarker(markerOptions_destLocation);
        } catch (Exception e) {

        }
    }


    public GoogleMap getMap() {
        return this.gMap;
    }

    private void setDriverDetail() {
        String image_url = "";
        if (tripDetail != null && tripDetail.size() > 0) {
            image_url = CommonUtilities.USER_PHOTO_PATH + tripDetail.get(0).get("iDriverId") + "/"
                    + tripDetail.get(0).get("driverImage");
        }
        if (!Utils.checkText(image_url)) {
            image_url = "Temp";
        }

        new LoadImage.builder(LoadImage.bind(image_url), ((ImageView) findViewById(R.id.user_img))).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        userNameTxt.setText(tripDetail.get(0).get("driverName"));
        userAddressTxt.setText(tripDetail.get(0).get("tSaddress"));
        float ratinguser = GeneralFunctions.parseFloatValue(0, tripDetail.get(0).get("driverRating"));
        Log.d("ratinguser", "setDriverDetail: " + ratinguser);
        ratingBar_ufx.setRating(GeneralFunctions.parseFloatValue(0, tripDetail.get(0).get("driverRating")));

    }

    public void setData() {

        tripId = data_trip.get("TripId");
        eType = data_trip.get("REQUEST_TYPE");

        vName = data_trip.get("PName");
        SelectableRoundedImageView userImg = findViewById(R.id.userImg);
        if (Utils.checkText(data_trip.get("PPicName"))) {
            vImage = data_trip.get("PPicName");
        } else {
            vImage = "temp";
        }

        String image_url = CommonUtilities.USER_PHOTO_PATH + data_trip.get("PassengerId") + "/" + vImage;
        new LoadImage.builder(LoadImage.bind(image_url), userImg).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
        deliveryVerificationCode = data_trip.get("vDeliveryConfirmCode");

        if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            recipientTxt.setText(data_trip.get("Running_Delivery_Txt"));
            pickupNameTxt.setText(data_trip.get("vReceiverName"));
            pickupNameTxt.setVisibility(View.VISIBLE);
            recipientTxt.setVisibility(View.VISIBLE);
            iTripDeliveryLocationId = data_trip.get("iTripDeliveryLocationId");
        }
        if (eType.equalsIgnoreCase("Deliver")) {
            pickupNameTxt.setText(vName + " ");
            pickupNameTxt.setVisibility(View.VISIBLE);
            RatingTxt.setText(data_trip.get("PRating"));
            RatingHArea.setVisibility(View.VISIBLE);
            deliveryDetailsArea.setVisibility(View.VISIBLE);

        }
        if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            pickupNameTxt.setText(vName + " ");
            pickupNameTxt.setVisibility(View.VISIBLE);
            RatingTxt.setText(data_trip.get("PRating"));
            RatingHArea.setVisibility(View.VISIBLE);
            deliveryDetailsArea.setVisibility(View.VISIBLE);
        }


        String DestLocLatitude = data_trip.get("DestLocLatitude");
        String DestLocLongitude = data_trip.get("DestLocLongitude");
        if (!DestLocLatitude.equals("") && !DestLocLatitude.equals("0") && !DestLocLongitude.equals("") && !DestLocLongitude.equals("0")) {
            setDestinationPoint(DestLocLatitude, DestLocLongitude, data_trip.get("DestLocAddress"), true);
            addressTxt.setVisibility(View.VISIBLE);
            destLocSearchArea.setVisibility(View.GONE);

        } else {
            destLocSearchArea.setOnClickListener(new setOnClickAct());
            destLocSearchArea.setVisibility(View.VISIBLE);
            addressTxt.setVisibility(View.GONE);

            tollTxtView.setVisibility(View.GONE);
            if (data_trip.get("REQUEST_TYPE").equalsIgnoreCase("UberX") || data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
                addressTxt.setVisibility(View.VISIBLE);
                destLocSearchArea.setVisibility(View.GONE);
                deliveryDetailsArea.setVisibility(View.GONE);
            } else {
                (findViewById(R.id.navigationViewArea)).setVisibility(View.VISIBLE);
            }
        }

        if (APP_TYPE.equalsIgnoreCase("UberX") || APP_TYPE.equalsIgnoreCase("Bidding")) {
            addressTxt.setVisibility(View.VISIBLE);
            destLocSearchArea.setVisibility(View.GONE);
            (findViewById(R.id.navigationViewArea)).setVisibility(View.GONE);
            tollTxtView.setVisibility(View.GONE);
            deliveryDetailsArea.setVisibility(View.GONE);
        }

        if (!data_trip.get("vTripStatus").equals("Arrived")) {
            startTripSlideButton.setVisibility(View.GONE);
            endTripSlideButton.setVisibility(View.VISIBLE);

            if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
                dropAllIconArea.setVisibility(View.VISIBLE);
            }
            isendslide = true;
            invalidateOptionsMenu();

            configTripStartView();

            if (data_trip.get("eFareType").equals(Utils.CabFaretypeHourly)) {

                Log.e("countdownstartCalled", ":: 1");
                countDownStart();
                btntimer.setVisibility(View.VISIBLE);
                if (data_trip.get("TimeState") != null && !data_trip.get("TimeState").equals("")) {
                    if (data_trip.get("TimeState").equalsIgnoreCase("Resume")) {

                        isresume = true;
                        btntimer.setText(generalFunc.retrieveLangLBl("pause", "LBL_PAUSE_TEXT"));
                        btntimer.setVisibility(View.VISIBLE);

                    } else {
                        if (timerrequesttask != null) {
                            timerrequesttask.stopRepeatingTask();
                            timerrequesttask = null;
                        }

                        isresume = false;
                        btntimer.setText(generalFunc.retrieveLangLBl("resume", "LBL_RESUME_TEXT"));
                        btntimer.setVisibility(View.VISIBLE);

                    }
                }

                if (data_trip.get("TotalSeconds") != null && !data_trip.get("TotalSeconds").equals("")) {
                    i = Integer.parseInt(data_trip.get("TotalSeconds"));
                    setTimerValues();

                }
                if (data_trip.get("iTripTimeId") != null && !data_trip.get("iTripTimeId").equals("")) {
                    TripTimeId = data_trip.get("iTripTimeId");
                    //  countDownStart();
                }
            }

            if (generalFunc.getJsonValue("ENABLE_INTRANSIT_SHOPPING_SYSTEM", obj_userProfile).equals("Yes") && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) &&
                    !data_trip.get("eRental").equalsIgnoreCase("Yes") && !data_trip.get("ePoolRide").equalsIgnoreCase("Yes") &&
                    data_trip.get("eTransit").equalsIgnoreCase("Yes")) {
                transitConfigTripStartView();
            }
        }

        REQUEST_TYPE = data_trip.get("REQUEST_TYPE");
        SITE_TYPE = data_trip.get("SITE_TYPE");
        deliveryVerificationCode = data_trip.get("vDeliveryConfirmCode");
        if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery) || data_trip.get("eHailTrip").equalsIgnoreCase("Yes")) {
            userImg.setVisibility(View.GONE);
        } else {
            userImg.setVisibility(View.VISIBLE);
        }

        setButtonName();
        if (data_trip.get("REQUEST_TYPE").equalsIgnoreCase(Utils.CabGeneralType_UberX) || data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
            buttonlayouts.setVisibility(View.GONE);
            findViewById(R.id.mapV2).setVisibility(View.GONE);
            getTripDeliveryLocations();

        } else {
            try {

                timerarea.setVisibility(View.GONE);
                scrollview.setVisibility(View.GONE);
                timerlayoutarea.setVisibility(View.GONE);
                timerlayoutMainarea.setVisibility(View.GONE);
                emeTapImgView.setVisibility(View.VISIBLE);
            } catch (Exception e) {

            }
        }
    }

    @Override
    public void onLocationUpdate(Location location) {

        if (location == null) {
            return;
        }
        if (gMap == null) {
            this.userLocation = location;
            return;
        }

        updateDriverMarker(new LatLng(location.getLatitude(), location.getLongitude()));

        this.userLocation = location;


        String DestLocLatitude = data_trip.get("DestLocLatitude");
        String DestLocLongitude = data_trip.get("DestLocLongitude");


        if (!ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP.equalsIgnoreCase("Yes")) {
            if (!DestLocLatitude.equals("") && !DestLocLatitude.equals("0") && !DestLocLongitude.equals("") && !DestLocLongitude.equals("0")) {
                double passenger_lat = GeneralFunctions.parseDoubleValue(0.0, DestLocLatitude);
                double passenger_lon = GeneralFunctions.parseDoubleValue(0.0, DestLocLongitude);
                LatLngBounds.Builder builder = new LatLngBounds.Builder();
                builder.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
                builder.include(new LatLng(passenger_lat, passenger_lon));
                gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), Utils.dipToPixels(getActContext(), 40)));
            }
        }

        if (ENABLE_DIRECTION_SOURCE_DESTINATION_DRIVER_APP.equalsIgnoreCase("Yes") || (DestLocLatitude.equals("") || DestLocLatitude.equals("0") || DestLocLongitude.equals("") || DestLocLongitude.equals("0"))) {
            try {
                if (isFirstMapMove) {
                    getMap().moveCamera(generalFunc.getCameraPosition(location, gMap));
                    isFirstMapMove = false;
                } else {
                    getMap().animateCamera(generalFunc.getCameraPosition(location, gMap), 1000, null);
                }
            } catch (Exception e) {

            }
        }

        checkUserLocation();

        if (data_trip.get("REQUEST_TYPE").equalsIgnoreCase(Utils.CabGeneralType_UberX) || data_trip.get("REQUEST_TYPE").equalsIgnoreCase("Bidding")) {
            String eFareType = data_trip.get("eFareType");
            if (eFareType.equals(Utils.CabFaretypeRegular)) {
                if (updateDirections == null) {
                    Location destLoc = new Location("temp");
                    destLoc.setLatitude(destLocLatitude);
                    destLoc.setLongitude(destLocLongitude);
                    if (destLocLatitude != 0.0) {
                        updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
                    }
                }

            } else if (eFareType.equals(Utils.CabFaretypeFixed)) {
                return;

            } else if (eFareType.equals(Utils.CabFaretypeHourly)) {
                return;
            } else {
                if (updateDirections == null) {
                    Location destLoc = new Location("temp");
                    destLoc.setLatitude(destLocLatitude);
                    destLoc.setLongitude(destLocLongitude);


                    updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
                    updateDirections.scheduleDirectionUpdate();
                }

            }
        } else {
            if (updateDirections == null) {
                Location destLoc = new Location("temp");
                destLoc.setLatitude(destLocLatitude);
                destLoc.setLongitude(destLocLongitude);


                updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
                if (eFly) {
                    double sourcelatitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLatitude"));
                    double sourcelongitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLongitude"));

                    updateDirections.iseFly(eFly, new LatLng(sourcelatitude, sourcelongitude));
                }
                updateDirections.scheduleDirectionUpdate();
            }
        }

        if (updateDirections != null) {
            updateDirections.changeUserLocation(location);
        }

    }

    public static Bitmap createDrawableFromView(Context context, View view) {
        DisplayMetrics displayMetrics = new DisplayMetrics();
        ((Activity) context).getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        view.setLayoutParams(new RelativeLayout.LayoutParams(RelativeLayout.LayoutParams.WRAP_CONTENT, RelativeLayout.LayoutParams.WRAP_CONTENT));
        view.measure(displayMetrics.widthPixels, displayMetrics.heightPixels);
        view.layout(0, 0, displayMetrics.widthPixels, displayMetrics.heightPixels);
        view.buildDrawingCache();
        Bitmap bitmap = Bitmap.createBitmap(view.getMeasuredWidth(), view.getMeasuredHeight(), Bitmap.Config.ARGB_8888);

        Canvas canvas = new Canvas(bitmap);
        view.draw(canvas);

        return bitmap;
    }


    public void updateDriverMarker(final LatLng newLocation) {

        if (MyApp.getInstance().isMyAppInBackGround() || gMap == null) {
            return;
        }


        boolean isUberX = eType.equalsIgnoreCase(Utils.CabGeneralType_UberX) || eType.equalsIgnoreCase("Bidding");
        if (driverMarker == null) {

            if (isUberX) {

                String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValueStr("vImage", obj_userProfile);
                View marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                        .inflate(R.layout.uberx_provider_maker_design, null);
                SelectableRoundedImageView providerImgView = (SelectableRoundedImageView) marker_view
                        .findViewById(R.id.providerImgView);

                final View finalMarker_view = marker_view;

                providerImgView.setImageResource(R.mipmap.ic_no_pic_user);

                if (Utils.checkText(generalFunc.getJsonValueStr("vImage", obj_userProfile))) {

                    MarkerOptions markerOptions_driver = new MarkerOptions();
                    markerOptions_driver.position(newLocation);
                    markerOptions_driver.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view))).anchor(0.5f,
                            0.5f).flat(true);
                    driverMarker = gMap.addMarker(markerOptions_driver);

                    new LoadImage.builder(LoadImage.bind(image_url), providerImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).setPicassoListener(new LoadImage.PicassoListener() {
                        @Override
                        public void onSuccess() {
                            driverMarker.setIcon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view)));
                        }

                        @Override
                        public void onError() {
                            driverMarker.setIcon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view)));
                        }
                    }).build();


                    driverMarker.setFlat(false);
                    driverMarker.setAnchor(0.5f, 1);
                    driverMarker.setTitle(generalFunc.getMemberId());
                } else {
                    MarkerOptions markerOptions_driver = new MarkerOptions();
                    markerOptions_driver.position(newLocation);
                    markerOptions_driver.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), finalMarker_view))).anchor(0.5f,
                            1.5f).flat(true);
                    driverMarker = gMap.addMarker(markerOptions_driver);
                    driverMarker.setFlat(false);
                    driverMarker.setAnchor(0.5f, 1);
                    driverMarker.setTitle(generalFunc.getMemberId());

                }
            } else {
                int iconId = R.mipmap.car_driver;

                String vVehicleType = data_trip.containsKey("vVehicleType") ? data_trip.get("vVehicleType") : "";

                if (Utils.checkText(vVehicleType)) {
                    if (vVehicleType.equalsIgnoreCase("Ambulance")) {
                        iconId = R.mipmap.car_driver_ambulance;
                    } else if (vVehicleType.equalsIgnoreCase("Bike")) {
                        iconId = R.mipmap.car_driver_1;
                    } else if (vVehicleType.equalsIgnoreCase("Cycle")) {
                        iconId = R.mipmap.car_driver_2;
                    } else if (vVehicleType.equalsIgnoreCase("Truck")) {
                        iconId = R.mipmap.car_driver_4;
                    } else if (vVehicleType.equalsIgnoreCase("Fly")) {
                        iconId = R.mipmap.ic_fly_icon;
                    }
                }

                MarkerOptions markerOptions_driver = new MarkerOptions();
                markerOptions_driver.position(newLocation);
                markerOptions_driver.icon(BitmapDescriptorFactory.fromResource(iconId)).anchor(0.5f, 0.5f).flat(true);

                driverMarker = gMap.addMarker(markerOptions_driver);
                driverMarker.setTitle(generalFunc.getMemberId());
            }

        }


        if (this.userLocation != null && newLocation != null) {
            LatLng currentLatLng = new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude());
            float rotation = driverMarker == null ? 0 : driverMarker.getRotation();

            if (MarkerAnim.currentLng != null) {
                rotation = (float) MarkerAnim.bearingBetweenLocations(MarkerAnim.currentLng, newLocation);
            } else {
                rotation = (float) MarkerAnim.bearingBetweenLocations(currentLatLng, newLocation);
            }

            if (isUberX) {
                rotation = 0;
            }


            if (driverMarker != null) {
                driverMarker.setTitle(generalFunc.getMemberId());
            }

            HashMap<String, String> previousItemOfMarker = MarkerAnim.getLastLocationDataOfMarker(driverMarker);

            HashMap<String, String> data_map = new HashMap<>();
            double vLatitude = newLocation.latitude;
            double vLongitude = newLocation.longitude;
            data_map.put("vLatitude", "" + vLatitude);
            data_map.put("vLongitude", "" + vLongitude);
            data_map.put("iDriverId", "" + generalFunc.getMemberId());
            data_map.put("RotationAngle", "" + rotation);
            data_map.put("LocTime", "" + System.currentTimeMillis());

            Location location = new Location("marker");
            location.setLatitude(vLatitude);
            location.setLongitude(vLongitude);


            String prevLocTime = previousItemOfMarker.get("LocTime");
            String LocTime = data_map.get("LocTime");

            if (MarkerAnim.toPositionLat.get("" + vLatitude) == null || MarkerAnim.toPositionLong.get("" + vLongitude) == null) {
                if (prevLocTime != null && !prevLocTime.equals("")) {

                    long previousLocTime = GeneralFunctions.parseLongValue(0, prevLocTime);
                    long newLocTime = GeneralFunctions.parseLongValue(0, LocTime);

                    if (previousLocTime != 0 && newLocTime != 0) {

                        if ((newLocTime - previousLocTime) > 0 && !MarkerAnim.driverMarkerAnimFinished) {
                            MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                        } else if ((newLocTime - previousLocTime) > 0) {
                            MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                        }

                    } else if ((previousLocTime == 0 || newLocTime == 0) && !MarkerAnim.driverMarkerAnimFinished) {
                        MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                    } else {
                        MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                    }
                } else if (!MarkerAnim.driverMarkerAnimFinished) {
                    MarkerAnim.addToListAndStartNext(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                } else {
                    MarkerAnim.animateMarker(driverMarker, this.gMap, location, rotation, 850, tripId, LocTime);
                }
            }
        }
    }

    public CameraPosition cameraForUserPosition(Location location, boolean isFirst) {
        double currentZoomLevel = getMap().getCameraPosition().zoom;

        if (isFirst) {
            currentZoomLevel = Utils.defaultZomLevel;
        }
        CameraPosition cameraPosition = new CameraPosition.Builder().target(new LatLng(location.getLatitude(), location.getLongitude())).bearing(getMap().getCameraPosition().bearing)
                .zoom((float) currentZoomLevel).build();

        return cameraPosition;
    }


    public void tripCancelled(String msg) {

        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            generateAlert.closeAlertBox();
            generalFunc.saveGoOnlineInfo();
            MyApp.getInstance().restartWithGetDataApp();
        });
        generateAlert.setContentMessage("", msg);
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.showAlertBox();
    }

    private void getTripDeliveryLocations() {
        if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            return;
        }

        final HashMap<String, String> parameters = new HashMap<>();
        if (eType.equals("Bidding")) {
            parameters.put("type", "getTaskLocations");
            parameters.put("iBiddingPostId", data_trip.get("iTripId"));
        } else {
            parameters.put("type", "getTripDeliveryLocations");
            parameters.put("iTripId", data_trip.get("iTripId"));
        }
        parameters.put("userType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters,
                responseString -> {

                    if (responseString != null && !responseString.equals("")) {


                        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {


                            switch (Objects.requireNonNull(data_trip.get("eFareType"))) {
                                case Utils.CabFaretypeRegular:
                                    findViewById(R.id.mapV2).setVisibility(View.VISIBLE);
                                    timerarea.setVisibility(View.GONE);
                                    scrollview.setVisibility(View.GONE);
                                    timerlayoutarea.setVisibility(View.GONE);
                                    timerlayoutMainarea.setVisibility(View.GONE);
                                    break;
                                case Utils.CabFaretypeFixed:
                                    scrollview.setVisibility(View.VISIBLE);
                                    timerlayoutarea.setVisibility(View.GONE);
                                    timerlayoutMainarea.setVisibility(View.GONE);
                                    emeTapImgView.setVisibility(View.GONE);
                                    break;
                                case Utils.CabFaretypeHourly:
                                    scrollview.setVisibility(View.VISIBLE);
                                    emeTapImgView.setVisibility(View.GONE);
                                    timerlayoutarea.setVisibility(View.VISIBLE);
                                    timerlayoutMainarea.setVisibility(View.VISIBLE);

                                    break;
                                default:
                                    if (eType.equals("Bidding")) {
                                        scrollview.setVisibility(View.VISIBLE);
                                        timerlayoutarea.setVisibility(View.GONE);
                                        timerlayoutMainarea.setVisibility(View.GONE);
                                        emeTapImgView.setVisibility(View.GONE);
                                    } else {
                                        timerarea.setVisibility(View.GONE);
                                    }
                                    break;
                            }


                            list = new ArrayList<>();

                            String message = generalFunc.getJsonValue(Utils.message_str, responseString);


                            tripDetail = new ArrayList<>();
                            JSONArray tripLocations = generalFunc.getJsonArray("States", message);
                            String driverdetails = generalFunc.getJsonValue("driverDetails", message);
                            tempMap = new HashMap<>();
                            tempMap.put("driverImage", generalFunc.getJsonValue("riderImage", driverdetails));
                            tempMap.put("driverName", generalFunc.getJsonValue("riderName", driverdetails));
                            tempMap.put("driverRating", generalFunc.getJsonValue("riderRating", driverdetails));
                            tempMap.put("tSaddress", generalFunc.getJsonValue("tSaddress", driverdetails));
                            tempMap.put("iDriverId", generalFunc.getJsonValue("iUserId", driverdetails));

                            tripDetail.add(tempMap);


                            list.clear();

                            String LBL_BOOKING = generalFunc.retrieveLangLBl("", "LBL_BOOKING");
                            if (tripLocations != null)
                                for (int i = 0; i < tripLocations.length(); i++) {
                                    tempMap = new HashMap<>();

                                    JSONObject jobject1 = generalFunc.getJsonObject(tripLocations, i);
                                    tempMap.put("status", generalFunc.getJsonValue("type", jobject1.toString()));
                                    tempMap.put("iTripId", generalFunc.getJsonValue("text", jobject1.toString()));

                                    tempMap.put("eType", generalFunc.getJsonValue("eType", jobject1.toString()));
                                    tempMap.put("value", generalFunc.getJsonValue("timediff", jobject1.toString()));
                                    tempMap.put("Booking_LBL", LBL_BOOKING);
                                    tempMap.put("msg", generalFunc.getJsonValue("text", jobject1.toString()));
                                    tempMap.put("tDisplayDate", generalFunc.getJsonValue("tDisplayDate", jobject1.toString()));
                                    tempMap.put("tDisplayTime", generalFunc.getJsonValue("tDisplayTime", jobject1.toString()));
                                    tempMap.put("tDisplayDateTime", generalFunc.getJsonValue("tDisplayDateTime", jobject1.toString()));
                                    tempMap.put("tDisplayTimeAbbr", generalFunc.getJsonValue("tDisplayTimeAbbr", jobject1.toString()));
                                    /*tempMap.put("time", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dateOrig", jobject1.toString()), Utils.OriginalDateFormate,
                                            DateTimeUtils.hour + ":" + DateTimeUtils.min)));
                                    tempMap.put("timeampm", generalFunc.convertNumberWithRTL(generalFunc.getDateFormatedType(generalFunc.getJsonValue("dateOrig", jobject1.toString()), Utils.OriginalDateFormate, DateTimeUtils.amPm)));*/
                                    list.add(tempMap);
                                }

                            setView();

                            if (eFly || eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                                scrollview.setVisibility(View.GONE);
                            }

                            setDriverDetail();
                            if (isVideoCall) {
                                manageViewForVideo();
                            }
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                        }
                    } else {
                        generalFunc.showError();
                    }
                });

    }

    public void buildMsgOnDeliveryEnd() {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        builder.setTitle(generalFunc.retrieveLangLBl("Delivery Confirmation", "LBL_DELIVERY_CONFIRM"));
        builder.setCancelable(false);
        LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dialog_end_delivery_design, null);

        final MaterialEditText verificationCodeBox = (MaterialEditText) dialogView.findViewById(R.id.editBox);
        verificationCodeBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        String contentMsg = generalFunc.retrieveLangLBl("Please enter the confirmation code received from recipient.", "LBL_DELIVERY_END_NOTE");
        if (SITE_TYPE.equalsIgnoreCase("Demo")) {
            contentMsg = contentMsg + " \n" +
                    generalFunc.retrieveLangLBl("For demo purpose, please enter confirmation code in text box as shown below.", "LBL_DELIVERY_END_NOTE_DEMO")
                    + " \n" + generalFunc.retrieveLangLBl("Confirmation Code", "LBL_CONFIRMATION_CODE") + ": " + deliveryVerificationCode;
        }

        ((MTextView) dialogView.findViewById(R.id.contentMsgTxt)).setText(contentMsg);

        builder.setView(dialogView);

        builder.setPositiveButton(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), (dialog, which) -> {

        });
        builder.setNegativeButton(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), (dialog, which) -> {
        });

        deliveryEndDialog = builder.create();
        LayoutDirection.setLayoutDirection(deliveryEndDialog);
        deliveryEndDialog.show();

        deliveryEndDialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(view -> {

            if (!Utils.checkText(verificationCodeBox)) {
                verificationCodeBox.setError(generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD"));
                return;
            }

            if (!Utils.getText(verificationCodeBox).equals(deliveryVerificationCode)) {
                verificationCodeBox.setError(generalFunc.retrieveLangLBl("Invalid code", "LBL_INVALID_DELIVERY_CONFIRM_CODE"));
                return;
            }

            deliveryEndDialog.dismiss();

            if (APP_TYPE.equalsIgnoreCase("UberX") || eType.equalsIgnoreCase(Utils.CabGeneralType_UberX) || eType.equalsIgnoreCase("Bidding") &&
                    data_trip != null && data_trip.get("eAfterUpload").equalsIgnoreCase("Yes")) {
                //&& generalFunc.retrieveValue(Utils.PHOTO_UPLOAD_SERVICE_ENABLE_KEY).equalsIgnoreCase("Yes")) {
                takeAndUploadPic(getActContext(), "after");
            } else {
                endTrip();
            }

        });

        deliveryEndDialog.getButton(AlertDialog.BUTTON_NEGATIVE).setOnClickListener(v -> {
            deliveryEndDialog.dismiss();
            endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
        });
    }

    public boolean onCreateOptionsMenu(Menu menu) {
        this.menu = menu;

        MenuInflater menuInflater = getMenuInflater();
        menuInflater.inflate(R.menu.trip_accept_menu, menu);

        if (REQUEST_TYPE.equals("Deliver") || REQUEST_TYPE.equals(Utils.eType_Multi_Delivery)) {

            menu.findItem(R.id.menu_passenger_detail).setTitle(generalFunc.retrieveLangLBl("View Delivery Details", "LBL_VIEW_DELIVERY_DETAILS"));
            if (!isendslide) {
                menu.findItem(R.id.menu_cancel_trip).setTitle(generalFunc.retrieveLangLBl("Cancel Delivery", "LBL_CANCEL_DELIVERY"));
            } else {
                MenuItem item = menu.findItem(R.id.menu_cancel_trip);
                item.setVisible(false);

            }
        } else {
            try {
                if (data_trip.get("eHailTrip").equalsIgnoreCase("Yes")) {
                    menu.findItem(R.id.menu_passenger_detail).setTitle(generalFunc.retrieveLangLBl("View passenger detail", "LBL_VIEW_PASSENGER_DETAIL")).setVisible(false);
                    menu.findItem(R.id.menu_call).setTitle(generalFunc.retrieveLangLBl("Call", "LBL_CALL_ACTIVE_TRIP")).setVisible(false);
                    menu.findItem(R.id.menu_message).setTitle(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_ACTIVE_TRIP")).setVisible(false);

                    chatArea.setVisibility(View.GONE);
                    callArea.setVisibility(View.GONE);
                    RatingHArea.setVisibility(View.GONE);

                } else {
                    menu.findItem(R.id.menu_passenger_detail).setTitle(generalFunc.retrieveLangLBl("View passenger detail", "LBL_VIEW_PASSENGER_DETAIL")).setVisible(false);
                }
            } catch (Exception e) {
                menu.findItem(R.id.menu_passenger_detail).setTitle(generalFunc.retrieveLangLBl("View passenger detail", "LBL_VIEW_PASSENGER_DETAIL")).setVisible(false);
            }
            menu.findItem(R.id.menu_cancel_trip).setTitle(generalFunc.retrieveLangLBl("Cancel trip", "LBL_CANCEL_TRIP"));
        }

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            menu.findItem(R.id.menu_cancel_trip).setTitle(generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB"));
        }

        String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
        if (!generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("") && generalFunc.getJsonValue("moreServices", last_trip_data).equalsIgnoreCase("Yes")) {
            menu.findItem(R.id.menu_specialInstruction).setTitle(generalFunc.retrieveLangLBl("Special Instruction", "LBL_TITLE_REQUESTED_SERVICES"));
        } else {

            menu.findItem(R.id.menu_specialInstruction).setTitle(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"));
        }
        menu.findItem(R.id.menu_call).setTitle(generalFunc.retrieveLangLBl("Call", "LBL_CALL_ACTIVE_TRIP"));
        menu.findItem(R.id.menu_message).setTitle(generalFunc.retrieveLangLBl("Message", "LBL_MESSAGE_ACTIVE_TRIP"));
        menu.findItem(R.id.menu_sos).setTitle(generalFunc.retrieveLangLBl("Emergency or SOS", "LBL_EMERGENCY_SOS_TXT"));


        String LBL_MENU_WAY_BILL = generalFunc.retrieveLangLBl("Way Bill", "LBL_MENU_WAY_BILL");
        if (REQUEST_TYPE.equals(Utils.CabGeneralType_UberX)) {


            menu.findItem(R.id.menu_specialInstruction).setVisible(true);
            menu.findItem(R.id.menu_waybill_trip).setTitle(LBL_MENU_WAY_BILL).setVisible(false);
            if (data_trip.get("eFareType").equals(Utils.CabFaretypeRegular)) {
                menu.findItem(R.id.menu_passenger_detail).setTitle(generalFunc.retrieveLangLBl("View User detail", "LBL_VIEW_USER_DETAIL")).setVisible(true);
                menu.findItem(R.id.menu_sos).setVisible(false);
                menu.findItem(R.id.menu_call).setVisible(false);
                menu.findItem(R.id.menu_message).setVisible(false);
            } else {
                menu.findItem(R.id.menu_passenger_detail).setVisible(false);
                menu.findItem(R.id.menu_call).setVisible(true);
                menu.findItem(R.id.menu_message).setVisible(true);
                menu.findItem(R.id.menu_sos).setVisible(true);
            }


        } else if (REQUEST_TYPE.equals("Bidding")) {
            menu.findItem(R.id.menu_passenger_detail).setVisible(false);
            menu.findItem(R.id.menu_cancel_trip).setVisible(false);
            menu.findItem(R.id.menu_waybill_trip).setVisible(false);
            menu.findItem(R.id.menu_call).setVisible(true);
            menu.findItem(R.id.menu_message).setVisible(true);
            menu.findItem(R.id.menu_sos).setVisible(true);
            menu.findItem(R.id.menu_bidding).setTitle(generalFunc.retrieveLangLBl("", "LBL_REQUESTED_BIDDING")).setVisible(true);
        } else {

            boolean eFlyEnabled = data_trip.get("eFly").equalsIgnoreCase("Yes");
            boolean isWayBillEnabled = generalFunc.getJsonValue("WAYBILL_ENABLE", obj_userProfile) != null && generalFunc.getJsonValueStr("WAYBILL_ENABLE", obj_userProfile).equalsIgnoreCase("yes");
            if (!data_trip.get("eHailTrip").equalsIgnoreCase("Yes")) {
                menu.findItem(R.id.menu_passenger_detail).setVisible(true);
                menu.findItem(R.id.menu_call).setVisible(false);
                menu.findItem(R.id.menu_message).setVisible(false);
                menu.findItem(R.id.menu_sos).setVisible(false);
                menu.findItem(R.id.menu_waybill_trip).setTitle(LBL_MENU_WAY_BILL).setVisible(isWayBillEnabled && !eFlyEnabled);
            } else {
                menu.findItem(R.id.menu_passenger_detail).setVisible(false);
                menu.findItem(R.id.menu_call).setVisible(false);
                menu.findItem(R.id.menu_message).setVisible(false);
                menu.findItem(R.id.menu_sos).setVisible(false);
                menu.findItem(R.id.menu_waybill_trip).setTitle(LBL_MENU_WAY_BILL).setVisible(isWayBillEnabled && !eFlyEnabled);
            }


        }

        if (isVideoCall) {
            menu.findItem(R.id.menu_sos).setVisible(false);
        }

        if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            menu.findItem(R.id.menu_passenger_detail).setVisible(false);
            if (generalFunc.getJsonValueStr("vTripStatus", obj_userProfile).equals("On Going Trip")) {
                wayBillImgView.setVisibility(View.VISIBLE);
                menu.findItem(R.id.menu_waybill_trip).setTitle(LBL_MENU_WAY_BILL).setVisible
                        (false);
                menu.findItem(R.id.menu_cancel_trip).setVisible(false);

            }
        }

        Utils.setMenuTextColor(menu.findItem(R.id.menu_passenger_detail), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_cancel_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_waybill_trip), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_bidding), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_sos), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_call), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_message), getResources().getColor(R.color.black));
        Utils.setMenuTextColor(menu.findItem(R.id.menu_specialInstruction), getResources().getColor(R.color.black));
        return true;
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_MENU) {

            return true;
        }

        // let the system handle all other key events
        return super.onKeyDown(keyCode, event);
    }

    private void getDeclineReasonsList() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetCancelReasons");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("eUserType", Utils.app_type);
        parameters.put("eJobType", generalFunc.getJsonValue("eJobType", generalFunc.getJsonValue("TripDetails", obj_userProfile.toString())));

        parameters.put("iTripId", tripId);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (Utils.checkText(responseString)) {
                JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {
                    showDeclineReasonsAlert(responseStringObj);
                } else {
                    String message = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                    if (message.equals("DO_RESTART") || message.equals(Utils.GCM_FAILED_KEY) || message.equals(Utils.APNS_FAILED_KEY)
                            || message.equals("LBL_SERVER_COMM_ERROR")) {

                        MyApp.getInstance().restartWithGetDataApp();
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", message));
                    }
                }

            } else {
                generalFunc.showError();
            }

        });

    }

    String selectedItemId = "";

    String titleDailog = "";
    int selCurrentPosition = -1;

    public void showDeclineReasonsAlert(JSONObject responseString) {
        selCurrentPosition = -1;
        if (data_trip.get("eType").equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_TRIP");
        } else if (data_trip.get("eType").equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_JOB");
        } else {
            titleDailog = generalFunc.retrieveLangLBl("", "LBL_CANCEL_DELIVERY");
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = this.getLayoutInflater();
        View dialogView = inflater.inflate(R.layout.decline_order_dialog_design, null);
        builder.setView(dialogView);

        MaterialEditText reasonBox = (MaterialEditText) dialogView.findViewById(R.id.inputBox);
        RelativeLayout commentArea = (RelativeLayout) dialogView.findViewById(R.id.commentArea);
        MyUtils.editBoxMultiLine(reasonBox);
        reasonBox.setHideUnderline(true);
        if (generalFunc.isRTLmode()) {
            reasonBox.setPaddings(0, 0, (int) getResources().getDimension(R.dimen._10sdp), 0);
        } else {
            reasonBox.setPaddings((int) getResources().getDimension(R.dimen._10sdp), 0, 0, 0);
        }
        reasonBox.setVisibility(View.GONE);
        commentArea.setVisibility(View.GONE);
        reasonBox.setBothText("", generalFunc.retrieveLangLBl("", "LBL_ENTER_REASON"));


        ArrayList<HashMap<String, String>> sub_list = new ArrayList<HashMap<String, String>>();
        JSONArray arr_msg = generalFunc.getJsonArray(Utils.message_str, responseString);
        if (arr_msg != null) {

            for (int i = 0; i < arr_msg.length(); i++) {
                JSONObject obj_tmp = generalFunc.getJsonObject(arr_msg, i);
                HashMap<String, String> datamap = new HashMap<>();
                datamap.put("title", generalFunc.getJsonValueStr("vTitle", obj_tmp));
                datamap.put("id", generalFunc.getJsonValueStr("iCancelReasonId", obj_tmp));
                sub_list.add(datamap);
            }

            HashMap<String, String> othermap = new HashMap<>();
            othermap.put("title", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
            othermap.put("id", "");
            sub_list.add(othermap);

            // AppCompatSpinner spinner = (AppCompatSpinner) dialogView.findViewById(R.id.declineReasonsSpinner);
            MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);
            MTextView submitTxt = (MTextView) dialogView.findViewById(R.id.submitTxt);
            MTextView subTitleTxt = (MTextView) dialogView.findViewById(R.id.subTitleTxt);
            MTextView errorTextView = dialogView.findViewById(R.id.errorTextView);
            ImageView cancelImg = (ImageView) dialogView.findViewById(R.id.cancelImg);
            subTitleTxt.setText(titleDailog);

            submitTxt.setText(generalFunc.retrieveLangLBl("", "LBL_YES"));
            cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO"));
            MTextView declinereasonBox = (MTextView) dialogView.findViewById(R.id.declinereasonBox);
            declinereasonBox.setText("-- " + generalFunc.retrieveLangLBl("", "LBL_SELECT_CANCEL_REASON") + " --");
            submitTxt.setClickable(false);
            submitTxt.setTextColor(getResources().getColor(R.color.gray_holo_light));

            submitTxt.setOnClickListener(v -> {

                if (selCurrentPosition == -1) {
                    return;
                }

                if (!Utils.checkText(reasonBox) && selCurrentPosition == (sub_list.size() - 1)) {
                    errorTextView.setVisibility(View.VISIBLE);
                    errorTextView.setText(generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                    return;
                }

                boolean isTempTripStart = isTripStart;
                if (isVideoCall) {
                    isTempTripStart = false;
                }

                new CancelTripDialog(getActContext(), data_trip, generalFunc, sub_list.get(selCurrentPosition).get("id"), Utils.getText(reasonBox), isTempTripStart, reasonBox.getText().toString().trim(), userLocation != null ? userLocation : GetLocationUpdates.getInstance().getLastLocation());

            });
            cancelTxt.setOnClickListener(v -> {
                Utils.hideKeyboard(getActContext());
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });

            cancelImg.setOnClickListener(v -> {
                Utils.hideKeyboard(getActContext());
                errorTextView.setVisibility(View.GONE);
                dialog_declineOrder.dismiss();
            });

            declinereasonBox.setOnClickListener(v -> OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SELECT_REASON"), sub_list, OpenListView.OpenDirection.CENTER, true, position -> {


                selCurrentPosition = position;
                HashMap<String, String> mapData = sub_list.get(position);
                errorTextView.setVisibility(View.GONE);
                selectedItemId = mapData.get("id");
                declinereasonBox.setText(mapData.get("title"));
                if (selCurrentPosition == (sub_list.size() - 1)) {
                    reasonBox.setVisibility(View.VISIBLE);
                    commentArea.setVisibility(View.VISIBLE);
                } else {
                    reasonBox.setVisibility(View.GONE);
                    commentArea.setVisibility(View.GONE);
                }

                submitTxt.setClickable(true);
                submitTxt.setTextColor(getResources().getColor(R.color.white));


            }).show(selCurrentPosition, "title"));


            dialog_declineOrder = builder.create();
            dialog_declineOrder.setCancelable(false);
            dialog_declineOrder.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
            LayoutDirection.setLayoutDirection(dialog_declineOrder);
            if (dialog_declineOrder != null && !this.isFinishing()) {
                dialog_declineOrder.show();
            }
        } else {
            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));
        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        int itemId = item.getItemId();
        if (itemId == R.id.menu_passenger_detail) {
            if (REQUEST_TYPE.equals("Deliver")) {
                Bundle bn = new Bundle();
                bn.putString("TripId", data_trip.get("TripId"));
                bn.putSerializable("data_trip", data_trip);
                new ActUtils(getActContext()).startActWithData(ViewDeliveryDetailsActivity.class, bn);
            } else {
                new OpenPassengerDetailDialog(getActContext(), data_trip, generalFunc, false, new OpenPassengerDetailDialog.DialogListener() {
                    @Override
                    public void callClick() {
                        callArea.performClick();
                    }

                    @Override
                    public void msgClick() {
                        chatArea.performClick();
                    }
                });
            }

            return true;
        } else if (itemId == R.id.menu_cancel_trip) {
            getDeclineReasonsList();
//                new CancelTripDialog(getActContext(), data_trip, generalFunc, isTripStart);
            return true;
        } else if (itemId == R.id.menu_waybill_trip) {
            Bundle bn4 = new Bundle();
            bn4.putSerializable("data_trip", data_trip);
            new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn4);
            return true;
        } else if (itemId == R.id.menu_sos) {
            if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                SafetyTools.getInstance().initiate(getActContext(), generalFunc, tripId, "");
                SafetyTools.getInstance().safetyToolsDialog(false);
            } else {
                Bundle bn = new Bundle();
                bn.putString("TripId", tripId);
                new ActUtils(getActContext()).startActWithData(ConfirmEmergencyTapActivity.class, bn);
            }

            return true;
        } else if (itemId == R.id.menu_bidding) {
            Bundle bn1 = new Bundle();
            bn1.putString("iBiddingPostId", data_trip.get("TripId"));
            bn1.putBoolean("isViewOnly", true);
            new ActUtils(getActContext()).startActWithData(BiddingViewDetailsActivity.class, bn1);
            return true;
        } else if (itemId == R.id.menu_call) {
            callArea.performClick();
            return true;
        } else if (itemId == R.id.menu_message) {
            chatArea.performClick();
            return true;
        } else if (itemId == R.id.menu_specialInstruction) {
            String last_trip_data = generalFunc.getJsonValue("TripDetails", obj_userProfile.toString());
            String moreServices = generalFunc.getJsonValue("moreServices", last_trip_data);
            if (!moreServices.equalsIgnoreCase("") && moreServices.equalsIgnoreCase("Yes")) {
                Bundle bundle = new Bundle();
                bundle.putString("iTripId", data_trip.get("iTripId"));
                new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bundle);

            } else {
                String tUserComment = data_trip.get("tUserComment");
                if (Utils.checkText(tUserComment)) {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"), tUserComment);
                } else {
                    generalFunc.showGeneralMessage(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"), generalFunc.retrieveLangLBl("", "LBL_NO_SPECIAL_INSTRUCTION"));

                }
            }


            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    public Context getActContext() {
        return WorkingtrekActivity.this; // Must be context of activity not application
    }

    public void addDestination(final String latitude, final String longitude, final String address) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "addDestination");
        parameters.put("Latitude", latitude);
        parameters.put("Longitude", longitude);
        parameters.put("Address", address);
        parameters.put("eConfirmByUser", eConfirmByUser);
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.userType);
        parameters.put("TripId", tripId);
        parameters.put("eTollConfirmByUser", eTollConfirmByUser);
        parameters.put("fTollPrice", tollamount + "");
        parameters.put("vTollPriceCurrencyCode", tollcurrancy);
        String tollskiptxt = "";
        if (istollIgnore) {
            tollamount = 0;
            tollskiptxt = "Yes";

        } else {
            tollskiptxt = "No";
        }
        parameters.put("eTollSkipped", tollskiptxt);
        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    if (istollIgnore) {
                        MyApp.getInstance().refreshView(this, responseString);
                        return;
                    }

                    setDestinationPoint(latitude, longitude, address, true);

                    Location destLoc = new Location("gps");
                    destLoc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, latitude));
                    destLoc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, longitude));

                    if (updateDirections == null) {
                        updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
                        updateDirections.scheduleDirectionUpdate();
                    } else {
                        updateDirections.changeDestLoc(destLoc);
                        updateDirections.updateDirections();

                    }
                    addDestinationMarker();
                } else {

                    String msg_str = generalFunc.getJsonValue(Utils.message_str, responseString);


                    if (msg_str.equalsIgnoreCase("LBL_DROP_LOCATION_NOT_ALLOW")) {
                        tollamount = 0.0;
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DROP_LOCATION_NOT_ALLOW"));
                        return;
                    }

                    if (msg_str.equalsIgnoreCase("Yes")) {
                        if (generalFunc.getJsonValue("SurgePrice", responseString).equalsIgnoreCase("")) {
                            openFixChargeDialog(responseString, false);
                        } else {
                            openFixChargeDialog(responseString, true);
                        }
                        return;
                    }

                    if (tollamount != 0.0 && tollamount != 0 && tollamount != 0.00) {

                        TollTaxDialog();

                        return;
                    }


                    if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                        generalFunc.restartApp();
                    } else {
                        generalFunc.showGeneralMessage("",
                                generalFunc.retrieveLangLBl("", msg_str));
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void openFixChargeDialog(String responseString, boolean isSurCharge) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        builder.setTitle("");
        builder.setCancelable(false);
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.surge_confirm_design, null);
        builder.setView(dialogView);
        MTextView payableAmountTxt;
        MTextView payableTxt;

        ((MTextView) dialogView.findViewById(R.id.headerMsgTxt)).setText(generalFunc.retrieveLangLBl("", generalFunc.retrieveLangLBl("", "LBL_FIX_FARE_HEADER")));


        ((MTextView) dialogView.findViewById(R.id.tryLaterTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_TRY_LATER"));
        payableTxt = (MTextView) dialogView.findViewById(R.id.payableTxt);
        payableAmountTxt = (MTextView) dialogView.findViewById(R.id.payableAmountTxt);
        if (!generalFunc.getJsonValue("fFlatTripPricewithsymbol", responseString).equalsIgnoreCase("")) {
            payableAmountTxt.setVisibility(View.VISIBLE);
            payableTxt.setVisibility(View.GONE);

            if (isSurCharge) {
                payableAmount = generalFunc.getJsonValue("fFlatTripPricewithsymbol", responseString) + " " + "(" + generalFunc.retrieveLangLBl("", "LBL_AT_TXT") + " " +
                        generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("SurgePrice", responseString)) + ")";
                ((MTextView) dialogView.findViewById(R.id.surgePriceTxt)).setText(generalFunc.convertNumberWithRTL(payableAmount));
            } else {
                payableAmount = generalFunc.getJsonValue("fFlatTripPricewithsymbol", responseString);
                ((MTextView) dialogView.findViewById(R.id.surgePriceTxt)).setText(generalFunc.convertNumberWithRTL(payableAmount));

            }
        } else {
            payableAmountTxt.setVisibility(View.GONE);
            payableTxt.setVisibility(View.VISIBLE);

        }

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));
        btn_type2.setId(Utils.generateViewId());

        btn_type2.setOnClickListener(view -> {
            alertDialog_surgeConfirm.dismiss();
            eConfirmByUser = "Yes";
            addDestination(latitude, longitirude, address);
        });
        (dialogView.findViewById(R.id.tryLaterTxt)).setOnClickListener(view -> {
            tollamount = 0.0;
            alertDialog_surgeConfirm.dismiss();

        });

        alertDialog_surgeConfirm = builder.create();
        alertDialog_surgeConfirm.setCancelable(false);
        alertDialog_surgeConfirm.setCanceledOnTouchOutside(false);
        LayoutDirection.setLayoutDirection(alertDialog_surgeConfirm);
        alertDialog_surgeConfirm.show();
        int width = (int) (getResources().getDisplayMetrics().widthPixels * 0.85);
        int height = (int) (getResources().getDisplayMetrics().heightPixels * 0.40);

        alertDialog_surgeConfirm.getWindow().setLayout(width, height);
    }

    public void setDestinationPoint(String latitude, String longitude, String address, boolean isDestinationAdded) {
        double dest_lat = GeneralFunctions.parseDoubleValue(0.0, latitude);
        double dest_lon = GeneralFunctions.parseDoubleValue(0.0, longitude);

        destLocSearchArea.setVisibility(View.GONE);
        addressTxt.setVisibility(View.VISIBLE);
        (findViewById(R.id.navigationViewArea)).setVisibility(View.VISIBLE);
        try {
            if (data_trip.get("eTollSkipped").equalsIgnoreCase("yes")) {
                tollTxtView.setVisibility(View.VISIBLE);
            }
        } catch (Exception e) {

        }

        if (address.equals("")) {
            addressTxt.setText(generalFunc.retrieveLangLBl("Locating you", "LBL_LOCATING_YOU_TXT"));
            GetAddressFromLocation getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
            getAddressFromLocation.setLocation(dest_lat, dest_lon);
            getAddressFromLocation.setAddressList((address1, latitude1, longitude1, geocodeobject) -> addressTxt.setText(address1));

            getAddressFromLocation.execute();
        } else {
            addressTxt.setText(address);
        }

        navigateAreaUP.setOnClickListener(new setOnClickAct("" + dest_lat, "" + dest_lon));

        this.isDestinationAdded = isDestinationAdded;
        this.destLocLatitude = dest_lat;
        this.destLocLongitude = dest_lon;
    }

    public void setTripStart() {

        if (!TextUtils.isEmpty(isFrom) && imageType.equalsIgnoreCase("before")) {

            HashMap<String, String> paramsList = new HashMap<String, String>() {{
                put("type", "StartTrip");
                put("iDriverId", generalFunc.getMemberId());
                put("TripID", tripId);

                if (userLocation != null) {
                    put("vLatitude", "" + userLocation.getLatitude());
                    put("vLongitude", "" + userLocation.getLongitude());
                } else if (GetLocationUpdates.getInstance().getLastLocation() != null) {
                    Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();
                    put("vLatitude", "" + lastLocation.getLatitude());
                    put("vLongitude", "" + lastLocation.getLongitude());

                }


                put("iUserId", data_trip.get("PassengerId"));
                put("UserType", Utils.app_type);
                put("iMemberId", generalFunc.getMemberId());
                put("MemberType", Utils.app_type);
                put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                put("GeneralUserType", Utils.app_type);
                put("GeneralMemberId", generalFunc.getMemberId());
            }};

            new UploadProfileImage(this, selectedImagePath, Utils.TempProfileImageName, paramsList, imageType).execute();

        } else if (isFaceMaskVerification) {

            HashMap<String, String> paramsList = new HashMap<String, String>() {{
                put("type", "StartTrip");
                put("iDriverId", generalFunc.getMemberId());
                put("TripID", tripId);

                if (userLocation != null) {
                    put("vLatitude", "" + userLocation.getLatitude());
                    put("vLongitude", "" + userLocation.getLongitude());
                } else if (GetLocationUpdates.getInstance().getLastLocation() != null) {
                    Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();
                    put("vLatitude", "" + lastLocation.getLatitude());
                    put("vLongitude", "" + lastLocation.getLongitude());

                }


                put("iUserId", data_trip.get("PassengerId"));
                put("UserType", Utils.app_type);
                put("iMemberId", generalFunc.getMemberId());
                put("MemberType", Utils.app_type);
                put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                put("GeneralUserType", Utils.app_type);
                put("GeneralMemberId", generalFunc.getMemberId());
            }};

            new UploadProfileImage(this, safetyselectedImagePath, Utils.TempProfileImageName, paramsList, "uploadImageWithMask").execute();

        } else {


            HashMap<String, String> parameters = new HashMap<>();
            parameters.put("type", "StartTrip");
            parameters.put("iDriverId", generalFunc.getMemberId());
            parameters.put("TripID", tripId);

            if (userLocation != null) {
                parameters.put("vLatitude", "" + userLocation.getLatitude());
                parameters.put("vLongitude", "" + userLocation.getLongitude());
            } else if (GetLocationUpdates.getInstance().getLastLocation() != null) {
                parameters.put("vLatitude", "" + GetLocationUpdates.getInstance().getLastLocation().getLatitude());
                parameters.put("vLongitude", "" + GetLocationUpdates.getInstance().getLastLocation().getLongitude());
            }

            parameters.put("iUserId", data_trip.get("PassengerId"));
            parameters.put("UserType", Utils.app_type);
            if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                parameters.put("iTripDeliveryLocationId", iTripDeliveryLocationId);
            }
            if (isPoolRide) {
                MyApp.getInstance().ispoolRequest = true;
            }

            ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> startTripResponse(responseString));
            exeWebServer.setCancelAble(false);
        }
    }

    private void startTripResponse(String responseString) {
        MyApp.getInstance().ispoolRequest = false;
        if (responseString != null && !responseString.equals("")) {

            if (eType.equals("UberX") || eType.equals("Bidding")) {
                if (eType.equals("Bidding") && generalFunc.getJsonValueStr("ENABLE_OTP_AFTER_BIDDING", obj_userProfile).equalsIgnoreCase("Yes")) {
                    MyApp.getInstance().restartWithGetDataApp();
                } else {
                    getTripDeliveryLocations();
                }
            } else {
                try {
                    String eFareType = data_trip.get("eFareType");
                    if (eFareType != null && !eFareType.equals("")) {
                        if (eFareType.equals(Utils.CabFaretypeFixed)) {
                            getTripDeliveryLocations();
                        } else if (eFareType.equals(Utils.CabFaretypeHourly)) {
                            btntimer.setVisibility(View.VISIBLE);
                            getTripDeliveryLocations();

                        }
                    }
                } catch (Exception e) {
                    Logger.e("ExceptionResponse", "::" + e.toString());

                }
            }

            if (uploadImgAlertDialog != null) {
                uploadImgAlertDialog.cancel();
                uploadImgAlertDialog = null;
            }

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {

                if (uploadImgAlertDialog != null) {
                    uploadImgAlertDialog.cancel();
                    uploadImgAlertDialog = null;
                }

                closeuploadServicePicAlertBox();

                eInterCityButtonBgColor = generalFunc.getJsonValue("eInterCityButtonBgColor", generalFunc.getJsonValue("TripDetails", generalFunc.getJsonValue("USER_DATA", responseString)));
                currencetprice = generalFunc.getJsonValue("fVisitFee", responseString);
                if (REQUEST_TYPE.equals("Deliver")) {
                    SITE_TYPE = generalFunc.getJsonValue("SITE_TYPE", responseString);
                    deliveryVerificationCode = generalFunc.getJsonValue(Utils.message_str, responseString);
                }
                if (data_trip.get("eFareType").equals(Utils.CabFaretypeHourly)) {
                    TripTimeId = generalFunc.getJsonValue("iTripTimeId", responseString);
//                    callsetTimeApi(true);
                    btntimer.setVisibility(View.VISIBLE);
                    Log.e("countdownstartCalled", ":: 2");
                    countDownStart();
                }
                ufxtripstatus = generalFunc.getJsonValueStr("vTripStatus", generalFunc.getJsonObject("USER_DATA", responseString));
                configTripStartView();
                //endTripSlideButton.setVisibility(View.VISIBLE);
                if (generalFunc.getJsonValue("ENABLE_INTRANSIT_SHOPPING_SYSTEM", obj_userProfile).equals("Yes") && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) &&
                        !data_trip.get("eRental").equalsIgnoreCase("Yes") && !data_trip.get("ePoolRide").equalsIgnoreCase("Yes") &&
                        data_trip.get("eTransit").equalsIgnoreCase("Yes")) {
                    transitConfigTripStartView();
                }

                GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("TripId"));
            } else {
                String msg_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                    generalFunc.restartApp();
                } else {
                    startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
                    generalFunc.showGeneralMessage("",
                            generalFunc.retrieveLangLBl("", msg_str));
                }

            }
        } else {
            generalFunc.showError();
            startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
        }

    }

    public void transitConfigTripStartView() {

        newbtn_timer.setVisibility(View.VISIBLE);
//        btntimer.performClick();
        holdWaitArea.setVisibility(View.VISIBLE);
//        callsetTimeApi(false);
        //countDownStart();
        transitCountDownStart();

        String TimeState = data_trip.get("TimeState");
        if (TimeState != null && !TimeState.equals("")) {
            if (TimeState.equalsIgnoreCase("Resume")) {

                isresume = true;
                newbtn_timer.setText(generalFunc.retrieveLangLBl("", "LBL_STOP_WAITING_TIMER"));
                newbtn_timer.setVisibility(View.VISIBLE);

            } else {
                if (timerrequesttask != null) {
                    timerrequesttask.stopRepeatingTask();
                    timerrequesttask = null;
                }

                isresume = false;
                newbtn_timer.setText(LBL_WAIT);
                newbtn_timer.setVisibility(View.VISIBLE);

            }
        } else {
            newbtn_timer.setText(LBL_WAIT);
        }

        String TotalSeconds = data_trip.get("TotalSeconds");
        if (TotalSeconds != null && !TotalSeconds.equals("")) {
            i = Integer.parseInt(TotalSeconds);
            setTransitTimerValues();
        }

        String iTripTimeId = data_trip.get("iTripTimeId");
        if (iTripTimeId != null && !iTripTimeId.equals("")) {
            TripTimeId = iTripTimeId;
            //  countDownStart();
        }
        isTripStart = true;
        generalFunc.storeData("PROVIDER_STATUS_MODE", "begin");
        startTripSlideButton.setVisibility(View.GONE);
        endTripSlideButton.setVisibility(View.VISIBLE);

        if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
            dropAllIconArea.setVisibility(View.VISIBLE);
        }
        isendslide = true;
        invalidateOptionsMenu();
    }

    public void internetConnection(boolean isNetConnection) {
        if (rtmpFrag != null) {
            rtmpFrag.internetConnection(isNetConnection, 0);
        }
    }

    @Override
    public boolean onDown(MotionEvent event) {
        Logger.d("Gesture", "onDown: " + event.toString());
        return true;
    }

    @Override
    public boolean onFling(MotionEvent e1, MotionEvent e2, float velocityX, float velocityY) {
        if (Math.abs(velocityX) > Math.abs(velocityY)) {
            if (velocityX < -500) {
                // Swipe left
                Logger.d("Gesture", "Swipe left");
                if (rtmpFrag != null) {
                    rtmpFrag.handleMinimize(true, true);
                    rtmpFragContainer.setX(0);
                }
            } else if (velocityX > 200) {
                // Swipe right
                Logger.d("Gesture", "Swipe right");
                if (rtmpFrag != null) {
                    rtmpFrag.handleMinimize(true, false);
                    rtmpFragContainer.setX(metrics.widthPixels - (int) (rtmpFragContainer.getMeasuredWidth() / 5));
                }
            }
        } else {
            if (velocityY < 0) {
                // Swipe up
                Logger.d("Gesture", "Swipe up");
            } else {
                // Swipe down
                Logger.d("Gesture", "Swipe down");
            }
        }
        return true;
    }

    @Override
    public void onLongPress(MotionEvent event) {
        Logger.d("Gesture", "onLongPress: " + event.toString());
    }

    @Override
    public boolean onScroll(MotionEvent event1, MotionEvent event2, float distanceX, float distanceY) {
        Logger.d("Gesture", "onScroll: " + event1.toString() + event2.toString());
        return true;
    }

    @Override
    public void onShowPress(MotionEvent event) {
        Logger.d("Gesture", "onShowPress: " + event.toString());
    }

    @Override
    public boolean onSingleTapUp(MotionEvent event) {
        Logger.d("Gesture", "onSingleTapUp: " + event.toString());
        return true;
    }

    @Override
    public boolean onDoubleTap(MotionEvent event) {
        Logger.d("Gesture", "onDoubleTap: " + event.toString());
        return true;
    }

    @Override
    public boolean onDoubleTapEvent(MotionEvent event) {
        Logger.d("Gesture", "onDoubleTapEvent: " + event.toString());
        return true;
    }

    @Override
    public boolean onSingleTapConfirmed(MotionEvent event) {
        Logger.d("Gesture", "onSingleTapConfirmed: " + event.toString());
        return true;
    }

    public void setVideoViewX() {
        rtmpFragContainer.setX(metrics.widthPixels - (rtmpFragContainer.getMeasuredWidth() * 5));
    }

    public void setFullScreen(boolean isExpand) {
        isExpandedVideoView = isExpand;
        if (isExpand) {
            oldPositionX = (int) rtmpFragContainer.getX();
            oldPositionY = (int) rtmpFragContainer.getY();
        }
        if (isExpand) {
            rtmpFragContainer.setX(0);
            rtmpFragContainer.setY(0);
        } else {
            rtmpFragContainer.setX(oldPositionX);
            rtmpFragContainer.setY(oldPositionY);
        }
    }

    public void reStartRTMPFrg(boolean isPlay) {
        if (rtmpFrag != null) {
            rtmpFragContainer.removeView(rtmpFrag.getView());
            rtmpFrag.internetConnection(false, 0);
            rtmpFrag = null;
            rtmpFrag = new RTMPServiceFragment(isPlay);
            rtmpFrag.newInstance(this);
            getSupportFragmentManager().beginTransaction().replace(rtmpFragContainer.getId(), rtmpFrag).commit();
        }
    }

    public void configTripStartView() {
        rtmpFragContainer = findViewById(R.id.rtmpFragContainer);
        rtmpFragContainer.setY((int) (metrics.heightPixels / 2.8));
        rtmpFragContainer.setX(30);
        if (!MyApp.getInstance().isGetDetailCall && !PermissionHandlers.getInstance().getVisibilityPager() && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            if (generalFunc.getJsonValueStr("ENABLE_PROVIDER_CAMERA_REC", obj_userProfile).equalsIgnoreCase("Yes")) {
                if (rtmpFrag == null) {
                    rtmpFrag = new RTMPServiceFragment(true);
                    rtmpFrag.newInstance(this);

                    rtmpFragContainer.setOnTouchListener(new View.OnTouchListener() {
                        int xPos = 0, yPos = (int) rtmpFragContainer.getY(), initialX, initialY;
                        float initialTouchX, initialTouchY;

                        @Override
                        public boolean onTouch(View v, MotionEvent event) {
                            if (isExpandedVideoView) {
                                return false;
                            }
                            gestureDetector.onTouchEvent(event);

                            switch (event.getAction()) {
                                case ACTION_DOWN -> {
                                    initialX = (int) v.getX();
                                    initialY = (int) v.getY();
                                    initialTouchX = event.getRawX();
                                    initialTouchY = event.getRawY();
                                    return true;
                                }
                                case ACTION_MOVE -> {
                                    xPos = initialX + (int) (event.getRawX() - initialTouchX);
                                    yPos = initialY + (int) (event.getRawY() - initialTouchY);

                                    if (xPos < 0) {
                                        xPos = 0;
                                    }
                                    if (yPos < 0) {
                                        yPos = 0;
                                    }
                                    if (xPos > (metrics.widthPixels - rtmpFragContainer.getMeasuredWidth())) {
                                        xPos = metrics.widthPixels - rtmpFragContainer.getMeasuredWidth();
                                    }
                                    if (yPos > metrics.heightPixels - rtmpFragContainer.getMeasuredHeight()) {
                                        yPos = metrics.heightPixels - rtmpFragContainer.getMeasuredHeight();
                                    }
                                    v.setX(xPos);
                                    v.setY(yPos);

                                    return true;
                                }
                            }
                            return false;
                        }
                    });
                    getSupportFragmentManager().beginTransaction().replace(rtmpFragContainer.getId(), rtmpFrag).commit();
                }
            }
        }

        isresume = true;
        //  btntimer.setVisibility(View.VISIBLE);
        //countDownStart();

        isTripStart = true;
        generalFunc.storeData("PROVIDER_STATUS_MODE", "begin");
        startTripSlideButton.setVisibility(View.GONE);
        endTripSlideButton.setVisibility(View.VISIBLE);
        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            if (ufxtripstatus.equals("On Going Trip")) {
                if (menu != null) {
                    onCreateOptionsMenu(menu);
                }

            }
        }
        if (isInterCity && isInterCityRoundTrip) {
            endTripSlideButton.setButtonText(LBL_BTN_SLIDE_INTERCITY_MARKED_AS_REACH);
            if (Utils.checkText(eInterCityButtonBgColor)) {
                endTripSlideButton.setBackgroundColor(Color.parseColor(eInterCityButtonBgColor));
            }
        }

        if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint) {
            dropAllIconArea.setVisibility(View.VISIBLE);
        }
        isendslide = true;
        invalidateOptionsMenu();


        if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {

            if (isendslide || getPasggerTripStatus().equals("On Going Trip")) {
                wayBillImgView.setVisibility(View.VISIBLE);
            }
        }
    }

    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        MenuItem item = menu.findItem(R.id.menu_waybill_trip);
        MenuItem item1 = menu.findItem(R.id.menu_cancel_trip);
        if (wayBillImgView.getVisibility() == View.VISIBLE) {
            item.setVisible(false);
            item1.setVisible(false);
        }
        return super.onPrepareOptionsMenu(menu);
    }

    public void cancelTrip(String reason, String comment) {
        isTripCancelPressed = true;
        this.reason = reason;
        this.comment = comment;

        if ((eType.equals("UberX") || eType.equals("Bidding")) && data_trip.get("eAfterUpload").equalsIgnoreCase("Yes")
                && data_trip != null) {
            //&& generalFunc.retrieveValue(Utils.PHOTO_UPLOAD_SERVICE_ENABLE_KEY).equalsIgnoreCase("Yes")) {
            takeAndUploadPic(getActContext(), "after");
        } else {
            endTrip();
        }
        //endTrip();
    }

    public void endTrip() {

        if (!REQUEST_TYPE.equals(Utils.eType_Multi_Delivery)) {
           /* if (eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId")) && isDropAll) {
                buildMsgOnDropAllBtn();
            }else {*/
            endTripFinal();
            //}
        } else {
            if (data_trip.containsKey("ePaymentByReceiver") && data_trip.get("ePaymentByReceiver").trim().equalsIgnoreCase("Yes")) {
                buildMsgOnEndBtn();
            } else {
                endTripFinal();
            }
        }

    }

    private void openEnterOtpView() {
        if (dialog_verify_via_otp != null) {
            dialog_verify_via_otp.dismiss();
            dialog_verify_via_otp = null;
        }
        dialog_verify_via_otp = new Dialog(getActContext(), R.style.ImageSourceDialogStyle);
        dialog_verify_via_otp.setContentView(R.layout.verify_with_otp_layout);
        MTextView titleTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.titleTxt);
        MTextView cancelTxt = (MTextView) dialog_verify_via_otp.findViewById(R.id.cancelTxt);
        MTextView verifyOtpNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpNote);
        MTextView verifyOtpValidationNote = (MTextView) dialog_verify_via_otp.findViewById(R.id.verifyOtpValidationNote);
        LinearLayout OtpAddArea = (LinearLayout) dialog_verify_via_otp.findViewById(R.id.OtpAddArea);
        MaterialEditText otpBox = (MaterialEditText) dialog_verify_via_otp.findViewById(R.id.otpBox);
        OtpView otp_view = (OtpView) dialog_verify_via_otp.findViewById(R.id.otp_verify_view);
        MButton btn_type2 = ((MaterialRippleLayout) dialog_verify_via_otp.findViewById(R.id.btn_type2)).getChildView();

        if (generalFunc.isRTLmode()) {
            otp_view.setTextAlignment(View.TEXT_ALIGNMENT_VIEW_START);
        }
        int vRandomCode = generalFunc.parseIntegerValue(4, data_trip.get("vRandomCode"));
        String LBL_OTP_INVALID_TXT = generalFunc.retrieveLangLBl("", "LBL_OTP_INVALID_TXT");
        if (vRandomCode <= 6) {
            OtpAddArea.setVisibility(View.VISIBLE);
            otpBox.setVisibility(View.GONE);
            verifyOtpValidationNote.setText(LBL_OTP_INVALID_TXT);
            otp_view.setItemCount(generalFunc.parseIntegerValue(4, String.valueOf(vRandomCode)));
        } else {
            otpBox.setBothText("", generalFunc.retrieveLangLBl("OTP", "LBL_ENTER_OTP_TITLE_TXT"));
            OtpAddArea.setVisibility(View.GONE);
            otpBox.setVisibility(View.VISIBLE);
            otpBox.setInputType(InputType.TYPE_CLASS_NUMBER);
        }

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));

        titleTxt.setText(generalFunc.retrieveLangLBl("Verify OTP", "LBL_OTP_VERIFICATION_TITLE_TXT"));
        verifyOtpNote.setText(generalFunc.retrieveLangLBl("Ask user to provide you an OTP.", "LBL_OTP_VERIFICATION_DESCRIPTION_TXT"));
        btn_type2.setEnabled(false);
//        btn_type2.setBackgroundColor(getResources().getColor(R.color.gray));
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

        cancelTxt.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            isOtpVerified = false;
            isOtpVerificationDenied = true;
            if (dialog_verify_via_otp != null) {
                dialog_verify_via_otp.dismiss();
                dialog_verify_via_otp = null;
            }

            if (startTripSlideButton.getVisibility() == View.VISIBLE) {
                startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
            }
            if (endTripSlideButton.getVisibility() == View.VISIBLE) {
                endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
            }
        });

        String vText = data_trip.get("vText");
        Logger.d("MD5_HASH", "Original  Values is ::" + vText);
        btn_type2.setOnClickListener(v -> {
            Utils.hideKeyboard(getActContext());
            if (OtpAddArea.getVisibility() == View.VISIBLE) {
                String finalCode = Utils.getText(otp_view);

                boolean isCorrectCOde = Utils.checkText(finalCode) && generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde || Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {
                    verifyOtpValidationNote.setVisibility(View.GONE);
                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }

                    isOtpVerified = true;
                    if (REQUEST_TYPE.equalsIgnoreCase("Bidding")) {
                        if (startTripSlideButton.getVisibility() == View.VISIBLE) {
                            updateBiddingTaskStatus("Ongoing");
                        }
                        if (endTripSlideButton.getVisibility() == View.VISIBLE) {
                            updateBiddingTaskStatus("Finished");
                        }
                    } else {
                        startTrip();
                    }
                } else {
                    verifyOtpValidationNote.setVisibility(View.VISIBLE);
                }
            } else {
                String finalCode = Utils.getText(otpBox);
                boolean isCorrectCOde = Utils.checkText(finalCode) && generalFunc.convertOtpToMD5(finalCode).equalsIgnoreCase(vText);
                boolean isCodeEntered = isCorrectCOde || Utils.setErrorFields(otpBox, LBL_OTP_INVALID_TXT);

                otp_view.setLineColor(isCorrectCOde ? getActContext().getResources().getColor(R.color.appThemeColor_1) : getActContext().getResources().getColor(R.color.red));

                if (isCodeEntered) {

                    if (dialog_verify_via_otp != null) {
                        dialog_verify_via_otp.dismiss();
                        dialog_verify_via_otp = null;
                    }
                    isOtpVerified = true;
                    startTrip();
                }
            }
        });
        otp_view.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < otp_view.getItemCount()) {
                    btn_type2.setEnabled(false);
                    verifyOtpValidationNote.setVisibility(View.GONE);
                    otp_view.setLineColor(getResources().getColor(R.color.gray));
//                    btn_type2.setBackgroundColor(getResources().getColor(R.color.gray));
                }
            }
        });

        otpBox.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                if (s.length() < vRandomCode) {
                    btn_type2.setEnabled(false);
                } else {
                    btn_type2.setEnabled(true);
                }
            }
        });

        otp_view.setOtpCompletionListener(otp -> {
            verifyOtpValidationNote.setVisibility(View.GONE);
            otp_view.setLineColor(getResources().getColor(R.color.appThemeColor_1));
            btn_type2.setEnabled(true);
        });
        otp_view.setCursorVisible(true);
        dialog_verify_via_otp.setCanceledOnTouchOutside(false);
        Window window = dialog_verify_via_otp.getWindow();
        window.setGravity(Gravity.BOTTOM);
        window.setLayout(ViewGroup.LayoutParams.FILL_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        dialog_verify_via_otp.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        LayoutDirection.setLayoutDirection(dialog_verify_via_otp);
        dialog_verify_via_otp.show();
    }


    public void buildMsgOnEndBtn() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
                endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
            } else {
                endTripFinal();
                generateAlert.closeAlertBox();
            }

        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_MULTI_PAYMENT_COLLECTED_MSG_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("Cancel", "LBL_CANCEL_TXT"));

        generateAlert.showAlertBox();
    }

    private void endTripFinal() {

        if (userLocation == null) {
            generalFunc.showMessage(dropAllAreaUP, generalFunc.retrieveLangLBl("", "LBL_NO_LOCATION_FOUND_TXT"));
            return;
        }

        ArrayList<HashMap<String, String>> store_locations = GetLocationUpdates.getInstance().getListOfTripLocations();
        ArrayList<String> store_locations_latitude = new ArrayList<>();
        ArrayList<String> store_locations_longitude = new ArrayList<>();


        if (!store_locations.isEmpty()) {
            for (int i = 0; i < store_locations.size(); i++) {
                store_locations_latitude.add("" + store_locations.get(i).get("Latitude"));
                store_locations_longitude.add("" + store_locations.get(i).get("Longitude"));
            }
        }

        if (userLocation != null) {
//            getDestinationAddress(store_locations_latitude, store_locations_longitude, "" + userLocation.getLatitude(), "" + userLocation.getLongitude());
            setTripEnd(store_locations_latitude, store_locations_longitude,
                    "" + userLocation.getLatitude(), "" + userLocation.getLongitude(), "");
        }
    }


    MyProgressDialog myPDialog;

    public void manageLoader() {
        if (isFinishing()) {
            return;
        }
        if (!intCheck.isNetworkConnected()) {
            if (myPDialog != null) {
                closeLoader(myPDialog);
            }

            if (startTripSlideButton.getVisibility() == View.VISIBLE) {
                startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
            }
            if (endTripSlideButton.getVisibility() == View.VISIBLE) {
                endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
            }
        }


    }

    public void getDestinationAddress(final ArrayList<String> store_locations_latitude, final ArrayList<String> store_locations_longitude,
                                      String endLatitude, String endLongitude) {

        if (!intCheck.isNetworkConnected()) {
            return;
        }
        myPDialog = showLoader();

        GetAddressFromLocation getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setLocation(generalFunc.parseDoubleValue(0.0, endLatitude), generalFunc.parseDoubleValue(0.0, endLongitude));
        getAddressFromLocation.setIsDestination(true);
        getAddressFromLocation.setAddressList((address, latitude, longitude, geocodeobject) -> {
            Logger.d("getDestinationAddress", "::called22222");
            closeLoader(myPDialog);

            if (address.equals("")) {
                generalFunc.showError();
                endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
            } else {
                setTripEnd(store_locations_latitude, store_locations_longitude,
                        "" + userLocation.getLatitude(), "" + userLocation.getLongitude(), address);
            }
        });
        getAddressFromLocation.execute();
    }

    public MyProgressDialog showLoader() {
        MyProgressDialog myPDialog = new MyProgressDialog(getActContext(), false, generalFunc.retrieveLangLBl("Loading", "LBL_LOADING_TXT"));
        myPDialog.show();

        return myPDialog;
    }

    public void closeLoader(MyProgressDialog myPDialog) {
        myPDialog.close();
    }

    public void setTripEnd(ArrayList<String> store_locations_latitude, ArrayList<String> store_locations_longitude, String endLatitude, String endLongitude, String destAddress) {

        if (!TextUtils.isEmpty(isFrom) && imageType.equalsIgnoreCase("after")) {

            HashMap<String, String> paramsList = new HashMap<String, String>() {{
                put("type", "ProcessEndTrip");
                put("TripId", tripId);
                put("latList", store_locations_latitude.toString().replace("[", "").replace("]", ""));
                put("lonList", store_locations_longitude.toString().replace("[", "").replace("]", ""));
                put("PassengerId", data_trip.get("PassengerId"));
                put("DriverId", generalFunc.getMemberId());
                put("dAddress", destAddress);
                put("dest_lat", endLatitude);
                put("dest_lon", endLongitude);
                put("waitingTime", "" + getWaitingTime());
                put("fMaterialFee", additonallist.get(0).toString());
                put("fMiscFee", additonallist.get(1).toString());
                put("fDriverDiscount", additonallist.get(2).toString());
                put("iMemberId", generalFunc.getMemberId());
                put("MemberType", Utils.app_type);
                put("tSessionId", generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
                put("GeneralUserType", Utils.app_type);
                put("GeneralMemberId", generalFunc.getMemberId());
                if (isTripCancelPressed) {
                    put("isTripCanceled", "true");
                    put("Comment", comment);
                    put("iCancelReasonId", selectedItemId);
                }
            }};

            new UploadProfileImage(this, selectedImagePath, Utils.TempProfileImageName, paramsList, imageType).execute();

        } else {
            HashMap<String, String> parameters = new HashMap<String, String>();
            parameters.put("type", "ProcessEndTrip");
            parameters.put("TripId", tripId);
            parameters.put("latList", store_locations_latitude.toString().replace("[", "").replace("]", ""));
            parameters.put("lonList", store_locations_longitude.toString().replace("[", "").replace("]", ""));
            parameters.put("PassengerId", data_trip.get("PassengerId"));
            parameters.put("DriverId", generalFunc.getMemberId());
            parameters.put("dAddress", destAddress);
            parameters.put("dest_lat", endLatitude);
            parameters.put("dest_lon", endLongitude);
            parameters.put("waitingTime", "" + getWaitingTime());
            if (generalFunc.getJsonValueStr("ENABLE_MANUAL_TOLL_FEATURE", obj_userProfile).equalsIgnoreCase("Yes")) {
                parameters.put("eIsTollEntered", "No");
            } else if (generalFunc.getJsonValueStr("ENABLE_OTHER_CHARGES_FEATURE", obj_userProfile).equalsIgnoreCase("Yes")) {
                parameters.put("eIsTollEntered", "No");
            } else {
                parameters.put("eIsTollEntered", "Yes");
            }

            if (data_trip.containsKey("vVehicleType")) {
                if (data_trip.get("vVehicleType").equalsIgnoreCase("Fly")) {
                    parameters.put("eIsTollEntered", "Yes");
                }
            }
            parameters.put("fMaterialFee", additonallist.get(0).toString());
            if (isVideoCall) {
                parameters.put("isVideoCallGenerate", MyUtils.getIsVideoCallGenerated());
            }
            parameters.put("fMiscFee", additonallist.get(1).toString());
            parameters.put("fDriverDiscount", additonallist.get(2).toString());
            if (eType.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                parameters.put("iTripDeliveryLocationId", iTripDeliveryLocationId);
            }
            /*Multistop over*/
            boolean isMspTrip = eType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId"));
            if (isMspTrip) {
                parameters.put("iStopId", data_trip.get("iStopId"));

                if (isDropAll) {
                    parameters.put("isDropAll", "" + isDropAll);
                }

            }
            if (isTripCancelPressed) {
                parameters.put("isTripCanceled", "true");
                parameters.put("Comment", comment);
                // parameters.put("Reason", reason);
                parameters.put("iCancelReasonId", selectedItemId);
            }

            ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> endTripResponse(responseString));

        }
    }

    private void endTripResponse(String responseString) {

        if (responseString != null && !responseString.equals("")) {
            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {
                MyUtils.setIsVideoCallGenerated("No");
                generalFunc.saveGoOnlineInfo();
                if (timerrequesttask != null) {
                    try {
                        timerrequesttask.stopRepeatingTask();
                        timerrequesttask = null;
                    } catch (Exception e) {

                    }
                }
                closeuploadServicePicAlertBox();
                stopProcess();

                GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");

                MyApp.getInstance().refreshView(this, responseString);

            } else {
                String msg_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                //Multi StopOver
                boolean isMspTrip = REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) && Utils.checkText(data_trip.get("iStopId"));
                if (msg_str.equalsIgnoreCase("DO_RESTART") && isMspTrip) {
                    MyApp.getInstance().restartWithGetDataApp(false);
                } else {
                    if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR")) {
                        generalFunc.restartApp();
                    } else {

                        endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
                        GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("TripId"));
                        generalFunc.showGeneralMessage("",
                                generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    }
                }

            }
        } else {
            GetLocationUpdates.getInstance().setTripStartValue(true, true, true, data_trip.get("TripId"));
            generalFunc.showError();
            endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
        }
    }

    private long getWaitingTime() {
        long waitingTime = 0;
        if (generalFunc != null && generalFunc.containsKey(Utils.DriverWaitingTime)) {
            waitingTime = GeneralFunctions.parseLongValue(0, generalFunc.retrieveValue(Utils.DriverWaitingTime)) / 60000;
        }
        return waitingTime;
    }

    @Override
    public void onBackPressed() {
        return;
    }

    @Override
    protected void onResume() {
        super.onResume();
        startLocationTracker();
        if (this.userLocation != null) {
            onLocationUpdate(this.userLocation);
        }

        if (updateDirections != null) {
            updateDirections.scheduleDirectionUpdate();
        }

        NavigationSensor.getInstance().configSensor(true);

        if (!MyApp.getInstance().isGetDetailCall && !PermissionHandlers.getInstance().getVisibilityPager() && eType.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            if (generalFunc.getJsonValueStr("ENABLE_PROVIDER_CAMERA_REC", obj_userProfile).equalsIgnoreCase("Yes")) {
                if (rtmpFrag != null) {
                    new Handler(Looper.getMainLooper()).postDelayed(() -> reStartRTMPFrg(true), 1000);
                }
            }
        }

    }

    @Override
    protected void onPause() {
        super.onPause();

        if (updateDirections != null) {
            updateDirections.releaseTask();
        }

        NavigationSensor.getInstance().configSensor(false);
    }

    public void stopProcess() {
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
    protected void onDestroy() {
        stopAllProcess();
        super.onDestroy();

        if (SafetyTools.getInstance() != null) {
            SafetyTools.getInstance().stopRecord();
        }
    }

    private void stopAllProcess() {
        stopProcess();
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == MyUtils.AUDIO_PERMISSION_REQ_CODE) {
            PermissionHandler.getInstance().initiateHandle(this, false, permissions, "", requestCode, requestCode);
        } else if (startTripSlideButton.getVisibility() == View.VISIBLE) {
            startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
        } else if (endTripSlideButton.getVisibility() == View.VISIBLE) {
            endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
        }
    }

    private void takeAndUploadPic(final Context mContext, final String picType) {
        imageType = picType;
        isFrom = "";
        selectedImagePath = "";

        uploadServicePicAlertBox = new Dialog(mContext, R.style.Theme_Dialog);
        uploadServicePicAlertBox.requestWindowFeature(Window.FEATURE_NO_TITLE);
        uploadServicePicAlertBox.setCancelable(false);
        uploadServicePicAlertBox.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        uploadServicePicAlertBox.setContentView(R.layout.design_upload_service_pic);

        MTextView titleTxt = (MTextView) uploadServicePicAlertBox.findViewById(R.id.titleTxt);
        final MTextView uploadStatusTxt = (MTextView) uploadServicePicAlertBox.findViewById(R.id.uploadStatusTxt);
        MTextView uploadTitleTxt = (MTextView) uploadServicePicAlertBox.findViewById(R.id.uploadTitleTxt);
        ImageView backImgView = (ImageView) uploadServicePicAlertBox.findViewById(R.id.backImgView);

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        MTextView skipTxt = (MTextView) uploadServicePicAlertBox.findViewById(R.id.skipTxt);
        final ImageView uploadImgVIew = (ImageView) uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew);
        LinearLayout uploadImgArea = (LinearLayout) uploadServicePicAlertBox.findViewById(R.id.uploadImgArea);
        MButton btn_type2 = ((MaterialRippleLayout) uploadServicePicAlertBox.findViewById(R.id.btn_type2)).getChildView();

        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_UPLOAD_IMAGE_SERVICE"));
        skipTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SKIP_TXT"));

        if (picType.equalsIgnoreCase("before")) {
            uploadTitleTxt.setText(generalFunc.retrieveLangLBl("Click and upload photo of your car before your service", "LBL_UPLOAD_SERVICE_BEFORE_TXT"));
            btn_type2.setText(generalFunc.retrieveLangLBl("Save Photo", "LBL_SAVE_PHOTO_START_SERVICE_TXT"));
        } else {
            uploadTitleTxt.setText(generalFunc.retrieveLangLBl("Click and upload photo of your car after your service", "LBL_UPLOAD_SERVICE_AFTER_TXT"));
            btn_type2.setText(generalFunc.retrieveLangLBl("Save Photo", "LBL_SAVE_PHOTO_END_SERVICE_TXT"));
        }

        btn_type2.setId(Utils.generateViewId());
        btn_type2.setTextSize(16);
        uploadImgArea.setOnClickListener(view -> getFileSelector().openFileSelection(FileSelector.FileType.Image));
        btn_type2.setOnClickListener(view -> {

            if (TextUtils.isEmpty(selectedImagePath)) {
                uploadStatusTxt.setVisibility(View.VISIBLE);
                generalFunc.showMessage(uploadStatusTxt, "Please select image");

            } else if (picType.equalsIgnoreCase("after")) {
                uploadStatusTxt.setVisibility(View.GONE);
                endTrip();
            } else {
                uploadStatusTxt.setVisibility(View.GONE);

                setTripStart();

            }
        });

        skipTxt.setOnClickListener(view -> {

            isFrom = "";
            selectedImagePath = "";
            uploadImgVIew.setImageURI(null);


            if (picType.equalsIgnoreCase("after")) {
                endTrip();
            } else {

                setTripStart();


            }

        });

        backImgView.setOnClickListener(v -> {
            closeuploadServicePicAlertBox();
            isFaceMaskVerification = false;
            if (startTripSlideButton.getVisibility() == View.VISIBLE) {
                startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
            }
            if (endTripSlideButton.getVisibility() == View.VISIBLE) {
                endTripSlideButton.resetButtonView(endTripSlideButton.btnText.getText().toString());
            }
        });

        LayoutDirection.setLayoutDirection(uploadServicePicAlertBox);
        uploadServicePicAlertBox.show();
    }

    public void closeuploadServicePicAlertBox() {
        if (uploadServicePicAlertBox != null && uploadServicePicAlertBox.isShowing()) {
            uploadServicePicAlertBox.dismiss();
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == MyUtils.AUDIO_PERMISSION_REQ_CODE) {
            if (MyApp.getInstance().checkMicWithStorePermission(generalFunc, false)) {
                PermissionHandler.getInstance().closeView();
            }
        } else if (requestCode == Utils.SEARCH_DEST_LOC_REQ_CODE && resultCode == RESULT_OK && data != null) {
            latitude = data.getStringExtra("Latitude");
            longitirude = data.getStringExtra("Longitude");
            address = data.getStringExtra("Address");

            //addDestination(latitude, longitirude, address);
            getTollcostValue();
        } else if (requestCode == Utils.REQUEST_CODE_GPS_ON) {
            handleNoLocationDial();
        }
    }

    public void getTollcostValue() {

        if (generalFunc.retrieveValue(Utils.ENABLE_TOLL_COST).equalsIgnoreCase("Yes")) {

            double sourcelatitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLatitude"));
            double sourcelongitude = GeneralFunctions.parseDoubleValue(0.0, data_trip.get("sourceLongitude"));
            String vCurrencyDriver = generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile);

            String url = CommonUtilities.TOLLURL + generalFunc.getJsonValue("TOLL_COST_API_KEY", obj_userProfile)
                    + "&waypoint0=" + sourcelatitude
                    + "," + sourcelongitude + "&waypoint1=" + latitude + "," + longitirude + "&mode=fastest;car&tollVehicleType=car" + "&currency=" + vCurrencyDriver.toUpperCase(Locale.ENGLISH);


            ApiHandler.execute(getActContext(), url, true, true, generalFunc, responseString -> {

                if (responseString != null && !responseString.equals("")) {
                    String response = generalFunc.getJsonValue("response", responseString);
                    JSONArray route = generalFunc.getJsonArray("route", response);
                    JSONObject routeObj = generalFunc.getJsonObject(route, 0);
                    JSONObject tollCostMain = generalFunc.getJsonObject("tollCost", routeObj);

                    if (generalFunc.getJsonValueStr("onError", tollCostMain).equalsIgnoreCase("FALSE")) {
                        try {

                            JSONObject costs = generalFunc.getJsonObject("cost", routeObj);
                            String currency = generalFunc.getJsonValueStr("currency", costs);
                            JSONObject details = generalFunc.getJsonObject("details", costs);
                            String tollCost = generalFunc.getJsonValueStr("tollCost", details);
                            if (currency != null && !currency.equals("")) {
                                tollcurrancy = currency;
                            }
                            if (tollCost != null && !tollCost.equals("") && !tollCost.equals("0.0")) {
                                tollamount = GeneralFunctions.parseDoubleValue(0.0, tollCost);
                            }

                            addDestination(latitude, longitirude, address);
                        } catch (Exception e) {
                            tollcurrancy = "";
                            tollamount = 0.0;
                            tollcurrancy = "";
                            addDestination(latitude, longitirude, address);
                        }
                    } else {
                        tollcurrancy = "";
                        tollamount = 0.0;
                        tollcurrancy = "";
                        addDestination(latitude, longitirude, address);
                    }
                } else {
                    tollcurrancy = "";
                    tollamount = 0.0;
                    tollcurrancy = "";
                    addDestination(latitude, longitirude, address);
                }

            });


        } else {
            addDestination(latitude, longitirude, address);
        }

    }

    public void TollTaxDialog() {

        if (tollamount != 0.0 && tollamount != 0 && tollamount != 0.00) {
            AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

            LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            View dialogView = inflater.inflate(R.layout.dialog_tolltax, null);

            final MTextView tolltaxTitle = (MTextView) dialogView.findViewById(R.id.tolltaxTitle);
            final MTextView tollTaxMsg = (MTextView) dialogView.findViewById(R.id.tollTaxMsg);
            final MTextView tollTaxpriceTxt = (MTextView) dialogView.findViewById(R.id.tollTaxpriceTxt);
            final MTextView cancelTxt = (MTextView) dialogView.findViewById(R.id.cancelTxt);

            final CheckBox checkboxTolltax = (CheckBox) dialogView.findViewById(R.id.checkboxTolltax);

            checkboxTolltax.setOnCheckedChangeListener((buttonView, isChecked) -> {

                if (checkboxTolltax.isChecked()) {
                    istollIgnore = true;
                } else {
                    istollIgnore = false;
                }

            });


            MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
            int submitBtnId = Utils.generateViewId();
            btn_type2.setId(submitBtnId);
            btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CONTINUE_BTN"));
            btn_type2.setOnClickListener(v -> {
                tolltax_dialog.dismiss();
                eTollConfirmByUser = "Yes";

                addDestination(latitude, longitirude, address);


            });


            builder.setView(dialogView);
            tolltaxTitle.setText(generalFunc.retrieveLangLBl("", "LBL_TOLL_ROUTE"));
            tollTaxMsg.setText(generalFunc.retrieveLangLBl("", "LBL_TOLL_PRICE_DESC"));

            tollTaxMsg.setText(generalFunc.retrieveLangLBl("", "LBL_TOLL_PRICE_DESC"));

            String currencySymbol = generalFunc.getJsonValueStr("CurrencySymbol", obj_userProfile);
            tollTaxpriceTxt.setText(generalFunc.retrieveLangLBl("Total toll price", "LBL_TOLL_PRICE_TOTAL") + ": " + generalFunc.formatNumAsPerCurrency(generalFunc, generalFunc.convertNumberWithRTL(tollamount + ""), currencySymbol, true));
            checkboxTolltax.setText(generalFunc.retrieveLangLBl("", "LBL_IGNORE_TOLL_ROUTE"));
            cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));

            cancelTxt.setOnClickListener(v -> {
                tolltax_dialog.dismiss();
                istollIgnore = false;
            });

            tolltax_dialog = builder.create();
            LayoutDirection.setLayoutDirection(tolltax_dialog);
            tolltax_dialog.show();
        } else {
            addDestination(latitude, longitirude, address);
        }

    }

    public void handleImgUploadResponse(String responseString, String imageUploadedType) {

        if (responseString != null && !responseString.equals("")) {

            if (imageType.equalsIgnoreCase("after")) {
                endTripResponse(responseString);
            } else if (imageType.equalsIgnoreCase(imageUploadedType)) {
                Logger.d("isFaceMaskVerification", isFaceMaskVerification + "");
                if (isFaceMaskVerification) {
                    Logger.d("isFaceMaskVerification", isFaceMaskVerification + "1111" + safetyselectedImagePath);
                    isFrom = "";
                    setTripStart();
                } else {
                    startTripResponse(responseString);
                }
            } else if (imageUploadedType.equalsIgnoreCase("uploadImageWithMask")) {
                startTripResponse(responseString);
            }
        } else {
            generalFunc.showError();
        }
    }

    public void countDownStop() {
        if (timerrequesttask != null) {
            callsetTimeApi(false);
        }
    }

    public void countDownStart() {
        if (timerrequesttask != null) {
            timerrequesttask.stopRepeatingTask();
            timerrequesttask = null;
        }

        timerrequesttask = new RecurringTask(1000);
        timerrequesttask.startRepeatingTask();
        timerrequesttask.setTaskRunListener(instance -> {
            i++;
            setTimerValues();
        });

    }

    public void transitCountDownStart() {
        if (timerrequesttask != null) {
            timerrequesttask.stopRepeatingTask();
            timerrequesttask = null;
        }

        timerrequesttask = new RecurringTask(1000);
        timerrequesttask.startRepeatingTask();
        timerrequesttask.setTaskRunListener(instance -> {
            i++;
            setTransitTimerValues();
        });

    }

    private void setTransitTimerValues() {
        newtvHour.setText("" + String.format(Locale.ENGLISH, "%02d", i / 3600));
        newtvMinute.setText("" + String.format(Locale.ENGLISH, "%02d", (i % 3600) / 60));
        newtvSecond.setText("" + String.format(Locale.ENGLISH, "%02d", i % 60));


        Logger.d("setTransitTimerValues", "::" + String.format(Locale.ENGLISH, "%02d", i / 3600) + "::" + String.format(Locale.ENGLISH, "%02d", i % 60));
    }

    private void setTimerValues() {
        tvHour.setText(generalFunc.convertNumberWithRTL("" + String.format(Locale.ENGLISH, "%02d", i / 3600)));
        tvMinute.setText(generalFunc.convertNumberWithRTL("" + String.format(Locale.ENGLISH, "%02d", (i % 3600) / 60)));
        tvSecond.setText(generalFunc.convertNumberWithRTL("" + String.format(Locale.ENGLISH, "%02d", i % 60)));
    }

    private void setView() {
        onGoingTripDetailAdapter = new OnGoingTripDetailAdapter(getActContext(), list, generalFunc);
        onGoingTripsDetailListRecyclerView.setAdapter(onGoingTripDetailAdapter);
        onGoingTripDetailAdapter.notifyDataSetChanged();

        ufx_loading.setVisibility(View.GONE);
        buttonlayouts.setVisibility(View.VISIBLE);

        timerarea.setVisibility(View.VISIBLE);

    }

    private void callsetTimeApi(final boolean isresumeGet) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SetTimeForTrips");
        parameters.put("eType", eType);
        parameters.put("iUserId", data_trip.get("PassengerId"));
        parameters.put("iTripId", tripId);
        if (!isresumeGet) {
            parameters.put("iTripTimeId", TripTimeId);
        }

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

            if (isDataAvail) {
                String msg_str = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (msg_str != null && !msg_str.equals("true") && !msg_str.equals("")) {
                    TripTimeId = msg_str;
                }
                String temptime = generalFunc.getJsonValue("totalTime", responseString);
                i = Integer.parseInt(temptime);
                setTimerValues();

                if (isresumeGet) {
                    if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                        countDownStart();
                        btntimer.setVisibility(View.VISIBLE);
                        btntimer.setText(generalFunc.retrieveLangLBl("pause", "LBL_PAUSE_TEXT"));
                    } else {
                        transitCountDownStart();
                        newbtn_timer.setText(generalFunc.retrieveLangLBl("", "LBL_STOP_WAITING_TIMER"));
                    }
                    isresume = true;
                } else {
                    if (timerrequesttask != null) {
                        timerrequesttask.stopRepeatingTask();
                        timerrequesttask = null;
                    }
                    if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                        btntimer.setText(generalFunc.retrieveLangLBl("resume", "LBL_RESUME_TEXT"));
                    } else {
                        newbtn_timer.setText(LBL_WAIT);
                    }
                    isresume = false;
                }

            }
        });

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
            if ((generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_RIDE", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_UFX", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_BIDDING", obj_userProfile).equalsIgnoreCase("Yes") && REQUEST_TYPE.equalsIgnoreCase("Bidding")) ||
                    (generalFunc.getJsonValueStr("ENABLE_GOOGLE_MAP_NAVIGATION_DELIVERY", obj_userProfile).equalsIgnoreCase("Yes") && (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery) || REQUEST_TYPE.equalsIgnoreCase("Deliver")))) {
                openNavigationView(dest_lat, dest_lon, address);
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


    private class setOnClickList implements View.OnClickListener {

        @Override
        public void onClick(View view) {
            Utils.hideKeyboard(getActContext());
            if (view.getId() == emeTapImgView.getId()) {
                if (generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {
                    SafetyTools.getInstance().initiate(getActContext(), generalFunc, tripId, REQUEST_TYPE);
                    SafetyTools.getInstance().safetyToolsDialog(false);
                } else {
                    Bundle bn = new Bundle();
                    bn.putString("TripId", tripId);
                    new ActUtils(getActContext()).startActWithData(ConfirmEmergencyTapActivity.class, bn);
                }

            }  /*Multistop over start*/ else if (view.getId() == dropAllIconArea.getId()) {
                //MTextView v = ((MTextView) findViewById(R.id.endTripTxt));
                Logger.d("BLINK", "" + isDropAll);
                startBlink(endTripSlideButton.btnText, !isDropAll);
            }
            /*Multistop over end*/
        }
    }

    private void startBlink(MTextView v, boolean startBlink) {
        if (Utils.checkText(data_trip.get("iStopId")) && currentStopOverPoint < totalStopOverPoint && !startBlink) {
            v.setText(LBL_CONFIRM_STOPOVER_1 + " " + LBL_CONFIRM_STOPOVER_2 + " " + generalFunc.convertNumberWithRTL(data_trip.get("currentStopOverPoint")));
        } else {
            v.setText(LBL_BTN_SLIDE_END_TRIP_TXT);
        }
        endTripSlideButton.setBackgroundColor(getResources().getColor(R.color.red));

        if (anim != null && !startBlink) {
            v.clearAnimation();
            /*if (canCancelAnimation()) {
                v.animate().cancel();
            }*/
            anim.setAnimationListener(null);
            anim = null;
            showView(v, startBlink);
            isDropAll = startBlink;
            return;
        }

        anim = new AlphaAnimation(0.0f, 1.0f);
        anim.setDuration(120); //You can manage the time of the blink with this parameter
        anim.setStartOffset(60);
        anim.setRepeatMode(Animation.REVERSE);
        anim.setRepeatCount(Animation.INFINITE);
        v.startAnimation(anim);
        anim.setAnimationListener(new Animation.AnimationListener() {
            @Override
            public void onAnimationStart(Animation animation) {
                showView(null, startBlink);
            }

            @Override
            public void onAnimationEnd(Animation animation) {
                showView(v, startBlink);
            }

            @Override
            public void onAnimationRepeat(Animation animation) {
            }
        });
    }

    private void showView(MTextView v, boolean startBlink) {
        if (v != null) {
            v.setVisibility(View.VISIBLE);
        } else {
            isDropAll = startBlink;
        }

        findViewById(R.id.dropCancel).setVisibility(startBlink ? View.VISIBLE : View.GONE);
        dropAllAreaUP.setVisibility(startBlink ? View.GONE : View.VISIBLE);

        if (startBlink) {
            generalFunc.showMessage(dropAllAreaUP, generalFunc.retrieveLangLBl("", "LBL_MULTI_DROP_ALL_CONFIRM_TXT"));
        }

    }

    public static boolean canCancelAnimation() {
        return Build.VERSION.SDK_INT >= Build.VERSION_CODES.ICE_CREAM_SANDWICH;
    }

    private class setOnClickAct implements View.OnClickListener {

        String dest_lat = "";
        String dest_lon = "";

        public setOnClickAct() {
        }


        public setOnClickAct(String dest_lat, String dest_lon) {
            this.dest_lat = dest_lat;
            this.dest_lon = dest_lon;
        }

        @Override
        public void onClick(View view) {
            int i = view.getId();
            if (i == R.id.navigateAreaUP) {
                /*if (!isTripStart) {
                    String REQUEST_TYPE = data_trip.get("REQUEST_TYPE");
                    if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NAVIGATION_ALERT"));
                    } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NAVIGATION_BOOKING_ALERT"));
                    } else {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NAVIGATION_DELIVERY_ALERT"));
                    }
                } else {
                    openNavigationDialog(dest_lat, dest_lon);
                }*/
                openNavigationDialog(dest_lat, dest_lon);
            } else if (i == R.id.destLocSearchArea) {


                if (data_trip.get("vTripPaymentMode").equalsIgnoreCase("Card") && data_trip.get("ePayWallet").equalsIgnoreCase("Yes")
                        && !generalFunc.getJsonValueStr("SYSTEM_PAYMENT_FLOW", obj_userProfile).equalsIgnoreCase("Method-1")) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NOTE_ADD_DEST_FROM_DRIVER"));
                    return;


                }

                Bundle bn = new Bundle();
                bn.putString("isPickUpLoc", "false");

                if (userLocation != null) {
                    bn.putString("PickUpLatitude", "" + userLocation.getLatitude());
                    bn.putString("PickUpLongitude", "" + userLocation.getLongitude());
                }
                new ActUtils(getActContext()).startActForResult(SearchPickupLocationActivity.class,
                        bn, Utils.SEARCH_DEST_LOC_REQ_CODE);
            } else if (i == btntimer.getId()) {
                if (!isresume) {
                    callsetTimeApi(true);
                } else {
                    countDownStop();
                }
            } else if (i == findViewById(R.id.logoutImageview).getId()) {
                Bundle bn4 = new Bundle();
                bn4.putSerializable("data_trip", data_trip);
                bn4.putSerializable("iTripDeliveryLocationId", data_trip.get("iTripDeliveryLocationId"));
                new ActUtils(getActContext()).startActWithData(WayBillActivity.class, bn4);
            } else if (i == findViewById(R.id.newbtn_timer).getId()) {
                if (!isresume) {
                    callsetTimeApi(true);
                } else {
                    countDownStop();
                }
            } else if (i == userLocBtnImgView.getId()) {
                userLocBtnImgView.setVisibility(View.GONE);
                LatLngBounds.Builder builder = new LatLngBounds.Builder();
                builder.include(new LatLng(userLocation.getLatitude(), userLocation.getLongitude()));
                if (destLocLatitude != 0.0 && destLocLongitude != 0.0) {
                    builder.include(new LatLng(destLocLatitude, destLocLongitude));
                }
                gMap.animateCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), Utils.dipToPixels(getActContext(), 40)));
            } else if (i == R.id.callArea || i == R.id.chatArea) {

                boolean isAdmin = data_trip.get("eBookingFrom").equalsIgnoreCase("Admin");
                boolean isKiosk = data_trip.get("eBookingFrom").equalsIgnoreCase("Kiosk");
                boolean isUser = Utils.checkText(data_trip.get("iGcmRegId_U"));
                boolean isMulti = REQUEST_TYPE.equals(Utils.eType_Multi_Delivery);

                boolean isDefaultMedia = false;
                CommunicationManager.MEDIA media = CommunicationManager.MEDIA_TYPE;
                if (isMulti) {
                    media = CommunicationManager.MEDIA.DEFAULT;
                    isDefaultMedia = true;
                } else if (isAdmin && !isUser) {
                    media = CommunicationManager.MEDIA.DEFAULT;
                    isDefaultMedia = true;
                } else if (isKiosk && !isUser) {
                    media = CommunicationManager.MEDIA.DEFAULT;
                    isDefaultMedia = true;
                }

                MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                        .setToMemberId(data_trip.get("PassengerId"))
                        .setPhoneNumber(i == R.id.chatArea ? data_trip.get("PPhone") : isMulti ? data_trip.get("vReceiverMobile") : data_trip.get("vPhone_U"))
                        .setToMemberType(Utils.CALLTOPASSENGER)
                        .setToMemberName(isMulti ? data_trip.get("vReceiverName") : vName)
                        .setToMemberImage(isMulti ? "" : data_trip.get("PPicName"))

                        .setMedia(media)
                        .setTripId(isDefaultMedia ? "" : data_trip.get("iTripId"))

                        .setBookingNo(data_trip.get("vRideNo"))
                        .setBid(eType.equals("Bidding"))
                        .build();
                CommunicationManager.getInstance().communicate(MyApp.getInstance().getCurrentAct(), mDataProvider, i == R.id.chatArea ? CommunicationManager.TYPE.CHAT : CommunicationManager.TYPE.OTHER);
            }
        }
    }

    public class setOnTouchList implements View.OnTouchListener {
        float x1, x2, y1, y2, startX, movedX;

        DisplayMetrics display = getResources().getDisplayMetrics();

        final int width = display.widthPixels;

        @Override
        public boolean onTouch(View view, MotionEvent event) {
            switch (event.getAction()) {
                // when user first touches the screen we get x and y coordinate
                case ACTION_DOWN: {
                    x1 = event.getX();
                    y1 = event.getY();

                    startX = event.getRawX();
                    break;
                }
                case MotionEvent.ACTION_UP: {
                    x2 = event.getX();
                    y2 = event.getY();
                    movedX = generalFunc.isRTLmode() ? startX - event.getRawX() : event.getRawX() - startX;

                    if (movedX > width / 2) {

                        if (generalFunc.isRTLmode() ? (x1 > x2) : (x1 < x2)) {

                            isTripCancelPressed = false;

                        }
                    }

                    break;
                }
            }
            return false;
        }
    }

    private void defaultAddtionalprice() {
        additonallist.add(0, 0.00);
        additonallist.add(1, 0.00);
        additonallist.add(2, 0.00);
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

    public void removeImage(View v) {
        isFrom = "";
        safetyselectedImagePath = "";
        isFaceMaskVerification = false;
        ((ImageView) uploadImgAlertDialog.findViewById(R.id.uploadImgVIew)).setImageDrawable(null);
        uploadImgAlertDialog.findViewById(R.id.camImgVIew).setVisibility(View.VISIBLE);
        uploadImgAlertDialog.findViewById(R.id.ic_add).setVisibility(View.VISIBLE);


        maskVerificationUploadImgArea.setClickable(true);
        clearImg.setVisibility(View.GONE);
    }

    private void showSafetyDialog() {

        if (uploadImgAlertDialog != null) {
            uploadImgAlertDialog.cancel();
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull DesginMaskVerificationBinding binding = DesginMaskVerificationBinding.inflate(inflater, null, false);
        mCardView = binding.mCardView;
        maskVerificationUploadImgArea = binding.uploadImgArea;
        clearImg = binding.clearImg;
        clearImg.setVisibility(View.GONE);
        if (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_RIDE").equalsIgnoreCase("Yes") ||
                (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_DELIVERY").equalsIgnoreCase("Yes") ||
                        (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_UFX").equalsIgnoreCase("Yes")))) {
            if (generalFunc.retrieveValue("ENABLE_RESTRICT_PASSENGER_LIMIT").equalsIgnoreCase("Yes")) {
                binding.capacityTxt.setText(data_trip.get("RESTRICT_PASSENGER_LIMIT_NOTE"));
                binding.capacityTxt1.setText(data_trip.get("RESTRICT_PASSENGER_LIMIT_NOTE"));
            }
        }
        boolean isOnlyPassengerLimitOn = generalFunc.retrieveValue("ENABLE_RESTRICT_PASSENGER_LIMIT").equalsIgnoreCase("Yes") && !generalFunc.retrieveValue("ENABLE_FACE_MASK_VERIFICATION").equalsIgnoreCase("Yes");

        binding.capacityTxt1.setVisibility(isOnlyPassengerLimitOn ? View.VISIBLE : View.GONE);
        binding.capacityTxt.setVisibility(isOnlyPassengerLimitOn ? View.GONE : View.VISIBLE);
        MButton btn_type2 = ((MaterialRippleLayout) binding.btnType2).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", isOnlyPassengerLimitOn ? "LBL_BTN_OK_TXT" : "LBL_BTN_SUBMIT_TXT"));
        btn_type2.setId(Utils.generateViewId());
        binding.uploadArea.setVisibility(View.VISIBLE);
        binding.titileTxt.setText(generalFunc.retrieveLangLBl("Safety Essential", "LBL_SAFETY_ESSENTIAL_VERIFICATION_TXT"));
        binding.imageUploadNoteTxt.setText(generalFunc.retrieveLangLBl("Kindly upload mask selfie to prove that your following safety rules,After than you can start the trip", "LBL_MASK_VERIFICATION_UPLOAD_PHOTO_TXT"));
        binding.uploadTitleTxt.setText(generalFunc.retrieveLangLBl("Upload Photo", "LBL_UPLOAD_PHOTO"));
        if (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_RIDE").equalsIgnoreCase("Yes") ||
                (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_DELIVERY").equalsIgnoreCase("Yes") ||
                        (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_UFX").equalsIgnoreCase("Yes")))) {

            if (generalFunc.retrieveValue("ENABLE_FACE_MASK_VERIFICATION").equalsIgnoreCase("Yes")) {
                mCardView.setVisibility(View.VISIBLE);
                binding.uploadArea.setVisibility(View.VISIBLE);

                maskVerificationUploadImgArea.setOnClickListener(view -> {
                    isFaceMaskVerification = true;
                    getFileSelector().openFileSelection(FileSelector.FileType.Image);
                });
            } else {
                mCardView.setVisibility(View.GONE);
                binding.uploadArea.setVisibility(View.GONE);

            }
        } else {
            mCardView.setVisibility(View.GONE);
            binding.uploadArea.setVisibility(View.GONE);
        }
        binding.cancelImg.setOnClickListener(v -> {
            uploadImgAlertDialog.dismiss();
            isFaceMaskVerification = false;
            safetyselectedImagePath = "";
            if (uploadServicePicAlertBox != null) {
                uploadServicePicAlertBox.dismiss();
            }

            if (startTripSlideButton.getVisibility() == View.VISIBLE) {
                startTripSlideButton.resetButtonView(startTripSlideButton.btnText.getText().toString());
            }

        });
        btn_type2.setOnClickListener(view -> {
            if (mCardView.getVisibility() == View.VISIBLE) {
                boolean isImageSelect = Utils.checkText(safetyselectedImagePath);

                if (!isImageSelect) {
                    mCardView.setBackgroundResource(R.drawable.error_border);

                }

                if ((!isImageSelect)) {
                    return;
                }
            }

            if (data_trip != null && data_trip.get("eBeforeUpload").equalsIgnoreCase("Yes")) {
                uploadImgAlertDialog.dismiss();
                takeAndUploadPic(getActContext(), "before");
            } else {
                setTripStart();
            }

            // setTripStart();
        });
        builder.setView(binding.getRoot());
        uploadImgAlertDialog = builder.create();
        uploadImgAlertDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(uploadImgAlertDialog);
        uploadImgAlertDialog.getWindow().setBackgroundDrawable(new ColorDrawable(Color.TRANSPARENT));
        uploadImgAlertDialog.show();
    }

    @Override
    public void onFileSelected(Uri mFileUri, String mFilePath, FileSelector.FileType mFileType) {
        isFrom = mFileType.name();
        if ((mFileUri != null && uploadServicePicAlertBox != null) || (isFaceMaskVerification && mFileUri != null && uploadImgAlertDialog != null)) {
            if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
                safetyselectedImagePath = mFilePath;
            } else {
                selectedImagePath = mFilePath;
            }
            try {
                BitmapFactory.Options options = new BitmapFactory.Options();
                options.inJustDecodeBounds = true;
                if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
                    BitmapFactory.decodeFile(safetyselectedImagePath, options);
                } else {
                    BitmapFactory.decodeFile(selectedImagePath, options);
                }

                if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
                    clearImg.setVisibility(View.VISIBLE);
                    maskVerificationUploadImgArea.setClickable(false);
                    new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadImgAlertDialog.findViewById(R.id.uploadImgVIew))).build();
                    ((LinearLayout) uploadImgAlertDialog.findViewById(R.id.mCardView)).setBackgroundResource(R.drawable.update_border);
                } else {
                    new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).build();
                }
            } catch (Exception e) {
                if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
                    clearImg.setVisibility(View.VISIBLE);
                    maskVerificationUploadImgArea.setClickable(false);

                    new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadImgAlertDialog.findViewById(R.id.uploadImgVIew))).build();
                    ((LinearLayout) uploadImgAlertDialog.findViewById(R.id.mCardView)).setBackgroundResource(R.drawable.update_border);
                } else {
                    new LoadImageGlide.builder(getActContext(), LoadImageGlide.bind(mFileUri), ((ImageView) uploadServicePicAlertBox.findViewById(R.id.uploadImgVIew))).build();
                }
            }

            if (uploadImgAlertDialog != null && uploadImgAlertDialog.isShowing()) {
                uploadImgAlertDialog.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
                uploadImgAlertDialog.findViewById(R.id.ic_add).setVisibility(View.GONE);
            } else {
                uploadServicePicAlertBox.findViewById(R.id.camImgVIew).setVisibility(View.GONE);
                uploadServicePicAlertBox.findViewById(R.id.ic_add).setVisibility(View.GONE);
            }
        }
    }
}