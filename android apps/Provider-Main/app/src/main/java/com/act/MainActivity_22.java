package com.act;

import static com.general.files.LocalNotification.playNotificationSound;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.location.Location;
import android.os.Bundle;
import android.os.Handler;
import android.text.TextUtils;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewTreeObserver;
import android.view.animation.AlphaAnimation;
import android.view.animation.Animation;
import android.view.animation.AnimationUtils;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.core.content.ContextCompat;
import androidx.core.view.ViewCompat;
import androidx.fragment.app.Fragment;
import androidx.fragment.app.FragmentManager;
import androidx.fragment.app.FragmentTransaction;
import androidx.recyclerview.widget.RecyclerView;

import com.AudioRecord.RecordingItem;
import com.activity.ParentActivity;
import com.adapter.files.ManageVehicleListAdapter;
import com.dialogs.BottomInfoDialog;
import com.facebook.ads.AdSize;
import com.fragments.InactiveFragment;
import com.fragments.MyBookingFragment;
import com.fragments.MyProfileFragment;
import com.fragments.MyWalletFragment;
import com.general.PermissionHandlers;
import com.general.SkeletonViewHandler;
import com.general.features.SafetyTools;
import com.general.files.ActUtils;
import com.general.files.AddBottomBar;
import com.general.files.AlarmReceiver;
import com.general.files.CovidDialog;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.MyApp;
import com.general.files.NotificationScheduler;
import com.general.files.OpenAdvertisementDialog;
import com.general.files.PolyLineAnimator;
import com.general.files.SlideButton;
import com.general.files.UpdateDirections;
import com.getbase.floatingactionbutton.FloatingActionButton;
import com.getbase.floatingactionbutton.FloatingActionsMenu;
import com.google.android.gms.ads.AdRequest;
import com.google.android.gms.ads.AdView;
import com.google.android.gms.ads.MobileAds;
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
import com.google.android.gms.maps.model.TileOverlay;
import com.google.android.gms.maps.model.TileOverlayOptions;
import com.google.android.gms.maps.model.VisibleRegion;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.android.material.snackbar.Snackbar;
import com.google.maps.android.heatmaps.Gradient;
import com.google.maps.android.heatmaps.HeatmapTileProvider;
import com.buddyverse.providers.R;
import com.kyleduo.switchbutton.SwitchButton;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.service.handler.AppService;
import com.service.model.EventInformation;
import com.service.server.ServerTask;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.GenerateAlertBox;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.view.anim.loader.AVLoadingIndicatorView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.concurrent.atomic.AtomicReference;

@SuppressLint({"SetTextI18n", "UseCompatLoadingForDrawables", "all"})
public class MainActivity_22 extends ParentActivity implements OnMapReadyCallback, GetLocationUpdates.LocationUpdatesListener, GoogleMap.OnCameraIdleListener, ManageVehicleListAdapter.OnItemClickList, GetAddressFromLocation.AddressFound {

    public Location userLocation;
    SupportMapFragment map;
    GoogleMap gMap;

    MTextView ufxonlineOfflineTxtView, ufxTitleonlineOfflineTxtView;
    LinearLayout llBox1Area, llBox2Area, llBox3Area, llBox4Area;
    MTextView txtYourEarning, txtTotalEarning, txtBox1Value, txtBox2Value, txtBox3Value, txtBox4Value, txtBox1Title, txtBox2Title, txtBox3Title, txtBox4Title;
    MTextView addressTxtView, addressTxtViewufx, joblocHTxtView, joblocHTxtViewufx;
    MTextView ufxDrivername, radiusTxtView, radiusTxtViewufx;
    MTextView pendingjobHTxtView, pendingjobValTxtView, upcomingjobHTxtView, upcomingjobValTxtView;
    SwitchButton onlineOfflineSwitch, ufxonlineOfflineSwitch;
    ImageView userLocBtnImgView, refreshImgView, notificationImg, menuufxImgView, imageradiusufx;

    public boolean isDriverOnline = false;
    boolean isFirstLocation = true, isOnlineOfflineSwitchCalled = false, isOnlineAvoid = false, isShowNearByPassengers = false;
    boolean iswallet = false, isFirstAddressLoaded = false, isBtnClick = false, isCarChangeTxt = false, isfirstZoom = false;

    //    Intent startUpdatingStatus;
    String radiusval = "0", ENABLE_HAIL_RIDES = "", selectedcar = "", HailEnableOnDriverStatus = "";
    String LBL_LOAD_ADDRESS = "", LBL_GO_ONLINE_TXT = "", LBL_GO_OFFLINE_TXT = "", LBL_ONLINE = "", LBL_OFFLINE = "", LBL_LOCATING_YOU_TXT = "";

    ArrayList<String> items_txt_car = new ArrayList<>();
    ArrayList<String> items_txt_car_json = new ArrayList<>();
    ArrayList<String> items_isHail_json = new ArrayList<>();
    ArrayList<String> items_car_id = new ArrayList<>();

    AlertDialog list_car;
    GetAddressFromLocation getAddressFromLocation;

    ServerTask heatMapAsyncTask;
    HashMap<String, String> onlinePassengerLocList = new HashMap<>();
    HashMap<String, String> historyLocList = new HashMap<>();
    ArrayList<TileOverlay> mapOverlayList = new ArrayList<>();

    int height, bottomBtnpos = 1;

    LinearLayout llEarningView, mapbottomviewarea, pendingMainArea, botomarea, joblocareaufx, eodLocationArea, removeEodTripArea;
    RelativeLayout rideviewarea, ufxarea, pendingarea, upcomginarea;
    RelativeLayout selCarArea, mapviewarea, activearea, Toolbar;

    double radius_map = 0, PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT = 5;
    View workAreaLine, headerLogo, headerLogoride;
    MTextView addressTxt, carNameTxt, carNumPlateTxt;
    Location lastPublishedLoc = null;
    private static final int SEL_CARD = 004, TRANSFER_MONEY = 87, WEBVIEWPAYMENT = 001;

    /*EndOfTheDay Trip view declaration start*/

    Marker sourceMarker, destMarker;
    private AlertDialog confirmDialog;
    private BottomSheetDialog faredialog;
    UpdateDirections updateDirections;
    /*EndOfTheDay Trip view declaration end*/ CustomDialog customDialog;

    FloatingActionButton heat_action, heat_actionRTL;
    FloatingActionButton hail_action, hail_actionRTL;
    FloatingActionsMenu menuMultipleActions, multiple_actionsRTL;
    FloatingActionButton return_action, return_actionRTL;
    FloatingActionButton location_action, location_actionRTL;
    FloatingActionButton changeCar_action, changeCar_actionRTL;
    MyProfileFragment myProfileFragment = null;
    MyWalletFragment myWalletFragment = null;
    public MyBookingFragment myBookingFragment = null;

    View shadowView;
    public AddBottomBar addBottomBar;
    FrameLayout containerufx, MainHeaderLayout, containerView;
    public boolean iswalletFragemnt = false, isbookingFragemnt = false, isProfileFragment = false, isUfxServicesEnabled = true, iswitchClick = true, isOnlyUfxServicesSelected = true;
    private SelectableRoundedImageView userPicImgView;
    private LinearLayout google_banner_container, banner_container, rewardsBtn;
    private BottomSheetDialog sheetDialog;
    private GenerateAlertBox noLocationAlertBox;
    private MTextView pendingBookingCount, newBookingArrivedTxtView;
    LinearLayout newBookingNotificationArea;
    AppCompatImageView continueBtn;
    Handler newOrderHandler;
    RelativeLayout pendingBookingArea;
    private Animation anim;
    InactiveFragment inactiveFragment = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main_22);
        if (MyApp.getInstance().isGetDetailCall) {
            return;
        }

        generalFunc.storeData("PROVIDER_STATUS_MODE", "idle");

        ImageView backImgView = findViewById(R.id.backImgView);
        backImgView.setVisibility(View.GONE);

        if (generalFunc.getJsonValueStr("ENABLE_FACEBOOK_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            faceBooksAdds();
        }
        if (generalFunc.getJsonValueStr("ENABLE_GOOGLE_ADS", obj_userProfile).equalsIgnoreCase("Yes")) {
            googleAdds();
        }
        llEarningView = (LinearLayout) findViewById(R.id.llEarningView);
        llEarningView.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.INVISIBLE);
        MyUtils.setPendingBookingsCount("");
        MyUtils.setIsVideoCallGenerated("No");

        Method1(savedInstanceState);


        isCarChangeTxt = true;


        changeObj();
        addBottomBar = new AddBottomBar(getActContext(), generalFunc, obj_userProfile, (LinearLayout) findViewById(R.id.rduTopArea));

        String advertise_banner_data = generalFunc.getJsonValueStr("advertise_banner_data", obj_userProfile);
        if (!MyApp.getInstance().isGetDetailCall && advertise_banner_data != null && !advertise_banner_data.equalsIgnoreCase("")) {
            if (generalFunc.getJsonValue("image_url", advertise_banner_data) != null && !generalFunc.getJsonValue("image_url", advertise_banner_data).equalsIgnoreCase("")) {
                HashMap<String, String> map = new HashMap<>();
                map.put("image_url", generalFunc.getJsonValue("image_url", advertise_banner_data));
                map.put("tRedirectUrl", generalFunc.getJsonValue("tRedirectUrl", advertise_banner_data));
                map.put("iAdvertBannerId", generalFunc.getJsonValue("iAdvertBannerId", advertise_banner_data));
                map.put("vImageWidth", generalFunc.getJsonValue("vImageWidth", advertise_banner_data));
                map.put("vImageHeight", generalFunc.getJsonValue("vImageHeight", advertise_banner_data));
                new OpenAdvertisementDialog(getActContext(), map, generalFunc);
            }
        }

        shadowView = (View) findViewById(R.id.shadowView);
        menuMultipleActions = (FloatingActionsMenu) findViewById(R.id.multiple_actions);
        multiple_actionsRTL = (FloatingActionsMenu) findViewById(R.id.multiple_actionsRTL);


        if (generalFunc.isRTLmode()) {
            multiple_actionsRTL.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.VISIBLE);
            menuMultipleActions.setVisibility(View.GONE);
        } else {
            menuMultipleActions.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.VISIBLE);
            multiple_actionsRTL.setVisibility(View.GONE);
        }
        menuMultipleActions.setOnFloatingActionsMenuUpdateListener(new FloatingActionsMenu.OnFloatingActionsMenuUpdateListener() {
            @Override
            public void onMenuExpanded() {
                shadowView.setVisibility(View.VISIBLE);
            }

            @Override
            public void onMenuCollapsed() {
                shadowView.setVisibility(View.GONE);

            }
        });


        multiple_actionsRTL.setOnFloatingActionsMenuUpdateListener(new FloatingActionsMenu.OnFloatingActionsMenuUpdateListener() {
            @Override
            public void onMenuExpanded() {
                shadowView.setVisibility(View.VISIBLE);
            }

            @Override
            public void onMenuCollapsed() {
                shadowView.setVisibility(View.GONE);

            }
        });


        MainHeaderLayout = (FrameLayout) findViewById(R.id.MainHeaderLayout);
        containerView = (FrameLayout) findViewById(R.id.containerView);
        containerufx = (FrameLayout) findViewById(R.id.containerufx);
        userPicImgView = (SelectableRoundedImageView) findViewById(R.id.userPicImgView);
        addToClickHandler(userPicImgView);
        notificationImg = (ImageView) findViewById(R.id.notificationImg);
        pendingBookingArea = (RelativeLayout) findViewById(R.id.pendingBookingArea);
        pendingBookingCount = (MTextView) findViewById(R.id.pendingBookingCount);
        newBookingArrivedTxtView = (MTextView) findViewById(R.id.newBookingArrivedTxtView);
        continueBtn = (AppCompatImageView) findViewById(R.id.continueBtn);
        newBookingNotificationArea = (LinearLayout) findViewById(R.id.newBookingNotificationArea);
        addToClickHandler(continueBtn);
        addToClickHandler(notificationImg);
        addToClickHandler(pendingBookingArea);
        Toolbar = (RelativeLayout) findViewById(R.id.Toolbar);
        rewardsBtn = (LinearLayout) findViewById(R.id.rewardsBtn);
        MTextView rewardsTxt = (MTextView) findViewById(R.id.rewardsTxt);
        rewardsTxt.setText(generalFunc.retrieveLangLBl("Rewards", "LBL_REWARDS_TEXT"));
        addToClickHandler(rewardsBtn);
        if (generalFunc.getJsonValueStr("ENABLE_SCHEDULED_RIDE_FLOW", obj_userProfile).equalsIgnoreCase("Yes")) {
            pendingBookingArea.setVisibility(View.VISIBLE);
        } else {
            pendingBookingArea.setVisibility(View.GONE);
        }
        managePendingCountArea();
        selCarArea = (RelativeLayout) findViewById(R.id.selCarArea);
        selCarArea.setVisibility(ServiceModule.IsTrackingProvider ? View.VISIBLE : View.GONE);
        carNameTxt = (MTextView) findViewById(R.id.carNameTxt);
        carNumPlateTxt = (MTextView) findViewById(R.id.carNumPlateTxt);

        hail_action = (FloatingActionButton) findViewById(R.id.hail_action);
        hail_actionRTL = (FloatingActionButton) findViewById(R.id.hail_actionRTL);
        String LBL_TAXI_HAIL = generalFunc.retrieveLangLBl("", "LBL_TAXI_HAIL");
        hail_action.setTitle(LBL_TAXI_HAIL);
        hail_actionRTL.setTitle(LBL_TAXI_HAIL);
        hail_action.setVisibility(View.GONE);
        hail_actionRTL.setVisibility(View.GONE);
        addToClickHandler(hail_action);
        addToClickHandler(hail_actionRTL);
        heat_action = (FloatingActionButton) findViewById(R.id.heat_action);
        heat_actionRTL = (FloatingActionButton) findViewById(R.id.heat_actionRTL);
        String LBL_HEAT = generalFunc.retrieveLangLBl("", "LBL_HEAT");
        heat_action.setTitle(LBL_HEAT);
        heat_actionRTL.setTitle(LBL_HEAT);
        addToClickHandler(heat_action);
        addToClickHandler(heat_actionRTL);
        return_action = (FloatingActionButton) findViewById(R.id.return_action);
        return_actionRTL = (FloatingActionButton) findViewById(R.id.return_actionRTL);
        String LBL_RETURN = generalFunc.retrieveLangLBl("", "LBL_RETURN");
        return_action.setTitle(LBL_RETURN);
        return_actionRTL.setTitle(LBL_RETURN);
        return_action.setVisibility(View.GONE);
        return_actionRTL.setVisibility(View.GONE);
        addToClickHandler(return_action);
        addToClickHandler(return_action);
        location_action = (FloatingActionButton) findViewById(R.id.location_action);
        location_actionRTL = (FloatingActionButton) findViewById(R.id.location_actionRTL);
        if (!ServiceModule.DeliverAllProduct && ServiceModule.ServiceProvider) {
            location_action.setVisibility(View.VISIBLE);
            location_actionRTL.setVisibility(View.VISIBLE);
        } else {
            location_action.setVisibility(View.GONE);
            location_actionRTL.setVisibility(View.GONE);
        }

        String LBL_LOCATIONS_TXT = generalFunc.retrieveLangLBl("", "LBL_LOCATIONS_TXT");
        location_action.setTitle(LBL_LOCATIONS_TXT);
        location_actionRTL.setTitle(LBL_LOCATIONS_TXT);
        addToClickHandler(location_action);
        addToClickHandler(location_actionRTL);

        changeCar_action = (FloatingActionButton) findViewById(R.id.changeCar_action);
        changeCar_actionRTL = (FloatingActionButton) findViewById(R.id.changeCar_actionRTL);

        if (ServiceModule.isRideView()) {
            changeCar_action.setVisibility(View.VISIBLE);
            changeCar_actionRTL.setVisibility(View.VISIBLE);
        } else {
            changeCar_action.setVisibility(View.GONE);
            changeCar_actionRTL.setVisibility(View.GONE);
        }
        String LBL_CHANGE_TXT = generalFunc.retrieveLangLBl("", "LBL_VEHICLE_INFORMATION");
        changeCar_action.setTitle(LBL_CHANGE_TXT);
        changeCar_actionRTL.setTitle(LBL_CHANGE_TXT);
        addToClickHandler(changeCar_action);
        addToClickHandler(changeCar_actionRTL);

        String ENABLE_NEWS_SECTION = generalFunc.getJsonValueStr("ENABLE_NEWS_SECTION", obj_userProfile);
        if (ENABLE_NEWS_SECTION != null && ENABLE_NEWS_SECTION.equalsIgnoreCase("yes")) {
            notificationImg.setVisibility(View.VISIBLE);
        } else {
            notificationImg.setVisibility(View.GONE);
        }


        PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT = GeneralFunctions.parseDoubleValue(5, generalFunc.retrieveValue(Utils.PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT));

        getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
        getAddressFromLocation.setAddressList(this);


        refreshImgView = (ImageView) findViewById(R.id.refreshImgView);

        pendingarea = (RelativeLayout) findViewById(R.id.pendingarea);
        upcomginarea = (RelativeLayout) findViewById(R.id.upcomginarea);
        addToClickHandler(pendingarea);
        addToClickHandler(upcomginarea);
        rideviewarea = (RelativeLayout) findViewById(R.id.rideviewarea);
        pendingjobHTxtView = (MTextView) findViewById(R.id.pendingjobHTxtView);
        pendingjobValTxtView = (MTextView) findViewById(R.id.pendingjobValTxtView);
        upcomingjobHTxtView = (MTextView) findViewById(R.id.upcomingjobHTxtView);
        upcomingjobValTxtView = (MTextView) findViewById(R.id.upcomingjobValTxtView);
        radiusTxtView = (MTextView) findViewById(R.id.radiusTxtView);
        radiusTxtViewufx = (MTextView) findViewById(R.id.radiusTxtViewufx);
        imageradiusufx = (ImageView) findViewById(R.id.imageradiusufx);
        headerLogoride = findViewById(R.id.appNameTxt);
        headerLogo = findViewById(R.id.appNameTxt1);
        activearea = (RelativeLayout) findViewById(R.id.activearea);
        joblocareaufx = (LinearLayout) findViewById(R.id.joblocareaufx);
        workAreaLine = (View) findViewById(R.id.workAreaLine);
        addToClickHandler(radiusTxtViewufx);
        addToClickHandler(imageradiusufx);
        addToClickHandler(refreshImgView);
        ufxarea = (RelativeLayout) findViewById(R.id.ufxarea);
        if (ServiceModule.isServiceProviderOnly()) {
            rideviewarea.setVisibility(View.GONE);
            ufxarea.setVisibility(View.VISIBLE);
            refreshImgView.setVisibility(View.VISIBLE);
            setRadiusVal();
        } else {
            rideviewarea.setVisibility(View.VISIBLE);
            ufxarea.setVisibility(View.GONE);
        }

        userLocBtnImgView = (ImageView) findViewById(R.id.userLocBtnImgView);

        menuufxImgView = (ImageView) findViewById(R.id.menuufxImgView);
        joblocHTxtView = (MTextView) findViewById(R.id.joblocHTxtView);
        joblocHTxtViewufx = (MTextView) findViewById(R.id.joblocHTxtViewufx);
        addressTxtView = (MTextView) findViewById(R.id.addressTxtView);
        addressTxtViewufx = (MTextView) findViewById(R.id.addressTxtViewufx);
        addToClickHandler(menuufxImgView);
        ufxDrivername = (MTextView) findViewById(R.id.ufxDrivername);
        pendingMainArea = (LinearLayout) findViewById(R.id.pendingMainArea);
        botomarea = (LinearLayout) findViewById(R.id.botomarea);

        pendingjobHTxtView.setText(generalFunc.retrieveLangLBl("Pending Jobs", "LBL_PENDING_JOBS"));
        upcomingjobHTxtView.setText(generalFunc.retrieveLangLBl("Upcoming Jobs", "LBL_UPCOMING_JOBS"));

        joblocHTxtView.setText(generalFunc.retrieveLangLBl("Your Job Location", "LBL_YOUR_JOB_LOCATION_TXT"));
        joblocHTxtViewufx.setText(generalFunc.retrieveLangLBl("Your Job Location", "LBL_YOUR_JOB_LOCATION_TXT"));

        txtYourEarning = (MTextView) findViewById(R.id.txtYourEarning);
        txtTotalEarning = (MTextView) findViewById(R.id.txtTotalEarning);
        txtBox1Value = (MTextView) findViewById(R.id.txtBox1Value);
        txtBox2Value = (MTextView) findViewById(R.id.txtBox2Value);
        txtBox3Value = (MTextView) findViewById(R.id.txtBox3Value);
        txtBox4Value = (MTextView) findViewById(R.id.txtBox4Value);

        txtBox1Title = (MTextView) findViewById(R.id.txtBox1Title);
        txtBox2Title = (MTextView) findViewById(R.id.txtBox2Title);
        txtBox3Title = (MTextView) findViewById(R.id.txtBox3Title);
        txtBox4Title = (MTextView) findViewById(R.id.txtBox4Title);


        llBox1Area = (LinearLayout) findViewById(R.id.llBox1Area);
        llBox2Area = (LinearLayout) findViewById(R.id.llBox2Area);
        llBox3Area = (LinearLayout) findViewById(R.id.llBox3Area);
        llBox4Area = (LinearLayout) findViewById(R.id.llBox4Area);

        openPages();

        LBL_LOAD_ADDRESS = generalFunc.retrieveLangLBl("", "LBL_LOAD_ADDRESS");
        LBL_LOCATING_YOU_TXT = generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT");
        LBL_GO_ONLINE_TXT = generalFunc.retrieveLangLBl("", "LBL_GO_ONLINE_TXT");
        LBL_GO_OFFLINE_TXT = generalFunc.retrieveLangLBl("", "LBL_GO_OFFLINE_TXT");
        LBL_ONLINE = generalFunc.retrieveLangLBl("", "LBL_ONLINE");
        LBL_OFFLINE = generalFunc.retrieveLangLBl("", "LBL_OFFLINE");

        addressTxtView.setText(LBL_LOCATING_YOU_TXT);
        addressTxtViewufx.setText(LBL_LOCATING_YOU_TXT);
        handleWorkAddress();


        showHeatMap();


        /*EndOfTheDay Trip view initialization start*/
        ((MTextView) findViewById(R.id.destinationModeHintTxt)).setText(generalFunc.retrieveLangLBl("", "LBL_DESTINATION_MODE_ON_TXT"));

        eodLocationArea = (LinearLayout) findViewById(R.id.eodLocationArea);
        removeEodTripArea = (LinearLayout) findViewById(R.id.removeEodTripArea);
        addToClickHandler(removeEodTripArea);
        addressTxt = (MTextView) findViewById(R.id.addressTxt);

        mapviewarea = (RelativeLayout) findViewById(R.id.mapviewarea);
        mapbottomviewarea = (LinearLayout) findViewById(R.id.mapbottomviewarea);


        ufxonlineOfflineTxtView = (MTextView) findViewById(R.id.ufxonlineOfflineTxtView);
        ufxTitleonlineOfflineTxtView = (MTextView) findViewById(R.id.ufxTitleonlineOfflineTxtView);
        onlineOfflineSwitch = (SwitchButton) findViewById(R.id.onlineOfflineSwitch);
        onlineOfflineSwitch.setText(LBL_ONLINE, LBL_OFFLINE);
        onlineOfflineSwitch.setTextColor(getActContext().getResources().getColor(R.color.appThemeColor_1));

        ufxonlineOfflineSwitch = (SwitchButton) findViewById(R.id.ufxonlineOfflineSwitch);
        ufxonlineOfflineSwitch.setText(LBL_ONLINE, LBL_OFFLINE);
        ufxonlineOfflineSwitch.setTextColor(getActContext().getResources().getColor(R.color.appThemeColor_1));

        map = (SupportMapFragment) getSupportFragmentManager().findFragmentById(R.id.mapV2);


//        startUpdatingStatus = new Intent(getApplicationContext(), UpdateDriverStatus.class);

        if (generalFunc.isRTLmode()) {
            addressTxt.setBackgroundResource(R.drawable.ic_shape_rtl);
        }

        ufxDrivername.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " " + generalFunc.getJsonValueStr("vLastName", obj_userProfile));

        if (generalFunc.getJsonValueStr("ENABLE_DRIVER_REWARD_MODULE", obj_userProfile).equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("ENABLE_HOMESCREEN_REWARDS_BUTTON", obj_userProfile).equalsIgnoreCase("yes")) {
            rewardsBtn.setVisibility(View.VISIBLE);
        } else {
            rewardsBtn.setVisibility(View.GONE);
        }
        setGeneralData();
        setUserInfo();

        if (generalFunc.getJsonValueStr("RIDE_LATER_BOOKING_ENABLED", obj_userProfile).equalsIgnoreCase("Yes")) {
            pendingMainArea.setVisibility(View.VISIBLE);
            botomarea.setVisibility(View.VISIBLE);
        } else {
            pendingMainArea.setVisibility(View.GONE);
            botomarea.setVisibility(View.GONE);
        }

        map.getMapAsync(MainActivity_22.this);


        addToClickHandler(userLocBtnImgView);


        if (ServiceModule.isServiceProviderOnly()) {

            ufxonlineOfflineSwitch.setOnTouchListener((v, event) -> {
                if (event.getAction() == MotionEvent.ACTION_UP) {
                    isOnlineOfflineSwitchCalled = true;
                }
                return false;
            });


            ufxonlineOfflineSwitch.setOnClickListener(view -> {
                Logger.d("ufxonlineOfflineSwitch", "::" + iswitchClick);
                iswitchClick = true;

            });

            ufxonlineOfflineSwitch.setOnCheckedChangeListener((compoundButton, b) -> {


                if (!intCheck.isNetworkConnected()) {
                    isOnlineOfflineSwitchCalled = false;
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
                    return;
                }

                if (b) {
                    ufxonlineOfflineSwitch.setThumbColorRes(R.color.Green);
                    ufxonlineOfflineSwitch.setBackColorRes(R.color.white);
                } else {
                    ufxonlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
                    ufxonlineOfflineSwitch.setBackColorRes(android.R.color.white);
                }


                if (isOnlineAvoid) {
                    isOnlineAvoid = false;
                    isOnlineOfflineSwitchCalled = false;
                    return;
                }

                if (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_RIDE").equalsIgnoreCase("Yes") || (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_DELIVERY").equalsIgnoreCase("Yes") || (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_UFX").equalsIgnoreCase("Yes")))) {

                    if (generalFunc.getJsonValueStr("ENABLE_SAFETY_CHECKLIST", obj_userProfile).equalsIgnoreCase("Yes") && b && iswitchClick) {
                        iswitchClick = false;
                        Bundle bn = new Bundle();

                        bn.putString("URL", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_INFO_URL", obj_userProfile));
                        new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                        //((Activity)getActContext()).overridePendingTransition( R.anim.bottom_up, R.anim.bottom_down );
                    } else {


                        goOnlineOffline(b, true);
                        isOnlineOfflineSwitchCalled = false;
                    }

                } else {


                    goOnlineOffline(b, true);
                    isOnlineOfflineSwitchCalled = false;
                }


            });

        } else {
            onlineOfflineSwitch.setOnTouchListener((v, event) -> {
                switch (event.getAction()) {
                    case MotionEvent.ACTION_UP:
                        isOnlineOfflineSwitchCalled = true;
                        iswitchClick = true;
                        break;
                }
                return false;
            });

            onlineOfflineSwitch.setOnClickListener(view -> {
                Logger.d("onlineOfflineSwitch", "::called");
                iswitchClick = true;

            });
            onlineOfflineSwitch.setOnCheckedChangeListener((compoundButton, b) -> {


                multiple_actionsRTL.collapse();
                menuMultipleActions.collapse();
                shadowView.setVisibility(View.GONE);

                if (!intCheck.isNetworkConnected()) {
                    isOnlineOfflineSwitchCalled = false;
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
                    return;
                }

                if (b) {
                    onlineOfflineSwitch.setThumbColorRes(R.color.Green);
                    onlineOfflineSwitch.setBackColorRes(android.R.color.white);
                } else {
                    onlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
                    onlineOfflineSwitch.setBackColorRes(android.R.color.white);
                }

                if (isOnlineAvoid) {
                    isOnlineAvoid = false;
                    isOnlineOfflineSwitchCalled = false;
                    return;
                }

                if (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_RIDE").equalsIgnoreCase("Yes") || (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_DELIVERY").equalsIgnoreCase("Yes") || (generalFunc.retrieveValue("ENABLE_SAFETY_FEATURE_UFX").equalsIgnoreCase("Yes")))) {

                    if (generalFunc.getJsonValueStr("ENABLE_SAFETY_CHECKLIST", obj_userProfile).equalsIgnoreCase("Yes") && b && iswitchClick) {
                        iswitchClick = false;
                        Bundle bn = new Bundle();


                        bn.putString("URL", generalFunc.getJsonValueStr("RESTRICT_PASSENGER_LIMIT_INFO_URL", obj_userProfile));
                        new ActUtils(getActContext()).startActForResult(CovidDialog.class, bn, 77);
                        //((Activity)getActContext()).overridePendingTransition( R.anim.bottom_up, R.anim.bottom_down );
                    } else {


                        goOnlineOffline(b, true);
                        isOnlineOfflineSwitchCalled = false;
                        MainActivity_22.super.onResume();
                    }

                } else {


                    goOnlineOffline(b, true);
                    isOnlineOfflineSwitchCalled = false;
                    MainActivity_22.super.onResume();
                }

            });

        }

        if (savedInstanceState != null) {
            String restratValue_str = savedInstanceState.getString("RESTART_STATE");

            if (restratValue_str != null && !restratValue_str.equals("") && restratValue_str.trim().equals("true")) {
                generalFunc.restartApp();
            }
        }

        generalFunc.storeData(Utils.DRIVER_CURRENT_REQ_OPEN_KEY, "false");


        String eStatus = generalFunc.getJsonValueStr("eStatus", obj_userProfile);
        if (myBookingFragment == null) {
            myBookingFragment = new MyBookingFragment();
            getSupportFragmentManager().beginTransaction().add(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId():containerView.getId(), myBookingFragment, "2").commit();
        }
        if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
            getSupportFragmentManager().beginTransaction().add(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId():containerView.getId(), myWalletFragment, "3").commit();
        }
        if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
            getSupportFragmentManager().beginTransaction().add(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId() : containerView.getId(), myProfileFragment, "4").commit();
        }
        if (eStatus.equalsIgnoreCase("inactive")) {
            mapbottomviewarea.setVisibility(View.GONE);
            llEarningView.setVisibility(View.GONE);
            mapviewarea.setVisibility(View.GONE);
            menuMultipleActions.setVisibility(View.GONE);
            multiple_actionsRTL.setVisibility(View.GONE);
            return_action.setVisibility(View.GONE);
            return_actionRTL.setVisibility(View.GONE);
            return_action.setVisibility(View.GONE);
            return_actionRTL.setVisibility(View.GONE);
            headerLogo.setVisibility(View.VISIBLE);
            onlineOfflineSwitch.setVisibility(View.GONE);
            selCarArea.setVisibility(View.GONE);
            headerLogoride.setVisibility(View.VISIBLE);
            userPicImgView.setVisibility(View.GONE);
            pendingBookingArea.setVisibility(View.GONE);
            if (inactiveFragment == null) {
                inactiveFragment = new InactiveFragment();
                getSupportFragmentManager().beginTransaction().add(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId():containerView.getId(), inactiveFragment, "1").commit();
            }

            if (ServiceModule.isServiceProviderOnly()) {
                activearea.setVisibility(View.GONE);
                openPageFrag(1, inactiveFragment);

            } else {
                containerView.setVisibility(View.VISIBLE);
                openPageFrag(1, inactiveFragment);
            }
            refreshImgView.setVisibility(View.GONE);
        } else {

            if (generalFunc.getJsonValueStr("ENABLE_SCHEDULED_RIDE_FLOW", obj_userProfile).equalsIgnoreCase("Yes")) {
                pendingBookingArea.setVisibility(View.VISIBLE);
            } else {
                pendingBookingArea.setVisibility(View.GONE);
            }
            onlineOfflineSwitch.setVisibility(View.VISIBLE);

            PermissionHandlers.getInstance().initiatePermissionHandler();

            isOnlyUfxServicesSelected = generalFunc.getJsonValueStr("isOnlyUfxServicesSelected", obj_userProfile).equalsIgnoreCase("Yes");
            selCarArea.setVisibility(ServiceModule.IsTrackingProvider ? View.VISIBLE : View.GONE);
            headerLogoride.setVisibility(View.GONE);
            userPicImgView.setVisibility(View.VISIBLE);


            if (ServiceModule.isServiceProviderOnly()) {
                joblocareaufx.setVisibility(View.VISIBLE);
                refreshImgView.setVisibility(View.VISIBLE);

            }

            headerLogo.setVisibility(View.GONE);

            if (isDriverOnline) {
                isHailRideOptionEnabled();
            }
            containerView.setVisibility(View.GONE);
            mapbottomviewarea.setVisibility(View.VISIBLE);
            mapviewarea.setVisibility(View.VISIBLE);


            handleNoLocationDial();

        }

        generalFunc.deleteTripStatusMessages();

        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");

//        boolean isEmailVerified = generalFunc.getJsonValueStr("eEmailVerified", obj_userProfile).equalsIgnoreCase("YES");
        boolean isEmailVerified = true;
        boolean isPhoneVerified = generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES");

        if (!isEmailVerified || !isPhoneVerified) {

            Bundle bn = new Bundle();
            if (!isEmailVerified && !isPhoneVerified) {
                bn.putString("msg", "DO_EMAIL_PHONE_VERIFY");
            } else if (!isEmailVerified) {
                bn.putString("msg", "DO_EMAIL_VERIFY");
            } else if (!isPhoneVerified) {
                bn.putString("msg", "DO_PHONE_VERIFY");
            }


        }

        if (!PermissionHandlers.getInstance().getVisibilityPager()) {
            if (generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") && !generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {

                BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);
                bottomInfoDialog.showPreferenceDialog(generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN"), generalFunc.retrieveLangLBl("", "LBL_QUICK_LOGIN_NOTE_TXT"), R.raw.biometric, false, generalFunc.retrieveLangLBl("", "LBL_OK"), "", true);
                generalFunc.storeData("isFirstTimeSmartLoginView", "Yes");
            }
        }
    }

    public void checkBiddingView(int pos) {
        if (isbookingFragemnt) {
            myBookingFragment.setFrag(pos);
        } else {
            Bundle bn = new Bundle();
            bn.putInt("viewPos", pos);
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn);
        }
    }

    private void openPages() {
        Bundle bn1 = new Bundle();
        txtBox1Value.setOnClickListener(v -> {
            bn1.putString("isView", "past");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn1);
        });
        txtBox2Value.setOnClickListener(v -> {
            //
            new ActUtils(getActContext()).startActWithData(DriverFeedbackActivity.class, bn1);
        });
        txtBox3Value.setOnClickListener(v -> {
            bn1.putString("isView", "upcoming");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn1);
        });
        txtBox4Value.setOnClickListener(v -> {
            bn1.putString("isView", "pending");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn1);
        });
    }

    private void googleAdds() {
        AdView mAdView;
        google_banner_container = findViewById(R.id.google_banner_container);
        google_banner_container.setVisibility(View.VISIBLE);
        MobileAds.initialize(getActContext());
        mAdView = new AdView(getActContext());
        mAdView.setAdSize(com.google.android.gms.ads.AdSize.BANNER);
        mAdView.setAdUnitId(generalFunc.getJsonValueStr("GOOGLE_ADMOB_ID", obj_userProfile));
        AdRequest adRequest = new AdRequest.Builder().build();
        google_banner_container.addView(mAdView);
        mAdView.loadAd(adRequest);
    }

    private void faceBooksAdds() {
        banner_container = findViewById(R.id.banner_container);
        banner_container.setVisibility(View.VISIBLE);
        com.facebook.ads.AdView adView;
        adView = new com.facebook.ads.AdView(this, "IMG_16_9_APP_INSTALL#" + generalFunc.getJsonValueStr("FACEBOOK_PLACEMENT_ID", obj_userProfile), AdSize.BANNER_HEIGHT_50);
        banner_container.addView(adView);
        adView.loadAd();
    }

    private void manageView(boolean isHome) {
        if (google_banner_container != null) {
            google_banner_container.setVisibility(isHome ? View.VISIBLE : View.GONE);
        }
        if (banner_container != null) {
            banner_container.setVisibility(isHome ? View.VISIBLE : View.GONE);
        }
    }

    private void changeObj() {
        String UFX_SERVICE_AVAILABLE = generalFunc.getJsonValueStr("UFX_SERVICE_AVAILABLE", obj_userProfile);
        isUfxServicesEnabled = !Utils.checkText(UFX_SERVICE_AVAILABLE) || (UFX_SERVICE_AVAILABLE != null && UFX_SERVICE_AVAILABLE.equalsIgnoreCase("Yes"));
    }

    public void openProfileFragment() {
        manageView(false);
        iswalletFragemnt = false;
        isbookingFragemnt = false;
        isProfileFragment = true;
        multiple_actionsRTL.collapse();

        menuMultipleActions.collapse();
        shadowView.setVisibility(View.GONE);
        eodLocationArea.setVisibility(View.GONE);

        mapbottomviewarea.setVisibility(View.GONE);
        selCarArea.setVisibility(View.GONE);
        llEarningView.setVisibility(View.GONE);
        mapviewarea.setVisibility(View.GONE);
        menuMultipleActions.setVisibility(View.GONE);
        multiple_actionsRTL.setVisibility(View.GONE);
        Toolbar.setVisibility(View.GONE);
        refreshImgView.setVisibility(View.GONE);

      /*  if (myProfileFragment == null) {
            myProfileFragment = new MyProfileFragment();
        }*/


        if (ServiceModule.isServiceProviderOnly()) {
            containerufx.setVisibility(View.VISIBLE);
            MainHeaderLayout.setVisibility(View.GONE);
        }

        if (bottomBtnpos == 4) {
            return;
        }
        openPageFrag(4,myProfileFragment);
        myProfileFragment.onResume();
        bottomBtnpos = 4;
    }


    public void openPageFrag(int position, Fragment fragToOpen) {
        if (MyApp.isAppKilled()) {
            return;
        }
        try {
            SkeletonViewHandler.getInstance().hideSkeletonView();
            containerView.setVisibility(View.VISIBLE);
            /* getSupportFragmentManager().beginTransaction().setCustomAnimations(leftAnim, rightAnim).replace(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId() : containerView.getId(), fragToOpen).commitNow();*/
            FragmentTransaction ft = getSupportFragmentManager().beginTransaction();
            if (inactiveFragment!=null){
                ft.hide(inactiveFragment);
            }
            ft.hide(myWalletFragment);
            ft.hide(myBookingFragment);
            ft.hide(myProfileFragment);
            ft.show(fragToOpen);
            ft.commitNow();

            PermissionHandlers.getInstance().setVisibility(View.GONE);
        } catch (Exception ignored) {
        }
    }

    public void openWalletFrgament() {
        manageView(false);
        iswalletFragemnt = true;
        isbookingFragemnt = false;
        isProfileFragment = false;
        eodLocationArea.setVisibility(View.GONE);
        menuMultipleActions.collapse();
        multiple_actionsRTL.collapse();

        shadowView.setVisibility(View.GONE);

        mapbottomviewarea.setVisibility(View.GONE);
        selCarArea.setVisibility(View.GONE);
        llEarningView.setVisibility(View.GONE);
        mapviewarea.setVisibility(View.GONE);
        menuMultipleActions.setVisibility(View.GONE);
        multiple_actionsRTL.setVisibility(View.GONE);
        Toolbar.setVisibility(View.GONE);
        refreshImgView.setVisibility(View.GONE);

        /*if (myWalletFragment == null) {
            myWalletFragment = new MyWalletFragment();
        }*/


        if (ServiceModule.isServiceProviderOnly()) {
            containerufx.setVisibility(View.VISIBLE);
            MainHeaderLayout.setVisibility(View.GONE);
        }

        if (bottomBtnpos == 3) {
            return;
        }
        openPageFrag(3, myWalletFragment);
        myWalletFragment.onResume();
        bottomBtnpos = 3;
    }


    public void openBookingFrgament() {
        manageView(false);

        isbookingFragemnt = true;
        iswalletFragemnt = false;
        isProfileFragment = false;

        menuMultipleActions.collapse();
        multiple_actionsRTL.collapse();

        eodLocationArea.setVisibility(View.GONE);

        shadowView.setVisibility(View.GONE);

        mapbottomviewarea.setVisibility(View.GONE);
        selCarArea.setVisibility(View.GONE);
        llEarningView.setVisibility(View.GONE);
        mapviewarea.setVisibility(View.GONE);
        menuMultipleActions.setVisibility(View.GONE);
        multiple_actionsRTL.setVisibility(View.GONE);
        Toolbar.setVisibility(View.GONE);
        refreshImgView.setVisibility(View.GONE);

       /* if (myBookingFragment == null) {
            myBookingFragment = new MyBookingFragment();
        }*/


        if (ServiceModule.isServiceProviderOnly()) {
            containerufx.setVisibility(View.VISIBLE);
            MainHeaderLayout.setVisibility(View.GONE);
        }

        if (bottomBtnpos == 2) {
            return;
        }
        openPageFrag(2, myBookingFragment);
        myBookingFragment.onCreateView(null, null, null);
        bottomBtnpos = 2;
    }

    private void showHeatMap() {

        if (!ServiceModule.IsMedicalAll && !ServiceModule.DeliverAllProduct && (ServiceModule.Taxi || ServiceModule.Delivery)) {
            heat_action.setVisibility(View.GONE);
            heat_actionRTL.setVisibility(View.GONE);
        } else {
            heat_action.setVisibility(View.GONE);
            heat_actionRTL.setVisibility(View.GONE);
        }
    }


    public void handleWorkAddress() {
        if (generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("Yes")) {

            if (generalFunc.getJsonValueStr("eSelectWorkLocation", obj_userProfile).equalsIgnoreCase("Fixed")) {
                String WORKLOCATION = generalFunc.retrieveValue(Utils.WORKLOCATION);
                if (!WORKLOCATION.equals("")) {
                    addressTxtView.setText(WORKLOCATION);
                    addressTxtViewufx.setText(WORKLOCATION);
                } else {
                    if (userLocation != null) {
                        getAddressFromLocation.setLocation(userLocation.getLatitude(), userLocation.getLongitude());
                        getAddressFromLocation.execute();
                        addressTxtView.setText(LBL_LOCATING_YOU_TXT);
                        addressTxtViewufx.setText(LBL_LOCATING_YOU_TXT);
                    }
                }
            } else {
                if (userLocation != null) {
                    getAddressFromLocation.setLocation(userLocation.getLatitude(), userLocation.getLongitude());
                    getAddressFromLocation.execute();
                    addressTxtView.setText(LBL_LOCATING_YOU_TXT);
                    addressTxtViewufx.setText(LBL_LOCATING_YOU_TXT);

                }

            }
        }
    }

    public void setRadiusVal() {

        String LBL_WITHIN = generalFunc.retrieveLangLBl("Within", "LBL_WITHIN");
        String LBL_RADIUS = generalFunc.retrieveLangLBl("Work Radius", "LBL_RADIUS");
        if (obj_userProfile != null && !generalFunc.getJsonValueStr("eUnit", obj_userProfile).equalsIgnoreCase("KMs")) {
            String LBL_MILE_DISTANCE_TXT = generalFunc.retrieveLangLBl("", "LBL_MILE_DISTANCE_TXT");
            radiusTxtView.setText(LBL_WITHIN + " " + radiusval + " " + LBL_MILE_DISTANCE_TXT + " " + LBL_RADIUS);
            radiusTxtViewufx.setText(LBL_WITHIN + " " + radiusval + " " + LBL_MILE_DISTANCE_TXT + " " + LBL_RADIUS);
        } else {
            String LBL_KM_DISTANCE_TXT = generalFunc.retrieveLangLBl("", "LBL_KM_DISTANCE_TXT");
            radiusTxtView.setText(LBL_WITHIN + " " + radiusval + " " + LBL_KM_DISTANCE_TXT + " " + LBL_RADIUS);
            radiusTxtViewufx.setText(LBL_WITHIN + " " + radiusval + " " + LBL_KM_DISTANCE_TXT + " " + LBL_RADIUS);
        }

    }


    private void isHailRideOptionEnabled() {

        if ((faredialog != null && faredialog.isShowing()) || eodLocationArea.getVisibility() == View.VISIBLE) {

            return_action.setVisibility(View.GONE);
            return_actionRTL.setVisibility(View.GONE);
            return;
        }

        enableEOD();

        boolean eDestinationMode = generalFunc.getJsonValueStr("eDestinationMode", obj_userProfile).equalsIgnoreCase("Yes");
        if (eDestinationMode) {
            return;
        }

        if (!HailEnableOnDriverStatus.equalsIgnoreCase("") && HailEnableOnDriverStatus.equalsIgnoreCase("No")) {
            hail_action.setVisibility(View.GONE);
            hail_actionRTL.setVisibility(View.GONE);
            return;
        }
        String eStatus = generalFunc.getJsonValueStr("eStatus", obj_userProfile);

        if (!eStatus.equalsIgnoreCase("inactive")) {
            ENABLE_HAIL_RIDES = generalFunc.getJsonValueStr("ENABLE_HAIL_RIDES", obj_userProfile);
            if (ENABLE_HAIL_RIDES.equalsIgnoreCase("Yes") && HailEnableOnDriverStatus.equalsIgnoreCase("Yes") && !ServiceModule.isDeliverAllOnly()) {
                hail_action.setVisibility(View.VISIBLE);
                hail_actionRTL.setVisibility(View.VISIBLE);
            } else {
                hail_action.setVisibility(View.GONE);
                hail_actionRTL.setVisibility(View.GONE);
            }
        } else {
            hail_action.setVisibility(View.GONE);
            hail_actionRTL.setVisibility(View.GONE);
        }


    }

    @Override
    public void Method1(Bundle bn) {
        super.Method1(bn);
    }

    public void setUserInfo() {
        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        changeObj();


        if (ServiceModule.isServiceProviderOnly()) {

            ufxonlineOfflineTxtView.setText(LBL_GO_ONLINE_TXT);
            ufxTitleonlineOfflineTxtView.setText(LBL_OFFLINE);

            String url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImage", obj_userProfile);
            generalFunc.checkProfileImage((SelectableRoundedImageView) findViewById(R.id.driverImgView), url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);

        } else {
            String url = CommonUtilities.PROVIDER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImage", obj_userProfile);

            generalFunc.checkProfileImage(userPicImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);
        }

        if (isCarChangeTxt) {
            String iDriverVehicleId = generalFunc.getJsonValueStr("iDriverVehicleId", obj_userProfile);
            setCarInfo(iDriverVehicleId);
        }


    }

    public void showMessageWithAction(View view, String message, final Bundle bn) {
        Snackbar snackbar = Snackbar.make(view, message, Snackbar.LENGTH_INDEFINITE).setAction(generalFunc.retrieveLangLBl("", "LBL_BTN_VERIFY_TXT"), view1 -> {
            bn.putString("msg", "DO_PHONE_VERIFY");
            new ActUtils(getActContext()).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_INFO_REQ_CODE);

        });
        snackbar.setActionTextColor(getActContext().getResources().getColor(R.color.verfiybtncolor));
        snackbar.setDuration(10000);
        snackbar.show();
    }


    public void setGeneralData() {
        HashMap<String, String> storeData = new HashMap<>();
        storeData.put(Utils.MOBILE_VERIFICATION_ENABLE_KEY, generalFunc.getJsonValueStr("MOBILE_VERIFICATION_ENABLE", obj_userProfile));
        storeData.put("LOCATION_ACCURACY_METERS", generalFunc.getJsonValueStr("LOCATION_ACCURACY_METERS", obj_userProfile));
        storeData.put("DRIVER_LOC_UPDATE_TIME_INTERVAL", generalFunc.getJsonValueStr("DRIVER_LOC_UPDATE_TIME_INTERVAL", obj_userProfile));
        storeData.put(Utils.REFERRAL_SCHEME_ENABLE, generalFunc.getJsonValueStr("REFERRAL_SCHEME_ENABLE", obj_userProfile));

        storeData.put(Utils.WALLET_ENABLE, generalFunc.getJsonValueStr("WALLET_ENABLE", obj_userProfile));
        storeData.put(Utils.REFERRAL_SCHEME_ENABLE, generalFunc.getJsonValueStr("REFERRAL_SCHEME_ENABLE", obj_userProfile));
        storeData.put(Utils.SMS_BODY_KEY, generalFunc.getJsonValueStr(Utils.SMS_BODY_KEY, obj_userProfile));
        storeData.put(Utils.PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT, generalFunc.getJsonValueStr("PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT", obj_userProfile));
        generalFunc.storeData(storeData);
    }

    public void setCarInfo(String iDriverVehicleId) {
        if (!iDriverVehicleId.equals("") && !iDriverVehicleId.equals("0")) {
            selectedcar = iDriverVehicleId;
            if (ServiceModule.IsTrackingProvider) {
                String vLicencePlateNo = generalFunc.getJsonValueStr("vLicencePlateNo", obj_userProfile);
                carNumPlateTxt.setText(vLicencePlateNo);

                String vMake = generalFunc.getJsonValueStr("vMake", obj_userProfile);
                String vModel = generalFunc.getJsonValueStr("vModel", obj_userProfile);
                carNameTxt.setText(vMake + " " + vModel);
            }
        }
    }


    public void configHeatMapView(boolean isShowNearByPassengers) {
        this.isShowNearByPassengers = isShowNearByPassengers;

        if (!mapOverlayList.isEmpty()) {
            for (int i = 0; i < mapOverlayList.size(); i++) {
                if (mapOverlayList.get(i) != null) {

                    mapOverlayList.get(i).setVisible(isShowNearByPassengers);

                    if (isShowNearByPassengers) {

                        //handle heat map view
                        if (isfirstZoom) {
                            isfirstZoom = false;
                            getMap().moveCamera(CameraUpdateFactory.zoomTo(14f));
                        }
                    } else {
                        userLocBtnImgView.performClick();
                    }
                }

            }
        }


        onCameraIdle();


    }

    @SuppressLint("MissingPermission")
    public void onMapReady(GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(this, R.raw.map_style));

        if (!MyApp.getInstance().isGetDetailCall && generalFunc.getJsonValueStr("ENABLE_SAFETY_TOOLS", obj_userProfile).equalsIgnoreCase("Yes")) {

            String iTripId_str = generalFunc.getJsonValue("iTripId", generalFunc.getJsonValueStr("TripDetails", obj_userProfile));

            String mFileName = "/safetyTools-" + iTripId_str + "-audio.wav";
            String filePath = getCacheDir() + mFileName;
            if (new File(filePath).exists()) {
                RecordingItem recordingItem = new RecordingItem();
                recordingItem.setFilePath(filePath);
                recordingItem.setName(mFileName);
                if (Utils.checkText(recordingItem.getName())) {
                    SafetyTools.getInstance().initiate(getActContext(), generalFunc, iTripId_str, "");
                    SafetyTools.getInstance().audioRecordingAvailableDialog(recordingItem);
                }
            }
        }

        this.gMap = googleMap;

        if (MyApp.getInstance().locationPermissionReq(false)) {

            getMap().setMyLocationEnabled(false);
            //  getMap().setPadding(0, 0, 0, Utils.dipToPixels(getActContext(), 50));
            getMap().getUiSettings().setTiltGesturesEnabled(false);
            getMap().getUiSettings().setZoomControlsEnabled(false);
            getMap().getUiSettings().setCompassEnabled(false);
            getMap().getUiSettings().setMyLocationButtonEnabled(false);
        }
        getMap().setOnCameraIdleListener(this);

        getMap().setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        GetLocationUpdates.getInstance().setTripStartValue(false, false, false, "");
        GetLocationUpdates.getInstance().startLocationUpdates(this, this);


    }

    public GoogleMap getMap() {
        return this.gMap;
    }

    public void goOnlineOffline(final boolean isGoOnline, final boolean isMessageShown) {

        handleNoLocationDial();
        if (noLocationAlertBox != null) {
            noLocationAlertBox.closeAlertBox();
            noLocationAlertBox = null;
        }
        if (isGoOnline && (userLocation == null || userLocation.getLatitude() == 0.0 || userLocation.getLongitude() == 0.0)) {
            noLocationAlertBox = generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("Application is not able to get your accurate location. Please try again. \n" + "If you still face the problem, please try again in open sky instead of closed area.", "LBL_NO_LOC_GPS_GENERAL"));
            onlineOfflineSwitch.setChecked(false);
            onlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
            onlineOfflineSwitch.setBackColorRes(android.R.color.white);

            ufxonlineOfflineSwitch.setChecked(false);
            ufxonlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
            ufxonlineOfflineSwitch.setBackColorRes(android.R.color.white);
            setOfflineState();
            return;
        }
        isHailRideOptionEnabled();

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "updateDriverStatus");
        parameters.put("iDriverId", generalFunc.getMemberId());

        if (isGoOnline) {
            parameters.put("Status", "Available");
            parameters.put("isUpdateOnlineDate", "true");
        } else {
            parameters.put("Status", "Not Available");
        }
        if (userLocation != null) {
            parameters.put("latitude", "" + userLocation.getLatitude());
            parameters.put("longitude", "" + userLocation.getLongitude());
        }

        parameters.put("isOnlineSwitchPressed", isOnlineOfflineSwitchCalled ? "true" : "");

        SkeletonViewHandler.getInstance().hideSkeletonView();
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(llEarningView, R.layout.shimmer_main_earnig);

        ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {

            SkeletonViewHandler.getInstance().hideSkeletonView();

            if (!isMessageShown) {
                return;
            }

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                String message = generalFunc.getJsonValue(Utils.message_str, responseString);

                if (message.equals("SESSION_OUT")) {
                    MyApp.getInstance().notifySessionTimeOut();
                    Utils.runGC();
                    return;
                }

                try {
                    obj_userProfile.put("vAvailability", isGoOnline ? "Available" : "Not Available");
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, obj_userProfile.toString());
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
                HashMap<String, String> storeData = new HashMap<>();
                storeData.put(Utils.DRIVER_DESTINATION_AVAILABLE_KEY, generalFunc.getJsonValue(Utils.DRIVER_DESTINATION_AVAILABLE_KEY, responseString));
                storeData.put(Utils.ENABLE_DRIVER_DESTINATIONS_KEY, generalFunc.getJsonValue(Utils.ENABLE_DRIVER_DESTINATIONS_KEY, responseString));
                generalFunc.storeData(storeData);

                isOnlyUfxServicesSelected = generalFunc.getJsonValue("isOnlyUfxServicesSelected", responseString).equalsIgnoreCase("Yes");
                selCarArea.setVisibility(ServiceModule.IsTrackingProvider ? View.VISIBLE : View.GONE);

                if (isDataAvail) {

                    HailEnableOnDriverStatus = generalFunc.getJsonValue("Enable_Hailtrip", responseString);

                    if (isGoOnline) {

                        if (message.equals("REQUIRED_MINIMUM_BALNCE")) {
                            isHailRideOptionEnabled();
                            if (getActContext() != null && !MainActivity_22.this.isFinishing()) {
                                MyUtils.buildLowBalanceMessage(getActContext(), generalFunc, obj_userProfile, generalFunc.getJsonValue("Msg", responseString), () -> {
                                    if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
                                        new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                                    } else {
                                        new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                                    }
                                });
                            }
                        }
                        setOnlineState();
                        generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_ONLINE_HEADER_TXT"));

                        if (ServiceModule.IsTrackingProvider) {
                            dialogShareRide();
                        }

                    } else {
                        workAreaLine.setVisibility(View.GONE);
                        if (!ServiceModule.DeliverAllProduct && ServiceModule.ServiceProvider) {
                            location_action.setVisibility(View.VISIBLE);
                            location_actionRTL.setVisibility(View.VISIBLE);
                        } else {
                            location_action.setVisibility(View.GONE);
                            location_actionRTL.setVisibility(View.GONE);
                        }
                        setOfflineState();
                        generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_OFFLINE_HEADER_TXT"));

                    }

                    if (generalFunc.getJsonValue("UberX_message", responseString) != null && !generalFunc.getJsonValue("UberX_message", responseString).equalsIgnoreCase("")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue("UberX_message", responseString)));
                    }
                } else {

                    if (generalFunc.getJsonValue("Enable_Hailtrip", responseString) != null & !generalFunc.getJsonValue("Enable_Hailtrip", responseString).equalsIgnoreCase("")) {

                        HailEnableOnDriverStatus = generalFunc.getJsonValue("Enable_Hailtrip", responseString);
                    }

                    isOnlineAvoid = true;
                    if (ServiceModule.isServiceProviderOnly()) {

                        if (isGoOnline) {
                            ufxonlineOfflineSwitch.setChecked(false);
                        } else {
                            iswitchClick = false;
                            ufxonlineOfflineSwitch.setChecked(true);
                        }

                    } else {
                        if (isGoOnline) {
                            onlineOfflineSwitch.setChecked(false);
                        } else {
                            iswitchClick = false;
                            onlineOfflineSwitch.setChecked(true);
                        }
                    }

                    Bundle bn = new Bundle();
                    bn.putString("msg", "" + message);
                    String eStatus = generalFunc.getJsonValueStr("eStatus", obj_userProfile);

                    if (!eStatus.equalsIgnoreCase("inactive")) {
                        Logger.d("SUBSCRIPTION", "2");

                        if (message.equals("DO_EMAIL_PHONE_VERIFY") || message.equals("DO_PHONE_VERIFY") || message.equals("DO_EMAIL_VERIFY")) {
                            accountVerificationAlert(generalFunc.retrieveLangLBl("", "LBL_ACCOUNT_VERIFY_ALERT_TXT"), bn, message);
                            return;
                        }
                    }

                    if (message.equalsIgnoreCase("LBL_DRIVER_DOC_EXPIRED")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl(generalFunc.getJsonValue(Utils.message_str, responseString), generalFunc.getJsonValue(Utils.message_str, responseString)));

                        goOnlineOffline(false, false);

                        return;
                    }

                    if (isGoOnline && !message.equalsIgnoreCase("PENDING_SUBSCRIPTION")) {
                        isHailRideOptionEnabled();
                    } else {
                        return_action.setVisibility(View.GONE);
                        return_actionRTL.setVisibility(View.GONE);
                    }

                    if (Utils.checkText(message) && message.equals("PENDING_SUBSCRIPTION") && isGoOnline) {
                        showSubscriptionStatusDialog(false, message);
                        return;
                    }

                    if (Utils.checkText(message) && message.equals("REQUIRED_MINIMUM_BALNCE") && isGoOnline) {

                        isHailRideOptionEnabled();
                        setOfflineState();
                        MyUtils.buildLowBalanceMessage(getActContext(), generalFunc, obj_userProfile, generalFunc.getJsonValue("Msg", responseString), () -> {
                            if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
                                new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                            } else {
                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            }
                        });
                        return;
                    }


                    if (Utils.checkText(message) && !message.equals("PENDING_SUBSCRIPTION")) {

                        if (message.equalsIgnoreCase("LBL_INACTIVE_CARS_MESSAGE_TXT")) {
                            hail_action.setVisibility(View.GONE);
                            hail_actionRTL.setVisibility(View.GONE);

                            return_action.setVisibility(View.GONE);
                            return_actionRTL.setVisibility(View.GONE);
                            GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                            alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", message));
                            alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                            alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                            alertBox.setBtnClickList(btn_id -> {

                                alertBox.closeAlertBox();
                                if (btn_id == 0) {
                                    new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                                }
                            });
                            alertBox.showAlertBox();
                        } else {
                            if (generalFunc.getJsonValue("isShowContactUs", responseString) != null && generalFunc.getJsonValue("isShowContactUs", responseString).equalsIgnoreCase("Yes")) {
                                final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
                                generateAlert.setCancelable(false);
                                generateAlert.setBtnClickList(btn_id -> {
                                    if (btn_id == 1) {
                                        Intent intent = new Intent(MainActivity_22.this, ContactUsActivity.class);
                                        intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
                                        startActivity(intent);
                                        // finish();

                                    }
                                });

                                generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                                generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                                generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));

                                generateAlert.showAlertBox();


                            } else {

                                if (message.equalsIgnoreCase("LBL_PENDING_MIXSUBSCRIPTION")) {
                                    hail_action.setVisibility(View.GONE);
                                    hail_actionRTL.setVisibility(View.GONE);

                                    return_action.setVisibility(View.GONE);
                                    return_actionRTL.setVisibility(View.GONE);

                                    showSubscriptionStatusDialog(false, message);
                                } else {
                                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl(generalFunc.getJsonValue(Utils.message_str, responseString), generalFunc.getJsonValue(Utils.message_str, responseString)));
                                }
                            }
                        }
                    }
                }

            } else {

                if (intCheck.isNetworkConnected()) {
                    isOnlineAvoid = true;

                    if (ServiceModule.isServiceProviderOnly()) {

                        if (isGoOnline) {
                            ufxonlineOfflineSwitch.setChecked(false);
                        } else {
                            iswitchClick = false;
                            ufxonlineOfflineSwitch.setChecked(true);
                        }

                    } else {
                        if (isGoOnline) {
                            onlineOfflineSwitch.setChecked(false);
                        } else {
                            iswitchClick = false;
                            onlineOfflineSwitch.setChecked(true);
                        }
                    }
                }

                generalFunc.showError();

            }
        });
        exeWebServer.setCancelAble(false);

    }

    private void dialogShareRide() {
        if (sheetDialog != null && sheetDialog.isShowing()) {
            return;
        }

        View dialogView = View.inflate(getActContext(), R.layout.dailog_track_trip_type, null);

        ImageView closeImg = dialogView.findViewById(R.id.closeImg);
        closeImg.setOnClickListener(v -> {
            if (sheetDialog != null) {
                sheetDialog.dismiss();
                sheetDialog = null;
            }
            onlineOfflineSwitch.setChecked(false);
        });
        MTextView txtTitle = dialogView.findViewById(R.id.txtTitle);
        MTextView txtRidePickup = dialogView.findViewById(R.id.txtRidePickup);
        MTextView txtRideDropOff = dialogView.findViewById(R.id.txtRideDropOff);

        txtTitle.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_SELECT_TRIP_TYPE_TXT"));
        txtRidePickup.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_PICKUP_TXT"));
        txtRideDropOff.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_TRIP_TYPE_DROPOFF_TXT"));

        LinearLayout llPickUpArea = dialogView.findViewById(R.id.llPickUpArea);
        LinearLayout llDropOffArea = dialogView.findViewById(R.id.llDropOffArea);
        selectArea(llPickUpArea, false);
        selectArea(llDropOffArea, false);

        AtomicReference<String> trackingTripType = new AtomicReference<>("");

        llPickUpArea.setOnClickListener(v -> {
            trackingTripType.set("Pickup");
            selectArea(llDropOffArea, false);
            selectArea(llPickUpArea, true);
        });
        llDropOffArea.setOnClickListener(v -> {
            trackingTripType.set("Dropoff");
            selectArea(llPickUpArea, false);
            selectArea(llDropOffArea, true);
        });

        AVLoadingIndicatorView loaderView = dialogView.findViewById(R.id.loaderView);
        loaderView.setVisibility(View.GONE);
        LinearLayout btnArea = dialogView.findViewById(R.id.btnArea);

        MButton btn_type2 = ((MaterialRippleLayout) dialogView.findViewById(R.id.btn_type2)).getChildView();
        btn_type2.setId(Utils.generateViewId());
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_TRACK_SERVICE_START_TRIP_TXT"));
        btn_type2.setOnClickListener(v -> {
            if (Utils.checkText(trackingTripType.get())) {
                initiateTrackingTrip(btnArea, loaderView, trackingTripType.get());
               /* generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_CONFIRM_START_TRIP_TXT"),
                        generalFunc.retrieveLangLBl("", "LBL_NO"),
                        generalFunc.retrieveLangLBl("", "LBL_YES"),
                        buttonId -> {
                            if (buttonId == 1) {
                                initiateTrackingTrip(btnArea, loaderView, trackingTripType.get());
                            }
                        });*/
            }
        });

        sheetDialog = new BottomSheetDialog(getActContext());
        sheetDialog.setContentView(dialogView);
        View bottomSheetView = sheetDialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(getResources().getColor(android.R.color.transparent));

        sheetDialog.setCancelable(false);
        LayoutDirection.setLayoutDirection(sheetDialog);
        Animation a = AnimationUtils.loadAnimation(getActContext(), R.anim.bottom_up);
        a.reset();
        bottomSheetView.clearAnimation();
        bottomSheetView.startAnimation(a);
        sheetDialog.show();
    }

    private void initiateTrackingTrip(LinearLayout btnArea, AVLoadingIndicatorView loaderView, String selTrackingTripType) {
        btnArea.setVisibility(View.GONE);
        loaderView.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "initiateTrackingTrip");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("TripType", selTrackingTripType);
        parameters.put("tStartLat", "" + userLocation.getLatitude());
        parameters.put("tStartLong", "" + userLocation.getLongitude());

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            loaderView.setVisibility(View.GONE);
            btnArea.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    MyApp.getInstance().refreshView(this, responseString);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void selectArea(LinearLayout views, boolean isSelect) {
        GradientDrawable drawable = (GradientDrawable) views.getBackground();
        if (isSelect) {
            drawable.setColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_98));
        } else {
            drawable.setColor(ContextCompat.getColor(getActContext(), R.color.white));
        }
    }

    public void showSubscriptionStatusDialog(boolean checkOnlineAvailability, String message) {

        String messageStr = message.equalsIgnoreCase("LBL_PENDING_MIXSUBSCRIPTION") ? message : "LBL_SUBSCRIPTION_REQ_SH_LBL";

        if (checkOnlineAvailability) {
            if (isDriverOnline) {

                setOfflineState();
                isOnlineAvoid = true;
                if (ServiceModule.isServiceProviderOnly()) {
                    ufxonlineOfflineSwitch.setChecked(false);
                    ufxonlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
                    ufxonlineOfflineSwitch.setBackColorRes(android.R.color.white);
                } else {
                    onlineOfflineSwitch.setChecked(false);
                    onlineOfflineSwitch.setThumbColorRes(android.R.color.holo_red_dark);
                    onlineOfflineSwitch.setBackColorRes(android.R.color.white);
                }

            }

        }

        if (customDialog != null) {
            customDialog.closeDialog();
        }


        customDialog = new CustomDialog(getActContext());
        customDialog.setDetails(generalFunc.retrieveLangLBl("", "LBL_SUBSCRIPTION_REQ_H_LBL"), generalFunc.retrieveLangLBl("", messageStr), generalFunc.retrieveLangLBl("", "LBL_SUBSCRIBE"), generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"), false, R.drawable.ic_menu_subscription, false, 1, false);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> new ActUtils(getActContext()).startAct(SubscriptionActivity.class));
        customDialog.setNegativeButtonClick(() -> {

        });
        customDialog.show();
    }

    public void setOfflineState() {
        isDriverOnline = false;
        if (ServiceModule.isServiceProviderOnly()) {
            ufxonlineOfflineTxtView.setText(LBL_GO_ONLINE_TXT);
            ufxTitleonlineOfflineTxtView.setText(LBL_OFFLINE);
        }

        hail_action.setVisibility(View.GONE);
        hail_actionRTL.setVisibility(View.GONE);

        return_action.setVisibility(View.GONE);
        return_actionRTL.setVisibility(View.GONE);
        removeEODTripData(false);
//        stopService(startUpdatingStatus);

        AppService.getInstance().executeService(AppService.Event.UNSUBSCRIBE, AppService.EventAction.CAB_REQUEST);

        NotificationScheduler.cancelReminder(MyApp.getInstance().getCurrentAct(), AlarmReceiver.class);

        MyApp.getInstance().setOfflineState();
    }

    public void setOnlineState() {

        isHailRideOptionEnabled();
        isDriverOnline = true;
        if (ServiceModule.isServiceProviderOnly()) {
            ufxonlineOfflineTxtView.setText(LBL_GO_OFFLINE_TXT);
            ufxTitleonlineOfflineTxtView.setText(LBL_ONLINE);
        }

        updateLocationToPubNub();

        AppService.getInstance().executeService(AppService.Event.SUBSCRIBE, AppService.EventAction.CAB_REQUEST);

        MyApp.getInstance().setOnlineState();
    }

    public void accountVerificationAlert(String message, final Bundle bn, String msgType) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 1) {
                generateAlert.closeAlertBox();
                bn.putString("msg", msgType);
                (new ActUtils(getActContext())).startActForResult(VerifyInfoActivity.class, bn, Utils.VERIFY_INFO_REQ_CODE);
            } else if (btn_id == 0) {
                generateAlert.closeAlertBox();
            }
        });
        generateAlert.setContentMessage("", message);
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_CANCEL_TRIP_TXT"));
        generateAlert.showAlertBox();
    }

    public void updateLocationToPubNub() {
        if (isDriverOnline && userLocation != null && userLocation.getLongitude() != 0.0 && userLocation.getLatitude() != 0.0) {
            if (lastPublishedLoc != null) {

                if (userLocation.distanceTo(lastPublishedLoc) < PUBSUB_PUBLISH_DRIVER_LOC_DISTANCE_LIMIT) {
                    return;
                } else {
                    lastPublishedLoc = userLocation;
                }

            } else {
                lastPublishedLoc = userLocation;
            }

            ArrayList<String> channelName = new ArrayList<>();
            channelName.add(generalFunc.getLocationUpdateChannel());
            AppService.getInstance().executeService(new EventInformation.EventInformationBuilder().setChanelList(channelName).setMessage(generalFunc.buildLocationJson(userLocation)).build(), AppService.Event.PUBLISH);
        }
    }

    public void getNearByPassenger(String radius, double center_lat, double center_long) {

        if (heatMapAsyncTask != null) {
            heatMapAsyncTask.cancel(true);
            heatMapAsyncTask = null;
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "loadPassengersLocation");
        parameters.put("Radius", radius);
        parameters.put("Latitude", String.valueOf(center_lat));
        parameters.put("Longitude", String.valueOf(center_long));

        this.heatMapAsyncTask = ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    JSONArray dataLocArr = generalFunc.getJsonArray(Utils.message_str, responseString);

                    ArrayList<LatLng> listTemp = new ArrayList<>();
                    ArrayList<LatLng> Online_listTemp = new ArrayList<>();
                    for (int i = 0; i < dataLocArr.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(dataLocArr, i);

                        String type = generalFunc.getJsonValueStr("Type", obj_temp);

                        double lat = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("Latitude", obj_temp));
                        double longi = GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValueStr("Longitude", obj_temp));

                        if (type.equalsIgnoreCase("Online")) {

                            String iUserId = generalFunc.getJsonValueStr("iUserId", obj_temp);

                            if (!onlinePassengerLocList.containsKey("ID_" + type + "_" + iUserId)) {
                                onlinePassengerLocList.put("ID_" + type + "_" + iUserId, "True");

                                Online_listTemp.add(new LatLng(lat, longi));
                            }


                        } else {
                            String iTripId = generalFunc.getJsonValueStr("iTripId", obj_temp);
                            if (!historyLocList.containsKey("ID_" + type + "_" + iTripId)) {
                                historyLocList.put("ID_" + type + "_" + iTripId, "True");

                                listTemp.add(new LatLng(lat, longi));
                            }
                        }
                    }

                    if (listTemp.size() > 0) {
                        mapOverlayList.add(getMap().addTileOverlay(new TileOverlayOptions().tileProvider(new HeatmapTileProvider.Builder().gradient(new Gradient(new int[]{Color.rgb(153, 0, 0), Color.WHITE}, new float[]{0.2f, 1.5f})).data(listTemp).build())));
                    }
                    if (Online_listTemp.size() > 0) {
                        mapOverlayList.add(getMap().addTileOverlay(new TileOverlayOptions().tileProvider(new HeatmapTileProvider.Builder().gradient(new Gradient(new int[]{Color.rgb(0, 51, 0), Color.WHITE}, new float[]{0.2f, 1.5f}, 1000)).data(Online_listTemp).build())));
                    }
                    if (!isShowNearByPassengers) {

                        configHeatMapView(false);
                    } else {
                        configHeatMapView(true);
                    }
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    public void configCarList(final boolean isCarUpdate, final String selectedCarId, final int position) {
        final HashMap<String, String> parameters = new HashMap<>();
        if (!isCarUpdate) {
            parameters.put("type", "LoadAvailableCars");
        } else {
            parameters.put("type", "SetDriverCarID");
            parameters.put("iDriverVehicleId", selectedCarId);
        }
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {

                    if (!isCarUpdate) {
                        LoadCarList(generalFunc.getJsonArray(Utils.message_str, responseString));
                    } else {

                        if (items_isHail_json.get(position).equalsIgnoreCase("Yes")) {
                            if (isDriverOnline) {
                                HailEnableOnDriverStatus = "Yes";
                                isHailRideOptionEnabled();
                            } else {
                                hail_action.setVisibility(View.GONE);
                                hail_actionRTL.setVisibility(View.GONE);
                            }

                        } else {
                            HailEnableOnDriverStatus = "No";
                            hail_action.setVisibility(View.GONE);
                            hail_actionRTL.setVisibility(View.GONE);
                        }
                        if (isDriverOnline) {
                            enableEOD();
                        } else {

                            return_action.setVisibility(View.GONE);
                            return_actionRTL.setVisibility(View.GONE);
                        }

                        selectedcar = selectedCarId;
                        if (ServiceModule.IsTrackingProvider) {
                            carNumPlateTxt.setText(generalFunc.getJsonValue("vLicencePlate", items_txt_car_json.get(position)));

                            String vMake = generalFunc.getJsonValue("vMake", items_txt_car_json.get(position));
                            String vModel = generalFunc.getJsonValue("vTitle", items_txt_car_json.get(position));
                            carNameTxt.setText(vMake + " " + vModel);
                        }
                        generalFunc.showMessage(generalFunc.getCurrentView(MainActivity_22.this), generalFunc.retrieveLangLBl("", "LBL_INFO_UPDATED_TXT"));
                    }

                } else {
                    String msg = generalFunc.getJsonValue(Utils.message_str, responseString);
                    if (msg.equalsIgnoreCase("LBL_INACTIVE_CARS_MESSAGE_TXT")) {
                        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", msg));
                        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CONTACT_US_TXT"));
                        alertBox.setBtnClickList(btn_id -> {

                            alertBox.closeAlertBox();
                            if (btn_id == 0) {
                                new ActUtils(getActContext()).startAct(ContactUsActivity.class);
                            }
                        });
                        alertBox.showAlertBox();
                    } else if (msg.equalsIgnoreCase("LBL_NO_CAR_AVAIL_FOR_RIDE_DELIVERY_TXT")) {
                        GenerateAlertBox alertBox = new GenerateAlertBox(getActContext());
                        alertBox.setContentMessage("", generalFunc.retrieveLangLBl("", msg));
                        alertBox.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"));
                        alertBox.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
                        alertBox.setBtnClickList(btn_id -> {
                            alertBox.closeAlertBox();
                            if (btn_id == 1) {
                                Bundle bn = new Bundle();
                                bn.putString("iDriverVehicleId", generalFunc.getJsonValueStr("iDriverVehicleId", obj_userProfile));
                                new ActUtils(getActContext()).startActWithData(ManageVehiclesActivity.class, bn);
                            }
                        });
                        alertBox.showAlertBox();
                    } else {
                        if ((msg.equalsIgnoreCase("PENDING_SUBSCRIPTION") || msg.equalsIgnoreCase("LBL_PENDING_MIXSUBSCRIPTION"))) {
                            showSubscriptionStatusDialog(true, msg);
                        } else {
                            generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", msg));
                        }
                    }
                }
            } else {
                generalFunc.showError();
            }
        });

    }


    public void LoadCarList(JSONArray array) {

        items_txt_car.clear();
        items_car_id.clear();
        items_txt_car_json.clear();
        items_isHail_json.clear();
        final ArrayList list = new ArrayList<>();
        for (int i = 0; i < array.length(); i++) {
            JSONObject obj_temp = generalFunc.getJsonObject(array, i);

            items_txt_car.add(generalFunc.getJsonValue("vMake", obj_temp) + " " + generalFunc.getJsonValue("vTitle", obj_temp));

            items_car_id.add(generalFunc.getJsonValueStr("iDriverVehicleId", obj_temp));
            items_txt_car_json.add(obj_temp.toString());
            items_isHail_json.add(generalFunc.getJsonValueStr("Enable_Hailtrip", obj_temp));

            HashMap<String, String> map = new HashMap<String, String>();
            map.put("car", items_txt_car.get(i).toString());
            map.put("iDriverVehicleId", items_car_id.get(i).toString());
            map.put("vLicencePlate", generalFunc.getJsonValueStr("vLicencePlate", obj_temp));
            list.add(map);
        }

        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());

        LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.dialog_selectcar_view, null);

        final MTextView vehTitleTxt = (MTextView) dialogView.findViewById(R.id.VehiclesTitleTxt);
        final MTextView mangeVehiclesTxt = (MTextView) dialogView.findViewById(R.id.mangeVehiclesTxt);
        final MTextView addVehiclesTxt = (MTextView) dialogView.findViewById(R.id.addVehiclesTxt);
        final ImageView cancel = (ImageView) dialogView.findViewById(R.id.cancel);
        final RecyclerView vehiclesRecyclerView = (RecyclerView) dialogView.findViewById(R.id.vehiclesRecyclerView);

        cancel.setOnClickListener(v -> list_car.dismiss());
        builder.setView(dialogView);
        vehTitleTxt.setText(generalFunc.retrieveLangLBl("Select Your Vehicles", "LBL_SELECT_CAR_TXT"));
        mangeVehiclesTxt.setText(generalFunc.retrieveLangLBl("", "LBL_MANAGE"));
        addVehiclesTxt.setText(generalFunc.retrieveLangLBl("ADD NEW", "LBL_ADD_VEHICLES"));

        ManageVehicleListAdapter adapter = new ManageVehicleListAdapter(getActContext(), list, generalFunc, selectedcar);
        vehiclesRecyclerView.setAdapter(adapter);
        adapter.setOnItemClickList(this);

        mangeVehiclesTxt.setOnClickListener(v -> {
            list_car.dismiss();

            Bundle bn = new Bundle();
            bn.putString("iDriverVehicleId", generalFunc.getJsonValueStr("iDriverVehicleId", obj_userProfile));

            new ActUtils(getActContext()).startActWithData(ManageVehiclesActivity.class, bn);
        });

        addVehiclesTxt.setOnClickListener(v -> {
            list_car.dismiss();
            Bundle bn = new Bundle();
            new ActUtils(getActContext()).startActWithData(AddVehicleActivity24.class, bn);
        });

        list_car = builder.create();
        LayoutDirection.setLayoutDirection(list_car);
        list_car.getWindow().setBackgroundDrawable(getActContext().getResources().getDrawable(R.drawable.all_roundcurve_card));
        if (!isFinishing()) {
            list_car.show();
        }

        list_car.setCancelable(false);
        final Button positiveButton = list_car.getButton(AlertDialog.BUTTON_POSITIVE);
        positiveButton.setTextColor(getResources().getColor(R.color.appThemeColor_1));
        final Button negativeButton = list_car.getButton(AlertDialog.BUTTON_NEGATIVE);
        negativeButton.setTextColor(getResources().getColor(R.color.black));
        list_car.setOnCancelListener(dialogInterface -> Utils.hideKeyboard(getActContext()));
    }

    @SuppressLint("MissingPermission")
    @Override
    public void onLocationUpdate(Location location) {

        try {
            if (location == null) {
                return;
            }

            /*if (isShowNearByPassengers) {
                return;
            }*/

            if (MyApp.getInstance().locationPermissionReq(false) && getMap() != null && !getMap().isMyLocationEnabled()) {
                getMap().setMyLocationEnabled(true);
            }

            this.userLocation = location;

            if (updateDirections != null) {
                updateDirections.changeUserLocation(location);
            }

            // CameraPosition cameraPosition = cameraForUserPosition();
            CameraUpdate cameraPosition = generalFunc.getCameraPosition(userLocation, gMap);

            if (cameraPosition != null) getMap().moveCamera(cameraPosition);

            if (!isFirstAddressLoaded) {
                getAddressFromLocation.setLocation(userLocation.getLatitude(), userLocation.getLongitude());
                getAddressFromLocation.execute();
                isFirstAddressLoaded = true;
            }

            if (isFirstLocation && generalFunc.getJsonValueStr("ePhoneVerified", obj_userProfile).equalsIgnoreCase("YES")) {

                isFirstLocation = false;

                String isGoOnline = generalFunc.retrieveValue(Utils.GO_ONLINE_KEY);

                if ((isGoOnline != null && !isGoOnline.equals("") && isGoOnline.equals("Yes"))) {
                    long lastTripTime = GeneralFunctions.parseLongValue(0, generalFunc.retrieveValue(Utils.LAST_FINISH_TRIP_TIME_KEY));
                    long currentTime = Calendar.getInstance().getTimeInMillis();

                    if ((currentTime - lastTripTime) < 60000) {
                        if (generalFunc.isLocationEnabled() && !ServiceModule.IsTrackingProvider) {
                            isOnlineOfflineSwitchCalled = true;
                            if (ServiceModule.isServiceProviderOnly()) {
                                iswitchClick = false;
                                ufxonlineOfflineSwitch.setChecked(true);
                            } else {
                                iswitchClick = false;
                                onlineOfflineSwitch.setChecked(true);
                            }
                        }
                    }
                    HashMap<String, String> storeData = new HashMap<>();
                    storeData.put(Utils.GO_ONLINE_KEY, "No");
                    storeData.put(Utils.LAST_FINISH_TRIP_TIME_KEY, "0");
                    generalFunc.storeData(storeData);
                }

                if (generalFunc.isLocationEnabled() && generalFunc.getJsonValueStr("vAvailability", obj_userProfile).equals("Available") && !isDriverOnline) {
                    isOnlineOfflineSwitchCalled = false;
                    iswitchClick = false;
                    if (ServiceModule.isServiceProviderOnly()) {
                        ufxonlineOfflineSwitch.setChecked(true);
                    } else {
                        if (PermissionHandlers.getInstance().getVisibilityPager() || ServiceModule.IsTrackingProvider) {
                            goOnlineOffline(false, false);
                        } else {
                            onlineOfflineSwitch.setChecked(true);
                        }
                    }
                }
            }
        } catch (Exception e) {

        }
    }


    public CameraPosition cameraForUserPosition() {
        double currentZoomLevel;

        // if (Utils.defaultZomLevel > currentZoomLevel) {
        currentZoomLevel = Utils.defaultZomLevel;
        //}
        if (userLocation != null) {
            CameraPosition cameraPosition = new CameraPosition.Builder().target(new LatLng(this.userLocation.getLatitude(), this.userLocation.getLongitude())).bearing(getMap().getCameraPosition().bearing).zoom((float) currentZoomLevel).build();

            return cameraPosition;
        } else {
            return null;
        }
    }

    public void openMenuProfile() {
        Bundle bn = new Bundle();
        bn.putBoolean("isDriverOnline", isDriverOnline);
        new ActUtils(getActContext()).startActForResult(MyProfileActivity.class, bn, Utils.MY_PROFILE_REQ_CODE);
    }


    public Context getActContext() {
        return MainActivity_22.this;
    }

    public void getWalletBalDetails() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);
        ApiHandler.execute(getActContext(), parameters, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    try {
                        JSONObject userProfileObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                        userProfileObj.put("user_available_balance", generalFunc.getJsonValue("MemberBalance", responseString));
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, userProfileObj.toString());

                        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                        changeObj();
                    } catch (Exception e) {

                    }
                }
            }
        });

    }

    @Override
    protected void onResume() {
        super.onResume();
        if (MyApp.getInstance().isGetDetailCall) {
            return;
        }

        isCarChangeTxt = false;
        managePendingCountArea();
        getWalletBalDetails();
        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        changeObj();

        handleWorkAddress();

        if (isDriverOnline) {
            isHailRideOptionEnabled();
        }

        obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        changeObj();

        if (ServiceModule.isServiceProviderOnly()) {
            getUserstatus();
        }
        if (!iswalletFragemnt && !isbookingFragemnt && !isProfileFragment) {
            getDriverStats(true);
        }

        setUserInfo();
        if (iswallet) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            changeObj();
            iswallet = false;
        }

        if (myBookingFragment != null && isbookingFragemnt) {
            myBookingFragment.onResume();
        }
    }

    private void getDriverStats(boolean isShow) {

        if (generalFunc.getJsonValueStr("eStatus", obj_userProfile).equalsIgnoreCase("inactive") || ServiceModule.IsTrackingProvider) {
            return;
        }

        if (isShow) {
            SkeletonViewHandler.getInstance().hideSkeletonView();
            SkeletonViewHandler.getInstance().ShowNormalSkeletonView(llEarningView, R.layout.shimmer_main_earnig);
        }

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverStats");

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            SkeletonViewHandler.getInstance().hideSkeletonView();

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    if (llEarningView.getVisibility() == View.INVISIBLE) {
                        llEarningView.setVisibility(View.VISIBLE);
                    }
                    txtYourEarning.setText(generalFunc.getJsonValue("vtitle", generalFunc.getJsonValue("your_earning", responseString)));
                    txtTotalEarning.setText(generalFunc.getJsonValue("value", generalFunc.getJsonValue("your_earning", responseString)));

                    String totalTrip = generalFunc.getJsonValue("total_trip", responseString);
                    txtBox1Title.setText(generalFunc.getJsonValue("vtitle", totalTrip));
                    txtBox1Value.setText(generalFunc.getJsonValue("value", totalTrip));

                    String avgRating = generalFunc.getJsonValue("avg_rating", responseString);
                    txtBox2Title.setText(generalFunc.getJsonValue("vtitle", avgRating));
                    txtBox2Value.setText(generalFunc.getJsonValue("value", avgRating));

                    String upcomingCount = generalFunc.getJsonValue("upcoming_count", responseString);
                    txtBox3Title.setText(generalFunc.getJsonValue("vtitle", upcomingCount));
                    txtBox3Value.setText(generalFunc.getJsonValue("value", upcomingCount));

                    String pendingCount = generalFunc.getJsonValue("pending_count", responseString);
                    txtBox4Title.setText(generalFunc.getJsonValue("vtitle", pendingCount));
                    txtBox4Value.setText(generalFunc.getJsonValue("value", pendingCount));

                    if (Utils.checkText(generalFunc.getJsonValue("PendingRideRequestCount", responseString)) && !generalFunc.getJsonValue("PendingRideRequestCount", responseString).equalsIgnoreCase("0")) {
                        MyUtils.setPendingBookingsCount(generalFunc.getJsonValue("PendingRideRequestCount", responseString));
                    } else {
                        MyUtils.setPendingBookingsCount("");
                    }
                    managePendingCountArea();
                    if (!Utils.checkText(Utils.getText(txtBox1Title))) {
                        llBox1Area.setVisibility(View.GONE);
                    } else {
                        if (Utils.checkText(generalFunc.getJsonValue("vBgColor", totalTrip))) {
                            ViewCompat.setBackgroundTintList(txtBox1Value, ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vBgColor", totalTrip))));
                        }
                        if (Utils.checkText(generalFunc.getJsonValue("vTextColor", totalTrip))) {
                            txtBox1Value.setTextColor(ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vTextColor", totalTrip))));
                        }
                    }
                    if (!Utils.checkText(Utils.getText(txtBox2Title))) {
                        llBox2Area.setVisibility(View.GONE);
                    } else {
                        if (Utils.checkText(generalFunc.getJsonValue("vBgColor", avgRating))) {
                            ViewCompat.setBackgroundTintList(txtBox2Value, ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vBgColor", avgRating))));
                        }
                        if (Utils.checkText(generalFunc.getJsonValue("vTextColor", avgRating))) {
                            txtBox2Value.setTextColor(ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vTextColor", avgRating))));
                        }
                    }

                    if (!Utils.checkText(Utils.getText(txtBox3Title))) {
                        llBox3Area.setVisibility(View.GONE);
                    } else {
                        if (Utils.checkText(generalFunc.getJsonValue("vBgColor", upcomingCount))) {
                            ViewCompat.setBackgroundTintList(txtBox3Value, ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vBgColor", upcomingCount))));
                        }
                        if (Utils.checkText(generalFunc.getJsonValue("vTextColor", upcomingCount))) {
                            txtBox3Value.setTextColor(ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vTextColor", upcomingCount))));
                        }
                    }
                    if (!Utils.checkText(Utils.getText(txtBox4Title))) {
                        llBox4Area.setVisibility(View.GONE);
                    } else {
                        if (Utils.checkText(generalFunc.getJsonValue("vBgColor", pendingCount))) {
                            ViewCompat.setBackgroundTintList(txtBox4Value, ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vBgColor", pendingCount))));
                        }
                        if (Utils.checkText(generalFunc.getJsonValue("vTextColor", pendingCount))) {
                            txtBox4Value.setTextColor(ColorStateList.valueOf(Color.parseColor(generalFunc.getJsonValue("vTextColor", pendingCount))));
                        }
                    }
                } else {

                    if (!generalFunc.getJsonValue(Utils.message_str, responseString).equalsIgnoreCase("")) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    } else {
                        generalFunc.showError();
                    }

                    llEarningView.setVisibility(View.INVISIBLE);
                }
            } else {
                llEarningView.setVisibility(View.INVISIBLE);
                generalFunc.showError();
            }
        });

    }

    @Override
    protected void onPause() {
        super.onPause();

    }

    public MyApp getApp() {
        return ((MyApp) getApplication());
    }

    public void removeLocationUpdates() {

        if (GetLocationUpdates.retrieveInstance() != null) {
            GetLocationUpdates.getInstance().stopLocationUpdates(this);
        }

        this.userLocation = null;
    }

    @Override
    protected void onDestroy() {
        try {
//            checkIsDriverOnline();
            removeLocationUpdates();
            if (getAddressFromLocation != null) {
                getAddressFromLocation.setAddressList(null);
                getAddressFromLocation = null;
            }

            if (gMap != null) {
                this.gMap.setOnCameraIdleListener(null);
                this.gMap = null;
            }

            if (heatMapAsyncTask != null) {
                heatMapAsyncTask.cancel(true);
                heatMapAsyncTask = null;
            }

            Utils.runGC();
        } catch (Exception ignored) {

        }
        super.onDestroy();
    }

    @Override
    public void onBackPressed() {
        if (faredialog != null && faredialog.isShowing()) {
            return;
        }
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                generateAlert.closeAlertBox();
                MyApp.getInstance().onTerminate();
                MainActivity_22.super.onBackPressed();
            }
        });

        generateAlert.setContentMessage(generalFunc.retrieveLangLBl("Exit App", "LBL_EXIT_APP_TITLE_TXT"), generalFunc.retrieveLangLBl("Are you sure you want to exit?", "LBL_WANT_EXIT_APP_TXT"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
        generateAlert.showAlertBox();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == Utils.MY_PROFILE_REQ_CODE && resultCode == RESULT_OK && data != null) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            changeObj();

            setUserInfo();

        } else if (requestCode == Utils.VERIFY_INFO_REQ_CODE && resultCode == RESULT_OK && data != null) {
            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            changeObj();
        } else if (requestCode == Utils.VERIFY_INFO_REQ_CODE) {

            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            changeObj();

        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == RESULT_OK && data != null) {

            obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
            changeObj();

        } else if (requestCode == Utils.REQUEST_CODE_GPS_ON) {
            handleNoLocationDial();
        } else if (requestCode == Utils.REQUEST_CODE_NETWOEK_ON) {
            handleNoNetworkDial();
        } else if (resultCode == RESULT_OK && data != null && data.hasExtra("isMoneyAddedOrTransferred")) {

            if (isDriverOnline && !ServiceModule.IsTrackingProvider) {
                if (ServiceModule.isServiceProviderOnly()) {
                    iswitchClick = false;
                    ufxonlineOfflineSwitch.setChecked(true);

                } else {
                    iswitchClick = false;
                    onlineOfflineSwitch.setChecked(true);
                }
            }
        }
        /*EndOfTheDay view click event*/
        else if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE && resultCode == RESULT_OK && data != null && gMap != null) {
            drawRoute(data);

        } else if (resultCode == RESULT_OK && (requestCode == SEL_CARD || requestCode == WEBVIEWPAYMENT || requestCode == TRANSFER_MONEY)) {

            if (myWalletFragment != null) {
                myWalletFragment.onActivityResult(requestCode, resultCode, data);
            }


        } else if (requestCode == 77 && resultCode == RESULT_OK && data != null) {
            if (data.getBooleanExtra("isOnline", false)) {
                goOnlineOffline(true, true);
                isOnlineOfflineSwitchCalled = false;
                MainActivity_22.super.onResume();
            } else {
                setOfflineState();
                isOnlineAvoid = true;
                onlineOfflineSwitch.setChecked(false);
                ufxonlineOfflineSwitch.setChecked(false);


            }

        }

    }

    /*EndOfTheDay Trip Implementation Start */

    public void isRouteDrawn() {
        hail_action.setVisibility(View.GONE);
        hail_actionRTL.setVisibility(View.GONE);

        return_action.setVisibility(View.GONE);
        return_actionRTL.setVisibility(View.GONE);
        heat_action.setVisibility(View.GONE);
        heat_actionRTL.setVisibility(View.GONE);

        multiple_actionsRTL.setVisibility(View.GONE);
        menuMultipleActions.setVisibility(View.GONE);

        handleMapAnimation();

        if (!updateDirections.data.hasExtra("eDestinationMode")) {
            if (faredialog == null) {
                openDestinationConfirmationDialog();
            } else if (faredialog != null && !faredialog.isShowing()) {
                ((MTextView) faredialog.findViewById(R.id.locationName)).setText(updateDirections.data.getStringExtra("Address"));
                ((MTextView) faredialog.findViewById(R.id.remainingDestTxt)).setText(getRemaningDest());
                faredialog.show();
            }
        } else {
            if (eodLocationArea.getVisibility() == View.GONE) {
                if (!iswalletFragemnt && !isbookingFragemnt && !isProfileFragment) {
                    eodLocationArea.setVisibility(View.VISIBLE);
                }
                addressTxt.setText(updateDirections.data.getStringExtra("Address"));
            }

        }
    }


    private void enableEOD() {
        boolean eDestinationMode = generalFunc.getJsonValueStr("eDestinationMode", obj_userProfile).equalsIgnoreCase("Yes");
        boolean ENABLE_DRIVER_DESTINATIONS = generalFunc.retrieveValue(Utils.ENABLE_DRIVER_DESTINATIONS_KEY).equalsIgnoreCase("Yes") && !eDestinationMode;
        return_action.setVisibility(ENABLE_DRIVER_DESTINATIONS ? View.VISIBLE : View.GONE);
        return_actionRTL.setVisibility(ENABLE_DRIVER_DESTINATIONS ? View.VISIBLE : View.GONE);
        JSONObject DriverDestinationData_obj = generalFunc.getJsonObject("DriverDestinationData", obj_userProfile);

        if (eDestinationMode && DriverDestinationData_obj != null && DriverDestinationData_obj.length() > 0) {
            Intent data = new Intent();
            data.putExtra("Latitude", generalFunc.getJsonValueStr("tDestinationStartedLatitude", DriverDestinationData_obj));
            data.putExtra("Longitude", generalFunc.getJsonValueStr("tDestinationStartedLongitude", DriverDestinationData_obj));
            data.putExtra("Address", generalFunc.getJsonValueStr("tDestinationStartedAddress", DriverDestinationData_obj));
            data.putExtra("eDestinationMode", generalFunc.getJsonValueStr("eDestinationMode", obj_userProfile));

            drawRoute(data);
        }

    }

    private void drawRoute(Intent data) {
        String destlat = data.getStringExtra("Latitude");
        String destlong = data.getStringExtra("Longitude");

        Location destLoc = new Location("temp");
        destLoc.setLatitude(GeneralFunctions.parseDoubleValue(0.0, destlat));
        destLoc.setLongitude(GeneralFunctions.parseDoubleValue(0.0, destlong));

        if (updateDirections == null) {
            updateDirections = new UpdateDirections(getActContext(), gMap, userLocation, destLoc);
        }

        if (updateDirections != null) {
            updateDirections.changeUserLocation(userLocation);
            updateDirections.setIntentData(data);
            if (!data.hasExtra("eDestinationMode")) {
                updateDirections.scheduleDirectionUpdate();
            } else {
                updateDirections.updateDirections();
            }
        }
    }


    public void handleMapAnimation() {

        try {
            LatLng sourceLocation = new LatLng(updateDirections.userLocation.getLatitude(), updateDirections.userLocation.getLongitude());
            LatLng destLocation = new LatLng(updateDirections.destinationLocation.getLatitude(), updateDirections.destinationLocation.getLongitude());

            PolyLineAnimator.getInstance().stopRouteAnim();

            LatLng fromLnt = new LatLng(sourceLocation.latitude, sourceLocation.longitude);
            LatLng toLnt = new LatLng(destLocation.latitude, destLocation.longitude);

            if (destMarker != null) {
                destMarker.remove();
                destMarker = null;
            }
            MarkerOptions markerOptions_destLocation = new MarkerOptions();
            markerOptions_destLocation.position(toLnt);
            markerOptions_destLocation.icon(BitmapDescriptorFactory.fromResource(R.drawable.pin_dest_select)).anchor(0.5f, 0.5f);
            destMarker = getMap().addMarker(markerOptions_destLocation);

            if (sourceMarker != null) {
                sourceMarker.remove();
                sourceMarker = null;
            }
            MarkerOptions markerOptions_sourceLocation = new MarkerOptions();
            markerOptions_sourceLocation.position(fromLnt);
            markerOptions_sourceLocation.icon(BitmapDescriptorFactory.fromResource(R.drawable.pin_source_select)).anchor(0.5f, 0.5f);
            sourceMarker = getMap().addMarker(markerOptions_sourceLocation);

            buildMarkers();

        } catch (Exception e) {
            // Backpress done by user then app crashes
            Logger.e("Exception", "::" + e.getMessage());
        }

    }

    private void buildMarkers() {
        {
            map.getView().getViewTreeObserver().addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
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

                        map.getView().getViewTreeObserver().removeOnGlobalLayoutListener(this);
                        DisplayMetrics metrics = new DisplayMetrics();
                        getWindowManager().getDefaultDisplay().getMetrics(metrics);
                        int height_ = metrics.heightPixels;
                        int width = metrics.widthPixels;
                        // Set Padding according to included bounds
                        int padding = 25; // offset from edges of the map in pixels
                        int height_NW;
                        if (faredialog != null && faredialog.isShowing()) {
                            height_NW = (height_ - height) - Utils.dipToPixels(getActContext(), 80);
                        } else {
                            height_NW = height_ - Utils.dipToPixels(getActContext(), 140) - Utils.dipToPixels(getActContext(), 80);
                        }


                        try {
                            /*  Method 3 */
                            padding = (int) (((height_NW + 5) * 0.100) / 2);
                            CameraUpdate cameraUpdate = CameraUpdateFactory.newLatLngBounds(builder.build(), width, (height_NW + 5), padding);
                            getMap().animateCamera(cameraUpdate);
                        } catch (Exception e) {
                            Logger.e("Exception", "::" + e.getMessage());
                            /*  Method 1 */
                            getMap().moveCamera(CameraUpdateFactory.newLatLngBounds(builder.build(), width, (height_NW + 5), padding));
                        }


                    }

                }
            });
        }
    }

    public ProgressBar mProgressBarEOD;
    public SlideButton slideButtonEOD;

    public void openDestinationConfirmationDialog() {
        if (faredialog != null) {
            faredialog.dismiss();
        }

        faredialog = new BottomSheetDialog(getActContext());

        View contentView = View.inflate(getActContext(), R.layout.design_end_day_start_trip, null);
        faredialog.setContentView(contentView);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        View bottomSheetView = faredialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        BottomSheetBehavior.from(bottomSheetView).setHideable(false);
        setCancelable(faredialog, false);


        mProgressBarEOD = (ProgressBar) faredialog.findViewById(R.id.mProgressBar);
        MTextView locationName = (MTextView) faredialog.findViewById(R.id.locationName);
        MTextView remainingDestTxt = (MTextView) faredialog.findViewById(R.id.remainingDestTxt);
        MTextView destDescriptionText = (MTextView) faredialog.findViewById(R.id.destDescriptionText);
        ImageView iv_close = (ImageView) faredialog.findViewById(R.id.iv_close);
        MButton btn_type2 = ((MaterialRippleLayout) faredialog.findViewById(R.id.btn_type2)).getChildView();
        int submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);
        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_START_DEST_MODE_TXT"));
        btn_type2.setVisibility(View.GONE);

        destDescriptionText.setText(generalFunc.retrieveLangLBl("", "LBL_START_DESTINATION_TRIP"));
        slideButtonEOD = faredialog.findViewById(R.id.slideButton);
        slideButtonEOD.setButtonText("  " + generalFunc.retrieveLangLBl("", "LBL_START_DEST_MODE_TXT"));
        slideButtonEOD.setBackgroundColor(getResources().getColor(R.color.appThemeColor_1));
        slideButtonEOD.onClickListener(generalFunc.isRTLmode(), isCompleted -> {
            if (isCompleted) {
                startDriverDestination(faredialog, updateDirections.data);
                new Handler().postDelayed(() -> slideButtonEOD.resetButtonView(slideButtonEOD.btnText.getText().toString()), 2000);
            }
        });

        height = Utils.dpToPx(380, getActContext());
        mBehavior.setPeekHeight(height);

        mProgressBarEOD.getIndeterminateDrawable().setColorFilter(getActContext().getResources().getColor(R.color.appThemeColor_2), android.graphics.PorterDuff.Mode.SRC_IN);
        mProgressBarEOD.setIndeterminate(true);
        mProgressBarEOD.setVisibility(View.VISIBLE);
        locationName.setText(updateDirections.data.getStringExtra("Address"));
        remainingDestTxt.setText(getRemaningDest());

        btn_type2.setOnClickListener(v -> confirmDestination(faredialog));

        iv_close.setOnClickListener(v -> {
            faredialog.dismiss();
            removeEODTripData(true);
        });

        faredialog.setOnDismissListener(dialog -> {
        });
        faredialog.show();
    }

    private String getRemaningDest() {
        String destAddressSHLbl = "";
        int MAX_DRIVER_DESTINATIONS = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValueStr("MAX_DRIVER_DESTINATIONS", obj_userProfile));
        int iDestinationCount = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValueStr("iDestinationCount", obj_userProfile));
        String remainingDestCount = generalFunc.convertNumberWithRTL("" + (MAX_DRIVER_DESTINATIONS - iDestinationCount));
        destAddressSHLbl = generalFunc.retrieveLangLBl("", "LBL_DESTINATION") + ": " + remainingDestCount + " " + generalFunc.retrieveLangLBl("", "LBL_REMAINIG_TXT");
        return destAddressSHLbl;

    }

    public void removeEODTripData(boolean resetHail) {
        if (sourceMarker != null) {
            sourceMarker.remove();
            sourceMarker = null;
        }

        if (destMarker != null) {
            destMarker.remove();
            destMarker = null;
        }


        if (updateDirections != null) {
            updateDirections.releaseTask();
            updateDirections = null;
        }

        eodLocationArea.setVisibility(View.GONE);
        showHeatMap();

        if (resetHail) {
            isHailRideOptionEnabled();
        }

        if (gMap != null) gMap.clear();

        if (generalFunc.isRTLmode()) {
            multiple_actionsRTL.setVisibility(ServiceModule.IsTrackingProvider || bottomBtnpos != 1 ? View.GONE : View.VISIBLE);
            menuMultipleActions.setVisibility(View.GONE);
        } else {
            menuMultipleActions.setVisibility(ServiceModule.IsTrackingProvider || bottomBtnpos != 1 ? View.GONE : View.VISIBLE);
            multiple_actionsRTL.setVisibility(View.GONE);
        }

    }

    public void confirmDestination(BottomSheetDialog dialog1) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActContext());
        builder.setTitle("");
        String message = generalFunc.retrieveLangLBl("", "LBL_START_DESTINATION_TRIP");
        builder.setMessage(message);

        builder.setPositiveButton(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"), (dialog, which) -> {

        });
        builder.setNegativeButton(generalFunc.retrieveLangLBl("", "LBL_NO"), (dialog, which) -> {
        });

        confirmDialog = builder.create();
        LayoutDirection.setLayoutDirection(confirmDialog);
        confirmDialog.show();

        confirmDialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(view -> startDriverDestination(dialog1, updateDirections.data));

        confirmDialog.getButton(AlertDialog.BUTTON_NEGATIVE).setOnClickListener(view -> confirmDialog.dismiss());
    }

    public void startDriverDestination(BottomSheetDialog dialog1, Intent data) {
        String destlat = data.getStringExtra("Latitude");
        String destlong = data.getStringExtra("Longitude");
        String destAddress = data.getStringExtra("Address");

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "startDriverDestination");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("tRootDestLatitudes", updateDirections != null ? TextUtils.join(",", updateDirections.lattitudeList) : "");
        parameters.put("tRootDestLongitudes", updateDirections != null ? TextUtils.join(",", updateDirections.longitudeList) : "");
        parameters.put("tAdress", destAddress);
        parameters.put("eStatus", "Active");
        parameters.put("tDriverDestLatitude", destlat);
        parameters.put("tDriverDestLongitude", destlong);

        ApiHandler.execute(getActContext(), parameters, true, true, generalFunc, responseString -> {

            if (faredialog != null) {
                faredialog.dismiss();
                faredialog = null;
            }

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                    changeObj();
                    dialog1.dismiss();

                    JSONObject DriverDestinationData_obj = generalFunc.getJsonObject("DriverDestinationData", obj_userProfile);
                    data.putExtra("Latitude", generalFunc.getJsonValueStr("tDestinationStartedLatitude", DriverDestinationData_obj));
                    data.putExtra("Longitude", generalFunc.getJsonValueStr("tDestinationStartedLongitude", DriverDestinationData_obj));
                    data.putExtra("Address", generalFunc.getJsonValueStr("tDestinationStartedAddress", DriverDestinationData_obj));
                    data.putExtra("eDestinationMode", generalFunc.getJsonValueStr("eDestinationMode", obj_userProfile));

                    if (updateDirections != null) {
                        updateDirections.setIntentData(data);
                        updateDirections.scheduleDirectionUpdate();
                    }

                    addressTxt.setText(data.getStringExtra("Address"));

                    if (!iswalletFragemnt && !isbookingFragemnt && !isProfileFragment) {
                        eodLocationArea.setVisibility(View.VISIBLE);
                    }
                    handleMapAnimation();

                } else {

                    String message_str = generalFunc.getJsonValue(Utils.message_str, responseString);
                    String message = generalFunc.retrieveLangLBl(message_str, message_str);
                    generalFunc.showGeneralMessage("", message);
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void buildMsgOnEODCancelRequests() {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getActContext());
        generateAlert.setCancelable(false);
        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                CancelDriverDestination();
            }
        });
        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_END_DESTINATION_TRIP"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_YES_TXT"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_BTN_NO_TXT"));
        generateAlert.showAlertBox();
    }

    public void CancelDriverDestination() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CancelDriverDestination");
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, true, true, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    generalFunc.storeData(Utils.USER_PROFILE_JSON, message);
                    obj_userProfile = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
                    changeObj();

                    generalFunc.storeData(Utils.DRIVER_DESTINATION_AVAILABLE_KEY, generalFunc.getJsonValue(Utils.DRIVER_DESTINATION_AVAILABLE_KEY, message));
                    removeEODTripData(true);
                } else {
                    generalFunc.showError();
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void setCancelable(Dialog dialogview, boolean cancelable) {
        final Dialog dialog = dialogview;
        View touchOutsideView = dialog.getWindow().getDecorView().findViewById(R.id.touch_outside);
        View bottomSheetView = dialog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        dialog.setCancelable(cancelable);

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

    /*EndOfTheDay Trip Implementation End */
    @Override
    public void onItemClick(int position, int viewClickId) {
        list_car.dismiss();
        String selected_carId = items_car_id.get(position);
        configCarList(true, selected_carId, position);
    }

    public void handleNoNetworkDial() {
        String eStatus = generalFunc.getJsonValueStr("eStatus", obj_userProfile);
        if (!eStatus.equalsIgnoreCase("inactive")) {
            if (intCheck.isNetworkConnected() && intCheck.check_int()) {
                handleNoLocationDial();
            }
        }
    }

    public void handleNoLocationDial() {
        try {
            if (!generalFunc.isLocationEnabled() && isDriverOnline) {
                if (ServiceModule.isServiceProviderOnly()) {
                    ufxonlineOfflineSwitch.setChecked(false);
                } else {
                    onlineOfflineSwitch.setChecked(false);
                }
            }
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (generalFunc.getJsonValueStr("PROVIDER_AVAIL_LOC_CUSTOMIZE", obj_userProfile).equalsIgnoreCase("Yes") && generalFunc.getJsonValueStr("eSelectWorkLocation", obj_userProfile).equalsIgnoreCase("Fixed")) {
            String WORKLOCATION = generalFunc.retrieveValue(Utils.WORKLOCATION);
            if (!WORKLOCATION.equals("")) {
                addressTxtView.setText(WORKLOCATION);
            } else {
                addressTxtView.setText(address);
                addressTxtViewufx.setText(address);
            }
        } else {
            addressTxtView.setText(address);
            addressTxtViewufx.setText(address);
        }
    }


    public void onClick(View view) {
        Intent intent = new Intent("BUTTONHANDLING");
        intent.putExtra("id", view.getId());
        intent.putExtra("name", view.getResources().getResourceName(view.getId()));
        sendBroadcast(intent); // NOSONAR
        Utils.hideKeyboard(MainActivity_22.this);
        if (view.getId() == userLocBtnImgView.getId()) {
            if (userLocation == null) {
                return;
            }

            CameraUpdate cameraPosition = generalFunc.getCameraPosition(userLocation, gMap);
            if (cameraPosition != null) {
                getMap().animateCamera(cameraPosition);
            }
        } else if (view.getId() == heat_action.getId() || view.getId() == heat_actionRTL.getId()) {
            menuMultipleActions.collapse();
            multiple_actionsRTL.collapse();
            if (userLocation == null) {
                return;
            }
            isfirstZoom = true;
            configHeatMapView(isShowNearByPassengers ? false : true);
        } else if (view.getId() == changeCar_action.getId() || view.getId() == changeCar_actionRTL.getId()) {
            menuMultipleActions.collapse();
            multiple_actionsRTL.collapse();
            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                generalFunc.showMessage(userLocBtnImgView, generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            } else {
                configCarList(false, "", 0);
            }
        } else if (view.getId() == hail_action.getId() || view.getId() == hail_actionRTL.getId()) {
            menuMultipleActions.collapse();
            multiple_actionsRTL.collapse();
            if (!intCheck.isNetworkConnected() && !intCheck.check_int()) {
                generalFunc.showMessage(userLocBtnImgView, generalFunc.retrieveLangLBl("No Internet Connection", "LBL_NO_INTERNET_TXT"));
            } else {
                if (!isBtnClick) {
                    isBtnClick = true;
                    checkHailType();
                }
            }
        } else if (view.getId() == pendingarea.getId()) {
            Bundle bn = new Bundle();
            bn.putString("isView", "pending");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn);
        } else if (view.getId() == upcomginarea.getId()) {

            Bundle bn = new Bundle();
            bn.putString("isView", "upcoming");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn);

        } else if (view.getId() == location_action.getId() || view.getId() == location_actionRTL.getId()) {
            menuMultipleActions.collapse();
            multiple_actionsRTL.collapse();


            new ActUtils(getActContext()).startAct(WorkLocationActivity.class);
        } else if (view.getId() == refreshImgView.getId()) {
            isFirstAddressLoaded = false;
            onLocationUpdate(GetLocationUpdates.getInstance().getLastLocation());
            getUserstatus();
        } else if (view.getId() == rewardsBtn.getId()) {
            new ActUtils(getActContext()).startAct(DriverRewardActivity.class);
        } else if (view.getId() == imageradiusufx.getId()) {
            new ActUtils(getActContext()).startAct(WorkLocationActivity.class);
        }
        /*EndOfTheDay Click events */
        else if (view.getId() == removeEodTripArea.getId()) {
            MyUtils.setBounceAnimation(getActContext(), removeEodTripArea, R.anim.bounce_interpolator, this::buildMsgOnEODCancelRequests);

        } else if (view.getId() == return_action.getId() || view.getId() == return_actionRTL.getId()) {

            menuMultipleActions.collapse();
            multiple_actionsRTL.collapse();

            if (generalFunc.retrieveValue(Utils.DRIVER_DESTINATION_AVAILABLE_KEY).equalsIgnoreCase("Yes")) {
                Bundle bn = new Bundle();
                bn.putString("requestType", "endOfDayTrip");
                bn.putString("locationArea", "dest");

                if (userLocation != null) {
                    bn.putDouble("lat", userLocation.getLatitude());
                    bn.putDouble("long", userLocation.getLongitude());
                }
                new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class, bn, Utils.SEARCH_PICKUP_LOC_REQ_CODE);
            } else {
                String message = generalFunc.retrieveLangLBl("", "LBL_DRIVER_DEST_LIMIT_REACHED") + " " + GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValueStr("MAX_DRIVER_DESTINATIONS", obj_userProfile)) + " " + generalFunc.retrieveLangLBl("", "LBL_FOR_A_DAY");
                Snackbar snackbar = Snackbar.make(view, message, Snackbar.LENGTH_INDEFINITE);
                snackbar.setActionTextColor(getActContext().getResources().getColor(R.color.verfiybtncolor));
                snackbar.setDuration(10000);
                snackbar.show();
            }
        } else if (view.getId() == notificationImg.getId()) {
            multiple_actionsRTL.collapse();
            menuMultipleActions.collapse();
            shadowView.setVisibility(View.GONE);
            new ActUtils(getActContext()).startAct(NotificationActivity.class);
        } else if (view.getId() == pendingBookingArea.getId()) {
            Bundle bn = new Bundle();
            bn.putString("isView", "pending");
            new ActUtils(getActContext()).startActWithData(BookingsActivity.class, bn);


        } else if (view.getId() == userPicImgView.getId()) {
            multiple_actionsRTL.collapse();
            menuMultipleActions.collapse();
            shadowView.setVisibility(View.GONE);
            addBottomBar.manualClickView(3);
        } else if (view.getId() == continueBtn.getId()) {
            newBookingNotificationArea.setVisibility(View.GONE);
            Intent intentBooking = new Intent(this, BookingsActivity.class);
            intentBooking.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
            intentBooking.putExtra("isView", "pending");
            startActivity(intentBooking);
        }
    }

    public void showBookingNotification(JSONObject obj_msg) {
        if (!isbookingFragemnt && !iswalletFragemnt && !isProfileFragment) {
            playNotificationSound(generalFunc.getJsonValueStr("vSound", obj_msg));
            managePendingCountArea();
            newBookingArrivedTxtView.setText(generalFunc.getJsonValueStr("vTitle", obj_msg));
            newBookingNotificationArea.setVisibility(View.VISIBLE);
            if (newOrderHandler != null) {
                newOrderHandler.removeCallbacks(newOrderRunnable);
            }
            newOrderHandler = new Handler();

            newOrderHandler.postDelayed(newOrderRunnable, 6000);
        }

    }

    public void showAccountActivated(JSONObject obj_msg) {
        String vTitle = generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vTitle", obj_msg));
        generalFunc.showGeneralMessage("", vTitle, "", generalFunc.retrieveLangLBl("", "LBL_BTN_OK_TXT"), new GeneralFunctions.OnAlertButtonClickListener() {
            @Override
            public void onAlertButtonClick(int i) {
                MyApp.getInstance().restartWithGetDataApp();
            }
        });
    }

    private Animation setPendingAreaAnimation() {
        if (anim == null) {
            anim = new AlphaAnimation(1.0f, 0.0f);
        }
        anim.setDuration(400); //You can manage the blinking time with this parameter
        anim.setStartOffset(1000);
        anim.setRepeatMode(Animation.REVERSE);
        anim.setRepeatCount(Animation.INFINITE);
        return anim;
    }

    private void StopPendingAreaAnimation() {
        if (isPendingAreaAnimationRunning()) {
            anim.cancel();
            anim = null;
        }
    }

    private boolean isPendingAreaAnimationRunning() {
        return anim != null && anim.hasStarted() && !anim.hasEnded();
    }

    private void managePendingCountArea() {
        if (Utils.checkText(MyUtils.getPendingBookingsCount()) && !MyUtils.getPendingBookingsCount().equalsIgnoreCase("0")) {
            pendingBookingCount.setText(MyUtils.getPendingBookingsCount());
            pendingBookingCount.setAnimation(setPendingAreaAnimation());
            pendingBookingCount.setVisibility(View.VISIBLE);
        } else {
            StopPendingAreaAnimation();
            pendingBookingCount.setVisibility(View.GONE);
        }
    }

    public void manageHome() {
        if (pendingBookingCount != null) {
            managePendingCountArea();
        }
        manageView(true);
        iswalletFragemnt = false;
        isbookingFragemnt = false;
        isProfileFragment = false;
        if (bottomBtnpos == 1) {
            return;
        }

        String eStatus = generalFunc.getJsonValueStr("eStatus", obj_userProfile);
        if (eStatus.equalsIgnoreCase("inactive")) {
            mapbottomviewarea.setVisibility(View.GONE);
            Toolbar.setVisibility(View.VISIBLE);
            llEarningView.setVisibility(View.GONE);
            mapviewarea.setVisibility(View.GONE);
            menuMultipleActions.setVisibility(View.GONE);
            multiple_actionsRTL.setVisibility(View.GONE);
            return_action.setVisibility(View.GONE);
            return_actionRTL.setVisibility(View.GONE);
            headerLogo.setVisibility(View.VISIBLE);
            onlineOfflineSwitch.setVisibility(View.GONE);
            selCarArea.setVisibility(View.GONE);
            headerLogoride.setVisibility(View.VISIBLE);
            if (inactiveFragment == null) {
                inactiveFragment = new InactiveFragment();
                getSupportFragmentManager().beginTransaction().add(ufxarea.getVisibility() == View.VISIBLE ? containerufx.getId():containerView.getId(), inactiveFragment, "1").commit();
            }
            if (ServiceModule.isServiceProviderOnly()) {
                activearea.setVisibility(View.GONE);
                MainHeaderLayout.setVisibility(View.VISIBLE);
            }

            openPageFrag(1, inactiveFragment);
            bottomBtnpos = 1;
            refreshImgView.setVisibility(View.GONE);
        } else {

            PermissionHandlers.getInstance().initiatePermissionHandler();

            if (generalFunc.isRTLmode()) {
                multiple_actionsRTL.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.VISIBLE);
                menuMultipleActions.setVisibility(View.GONE);
            } else {
                menuMultipleActions.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.VISIBLE);
                multiple_actionsRTL.setVisibility(View.GONE);
            }

            Toolbar.setVisibility(View.VISIBLE);
            onlineOfflineSwitch.setVisibility(View.VISIBLE);
            selCarArea.setVisibility(ServiceModule.IsTrackingProvider ? View.VISIBLE : View.GONE);
            headerLogoride.setVisibility(View.GONE);
            if (ServiceModule.isServiceProviderOnly()) {
                ufxarea.setVisibility(View.VISIBLE);
            }
            userPicImgView.setVisibility(View.VISIBLE);


            if (ServiceModule.isServiceProviderOnly()) {
                containerufx.setVisibility(View.GONE);
                MainHeaderLayout.setVisibility(View.VISIBLE);
                refreshImgView.setVisibility(View.VISIBLE);
            }
            headerLogo.setVisibility(View.GONE);

            if (isDriverOnline) {
                isHailRideOptionEnabled();
            }
            containerView.setVisibility(View.GONE);
            mapbottomviewarea.setVisibility(View.VISIBLE);
            llEarningView.setVisibility(ServiceModule.IsTrackingProvider ? View.GONE : View.VISIBLE);
            getDriverStats(false);
            mapviewarea.setVisibility(View.VISIBLE);

            handleNoLocationDial();
            if (updateDirections != null) {
                isRouteDrawn();
            }
            bottomBtnpos = 1;
        }

    }

    @Override
    public void onCameraIdle() {
        if (this.userLocation == null || !isShowNearByPassengers) {
            return;
        }

        VisibleRegion vr = getMap().getProjection().getVisibleRegion();
        final LatLng mainCenter = vr.latLngBounds.getCenter();
        final LatLng southwest = vr.latLngBounds.southwest;

        final double radius_map = GeneralFunctions.calculationByLocation(mainCenter.latitude, mainCenter.longitude, southwest.latitude, southwest.longitude, "KM");

        boolean isWithin1m = radius_map > this.radius_map + 0.001;

        if (isWithin1m)
            getNearByPassenger(String.valueOf(radius_map), mainCenter.latitude, mainCenter.longitude);

        this.radius_map = radius_map;

    }

    public void getUserstatus() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetUserStats");
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, false, true, generalFunc, responseString -> {
            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    pendingjobValTxtView.setText(generalFunc.getJsonValue("Pending_Count", responseString));
                    upcomingjobValTxtView.setText(generalFunc.getJsonValue("Upcoming_Count", responseString));
                    radiusval = generalFunc.getJsonValue("Radius", responseString);
                    setRadiusVal();
                }
            }
        });
    }

    private void checkHailType() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "CheckVehicleEligibleForHail");
        parameters.put("iDriverId", generalFunc.getMemberId());

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    isBtnClick = false;
                    if (userLocation != null) {
                        Bundle bn = new Bundle();
                        bn.putString("userLocation", userLocation + "");
                        bn.putDouble("lat", userLocation.getLatitude());
                        bn.putDouble("long", userLocation.getLongitude());
                        new ActUtils(getActContext()).startActWithData(HailActivity.class, bn);
                    }

                } else {
                    isBtnClick = false;
                    String message = generalFunc.getJsonValue(Utils.message_str, responseString);
                    if (message.equals("REQUIRED_MINIMUM_BALNCE")) {
                        isHailRideOptionEnabled();
                        MyUtils.buildLowBalanceMessage(getActContext(), generalFunc, obj_userProfile, generalFunc.getJsonValue("Msg", responseString), () -> {
                            if (generalFunc.getJsonValueStr("APP_PAYMENT_MODE", obj_userProfile).equalsIgnoreCase("Cash")) {
                                new ActUtils(getActContext()).startAct(ContactUsActivity.class);

                            } else {
                                new ActUtils(getActContext()).startAct(MyWalletActivity.class);
                            }
                        });
                        return;
                    }
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                isBtnClick = false;
            }
        });
    }

    Runnable newOrderRunnable = () -> {
        newBookingNotificationArea.setVisibility(View.GONE);
    };

    @Override
    public void onBottomTabSelected(int position) {
        addBottomBar.setBottomFrg(position);
    }

}