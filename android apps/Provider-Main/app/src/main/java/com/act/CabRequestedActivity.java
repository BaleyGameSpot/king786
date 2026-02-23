package com.act;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.KeyguardManager;
import android.content.ContentResolver;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.drawable.Drawable;
import android.location.Location;
import android.media.MediaPlayer;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.os.Handler;
import android.os.Looper;
import android.text.Editable;
import android.text.InputFilter;
import android.text.InputType;
import android.text.TextWatcher;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.WindowManager;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.Toolbar;
import androidx.core.content.ContextCompat;

import com.activity.ParentActivity;
import com.general.ServiceRequest;
import com.general.files.ActUtils;
import com.general.files.DecimalDigitsInputFilter;
import com.general.files.GeneralFunctions;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.general.files.PolyLineAnimator;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptor;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.LatLngBounds;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;
import com.google.maps.android.SphericalUtil;
import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.service.model.EventInformation;
import com.service.server.ServerTask;
import com.trafi.anchorbottomsheetbehavior.AnchorBottomSheetBehavior;
import com.utils.Logger;
import com.utils.Utils;
import com.view.AutoFitEditText;
import com.view.DividerView;
import com.view.GenerateAlertBox;
import com.view.MTextView;
import com.view.simpleratingbar.SimpleRatingBar;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

@SuppressWarnings("ResourceType")
public class CabRequestedActivity extends ParentActivity implements GenerateAlertBox.HandleAlertBtnClick, OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener {
    private ProgressBar mProgressBar, progressbar_dialog;
    private RelativeLayout progressLayout;
    private LinearLayout viewDetailsArea;
    public LinearLayout afterAcceptTaxiBidOfferArea;
    private boolean eFly = false, blink, istimerfinish = false, isloadedAddress = false, isUfx = false, isFindRoute = false, enableGoogleDirection = false;

    GenerateAlertBox generateAlert;
    private int maxProgressValue = 30, DRIVER_ARRIVED_MIN_TIME_PER_MINUTE = 3, peekHeight;
    private MediaPlayer mp = null;
    private CountDownTimer countDownTimer; // built in android class
    // CountDownTimer
    private long totalTimeCountInMilliseconds = maxProgressValue * (long) 1000; // total count down time in
    // milliseconds
    private final long timeBlinkInMilliseconds = 10 * (long) 1000; // start time of start blinking
    private FrameLayout progressLayout_frame, progressLayout_frame_dialog, bottom_sheet;
    private Location userLocation;
    private long milliLeft;
    private Marker sourceMarker, destMarker, sourceDotMarker, destDotMarker;
    private MarkerOptions source_dot_option, dest_dot_option;
    private GoogleMap gMap;
    private LatLngBounds.Builder builder = new LatLngBounds.Builder();
    private Polyline route_polyLine;
    public LatLng sourceLocation = null;
    public LatLng destLocation = null;
    private final double DEFAULT_CURVE_ROUTE_CURVATURE = 0.5f;
    private final int DEFAULT_CURVE_POINTS = 60;
    private SupportMapFragment fm;
    private JSONObject userProfileJsonObj;
    private String isVideoCall = "", userProfileJson, requestTypeVal = "", time = "", distance = "", REQUEST_TYPE = "", iCabRequestId = "", iOrderId = "", LBL_RECIPIENT, LBL_PAYMENT_MODE_TXT, LBL_TOTAL_DISTANCE, LBL_Total_Fare_TXT, LBL_POOL_REQUEST, LBL_PERSON, LBL_FLY_REQUEST, LBL_REQUEST, LBL_DELIVERY, LBL_RIDE, LBL_JOB_TXT, LBL_VIDEO_CONSULT_AT_YOUR_LOC, LBL_RENTAL_RIDE_REQUEST, LBL_RENTAL_AIRCRAFT_REQUEST, specialUserComment = "", GenieOrder = "No", destinationAddress = "", pickUpAddress = "", msgCode, message_str, LBL_INTERCITY_RIDE_REQUEST;
    private AutoFitEditText yoreOfferEdit;
    private MTextView taxiBidOfferAcceptMsgTxt, userOfferAcceptMsgTxt, deliveryDetailsBtn, addressTxt, etaTxt, pkgType, moreSeriveTxt, specialHintTxt, specialValTxt, locationAddressTxt, ufxlocationAddressTxt, destAddressTxt, textViewShowTime, tvTimeCount_dialog, destAddressHintTxt, locationAddressHintTxt, ufxlocationAddressHintTxt, serviceType, ufxserviceType;
    private AnchorBottomSheetBehavior behavior;
    private boolean isInterCity;
    public boolean isAnotherRequestAvailable = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
            setShowWhenLocked(true);
            setTurnScreenOn(true);

            KeyguardManager keyguardManager = (KeyguardManager) getSystemService(Context.KEYGUARD_SERVICE);
            keyguardManager.requestDismissKeyguard(this, null);
        } else {
            getWindow().addFlags(WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED |
                    WindowManager.LayoutParams.FLAG_DISMISS_KEYGUARD |
                    WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON |
                    WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON |
                    WindowManager.LayoutParams.FLAG_ALLOW_LOCK_WHILE_SCREEN_ON);
        }
        setContentView(R.layout.activity_cab_requested);

        peekHeight = getResources().getDimensionPixelSize(R.dimen._100sdp);

        generalFunc.removeValue(Utils.DRIVER_ACTIVE_REQ_MSG_KEY);


        Toolbar mToolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);

        message_str = getIntent().getStringExtra("Message");
        msgCode = generalFunc.getJsonValue("MsgCode", message_str);


        String iCabRequestId_ = generalFunc.getJsonValue("iCabRequestId", message_str);
        if (iCabRequestId_ != null && !iCabRequestId_.equalsIgnoreCase("")) {
            iCabRequestId = iCabRequestId_;
        }
        isInterCity = Utils.checkText(generalFunc.getJsonValue("eIsInterCity", message_str)) && generalFunc.getJsonValue("eIsInterCity", message_str).equalsIgnoreCase("Yes");
        String iOrderId_ = generalFunc.getJsonValue("iOrderId", message_str);
        if (iOrderId_ != null && !iOrderId_.equalsIgnoreCase("")) {
            iOrderId = iOrderId_;
        }

        if (generalFunc.containsKey(Utils.DRIVER_REQ_COMPLETED_MSG_CODE_KEY + msgCode)) {
            finish();
            return;
        } else {
            generalFunc.storeData(Utils.DRIVER_REQ_COMPLETED_MSG_CODE_KEY + msgCode, "true");
            generalFunc.storeData(Utils.DRIVER_REQ_COMPLETED_MSG_CODE_KEY + msgCode, "" + System.currentTimeMillis());
        }
        generalFunc.storeData(Utils.DRIVER_CURRENT_REQ_OPEN_KEY, "true");

        ServiceRequest.sendEvent(msgCode, "Opened");
        isAnotherRequestAvailable = true;

        deliveryDetailsBtn = findViewById(R.id.deliveryDetailsBtn);
        moreSeriveTxt = (MTextView) findViewById(R.id.moreServiceBtn);
        bottom_sheet = findViewById(R.id.bottom_sheet);

        locationAddressTxt = (MTextView) findViewById(R.id.locationAddressTxt);
        ufxlocationAddressTxt = (MTextView) findViewById(R.id.ufxlocationAddressTxt);
        locationAddressHintTxt = (MTextView) findViewById(R.id.locationAddressHintTxt);
        ufxlocationAddressHintTxt = (MTextView) findViewById(R.id.ufxlocationAddressHintTxt);
        destAddressHintTxt = (MTextView) findViewById(R.id.destAddressHintTxt);
        destAddressTxt = (MTextView) findViewById(R.id.destAddressTxt);
        viewDetailsArea = (LinearLayout) findViewById(R.id.viewDetailsArea);
        progressLayout = (RelativeLayout) findViewById(R.id.progressLayout);
        specialHintTxt = (MTextView) findViewById(R.id.specialHintTxt);
        specialValTxt = (MTextView) findViewById(R.id.specialValTxt);
        pkgType = (MTextView) findViewById(R.id.pkgType);

        progressLayout_frame = (FrameLayout) findViewById(R.id.progressLayout_frame);
        progressLayout_frame_dialog = (FrameLayout) findViewById(R.id.progressLayout_frame_dialog);


        mProgressBar = (ProgressBar) findViewById(R.id.progressbar);
        progressbar_dialog = (ProgressBar) findViewById(R.id.progressbar_dialog);


        textViewShowTime = (MTextView) findViewById(R.id.tvTimeCount);
        tvTimeCount_dialog = (MTextView) findViewById(R.id.tvTimeCount_dialog);
        serviceType = (MTextView) findViewById(R.id.serviceType);
        ufxserviceType = (MTextView) findViewById(R.id.ufxserviceType);

        (findViewById(R.id.menuImgView)).setVisibility(View.GONE);


        maxProgressValue = GeneralFunctions.parseIntegerValue(30, generalFunc.retrieveValue("RIDER_REQUEST_ACCEPT_TIME"));
        if (generalFunc.getJsonValue("isTaxiBid", message_str).equalsIgnoreCase("Yes")) {
            maxProgressValue = GeneralFunctions.parseIntegerValue(30, generalFunc.retrieveValue("RIDER_REQUEST_ACCEPT_TIME_BID_TAXI"));
        }
        totalTimeCountInMilliseconds = maxProgressValue * 1 * (long) 1000; // total count down time in
        textViewShowTime.setText(maxProgressValue + ":" + "00");
        tvTimeCount_dialog.setText(maxProgressValue + ":" + "00");
        mProgressBar.setMax(maxProgressValue);
        mProgressBar.setProgress(maxProgressValue);
        progressbar_dialog.setMax(maxProgressValue);
        progressbar_dialog.setProgress(maxProgressValue);


        generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setBtnClickList(this);
        generateAlert.setCancelable(false);

        REQUEST_TYPE = generalFunc.getJsonValue("REQUEST_TYPE", message_str);

        fm = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2_calling_driver);

        fm.getMapAsync(this);


        deliveryDetailsBtn.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_DELIVERY_DETAILS"));
        addToClickHandler(deliveryDetailsBtn);
        LBL_REQUEST = generalFunc.retrieveLangLBl("Request", "LBL_REQUEST");
        LBL_DELIVERY = generalFunc.retrieveLangLBl("Delivery", "LBL_DELIVERY");
        LBL_RIDE = generalFunc.retrieveLangLBl("Ride", "LBL_RIDE");
        LBL_JOB_TXT = generalFunc.retrieveLangLBl("Job", "LBL_JOB_TXT");
        LBL_VIDEO_CONSULT_AT_YOUR_LOC = generalFunc.retrieveLangLBl("", "LBL_VIDEO_CONSULT_AT_YOUR_LOC");
        LBL_RENTAL_RIDE_REQUEST = generalFunc.retrieveLangLBl("", "LBL_RENTAL_RIDE_REQUEST");
        LBL_INTERCITY_RIDE_REQUEST = generalFunc.retrieveLangLBl("InterCity Ride Request", "LBL_INTERCITY_RIDE_REQUEST");
        LBL_RECIPIENT = generalFunc.retrieveLangLBl("", "LBL_RECIPIENT");
        LBL_PAYMENT_MODE_TXT = generalFunc.retrieveLangLBl("", "LBL_PAYMENT_MODE_TXT");
        LBL_TOTAL_DISTANCE = generalFunc.retrieveLangLBl("", "LBL_TOTAL_DISTANCE");
        LBL_Total_Fare_TXT = generalFunc.retrieveLangLBl("", "LBL_Total_Fare_TXT");
        LBL_POOL_REQUEST = generalFunc.retrieveLangLBl("Ride", "LBL_POOL_REQUEST");
        LBL_PERSON = generalFunc.retrieveLangLBl("", "LBL_PERSON");
        LBL_FLY_REQUEST = generalFunc.retrieveLangLBl("", "LBL_FLY_REQUEST");
        LBL_RENTAL_AIRCRAFT_REQUEST = generalFunc.retrieveLangLBl("", "LBL_RENTAL_AIRCRAFT_REQUEST");

        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        if (generalFunc.getJsonValue("IS_SHOW_ROUTE_ON_REQUEST", userProfileJson).equalsIgnoreCase("yes")) {
            enableGoogleDirection = true;
        }

        setData();
        setLabels();


        startTimer(totalTimeCountInMilliseconds);


        addToClickHandler(progressLayout);
        addToClickHandler(viewDetailsArea);
        addToClickHandler(moreSeriveTxt);

        setBottomSheet();
        setEstView(message_str);

        setTaxiBidView();
    }

    private void setBottomSheet() {
        behavior = AnchorBottomSheetBehavior.from(bottom_sheet);
        behavior.setState(AnchorBottomSheetBehavior.STATE_COLLAPSED);
        if (generalFunc.getJsonValue("isTaxiBid", message_str).equalsIgnoreCase("Yes")) {
            behavior.setState(AnchorBottomSheetBehavior.STATE_EXPANDED);
        }
        behavior.setPeekHeight(peekHeight);
        behavior.addBottomSheetCallback(new AnchorBottomSheetBehavior.BottomSheetCallback() {
            @Override
            public void onStateChanged(@NonNull View view, int i, int i1) {

                if (i1 == AnchorBottomSheetBehavior.STATE_COLLAPSED) {
                    progressLayout_frame_dialog.setVisibility(View.GONE);
                    progressLayout_frame.setVisibility(View.VISIBLE);
                    behavior.setPeekHeight(peekHeight);
                } else if (i1 == AnchorBottomSheetBehavior.STATE_EXPANDED || i1 == AnchorBottomSheetBehavior.STATE_DRAGGING) {
                    progressLayout_frame_dialog.setVisibility(View.VISIBLE);
                    progressLayout_frame.setVisibility(View.GONE);
                    behavior.setPeekHeight(peekHeight + getResources().getDimensionPixelSize(R.dimen._65sdp));
                }
            }

            @Override
            public void onSlide(@NonNull View view, float v) {
                int extraVal = 75;
                if (getResources().getDisplayMetrics().density == 3) {
                    extraVal = 75;
                } else if (getResources().getDisplayMetrics().density == 2) {
                    extraVal = Utils.dpToPx(75, getActContext());
                }
                try {
                    buildBuilder(Utils.dpToPx(getActContext(), ((bottom_sheet.getHeight() / 4) + extraVal)) - (view.getTop()));
                } catch (Exception e) {

                }
            }
        });

        ImageView imagedest = findViewById(R.id.imagedest);
        DividerView dashImage = findViewById(R.id.dashImage);
        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) dashImage.getLayoutParams();
        params.addRule(RelativeLayout.ABOVE, imagedest.getId());

        MTextView requestType = findViewById(R.id.requestType);
        requestType.setSelected(true);
        requestType.setText(requestTypeVal);

        MTextView pNameTxtView = findViewById(R.id.pNameTxtView);
        pNameTxtView.setText(generalFunc.getJsonValue("PName", message_str));

        SimpleRatingBar ratingBar = findViewById(R.id.ratingBar);
        ratingBar.setRating(GeneralFunctions.parseFloatValue(0, generalFunc.getJsonValue("PRating", message_str)));

        ImageView imgVideoConsult = findViewById(R.id.imgVideoConsult);
        imgVideoConsult.setVisibility(isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
    }

    private void setEstView(String msg) {
        LinearLayout estFareArea = findViewById(R.id.estFareArea);
        LinearLayout estPickUpArea = findViewById(R.id.estPickUpArea);
        LinearLayout estTripArea = findViewById(R.id.estTripArea);
        String vRentalTxt = generalFunc.getJsonValue("PackageName", msg);
        String minFareRental = generalFunc.getJsonValue("vPackageFareEst", msg);

        estFareArea.setVisibility(View.GONE);
        estPickUpArea.setVisibility(View.GONE);
        estTripArea.setVisibility(View.GONE);

        String vFareEst = generalFunc.getJsonValue("vFareEst", msg);
        String vPickupEst = generalFunc.getJsonValue("vPickupEst", msg);
        String vTripEst = generalFunc.getJsonValue("vTripEst", msg);

        if (Utils.checkText(vFareEst)) {
            estFareArea.setVisibility(View.VISIBLE);
            MTextView estFareHTxt = findViewById(R.id.estFareHTxt);
            MTextView estFareVTxt = findViewById(R.id.estFareVTxt);
            estFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ESTIMATE_FARE_TXT"));
            estFareVTxt.setText(vFareEst);
        }
        if (Utils.checkText(vPickupEst)) {
            estPickUpArea.setVisibility(View.VISIBLE);
            MTextView estPickUpHTxt = findViewById(R.id.estPickUpHTxt);
            MTextView estPickUpVTxt = findViewById(R.id.estPickUpVTxt);
            estPickUpHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_ESTIMATE_TXT"));
            estPickUpVTxt.setText(vPickupEst);
        }
        if (Utils.checkText(vTripEst)) {
            estTripArea.setVisibility(View.VISIBLE);
            MTextView estTripHTxt = findViewById(R.id.estTripHTxt);
            MTextView estTripVTxt = findViewById(R.id.estTripVTxt);
            estTripHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TRIP_ESTIMATE_TXT"));
            estTripVTxt.setText(vTripEst);
        }
        if (Utils.checkText(vRentalTxt)) {
            estFareArea.setVisibility(View.VISIBLE);
            MTextView estFareHTxt = findViewById(R.id.estFareHTxt);
            MTextView estFareVTxt = findViewById(R.id.estFareVTxt);
            estFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PACKAGE_INFORMATION_TXT"));
            estFareVTxt.setText(vRentalTxt);

        }
        if (Utils.checkText(minFareRental)) {
            estTripArea.setVisibility(View.VISIBLE);
            MTextView estTripHTxt = findViewById(R.id.estTripHTxt);
            MTextView estTripVTxt = findViewById(R.id.estTripVTxt);
            estTripHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ESTIMATE_MIN_FARE"));
            estTripVTxt.setText(minFareRental);
        }
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            peekHeight = getResources().getDimensionPixelSize(R.dimen._100sdp);
            if (Utils.checkText(vFareEst)) {
                peekHeight = peekHeight + estFareArea.getMeasuredHeight();
            }
            if (Utils.checkText(vPickupEst)) {
                peekHeight = peekHeight + estPickUpArea.getMeasuredHeight();
            }
            if (Utils.checkText(vTripEst) && estPickUpArea.getVisibility() == View.GONE) {
                peekHeight = peekHeight + estTripArea.getMeasuredHeight();
            }
            behavior.setPeekHeight(peekHeight);

            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) progressLayout_frame.getLayoutParams();
            params.setMargins(0, 0, 0, peekHeight);
            progressLayout_frame.setLayoutParams(params);
        }, 100);


    }

    @SuppressLint("SetTextI18n")
    private void setTaxiBidView() {
        afterAcceptTaxiBidOfferArea = findViewById(R.id.afterAcceptTaxiBidOfferArea);
        afterAcceptTaxiBidOfferArea.setVisibility(View.GONE);

        userOfferAcceptMsgTxt = findViewById(R.id.userOfferAcceptMsgTxt);

        LinearLayout taxiBidArea = findViewById(R.id.taxiBidArea);
        taxiBidArea.setVisibility(View.GONE);
        if (generalFunc.getJsonValue("isTaxiBid", message_str).equalsIgnoreCase("Yes")) {
            taxiBidArea.setVisibility(View.VISIBLE);

            taxiBidOfferAcceptMsgTxt = findViewById(R.id.taxiBidOfferAcceptMsgTxt);
            MTextView taxiBidNoteMsgTxt = findViewById(R.id.taxiBidNoteMsgTxt);
            taxiBidNoteMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DO_NOT_RESTART_APP_TAXI_BID_TXT"));

            MTextView receiverOfferTxt = findViewById(R.id.receiverOfferTxt);
            MTextView enterYourFareHTxt = findViewById(R.id.enterYourFareHTxt);
            MTextView currencyTxt = findViewById(R.id.currencyTxt);
            receiverOfferTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECEIVED_OFFER_TAXI_BID_TXT") + ": " + generalFunc.getJsonValue("userTaxiBidAmountWithCurrencySymbol", message_str));
            enterYourFareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ENATER_YOUR_FARE_TAXI_BID_TXT"));
            currencyTxt.setText(generalFunc.getJsonValueStr("vCurrencyDriver", obj_userProfile));

            yoreOfferEdit = findViewById(R.id.yoreOfferEdit);
            yoreOfferEdit.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
            yoreOfferEdit.setFilters(new InputFilter[]{new DecimalDigitsInputFilter(2)});

            double fareBidTaxi = Double.parseDouble(generalFunc.getJsonValue("userTaxiBidAmount", message_str));
            yoreOfferEdit.setText("" + fareBidTaxi);

            yoreOfferEdit.addTextChangedListener(new TextWatcher() {
                @Override
                public void beforeTextChanged(CharSequence s, int start, int count, int after) {

                }

                @Override
                public void onTextChanged(CharSequence s, int start, int before, int count) {

                    if (Utils.checkText(yoreOfferEdit) && GeneralFunctions.parseDoubleValue(0, yoreOfferEdit.getTxt()) < fareBidTaxi) {
                        yoreOfferEdit.setText("" + fareBidTaxi);
                    }

                    ((MTextView) findViewById(R.id.AcceptbtnTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));
                    ((MTextView) findViewById(R.id.AcceptbtnTxt1)).setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));

                    if (Utils.checkText(yoreOfferEdit)) {
                        if (GeneralFunctions.parseDoubleValue(0, yoreOfferEdit.getTxt()) != fareBidTaxi) {
                            ((MTextView) findViewById(R.id.AcceptbtnTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_OFFER_TAXI_BID_TEXT"));
                            ((MTextView) findViewById(R.id.AcceptbtnTxt1)).setText(generalFunc.retrieveLangLBl("", "LBL_OFFER_TAXI_BID_TEXT"));
                        }
                    }
                }

                @Override
                public void afterTextChanged(Editable s) {
                    yoreOfferEdit.afterTextChange(s);
                }
            });

            View offerMinus = findViewById(R.id.offerMinus);
            View offerPlus = findViewById(R.id.offerPlus);
            offerMinus.setOnClickListener(view -> {
                if (Utils.checkText(yoreOfferEdit) && GeneralFunctions.parseDoubleValue(0, yoreOfferEdit.getTxt()) > fareBidTaxi) {
                    yoreOfferEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, yoreOfferEdit.getTxt()) - 1));
                    if (!(GeneralFunctions.parseDoubleValue(0, yoreOfferEdit.getTxt()) >= 0)) {
                        yoreOfferEdit.setText(AutoFitEditText.convertCommaToDecimal("0.00", false));
                    }
                }
            });
            offerPlus.setOnClickListener(view -> {
                if (Utils.checkText(yoreOfferEdit)) {
                    yoreOfferEdit.setText(String.format(Locale.ENGLISH, "%.2f", GeneralFunctions.parseDoubleValue(0.0, yoreOfferEdit.getTxt()) + 1));
                } else {
                    yoreOfferEdit.setText(AutoFitEditText.convertCommaToDecimal("1.00", false));
                }
            });
        }
    }

    private boolean isUserAcceptShowDialog = false;

    public void pubNubMsgArrived(final String message) {
        runOnUiThread(() -> {

            String msgType = generalFunc.getJsonValue("MsgType", message);

            if (msgType.equals("WaitTripGenerateProcessRunning")) {
                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (!isUserAcceptShowDialog) {
                        MyApp.getInstance().restartWithGetDataApp();
                    }
                }, 10000);

            } else if (msgType.equals("UserAccepDriverOffer")) {
                isUserAcceptShowDialog = true;
                generalFunc.showGeneralMessage("", generalFunc.getJsonValue("vTitle", message), generalFunc.retrieveLangLBl("Ok", "LBL_BTN_OK_TXT"), "", buttonId -> {
                    //
                    MyApp.getInstance().restartWithGetDataApp();
                });
            }
        });
    }

    public PolylineOptions createCurveRoute(LatLng origin, LatLng dest) {

        double distance = SphericalUtil.computeDistanceBetween(origin, dest);
        double heading = SphericalUtil.computeHeading(origin, dest);
        double halfDistance = distance > 0 ? (distance / 2) : (distance * DEFAULT_CURVE_ROUTE_CURVATURE);

        // Calculate midpoint position
        LatLng midPoint = SphericalUtil.computeOffset(origin, halfDistance, heading);

        // Calculate position of the curve center point
        double sqrCurvature = DEFAULT_CURVE_ROUTE_CURVATURE * DEFAULT_CURVE_ROUTE_CURVATURE;
        double extraParam = distance / (4 * DEFAULT_CURVE_ROUTE_CURVATURE);
        double midPerpendicularLength = (1 - sqrCurvature) * extraParam;
        double r = (1 + sqrCurvature) * extraParam;

        LatLng circleCenterPoint = SphericalUtil.computeOffset(midPoint, midPerpendicularLength, heading + 90.0);

        // Calculate heading between circle center and two points
        double headingToOrigin = SphericalUtil.computeHeading(circleCenterPoint, origin);

        // Calculate positions of points on the curve
        double step = Math.toDegrees(Math.atan(halfDistance / midPerpendicularLength)) * 2 / DEFAULT_CURVE_POINTS;
        //Polyline options
        PolylineOptions options = new PolylineOptions();

        for (int i = 0; i < DEFAULT_CURVE_POINTS; ++i) {
            LatLng pi = SphericalUtil.computeOffset(circleCenterPoint, r, headingToOrigin + i * step);
            options.add(pi);
        }
        return options;
    }

    public void setLabels() {
        /*Multi Delivery Lables*/

        ((MTextView) findViewById(R.id.recipientHintTxt)).setText(generalFunc.isRTLmode() ? ":" + LBL_RECIPIENT : LBL_RECIPIENT + ":");

        ((MTextView) findViewById(R.id.paymentModeHintTxt)).setText(generalFunc.isRTLmode() ? ":" + LBL_PAYMENT_MODE_TXT : LBL_PAYMENT_MODE_TXT + ":");

        ((MTextView) findViewById(R.id.totalMilesHintTxt)).setText(generalFunc.isRTLmode() ? ":" + LBL_TOTAL_DISTANCE : LBL_TOTAL_DISTANCE + ":");

        ((MTextView) findViewById(R.id.totalFareHintTxt)).setText(generalFunc.isRTLmode() ? ":" + LBL_Total_Fare_TXT : LBL_Total_Fare_TXT + ":");


        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            locationAddressHintTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOCATION_HEADER_TXT"));
            destAddressHintTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEST_ADD_TXT"));
        } else {
            locationAddressHintTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SENDER_LOCATION"));
            destAddressHintTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECEIVER_LOCATION"));
        }
        ufxlocationAddressHintTxt.setText(generalFunc.retrieveLangLBl("Job Location", "LBL_JOB_LOCATION_TXT"));

        ((MTextView) findViewById(R.id.hintTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_HINT_TAP_TXT"));
        specialHintTxt.setText(generalFunc.retrieveLangLBl("Special Instruction", "LBL_SPECIAL_INSTRUCTION_TXT"));
        moreSeriveTxt.setText(generalFunc.retrieveLangLBl("", "LBL_VIEW_REQUESTED_SERVICES"));


    }

    double desLat = 0.0;
    double destLog = 0.0;

    public void setData() {
        HashMap<String, String> hashMap = new HashMap<>();

        double pickupLat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("sourceLatitude", message_str));
        double pickupLog = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("sourceLongitude", message_str));


        GenieOrder = generalFunc.getJsonValue("GenieOrder", message_str);
        handleGenieTimerView();


        String destLatitude = generalFunc.getJsonValue("destLatitude", message_str);
        String destLongitude = generalFunc.getJsonValue("destLongitude", message_str);
        if (!destLatitude.isEmpty() && !destLongitude.isEmpty()) {

            desLat = GeneralFunctions.parseDoubleValue(0.0, destLatitude);
            destLog = GeneralFunctions.parseDoubleValue(0.0, destLongitude);

            if (desLat == 0.0 && destLog == 0.0) {
                destAddressTxt.setVisibility(View.GONE);
                destAddressHintTxt.setVisibility(View.GONE);
            } else {
                destAddressTxt.setVisibility(View.VISIBLE);
                destAddressHintTxt.setVisibility(View.VISIBLE);
            }
        }
        hashMap.put("s_latitude", pickupLat + "");
        hashMap.put("s_longitude", pickupLog + "");
        hashMap.put("d_latitude", destLatitude + "");
        hashMap.put("d_longitude", destLongitude + "");
        String parameters = "origin=" + desLat + "&destination=" + destLog;
        hashMap.put("parameters", parameters);


        if ((iCabRequestId != null && !iCabRequestId.equals("")) || (iOrderId != null && !iOrderId.equals(""))) {
            if (Utils.checkText(generalFunc.getJsonValue("CabRequestData", message_str))) {
                createRequestScreen(generalFunc.getJsonValue("CabRequestData", message_str));
                getAddressFormServer();
            } else {
                getAddressFormServer();
            }
        } else {
            AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(pickupLat + "", pickupLog + "").setDestLatitude(destLatitude + "").setDestLongitude(destLongitude + "").setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {
                if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                    return;
                }
                manageResult(data, new LatLng(pickupLat, pickupLog), new LatLng(desLat, destLog));

            });

        }

        LinearLayout packageInfoArea = (LinearLayout) findViewById(R.id.packageInfoArea);

        String VehicleTypeName = generalFunc.getJsonValue("VehicleTypeName", message_str);
        String SelectedTypeName = generalFunc.getJsonValue("SelectedTypeName", message_str);
        eFly = generalFunc.getJsonValue("eFly", message_str).equalsIgnoreCase("Yes");

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            isUfx = true;
            progressLayout_frame.setVisibility(View.GONE);
            locationAddressTxt.setVisibility(View.GONE);
            locationAddressHintTxt.setVisibility(View.GONE);
            destAddressHintTxt.setVisibility(View.GONE);
            destAddressTxt.setVisibility(View.GONE);
            ufxlocationAddressTxt.setVisibility(View.VISIBLE);
            ufxlocationAddressHintTxt.setVisibility(View.VISIBLE);
            progressLayout_frame.setVisibility(View.VISIBLE);
            specialHintTxt.setVisibility(View.VISIBLE);
            specialValTxt.setVisibility(View.VISIBLE);


            requestTypeVal = LBL_JOB_TXT + "  " + LBL_REQUEST;
            if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                requestTypeVal = LBL_VIDEO_CONSULT_AT_YOUR_LOC;
            }

            ufxserviceType.setVisibility(View.VISIBLE);
            ufxserviceType.setText(SelectedTypeName);
            packageInfoArea.setVisibility(View.GONE);
        } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX)) {
            requestTypeVal = LBL_JOB_TXT + "  " + LBL_REQUEST;
            if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                requestTypeVal = LBL_VIDEO_CONSULT_AT_YOUR_LOC;
            }
            (findViewById(R.id.serviceType)).setVisibility(View.VISIBLE);
            serviceType.setText(SelectedTypeName);
            packageInfoArea.setVisibility(View.GONE);
        } else if (REQUEST_TYPE.equals("Deliver")) {
            (findViewById(R.id.packageInfoArea)).setVisibility(View.VISIBLE);
            ((MTextView) findViewById(R.id.packageInfoTxt)).setText(generalFunc.getJsonValue("PACKAGE_TYPE", message_str));

            if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST + " (" + VehicleTypeName + ")";

            } else {
                requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST;
            }
        } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            destAddressHintTxt.setVisibility(View.GONE);
            destAddressTxt.setVisibility(View.GONE);
            (findViewById(R.id.packageInfoArea)).setVisibility(View.GONE);
            (findViewById(R.id.deliver_Area)).setVisibility(View.VISIBLE);

            if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST + " (" + VehicleTypeName + ")";

            } else {
                requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST;
            }

        } else {
            (findViewById(R.id.packageInfoArea)).setVisibility(View.GONE);

            if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_FLY_REQUEST : (LBL_RIDE + " " + LBL_REQUEST)) + " (" + VehicleTypeName + ")";
            } else {
                requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_FLY_REQUEST : (LBL_RIDE + " " + LBL_REQUEST));
            }

        }
        String ePoolRequest = generalFunc.getJsonValue("ePoolRequest", message_str);
        if (ePoolRequest != null && ePoolRequest.equalsIgnoreCase("Yes")) {
            requestTypeVal =
                    LBL_POOL_REQUEST + " ( " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("iPersonSize", message_str)) + " " +
                            LBL_PERSON + " )";
        }
    }

    public void handleGenieTimerView() {
        if (GenieOrder != null && GenieOrder.equalsIgnoreCase("Yes")) {
            findViewById(R.id.ratingBar).setVisibility(View.GONE);

        }
    }


    private void manageEta() {
        double lowestKM = Utils.CalculationByLocation(userLocation.getLatitude(), userLocation.getLongitude(), user_lat, user_lon, "");
        int lowestTime = ((int) (lowestKM * DRIVER_ARRIVED_MIN_TIME_PER_MINUTE));

        if (lowestTime < 1) {
            lowestTime = 1;
        }

        if (!isSkip) {
            findRoute("" + lowestTime + "\n" + generalFunc.retrieveLangLBl("", "LBL_MIN_SMALL_TXT"));
        } else {
            handleSourceMarker("" + lowestTime + "\n" + generalFunc.retrieveLangLBl("", "LBL_MIN_SMALL_TXT"));
        }
    }

    private void getAddressFormServer() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getCabRequestAddress");
        if (!iCabRequestId.equalsIgnoreCase("")) {
            parameters.put("iCabRequestId", iCabRequestId);
        }
        if (!iOrderId.equalsIgnoreCase("")) {
            parameters.put("iOrderId", iOrderId);
            parameters.put("eSystem", Utils.eSystem_Type);
        }

        Location providerLoc = GetLocationUpdates.getInstance().getLastLocation();
        if (providerLoc != null) {
            parameters.put("vLatitude", "" + providerLoc.getLatitude());
            parameters.put("vLongitude", "" + providerLoc.getLongitude());
        }

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);
            if (responseStringObj != null) {
                createRequestScreen(responseString);
            } else {
                if (!isFinishing()) {
                    new Handler(Looper.getMainLooper()).postDelayed(this::getAddressFormServer, 2000);
                }
            }
        });

    }

    private void createRequestScreen(String responseString) {
        JSONObject responseStringObj = generalFunc.getJsonObject(responseString);
        if (GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj)) {

            String MessageJson = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
            pickUpAddress = generalFunc.getJsonValue("tSourceAddress", MessageJson);
            destinationAddress = generalFunc.getJsonValue("tDestAddress", MessageJson);
            isVideoCall = generalFunc.getJsonValue("isVideoCall", MessageJson);
            eFly = generalFunc.getJsonValue("eFly", MessageJson).equalsIgnoreCase("Yes");
            if (isUfx) {
                String tUserComment = generalFunc.getJsonValue("tUserComment", MessageJson);
                if (Utils.checkText(tUserComment)) {
                    specialUserComment = tUserComment;
                    specialValTxt.setText(tUserComment);
                } else {
                    specialValTxt.setText("------------");
                }
            }

            REQUEST_TYPE = generalFunc.getJsonValue("eType", MessageJson);

            user_lat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("sourceLatitude", MessageJson));
            user_lon = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("sourceLongitude", MessageJson));

            String destLatitude = generalFunc.getJsonValue("destLatitude", MessageJson);
            if (destLatitude != null && !destLatitude.equalsIgnoreCase("")) {
                user_destLat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("destLatitude", MessageJson));
                user_destLon = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("destLongitude", MessageJson));
                DestLnt = new LatLng(user_destLat, user_destLon);
            } else {
                isSkip = true;
            }

            fromLnt = new LatLng(user_lat, user_lon);

            String moreServices = generalFunc.getJsonValue("moreServices", MessageJson);
            if (!moreServices.equals("") && moreServices.equals("Yes")) {
                specialValTxt.setVisibility(View.GONE);
                specialHintTxt.setVisibility(View.GONE);
                moreSeriveTxt.setVisibility(View.VISIBLE);
            }

            String VehicleTypeName = generalFunc.getJsonValue("VehicleTypeName", MessageJson);

            if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                requestTypeVal = LBL_JOB_TXT + "  " + LBL_REQUEST;
                if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                    requestTypeVal = LBL_VIDEO_CONSULT_AT_YOUR_LOC;
                }
            } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {

                if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                    requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST + " (" + VehicleTypeName + ")";

                } else {
                    requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST;
                }

                if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery) || generalFunc.getJsonValue("eType", MessageJson).equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                    deliveryDetailsBtn.setVisibility(View.VISIBLE);
                    int Total_Delivery = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue("Total_Delivery", MessageJson));
                    String ePayType = generalFunc.getJsonValue("ePayType", MessageJson);
                    String fTripGenerateFare = generalFunc.getJsonValue("fTripGenerateFare", MessageJson);
                    String fDistance = generalFunc.getJsonValue("fDistance", MessageJson);

                    if (Total_Delivery == 1) {
                        ((MTextView) findViewById(R.id.recipientHintTxt)).setText(LBL_RECIPIENT + ":");
                    } else {
                        ((MTextView) findViewById(R.id.recipientHintTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_MULTI_RECIPIENTS") + ":");
                    }

                    ((MTextView) findViewById(R.id.recipientValTxt)).setText(Utils.checkText("" + Total_Delivery) ? " " + ("" + Total_Delivery).trim() : "");

                    ((MTextView) findViewById(R.id.paymentModeValTxt)).setText(Utils.checkText(ePayType) ? " " + ePayType.trim() : "");
                    if (generalFunc.getJsonValue("ePayWallet", MessageJson).equalsIgnoreCase("Yes")) {
                        ((MTextView) findViewById(R.id.paymentModeValTxt)).setText(generalFunc.retrieveLangLBl("Wallet", "LBL_WALLET_TXT"));
                    }

                    ((MTextView) findViewById(R.id.totalMilesValTxt)).setText(Utils.checkText(fDistance) ? " " + fDistance.trim() : "");

                    ((MTextView) findViewById(R.id.totalFareValTxt)).setText(Utils.checkText(fTripGenerateFare) ? " " + fTripGenerateFare.trim() : "");

                    destAddressHintTxt.setVisibility(View.GONE);
                    destAddressTxt.setVisibility(View.GONE);
                    (findViewById(R.id.packageInfoArea)).setVisibility(View.GONE);
                }

            } else if (REQUEST_TYPE.equals("Deliver") || REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {


                if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                    requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST + " (" + VehicleTypeName + ")";
                } else {
                    requestTypeVal = LBL_DELIVERY + " " + LBL_REQUEST;
                }
            } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {

                if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                    requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_FLY_REQUEST : (LBL_RIDE + " " + LBL_REQUEST)) + " (" + VehicleTypeName + ")";
                } else {
                    requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_FLY_REQUEST : (LBL_RIDE + " " + LBL_REQUEST));
                }

                String PackageName = generalFunc.getJsonValue("PackageName", MessageJson);
                if (PackageName != null && !PackageName.equalsIgnoreCase("")) {
                    pkgType.setVisibility(View.VISIBLE);
                    pkgType.setText(PackageName);

                    if (VehicleTypeName != null && !VehicleTypeName.equalsIgnoreCase("")) {
                        requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_RENTAL_AIRCRAFT_REQUEST : LBL_RENTAL_RIDE_REQUEST) + " (" + VehicleTypeName + ")";
                    } else {
                        requestTypeVal = (isInterCity ? LBL_INTERCITY_RIDE_REQUEST : eFly ? LBL_RENTAL_AIRCRAFT_REQUEST : LBL_RENTAL_RIDE_REQUEST);
                    }
                }

            } else if (REQUEST_TYPE.equalsIgnoreCase("DeliverAll")) {

                requestTypeVal = generalFunc.retrieveLangLBl("Delivery", "LBL_DELIVERY") + " " + generalFunc.retrieveLangLBl("Request", "LBL_REQUEST");

            }

            isloadedAddress = true;

            if (destinationAddress.equalsIgnoreCase("")) {
                destinationAddress = "----";
            }

            destAddressTxt.setText(destinationAddress);
            locationAddressTxt.setText(pickUpAddress);
            ufxlocationAddressTxt.setText(pickUpAddress);

            String ePoolRequest = generalFunc.getJsonValue("ePoolRequest", message_str);

            if (ePoolRequest != null && ePoolRequest.equalsIgnoreCase("Yes")) {
                requestTypeVal = LBL_POOL_REQUEST + " ( " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("iPersonSize", message_str)) + " " + LBL_PERSON + " )";
            }

            //manageEta();

            if (GetLocationUpdates.getInstance() != null && GetLocationUpdates.getInstance().getLastLocation() != null) {
                Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();
                onLocationUpdate(lastLocation);
            }

            manageBottomDialog();
            setEstView(MessageJson);

        } else {
            if (!isFinishing()) {
                new Handler(Looper.getMainLooper()).postDelayed(this::getAddressFormServer, 2000);
            }

        }
    }


    @Override
    protected void onResume() {
        super.onResume();
        if (istimerfinish) {
            trimCache(getActContext());
            istimerfinish = false;
            finish();
        }
        if (mp != null && !mp.isPlaying()) {
            mp.setLooping(true);
            mp.start();
        }
    }

    private void trimCache(Context context) {
        try {
            File dir = context.getCacheDir();
            if (dir != null && dir.isDirectory()) {
                deleteDir(dir);
            }
        } catch (Exception e) {
            // TODO: handle exception
        }
    }

    private boolean deleteDir(File dir) {
        if (dir != null && dir.isDirectory()) {
            String[] children = dir.list();
            if (children != null) {
                for (String child : children) {
                    boolean success = deleteDir(new File(dir, child));
                    if (!success) {
                        return false;
                    }
                }
            }
        }
        return Objects.requireNonNull(dir).delete();
    }

    @Override
    protected void onDestroy() {
        removeAllEvents();
        super.onDestroy();
    }

    @Override
    protected void onPause() {
        super.onPause();
        removeSound();
    }

    @Override
    public void handleBtnClick(int btn_id) {
        Utils.hideKeyboard(CabRequestedActivity.this);

        cancelRequest(false);
    }

    public void acceptRequest() {
        /*Stop Timer*/
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }
        removeSound();
        progressLayout.setClickable(false);
        generateTrip();
    }

    public void generateTrip() {

        ServiceRequest.sendEvent(msgCode, "Attempted");
        isAnotherRequestAvailable = false;

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), generateTripParams(), true, false, generalFunc, responseString -> {
            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);

            if (responseStringObj != null) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj);

                if (isDataAvail) {
                    ServiceRequest.sendEvent(msgCode, "Accepted");

                    String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);
                    if (generalFunc.getJsonValue("isTaxiBid", message_str).equalsIgnoreCase("Yes")) {
                        offerProcessTimeEnded(msg_str);
                        return;
                    }

                    if (GetLocationUpdates.retrieveInstance() != null) {
                        GetLocationUpdates.getInstance().stopLocationUpdates(this);
                    }

                    MyApp.getInstance().refreshView(this, responseString);

                } else {
                    final String msg_str = generalFunc.getJsonValueStr(Utils.message_str, responseStringObj);

                    GenerateAlertBox alertBox = generalFunc.notifyRestartApp("", generalFunc.retrieveLangLBl("", msg_str));
                    alertBox.setCancelable(false);
                    alertBox.setBtnClickList(btn_id -> {
                        if (msg_str.equals(Utils.GCM_FAILED_KEY) || msg_str.equals(Utils.APNS_FAILED_KEY) || msg_str.equals("LBL_SERVER_COMM_ERROR") || msg_str.equals("DO_RESTART")) {
                            MyApp.getInstance().restartWithGetDataApp();
                        } else {
                            //CabRequestedActivity.super.onBackPressed();
                            MyApp.getInstance().restartWithGetDataApp();
                        }
                    });
                }
            } else {

                //startTimer(milliLeft); // start Timer From Paused Seconds - if required in future
                generalFunc.showError(i -> MyApp.getInstance().restartWithGetDataApp());
            }
        });
        exeWebServer.setCancelAble(false);
    }

    private void offerProcessTimeEnded(String msg_str) {
        removeCustomNotiSound();
        afterAcceptTaxiBidOfferArea.setVisibility(View.VISIBLE);
        taxiBidOfferAcceptMsgTxt.setText(msg_str);
        new Handler(Looper.getMainLooper()).postDelayed(() -> {
            if (!isFinishing()) {
                userOfferAcceptMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DRIVER_OFFER_NOT_ACCEPTED_USER_TAXI_BID_TXT"));

                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (!isFinishing()) {
                        // Automatically remove activity
                        MyApp.getInstance().restartWithGetDataApp();
                    }
                }, 10 * 1000);
            }
        }, GeneralFunctions.parseIntegerValue(25, generalFunc.retrieveValue("DRIVER_QUOTATION_ACCEPT_TIME_BID_TAXI")) * 1000);
    }

    public void declineTripRequest() {
        removeSound();
        ServiceRequest.sendEvent(msgCode, "Declined");
        isAnotherRequestAvailable = false;

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DeclineTripRequest");
        parameters.put("DriverID", generalFunc.getMemberId());
        parameters.put("PassengerID", generalFunc.getJsonValue("PassengerId", message_str));
        parameters.put("vMsgCode", msgCode);


        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc,
                responseString -> cancelRequest(true));
        exeWebServer.setCancelAble(false);

    }

    public HashMap<String, String> generateTripParams() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GenerateTrip");


        parameters.put("PassengerID", generalFunc.getJsonValue("PassengerId", message_str));
        parameters.put("start_lat", generalFunc.getJsonValue("sourceLatitude", message_str));
        parameters.put("start_lon", generalFunc.getJsonValue("sourceLongitude", message_str));
        parameters.put("iCabBookingId", generalFunc.getJsonValue("iBookingId", message_str));

        if (!iCabRequestId.equalsIgnoreCase("")) {
            parameters.put("iCabRequestId", iCabRequestId);
            parameters.put("DriverID", generalFunc.getMemberId());
        }
        if (!iOrderId.equalsIgnoreCase("")) {
            parameters.put("iOrderId", iOrderId);
            parameters.put("eSystem", Utils.eSystem_Type);
            parameters.put("iDriverId", generalFunc.getMemberId());
        }
        parameters.put("sAddress", pickUpAddress);
        parameters.put("GoogleServerKey", generalFunc.retrieveValue(Utils.GOOGLE_SERVER_ANDROID_DRIVER_APP_KEY));
        parameters.put("vMsgCode", msgCode);
        parameters.put("UserType", Utils.app_type);

        if (userLocation != null) {
            parameters.put("vLatitude", "" + userLocation.getLatitude());
            parameters.put("vLongitude", "" + userLocation.getLongitude());
        } else if (GetLocationUpdates.getInstance() != null && GetLocationUpdates.getInstance().getLastLocation() != null) {
            Location lastLocation = GetLocationUpdates.getInstance().getLastLocation();

            parameters.put("vLatitude", "" + lastLocation.getLatitude());
            parameters.put("vLongitude", "" + lastLocation.getLongitude());
        }

        parameters.put("REQUEST_TYPE", REQUEST_TYPE);

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            parameters.put("ride_type", REQUEST_TYPE);
        }

        if (generalFunc.getJsonValue("isTaxiBid", message_str).equalsIgnoreCase("Yes")) {
            if (yoreOfferEdit != null) {
                parameters.put("isTaxiBid", "Yes");
                parameters.put("OfferFare", yoreOfferEdit.getTxt());
            }
        }

        return parameters;
    }

    private void cancelRequest(boolean isDecline) {
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }

        generalFunc.storeData(Utils.DRIVER_CURRENT_REQ_OPEN_KEY, "false");

        cancelCabReq(isDecline);

        try {
            CabRequestedActivity.super.onBackPressed();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }

    }

    private void startTimer(long totalTimeCountInMilliseconds) {

        countDownTimer = new CountDownTimer(totalTimeCountInMilliseconds, 1000) {
            // 1000 means, onTick function will be called at every 1000
            // milliseconds

            @SuppressLint({"DefaultLocale", "SetTextI18n"})
            @Override
            public void onTick(long leftTimeInMilliseconds) {
                milliLeft = leftTimeInMilliseconds;
                long seconds = leftTimeInMilliseconds / 1000;
                // i++;
                // Setting the Progress Bar to decrease wih the timer
                mProgressBar.setProgress((int) (leftTimeInMilliseconds / 1000));
                progressbar_dialog.setProgress((int) (leftTimeInMilliseconds / 1000));
                textViewShowTime.setTextAppearance(getActContext(), android.R.color.holo_green_dark);
                tvTimeCount_dialog.setTextAppearance(getActContext(), android.R.color.holo_green_dark);
                try {
                    Uri notification = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                    if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_1.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_1);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_2.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_2);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_3.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_3);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_4.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_4);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_5.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_5);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_6.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_6);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_7.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_7);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_8.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_8);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_9.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_9);
                    } else if (generalFunc.getJsonValue("DIAL_NOTIFICATION", userProfileJson).equalsIgnoreCase("dial_notification_10.mp3")) {
                        notification = Uri.parse(ContentResolver.SCHEME_ANDROID_RESOURCE + "://" + getActContext().getPackageName() + "/" + R.raw.notification_10);
                    }
                    if (mp == null) {
                        mp = new MediaPlayer();
                        mp = MediaPlayer.create(getApplicationContext(), notification);
                        mp.setLooping(true);
                        mp.start();
                    }
                } catch (Exception e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }

                if (leftTimeInMilliseconds < timeBlinkInMilliseconds) {

                    if (blink) {
                        textViewShowTime.setVisibility(View.VISIBLE);
                        tvTimeCount_dialog.setVisibility(View.VISIBLE);
                    } else {
                        textViewShowTime.setVisibility(View.INVISIBLE);
                        tvTimeCount_dialog.setVisibility(View.INVISIBLE);
                    }

                    blink = !blink;
                }

                textViewShowTime.setText(String.format("%02d", seconds / 60) + ":" + String.format("%02d", seconds % 60));
                tvTimeCount_dialog.setText(String.format("%02d", seconds / 60) + ":" + String.format("%02d", seconds % 60));

            }

            @Override
            public void onFinish() {
                istimerfinish = true;

                ServiceRequest.sendEvent(msgCode, "Timeout");
                isAnotherRequestAvailable = false;

                textViewShowTime.setVisibility(View.VISIBLE);
                tvTimeCount_dialog.setVisibility(View.VISIBLE);
                progressLayout.setClickable(false);
                cancelRequest(false);
            }

        }.start();

    }

    private void removeAllEvents() {

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        try {
            PolyLineAnimator.getInstance().stopRouteAnim();
            if (route_polyLine != null) {
                route_polyLine.remove();
                route_polyLine = null;
            }
        } catch (Exception e) {

        }

        removeCustomNotiSound();
    }

    private void removeCustomNotiSound() {
        if (mp != null) {
            mp.stop();
            mp = null;
        }

        if (countDownTimer != null) {
            countDownTimer.cancel();
        }
    }

    public void removeSound() {
        if (mp != null) {
            mp.pause();
        }
    }

    private void cancelCabReq(boolean isDecline) {
        String PassengerId = generalFunc.getJsonValue("PassengerId", message_str);

        ArrayList<String> channelName = new ArrayList<>();
        channelName.add("PASSENGER_" + PassengerId);

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX) && isDecline) {
            String jsonString = generalFunc.buildRequestCancelJson(PassengerId, msgCode);
            String finalJson = "";
            try {
                JSONObject item = new JSONObject(jsonString);
                item.put("isDecline", "Yes");
                finalJson = item.toString();

            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

            AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).setMessage(finalJson).build(), AppService.Event.PUBLISH);
        } else {
            AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).setMessage(generalFunc.buildRequestCancelJson(PassengerId, msgCode)).build(), AppService.Event.PUBLISH);
        }
        generalFunc.storeData(Utils.DRIVER_CURRENT_REQ_OPEN_KEY, "false");
    }

    private Context getActContext() {
        return CabRequestedActivity.this;
    }

    @Override
    public void onBackPressed() {
        cancelCabReq(false);

        removeAllEvents();
        super.onBackPressed();
    }

    View marker_view;
    View marker_view1;

    @Override
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        gMap = googleMap;
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);

        googleMap.getUiSettings().setZoomControlsEnabled(false);
        googleMap.getUiSettings().setTiltGesturesEnabled(false);
        googleMap.getUiSettings().setCompassEnabled(false);
        googleMap.getUiSettings().setMyLocationButtonEnabled(false);
        googleMap.getUiSettings().setMapToolbarEnabled(false);
    }

    double user_lat = 0;
    double user_lon = 0;
    double user_destLat = 0;
    double user_destLon = 0;

    boolean isSkip = false;
    LatLng fromLnt;
    LatLng DestLnt;

    private void handleSourceMarker(String etaVal) {
        if (marker_view == null) {
            marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                    .inflate(R.layout.custom_marker, null);
            addressTxt = (MTextView) marker_view
                    .findViewById(R.id.addressTxt);
            etaTxt = (MTextView) marker_view.findViewById(R.id.etaTxt);
        }

        if (marker_view != null) {
            etaTxt = (MTextView) marker_view.findViewById(R.id.etaTxt);
        }

        addressTxt.setTextColor(getActContext().getResources().getColor(R.color.sourceAddressTxt));

        if (isSkip) {

            if (destMarker != null) {
                destMarker.remove();
                destMarker = null;
            }
            if (destDotMarker != null) {
                destDotMarker.remove();
            }
            if (route_polyLine != null) {
                route_polyLine.remove();
            }

        }


        etaTxt.setVisibility(View.VISIBLE);
        etaTxt.setText(etaVal);

        if (sourceMarker != null) {
            sourceMarker.remove();
            sourceMarker = null;
        }

        if (source_dot_option != null && sourceDotMarker != null) {
            sourceDotMarker.remove();
            sourceDotMarker = null;
            source_dot_option = null;
        }

        source_dot_option = new MarkerOptions().position(fromLnt).icon(bitmapDescriptorFromVector(getActContext(), R.drawable.ic_source_locate));

        if (gMap != null) {
            sourceDotMarker = gMap.addMarker(source_dot_option);
        }

        addressTxt.setText(GeneralFunctions.fromHtml(pickUpAddress));
        MarkerOptions marker_opt_source = new MarkerOptions().position(fromLnt).icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))).anchor(0.00f, 0.20f);
        if (gMap != null) {
            sourceMarker = gMap.addMarker(marker_opt_source);
            sourceMarker.setTag("1");
        }

        buildBuilder(-1);
    }


    private void handleMapAnimation(String responseString, LatLng sourceLocation, LatLng destLocation, String etaVal, String timeVal) {
        try {
            if (isSkip) {
                PolyLineAnimator.getInstance().stopRouteAnim();
                if (route_polyLine != null) {
                    route_polyLine.remove();
                    route_polyLine = null;
                }
                return;
            }

            PolyLineAnimator.getInstance().stopRouteAnim();

            LatLng toLnt = new LatLng(destLocation.latitude, destLocation.longitude);


            if (marker_view == null) {

                marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                        .inflate(R.layout.custom_marker, null);
                addressTxt = (MTextView) marker_view
                        .findViewById(R.id.addressTxt);
                etaTxt = (MTextView) marker_view.findViewById(R.id.etaTxt);
            }

            addressTxt.setTextColor(getActContext().getResources().getColor(R.color.destAddressTxt));


            addressTxt.setText(destinationAddress);

            MarkerOptions marker_opt_dest = new MarkerOptions().position(toLnt);
            etaTxt.setVisibility(View.GONE);

            marker_opt_dest.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))).anchor(0.00f, 0.20f);
            if (dest_dot_option != null) {
                destDotMarker.remove();
            }
            dest_dot_option = new MarkerOptions().position(toLnt).icon(bitmapDescriptorFromVector(getActContext(), R.drawable.ic_dest_locate));
            destDotMarker = gMap.addMarker(dest_dot_option);

            if (destMarker != null) {
                destMarker.remove();
            }
            destMarker = gMap.addMarker(marker_opt_dest);
            destMarker.setTag("2");

            handleSourceMarker(etaVal);

            JSONArray obj_routes1 = generalFunc.getJsonArray("routes", responseString);

            if (enableGoogleDirection && obj_routes1 != null && obj_routes1.length() > 0) {
                PolylineOptions lineOptions = null;

                if (enableGoogleDirection && !eFly) {
                    if (isGoogle) {
                        HashMap<String, String> routeMap = new HashMap<>();
                        routeMap.put("routes", generalFunc.getJsonArray("routes", responseString).toString());
                        responseString = routeMap.toString();
                        lineOptions = generalFunc.getGoogleRouteOptions(responseString, Utils.dipToPixels(getActContext(), 5), getActContext().getResources().getColor(R.color.black));
                    } else {
                        HashMap<String, String> routeMap = new HashMap<>();
                        routeMap.put("routes", generalFunc.getJsonArray("routes", responseString).toString());
                        responseString = routeMap.toString();
                        lineOptions = getGoogleRouteOptionsHandle(responseString, Utils.dipToPixels(getActContext(), 5), getActContext().getResources().getColor(R.color.black));
                    }
                } else {
                    lineOptions = createCurveRoute(new LatLng(sourceLocation.latitude, sourceLocation.longitude), new LatLng(destLocation.latitude, destLocation.longitude));
                }

                if (lineOptions != null) {
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                        route_polyLine = null;
                    }
                    route_polyLine = gMap.addPolyline(lineOptions);
                    route_polyLine.remove();
                }
            }

            DisplayMetrics metrics = new DisplayMetrics();
            getWindowManager().getDefaultDisplay().getMetrics(metrics);

            if (route_polyLine != null && route_polyLine.getPoints().size() > 1) {
                PolyLineAnimator.getInstance().animateRoute(gMap, route_polyLine.getPoints(), getActContext());
            }

            gMap.setOnCameraMoveListener(() -> {
                DisplayMetrics displaymetrics = new DisplayMetrics();
                getWindowManager().getDefaultDisplay().getMetrics(displaymetrics);

            });

            runOnUiThread(() -> {
                if (route_polyLine != null && route_polyLine.getPoints().size() > 1 && !isSkip) {
                    if (marker_view1 == null) {
                        marker_view1 = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.custom_marker_eta, null);
                        MTextView timeText = (MTextView) marker_view1.findViewById(R.id.etaTxt);
                        timeText.setText((Utils.checkText(timeVal) && timeVal.length() > 8 ? " " + " " : "") + timeVal);
                    }
                    MarkerOptions op = new MarkerOptions()
                            .position(route_polyLine.getPoints().get(route_polyLine.getPoints().size() / 2))
                            .icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view1)))
                            .anchor(0.00f, 0.20f);
                    gMap.addMarker(op);
                }
            });

        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    private PolylineOptions getGoogleRouteOptionsHandle(String directionJson, int width, int color) {
        PolylineOptions lineOptions = new PolylineOptions();


        try {
            JSONArray obj_routes1 = generalFunc.getJsonArray("routes", directionJson);

            ArrayList<LatLng> points = new ArrayList<>();

            if (obj_routes1.length() > 0) {
                // Fetching i-th route
                // Fetching all the points in i-th route
                for (int j = 0; j < obj_routes1.length(); j++) {

                    JSONObject point = generalFunc.getJsonObject(obj_routes1, j);

                    LatLng position = new LatLng(GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("latitude", point).toString()), GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("longitude", point).toString()));


                    points.add(position);

                }


                lineOptions.addAll(points);
                lineOptions.width(width);
                lineOptions.color(color);

                return lineOptions;
            } else {
                return null;
            }
        } catch (Exception e) {
            return null;
        }
    }

    private BitmapDescriptor bitmapDescriptorFromVector(Context context, int vectorResId) {
        Drawable vectorDrawable = ContextCompat.getDrawable(context, vectorResId);
        vectorDrawable.setBounds(0, 0, Utils.dpToPx(25, getActContext()), Utils.dpToPx(25, getActContext()));
        Bitmap bitmap = Bitmap.createBitmap(Utils.dpToPx(25, getActContext()), Utils.dpToPx(25, getActContext()), Bitmap.Config.ARGB_8888);
        Canvas canvas = new Canvas(bitmap);
        vectorDrawable.draw(canvas);
        return BitmapDescriptorFactory.fromBitmap(bitmap);
    }

    boolean isRouteFail = false;
    boolean isGoogle = false;

    private void findRoute(String etaVal) {
        if (isFindRoute) {
            return;
        }
        isFindRoute = true;
        AppService.getInstance().executeService(getActContext(), new DataProvider.DataProviderBuilder(fromLnt.latitude + "", fromLnt.longitude + "").setDestLatitude(DestLnt.latitude + "").setDestLongitude(DestLnt.longitude + "").setWayPoints(new JSONArray()).build(), AppService.Service.DIRECTION, data -> {
            if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                return;
            }
            manageResult(data, fromLnt, DestLnt);
        });
    }

    private void manageResult(HashMap<String, Object> data, LatLng sourceLatLng, LatLng destLatLng) {
        String responseString = "";
        if (data != null && data.containsKey("ROUTES") && Utils.checkText(String.valueOf(data.get("ROUTES")))) {
            responseString = String.valueOf(data.get("ROUTES"));
        }


        if (!isFindRoute) {

            if (Utils.checkText(responseString)) {
                if (!responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {

                    responseString = generalFunc.getJsonValue("routes", responseString);

                    JSONArray obj_routes = generalFunc.getJsonArray(responseString);
                    if (obj_routes != null && obj_routes.length() > 0) {
                        JSONObject obj_legs = generalFunc.getJsonObject(generalFunc.getJsonArray("legs", generalFunc.getJsonObject(obj_routes, 0).toString()), 0);

                        pickUpAddress = generalFunc.getJsonValue("start_address", obj_legs.toString());
                        destinationAddress = generalFunc.getJsonValue("end_address", obj_legs.toString());
                    }
                    isloadedAddress = true;
                    if (destinationAddress.equalsIgnoreCase("")) {
                        destinationAddress = "----";
                    }
                    destAddressTxt.setText(destinationAddress);
                    locationAddressTxt.setText(pickUpAddress);
                    ufxlocationAddressTxt.setText(pickUpAddress);
                }

            }
        } else {
            if (Utils.checkText(responseString)) {
                isRouteFail = false;

                if (Utils.checkText(responseString) && data != null && data.get("DISTANCE") == null) {
                    isGoogle = true;

//                    JSONArray obj_routes = generalFunc.getJsonArray(responseString);
                    JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);

                    if (obj_routes != null && obj_routes.length() > 0) {

                        JSONObject obj_legs = generalFunc.getJsonObject(generalFunc.getJsonArray("legs", generalFunc.getJsonObject(obj_routes, 0).toString()), 0);

                        double vDistance = GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("distance", obj_legs.toString())));

                        distance = "" + vDistance;

                        time = "" + (GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("duration", obj_legs.toString()))));

                        sourceLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lat", generalFunc.getJsonValue("start_location", obj_legs.toString()))),
                                GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lng", generalFunc.getJsonValue("start_location", obj_legs.toString()))));

                        destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lat", generalFunc.getJsonValue("end_location", obj_legs.toString()))),
                                GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lng", generalFunc.getJsonValue("end_location", obj_legs.toString()))));
                        //temp animation test
                        String time1 = generalFunc.getJsonValue("text", generalFunc.getJsonValue("duration", obj_legs.toString()));


                        handleMapAnimation(responseString, sourceLocation, destLocation, getRouteDistance(vDistance) /*etaVal*/, time1);
                    }

                } else {
                    isGoogle = false;
                    if (data != null) {
                        distance = String.valueOf(data.get("DISTANCE"));
                        time = String.valueOf(data.get("DURATION"));
                    }

                    sourceLocation = sourceLatLng;


                    destLocation = destLatLng;


                    HashMap<String, String> data_dict = new HashMap<>();
                    if (data != null) {
                        data_dict.put("routes", String.valueOf(data.get("ROUTES")));
                    }

                    handleMapAnimation(getRouteDetails(data_dict), sourceLocation, destLocation, getRouteDistance(0) /*etaVal*/, getTimeTxt((int) (GeneralFunctions.parseDoubleValue(0, time) / 60)));
                }


            }
        }


    }

    private String getTimeTxt(int duration) {

        if (duration < 1) {
            duration = 1;
        }
        String durationTxt = "";
        String timeToreach = duration == 0 ? "--" : "" + duration;

        timeToreach = duration > 60 ? formatHoursAndMinutes(duration) : timeToreach;


        durationTxt = (duration < 60 ? generalFunc.retrieveLangLBl("", "LBL_MINS_SMALL") : generalFunc.retrieveLangLBl("", "LBL_HOUR_TXT"));

        durationTxt = duration == 1 ? generalFunc.retrieveLangLBl("", "LBL_MIN_SMALL") : durationTxt;
        durationTxt = duration > 120 ? generalFunc.retrieveLangLBl("", "LBL_HOURS_TXT") : durationTxt;

        return timeToreach + " " + durationTxt;
    }

    private String getRouteDistance(double distance) {
        if (!isGoogle) {
            distance = GeneralFunctions.calculationByLocation(user_lat, user_lon, user_destLat, user_destLon, "KM");
        } else {
            distance = distance / 1000;
        }


        String eUnit = generalFunc.getJsonValueStr("eUnit", userProfileJsonObj);
        if (!eUnit.equalsIgnoreCase("KMs")) {
            distance = distance * 0.621371;
        }
        distance = generalFunc.round(distance, 2);
        if (eUnit.equalsIgnoreCase("KMs")) {
            return distance + "\n" + generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT");
        } else {
            return distance + "\n " + generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT");
        }
    }

    private void buildBuilder(int paddingVal) {
        try {


            if (sourceMarker != null && (destMarker == null)) {

                builder = new LatLngBounds.Builder();

                builder.include(sourceMarker.getPosition());

                DisplayMetrics metrics = new DisplayMetrics();
                getWindowManager().getDefaultDisplay().getMetrics(metrics);

                int width = metrics.widthPixels;

                //  int padding = (mainAct != null && mainAct.isMultiDelivery()) ? (width != 0 ? (int) (width * 0.35) : 0) : 0; // offset from edges of the map in pixels

                LatLngBounds bounds = builder.build();
                LatLng center = bounds.getCenter();
                LatLng northEast = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
                LatLng southWest = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));
                builder.include(southWest);
                builder.include(northEast);
                int padding = (int) (width * 0.30);

                gMap.moveCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), padding));
                gMap.setPadding(0, 0, 0, peekHeight);
            } else if (gMap != null) {
                boolean isBoundIncluded = false;
                LatLngBounds.Builder builder = new LatLngBounds.Builder();
                if (sourceMarker != null) {
                    isBoundIncluded = true;
                    builder.include(sourceMarker.getPosition());
                }

                if (destMarker != null) {
                    isBoundIncluded = true;
                    builder.include(destMarker.getPosition());
                }

                if (isBoundIncluded) {
                    LatLngBounds bounds = builder.build();

                    LatLng center = bounds.getCenter();
                    LatLng northEast = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
                    LatLng southWest = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));

                    builder.include(southWest);
                    builder.include(northEast);

                    DisplayMetrics metrics = new DisplayMetrics();
                    getWindowManager().getDefaultDisplay().getMetrics(metrics);

                    int width = metrics.widthPixels;
                    // Set Padding according to included bounds
                    int padding = (int) (width * 0.30); // offset from edges of the map 10% of screen
                    gMap.moveCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), padding));
                    gMap.setPadding(0, 0, 0, peekHeight);
                }
            }
            if (isUfx) {

                if (gMap != null) {
                    gMap.moveCamera(CameraUpdateFactory.newLatLngZoom(builder.build().getCenter(), 18));
                }

            }
        } catch (Exception e) {

        }
    }

    private Bitmap createDrawableFromView(Context context, View view) {
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

    @Override
    public void onLocationUpdate(Location location) {
        this.userLocation = location;

        if (fromLnt != null) {
            Logger.d("onLocationUpdate", ":: called");
            manageEta();
        }
    }

    private void manageBottomDialog() {
        setButtons();

        MTextView requestType = findViewById(R.id.requestType);
        requestType.setSelected(true);
        requestType.setText(requestTypeVal);

        MTextView declinebtnTxt = findViewById(R.id.declinebtnTxt);
        MTextView AcceptbtnTxt = findViewById(R.id.AcceptbtnTxt);
        MTextView sourceAddressHTxt = findViewById(R.id.sourceAddressHTxt);
        MTextView sourceAddressTxt = findViewById(R.id.sourceAddressTxt);
        MTextView destAddressHTxt = findViewById(R.id.destAddressHTxt);
        MTextView destAddressTxt = findViewById(R.id.destAddressTxt);

        ImageView imagedest = findViewById(R.id.imagedest);
        DividerView dashImage = findViewById(R.id.dashImage);
        LinearLayout btnArea = findViewById(R.id.btnArea);
        ImageView btnImg = findViewById(R.id.btnImg);
        LinearLayout destarea = findViewById(R.id.destarea);

        ImageView srcimage = findViewById(R.id.srcimage);
        ImageView imgVideoConsult = findViewById(R.id.imgVideoConsult);
        imgVideoConsult.setVisibility(View.GONE);

        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.login_border_rtl));
        }

        btnArea.setOnClickListener(v -> acceptRequest());
        declinebtnTxt.setOnClickListener(v -> declineTripRequest());

        if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)) {
            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOCATION_HEADER_TXT"));
            destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DEST_ADD_TXT"));

            if (eFly) {
                destarea.setVisibility(View.GONE);
                imagedest.setVisibility(View.GONE);
                dashImage.setVisibility(View.GONE);
            }

        } else if (REQUEST_TYPE.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            destarea.setVisibility(View.GONE);
            imagedest.setVisibility(View.GONE);
            dashImage.setVisibility(View.GONE);
            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("Job Location", "LBL_JOB_LOCATION_TXT"));

            if (isVideoCall != null && isVideoCall.equalsIgnoreCase("Yes")) {
                sourceAddressHTxt.setVisibility(View.GONE);
                sourceAddressTxt.setVisibility(View.GONE);
                srcimage.setVisibility(View.GONE);
                imgVideoConsult.setVisibility(View.VISIBLE);
            }
        } else if (REQUEST_TYPE.equals("Deliver") || REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SENDER_LOCATION"));
            destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RECEIVER_LOCATION"));

            if (REQUEST_TYPE.equalsIgnoreCase(Utils.eType_Multi_Delivery)) {
                destarea.setVisibility(View.GONE);
                imagedest.setVisibility(View.GONE);
                dashImage.setVisibility(View.GONE);
            }
        } else {
            sourceAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_FOR_FRONT"));
            destAddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DELIVERY_LOCATION_TXT"));
        }

        destAddressTxt.setText(destinationAddress);
        sourceAddressTxt.setText(pickUpAddress);

        if (isSkip) {
            destarea.setVisibility(View.GONE);
            imagedest.setVisibility(View.GONE);
            dashImage.setVisibility(View.GONE);
        }

        declinebtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        AcceptbtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));

        handleGenieTimerView();

        progressLayout_frame.setVisibility(View.VISIBLE);
        progressLayout_frame_dialog.setVisibility(View.GONE);
    }

    private void setButtons() {

        MTextView declinebtnTxt1 = findViewById(R.id.declinebtnTxt1);
        MTextView AcceptbtnTxt1 = findViewById(R.id.AcceptbtnTxt1);
        ImageView btnImg = findViewById(R.id.btnImg1);
        LinearLayout btnArea1 = findViewById(R.id.btnArea1);
        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea1.setBackground(ContextCompat.getDrawable(getActContext(), R.drawable.login_border_rtl));
        }

        declinebtnTxt1.setText(generalFunc.retrieveLangLBl("", "LBL_DECLINE_TXT"));
        AcceptbtnTxt1.setText(generalFunc.retrieveLangLBl("", "LBL_ACCEPT_TXT"));

        btnArea1.setOnClickListener(v -> acceptRequest());
        declinebtnTxt1.setOnClickListener(v -> declineTripRequest());
    }

    private String getRouteDetails(HashMap<String, String> directionlist) {
        HashMap<String, String> routeMap = new HashMap<>();
        routeMap.put("routes", directionlist.get("routes"));
        return routeMap.toString();
    }

    private String formatHoursAndMinutes(int totalMinutes) {
        String minutes = Integer.toString(totalMinutes % 60);
        minutes = minutes.length() == 1 ? "0" + minutes : minutes;
        return (totalMinutes / 60) + ":" + minutes;
    }


    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == deliveryDetailsBtn.getId()) {
            Bundle bn = new Bundle();
            bn.putString("TripId", "");
            bn.putString("iCabBookingId", generalFunc.getJsonValue("iBookingId", message_str));
            bn.putString("iCabRequestId", iCabRequestId);
            bn.putString("Status", "cabRequestScreen");
            new ActUtils(getActContext()).startActWithData(ViewMultiDeliveryDetailsActivity.class, bn);
        } else if (i == moreSeriveTxt.getId()) {
            Bundle bundle = new Bundle();
            bundle.putString("iCabRequestId", iCabRequestId);
            new ActUtils(getActContext()).startActWithData(MoreServiceInfoActivity.class, bundle);
        }
    }
}