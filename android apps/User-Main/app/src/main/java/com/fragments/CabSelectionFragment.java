package com.fragments;


import static com.utils.MapUtils.createDrawableFromView;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.Point;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewTreeObserver;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.LinearSmoothScroller;
import androidx.recyclerview.widget.RecyclerView;

import com.act.ContactUsActivity;
import com.act.FareBreakDownActivity;
import com.act.MainActivity;
import com.act.PaymentWebviewActivity;
import com.act.RentalDetailsActivity;
import com.adapter.files.CabTypeAdapter;
import com.adapter.files.PoolSeatsSelectionAdapter;
import com.drawRoute.DirectionsJSONParser;
import com.fontanalyzer.SystemFont;
import com.general.files.ActUtils;
import com.general.files.CovidDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.general.files.StopOverComparator;
import com.general.files.StopOverPointsDataParser;
import com.general.files.ToolTipDialog;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DailogFaredetailsBinding;
import com.map.BitmapDescriptorFactory;
import com.map.GeoMapLoader;
import com.map.Marker;
import com.map.Polyline;
import com.map.helper.PolyLineAnimator;
import com.map.helper.SphericalUtil;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.MarkerOptions;
import com.map.models.PolylineOptions;
import com.model.ContactModel;
import com.model.Stop_Over_Points_Data;
import com.model.getProfilePaymentModel;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.DataProvider;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.DateTimeUtils;
import com.utils.LayoutDirection;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;

/**
 * A simple {@link Fragment} subclass.
 */
public class CabSelectionFragment extends BaseFragment implements CabTypeAdapter.OnItemClickList, /*ViewTreeObserver.OnGlobalLayoutListener,*/ PoolSeatsSelectionAdapter.OnItemClickList {

    private final static double DEFAULT_CURVE_ROUTE_CURVATURE = 0.5f;
    private final static int DEFAULT_CURVE_POINTS = 60;

    private MainActivity mainAct;
    private GeneralFunctions generalFunc;
    private MTextView payTypeTxt, payTypeHTxt;
    private ImageView payImgView, errorImage;
    public MButton ride_now_btn;
    ImageView scheduleArrowImage;
    public int currentPanelDefaultStateHeight = 100;
    public String currentCabGeneralType = "";
    public CabTypeAdapter adapter;
    public ArrayList<HashMap<String, String>> cabTypeList;
    public ArrayList<HashMap<String, String>> rentalTypeList;
    public ArrayList<HashMap<String, String>> tempCabTypeList = new ArrayList<>();
    public String app_type = "Ride";
    public ImageView img_ridelater;
    public boolean isclickableridebtn = false;
    public boolean isroutefound = false;
    public int selpos = 0;
    public View view = null;
    public boolean isCardValidated = true;
    public boolean isSkip = false;
    public LatLng sourceLocation = null;
    public LatLng destLocation = null;
    public Marker sourceMarker, destMarker, sourceDotMarker, destDotMarker;
    public View requestPayArea;

    LinearLayout imageLaterarea;
    LinearLayout btnArea;
    String userProfileJson = "";
    String LBL_CURRENT_PERSON_LIMIT;
    RecyclerView carTypeRecyclerView;
    String currency_sign = "";
    boolean isKilled = false;
    LinearLayout paymentArea;

    public String distance = "";
    public String time = "";
    AVLoadingIndicatorView loaderView;
    MTextView noServiceTxt;
    boolean isCardnowselcted = false;
    boolean isCardlaterselcted = false;
    String RideDeliveryType = "";
    int i = 0;
    ServerTask estimateFareTask;
    Polyline route_polyLine;
    boolean isRouteFail = false;
    int height = 0;
    int width = 0;

    MarkerOptions source_dot_option, dest_dot_option;
    String required_str = "";
    ProgressBar mProgressBar;
    AlertDialog outstanding_dialog;

    LinearLayout detailArea;

    //#UberPool
    /*UberPool Related Declaration Start*/
    public MButton confirm_seats_btn;
    public ImageView poolBackImage;
    public PoolSeatsSelectionAdapter seatsSelectionAdapter;
    public MTextView poolTxt, poolFareTxt, poolnoseatsTxt, poolnoteTxt;
    RecyclerView poolSeatsRecyclerView;
    public LinearLayout cashCardArea, poolArea;
    public View mainContentArea;
    public int seatsSelectpos = 0;

    String routeDrawResponse = "";
    public ArrayList<String> poolSeatsList = new ArrayList<>();
    /*UberPool Related Declaration End*/

    public ImageView rentalBackImage;
    MTextView rentalPkg, rentalPkg2;
    public RelativeLayout rentalarea, rentalArea2;
    public MTextView rentalPkgDesc;
    public RelativeLayout rentalMainArea;
    public static int RENTAL_REQ_CODE = 1234;
    public String iRentalPackageId = "";

    View marker_view;
    MTextView addressTxt, etaTxt;
    boolean isRental = false;
    int lstSelectpos = 0;
    LinearLayout organizationArea;
    LinearLayout showDropDownArea;
    MTextView organizationTxt;
    int noOfSeats = 2;
    AppCompatImageView rentIconImage, rentIconImage2;


    ArrayList<Stop_Over_Points_Data> wayPointslist = new ArrayList<>();  // List of Way Points/ middle points
    ArrayList<Stop_Over_Points_Data> destPointlist = new ArrayList<>();  // destination Points
    ArrayList<Stop_Over_Points_Data> finalPointlist = new ArrayList<>();  // final Points list with time & distance & based on shortest location first algorithm
    ArrayList<Stop_Over_Points_Data> stop_Over_Points_Temp_Array = new ArrayList<Stop_Over_Points_Data>();
    LatLngBounds.Builder builder = new LatLngBounds.Builder();
    private String APP_PAYMENT_MODE = "";
    private String APP_PAYMENT_METHOD = "";
    private String SYSTEM_PAYMENT_FLOW = "";
    private AlertDialog uploadImgAlertDialog;
    private static final int WEBVIEWPAYMENT = 001;

    View verticalscrollIndicator;
    ImageView arrowImg;
    FrameLayout cabBottomSheetLayout;
    LinearLayout tollbarArea, sendRequestArea, tempRentalArea;
    public int tempMeasuredHeight;
    public int tempState;
    private int rvPaddignHeight, rentalAreaHeight = 0;
    private View testBgView;
    public LinearLayout design_linear_layout_car_details;
    DailogFaredetailsBinding fareDetailBinding;
    BottomSheetDialog faredialog;
    boolean isFirstTime = true;
    boolean carDetailsDialogLastVisibleState = false;
    boolean reqpayAreaLastVisibleState = true;
    public boolean isRentalClick = false;
    private MTextView mordetailsTxt, pkgMsgTxt, carTypeTitle, fareVTxt, personsizeTxt;
    private AppCompatImageView morwArrow, imagecar;
    private ImageView DeliveryHelper;
    private int collapsedRvPadding, expandRvPadding;
    private boolean isHeightGiven = false;
    private int currentState;
    private boolean isRentalDesignClick = false;
    int noServiceTextTopMargin;
    private boolean isCallDirectionApi = false;

    @SuppressLint("ClickableViewAccessibility")
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        if (view != null) {
            return view;
        }


        view = inflater.inflate(R.layout.fragment_new_cab_selection, container, false);
        mainAct = (MainActivity) getActivity();
        generalFunc = mainAct.generalFunc;
        cabBottomSheetLayout = view.findViewById(R.id.bottom_sheet_cab_frag_layout);
        mainAct.cabBottomSheetBehavior = BottomSheetBehavior.from(cabBottomSheetLayout);
        tempRentalArea = view.findViewById(R.id.tempRentalArea);
        requestPayArea = view.findViewById(R.id.requestPayArea);
        tollbarArea = view.findViewById(R.id.tollbarArea);
        sendRequestArea = view.findViewById(R.id.sendRequestArea);
        tempMeasuredHeight = (int) (Utils.getScreenPixelHeight(getActContext()) / 2) + 10;
        tempState = BottomSheetBehavior.STATE_COLLAPSED;
        //#UberPool
        /* Pool related views declaration started */
        rentIconImage = view.findViewById(R.id.rentIconImage);
        rentIconImage2 = view.findViewById(R.id.rentIconImage2);
        poolBackImage = view.findViewById(R.id.poolBackImage);
        poolFareTxt = view.findViewById(R.id.poolFareTxt);
        poolTxt = view.findViewById(R.id.poolTxt);
        poolnoseatsTxt = view.findViewById(R.id.poolnoseatsTxt);
        poolnoteTxt = view.findViewById(R.id.poolnoteTxt);
        poolSeatsRecyclerView = view.findViewById(R.id.poolSeatsRecyclerView);
        cashCardArea = view.findViewById(R.id.cashcardarea);
        poolArea = view.findViewById(R.id.poolArea);
        mainContentArea = view.findViewById(R.id.mainContentArea);
        rentalPkgDesc = view.findViewById(R.id.rentalPkgDesc);
        rentalMainArea = view.findViewById(R.id.rentalMainArea);
        testBgView = view.findViewById(R.id.testBgView);
        collapsedRvPadding = getResources().getDimensionPixelSize(R.dimen._300sdp);
        expandRvPadding = getResources().getDimensionPixelSize(R.dimen._80sdp);
        if (mainAct.isVerticalCabscroll) {

            verticalscrollIndicator = view.findViewById(R.id.verticalscrollIndicator);
            verticalscrollIndicator.setVisibility(View.VISIBLE);
            rentalPkgDesc.setVisibility(View.VISIBLE);
            MTextView titleTxt = view.findViewById(R.id.titleTxt);
            arrowImg = view.findViewById(R.id.arrowImg);
            if (generalFunc.isRTLmode()) {
                arrowImg.setRotation(90);
            }
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_RIDE_TXT"));
            if (mainAct.isMultiDelivery() || mainAct.isDeliver(mainAct.getCurrentCabGeneralType())) {
                titleTxt.setText(generalFunc.retrieveLangLBl("Choose Delivery", "LBL_CHOOSE_DELIVERY_TXT"));
            }
            tollbarArea.findViewById(R.id.backImgView).setOnClickListener(v -> mainAct.cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED));
        }
        addToClickHandler(poolBackImage);
        /* Pool related views declaration ended */

        detailArea = view.findViewById(R.id.detailArea);

        rentalBackImage = view.findViewById(R.id.rentalBackImage);
        rentalarea = view.findViewById(R.id.rentalarea);
        rentalArea2 = view.findViewById(R.id.rentalArea2);
        organizationArea = view.findViewById(R.id.organizationArea);
        showDropDownArea = view.findViewById(R.id.showDropDownArea);
        rentalPkg = view.findViewById(R.id.rentalPkg);
        rentalPkg2 = view.findViewById(R.id.rentalPkg2);
        design_linear_layout_car_details = view.findViewById(R.id.design_linear_layout_car_details);

        organizationTxt = view.findViewById(R.id.organizationTxt);
        img_ridelater = view.findViewById(R.id.img_ridelater);
        addToClickHandler(rentalBackImage);
        addToClickHandler(rentalPkg);
        addToClickHandler(rentalArea2);
        addToClickHandler(img_ridelater);
        addToClickHandler(organizationArea);
        if (generalFunc.isRTLmode()) {
            rentalBackImage.setRotation(180);
            img_ridelater.setRotationY(180);
            poolBackImage.setRotation(180);
        }

        for (int i = 0; i < noOfSeats; i++) {
            poolSeatsList.add("" + (i + 1));
        }


        poolnoseatsTxt.setText(generalFunc.retrieveLangLBl("How Many seats do you need?", "LBL_POOL_SEATS"));
        poolnoteTxt.setText(generalFunc.retrieveLangLBl("This fare is based on our estimation. This may vary during trip and final fare.", "LBL_GENERAL_NOTE_FARE_EST"));
        poolTxt.setText(generalFunc.retrieveLangLBl("Pool", "LBL_POOL"));
        if (mainAct.eShowOnlyMoto != null && mainAct.eShowOnlyMoto.equalsIgnoreCase("Yes")) {
            rentalPkg.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_MOTO_TITLE_TXT"));
            rentalPkg2.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_MOTO_TITLE_TXT"));
            img_ridelater.setImageResource(R.drawable.bike_later);
            rentIconImage.setImageResource(R.drawable.ic_rentabike);
            rentIconImage2.setImageResource(R.drawable.ic_rentabike);
        } else if (mainAct.eFly) {
            rentalPkg.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_AIRCRAFT_TITLE_TXT"));
            rentalPkg2.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_AIRCRAFT_TITLE_TXT"));
            rentIconImage.setImageResource(R.drawable.ic_air_freight_new);
            rentIconImage2.setImageResource(R.drawable.ic_air_freight_new);

        } else {
            rentIconImage.setImageResource(R.drawable.car_rental);
            rentIconImage2.setImageResource(R.drawable.car_rental);
            rentalPkg.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_A_CAR"));
            rentalPkg2.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_A_CAR"));
        }
        manageTitleForVerticalCaRList();
        rentalPkgDesc.setVisibility(View.VISIBLE);
        height = (int) Utils.getScreenPixelHeight(getActContext());
        width = (int) Utils.getScreenPixelWidth(getActContext());
        height = height - Utils.dpToPx(getActContext(), 300);

        ride_now_btn = ((MaterialRippleLayout) view.findViewById(R.id.ride_now_btn)).getChildView();
        ride_now_btn.setAllCaps(false);
        scheduleArrowImage = view.findViewById(R.id.scheduleArrowImage);

        confirm_seats_btn = ((MaterialRippleLayout) view.findViewById(R.id.confirm_seats_btn)).getChildView();
        ride_now_btn.setId(Utils.generateViewId());
        confirm_seats_btn.setId(Utils.generateViewId());
        confirm_seats_btn.setAllCaps(true);

        mProgressBar = view.findViewById(R.id.mProgressBar);
        mProgressBar.getIndeterminateDrawable().setColorFilter(getActContext().getResources().getColor(R.color.appThemeColor_1), android.graphics.PorterDuff.Mode.SRC_IN);
        findRoute("--");
        RideDeliveryType = getArguments().getString("RideDeliveryType");

        carTypeRecyclerView = view.findViewById(R.id.carTypeRecyclerView);
        if (mainAct.isVerticalCabscroll) {
            carTypeRecyclerView.setLayoutManager(new LinearLayoutManager(mainAct, LinearLayoutManager.VERTICAL, false));
        }
        loaderView = view.findViewById(R.id.loaderView);
        payTypeTxt = view.findViewById(R.id.payTypeTxt);
        payTypeHTxt = view.findViewById(R.id.payTypeHTxt);


        imageLaterarea = view.findViewById(R.id.imageLaterarea);
        btnArea = view.findViewById(R.id.btnArea);
        noServiceTxt = view.findViewById(R.id.noServiceTxt);
        addToClickHandler(img_ridelater);

        if (generalFunc.getJsonValueStr("ENABLE_CORPORATE_PROFILE", mainAct.obj_userProfile).equalsIgnoreCase("Yes") && mainAct.getCurrentCabGeneralType().equalsIgnoreCase("Ride")) {
            organizationArea.setVisibility(View.VISIBLE);
            showDropDownArea.setVisibility(View.GONE);
            organizationTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PERSONAL"));
            LinearLayout.LayoutParams organizationLayoutParams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            organizationLayoutParams.setMargins(0, 0, 0, -Utils.dpToPx(getActContext(), 5));
            organizationArea.setLayoutParams(organizationLayoutParams);
        } else {
            showDropDownArea.setVisibility(View.GONE);
        }
        paymentArea = view.findViewById(R.id.paymentArea);
        addToClickHandler(paymentArea);
        addToClickHandler(arrowImg);

        payImgView = view.findViewById(R.id.payImgView);
        errorImage = view.findViewById(R.id.errorImage);

        getUserProfileJson(mainAct.obj_userProfile.toString());

        currency_sign = generalFunc.getJsonValue("CurrencySymbol", userProfileJson);
        app_type = generalFunc.getJsonValue("APP_TYPE", userProfileJson);

        showBookingLaterArea();

        if (mainAct.isDeliver(mainAct.getCurrentCabGeneralType())) {
            img_ridelater.setImageResource(R.drawable.ic_delivery_later);
        }

        if (mainAct.eShowOnlyMoto != null && !mainAct.eShowOnlyMoto.equalsIgnoreCase("") && mainAct.eShowOnlyMoto.equalsIgnoreCase("yes")) {
            img_ridelater.setImageResource(R.drawable.ic_timetable);
        }


        if (mainAct.eFly) {
            img_ridelater.setImageResource(R.drawable.ic_calendar_later);
        }

        if (app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX)) {
            app_type = "Ride";
        }

        if (app_type.equals(Utils.CabGeneralType_UberX)) {
            view.setVisibility(View.GONE);
            return view;
        }

        isKilled = false;

        if (mainAct.isMultiDelivery()) {


            cashCardArea.setVisibility(View.GONE);


        } else {
            if (!mainAct.isVerticalCabscroll) {
                detailArea.setBackgroundColor(Color.parseColor("#f1f1f1"));
            }
            manageProfilePayment();

        }


        setLabels(true);
        addToClickHandler(ride_now_btn);
        addToClickHandler(confirm_seats_btn);
        configRideLaterBtnArea(false);


        seatsSelectionAdapter = new PoolSeatsSelectionAdapter(getActContext(), poolSeatsList, generalFunc);
        seatsSelectionAdapter.setSelectedSeat(seatsSelectpos);
        poolSeatsRecyclerView.setAdapter(seatsSelectionAdapter);
        seatsSelectionAdapter.notifyDataSetChanged();
        seatsSelectionAdapter.setOnItemClickList(this);
        initCarDetailsView(view);

        carTypeRecyclerView.addOnItemTouchListener(new RecyclerView.OnItemTouchListener() {
            @Override
            public boolean onInterceptTouchEvent(@NonNull RecyclerView rv, @NonNull MotionEvent motionEvent) {
                if (tempState == BottomSheetBehavior.STATE_COLLAPSED) {
                    /** Working Fine but item does not clickable while collapsed **/
//                    return motionEvent.getAction() == MotionEvent.ACTION_DOWN;

                    /** Working Fine but little bit glitches while expands **/
                    return rv.getScrollState() == RecyclerView.SCROLL_STATE_DRAGGING;
                }
                if (tempState == BottomSheetBehavior.STATE_EXPANDED) {
                    mainAct.enableDisableBottomSheetDrag(!rv.canScrollVertically(-1), false);
                }
                return false;
            }

            @Override
            public void onTouchEvent(@NonNull RecyclerView rv, @NonNull MotionEvent motionEvent) {

            }

            @Override
            public void onRequestDisallowInterceptTouchEvent(boolean disallowIntercept) {
            }
        });
        carTypeRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrollStateChanged(@NonNull RecyclerView recyclerView, int newState) {
                super.onScrollStateChanged(recyclerView, newState);

                if (tempState == BottomSheetBehavior.STATE_EXPANDED) {
                    if (!recyclerView.canScrollVertically(-1) && newState == RecyclerView.SCROLL_STATE_IDLE) {
                        mainAct.cabBottomSheetBehavior.setDraggable(true);
                    } else {
                        mainAct.cabBottomSheetBehavior.setDraggable(false);
                    }
                }
            }

        });

        if (mainAct.isVerticalCabscroll) {
            tollbarArea.setAlpha(0);
            mainAct.cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED);
            if (mainAct.isInterCity || mainAct.isFromChooseTrip) {
                mainAct.setPanelHeight((int) (Utils.getScreenPixelHeight(getActContext()) / 2));
            }
            mainAct.enableDisableBottomSheetDrag(false, false);
            cabBottomSheetLayout.setClickable(true);
            cabBottomSheetLayout.setFocusable(true);
            cabBottomSheetLayout.setFocusableInTouchMode(true);
            BottomSheetBehavior.BottomSheetCallback bottomSheetCallback = new BottomSheetBehavior.BottomSheetCallback() {
                @Override
                public void onStateChanged(@NonNull View view, int newState) {

                    Logger.d("onPanelStateChanged", "::" + newState);

                    switch (newState) {

                        case BottomSheetBehavior.STATE_EXPANDED:
                            tempState = BottomSheetBehavior.STATE_EXPANDED;
                            setPaddingToRv(carTypeRecyclerView, expandRvPadding);
                            hideRentalArea();
                            mProgressBar.setVisibility(View.GONE);
                            if (mainAct.isMultiDelivery()) {
                                mainAct.titleTxt.setText(generalFunc.retrieveLangLBl("Choose Delivery", "LBL_CHOOSE_DELIVERY_TXT"));
                            }
                            if (!mainAct.isMultiDelivery()) {
                                requestPayArea.animate().translationY(sendRequestArea.getMeasuredHeight() + getActContext().getResources().getDimensionPixelSize(R.dimen._11sdp));
                            }

                            rentalPkgDesc.setVisibility(View.GONE);
                            verticalscrollIndicator.setVisibility(View.GONE);

                            testBgView.setAlpha(1);
                            rentalarea.setAlpha(0);
                            setRentalAreaHeight(rentalarea, 1, rentalAreaHeight);
                            toolBarAnimation(1);
                            break;
                        case BottomSheetBehavior.STATE_DRAGGING:
                            mProgressBar.setVisibility(View.GONE);
                            rentalBackImage.setVisibility(View.GONE);

                            if (!mainAct.isMultiDelivery()) {
                                requestPayArea.animate().translationY(sendRequestArea.getMeasuredHeight() + getActContext().getResources().getDimensionPixelSize(R.dimen._11sdp));
                            }
                            rentalarea.animate().translationY(rentalarea.getMeasuredHeight() + getActContext().getResources().getDimensionPixelSize(R.dimen._20sdp));
                            break;
                        case BottomSheetBehavior.STATE_COLLAPSED:
                            tempState = BottomSheetBehavior.STATE_COLLAPSED;
                            mProgressBar.setVisibility(mainAct.isMultiDelivery() ? View.GONE : View.INVISIBLE);
                            verticalscrollIndicator.setVisibility(View.VISIBLE);
                            if (!mainAct.isMultiDelivery()) {
                                mainAct.mainHeaderFrag.backBtn.setVisibility(View.VISIBLE);
                            }
                            requestPayArea.animate().translationY(0);
                            rentalPkgDesc.setVisibility(View.VISIBLE);
                            rentalarea.animate().translationY(0);
                            showRentalArea();
                            isHeightGiven = false;
                            if (isRental) {
                                rentalBackImage.setVisibility(View.VISIBLE);
                            }
                            setPaddingToRv(carTypeRecyclerView, getResources().getDimensionPixelSize(R.dimen._300sdp));
                            scrollPosition(carTypeRecyclerView, selpos >= 3 ? selpos - 2 : 0);
                            Logger.d("setPadding", "1");
                            mainAct.enableDisableBottomSheetDrag(noServiceTxt.getVisibility() != View.VISIBLE, false);
                            if (mainAct.isMultiDelivery()) {
                                mainAct.setMultiTitleTexManage();
                            }
                            if (mainAct.userLocBtnImgView != null) {
                                mainAct.userLocBtnImgView.performClick();
                            }
                            testBgView.setAlpha(0);
                            rentalarea.setAlpha(1);
                            setRentalAreaHeight(rentalarea, 0, rentalAreaHeight);
                            toolBarAnimation(0);
                            break;

                    }
                }

                @Override
                public void onSlide(@NonNull View view, float v) {
                    toolBarAnimation(v);
                    rentalarea.setAlpha(1 - v);
                    testBgView.setAlpha(v);
                    setRentalAreaHeight(rentalarea, v, rentalAreaHeight);
                    if (v < 0.95) {
                        Logger.d("onPanelSlide11", ":11111:" + v);
                        verticalscrollIndicator.setVisibility(View.VISIBLE);
                        rentalPkgDesc.setVisibility(View.VISIBLE);

                    }
                }
            };
            mainAct.cabBottomSheetBehavior.addBottomSheetCallback(bottomSheetCallback);
        }
        mainAct.setAdsView(false);

        //TODO DO not delete BelowCode (Code For Full Screen Map)
//        getMap().setOnCameraIdleListener(() -> {
//            if (mainAct.isCameraMoveHitByUser) {
//                showHideViewsOnMapClick(false);
//                mainAct.isCameraMoveHitByUser = false;
//            }
//        });
        //TODO DO not delete above code (Code For Full Screen Map)
        return view;
    }

    private void initCarDetailsView(View view) {
        mordetailsTxt = view.findViewById(R.id.mordetailsTxt);
        pkgMsgTxt = view.findViewById(R.id.pkgMsgTxt);
        carTypeTitle = view.findViewById(R.id.carTypeTitle);
        fareVTxt = view.findViewById(R.id.fareVTxt);
        personsizeTxt = view.findViewById(R.id.personsizeTxt);
        morwArrow = view.findViewById(R.id.morwArrow);
        imagecar = view.findViewById(R.id.imagecar);
        DeliveryHelper = view.findViewById(R.id.DeliveryHelper);
    }

    private void toolBarAnimation(float v) {
        if (mainAct.isMultiDelivery()) {
            mainAct.rduTollbar.findViewById(R.id.backImgView).setRotation(generalFunc.isRTLmode() ? 180 + (90 * v) : -90 * v);
            tollbarArea.setVisibility(View.GONE);

        } else {
            tollbarArea.setVisibility(View.VISIBLE);
            tollbarArea.setAlpha(v);
            tollbarArea.findViewById(R.id.backImgView).setRotation(generalFunc.isRTLmode() ? 180 + (90 * v) : -90 * v);
        }
    }

    private void setRentalAreaHeight(RelativeLayout rentalarea, float v, int rentalAreaHeight) {
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        rentalarea.setLayoutParams(params);
        params.height = (int) (rentalAreaHeight - (rentalAreaHeight * v));
        rentalarea.setLayoutParams(params);
    }

    private void setPaddingToRv(RecyclerView carTypeRecyclerView, int rvPaddignHeight) {
        if (cabTypeList.size() > 3) {
            carTypeRecyclerView.setPadding(0, 0, 0, rvPaddignHeight);
            carTypeRecyclerView.setClipToPadding(false);
        }
    }

    private void scrollPosition(RecyclerView carTypeRecyclerView, int pos) {
        RecyclerView.SmoothScroller smoothScroller = new LinearSmoothScroller(getActContext()) {
            @Override
            protected int getVerticalSnapPreference() {
                return LinearSmoothScroller.SNAP_TO_START;
            }
        };

        smoothScroller.setTargetPosition(pos);
        carTypeRecyclerView.getLayoutManager().startSmoothScroll(smoothScroller);
    }

    private void showBookingLaterArea() {
        if (generalFunc.getJsonValue("RIDE_LATER_BOOKING_ENABLED", userProfileJson).equalsIgnoreCase("Yes") && !mainAct.isMultiDelivery()) {

            if (mainAct.isPoolCabTypeIdSelected) {
                showRideLaterBtn(false);
            } else if (app_type.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX) && !mainAct.iscubejekRental) {
                showRideLaterBtn(true);
            } else showRideLaterBtn(!mainAct.iscubejekRental && !mainAct.isRental);

        } else {
            showRideLaterBtn(false);

        }

    }

    private void showRideLaterBtn(boolean show) {
        imageLaterarea.setVisibility(View.GONE);
    }

    @Override
    public void onResume() {
        super.onResume();
        manageProfilePayment();
    }

    public void showLoader() {
        try {
            mProgressBar.setIndeterminate(true);
            mProgressBar.setVisibility(View.VISIBLE);
            closeNoServiceText();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    public void showNoServiceText() {
        noServiceTxt.setVisibility(View.VISIBLE);
        rentalMainArea.setVisibility(View.GONE);
        carTypeRecyclerView.setVisibility(View.GONE);
        updateMarginToNoServiceTextView();
        if (tempState == BottomSheetBehavior.STATE_EXPANDED) {
            mainAct.cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED);
        }
        if (adapter != null) {
            adapter.isFirstTime = true;
            adapter.cabCounter = 0;
            adapter.measuredHeight = 0;
        }
        mainAct.enableDisableBottomSheetDrag(false, false);
    }

    private void updateMarginToNoServiceTextView() {
        RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) noServiceTxt.getLayoutParams();
        params.setMargins(0, noServiceTextTopMargin, 0, 0);
        noServiceTxt.setLayoutParams(params);
    }

    public void closeNoServiceText() {
        noServiceTxt.setVisibility(View.GONE);

        if (tollbarArea.getAlpha() == 0) {
            rentalMainArea.setVisibility(View.VISIBLE);
        }

        carTypeRecyclerView.setVisibility(View.VISIBLE);
    }

    public void closeLoader() {
        try {
            manageProfilePayment();
            mProgressBar.setVisibility(View.GONE);
            if (mainAct.cabTypesArrList.size() == 0) {
                showNoServiceText();
            } else {
                closeNoServiceText();
            }
        } catch (Exception e) {

        }
    }

    public void getUserProfileJson(String userProfileJsonStr) {
        userProfileJson = userProfileJsonStr;
        APP_PAYMENT_MODE = generalFunc.getJsonValue("APP_PAYMENT_MODE", userProfileJson);
        APP_PAYMENT_METHOD = generalFunc.getJsonValue("APP_PAYMENT_METHOD", userProfileJson);
        SYSTEM_PAYMENT_FLOW = generalFunc.getJsonValue("SYSTEM_PAYMENT_FLOW", userProfileJson);
    }

    public void manageProfilePayment() {
        if (view == null || payTypeTxt == null || payImgView == null || errorImage == null) {
            return;
        }

        payTypeTxt.setText(generalFunc.getJsonValue("PAYMENT_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString());
        payTypeHTxt.setText(generalFunc.getJsonValue("PAYMENT_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString());
        organizationTxt.setText(generalFunc.getJsonValue("PROFILE_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString());
        if (!mainAct.getCurrentCabGeneralType().equalsIgnoreCase(Utils.CabGeneralType_Ride) && mainAct.mainHeaderFrag.isSchedule) {
            enableRideNowBtn(true);
        }
        img_ridelater.setEnabled(true);
        payImgView.setVisibility(View.VISIBLE);
        errorImage.setVisibility(View.GONE);
        organizationTxt.setVisibility(View.VISIBLE);

        if (Utils.checkText(generalFunc.getJsonValue("PROFILE_DISPLAY_LBL", getProfilePaymentModel.getProfileInfo()).toString())) {
            organizationTxt.setVisibility(View.VISIBLE);
            payTypeTxt.setVisibility(View.GONE);
            payTypeHTxt.setVisibility(View.VISIBLE);
            payTypeTxt.setPadding(0, (int) getActContext().getResources().getDimension(R.dimen._5sdp), 0, 0);
        } else {
            payTypeTxt.setPadding(0, (int) getActContext().getResources().getDimension(R.dimen._10sdp), 0, 0);
            organizationTxt.setVisibility(View.GONE);
            payTypeHTxt.setVisibility(View.GONE);
            payTypeTxt.setVisibility(View.VISIBLE);
        }
        if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("")) {
            errorImage.setVisibility(View.VISIBLE);
            payImgView.setVisibility(View.GONE);
            if (!mainAct.isMultiDelivery()) {
                enableRideNowBtn(false);
                img_ridelater.setEnabled(false);
            }
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CASH")) {
            payImgView.setImageResource(R.drawable.ic_cash_payment_pro);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("CARD")) {
            payImgView.setImageResource(R.mipmap.ic_card_new);
        } else if (generalFunc.getJsonValue("PaymentMode", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("BUSINESS")) {
            payImgView.setImageResource(R.drawable.ic_business_pay);
        } else {
            payImgView.setImageResource(R.drawable.ic_menu_wallet);
        }

        new LoadImage.builder(LoadImage.bind(generalFunc.getJsonValue("PaymentModeImg", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("") ? "123" : generalFunc.getJsonValue("PaymentModeImg", getProfilePaymentModel.getProfileInfo()).toString()), payImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        new LoadImage.builder(LoadImage.bind(generalFunc.getJsonValue("PaymentModeImg", getProfilePaymentModel.getProfileInfo()).toString().equalsIgnoreCase("") ? "123" : generalFunc.getJsonValue("PaymentModeImg", getProfilePaymentModel.getProfileInfo()).toString()), errorImage).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

        if (!mainAct.isMultiDelivery()) {

            findRoute("--");
        }


    }

    public void handleForSchedule() {
        if (mainAct.cabRquestType.equals(Utils.CabReqType_Later)) {
            enableRideNowBtn(true);
            if (mainAct.isInterCity) {
                ride_now_btn.setText(Utils.convertDateToFormat(DateTimeUtils.getDetailDateFormatWise(DateTimeUtils.DateFormat, generalFunc), mainAct.scheduledate));
            } else {
                ride_now_btn.setText(Utils.convertDateToFormat(DateTimeUtils.DateTimeFormat, mainAct.scheduledate));

            }
            scheduleArrowImage.setVisibility(View.GONE);
            if (generalFunc.isRTLmode()) {
                scheduleArrowImage.setRotation(180);
            }
        } else {
            scheduleArrowImage.setVisibility(View.GONE);
        }
    }

    public void setLabels(boolean isCallGenerateType) {

        if (mainAct == null || view == null) {
            return;
        }

        if (generalFunc == null) {
            generalFunc = mainAct.generalFunc;
        }

        if (generalFunc == null) {
            return;
        }

        required_str = generalFunc.retrieveLangLBl("", "LBL_FEILD_REQUIRD");
        noServiceTxt.setText(mainAct.eFly ? generalFunc.retrieveLangLBl("No rides for these locations.", "LBL_FLY_NO_VEHICLES") : generalFunc.retrieveLangLBl("service not available in this location", "LBL_NO_SERVICE_AVAILABLE_TXT"));

        if (mainAct.isMultiDelivery()/*mainAct.isMultiDeliveryTrip*/) {
            enableRideNowBtn(true);
            ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        } else {
            if (mainAct.currentLoadedDriverList == null || mainAct.currentLoadedDriverList.size() < 1) {
                ride_now_btn.setText(generalFunc.retrieveLangLBl("No Car available.", mainAct.loadAvailCabs.loadAvailableCalled ? "LBL_FINDING_DRIVERS_NEAR_BY" : "LBL_NO_CARS"));
                enableRideNowBtn(false);
                if (isCallGenerateType) {
                    generateCarType();

                }
                handleForSchedule();
                return;

            } else {
                enableRideNowBtn(true);
                ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
            }
        }


        if (app_type.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
            currentCabGeneralType = Utils.CabGeneralType_UberX;
        } else {
            String type = mainAct.isDeliver(app_type) || mainAct.isDeliver(RideDeliveryType) ? "Deliver" : Utils.CabGeneralType_Ride;
            if (type.equals("Deliver")) {
                enableRideNowBtn(true);
                if (mainAct.getCabReqType().equals(Utils.CabReqType_Now)) {
                    ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                } else if (mainAct.getCabReqType().equals(Utils.CabReqType_Later)) {
                    ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                }
            } else {
                if (mainAct.mainHeaderFrag != null && !mainAct.mainHeaderFrag.isSchedule) {
                    ride_now_btn.setText(generalFunc.retrieveLangLBl("Request Now", "LBL_REQUEST_NOW"));
                    enableRideNowBtn(true);
                }
            }


            if (generalFunc.getSelectedCarTypeData(mainAct.getSelectedCabTypeId(), mainAct.cabTypesArrList, "ePoolStatus").equalsIgnoreCase("Yes")) {
                ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_SEATS"));

            }

        }
        confirm_seats_btn.setText(generalFunc.retrieveLangLBl("Confirm Seats", "LBL_CONFIRM_SEATS"));
        confirm_seats_btn.setAllCaps(false);
        if (isCallGenerateType) {
            generateCarType();
        }
        handleForSchedule();

    }

    public void releaseResources() {
        isKilled = true;
        PolyLineAnimator.getInstance().stopRouteAnim();
        if (route_polyLine != null) {
            route_polyLine.remove();
            route_polyLine = null;
        }
    }

    public void changeCabGeneralType(String currentCabGeneralType) {
        this.currentCabGeneralType = currentCabGeneralType;
    }

    public String getCurrentCabGeneralType() {
        return this.currentCabGeneralType;
    }

    public void configRideLaterBtnArea(boolean isGone) {
        if (mainAct.isMultiDelivery()) {
            mainAct.setPanelHeight((int) Utils.getScreenPixelHeight(getActContext()) / 2);
            currentPanelDefaultStateHeight = (int) Utils.getScreenPixelHeight(getActContext()) / 2;
        } else {
            if (isGone || app_type.equalsIgnoreCase("Ride-Delivery")) {
                mainAct.setPanelHeight(237);
                if (!app_type.equalsIgnoreCase("Ride-Delivery")) {
                }
                return;
            }
            if (!generalFunc.getJsonValue("RIIDE_LATER", userProfileJson).equalsIgnoreCase("YES") && !app_type.equalsIgnoreCase("Ride-Delivery")) {
                mainAct.setPanelHeight(237);
            } else {


                mainAct.setPanelHeight((int) (Utils.getScreenPixelHeight(getActContext()) / 2));
                currentPanelDefaultStateHeight = (int) (Utils.getScreenPixelHeight(getActContext()) / 2);
            }
        }
        new Handler().postDelayed(new Runnable() {
            @Override
            public void run() {
                noServiceTextTopMargin = ((currentPanelDefaultStateHeight - requestPayArea.getMeasuredHeight() - view.findViewById(R.id.verticalscrollIndicatorLL).getMeasuredHeight()) / 2);
            }
        }, 50);

    }

    public void generateCarType() {
        if (getActContext() == null || view == null) {
            return;
        }

        if (cabTypeList == null) {
            cabTypeList = new ArrayList<>();
            rentalTypeList = new ArrayList<>();
            if (adapter == null) {
                adapter = new CabTypeAdapter(getActContext(), cabTypeList, generalFunc, mainAct.obj_userProfile);
                adapter.setSelectedVehicleTypeId(mainAct.getSelectedCabTypeId());
                adapter.isMultiDelivery(mainAct.isMultiDelivery());
                carTypeRecyclerView.setAdapter(adapter);
                adapter.setOnItemClickList(this);
            } else {
                adapter.notifyDataSetChanged();
            }
        } else {
            cabTypeList.clear();
            rentalTypeList.clear();
        }

        if (mainAct.isDeliver(currentCabGeneralType)) {
            this.currentCabGeneralType = "Deliver";
        }

        String APP_TYPE = generalFunc.getJsonValue("APP_TYPE", userProfileJson);
        for (int i = 0; i < mainAct.cabTypesArrList.size(); i++) {

            HashMap<String, String> map = new HashMap<>();
            String iVehicleTypeId = mainAct.cabTypesArrList.get(i).get("iVehicleTypeId");

            String vVehicleType = mainAct.cabTypesArrList.get(i).get("vVehicleType");
            String vRentalVehicleTypeName = mainAct.cabTypesArrList.get(i).get("vRentalVehicleTypeName");
            String fPricePerKM = mainAct.cabTypesArrList.get(i).get("fPricePerKM");
            String fPricePerMin = mainAct.cabTypesArrList.get(i).get("fPricePerMin");
            String iBaseFare = mainAct.cabTypesArrList.get(i).get("iBaseFare");
            String fCommision = mainAct.cabTypesArrList.get(i).get("fCommision");
            String iPersonSize = mainAct.cabTypesArrList.get(i).get("iPersonSize");
            String vLogo = mainAct.cabTypesArrList.get(i).get("vLogo");
            String vLogo1 = mainAct.cabTypesArrList.get(i).get("vLogo1");
            String eType = mainAct.cabTypesArrList.get(i).get("eType");
            String fPoolPercentage = mainAct.cabTypesArrList.get(i).get("fPoolPercentage");

            String eRental = mainAct.cabTypesArrList.get(i).get("eRental");
            String ePoolStatus = mainAct.cabTypesArrList.get(i).get("ePoolStatus");
            String eDeliveryHelper = mainAct.cabTypesArrList.get(i).get("eDeliveryHelper");
            String tDeliveryHelperNoteUser = mainAct.cabTypesArrList.get(i).get("tDeliveryHelperNoteUser");

            //addtional condition is manage for hide pool when schedule selected
            if (!eType.equalsIgnoreCase(currentCabGeneralType) || (mainAct.getCabReqType().equals(Utils.CabReqType_Later) && ePoolStatus.equalsIgnoreCase("Yes"))) {
                continue;
            }

            map.put("iVehicleTypeId", iVehicleTypeId);
            map.put("vVehicleType", vVehicleType);
            map.put("vRentalVehicleTypeName", vRentalVehicleTypeName);
            map.put("tInfoText", mainAct.cabTypesArrList.get(i).get("tInfoText"));
            map.put("fPricePerKM", fPricePerKM);
            map.put("fPricePerMin", fPricePerMin);
            map.put("iBaseFare", iBaseFare);
            map.put("fCommision", fCommision);
            map.put("iPersonSize", iPersonSize);
            map.put("vLogo", vLogo);
            map.put("vLogo1", vLogo1);
            map.put("fPoolPercentage", fPoolPercentage);
            //#UberPool Change
            map.put("ePoolStatus", ePoolStatus);
            map.put("eDeliveryHelper", eDeliveryHelper);
            map.put("tDeliveryHelperNoteUser", tDeliveryHelperNoteUser);

            if (((APP_TYPE.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery)) || (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX)) || (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride))) && (mainAct.iscubejekRental || mainAct.isRental)) {
                map.put("eRental", eRental);
            } else {
                map.put("eRental", "No");
            }

            if (mainAct.isTaxiBid) {
                map.put("isTaxiBid", "Yes");
            }

            if (i == 0) {
                adapter.setSelectedVehicleTypeId(iVehicleTypeId);
            }

            cabTypeList.add(map);

            if (eRental != null && eRental.equalsIgnoreCase("Yes")) {
                HashMap<String, String> rentalmap = (HashMap<String, String>) map.clone();
                rentalmap.put("eRental", "Yes");
                rentalTypeList.add(rentalmap);
            }
        }


        manageRentalArea();

        mainAct.setCabTypeList(cabTypeList);
        adapter.notifyDataSetChanged();

        if (cabTypeList.size() > 0) {
            if (tempState == BottomSheetBehavior.STATE_COLLAPSED) {
                isFirstTime = true;
                onItemClick(0);
            }
        }
    }

    public void manageRentalArea() {
        if (rentalarea != null && rentalBackImage != null && mainAct.isMultiStopOverEnabled()) {
            // Show or Hide Rental - if MultiStop Over Added
            int rentalArea = rentalarea.getVisibility();
            int rentalBackImgArea = rentalBackImage.getVisibility();

            if (mainAct.stopOverPointsList.size() > 2) {
                hideRentalArea();
            } else if ((rentalArea == View.GONE && rentalBackImgArea == View.GONE)) {
                showRentalArea();
            }
        } else {
            showRentalArea();
        }

    }

    public void showRentalArea() {
        if (mainAct != null && mainAct.mainHeaderFrag != null && mainAct.mainHeaderFrag.isSchedule) {
            return;
        }
        if (mainAct != null && !mainAct.iscubejekRental) {
            if (rentalTypeList != null && rentalTypeList.size() > 0) {
                String APP_TYPE = generalFunc.getJsonValue("APP_TYPE", userProfileJson);

                if (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride) || APP_TYPE.equalsIgnoreCase("Ride-Delivery") || (RideDeliveryType.equalsIgnoreCase(Utils.CabGeneralType_Ride) && !mainAct.iscubejekRental && !mainAct.isInterCity)) {
                    rentalPkg.setVisibility(View.VISIBLE);
                    rentalarea.setVisibility(View.VISIBLE);
                }

            }
        }
    }

    public void closeLoadernTxt() {
        //  loaderView.setVisibility(View.GONE);
        if (mProgressBar != null) {
            mProgressBar.setVisibility(View.GONE);
            closeNoServiceText();
        }

    }

    public Context getActContext() {
        return mainAct != null ? mainAct.getActContext() : getActivity();
    }

    @Override
    public void onItemClick(int position) {

        String iVehicleTypeId = cabTypeList.get(position).get("iVehicleTypeId");
        String ePoolStatus = cabTypeList.get(position).get("ePoolStatus");

        if (ePoolStatus.equalsIgnoreCase("Yes") && mainAct.stopOverPointsList.size() > 2) {
            generalFunc.showMessage(carTypeRecyclerView, generalFunc.retrieveLangLBl("", "LBL_REMOVE_MULTI_STOP_OVER_TXT"));
            return;
        }


        selpos = position;

        mainAct.isPoolCabTypeIdSelected = ePoolStatus.equalsIgnoreCase("Yes");

        showBookingLaterArea();

        if (tempState == BottomSheetBehavior.STATE_EXPANDED) {
            mainAct.selectedCabTypeId = iVehicleTypeId;
            adapter.setSelectedVehicleTypeId(iVehicleTypeId);
            adapter.notifyDataSetChanged();
            mainAct.changeCabType(iVehicleTypeId);

            mainAct.isFixFare = cabTypeList.get(position).get("eFlatTrip") != null && (!cabTypeList.get(position).get("eFlatTrip").equalsIgnoreCase("")) && cabTypeList.get(position).get("eFlatTrip").equalsIgnoreCase("Yes");
            //#UberPool Change
            if (ePoolStatus.equalsIgnoreCase("Yes")) {
                enableRideNowBtn(true);
                mainAct.loadAvailCabs.checkAvailableCabs();
                ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_CONFIRM_SEATS"));
            } else {
                String type = mainAct.isDeliver(app_type) || mainAct.isDeliver(RideDeliveryType) ? "Deliver" : Utils.CabGeneralType_Ride;
                if (type.equals("Deliver")) {
                    enableRideNowBtn(true);
                    if (mainAct.getCabReqType().equals(Utils.CabReqType_Now)) {
                        ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                    } else if (mainAct.getCabReqType().equals(Utils.CabReqType_Later)) {
                        ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                    }
                } else {
                    if (mainAct.mainHeaderFrag != null && !mainAct.mainHeaderFrag.isSchedule) {
                        enableRideNowBtn(true);
                        ride_now_btn.setText(generalFunc.retrieveLangLBl("Request Now", "LBL_REQUEST_NOW"));
                    }
                }

            }
            openCarDetailsDialog(position);

        } else {
            if (!iVehicleTypeId.equals(mainAct.getSelectedCabTypeId())) {
                mainAct.selectedCabTypeId = iVehicleTypeId;
                adapter.setSelectedVehicleTypeId(iVehicleTypeId);
                adapter.notifyDataSetChanged();
                mainAct.changeCabType(iVehicleTypeId);

                mainAct.isFixFare = cabTypeList.get(position).get("eFlatTrip") != null && (!cabTypeList.get(position).get("eFlatTrip").equalsIgnoreCase("")) && cabTypeList.get(position).get("eFlatTrip").equalsIgnoreCase("Yes");
                //#UberPool Change
                if (ePoolStatus.equalsIgnoreCase("Yes")) {
                    enableRideNowBtn(true);
                    mainAct.loadAvailCabs.checkAvailableCabs();
                    ride_now_btn.setText(generalFunc.retrieveLangLBl("", mainAct.loadAvailCabs.loadAvailableCalled ? "LBL_FINDING_DRIVERS_NEAR_BY" : "LBL_CONFIRM_SEATS"));
                } else {
                    String type = mainAct.isDeliver(app_type) || mainAct.isDeliver(RideDeliveryType) ? "Deliver" : Utils.CabGeneralType_Ride;
                    if (type.equals("Deliver")) {
                        enableRideNowBtn(true);
                        if (mainAct.getCabReqType().equals(Utils.CabReqType_Now)) {
                            ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                        } else if (mainAct.getCabReqType().equals(Utils.CabReqType_Later)) {
                            ride_now_btn.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
                        }
                    } else {
                        if (mainAct.mainHeaderFrag != null && !mainAct.mainHeaderFrag.isSchedule) {
                            if (!(mainAct.getCurrentCabGeneralType().equalsIgnoreCase(Utils.CabGeneralType_Ride) && mainAct.loadAvailCabs.loadAvailableCalled)) {
                                enableRideNowBtn(true);
                                ride_now_btn.setText(generalFunc.retrieveLangLBl("Request Now", "LBL_REQUEST_NOW"));
                            }
                        }
                    }

                }
                if (mainAct.isVerticalCabscroll) {
                    if (mainAct.cabBottomSheetBehavior.getState() == BottomSheetBehavior.STATE_COLLAPSED) {
                        scrollPosition(carTypeRecyclerView, selpos >= 3 ? (selpos - 2) : 0);
                    }
                }

            } else {

                openFareDetailsDilaog(position);
            }
        }

        poolFareTxt.setText(cabTypeList.get(position).get("total_fare"));

        if (mainAct.isPoolCabTypeIdSelected) {
            if (Utils.checkText(routeDrawResponse)) {
                routeDraw();
            } else {
                findRoute("--");
            }
        } else {
            if (mainAct.eFly) {
                getMap().getUiSettings().setZoomGesturesEnabled(false);
                routeDrawResponse = "--";
            }

            if (routeDrawResponse != null && !routeDrawResponse.equalsIgnoreCase("")) {
                PolylineOptions lineOptions = null;

                if ((mainAct.isPoolCabTypeIdSelected || mainAct.eFly) && sourceLocation != null && destLocation != null) {
                    lineOptions = createCurveRoute(new LatLng(sourceLocation.latitude, sourceLocation.longitude), new LatLng(destLocation.latitude, destLocation.longitude));

                } else if (!mainAct.isPoolCabTypeIdSelected && Utils.checkText(routeDrawResponse)) {


                    if (mainAct.stopOverPointsList.size() > 2) {
                        lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(R.color.black), getActContext(), mainAct.stopOverPointsList, wayPointslist, destPointlist, finalPointlist, getMap(), builder, isGoogle);

                    } else {
                        lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(android.R.color.black), isGoogle);
                    }

                }


                if (lineOptions != null) {
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                        route_polyLine = null;

                    }
                    route_polyLine = getMap().addPolyline(lineOptions);
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                    }
                }


                if (isSkip) {
                    PolyLineAnimator.getInstance().stopRouteAnim();
                    if (route_polyLine != null) {
                        route_polyLine.remove();
                        route_polyLine = null;
                    }
                    return;
                }
                if (route_polyLine != null && route_polyLine.getPoints().size() > 1) {
                    PolyLineAnimator.getInstance().animateRoute(getMap(), route_polyLine.getPoints(), getActContext());
                }
            }
        }

    }

    @Override
    public void onDeliveryHelperClick(int position) {
        if (tempState == BottomSheetBehavior.STATE_EXPANDED) {
            //If BottomSheet Expands make helper icon Clickable False
            return;
        }
        new ToolTipDialog(getActContext(), generalFunc, generalFunc.retrieveLangLBl("", "LBL_DEL_HELPER"), cabTypeList.get(position).get("tDeliveryHelperNoteUser") + "");
    }

    private void openCarDetailsDialog(int pos) {

        String vehicleIconPath = CommonUtilities.SERVER_URL + "webimages/icons/VehicleType/";
        String vehicleDefaultIconPath = CommonUtilities.SERVER_URL + "webimages/icons/DefaultImg/";

        if (generalFunc.isRTLmode()) {
            design_linear_layout_car_details.setLayoutDirection(View.LAYOUT_DIRECTION_RTL);
        } else {
            design_linear_layout_car_details.setLayoutDirection(View.LAYOUT_DIRECTION_LTR);
        }

        mordetailsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MORE_DETAILS"));
        pkgMsgTxt.setText(cabTypeList.get(pos).get("tInfoText"));

        if ((cabTypeList.get(pos).get("isTaxiBid") != null && cabTypeList.get(pos).get("isTaxiBid").equalsIgnoreCase("Yes")) || mainAct.isMultiDelivery()) {
            mordetailsTxt.setVisibility(View.GONE);
            morwArrow.setVisibility(View.GONE);
        }

        if (!cabTypeList.get(pos).get("eRental").equals("") && cabTypeList.get(pos).get("eRental").equals("Yes") || (mainAct.isInterCity && mainAct.isInterCityRoundTrip)) {
            carTypeTitle.setText(cabTypeList.get(pos).get("vRentalVehicleTypeName"));
        } else {
            carTypeTitle.setText(cabTypeList.get(pos).get("vVehicleType"));
        }
        if (cabTypeList.get(pos).get("total_fare") != null && !cabTypeList.get(pos).get("total_fare").equalsIgnoreCase("")) {
            fareVTxt.setText(generalFunc.convertNumberWithRTL(cabTypeList.get(pos).get("total_fare")));
        } else {
            fareVTxt.setText("--");
        }
        if (mainAct.getCurrentCabGeneralType().equals(Utils.CabGeneralType_Ride)) {
            personsizeTxt.setText(generalFunc.convertNumberWithRTL(cabTypeList.get(pos).get("iPersonSize")));
            personsizeTxt.setVisibility(View.VISIBLE);

        } else {
            personsizeTxt.setVisibility(View.GONE);
        }

        String imgName = cabTypeList.get(pos).get("vLogo1");
        if (!Utils.checkText(imgName)) {
            imagecar.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_vehicle_placeholder));
        } else {
            imgName = vehicleIconPath + cabTypeList.get(pos).get("iVehicleTypeId") + "/android/" + "xxxhdpi_" + cabTypeList.get(pos).get("vLogo1");
            new LoadImage.builder(LoadImage.bind(imgName), imagecar).setErrorImagePath(R.drawable.ic_vehicle_placeholder).setPlaceholderImagePath(R.drawable.ic_vehicle_placeholder).build();
        }


        morwArrow.setOnClickListener(v -> mordetailsTxt.performClick());

        mordetailsTxt.setOnClickListener((View.OnClickListener) v -> {
            if ((cabTypeList.get(pos).get("eRental") != null && cabTypeList.get(pos).get("eRental").equalsIgnoreCase("Yes") || (mainAct.isInterCity && mainAct.isInterCityRoundTrip))) {
                openFareDetailsDilaog(pos);
                return;
            }
            Bundle bn = new Bundle();
            bn.putString("SelectedCar", cabTypeList.get(pos).get("iVehicleTypeId"));
            bn.putString("iUserId", generalFunc.getMemberId());
            bn.putString("distance", distance);
            bn.putString("time", time);
            if (cabTypeList.get(pos).get("eRental").equalsIgnoreCase("Yes")) {
                bn.putString("vVehicleType", cabTypeList.get(pos).get("vRentalVehicleTypeName"));
            } else {
                bn.putString("vVehicleType", cabTypeList.get(pos).get("vVehicleType"));
            }
            bn.putBoolean("isSkip", isSkip);
            if (mainAct.getPickUpLocation() != null) {
                bn.putString("picupLat", mainAct.getPickUpLocation().getLatitude() + "");
                bn.putString("pickUpLong", mainAct.getPickUpLocation().getLongitude() + "");
            }
            if (mainAct.destLocation != null) {
                bn.putString("destLat", mainAct.destLocLatitude + "");
                bn.putString("destLong", mainAct.destLocLongitude + "");
            }
            bn.putBoolean("isFixFare", mainAct.isFixFare);

            if (mainAct.eFly) {
                bn.putString("iFromStationId", mainAct.iFromStationId);
                bn.putString("iToStationId", mainAct.iToStationId);
                bn.putString("eFly", "Yes");
            }
            new ActUtils(getActContext()).startActWithData(FareBreakDownActivity.class, bn);
        });
        if (mainAct.isMultiDelivery()) {
            personsizeTxt.setVisibility(View.GONE);
            fareVTxt.setVisibility(View.GONE);

            DeliveryHelper.setVisibility(View.VISIBLE);
            DeliveryHelper.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    onDeliveryHelperClick(pos);
                }
            });

        }

        animateCarView(View.VISIBLE);

    }

    @Override
    public void onHeightMeasured(int measuredHeight, int cabCounter) {
        try {

            if (!isAdded()) {
                return;
            }

            /** Updated Code -  Fixed Height given in item_design_vertical_cab _type && based on below code written*/
            new Handler().postDelayed(() -> {
                rentalAreaHeight = rentalarea.getMeasuredHeight();
                rvPaddignHeight = getResources().getDimensionPixelSize(R.dimen._200sdp);
                if (cabCounter == 1) {
                    tempMeasuredHeight = cashCardArea.getVisibility() == View.VISIBLE ? requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._240sdp) : requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._170sdp);
                } else if (cabCounter == 2) {
                    tempMeasuredHeight = cashCardArea.getVisibility() == View.VISIBLE ? requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._240sdp) : requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._170sdp);
                } else {
                    tempMeasuredHeight = cashCardArea.getVisibility() == View.VISIBLE ? requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._300sdp) : requestPayArea.getMeasuredHeight() + getResources().getDimensionPixelSize(R.dimen._250sdp);
                }
                if (mainAct.isRental || mainAct.iscubejekRental || mainAct.isRidePool /*|| (!mainAct.selType.equalsIgnoreCase("rental") && rentalAreaHeight == 0)*/) {
                    tempMeasuredHeight -= getResources().getDimensionPixelSize(R.dimen._30sdp);
                } else if (mainAct.isTaxiBid || mainAct.eForMedicalService || mainAct.isInterCity || mainAct.cabRquestType.equals(Utils.CabReqType_Later) || (mainAct.isMultiStopOverEnabled() && mainAct.stopOverPointsList != null && mainAct.stopOverPointsList.size() > 2) || (Utils.checkText(RideDeliveryType) && RideDeliveryType.equalsIgnoreCase("ride") && rentalAreaHeight == 0)) {
                    tempMeasuredHeight -= getResources().getDimensionPixelSize(R.dimen._40sdp);
                }
                mainAct.setPanelHeight(tempMeasuredHeight);
                mainAct.MultiDelPaddingGiven = true;
                noServiceTextTopMargin = tempMeasuredHeight / 2 - requestPayArea.getMeasuredHeight();
                mainAct.enableDisableBottomSheetDrag(true, false);
            }, 50);
        } catch (Exception ignored) {

        }
    }

    public void routeDraw() {
        if (isSkip) {
            if (route_polyLine != null) {
                route_polyLine.remove();
                route_polyLine = null;
            }
            return;
        }
        PolylineOptions lineOptions = null;

        if ((mainAct.isPoolCabTypeIdSelected || mainAct.eFly) && sourceLocation != null && destLocation != null) {
            lineOptions = createCurveRoute(new LatLng(sourceLocation.latitude, sourceLocation.longitude), new LatLng(destLocation.latitude, destLocation.longitude));

        } else if (!mainAct.isPoolCabTypeIdSelected && Utils.checkText(routeDrawResponse)) {

            if (mainAct.stopOverPointsList.size() > 2) {
                lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(R.color.black), getActContext(), mainAct.stopOverPointsList, wayPointslist, destPointlist, finalPointlist, getMap(), builder, isGoogle);

            } else {
                lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(android.R.color.black), isGoogle);
            }


        }

        if (lineOptions != null) {
            if (route_polyLine != null) {
                route_polyLine.remove();
                route_polyLine = null;
            }

            if (PolyLineAnimator.getInstance() != null) {
                PolyLineAnimator.getInstance().stopRouteAnim();
            }

            //Draw polyline
            route_polyLine = getMap().addPolyline(lineOptions);

            if (mainAct.isPoolCabTypeIdSelected) {
                route_polyLine.setColor(Color.parseColor("#cecece"));
                route_polyLine.setStartCap(getMap().getRoundCap());
                route_polyLine.setEndCap(getMap().getRoundCap());
            }


            if (route_polyLine != null && route_polyLine.getPoints().size() > 1) {
                PolyLineAnimator.getInstance().animateRoute(getMap(), route_polyLine.getPoints(), getActContext());
            }

            if (route_polyLine != null) {
                route_polyLine.remove();
            }
        }

    }

    public void buildNoCabMessage(String message, String positiveBtn) {

        if (mainAct.noCabAvailAlertBox != null) {
            mainAct.noCabAvailAlertBox.closeAlertBox();
            mainAct.noCabAvailAlertBox = null;
        }

        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(true);
        generateAlert.setBtnClickList(btn_id -> generateAlert.closeAlertBox());
        generateAlert.setContentMessage("", message);
        generateAlert.setPositiveBtn(positiveBtn);
        generateAlert.showAlertBox();

        mainAct.noCabAvailAlertBox = generateAlert;
    }

    public void findRoute(String etaVal) {

        if (mainAct != null && mainAct.isMultiDelivery()) {
            if (getMap() != null) {
                getMap().moveCamera(new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude(), Utils.defaultZomLevel));
            }

            if (mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.changeCabs();
            }

            return;
        }


        try {
            String waypoints = "";
            String parameters = "";
            ArrayList<String> data_waypoints = new ArrayList<>();
            HashMap<String, String> hashMap = new HashMap<>();

            if (mainAct != null && mainAct.stopOverPointsList.size() > 2 && mainAct.isMultiStopOverEnabled()) {
                // Origin of route
                String str_origin = "origin=" + mainAct.stopOverPointsList.get(0).getDestLat() + "," + mainAct.stopOverPointsList.get(0).getDestLong();


                String str_dest = "";


                wayPointslist = new ArrayList<>();      // List of Way Points
                destPointlist = new ArrayList<>();      // destination Points
                finalPointlist = new ArrayList<>();     // final Points list with time & distance & based on shortest location first algorithm
                stop_Over_Points_Temp_Array = new ArrayList<>(); // temp list of all points

                stop_Over_Points_Temp_Array = new ArrayList<Stop_Over_Points_Data>(mainAct.stopOverPointsList.subList(1, mainAct.stopOverPointsList.size()));

                // Retrive middle & destination points

                if (stop_Over_Points_Temp_Array.size() > 0) {
                    String lastAddress = "";
                    for (int i = 0; i < stop_Over_Points_Temp_Array.size(); i++) {

                        if (i == stop_Over_Points_Temp_Array.size() - 1) {
                            str_dest = "destination=" + stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLat() + "," + stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLong();
                            hashMap.put("d_latitude", stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLat() + "");
                            hashMap.put("d_longitude", stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLong() + "");
                            stop_Over_Points_Temp_Array.get(i).setDestination(true);
                            destPointlist.add(stop_Over_Points_Temp_Array.get(i));
                        } else if (i == stop_Over_Points_Temp_Array.size() - 2) {
                            wayPointslist.add(stop_Over_Points_Temp_Array.get(i));
                            lastAddress = stop_Over_Points_Temp_Array.get(i).getDestLat() + "," + stop_Over_Points_Temp_Array.get(i).getDestLong();
                            data_waypoints.add(stop_Over_Points_Temp_Array.get(i).getDestLat() + "," + stop_Over_Points_Temp_Array.get(i).getDestLong());

                        } else {
                            wayPointslist.add(stop_Over_Points_Temp_Array.get(i));
                            waypoints = waypoints + stop_Over_Points_Temp_Array.get(i).getDestLat() + "," + stop_Over_Points_Temp_Array.get(i).getDestLong() + "|";

                            data_waypoints.add(stop_Over_Points_Temp_Array.get(i).getDestLat() + "," + stop_Over_Points_Temp_Array.get(i).getDestLong());
                        }

                    }
                    waypoints = "waypoints=optimize:true|" + waypoints + lastAddress;

                } else {
                    str_dest = "destination=" + stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLat() + "," + stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLong();
                    hashMap.put("d_latitude", stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLat() + "");
                    hashMap.put("d_longitude", stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLong() + "");
                    destPointlist.add(stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1));
                }

                // Building the parameters to the web service
                if (stop_Over_Points_Temp_Array.size() > 1) {
                    parameters = str_origin + "&" + str_dest + "&" + waypoints;
                    // data_waypoints.add(stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLat() + "," + stop_Over_Points_Temp_Array.get(stop_Over_Points_Temp_Array.size() - 1).getDestLong());

                    hashMap.put("s_latitude", mainAct.stopOverPointsList.get(0).getDestLat() + "");
                    hashMap.put("s_longitude", mainAct.stopOverPointsList.get(0).getDestLong() + "");


                } else {
                    parameters = str_origin + "&" + str_dest;

                    hashMap.put("s_latitude", mainAct.stopOverPointsList.get(0).getDestLat() + "");
                    hashMap.put("s_longitude", mainAct.stopOverPointsList.get(0).getDestLong() + "");
                    hashMap.put("d_latitude", mainAct.stopOverPointsList.get(0).getDestLat() + "");
                    hashMap.put("d_longitude", mainAct.stopOverPointsList.get(0).getDestLong() + "");

                }

            } else {

                String originLoc = null;
                if (mainAct != null) {
                    originLoc = mainAct.getPickUpLocation().getLatitude() + "," + mainAct.getPickUpLocation().getLongitude();

                    String destLoc = null;
                    if (mainAct.destLocation != null) {
                        destLoc = mainAct.destLocation.getLatitude() + "," + mainAct.destLocation.getLongitude();
                        hashMap.put("d_latitude", mainAct.destLocation.getLatitude() + "");
                        hashMap.put("d_longitude", mainAct.destLocation.getLongitude() + "");

                        if (mainAct.destLocation.getLatitude() == 0.0) {
                            hashMap.put("d_latitude", mainAct.getPickUpLocation().getLatitude() + "");
                            hashMap.put("d_longitude", mainAct.getPickUpLocation().getLongitude() + "");

                        }


                    } else {
                        destLoc = mainAct.getPickUpLocation().getLatitude() + "," + mainAct.getPickUpLocation().getLongitude();
                        hashMap.put("d_latitude", mainAct.getPickUpLocation().getLatitude() + "");
                        hashMap.put("d_longitude", mainAct.getPickUpLocation().getLongitude() + "");

                    }

                    hashMap.put("s_latitude", mainAct.getPickUpLocation().getLatitude() + "");
                    hashMap.put("s_longitude", mainAct.getPickUpLocation().getLongitude() + "");


                    parameters = "origin=" + originLoc + "&destination=" + destLoc;

                }
            }
            hashMap.put("parameters", parameters);
            hashMap.put("waypoints", data_waypoints.toString());

            mProgressBar.setIndeterminate(true);
            mProgressBar.setVisibility(View.VISIBLE);

            if (mainAct != null && !isCallDirectionApi) {
                isCallDirectionApi = true;

                AppService.getInstance().executeService(mainAct.getActContext(), new DataProvider.DataProviderBuilder(hashMap.get("s_latitude"), hashMap.get("s_longitude")).setDestLatitude(hashMap.get("d_latitude")).setDestLongitude(hashMap.get("d_longitude")).setWayPoints(MyApp.getInstance().GetStringArray(data_waypoints)).setData_Str(waypoints).build(), AppService.Service.DIRECTION, data -> {
                    Logger.d("onResult", "::" + data);

                    isCallDirectionApi = false;

                    mProgressBar.setIndeterminate(false);
                    mProgressBar.setVisibility(mainAct.isMultiDelivery() ? View.GONE : View.INVISIBLE);
                    if (data.get("RESPONSE_TYPE") != null && data.get("RESPONSE_TYPE").toString().equalsIgnoreCase("FAIL")) {
                        isRouteFail = true;
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                        return;
                    }

                    String responseString = "";
                    try {
                        // First try to get ROUTES value (old format)
                        Object routesObject = data.get("ROUTES");
                        if (routesObject != null) {
                            if (routesObject instanceof String) {
                                // Parse the ROUTES string as a JSONObject
                                JSONObject routesJsonObject = new JSONObject((String) routesObject);

                                // Extract the "routes" array from the JSONObject
                                JSONArray routesArray = routesJsonObject.optJSONArray("routes");

                                if (routesArray != null && routesArray.length() > 0) {
                                    // Convert the array to string and set response
                                    responseString = routesArray.toString();
                                } else {
                                    // Handle empty routes array
                                    isRouteFail = true;
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                    return;
                                }
                            } else if (routesObject instanceof org.json.JSONArray) {
                                // ROUTES is already a JSONArray (new format - array of coordinates)
                                JSONArray routesArray = (org.json.JSONArray) routesObject;
                                if (routesArray.length() > 0) {
                                    // Convert coordinate array to routes format
                                    Object responseDataObj = data.get("RESPONSE_DATA");
                                    String distanceStr = data.get("DISTANCE") != null ? data.get("DISTANCE").toString() : "0";
                                    String durationStr = data.get("DURATION") != null ? data.get("DURATION").toString() : "0";
                                    
                                    JSONObject routeObj = new JSONObject();
                                    JSONObject legObj = new JSONObject();
                                    JSONObject distanceObj = new JSONObject();
                                    JSONObject durationObj = new JSONObject();
                                    JSONObject startLocation = new JSONObject();
                                    JSONObject endLocation = new JSONObject();
                                    
                                    distanceObj.put("value", Integer.parseInt(distanceStr));
                                    distanceObj.put("text", distanceStr + " m");
                                    
                                    durationObj.put("value", Integer.parseInt(durationStr));
                                    durationObj.put("text", durationStr + " sec");
                                    
                                    // Get first and last coordinates
                                    JSONObject firstPoint = routesArray.getJSONObject(0);
                                    JSONObject lastPoint = routesArray.getJSONObject(routesArray.length() - 1);
                                    
                                    startLocation.put("lat", firstPoint.optString("latitude", firstPoint.optString("lat", "0")));
                                    startLocation.put("lng", firstPoint.optString("longitude", firstPoint.optString("lng", "0")));
                                    
                                    endLocation.put("lat", lastPoint.optString("latitude", lastPoint.optString("lat", "0")));
                                    endLocation.put("lng", lastPoint.optString("longitude", lastPoint.optString("lng", "0")));
                                    
                                    legObj.put("distance", distanceObj);
                                    legObj.put("duration", durationObj);
                                    legObj.put("start_location", startLocation);
                                    legObj.put("end_location", endLocation);
                                    
                                    JSONArray legsArray = new JSONArray();
                                    legsArray.put(legObj);
                                    
                                    routeObj.put("legs", legsArray);
                                    
                                    JSONArray routesArrayNew = new JSONArray();
                                    routesArrayNew.put(routeObj);
                                    
                                    responseString = routesArrayNew.toString();
                                } else {
                                    isRouteFail = true;
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                    return;
                                }
                            } else if (routesObject instanceof List) {
                                // ROUTES is a List (new format - array of coordinate maps)
                                List<?> routesList = (List<?>) routesObject;
                                if (routesList.size() > 0) {
                                    // Convert List to JSONArray
                                    JSONArray routesArray = new JSONArray();
                                    for (Object item : routesList) {
                                        if (item instanceof java.util.Map) {
                                            JSONObject coordObj = new JSONObject((java.util.Map<?, ?>) item);
                                            routesArray.put(coordObj);
                                        } else {
                                            routesArray.put(item);
                                        }
                                    }
                                    
                                    // Convert coordinate array to routes format
                                    String distanceStr = data.get("DISTANCE") != null ? data.get("DISTANCE").toString() : "0";
                                    String durationStr = data.get("DURATION") != null ? data.get("DURATION").toString() : "0";
                                    
                                    JSONObject routeObj = new JSONObject();
                                    JSONObject legObj = new JSONObject();
                                    JSONObject distanceObj = new JSONObject();
                                    JSONObject durationObj = new JSONObject();
                                    JSONObject startLocation = new JSONObject();
                                    JSONObject endLocation = new JSONObject();
                                    
                                    distanceObj.put("value", Integer.parseInt(distanceStr));
                                    distanceObj.put("text", distanceStr + " m");
                                    
                                    durationObj.put("value", Integer.parseInt(durationStr));
                                    durationObj.put("text", durationStr + " sec");
                                    
                                    // Get first and last coordinates
                                    JSONObject firstPoint = routesArray.getJSONObject(0);
                                    JSONObject lastPoint = routesArray.getJSONObject(routesArray.length() - 1);
                                    
                                    startLocation.put("lat", firstPoint.optString("latitude", firstPoint.optString("lat", "0")));
                                    startLocation.put("lng", firstPoint.optString("longitude", firstPoint.optString("lng", "0")));
                                    
                                    endLocation.put("lat", lastPoint.optString("latitude", lastPoint.optString("lat", "0")));
                                    endLocation.put("lng", lastPoint.optString("longitude", lastPoint.optString("lng", "0")));
                                    
                                    legObj.put("distance", distanceObj);
                                    legObj.put("duration", durationObj);
                                    legObj.put("start_location", startLocation);
                                    legObj.put("end_location", endLocation);
                                    
                                    JSONArray legsArray = new JSONArray();
                                    legsArray.put(legObj);
                                    
                                    routeObj.put("legs", legsArray);
                                    
                                    JSONArray routesArrayNew = new JSONArray();
                                    routesArrayNew.put(routeObj);
                                    
                                    responseString = routesArrayNew.toString();
                                } else {
                                    isRouteFail = true;
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                    return;
                                }
                            } else {
                                // Handle unexpected type - try to convert to string and parse
                                String routesStr = routesObject.toString();
                                try {
                                    JSONArray routesArray = new JSONArray(routesStr);
                                    if (routesArray.length() > 0) {
                                        // Same conversion as above
                                        String distanceStr = data.get("DISTANCE") != null ? data.get("DISTANCE").toString() : "0";
                                        String durationStr = data.get("DURATION") != null ? data.get("DURATION").toString() : "0";
                                        
                                        JSONObject routeObj = new JSONObject();
                                        JSONObject legObj = new JSONObject();
                                        JSONObject distanceObj = new JSONObject();
                                        JSONObject durationObj = new JSONObject();
                                        JSONObject startLocation = new JSONObject();
                                        JSONObject endLocation = new JSONObject();
                                        
                                        distanceObj.put("value", Integer.parseInt(distanceStr));
                                        distanceObj.put("text", distanceStr + " m");
                                        
                                        durationObj.put("value", Integer.parseInt(durationStr));
                                        durationObj.put("text", durationStr + " sec");
                                        
                                        JSONObject firstPoint = routesArray.getJSONObject(0);
                                        JSONObject lastPoint = routesArray.getJSONObject(routesArray.length() - 1);
                                        
                                        startLocation.put("lat", firstPoint.optString("latitude", firstPoint.optString("lat", "0")));
                                        startLocation.put("lng", firstPoint.optString("longitude", firstPoint.optString("lng", "0")));
                                        
                                        endLocation.put("lat", lastPoint.optString("latitude", lastPoint.optString("lat", "0")));
                                        endLocation.put("lng", lastPoint.optString("longitude", lastPoint.optString("lng", "0")));
                                        
                                        legObj.put("distance", distanceObj);
                                        legObj.put("duration", durationObj);
                                        legObj.put("start_location", startLocation);
                                        legObj.put("end_location", endLocation);
                                        
                                        JSONArray legsArray = new JSONArray();
                                        legsArray.put(legObj);
                                        
                                        routeObj.put("legs", legsArray);
                                        
                                        JSONArray routesArrayNew = new JSONArray();
                                        routesArrayNew.put(routeObj);
                                        
                                        responseString = routesArrayNew.toString();
                                    } else {
                                        isRouteFail = true;
                                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                        return;
                                    }
                                } catch (Exception e) {
                                    Logger.e("CabSelectionFragment", "Error parsing ROUTES: " + e.getMessage());
                                    isRouteFail = true;
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                    return;
                                }
                            }
                        } else {
                            // Try RESPONSE_DATA (new format)
                            Object responseDataObject = data.get("RESPONSE_DATA");
                            if (responseDataObject != null) {
                                String responseDataStr = responseDataObject.toString();
                                if (!responseDataStr.equalsIgnoreCase("") && !responseDataStr.equalsIgnoreCase("null")) {
                                    // Parse RESPONSE_DATA to check if it has routes or data array
                                    JSONObject responseJson = new JSONObject(responseDataStr);
                                    
                                    // Check for "routes" array (Google Maps format)
                                    JSONArray routesArray = responseJson.optJSONArray("routes");
                                    if (routesArray != null && routesArray.length() > 0) {
                                        responseString = routesArray.toString();
                                    } else {
                                        // Check for "data" array (new API format)
                                        JSONArray dataArray = responseJson.optJSONArray("data");
                                        if (dataArray != null && dataArray.length() > 0) {
                                            // Convert data array format to routes format for compatibility
                                            JSONObject routeObj = new JSONObject();
                                            JSONObject legObj = new JSONObject();
                                            JSONObject distanceObj = new JSONObject();
                                            JSONObject durationObj = new JSONObject();
                                            JSONObject startLocation = new JSONObject();
                                            JSONObject endLocation = new JSONObject();
                                            
                                            // Get distance and duration from response
                                            String distanceStr = responseJson.optString("distance", "0");
                                            String durationStr = responseJson.optString("duration", "0");
                                            
                                            distanceObj.put("value", Integer.parseInt(distanceStr));
                                            distanceObj.put("text", distanceStr + " m");
                                            
                                            durationObj.put("value", Integer.parseInt(durationStr));
                                            durationObj.put("text", durationStr + " sec");
                                            
                                            // Get first and last coordinates
                                            if (dataArray.length() > 0) {
                                                JSONObject firstPoint = dataArray.getJSONObject(0);
                                                JSONObject lastPoint = dataArray.getJSONObject(dataArray.length() - 1);
                                                
                                                startLocation.put("lat", firstPoint.optString("latitude", "0"));
                                                startLocation.put("lng", firstPoint.optString("longitude", "0"));
                                                
                                                endLocation.put("lat", lastPoint.optString("latitude", "0"));
                                                endLocation.put("lng", lastPoint.optString("longitude", "0"));
                                            }
                                            
                                            legObj.put("distance", distanceObj);
                                            legObj.put("duration", durationObj);
                                            legObj.put("start_location", startLocation);
                                            legObj.put("end_location", endLocation);
                                            
                                            JSONArray legsArray = new JSONArray();
                                            legsArray.put(legObj);
                                            
                                            routeObj.put("legs", legsArray);
                                            
                                            JSONArray routesArrayNew = new JSONArray();
                                            routesArrayNew.put(routeObj);
                                            
                                            responseString = routesArrayNew.toString();
                                        } else {
                                            // Use the full response as-is
                                            responseString = responseDataStr;
                                        }
                                    }
                                }
                            }
                            
                            if (responseString.equalsIgnoreCase("")) {
                                // Handle missing both ROUTES and RESPONSE_DATA keys
                                isRouteFail = true;
                                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                                return;
                            }
                        }
                    } catch (JSONException e) {
                        // Handle JSON parsing errors
                        Logger.e("CabSelectionFragment", "Error parsing direction response: " + e.getMessage());
                        isRouteFail = true;
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DEST_ROUTE_NOT_FOUND"));
                        e.printStackTrace();
                    }


                    if (responseString.equalsIgnoreCase("")) {


                        isRouteFail = true;
                        if (!isSkip) {
                            GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                            alertBox.setContentMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                            alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                            alertBox.setBtnClickList(btn_id -> {
                                alertBox.closeAlertBox();
                                mainAct.userLocBtnImgView.performClick();

                            });
                            alertBox.showAlertBox();
                        }

                        if (isSkip) {
                            Logger.d("directionResult", "::NULLSKIP##");
                            isRouteFail = false;
                            if (mainAct.destLocation != null && mainAct.pickUpLocation != null) {
                                handleMapAnimation(responseString, new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude()), new LatLng(mainAct.destLocation.getLatitude(), mainAct.destLocation.getLongitude()), "--");
                            }
                        } else {
                            mainAct.userLocBtnImgView.performClick();
                        }

                        isSkip = true;
                        if (getActivity() != null) {
                            estimateFare(null, null);
                        }
                        return;
                    }
                    if (responseString.equalsIgnoreCase("null")) {
                        responseString = null;
                    }

                    if (responseString != null && !responseString.equalsIgnoreCase("") && data.get("DISTANCE") == null) {
                        isGoogle = true;
                        isRouteFail = false;
                        //                JSONArray obj_routes = generalFunc.getJsonArray(responseString);
                        JSONArray obj_routes = generalFunc.getJsonArray("routes", responseString);
                        if (obj_routes != null && obj_routes.length() > 0) {


                            if (mainAct.stopOverPointsList.size() > 2 && mainAct.isMultiStopOverEnabled()) {

                                if (finalPointlist.size() > 0) {
                                    ArrayList<Stop_Over_Points_Data> finalAllPointlist = new ArrayList<>();
                                    finalAllPointlist = new ArrayList<>();
                                    finalAllPointlist.add(mainAct.stopOverPointsList.get(0));
                                    finalAllPointlist.addAll(finalPointlist);
                                    finalPointlist.clear();
                                    mainAct.stopOverPointsList.clear();
                                    mainAct.stopOverPointsList.addAll(finalAllPointlist);
                                }


                                sourceLocation = mainAct.stopOverPointsList.get(0).getDestLatLong();
                                destLocation = mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).getDestLatLong();

                                StopOverPointsDataParser parser = new StopOverPointsDataParser(getActContext(), mainAct.stopOverPointsList, wayPointslist, destPointlist, finalPointlist, getMap(), builder);
                                HashMap<String, Object> data_dict = new HashMap<>();
                                Object routesData = data.get("ROUTES") != null ? data.get("ROUTES") : data.get("RESPONSE_DATA");
                                data_dict.put("routes", routesData);
                                parser.getDistanceArray(generalFunc.getJsonObject(responseString));
                                //  List<List<HashMap<String, String>>> routes_list = parser.parse(generalFunc.getJsonObject(responseString));

                                distance = parser.distance;
                                time = parser.time;

                            } else {

                                JSONObject obj_legs = generalFunc.getJsonObject(generalFunc.getJsonArray("legs", generalFunc.getJsonObject(obj_routes, 0).toString()), 0);


                                distance = "" + (GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("distance", obj_legs.toString()))));

                                time = "" + (GeneralFunctions.parseDoubleValue(0, generalFunc.getJsonValue("value", generalFunc.getJsonValue("duration", obj_legs.toString()))));

                                sourceLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lat", generalFunc.getJsonValue("start_location", obj_legs.toString()))), GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lng", generalFunc.getJsonValue("start_location", obj_legs.toString()))));

                                destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lat", generalFunc.getJsonValue("end_location", obj_legs.toString()))), GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("lng", generalFunc.getJsonValue("end_location", obj_legs.toString()))));

                            }

                            if (getActivity() != null) {
                                estimateFare(distance, time);
                            }


                            //temp animation test
                            Object routesData = data.get("ROUTES") != null ? data.get("ROUTES") : data.get("RESPONSE_DATA");
                            if (routesData != null) {
                                responseString = routesData.toString();
                            }
                            handleMapAnimation(responseString, sourceLocation, destLocation, "--");

                        }
                    } else if (responseString == null) {
                        Logger.d("directionResult", "::NULL##");

                        isRouteFail = true;
                        if (!isSkip) {
                            GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                            alertBox.setContentMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                            alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                            alertBox.setBtnClickList(btn_id -> {
                                alertBox.closeAlertBox();
                                mainAct.userLocBtnImgView.performClick();

                            });
                            alertBox.showAlertBox();
                        }

                        if (isSkip) {
                            Logger.d("directionResult", "::NULLSKIP##");
                            isRouteFail = false;
                            if (mainAct.destLocation != null && mainAct.pickUpLocation != null) {
                                handleMapAnimation(responseString, new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude()), new LatLng(mainAct.destLocation.getLatitude(), mainAct.destLocation.getLongitude()), "--");
                            }
                        } else {
                            mainAct.userLocBtnImgView.performClick();
                        }

                        isSkip = true;
                        if (getActivity() != null) {
                            estimateFare(null, null);
                        }

                    } else {
                        isGoogle = false;

                        if (mainAct.stopOverPointsList.size() > 2 && mainAct.isMultiStopOverEnabled()) {

                            if (finalPointlist.size() > 0) {
                                ArrayList<Stop_Over_Points_Data> finalAllPointlist = new ArrayList<>();
                                finalAllPointlist = new ArrayList<>();
                                finalAllPointlist.add(mainAct.stopOverPointsList.get(0));
                                finalAllPointlist.addAll(finalPointlist);
                                finalPointlist.clear();
                                mainAct.stopOverPointsList.clear();
                                mainAct.stopOverPointsList.addAll(finalAllPointlist);
                            }


                            sourceLocation = mainAct.stopOverPointsList.get(0).getDestLatLong();
                            destLocation = mainAct.stopOverPointsList.get(mainAct.stopOverPointsList.size() - 1).getDestLatLong();


                            setWaypoints(generalFunc.getJsonArray(data.get("WAYPOINTS_ORDER").toString()));

                            distance = data.get("DISTANCE").toString();
                            time = data.get("DURATION").toString();
                            sourceLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, hashMap.get("s_latitude")), GeneralFunctions.parseDoubleValue(0.0, hashMap.get("s_longitude")));

                            destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_latitude")), GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_longitude")));

                        } else {

                            distance = data.get("DISTANCE").toString();
                            time = data.get("DURATION").toString();
                            sourceLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, hashMap.get("s_latitude")), GeneralFunctions.parseDoubleValue(0.0, hashMap.get("s_longitude")));

                            destLocation = new LatLng(GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_latitude")), GeneralFunctions.parseDoubleValue(0.0, hashMap.get("d_longitude")));
                        }


                        if (getActivity() != null) {
                            estimateFare(distance, time);
                        }


                        //temp animation test


                        HashMap<String, Object> data_dict = new HashMap<>();
                        Object routesData = data.get("ROUTES") != null ? data.get("ROUTES") : data.get("RESPONSE_DATA");
                        data_dict.put("routes", routesData);
                        responseString = data_dict.toString();

                        handleMapAnimation(responseString, sourceLocation, destLocation, "--");
                    }


                });
            }

        } catch (Exception e) {


        }
    }


    public void setEta(String time) {
        if (etaTxt != null) {
            etaTxt.setText(time);
        }


    }

    public void mangeMrakerPostion() {
        try {
            updateMarkerAnchorPosition(sourceMarker);
            updateMarkerAnchorPosition(destMarker);
        } catch (Exception e) {

            e.printStackTrace();
        }


    }

    private void updateMarkerAnchorPosition(Marker marker) {
        if (true) {
            return;
        }

        float screenWidth = Utils.getScreenPixelWidth(getActContext());
        float screenHeight = Utils.getScreenPixelHeight(getActContext());
        int bottomViewHeight = tempMeasuredHeight - rentalAreaHeight;


        if (marker != null) {
            Point PickupPoint = getMap().getProjection().toScreenLocation(new LatLng(marker.getPosition().latitude, marker.getPosition().longitude));

            float anchorU = marker.getAnchorU();
            float anchorV = marker.getAnchorV();


            boolean isAnchorChanged = false;
            if (PickupPoint.x - mainAct.getResources().getDimensionPixelSize(R.dimen._115sdp) < 0) {
                // move horizontally right side
                anchorU = 0.1f;
                isAnchorChanged = true;
                marker.flipIcon(1, 0);
            }
            if (PickupPoint.x + mainAct.getResources().getDimensionPixelSize(R.dimen._100sdp) > screenWidth) {
                // move horizontally left side
                anchorU = 0.9f;
                isAnchorChanged = true;

                marker.flipIcon(-1, 0);
            }
            if (PickupPoint.y - mainAct.getResources().getDimensionPixelSize(R.dimen._50sdp) < 0) {
                // move vertically top side
                anchorV = 0f;
                isAnchorChanged = true;
                marker.flipIcon(0, -1);
            }
            if (PickupPoint.y + mainAct.getResources().getDimensionPixelSize(R.dimen._40sdp) > (screenHeight - bottomViewHeight)) {
                // move vertically bottom side
                anchorV = 1.50f;
                isAnchorChanged = true;
                marker.flipIcon(0, 1);
            }
            if (isAnchorChanged) {
                marker.setAnchor(anchorU, anchorV);
            }
        }


    }

    public void handleSourceMarker(String etaVal) {
        try {
            if (!isSkip) {
                if (mainAct.pickUpLocation == null) {
                    return;
                }
            }

            if (marker_view == null) {
                marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.custom_marker, null);
                addressTxt = marker_view.findViewById(R.id.addressTxt);
                etaTxt = marker_view.findViewById(R.id.etaTxt);
            }

            if (marker_view != null) {
                etaTxt = marker_view.findViewById(R.id.etaTxt);
            }

            addressTxt.setTextColor(getActContext().getResources().getColor(R.color.sourceAddressTxt));

            LatLng fromLnt;
//            if (isSkip) {
            estimateFare(distance, time);
//                if (destMisSkiparker != null) {
//                    destMarker.remove();
//                }
            if (destDotMarker != null) {
                destDotMarker.remove();
            }
            if (route_polyLine != null) {
                route_polyLine.remove();
            }

            destLocation = null;
            mainAct.destLocation = null;

            fromLnt = new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude());

//            } else {
//                fromLnt = new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude());
//
//                if (sourceLocation != null) {
//                    fromLnt = sourceLocation;
//                }
//
//
//            }


            etaTxt.setVisibility(View.VISIBLE);
            etaTxt.setText(etaVal);

            if (sourceMarker != null) {
                sourceMarker.remove();
                sourceMarker = null;
            }

            if (source_dot_option != null) {
                if (sourceDotMarker != null) {
                    sourceDotMarker.remove();
                    sourceDotMarker = null;
                }
                source_dot_option = null;
            }

            source_dot_option = new MarkerOptions().position(fromLnt).icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.marker_square, 0));

            if (getMap() != null) {
                sourceDotMarker = getMap().addMarker(source_dot_option);
            }

            String name = "";
            if (generalFunc.retrieveValue(Utils.BOOK_FOR_ELSE_ENABLE_KEY).equalsIgnoreCase("yes") && getCurrentCabGeneralType().equalsIgnoreCase(Utils.CabGeneralType_Ride)) {

                if (generalFunc.containsKey(Utils.BFSE_SELECTED_CONTACT_KEY) && Utils.checkText(generalFunc.retrieveValue(Utils.BFSE_SELECTED_CONTACT_KEY))) {
                    Gson gson = new Gson();
                    String data1 = generalFunc.retrieveValue(Utils.BFSE_SELECTED_CONTACT_KEY);
                    ContactModel contactdetails = gson.fromJson(data1, new TypeToken<ContactModel>() {
                    }.getType());


                    if (Utils.checkText(contactdetails.name) && !contactdetails.name.equalsIgnoreCase(generalFunc.retrieveLangLBl("Me", "LBL_ME"))) {
                        int n = 5;
                        String upToNCharacters = contactdetails.name.substring(0, Math.min(contactdetails.name.length(), n)) + (contactdetails.name.length() > n ? "..." : "");
                        name = "<b><font color=" + getActContext().getResources().getColor(R.color.black) + ">" + "@" + upToNCharacters + "</font><b>" + " - ";
                    }
                }
            }

            addressTxt.setText(GeneralFunctions.fromHtml(name + mainAct.pickUpLocationAddress));
            MarkerOptions marker_opt_source = new MarkerOptions().position(fromLnt).icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))).anchor(0.1f, 1.60f);
            if (getMap() != null) {
                sourceMarker = getMap().addMarker(marker_opt_source);

                sourceMarker.setTag("1");
            }

            buildBuilder();

            if (isSkip) {
                if (getMap() != null) {
                    getMap().moveCamera(new LatLng(mainAct.pickUpLocation.getLatitude(), mainAct.pickUpLocation.getLongitude(), Utils.defaultZomLevel));
                }
            }

        } catch (Exception e) {
            // Backpress done by user then app crashes
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    @SuppressLint("SetTextI18n")
    public void handleMapAnimation(String responseString, LatLng sourceLocation, LatLng destLocation, String etaVal) {

        try {
            if (mainAct == null) {
                return;
            }

            //    getMap().clear();
            if (mainAct.cabSelectionFrag == null) {
                return;
            }

            if (isSkip) {
                PolyLineAnimator.getInstance().stopRouteAnim();
                if (route_polyLine != null) {
                    route_polyLine.remove();
                    route_polyLine = null;
                }
                handleSourceMarker(etaVal);
                return;
            }
            //manage for remove duplicate marker
            PolyLineAnimator.getInstance().stopRouteAnim();

            LatLng fromLnt = new LatLng(sourceLocation.latitude, sourceLocation.longitude);
            LatLng toLnt = new LatLng(destLocation.latitude, destLocation.longitude);


            if (marker_view == null) {

                marker_view = ((LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE))
                        .inflate(R.layout.custom_marker, null);
                addressTxt = marker_view
                        .findViewById(R.id.addressTxt);
                etaTxt = marker_view.findViewById(R.id.etaTxt);
            }

            addressTxt.setTextColor(getActContext().getResources().getColor(R.color.destAddressTxt));


            addressTxt.setText(mainAct.destAddress + " " + (mainAct.stopOverPointsList.size() >= 3 ? ">" : ""));

            MarkerOptions marker_opt_dest = new MarkerOptions().position(toLnt);
            etaTxt.setVisibility(View.GONE);

            marker_opt_dest.icon(BitmapDescriptorFactory.fromBitmap(createDrawableFromView(getActContext(), marker_view))).anchor(0.1f, 1.50f);
            if (dest_dot_option != null) {
                destDotMarker.remove();
            }
            dest_dot_option = new MarkerOptions().position(toLnt).icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.dot_filled_new, 0));
            destDotMarker = getMap().addMarker(dest_dot_option);

            if (destMarker != null) {
                destMarker.remove();
            }
            destMarker = getMap().addMarker(marker_opt_dest);
            destMarker.setTag("2");

            handleSourceMarker(etaVal);

            JSONArray obj_routes1 = generalFunc.getJsonArray("routes", responseString);


            PolylineOptions lineOptions = null;
            if (obj_routes1 != null && obj_routes1.length() > 0) {
                routeDrawResponse = responseString;
                Logger.d("routeDrawResponse", "::" + routeDrawResponse);

                if ((mainAct.isPoolCabTypeIdSelected || mainAct.eFly) && sourceLocation != null && destLocation != null) {
                    lineOptions = createCurveRoute(new LatLng(sourceLocation.latitude, sourceLocation.longitude), new LatLng(destLocation.latitude, destLocation.longitude));

                } else if (!mainAct.isPoolCabTypeIdSelected && Utils.checkText(routeDrawResponse)) {
                    if (mainAct.stopOverPointsList.size() > 2) {
                        lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(R.color.black), getActContext(), mainAct.stopOverPointsList, wayPointslist, destPointlist, finalPointlist, getMap(), builder, isGoogle);

                    } else {
                        lineOptions = getGoogleRouteOptions(routeDrawResponse, 5, getActContext().getResources().getColor(android.R.color.black), isGoogle);
                    }

                }
            } else {
                if (mainAct.eFly && sourceLocation != null && destLocation != null) {
                    lineOptions = createCurveRoute(new LatLng(sourceLocation.latitude, sourceLocation.longitude), new LatLng(destLocation.latitude, destLocation.longitude));
                }
            }

            if (lineOptions != null) {
                if (route_polyLine != null) {
                    route_polyLine.remove();
                    route_polyLine = null;
                }
                route_polyLine = getMap().addPolyline(lineOptions);
                route_polyLine.remove();
            }
            if (route_polyLine != null && route_polyLine.getPoints().size() > 1) {
                PolyLineAnimator.getInstance().animateRoute(getMap(), route_polyLine.getPoints(), getActContext());
            }


            if (mainAct.loadAvailCabs != null && !mainAct.loadAvailCabs.driverMarkerList.isEmpty()) {
                mainAct.loadAvailCabs.changeCabs();
            }


        } catch (Exception e) {
            // Backpress done by user then app crashes
            Logger.d("onResult", "::error::" + e);
            Logger.e("Exception", "::" + e.getMessage());
        }

    }

    public void buildBuilder() {
        if (mainAct == null) {
            return;
        }
        if (sourceMarker != null && (destMarker == null || isSkip)) {

            builder = new LatLngBounds.Builder();

            builder.include(sourceMarker.getPosition());

            int padding = (mainAct != null && mainAct.isMultiDelivery()) ? (Utils.getScreenPixelWidth(getActContext()) != 0 ? (int) ((Utils.getScreenPixelWidth(getActContext())) * 0.35) : 0) : 0; // offset from edges of the map in pixels

            LatLngBounds bounds = builder.build();
            LatLng center = bounds.getCenter();
            LatLng northEast = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
            LatLng southWest = SphericalUtil.computeOffset(center, 30 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));
            builder.include(southWest);
            builder.include(northEast);

            getMap().moveCamera(builder.build(), padding);

        } else if (getMap() != null && getMap().getViewTreeObserver().isAlive()) {
            getMap().getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
                @SuppressLint("NewApi") // We check which build version we are using.
                @Override
                public void onGlobalLayout() {

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

                        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.JELLY_BEAN) {
                            getMap().getViewTreeObserver().removeGlobalOnLayoutListener(this);
                        } else {
                            getMap().getViewTreeObserver().removeOnGlobalLayoutListener(this);
                        }


                        LatLngBounds bounds = builder.build();


                        LatLng center = bounds.getCenter();

                        LatLng northEast = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), SphericalUtil.computeHeading(center, bounds.northeast));
                        LatLng southWest = SphericalUtil.computeOffset(center, 10 * Math.sqrt(2.0), (180 + (180 + SphericalUtil.computeHeading(center, bounds.southwest))));

                        builder.include(southWest);
                        builder.include(northEast);
                        int width = (int) (Utils.getScreenPixelWidth(getActContext()));
                        int height = (int) (Utils.getScreenPixelHeight(getActContext()));
                        // Set Padding according to included bounds

                        int padding = (int) (width * 0.25); // offset from edges of the map 25% of screen
                        int height_ = 0;
                        if (mainAct != null && mainAct.mainHeaderFrag != null && mainAct.isMultiDelivery()) {
                            height_ = mainAct.mainHeaderFrag.fragmentHeight;
                        } else {
                            height_ = Utils.dipToPixels(getActContext(), 60);
                        }

                        padding = height - ((tempMeasuredHeight + 5) + height_);

                        int screenWidth = (int) Utils.getScreenPixelWidth(getActContext());
                        int screenHeight = (int) Utils.getScreenPixelHeight(getActContext());
                        getMap().animateCamera(builder.build(), screenWidth, screenHeight, Math.max((padding / 3), 0));


                    }

                }
            });
        }
    }

    // add route polyline line
    public PolylineOptions getGoogleRouteOptions(String directionJson, int width, int color, Context mContext, ArrayList<Stop_Over_Points_Data> list, ArrayList<Stop_Over_Points_Data> wayPointslist, ArrayList<Stop_Over_Points_Data> destPointlist, ArrayList<Stop_Over_Points_Data> finalPointlist, GeoMapLoader.GeoMap geoMap, LatLngBounds.Builder builder, Boolean isGoogle) {
        PolylineOptions lineOptions = new PolylineOptions();

        Logger.d("isGoogleVal", "::" + isGoogle);
        if (isGoogle) {


            StopOverPointsDataParser parser = new StopOverPointsDataParser(mContext, list, wayPointslist, destPointlist, finalPointlist, geoMap, builder);

            List<List<HashMap<String, String>>> routes_list = null;
            try {
                routes_list = parser.parse(new JSONObject(directionJson));
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
            ArrayList<LatLng> points = new ArrayList<LatLng>();

            if (routes_list != null && routes_list.size() > 0) {
                // Fetching i-th route
                List<HashMap<String, String>> path = routes_list.get(0);

                // Fetching all the points in i-th route
                for (int j = 0; j < path.size(); j++) {
                    HashMap<String, String> point = path.get(j);

                    double lat = Double.parseDouble(point.get("lat"));
                    double lng = Double.parseDouble(point.get("lng"));
                    LatLng position = new LatLng(lat, lng);
                    points.add(position);

                }

                lineOptions.addAll(points);
                lineOptions.width(width);
                lineOptions.color(color);

                return lineOptions;
            } else {
                return null;
            }
        } else {

            try {
                JSONArray obj_routes1 = generalFunc.getJsonArray("routes", directionJson);

                ArrayList<LatLng> points = new ArrayList<LatLng>();

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
    }

    public PolylineOptions getGoogleRouteOptions(String directionJson, int width, int color, Boolean isGoogle) {
        PolylineOptions lineOptions = new PolylineOptions();

        if (isGoogle) {

            try {
                DirectionsJSONParser parser = new DirectionsJSONParser();
                List<List<HashMap<String, String>>> routes_list = parser.parse(new JSONObject(directionJson));

                ArrayList<LatLng> points = new ArrayList<LatLng>();

                if (routes_list.size() > 0) {
                    // Fetching i-th route
                    List<HashMap<String, String>> path = routes_list.get(0);

                    // Fetching all the points in i-th route
                    for (int j = 0; j < path.size(); j++) {
                        HashMap<String, String> point = path.get(j);

                        double lat = Double.parseDouble(point.get("lat"));
                        double lng = Double.parseDouble(point.get("lng"));
                        LatLng position = new LatLng(lat, lng);

                        points.add(position);

                    }

                    lineOptions.addAll(points);
                    lineOptions.width(width);
                    lineOptions.color(color);

                    return lineOptions;
                } else {
                    Logger.d("getGoogleRouteOptionsEx", ":: null");
                    return null;

                }
            } catch (Exception e) {
                Logger.d("getGoogleRouteOptionsEx", "::" + e);
                return null;
            }
        } else {

            try {
                JSONArray obj_routes1 = generalFunc.getJsonArray("routes", directionJson);

                ArrayList<LatLng> points = new ArrayList<LatLng>();

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
    }

    public String getAvailableCarTypesIds() {
        String carTypesIds = "";
        for (int i = 0; i < mainAct.cabTypesArrList.size(); i++) {
            String iVehicleTypeId = mainAct.cabTypesArrList.get(i).get("iVehicleTypeId");

            carTypesIds = carTypesIds.equals("") ? iVehicleTypeId : (carTypesIds + "," + iVehicleTypeId);
        }
        return carTypesIds;
    }

    public void estimateFare(final String distance, String time) {
        if (mainAct.isMultiDelivery()) {
            return;
        }

        //  loaderView.setVisibility(View.VISIBLE);

        if (estimateFareTask != null) {
            return;
        }
        if (distance == null && time == null) {
        } else {
            if (mainAct.loadAvailCabs != null) {
                if (mainAct.loadAvailCabs.isAvailableCab) {
                    isroutefound = true;
                    if (!mainAct.timeval.equalsIgnoreCase("\n" + "--")) {
                        mainAct.noCabAvail = false;
                    }
                }
            }

        }

        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "estimateFareNew");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("SelectedCarTypeID", getAvailableCarTypesIds());
        if (distance != null && time != null) {
            parameters.put("distance", distance);
            parameters.put("time", time);
        }
        parameters.put("SelectedCar", mainAct.getSelectedCabTypeId());

        if (mainAct.getPickUpLocation() != null) {
            parameters.put("StartLatitude", "" + mainAct.getPickUpLocation().getLatitude());
            parameters.put("EndLongitude", "" + mainAct.getPickUpLocation().getLongitude());
        }

        if (mainAct.getDestLocLatitude() != null && !mainAct.getDestLocLatitude().equalsIgnoreCase("")) {
            parameters.put("DestLatitude", "" + mainAct.getDestLocLatitude());
            parameters.put("DestLongitude", "" + mainAct.getDestLocLongitude());
        }

        if (mainAct.eFly) {
            parameters.put("iFromStationId", mainAct.iFromStationId);
            parameters.put("iToStationId", mainAct.iToStationId);
            parameters.put("eFly", "Yes");
        }
        if (mainAct.isInterCity && mainAct.isInterCityRoundTrip) {
            parameters.put("eRoundTrip", "Yes");
            parameters.put("eIsInterCity", "Yes");
        }


        if (mainAct.isMultiStopOverEnabled() && mainAct.stopOverPointsList != null && mainAct.stopOverPointsList.size() > 2) {
            JSONArray jaStore = new JSONArray();
            for (int j = 0; j < mainAct.stopOverPointsList.size(); j++) {
                Stop_Over_Points_Data data1 = mainAct.stopOverPointsList.get(j);
                if (data1.isDestination()) {
                    try {
                        JSONObject stopOverPointsObj = new JSONObject();
                        stopOverPointsObj.put("tDAddress", "" + data1.getDestAddress());
                        stopOverPointsObj.put("tDestLatitude", "" + data1.getDestLat());
                        stopOverPointsObj.put("tDestLongitude", "" + data1.getDestLong());
                        jaStore.put(stopOverPointsObj);
                    } catch (Exception e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
            }
            parameters.put("stopoverpoint_arr", jaStore.toString());
        }
        estimateFareTask = ApiHandler.execute(getActContext(), parameters, responseString -> {

            estimateFareTask = null;

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    LBL_CURRENT_PERSON_LIMIT = generalFunc.getJsonValue("person_limit", responseString);

                    JSONArray vehicleTypesArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    String APP_TYPE = generalFunc.getJsonValue("APP_TYPE", userProfileJson);

                    for (int i = 0; i < vehicleTypesArr.length(); i++) {

                        JSONObject obj_temp = generalFunc.getJsonObject(vehicleTypesArr, i);

                        if (distance != null) {

                            String type = mainAct.getCurrentCabGeneralType();
                            if (type.equalsIgnoreCase("rental")) {
                                type = Utils.CabGeneralType_Ride;
                            }

                            if (generalFunc.getJsonValueStr("eType", obj_temp).contains(type)) {

                                if (cabTypeList != null) {
                                    for (int k = 0; k < cabTypeList.size(); k++) {
                                        HashMap<String, String> map = cabTypeList.get(k);

                                        if (map.get("iVehicleTypeId").equalsIgnoreCase(generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp))) {

                                            map.put("MINIMUM_FARE_BID_TAXI", generalFunc.getJsonValueStr("MINIMUM_FARE_BID_TAXI", obj_temp));
                                            map.put("MINIMUM_FARE_BID_TAXI_WITH_SYMBOL", generalFunc.getJsonValueStr("MINIMUM_FARE_BID_TAXI_WITH_SYMBOL", obj_temp));

                                            String totalfare = "";

                                            if (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery_UberX) || (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralTypeRide_Delivery) || (APP_TYPE.equalsIgnoreCase(Utils.CabGeneralType_Ride)))) {
                                                if ((mainAct.isInterCity && mainAct.isInterCityRoundTrip) || (map.get("eRental").equalsIgnoreCase("Yes") && (mainAct.iscubejekRental || mainAct.isRental))) {
                                                    totalfare = generalFunc.getJsonValueStr("eRental_total_fare", obj_temp);
                                                } else {
                                                    totalfare = generalFunc.getJsonValueStr("total_fare", obj_temp);
                                                }
                                            } else {
                                                totalfare = generalFunc.getJsonValueStr("total_fare", obj_temp);
                                            }

                                            // 'Enter Destination later' skipped -> true/false
                                            if (isSkip) {
                                                totalfare = "";
                                            }

                                            if (totalfare != null && !totalfare.equals("")) {
                                                map.put("total_fare", totalfare);
                                                map.put("FinalFare", generalFunc.getJsonValueStr("FinalFare", obj_temp));
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));
                                                map.put("currencySymbol", generalFunc.getJsonValueStr("currencySymbol", obj_temp));
                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                Logger.e("FinalFare", "::" + generalFunc.getJsonValueStr("FinalFare", obj_temp));
                                                for (int g = 0; g < noOfSeats; g++) {
                                                    if (Utils.checkText(generalFunc.getJsonValueStr("poolPrice" + (g + 1), obj_temp))) {
                                                        map.put("poolPrice" + (g + 1), generalFunc.getJsonValueStr("poolPrice" + (g + 1), obj_temp));
                                                    }
                                                }
                                                cabTypeList.set(k, map);

                                            } else {
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                cabTypeList.set(k, map);
                                            }
                                        }

                                    }
                                }

                                if (rentalTypeList != null) {
                                    for (int k = 0; k < rentalTypeList.size(); k++) {
                                        HashMap<String, String> map = rentalTypeList.get(k);

                                        if (/*map.get("vVehicleType").equalsIgnoreCase(generalFunc.getJsonValueStr()("vVehicleType", obj_temp))
                                                && */map.get("iVehicleTypeId").equalsIgnoreCase(generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp))) {

                                            String totalfare = generalFunc.getJsonValueStr("eRental_total_fare", obj_temp);

                                            // 'Enter Destination later' skipped -> true/false
                                            if (isSkip) {
                                                totalfare = "";
                                            }
                                            if (totalfare != null && !totalfare.equals("")) {
                                                map.put("total_fare", totalfare);
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                rentalTypeList.set(k, map);
                                            } else {
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                rentalTypeList.set(k, map);
                                            }
                                        }
                                    }
                                }


                            }
                        } else {


                            if (generalFunc.getJsonValueStr("eType", obj_temp).equalsIgnoreCase(mainAct.getCurrentCabGeneralType())) {

                                if (cabTypeList != null) {


                                    for (int k = 0; k < cabTypeList.size(); k++) {
                                        HashMap<String, String> map = cabTypeList.get(k);

                                        if (mainAct.iscubejekRental || mainAct.isRental) {
                                            if (/*map.get("vVehicleType").equalsIgnoreCase(generalFunc.getJsonValueStr()("vVehicleType", obj_temp))
                                            &&*/ map.get("iVehicleTypeId").equalsIgnoreCase(generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp))) {
                                                String totalfare = generalFunc.getJsonValueStr("eRental_total_fare", obj_temp);
                                                if (totalfare != null && !totalfare.equals("")) {
                                                    map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                    map.put("total_fare", totalfare);
                                                    map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                    rentalTypeList.set(k, map);
                                                } else {
                                                    map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                    map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                    rentalTypeList.set(k, map);
                                                }

                                            }

                                        } else {

                                            if (/*map.get("vVehicleType").equalsIgnoreCase(generalFunc.getJsonValueStr()("vVehicleType", obj_temp))
                                            &&*/ map.get("iVehicleTypeId").equalsIgnoreCase(generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp))) {
                                                map.put("total_fare", "");
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                cabTypeList.set(k, map);
                                            }
                                        }
                                    }
                                }

                                if (rentalTypeList != null) {
                                    for (int k = 0; k < rentalTypeList.size(); k++) {
                                        HashMap<String, String> map = rentalTypeList.get(k);

                                        if (/*map.get("vVehicleType").equalsIgnoreCase(generalFunc.getJsonValueStr()("vVehicleType", obj_temp))
                                                && */map.get("iVehicleTypeId").equalsIgnoreCase(generalFunc.getJsonValueStr("iVehicleTypeId", obj_temp))) {

                                            String totalfare = generalFunc.getJsonValueStr("eRental_total_fare", obj_temp);
                                            if (totalfare != null && !totalfare.equals("")) {
                                                map.put("total_fare", totalfare);
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                rentalTypeList.set(k, map);
                                            } else {
                                                map.put("RESTRICT_PASSENGER_LIMIT_NOTE", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_NOTE", obj_temp));

                                                map.put("eFlatTrip", generalFunc.getJsonValueStr("eFlatTrip", obj_temp));
                                                rentalTypeList.set(k, map);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (adapter != null) {
                        adapter.notifyDataSetChanged();
                    }
                    updateFareDetails();
                }
            }
        });

    }

    private void updateFareDetails() {
        if (cabTypeList != null && cabTypeList.size() > 0) {

            if (cabTypeList.get(selpos).get("total_fare") != null && !cabTypeList.get(selpos).get("total_fare").equalsIgnoreCase("")) {
                String total_fare = generalFunc.convertNumberWithRTL(cabTypeList.get(selpos).get("total_fare"));
                if (design_linear_layout_car_details.getVisibility() == View.VISIBLE) {
                    fareVTxt.setText(total_fare);
                }

                if (poolArea != null && poolArea.getVisibility() == View.VISIBLE) {
                    poolFareTxt.setText(total_fare);
                }

                if (faredialog != null && faredialog.isShowing()) {
                    if (fareDetailBinding != null) {
                        fareDetailBinding.fareVTxt.setText(total_fare);
                    }
                }
            }

        }

    }

    public void openFareDetailsDilaog(final int pos) {
        if (isFirstTime) {
            isFirstTime = false;
            return;
        }

        if (mainAct.isMultiDelivery()) {
            return;
        }
        if (faredialog != null && faredialog.isShowing()) {
            return;
        }
        String vehicleIconPath = CommonUtilities.SERVER_URL + "webimages/icons/VehicleType/";
        String vehicleDefaultIconPath = CommonUtilities.SERVER_URL + "webimages/icons/DefaultImg/";
        faredialog = new BottomSheetDialog(getActContext());

        fareDetailBinding = DailogFaredetailsBinding.inflate(LayoutInflater.from(getContext()));
        View contentView = fareDetailBinding.getRoot();
        faredialog.setContentView(contentView);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(1500);
        View bottomSheetView = faredialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        setCancelable(faredialog, false);

        MButton doneBtn = ((MaterialRippleLayout) faredialog.findViewById(R.id.doneBtn)).getChildView();
        doneBtn.setText(generalFunc.retrieveLangLBl("", "LBL_DONE"));
        doneBtn.setOnClickListener((View.OnClickListener) v -> faredialog.dismiss());

        fareDetailBinding.capacityHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CAPACITY"));
        fareDetailBinding.fareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FARE_TXT"));
        fareDetailBinding.mordetailsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MORE_DETAILS"));

        if (mainAct.isFixFare || mainAct.eFly) {
            fareDetailBinding.farenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENERAL_NOTE_FLAT_FARE_EST"));
        } else {
            fareDetailBinding.farenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_GENERAL_NOTE_FARE_EST"));
        }

        if (cabTypeList.get(pos).get("eRental") != null && cabTypeList.get(pos).get("eRental").equalsIgnoreCase("Yes") || (mainAct.isInterCity && mainAct.isInterCityRoundTrip)) {
            fareDetailBinding.mordetailsTxt.setVisibility(View.GONE);
            fareDetailBinding.morwArrow.setVisibility(View.GONE);
            fareDetailBinding.pkgMsgTxt.setVisibility(View.VISIBLE);
            fareDetailBinding.fareHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PKG_STARTING_AT"));

            if (mainAct.eShowOnlyMoto != null && mainAct.eShowOnlyMoto.equalsIgnoreCase("Yes")) {
                fareDetailBinding.pkgMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_MOTO_PKG_MSG"));
            } else if (mainAct.eFly) {
                fareDetailBinding.pkgMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_AIRCRAFT_PKG_MSG"));
            } else if (mainAct.isInterCity && mainAct.isInterCityRoundTrip) {
                fareDetailBinding.pkgMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_GENERAL_NOTE_TXT"));
            } else {
                fareDetailBinding.pkgMsgTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_PKG_MSG"));
            }
            if (mainAct.isInterCity && mainAct.isInterCityRoundTrip) {
                fareDetailBinding.farenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_FARE_VARY_NOTE_TXT"));
            } else {
                fareDetailBinding.farenoteTxt.setText(generalFunc.retrieveLangLBl("", mainAct.eFly ? "LBL_RENT_AIRCRAFT_PKG_DETAILS" : "LBL_RENT_PKG_DETAILS"));
            }
        } else {
            fareDetailBinding.pkgMsgTxt.setVisibility(View.GONE);
        }

        if (cabTypeList.get(pos).get("isTaxiBid") != null && cabTypeList.get(pos).get("isTaxiBid").equalsIgnoreCase("Yes")) {
            fareDetailBinding.mordetailsTxt.setVisibility(View.GONE);
            fareDetailBinding.morwArrow.setVisibility(View.GONE);
            fareDetailBinding.fareHTxt.setText(generalFunc.retrieveLangLBl("Average Fare", "LBL_AVERAGE_FARE_TAXI_BID_TEXT"));
            fareDetailBinding.farenoteTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MINIMUM_CURRENT_FARE_TAXI_BID_TEXT") + " " + generalFunc.convertNumberWithRTL(cabTypeList.get(pos).get("MINIMUM_FARE_BID_TAXI_WITH_SYMBOL")));
        }

        if (!cabTypeList.get(pos).get("eRental").equals("") && cabTypeList.get(pos).get("eRental").equals("Yes") || (mainAct.isInterCity && mainAct.isInterCityRoundTrip)) {
            fareDetailBinding.carTypeTitle.setText(cabTypeList.get(pos).get("vRentalVehicleTypeName"));
        } else {
            fareDetailBinding.carTypeTitle.setText(cabTypeList.get(pos).get("vVehicleType"));
        }
        if (cabTypeList.get(pos).get("total_fare") != null && !cabTypeList.get(pos).get("total_fare").equalsIgnoreCase("")) {
            fareDetailBinding.fareVTxt.setText(generalFunc.convertNumberWithRTL(cabTypeList.get(pos).get("total_fare")));
        } else {
            fareDetailBinding.fareVTxt.setText("--");
        }
        if (mainAct.getCurrentCabGeneralType().equals(Utils.CabGeneralType_Ride)) {
            fareDetailBinding.capacityVTxt.setText(generalFunc.convertNumberWithRTL(cabTypeList.get(pos).get("iPersonSize")) + " " + generalFunc.retrieveLangLBl("", "LBL_PEOPLE_TXT"));
            fareDetailBinding.capacityArea.setVisibility(View.VISIBLE);

        } else {
            fareDetailBinding.capacityVTxt.setText("---");
            fareDetailBinding.capacityArea.setVisibility(View.GONE);
        }

        String imgName = cabTypeList.get(pos).get("vLogo1");
        if (!Utils.checkText(imgName)) {
            fareDetailBinding.imagecar.setImageDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.ic_vehicle_placeholder));
        } else {
            imgName = vehicleIconPath + cabTypeList.get(pos).get("iVehicleTypeId") + "/android/" + "xxxhdpi_" + cabTypeList.get(pos).get("vLogo1");
            new LoadImage.builder(LoadImage.bind(imgName), fareDetailBinding.imagecar).setErrorImagePath(R.drawable.ic_vehicle_placeholder).setPlaceholderImagePath(R.drawable.ic_vehicle_placeholder).build();
        }

        fareDetailBinding.mordetailslayout.setOnClickListener((View.OnClickListener) v -> {
            Bundle bn = new Bundle();
            bn.putString("SelectedCar", cabTypeList.get(pos).get("iVehicleTypeId"));
            bn.putString("iUserId", generalFunc.getMemberId());
            bn.putString("distance", distance);
            bn.putString("time", time);
            if (cabTypeList.get(pos).get("eRental").equalsIgnoreCase("Yes") || (mainAct.isInterCity && mainAct.isInterCityRoundTrip)) {
                bn.putString("vVehicleType", cabTypeList.get(pos).get("vRentalVehicleTypeName"));
            } else {
                bn.putString("vVehicleType", cabTypeList.get(pos).get("vVehicleType"));
            }
            bn.putBoolean("isSkip", isSkip);
            if (mainAct.getPickUpLocation() != null) {
                bn.putString("picupLat", mainAct.getPickUpLocation().getLatitude() + "");
                bn.putString("pickUpLong", mainAct.getPickUpLocation().getLongitude() + "");
            }
            if (mainAct.destLocation != null) {
                bn.putString("destLat", mainAct.destLocLatitude + "");
                bn.putString("destLong", mainAct.destLocLongitude + "");
            }
            bn.putBoolean("isFixFare", mainAct.isFixFare);

            if (mainAct.eFly) {
                bn.putString("iFromStationId", mainAct.iFromStationId);
                bn.putString("iToStationId", mainAct.iToStationId);
                bn.putString("eFly", "Yes");
            }
            new ActUtils(getActContext()).startActWithData(FareBreakDownActivity.class, bn);
            faredialog.dismiss();
        });
        faredialog.setOnDismissListener(dialog -> {
        });
        LayoutDirection.setLayoutDirection(faredialog);
        faredialog.show();
    }

    public void setCancelable(Dialog dialogview, boolean cancelable) {
        final Dialog dialog = dialogview;
        View touchOutsideView = dialog.getWindow().getDecorView().findViewById(R.id.touch_outside);
        View bottomSheetView = dialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);

        if (cancelable) {
            touchOutsideView.setOnClickListener(v -> {
                if (dialog.isShowing()) {
                    dialog.cancel();
                }
            });
            BottomSheetBehavior.from(bottomSheetView).setHideable(true);
        } else {
            touchOutsideView.setOnClickListener(null);
            BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();

        releseInstances();
    }

    private void releseInstances() {
        Utils.hideKeyboard(getActContext());
        if (estimateFareTask != null) {
            estimateFareTask.cancel(true);
            estimateFareTask = null;
        }
    }

    public void Checkpickupdropoffrestriction() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "Checkpickupdropoffrestriction");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("PickUpLatitude", "" + mainAct.getPickUpLocation().getLatitude());
        parameters.put("PickUpLongitude", "" + mainAct.getPickUpLocation().getLongitude());
        parameters.put("DestLatitude", mainAct.getDestLocLatitude());
        parameters.put("DestLongitude", mainAct.getDestLocLongitude());
        parameters.put("UserType", Utils.userType);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            String message = generalFunc.getJsonValue(Utils.message_str, responseString);
            if (responseString != null && !responseString.equals("")) {
                if (generalFunc.getJsonValue("Action", responseString).equalsIgnoreCase("0")) {
                    if (message.equalsIgnoreCase("LBL_DROP_LOCATION_NOT_ALLOW")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DROP_LOCATION_NOT_ALLOW"));
                    } else if (message.equalsIgnoreCase("LBL_PICKUP_LOCATION_NOT_ALLOW")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PICKUP_LOCATION_NOT_ALLOW"));
                    }
                } else if (generalFunc.getJsonValue("Action", responseString).equalsIgnoreCase("1")) {


                    //need to manage here for covid

                    mainAct.continueDeliveryProcess();

                }

            } else {
                generalFunc.showError();
            }
        });

    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        releseInstances();
    }

    String ShowAdjustTripBtn;
    String ShowPayNow;
    String ShowContactUsBtn;

    public void outstandingDialog(/*boolean isReqNow*/String responseString, Intent data) {

        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dailog_outstanding, null);

        final MTextView outStandingTitle = dialogView.findViewById(R.id.outStandingTitle);
        final MTextView outStandingValue = dialogView.findViewById(R.id.outStandingValue);
        final MTextView cardtitleTxt = dialogView.findViewById(R.id.cardtitleTxt);
        final MTextView adjustTitleTxt = dialogView.findViewById(R.id.adjustTitleTxt);

        final LinearLayout cardArea = dialogView.findViewById(R.id.cardArea);
        final LinearLayout adjustarea = dialogView.findViewById(R.id.adjustarea);

        final MTextView adjustSubTitleTxt = dialogView.findViewById(R.id.adjustSubTitleTxt);
        final MTextView adjustTripMessageTxt = dialogView.findViewById(R.id.adjustTripMessageTxt);
        if (generalFunc.isRTLmode()) {
            (dialogView.findViewById(R.id.imgCardPayNow)).setRotationY(180);
            (dialogView.findViewById(R.id.imgAdjustInTrip)).setRotationY(180);
        }

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setBackgroundTintList(ColorStateList.valueOf(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1)));
        btn_type2.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        outStandingTitle.setText(generalFunc.retrieveLangLBl("", "LBL_OUTSTANDING_AMOUNT_TXT"));
        String type = mainAct.getCurrentCabGeneralType();

        adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_OUT_AMT_RIDE_TXT"));
        adjustSubTitleTxt.setText(generalFunc.retrieveLangLBl("Outstanding amount will be added in invoice total amount.", "LBL_OUTSTANDING_AMOUNT_ADDED_INVOICE_NOTE"));
        adjustTripMessageTxt.setText(generalFunc.getJsonValue("outstanding_restriction_label", responseString));
        if (type.equalsIgnoreCase("Ride")) {
            adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_IN_YOUR_BOOKING"));
        } else if (type.equalsIgnoreCase("Deliver")) {
            adjustTitleTxt.setText(generalFunc.retrieveLangLBl("Adjust in Your trip", "LBL_ADJUST_IN_YOUR_ORDER"));
        }
        outStandingValue.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValue("fOutStandingAmountWithSymbol", responseString)));
        cardtitleTxt.setText(generalFunc.retrieveLangLBl("Pay Now", "LBL_PAY_NOW"));

        ShowAdjustTripBtn = generalFunc.getJsonValue("ShowAdjustTripBtn", responseString);
        ShowAdjustTripBtn = (ShowAdjustTripBtn == null || ShowAdjustTripBtn.isEmpty()) ? "No" : ShowAdjustTripBtn;
        ShowPayNow = generalFunc.getJsonValue("ShowPayNow", responseString);
        ShowContactUsBtn = generalFunc.getJsonValue("ShowContactUsBtn", responseString);
        ShowContactUsBtn = (ShowContactUsBtn == null || ShowContactUsBtn.isEmpty()) ? "No" : ShowContactUsBtn;
        ShowPayNow = (ShowPayNow == null || ShowPayNow.isEmpty()) ? "No" : ShowPayNow;


        if (ShowPayNow.equalsIgnoreCase("Yes") && ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.VISIBLE);
        } else if (ShowPayNow.equalsIgnoreCase("Yes")) {
            cardArea.setVisibility(View.VISIBLE);
            adjustarea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
        } else if (ShowAdjustTripBtn.equalsIgnoreCase("Yes")) {
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            }*/
            adjustarea.setVisibility(View.VISIBLE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
        } else {
            adjustarea.setVisibility(View.GONE);
            cardArea.setVisibility(View.GONE);
            dialogView.findViewById(R.id.cashAreaSeparation).setVisibility(View.GONE);
            dialogView.findViewById(R.id.adjustarea_seperation).setVisibility(View.GONE);
            adjustTripMessageTxt.setVisibility(View.VISIBLE);
            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                adjustTripMessageTxt.setText(outstanding_restriction_label);
            }
        }
        /*else if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {

            String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                generateAlert.setCancelable(false);
                generateAlert.setBtnClickList(btn_id -> {
                    if (btn_id == 1) {
                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                    }
                });
                generateAlert.setContentMessage("", outstanding_restriction_label);
                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_OK"));
                if (ShowContactUsBtn != null && ShowContactUsBtn.equalsIgnoreCase("Yes")) {
                    generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                }
                generateAlert.showAlertBox();
                return;
            }
        }*/

        final LinearLayout contactUsArea = dialogView.findViewById(R.id.contactUsArea);
        contactUsArea.setVisibility(View.GONE);
        ShowContactUsBtn = generalFunc.getJsonValueStr("ShowContactUsBtn", mainAct.obj_userProfile);
        if (ShowContactUsBtn.equalsIgnoreCase("Yes")) {
            MTextView contactUsTxt = dialogView.findViewById(R.id.contactUsTxt);
            contactUsTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
            contactUsArea.setVisibility(View.VISIBLE);
            contactUsArea.setOnClickListener(v -> new ActUtils(getActContext()).startAct(ContactUsActivity.class));
            /*if (generalFunc.isRTLmode()) {
                (dialogView.findViewById(R.id.contactUsArrow)).setRotationY(180);
            }*/
        }

        cardArea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();

            Bundle bn = new Bundle();
            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + mainAct.getCurrentCabGeneralType() + "&ePaymentType=ChargeOutstandingAmount";


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);


            bn.putString("CouponCode", generalFunc.getJsonValue("PromoCode", getProfilePaymentModel.getProfileInfo()).toString());
            bn.putString("eType", mainAct.getCurrentCabGeneralType());
            bn.putBoolean("eFly", mainAct.eFly);


            if (sourceLocation != null) {
                bn.putString("vSourceLatitude", String.valueOf(sourceLocation.latitude));
                bn.putString("vSourceLongitude", String.valueOf(sourceLocation.longitude));
            }
            if (destLocation != null) {
                bn.putString("vDestLatitude", String.valueOf(destLocation.latitude));
                bn.putString("vDestLongitude", String.valueOf(destLocation.longitude));
            }
            bn.putString("eTakeAway", "No");
            bn.putString("isTaxiBid", mainAct.isTaxiBid ? "Yes" : "No");

            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);
        });

        adjustarea.setOnClickListener(v -> {
            outstanding_dialog.dismiss();
            /*String outstanding_restriction_label = generalFunc.getJsonValue("outstanding_restriction_label", responseString);
            if (outstanding_restriction_label != null && !outstanding_restriction_label.isEmpty()) {
                generalFunc.showGeneralMessage("", outstanding_restriction_label);
                return;
            }*/

            mainAct.continueSurgeChargeExecution(responseString, data);
        });

        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);

        btn_type2.setOnClickListener(v -> outstanding_dialog.dismiss());

        builder.setView(dialogView);
        outstanding_dialog = builder.create();
        LayoutDirection.setLayoutDirection(outstanding_dialog);
        if (generalFunc.isRTLmode()) {
            dialogView.findViewById(R.id.cardimagearrow).setRotationY(180);
            dialogView.findViewById(R.id.adjustimagearrow).setRotationY(180);
        }
        outstanding_dialog.setCancelable(false);
        outstanding_dialog.getWindow().setBackgroundDrawable(ContextCompat.getDrawable(getActContext(), R.drawable.all_roundcurve_card));
        outstanding_dialog.show();
    }

    @Override
    public void onItemClick(int position, String selectedType) {

        seatsSelectpos = position;

        if (cabTypeList.get(selpos).containsKey("poolPrice" + (position + 1))) {
            poolFareTxt.setText(cabTypeList.get(selpos).get("poolPrice" + (position + 1)));
        } else {
            double totalFare = GeneralFunctions.parseDoubleValue(0, cabTypeList.get(selpos).get("FinalFare"));
            double seatVal = GeneralFunctions.parseDoubleValue(1, poolSeatsList.get(position));

            if (seatVal > 1) {
                double res = (totalFare / 100.0f) * GeneralFunctions.parseDoubleValue(0, mainAct.cabTypesArrList.get(selpos).get("fPoolPercentage"));
                res = res + totalFare;
                // poolFareTxt.setText(cabTypeList.get(selpos).get("currencySymbol") + " " + String.format("%.2f", (float) res));
                DecimalFormat formatter = new DecimalFormat("#,###,###.00");
                poolFareTxt.setText(cabTypeList.get(selpos).get("currencySymbol") + " " + formatter.format(res));
            } else {
                poolFareTxt.setText(cabTypeList.get(selpos).get("total_fare"));
            }
        }
        if (seatsSelectionAdapter != null) {
            seatsSelectionAdapter.setSelectedSeat(seatsSelectpos);
            seatsSelectionAdapter.notifyDataSetChanged();
        }
    }

    public void hideRentalArea() {
        rentalPkg.setVisibility(View.GONE);
        rentalarea.setVisibility(View.GONE);
    }

    boolean isGoogle = false;


    public void setWaypoints(JSONArray waypoint_order) {


        for (int l = 0; l < waypoint_order.length(); l++) {

            Logger.d("WayPointsArray", "::" + generalFunc.getJsonValue(waypoint_order, l));
            int ordering = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValue(waypoint_order, l).toString());
            Logger.d("Api", "waypoint_order sequence : ordering" + ordering);
            wayPointslist.get(l).setSequenceId(ordering);
            destPointlist.get(0).setSequenceId(waypoint_order.length());


            LatLng latLng = wayPointslist.get(l).getDestLatLong();

            Logger.d("Route_Parser", "else");

            MarkerOptions dest_dot_option = new MarkerOptions().position(latLng).icon(VectorUtils.vectorToBitmap(getActContext(), R.drawable.dot_filled_new, 0));
            Marker dest_marker = getMap().addMarker(dest_dot_option);
            builder.include(dest_marker.getPosition());

        }

        finalPointlist.addAll(wayPointslist);
        finalPointlist.addAll(destPointlist);

        if (finalPointlist.size() > 0) {
            Collections.sort(finalPointlist, new StopOverComparator("SequenceId"));
        }

    }


    @Override
    public void onClickView(View view) {
        int i = view.getId();
        Utils.hideKeyboard(getActContext());
        if (i == ride_now_btn.getId()) {

            if (isRouteFail || mProgressBar.getVisibility() == View.VISIBLE) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            if (!mainAct.cabRquestType.equals(Utils.CabReqType_Later)) {
                if ((mainAct.currentLoadedDriverList != null && mainAct.currentLoadedDriverList.size() < 1) || mainAct.currentLoadedDriverList == null || (cabTypeList != null && cabTypeList.size() < 1) || cabTypeList == null) {

                    String messageLabel = "LBL_NO_CARS_AVAIL_IN_TYPE";
                    if (mainAct.isMultiDelivery()) {
                        messageLabel = "LBL_NO_CARRIERS_AVAIL_TXT";
                    }
                    buildNoCabMessage(generalFunc.retrieveLangLBl("", messageLabel), generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                    return;
                }
            }

            if (cabTypeList != null && cabTypeList.size() == 0) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_NO_CARS"));
                return;
            }

            if (cabTypeList != null && cabTypeList.get(selpos).get("ePoolStatus").equalsIgnoreCase("Yes") && !mainAct.isDestinationAdded) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DESTINATION_REQUIRED_POOL"));
                return;

            }


            if (isRouteFail && !mainAct.isMultiDelivery()) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                return;
            }

            if (!mainAct.cabRquestType.equals(Utils.CabReqType_Later)) {
                mainAct.setCabReqType(Utils.CabReqType_Now);
            }


            if (!isCardValidated && APP_PAYMENT_MODE.equalsIgnoreCase("Card") && SYSTEM_PAYMENT_FLOW.equalsIgnoreCase("Method-1")) {
                isCardnowselcted = true;
                isCardlaterselcted = false;

                return;
            }


            if (mainAct.isDeliver(mainAct.getCurrentCabGeneralType())) {
                if (!mainAct.getDestinationStatus() && !mainAct.isMultiDelivery()) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Please add your destination location " + "to deliver your package.", "LBL_ADD_DEST_MSG_DELIVER_ITEM"));
                    return;
                }

                String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);

                if ((generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_DELIVERY", userProfileJson).equalsIgnoreCase("Yes") && ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes")) && !mainAct.isMultiDelivery()) {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isDeliverNow", true);
                    bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                    bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                    new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                    ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);

                } else {
                    Checkpickupdropoffrestriction();
                }

                return;
            }

            if (cabTypeList.get(selpos).get("ePoolStatus").equalsIgnoreCase("Yes")) {
                rentalarea.setVisibility(View.GONE);
                poolArea.setVisibility(View.VISIBLE);
                mainAct.mainHeaderFrag.backBtn.setVisibility(View.GONE);
                requestPayArea.setVisibility(View.GONE);
                mainAct.setMarginToLocationButton(true, getResources().getDimensionPixelSize(R.dimen._35sdp));
                reqpayAreaLastVisibleState = false;
                currentState = BottomSheetBehavior.STATE_COLLAPSED;
                if (design_linear_layout_car_details.getVisibility() == View.VISIBLE) {
                    currentState = BottomSheetBehavior.STATE_EXPANDED;
                    design_linear_layout_car_details.setVisibility(View.GONE);
                    carDetailsDialogLastVisibleState = false;
                    mainAct.cabBottomSheetBehavior.setPeekHeight(tempMeasuredHeight);
                }
                mainContentArea.setVisibility(View.GONE);
                mainAct.enableDisableBottomSheetDrag(false, false);
                new Handler().postDelayed(() -> {
                    mainAct.setMapPaddingForCab(0, 0, 0, poolArea.getMeasuredHeight(), true);
                    mainAct.lastCabBottomPadding = poolArea.getMeasuredHeight();
                }, 50);


                double totalFare = GeneralFunctions.parseDoubleValue(0, cabTypeList.get(selpos).get("FinalFare"));
                double seatVal = GeneralFunctions.parseDoubleValue(1, poolSeatsList.get(seatsSelectpos));
                if (seatVal > 1) {
                    if (cabTypeList.get(selpos).containsKey("poolPrice" + (selpos + 1))) {
                        poolFareTxt.setText(cabTypeList.get(selpos).get("poolPrice" + (selpos + 1)));
                    } else {
                        double res = (totalFare / 100.0f) * GeneralFunctions.parseDoubleValue(0, mainAct.cabTypesArrList.get(selpos).get("fPoolPercentage"));
                        res = res + totalFare;
                        poolFareTxt.setText(cabTypeList.get(selpos).get("currencySymbol") + " " + String.format(Locale.ENGLISH, "%.2f", (float) res));

                    }
                } else {
                    poolFareTxt.setText(cabTypeList.get(selpos).get("total_fare"));
                }

                return;
            }

            if ((mainAct.isInterCity && mainAct.isInterCityRoundTrip) || (cabTypeList.get(selpos).get("eRental") != null && !cabTypeList.get(selpos).get("eRental").equalsIgnoreCase("") && cabTypeList.get(selpos).get("eRental").equalsIgnoreCase("Yes"))) {

                String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);
                String ENABLE_RESTRICT_PASSENGER_LIMIT = generalFunc.getJsonValue("ENABLE_RESTRICT_PASSENGER_LIMIT", userProfileJson);


                if (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_RIDE", userProfileJson).equalsIgnoreCase("Yes")) {
                    if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("No") && ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") && !mainAct.isDeliver(RideDeliveryType)) {
                        showPassengerLimitDialog(rentalTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE"), "ContinuePickup");
                    } else if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes")) {
                        Bundle bn = new Bundle();
                        bn.putBoolean("isRideNow", !mainAct.getCabReqType().equals(Utils.CabReqType_Later));
                        bn.putBoolean("isRental", true);
                        bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                        bn.putString("LBL_CURRENT_PERSON_LIMIT", ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") ? rentalTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE") : "");
                        new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                        ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);

                        return;

                    } else {
                        Bundle bn = new Bundle();
                        bn.putString("address", mainAct.pickUpLocationAddress);
                        bn.putString("vVehicleType", cabTypeList.get(selpos).get("vRentalVehicleTypeName"));
                        bn.putString("iVehicleTypeId", cabTypeList.get(selpos).get("iVehicleTypeId"));
                        bn.putString("vLogo", cabTypeList.get(selpos).get("vLogo1"));
                        bn.putString("eta", etaTxt.getText().toString());
                        bn.putString("eMoto", mainAct.eShowOnlyMoto);
                        bn.putBoolean("eFly", mainAct.eFly);

                        //InterCity Stuffs
                        bn.putString("sLat", mainAct.pickup_latitude);
                        bn.putString("sLong", mainAct.pickup_longitude);
                        bn.putString("eLat", String.valueOf(mainAct.destLocation.getLatitude()));
                        bn.putString("eLong", String.valueOf(mainAct.destLocation.getLongitude()));
                        bn.putString("pickupDT", Utils.checkText(mainAct.intercityPickupDT) ? mainAct.intercityPickupDT : "");
                        bn.putString("dropoffDT", Utils.checkText(mainAct.intecityDropoffDT) ? mainAct.intecityDropoffDT : "");
                        bn.putBoolean("isInterCity", mainAct.isInterCity);

                        new ActUtils(getActContext()).startActForResult(RentalDetailsActivity.class, bn, RENTAL_REQ_CODE);
                        return;

                    }

                } else {
                    Bundle bn = new Bundle();
                    bn.putString("address", mainAct.pickUpLocationAddress);
                    bn.putString("vVehicleType", cabTypeList.get(selpos).get("vRentalVehicleTypeName"));
                    bn.putString("iVehicleTypeId", cabTypeList.get(selpos).get("iVehicleTypeId"));
                    bn.putString("vLogo", cabTypeList.get(selpos).get("vLogo1"));
                    bn.putString("eta", etaTxt.getText().toString());
                    bn.putString("eMoto", mainAct.eShowOnlyMoto);
                    bn.putBoolean("eFly", mainAct.eFly);

                    //InterCity Stuffs
                    bn.putString("sLat", mainAct.pickup_latitude);
                    bn.putString("sLong", mainAct.pickup_longitude);

                    bn.putString("eLat", String.valueOf(mainAct.getDestLocLatitude()));
                    bn.putString("eLong", String.valueOf(mainAct.getDestLocLongitude()));

//                    bn.putString("eLat", String.valueOf(mainAct.destLocation.getLatitude()));
//                    bn.putString("eLong", String.valueOf(mainAct.destLocation.getLongitude()));

                    bn.putString("pickupDT", Utils.checkText(mainAct.intercityPickupDT) ? mainAct.intercityPickupDT : "");
                    bn.putString("dropoffDT", Utils.checkText(mainAct.intecityDropoffDT) ? mainAct.intecityDropoffDT : "");
                    bn.putBoolean("isInterCity", mainAct.isInterCity);


                    new ActUtils(getActContext()).startActForResult(RentalDetailsActivity.class, bn, RENTAL_REQ_CODE);
                    return;


                }


            }

            String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);
            String ENABLE_RESTRICT_PASSENGER_LIMIT = generalFunc.getJsonValue("ENABLE_RESTRICT_PASSENGER_LIMIT", userProfileJson);

            if (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_RIDE", userProfileJson).equalsIgnoreCase("Yes") || (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_DELIVERY", userProfileJson).equalsIgnoreCase("Yes") || (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_UFX", userProfileJson).equalsIgnoreCase("Yes")))) {

                if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("No") && ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") && !mainAct.isDeliver(RideDeliveryType)) {
                    showPassengerLimitDialog(cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE"), "ContinuePickup");
                } else if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes")) {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isRideNow", !mainAct.getCabReqType().equals(Utils.CabReqType_Later));
                    bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                    bn.putString("LBL_CURRENT_PERSON_LIMIT", ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") ? cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE") : "");
                    new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                    ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);


                } else {
                    if (mainAct.getCabReqType().equals(Utils.CabReqType_Later)) {
                        mainAct.setRideSchedule();
                    } else {
                        mainAct.continuePickUpProcess();
                    }

                }

            } else {
                if (mainAct.getCabReqType().equals(Utils.CabReqType_Later)) {
                    mainAct.setRideSchedule();
                } else {
                    mainAct.continuePickUpProcess();
                }

            }
        } else if (i == img_ridelater.getId()) {
            try {

                if (mainAct.stopOverPointsList.size() > 2) {
                    generalFunc.showMessage(carTypeRecyclerView, generalFunc.retrieveLangLBl("", "LBL_REMOVE_MULTI_STOP_OVER_TXT"));
                    return;
                }


                if (mProgressBar.getVisibility() == View.VISIBLE) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                    return;
                }


                if (!cabTypeList.get(selpos).get("eRental").equalsIgnoreCase("Yes")) {
                    if (mainAct.destAddress == null || mainAct.destAddress.equalsIgnoreCase("")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Destination is required to create scheduled booking.", "LBL_DEST_REQ_FOR_LATER"));

                        return;
                    }
                }

                if (isRouteFail && !mainAct.isMultiDelivery()) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Route not found", "LBL_DEST_ROUTE_NOT_FOUND"));
                    return;
                }
                if (cabTypeList.size() > 0) {
                    if (!isCardValidated && APP_PAYMENT_MODE.equalsIgnoreCase("Card") && !mainAct.isMultiDelivery()) {
                        isCardlaterselcted = true;
                        isCardnowselcted = false;

                        return;
                    }

                    String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);
                    String ENABLE_RESTRICT_PASSENGER_LIMIT = generalFunc.getJsonValue("ENABLE_RESTRICT_PASSENGER_LIMIT", userProfileJson);

                    if (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_RIDE", userProfileJson).equalsIgnoreCase("Yes")) {
                        if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("No") && ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") && !mainAct.isDeliver(RideDeliveryType)) {
                            showPassengerLimitDialog(cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE"), "chooseDateTime");
                        } else if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_RIDE", userProfileJson).equalsIgnoreCase("Yes") && !mainAct.isDeliver(RideDeliveryType)) {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isRideNow", false);
                            bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                            bn.putString("LBL_CURRENT_PERSON_LIMIT", ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") ? cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE") : "");
                            new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                            ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
                        } else if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes") && generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_DELIVERY", userProfileJson).equalsIgnoreCase("Yes") && mainAct.isDeliver(RideDeliveryType)) {
                            Bundle bn = new Bundle();
                            bn.putBoolean("isDeliverLater", false);
                            bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                            bn.putString("LBL_CURRENT_PERSON_LIMIT", "");
                            new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                            ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);
                        } else {
                            mainAct.chooseDateTime();

                        }
                    } else {
                        mainAct.chooseDateTime();
                    }
                }
            } catch (Exception e) {

            }
        } else if (i == R.id.organizationArea) {
            paymentArea.performClick();
        } else if (i == R.id.arrowImg) {
            paymentArea.performClick();
        } else if (i == R.id.paymentArea) {
            Bundle bn = new Bundle();

            String url = generalFunc.getJsonValue("PAYMENT_MODE_URL", userProfileJson) + "&eType=" + mainAct.getCurrentCabGeneralType();


            url = url + "&tSessionId=" + (generalFunc.getMemberId().equals("") ? "" : generalFunc.retrieveValue(Utils.SESSION_ID_KEY));
            url = url + "&GeneralUserType=" + Utils.app_type;
            url = url + "&GeneralMemberId=" + generalFunc.getMemberId();
            url = url + "&ePaymentOption=" + "Card";
            url = url + "&vPayMethod=" + "Instant";
            url = url + "&SYSTEM_TYPE=" + "APP";
            url = url + "&vCurrentTime=" + generalFunc.getCurrentDateHourMin();


            bn.putString("url", url);
            bn.putBoolean("handleResponse", true);
            bn.putBoolean("isBack", false);

            bn.putString("CouponCode", generalFunc.getJsonValue("PromoCode", getProfilePaymentModel.getProfileInfo()).toString());
            bn.putString("eType", mainAct.getCurrentCabGeneralType());
            bn.putBoolean("eFly", mainAct.eFly);


            if (sourceLocation != null) {
                bn.putString("vSourceLatitude", String.valueOf(sourceLocation.latitude));
                bn.putString("vSourceLongitude", String.valueOf(sourceLocation.longitude));
            }
            if (destLocation != null) {
                bn.putString("vDestLatitude", String.valueOf(destLocation.latitude));
                bn.putString("vDestLongitude", String.valueOf(destLocation.longitude));
            }
            bn.putString("eTakeAway", "No");
            bn.putString("isTaxiBid", mainAct.isTaxiBid ? "Yes" : "No");

            new ActUtils(getActContext()).startActForResult(PaymentWebviewActivity.class, bn, WEBVIEWPAYMENT);

        } else if (i == R.id.rentalBackImage) {

            mainAct.isRental = false;
            mainAct.iscubejekRental = false;
            isRentalClick = true;
            if (mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.checkAvailableCabs();
            }
            selpos = 0;
            iRentalPackageId = "";
            lstSelectpos = 0;
            cabTypeList = (ArrayList<HashMap<String, String>>) tempCabTypeList.clone();
            mainAct.setCabTypeList(cabTypeList);
            tempCabTypeList.clear();
            tempCabTypeList = (ArrayList<HashMap<String, String>>) cabTypeList.clone();
            isRental = false;
            if (cabTypeList.size() > 0) {
                adapter.setSelectedVehicleTypeId(cabTypeList.get(0).get("iVehicleTypeId"));
                mainAct.selectedCabTypeId = cabTypeList.get(0).get("iVehicleTypeId");
                adapter.setRentalItem(cabTypeList);
                mainAct.changeCabType(mainAct.selectedCabTypeId);
                adapter.notifyDataSetChanged();
            }
            rentalBackImage.setVisibility(View.GONE);
            rentalPkgDesc.setVisibility(View.VISIBLE);
            scrollPosition(carTypeRecyclerView, selpos);
            manageTitleForVerticalCaRList();


            rentalPkg.setVisibility(View.VISIBLE);
            rentalarea.setVisibility(View.VISIBLE);

            showBookingLaterArea();
            tempMeasuredHeight += rentalAreaHeight;
            mainAct.setPanelHeight(tempMeasuredHeight);
            mainAct.setMarginToLocationButton(true, mainAct.lastUserLocationMargin);
        } else if (i == R.id.rentalPkg) {


            mainAct.isRental = true;
            mainAct.iscubejekRental = true;
            isRentalClick = true;

            if (mainAct.loadAvailCabs != null) {
                mainAct.loadAvailCabs.checkAvailableCabs();
            }

            selpos = 0;
            iRentalPackageId = "";
            lstSelectpos = 1;
            tempCabTypeList.clear();
            tempCabTypeList = (ArrayList<HashMap<String, String>>) cabTypeList.clone();
            cabTypeList.clear();
            cabTypeList = (ArrayList<HashMap<String, String>>) rentalTypeList.clone();
            adapter.setRentalItem(cabTypeList);
            isRental = true;
            if (cabTypeList.size() > 0) {
                adapter.setSelectedVehicleTypeId(cabTypeList.get(0).get("iVehicleTypeId"));
                mainAct.selectedCabTypeId = cabTypeList.get(0).get("iVehicleTypeId");
                mainAct.changeCabType(mainAct.selectedCabTypeId);
                adapter.notifyDataSetChanged();
            }
            manageTitleForVerticalCaRList();
            rentalPkgDesc.setVisibility(View.VISIBLE);
            rentalBackImage.setVisibility(View.VISIBLE);
            rentalPkg.setVisibility(View.GONE);
            rentalarea.setVisibility(View.GONE);
            showRideLaterBtn(false);
            tempMeasuredHeight -= rentalAreaHeight;
            mainAct.setPanelHeight(tempMeasuredHeight);
            if (adapter.getItemCount() > 0) {
                scrollPosition(carTypeRecyclerView, selpos);
            }
        } else if (i == R.id.rentPkgImage) {
            rentalPkg.performClick();
        } else if (i == R.id.rentalArea2) {
            isRentalDesignClick = true;
            mainAct.setMapPaddingGeneral();
            animateCarView(View.GONE);
        } else if (i == R.id.poolBackImage) {
            if (rentalTypeList != null && rentalTypeList.size() > 0 && !mainAct.iscubejekRental) {
                rentalarea.setVisibility(View.VISIBLE);
            }
            mainAct.enableDisableBottomSheetDrag(true, false);
            mainAct.setMarginToLocationButton(true, mainAct.lastUserLocationMargin);
            poolArea.setVisibility(View.GONE);
            requestPayArea.setVisibility(View.VISIBLE);
            reqpayAreaLastVisibleState = true;
            mainAct.mainHeaderFrag.backBtn.setVisibility(View.VISIBLE);
            mainContentArea.setVisibility(View.VISIBLE);
            if (currentState != BottomSheetBehavior.STATE_COLLAPSED) {
                if (tempState == BottomSheetBehavior.STATE_COLLAPSED) {
                    mainAct.cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);
                }
            }
            if (carDetailsDialogLastVisibleState) {
                design_linear_layout_car_details.setVisibility(View.VISIBLE);
            }
            mainAct.setMapPaddingGeneral();

            if (seatsSelectionAdapter != null) {
                seatsSelectpos = 0;
                seatsSelectionAdapter.setSelectedSeat(seatsSelectpos);
                seatsSelectionAdapter.notifyDataSetChanged();
                if (cabTypeList != null && cabTypeList.get(selpos).get("total_fare") != null && !cabTypeList.get(selpos).get("total_fare").equalsIgnoreCase("")) {
                    poolFareTxt.setText(cabTypeList.get(selpos).get("total_fare"));
                }

            }
        } else if (i == confirm_seats_btn.getId()) {
            String ePoolStatus = cabTypeList.get(selpos).get("ePoolStatus");

            if (ePoolStatus.equalsIgnoreCase("Yes") && mainAct.stopOverPointsList.size() > 2) {
                generalFunc.showMessage(carTypeRecyclerView, generalFunc.retrieveLangLBl("", "LBL_REMOVE_MULTI_STOP_OVER_TXT"));
                return;
            }
            String ENABLE_SAFETY_CHECKLIST = generalFunc.getJsonValue("ENABLE_SAFETY_CHECKLIST", userProfileJson);
            String ENABLE_RESTRICT_PASSENGER_LIMIT = generalFunc.getJsonValue("ENABLE_RESTRICT_PASSENGER_LIMIT", userProfileJson);
            if (generalFunc.getJsonValue("ENABLE_SAFETY_FEATURE_RIDE", userProfileJson).equalsIgnoreCase("Yes")) {
                if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("No") && ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") && !mainAct.isDeliver(RideDeliveryType)) {
                    showPassengerLimitDialog(cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE"), "ContinuePickup");
                } else if (ENABLE_SAFETY_CHECKLIST.equalsIgnoreCase("Yes")) {
                    Bundle bn = new Bundle();
                    bn.putBoolean("isRideNow", !mainAct.getCabReqType().equals(Utils.CabReqType_Later));
                    bn.putString("URL", generalFunc.getJsonValue("RESTRICT_PASSENGER_LIMIT_INFO_URL", userProfileJson));
                    bn.putString("LBL_CURRENT_PERSON_LIMIT", ENABLE_RESTRICT_PASSENGER_LIMIT.equalsIgnoreCase("Yes") ? cabTypeList.get(selpos).get("RESTRICT_PASSENGER_LIMIT_NOTE") : "");
                    new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                    ((Activity) getActContext()).overridePendingTransition(R.anim.bottom_up, R.anim.bottom_down);


                } else {
                    mainAct.continuePickUpProcess();

                }

            } else {
                mainAct.continuePickUpProcess();

            }


        }

    }

    private void manageTitleForVerticalCaRList() {
        if (mainAct.isRental || mainAct.iscubejekRental) {
            rentalPkgDesc.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_PKG_INFO"));
            if (Utils.checkText(mainAct.eShowOnlyMoto) && mainAct.eShowOnlyMoto.equalsIgnoreCase("Yes")) {
                rentalPkgDesc.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_MOTO_PKG_INFO"));
            }
        } else if (mainAct.isMultiDelivery()) {
            rentalPkgDesc.setText(generalFunc.retrieveLangLBl("Choose a Delivery or swipe up for more", "LBL_CHOOSE_DELIVERY_OR_SWIPE"));
        } else if (mainAct.isInterCity && mainAct.isInterCityRoundTrip) {
            rentalPkgDesc.setText(generalFunc.retrieveLangLBl("", "LBL_INTERCITY_CHOOSE_AN_OPTION"));
        } else if (mainAct.eFly) {
            rentalPkgDesc.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_AIRCRAFT_PKG_INFO"));
        } else {
            rentalPkgDesc.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_TRIP_OR_SWIPE"));
        }

    }

    public void setOrganizationName(String name, boolean isOrganization) {
        organizationTxt.setText(name);
        if (!isOrganization) {
            LinearLayout.LayoutParams organizationLayoutParams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            organizationLayoutParams.setMargins(0, 0, 0, -Utils.dpToPx(getActContext(), 5));

            organizationArea.setLayoutParams(organizationLayoutParams);
            organizationTxt.setTypeface(SystemFont.FontStyle.REGULAR.font);
            organizationTxt.setTextColor(Color.parseColor("#6d6d6d"));
            organizationTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 10);
        } else {
            LinearLayout.LayoutParams organizationLayoutParams = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            organizationLayoutParams.setMargins(0, 0, 0, 0);
            organizationArea.setLayoutParams(organizationLayoutParams);

            organizationTxt.setTypeface(SystemFont.FontStyle.MEDIUM.font);
            organizationTxt.setTextColor(Color.parseColor("#2f2f2f"));
            organizationTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);

            payTypeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CASH_TXT"));

            payTypeTxt.setVisibility(View.GONE);

            isCardValidated = true;
            payImgView.setImageResource(R.drawable.ic_business_pay);

        }
    }

    public boolean handleRnetalView() {
        if (cabTypeList.get(selpos).get("eRental") != null && !cabTypeList.get(selpos).get("eRental").equalsIgnoreCase("") && cabTypeList.get(selpos).get("eRental").equalsIgnoreCase("Yes")) {

            Bundle bn = new Bundle();
            bn.putString("address", mainAct.pickUpLocationAddress);
            bn.putString("vVehicleType", cabTypeList.get(selpos).get("vRentalVehicleTypeName"));
            bn.putString("iVehicleTypeId", cabTypeList.get(selpos).get("iVehicleTypeId"));
            bn.putString("vLogo", cabTypeList.get(selpos).get("vLogo1"));
            bn.putString("eta", etaTxt.getText().toString());
            bn.putString("eMoto", mainAct.eShowOnlyMoto);
            bn.putBoolean("eFly", mainAct.eFly);

            new ActUtils(getActContext()).startActForResult(RentalDetailsActivity.class, bn, RENTAL_REQ_CODE);
            return true;


        }
        return false;
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

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == WEBVIEWPAYMENT && resultCode == Activity.RESULT_OK) {

        } else {
            if (data != null && data.getBooleanExtra("isRideNow", false)) {
                Bundle bn = new Bundle();
                bn.putString("address", mainAct.pickUpLocationAddress);
                bn.putString("vVehicleType", cabTypeList.get(selpos).get("vRentalVehicleTypeName"));
                bn.putString("iVehicleTypeId", cabTypeList.get(selpos).get("iVehicleTypeId"));
                bn.putString("vLogo", cabTypeList.get(selpos).get("vLogo1"));
                bn.putString("eta", etaTxt.getText().toString());
                bn.putString("eMoto", mainAct.eShowOnlyMoto);
                bn.putBoolean("eFly", mainAct.eFly);


                new ActUtils(getActContext()).startActForResult(RentalDetailsActivity.class, bn, RENTAL_REQ_CODE);
            } else if (data != null && data.getBooleanExtra("isDeliverNow", false)) {
                Checkpickupdropoffrestriction();
            }
        }


    }

    public void manageisRentalValue() {
        if (rentalarea.getVisibility() == View.VISIBLE || rentalBackImage.getVisibility() == View.VISIBLE) {
            mainAct.isRental = false;
            mainAct.iscubejekRental = false;

        }
    }

    private void showPassengerLimitDialog(String RESTRICT_PASSENGER_LIMIT_NOTE, String type) {

        if (uploadImgAlertDialog != null) {
            uploadImgAlertDialog.cancel();
            uploadImgAlertDialog = null;
        }
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.desgin_passenger_limit, null);
        builder.setView(dialogView);


        final ImageView iamage_source = dialogView.findViewById(R.id.iamage_source);

        final ImageView cancelImg = dialogView.findViewById(R.id.cancelImg);
        final MTextView capacityTxt1 = dialogView.findViewById(R.id.capacityTxt1);
        final MTextView titileTxt = dialogView.findViewById(R.id.titileTxt);

        capacityTxt1.setText(RESTRICT_PASSENGER_LIMIT_NOTE);
        capacityTxt1.setVisibility(View.VISIBLE);

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        btn_type2.setId(Utils.generateViewId());
        titileTxt.setText(generalFunc.retrieveLangLBl("Safety Essential", "LBL_SAFETY_ESSENTIAL_VERIFICATION_TXT"));

        cancelImg.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (uploadImgAlertDialog != null) {
                    uploadImgAlertDialog.dismiss();
                    uploadImgAlertDialog = null;
                }

            }
        });


        btn_type2.setOnClickListener(view -> {

            if (uploadImgAlertDialog != null) {
                uploadImgAlertDialog.cancel();
                uploadImgAlertDialog = null;
            }

            if (type.equalsIgnoreCase("Rental") || mainAct.isRental) {
                Bundle bn = new Bundle();
                bn.putString("address", mainAct.pickUpLocationAddress);
                bn.putString("vVehicleType", cabTypeList.get(selpos).get("vRentalVehicleTypeName"));
                bn.putString("iVehicleTypeId", cabTypeList.get(selpos).get("iVehicleTypeId"));
                bn.putString("vLogo", cabTypeList.get(selpos).get("vLogo1"));
                bn.putString("eta", etaTxt.getText().toString());
                bn.putString("eMoto", mainAct.eShowOnlyMoto);
                bn.putBoolean("eFly", mainAct.eFly);


                new ActUtils(getActContext()).startActForResult(RentalDetailsActivity.class, bn, RENTAL_REQ_CODE);
            } else if (type.equalsIgnoreCase("ContinuePickup")) {
                mainAct.continuePickUpProcess();
            } else if (type.equalsIgnoreCase("chooseDateTime")) {
                mainAct.chooseDateTime();
            }

        });
        uploadImgAlertDialog = builder.create();
        uploadImgAlertDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(uploadImgAlertDialog);
        uploadImgAlertDialog.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        uploadImgAlertDialog.show();
    }

    private GeoMapLoader.GeoMap getMap() {
        return mainAct.getMap();
    }

    public void animateCarView(int visibility) {
        if (visibility == View.VISIBLE) {
            carDetailsDialogLastVisibleState = true;
            mainAct.cabBottomSheetBehavior.setPeekHeight(0, true);
        } else {
            carDetailsDialogLastVisibleState = false;
            mainAct.cabBottomSheetBehavior.setPeekHeight(tempMeasuredHeight, true);
        }
        Animation animation = visibility == View.VISIBLE ? AnimationUtils.loadAnimation(getActContext(), R.anim.slide_up_anim) : AnimationUtils.loadAnimation(getActContext(), R.anim.slide_out_down_anim);
        design_linear_layout_car_details.startAnimation(animation);
        design_linear_layout_car_details.setVisibility(visibility);
        rentalArea2.setVisibility(visibility == View.VISIBLE ? isRental || mainAct.iscubejekRental || mainAct.isInterCity || (mainAct.isMultiStopOverEnabled() && mainAct.stopOverPointsList != null && mainAct.stopOverPointsList.size() > 2) || mainAct.isMultiDelivery() || mainAct.isRidePool || mainAct.isTaxiBid || mainAct.eForMedicalService || rentalAreaHeight <= 0 ? View.GONE : View.VISIBLE : View.VISIBLE);
        if (!mainAct.isMultiDelivery() && !isRentalDesignClick) {
            requestPayArea.animate().translationY(visibility == View.VISIBLE ? 0 : sendRequestArea.getMeasuredHeight() + getActContext().getResources().getDimensionPixelSize(R.dimen._11sdp));
        }
        if (visibility == View.VISIBLE) {
            new Handler().postDelayed(() -> {
                int value = mainAct.isRental || mainAct.iscubejekRental ? requestPayArea.getMeasuredHeight() + design_linear_layout_car_details.getMeasuredHeight() : requestPayArea.getMeasuredHeight() + design_linear_layout_car_details.getMeasuredHeight() - rentalAreaHeight;
                mainAct.setMapPaddingForCab(0, mainAct.isMultiDelivery() ? (int) getResources().getDimensionPixelSize(R.dimen._150sdp) : 0, 0, value, true);
                mainAct.setMarginToLocationButton(true, mainAct.isMultiDelivery() ? value + getResources().getDimensionPixelSize(R.dimen._10sdp) : mainAct.isTaxiBid ? getResources().getDimensionPixelSize(R.dimen._65sdp) : !isRental ? rentalAreaHeight + getResources().getDimensionPixelSize(R.dimen._85sdp) : getResources().getDimensionPixelSize(R.dimen._85sdp));
                if (!mainAct.isMultiDelivery()) {
                    mainAct.lastCabBottomPadding = value;
                }
            }, 50);

        } else {
            mainAct.setMapPaddingForCab(0, mainAct.isMultiDelivery() ? (int) getResources().getDimensionPixelSize(R.dimen._100sdp) : 0, 0, mainAct.lastCabBottomPadding, true);
            mainAct.setMarginToLocationButton(true, mainAct.lastUserLocationMargin);
        }
        if (isRentalDesignClick) {
            mainAct.cabBottomSheetBehavior.setState(BottomSheetBehavior.STATE_COLLAPSED);
            rentalPkg.performClick();
            isRentalDesignClick = false;
            return;
        }
        mainAct.cabBottomSheetBehavior.setState(visibility == View.VISIBLE ? BottomSheetBehavior.STATE_COLLAPSED : BottomSheetBehavior.STATE_EXPANDED);
    }

    //TODO Don't Delete. This method is require for Full Screen Map functionality.
    public void showHideViewsOnMapClick(boolean hide) {
        Animation animation;
        if (hide) {
            mainAct.setMapPaddingForCab(20, 0, 35, 35, true);
            animation = AnimationUtils.loadAnimation(getActContext(), R.anim.view_hide);
            mainAct.mainHeaderFrag.backBtn.setVisibility(View.GONE);
        } else {
            animation = AnimationUtils.loadAnimation(getActContext(), R.anim.view_show);
            mainAct.setMapPaddingForCab(0, 0, 0, mainAct.lastCabBottomPadding, true);

            if (!mainAct.isMultiDelivery() && !(poolArea.getVisibility() == View.VISIBLE)) {
                mainAct.mainHeaderFrag.backBtn.setVisibility(View.VISIBLE);
            }
        }

        setAnimation(animation, hide);
    }

    private void setAnimation(Animation animation, boolean hide) {
        animation.setAnimationListener(new Animation.AnimationListener() {
            @Override
            public void onAnimationStart(Animation animation) {

            }

            @Override
            public void onAnimationEnd(Animation animation) {
                if (hide) {
                    mainAct.selAddresArea.setVisibility(View.GONE);
                    cabBottomSheetLayout.setVisibility(View.GONE);
                    if (reqpayAreaLastVisibleState) {
                        requestPayArea.setVisibility(View.GONE);
                    }
                    mainAct.userLocBtnImgView.setVisibility(View.GONE);
                    if (carDetailsDialogLastVisibleState) {
                        design_linear_layout_car_details.setVisibility(View.GONE);
                    }
                } else {
                    if (!mainAct.isMultiDelivery()) {
                        mainAct.selAddresArea.setVisibility(View.VISIBLE);
                    }
                    cabBottomSheetLayout.setVisibility(View.VISIBLE);
                    mainAct.userLocBtnImgView.setVisibility(View.VISIBLE);
                    if (reqpayAreaLastVisibleState) {
                        requestPayArea.setVisibility(View.VISIBLE);
                    }
                    if (carDetailsDialogLastVisibleState) {
                        design_linear_layout_car_details.setVisibility(View.VISIBLE);
                    }

                }
            }

            @Override
            public void onAnimationRepeat(Animation animation) {

            }
        });
        mainAct.selAddresArea.startAnimation(animation);
        cabBottomSheetLayout.startAnimation(animation);
        if (reqpayAreaLastVisibleState) {
            requestPayArea.startAnimation(animation);
        }
        mainAct.userLocBtnImgView.startAnimation(animation);
        if (carDetailsDialogLastVisibleState) {
            design_linear_layout_car_details.startAnimation(animation);
        }

    }

    public void enableRideNowBtn(boolean isVisibility) {
        if (mainAct.isMultiDelivery() || Utils.checkText(generalFunc.getJsonValueStr("PaymentMode", getProfilePaymentModel.getProfileInfo()))) {
            ride_now_btn.setEnabled(isVisibility);
        } else {
            ride_now_btn.setEnabled(false);
        }
    }

}