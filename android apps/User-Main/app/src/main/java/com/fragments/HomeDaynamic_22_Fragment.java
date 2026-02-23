package com.fragments;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.location.Location;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatDialog;
import androidx.appcompat.content.res.AppCompatResources;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.drawable.DrawableCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import androidx.core.widget.NestedScrollView;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.vectordrawable.graphics.drawable.VectorDrawableCompat;
import androidx.viewpager.widget.ViewPager;

import com.ViewPagerCards.HomeCardPagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.act.MainActivity;
import com.act.RequestBidInfoActivity;
import com.act.SearchLocationActivity;
import com.act.SearchServiceActivity;
import com.act.UberXHomeActivity;
import com.act.UberXSelectServiceActivity;
import com.act.deliverAll.BuyAnythingActivity;
import com.act.deliverAll.EditCartActivity;
import com.activity.ParentActivity;
import com.adapter.files.UberXCategory22Adapter;
import com.adapter.files.UberXCategoryMoreAdapter;
import com.adapter.files.UberXSubCategory22Adapter;
import com.dialogs.CommunicationCallTypeDialog;
import com.fragments.deliverall.DeliverAllServiceCategory;
import com.general.SkeletonViewHandler;
import com.general.call.CommunicationManager;
import com.general.files.ActUtils;
import com.general.files.DividerItemDecoration;
import com.general.files.GeneralFunctions;
import com.general.files.GetAddressFromLocation;
import com.general.files.GetLocationUpdates;
import com.general.files.InternetConnection;
import com.general.files.MyApp;
import com.general.files.OpenCatType;
import com.general.files.OpenNoLocationView;
import com.general.files.RecurringTask;
import com.general.files.showTermsDialog;
import com.google.android.gms.maps.MapsInitializer;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.huawei.hms.adapter.AvailableAdapter;
import com.huawei.hms.adapter.internal.AvailableCode;
import com.buddyverse.main.R;
import com.model.ServiceModule;
import com.realmModel.Cart;
import com.service.handler.ApiHandler;
import com.service.server.ServerTask;
import com.skyfishjy.library.RippleBackground;
import com.utils.CommonUtilities;
import com.utils.LayoutDirection;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;
import com.viewpagerdotsindicator.DotsIndicator;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Locale;
import java.util.Objects;

import io.realm.Realm;
import io.realm.RealmResults;

public class HomeDaynamic_22_Fragment extends BaseFragment implements UberXSubCategory22Adapter.OnItemClickList
        , GetAddressFromLocation.AddressFound, GetLocationUpdates.LocationUpdates,
        UberXCategory22Adapter.OnItemClickList {

    GeneralFunctions generalFunc;

    final int UBER_X_SEARCH_SERVICE_REQ_CODE = 201;
    int SEL_STORE = 105;
    String mAddress;
    public String CAT_TYPE_MODE = "0";
    private String userProfileJson, UBERX_PARENT_CAT_ID, vParentCategoryName, parentId = "";
    private String MORE_ICON = "";
    private String latitude = "0.0", longitude = "0.0";

    public boolean isUfxaddress = false;
    boolean isbanner = false;
    public boolean isLoading = false;
    private boolean isback, isMainShimmerLoading = false;

    private int submitBtnId, noColumns;
    private int color;

    private JSONObject obj_userProfile;

    private GetLocationUpdates getLastLocation;
    private GetAddressFromLocation getAddressFromLocation;

    InternetConnection intCheck;

    public ArrayList<String> multiServiceSelect = new ArrayList<>(); //required
    public ArrayList<String> multiServiceCategorySelect = new ArrayList<>(); //required

    private ArrayList<ArrayList<HashMap<String, String>>> generalCategoryWiseList = new ArrayList<>(); //required
    private ArrayList<ArrayList<HashMap<String, String>>> generalCategoryWiseMoreList = new ArrayList<>(); //required
    private ArrayList<HashMap<String, String>> allMainCategoryList = new ArrayList<>();  //required
    private ArrayList<HashMap<String, String>> subCategoryList = new ArrayList<>(); //required
    private HashMap<String, String> mapData = null; //required
    private ArrayList<String> statusBarList;

    private UberXCategory22Adapter ufxCatNewAdapter;
    private UberXSubCategory22Adapter ufxSubCategoryAdapter;
    private HomeCardPagerAdapter mCardAdapter;
    private ShadowTransformer mCardShadowTransformer;

    private DividerItemDecoration itemDecoration;

    public UberXHomeActivity uberXHomeActivity;

    private BottomSheetDialog moreDialog;

    private View view = null, subCategoryToolHomeArea, masterArea, bannerArea, bannerBg, viewGradient;
    private RecyclerView dynamicListRecyclerView, subServiceCategory;
    private MTextView selectServiceTxt, userNameTxt, userSinceTxt;
    public MTextView headerLocAddressHomeTxt;
    private MTextView LocStaticTxt, headerLocAddressTxt, searchTxtView;
    private ImageView backUserImg/*, verticalBar*/, backImgView, bottomRoundfImg;
    private SelectableRoundedImageView userImgView;
    private LinearLayout btnArea, MainLayout, searchArea, subCategoryArea/*, homeLabel*/;
    private MButton btn_type2;
    private RelativeLayout MainTopArea, MainArea;
    private LinearLayout subCategoryToolArea, uberXHeaderLayout;
    private ViewPager bannerViewPager;
    AppCompatDialog noSourceLocationdialog;
    private NestedScrollView MainNsLayout;
    public View mainBannerArea;

    boolean isVideoConsultEnable = false;
    RelativeLayout dotsArea;
    DotsIndicator dotsIndicator;
    String eShowType = "";
    boolean isfirst = true;
    private CommunicationCallTypeDialog mCommunicationCallTypeDialog;
    private boolean mSearchServiceActivityResult = false;
    private RecurringTask mRecurringTask;
    private int currentBannerPosition = 0;


    //This Field is used for check local subcategory data available or not
    boolean iVehicleCategoryIdMatch = false;

    private String deliverallObjStr = "";

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        if (view != null) {
            return view;
        }
        try {
            MapsInitializer.initialize(MyApp.getInstance().getApplicationContext());
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
        view = inflater.inflate(R.layout.activity_uber_xhome_22, container, false);

        generalFunc = MyApp.getInstance().getGeneralFun(getActContext());

        uberXHomeActivity = (UberXHomeActivity) getActivity();

        initView();
        if (ServiceModule.isOnlyMedicalServiceEnable()) {
            color = ContextCompat.getColor(getActContext(), R.color.graybg);
        } else {
            color = ContextCompat.getColor(getActContext(), R.color.white);
        }

        if (MyApp.getInstance().isHMSOnly()) {
            AvailableAdapter availableAdapter = new AvailableAdapter(60400312);
            int result = availableAdapter.isHuaweiMobileServicesAvailable(getActContext());
            if (result != AvailableCode.SUCCESS) {
                availableAdapter.startResolution(getActivity(), result1 -> {
                    Logger.e("HMSResult", "onComplete before result: " + result);
                    Logger.e("HMSResult", "onComplete result: " + result1);
                });
            }
        }

        isUfxaddress = false;
        setUserProfileData("", false);
        setInitToolbarArea();

        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", userProfileJson);
        generalFunc.checkProfileImage(userImgView, url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);

        itemDecoration = new DividerItemDecoration(getActContext(), DividerItemDecoration.VERTICAL_LIST);
        intCheck = new InternetConnection(getActContext());

        if (getArguments() != null) {
            mAddress = getArguments().getString("uberXAddress");
            this.latitude = getArguments().getString("uberXlat");
            this.longitude = getArguments().getString("uberXlong");
            isback = getArguments().getBoolean("isback", false);
            if (!this.latitude.equalsIgnoreCase("0.0") && !this.longitude.equalsIgnoreCase("0.0")) {
                isUfxaddress = true;
                headerLocAddressTxt.setText(mAddress);
                headerLocAddressHomeTxt.setText(mAddress);
            }
        }

        if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
            if (!isUfxaddress) {
                generalFunc.isLocationPermissionGranted(true);
            }
        }

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_BTN_NEXT_TXT"));
        //setRecyclerViewScrollListener();

        // boolean isRideDeliveryUberx = ServiceModule.RideDeliveryUbexProduct;
        boolean isRideDeliveryUberx = false;
        if (isRideDeliveryUberx ||
                ServiceModule.isServiceProviderOnly()) {

            if (isRideDeliveryUberx) {
                uberXHeaderLayout.setVisibility(View.VISIBLE);
            }

            if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
                btnArea.setVisibility(View.GONE);
            } else {
                btnArea.setVisibility(View.VISIBLE);
            }
        } else {
            if (!ServiceModule.isDeliverAllOnly()) {
                btnArea.setVisibility(View.VISIBLE);
                uberXHeaderLayout.setVisibility(View.VISIBLE);
            }
        }


        searchTxtView.setText(generalFunc.retrieveLangLBl("", "LBL_SEARCH_SERVICES"));
        manageHedaer(headerLocAddressHomeTxt);

        if (isback) {
            backImgView.setVisibility(View.VISIBLE);
            manageToolBarAddressView(true);
        }

        ufxCatNewAdapter = new UberXCategory22Adapter(getActContext(), allMainCategoryList, generalCategoryWiseList, generalFunc);

        if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0") || UBERX_PARENT_CAT_ID.equalsIgnoreCase("")) {
            CAT_TYPE_MODE = "0";
            noColumns = getNumOfColumns();
            btnArea.setVisibility(View.GONE);
        } else {
            CAT_TYPE_MODE = "1";
        }
        dynamicListRecyclerView.setAdapter(ufxCatNewAdapter);
        ufxCatNewAdapter.setOnItemClickList(this);

        ufxSubCategoryAdapter = new UberXSubCategory22Adapter(getActContext(), subCategoryList, generalFunc);
        subServiceCategory.setAdapter(ufxSubCategoryAdapter);
        ufxSubCategoryAdapter.setOnItemClickList(this);

        setData();
        allMainCategoryList.clear();
        subCategoryList.clear();

        if (generalFunc.retrieveValue("SERVICE_HOME_DATA") != null && !generalFunc.retrieveValue("SERVICE_HOME_DATA").equalsIgnoreCase("")) {
            manageHomeScreenView(generalFunc.retrieveValue("SERVICE_HOME_DATA"));

        } else {
            SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainTopArea, R.layout.subcategory_shimmer_view_22);
            getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);
        }

        return view;
    }

    private void initView() {

        mainBannerArea = view.findViewById(R.id.mainBannerArea);
        subCategoryToolHomeArea = view.findViewById(R.id.subCategoryToolHomeArea);
        masterArea = view.findViewById(R.id.masterArea);
        bannerArea = view.findViewById(R.id.bannerArea);
        bannerViewPager = (ViewPager) view.findViewById(R.id.bannerViewPager);
        userImgView = view.findViewById(R.id.userImgView);
        backUserImg = view.findViewById(R.id.backUserImg);
        viewGradient = view.findViewById(R.id.viewGradient);
        bottomRoundfImg = view.findViewById(R.id.bottomRoundfImg);
        if (getActivity() instanceof ParentActivity) {
            ((ParentActivity) getActivity()).manageVectorImage(viewGradient, R.drawable.ic_gradient, R.drawable.ic_gradient_compat);
            ((ParentActivity) getActivity()).manageVectorImage(bottomRoundfImg, R.drawable.ic_dayanamic_shapl, R.drawable.ic_dayanamic_shapl_compat);
        }
        userNameTxt = view.findViewById(R.id.userNameTxt);
        userSinceTxt = view.findViewById(R.id.userSinceTxt);
        btn_type2 = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2)).getChildView();
        btnArea = view.findViewById(R.id.btnArea);
        MainLayout = view.findViewById(R.id.MainLayout);
        searchArea = view.findViewById(R.id.searchArea);
        subCategoryArea = view.findViewById(R.id.subCategoryArea);
        MainArea = view.findViewById(R.id.MainArea);
        MainTopArea = view.findViewById(R.id.MainTopArea);
        selectServiceTxt = (MTextView) view.findViewById(R.id.selectServiceTxt);
        headerLocAddressHomeTxt = (MTextView) view.findViewById(R.id.headerLocAddressHomeTxt);
        searchTxtView = view.findViewById(R.id.searchTxtView);
        dynamicListRecyclerView = (RecyclerView) view.findViewById(R.id.dynamicListRecyclerView);
        subServiceCategory = (RecyclerView) view.findViewById(R.id.subServiceCategory);
        subCategoryToolArea = (LinearLayout) view.findViewById(R.id.subCategoryToolArea);
        backImgView = (ImageView) view.findViewById(R.id.backImgView);

        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        uberXHeaderLayout = view.findViewById(R.id.uberXHeaderLayout);
        LocStaticTxt = (MTextView) view.findViewById(R.id.LocStaticTxt);
        headerLocAddressTxt = (MTextView) view.findViewById(R.id.headerLocAddressTxt);
        bannerBg = view.findViewById(R.id.bannerBg);
        MainNsLayout = (NestedScrollView) view.findViewById(R.id.MainNsLayout);
        dotsIndicator = view.findViewById(R.id.dots_indicator);
        dotsArea = view.findViewById(R.id.dotsArea);

        LocStaticTxt.setVisibility(View.GONE);

        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);

        addToClickHandler(btn_type2);
        headerLocAddressHomeTxt.setOnClickListener(new setOnClickLst());
        backImgView.setOnClickListener(new setOnClickLst());
        uberXHeaderLayout.setOnClickListener(new setOnClickLst());
        searchArea.setOnClickListener(new setOnClickLst());
        bannerViewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

            }

            @Override
            public void onPageSelected(int position) {

            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });

        getActivity().getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
        subCategoryToolArea.setBackgroundColor(getActContext().getResources().getColor(R.color.appThemeColor_1));
        subCategoryToolHomeArea.setBackgroundColor(getActContext().getResources().getColor(R.color.appThemeColor_1));
    }

    private boolean isOnlyUfxGrid() {
        if ((ServiceModule.ServiceProvider && this.eShowType.equalsIgnoreCase("Grid"))) {
            return true;
        }
        return false;
    }

    private void showStatusBar() {
        if (Build.VERSION.SDK_INT >= 30) {
            Window window = getActivity().getWindow();
            View decorView = getActivity().getWindow().getDecorView();
            WindowInsetsControllerCompat controllerCompat = new WindowInsetsControllerCompat(window, decorView);
            controllerCompat.show(WindowInsetsCompat.Type.systemBars() | WindowInsetsCompat.Type.navigationBars());
        } else {
            View decorView = getActivity().getWindow().getDecorView(); // Hide the status bar.
            int uiOptions = View.SYSTEM_UI_FLAG_LAYOUT_STABLE;
            decorView.setSystemUiVisibility(uiOptions);
        }
    }


    private void setUserProfileData(String userProfileData, boolean isSet) {
        userProfileJson = isSet ? userProfileData : generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        obj_userProfile = generalFunc.getJsonObject(userProfileJson);
        UBERX_PARENT_CAT_ID = generalFunc.getJsonValueStr(Utils.UBERX_PARENT_CAT_ID, obj_userProfile);
    }

    private void initializeLocation() {
        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            // manageMainCatShimmer();
        } else {
            manageMainSubCatShimmer();
        }
        headerLocAddressHomeTxt.setHint(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"));
        stopLocationUpdates();
        GetLocationUpdates.locationResolutionAsked = false;
        getLastLocation = new GetLocationUpdates(getActContext(), Utils.LOCATION_UPDATE_MIN_DISTANCE_IN_MITERS, true, this);
    }

    public void initializeLocationCheckDone() {
        if (generalFunc == null) {
            generalFunc = new GeneralFunctions(MyApp.getInstance().getCurrentAct());
        }
        if (generalFunc.isLocationPermissionGranted(false) && generalFunc.isLocationEnabled()) {
            if (isUfxaddress) {
                stopLocationUpdates();
                Location temploc = new Location("PickupLoc");

                temploc.setLatitude(Double.parseDouble(latitude));
                temploc.setLongitude(Double.parseDouble(longitude));
                onLocationUpdate(temploc);
            } else {
                initializeLocation();
            }
        } else if (generalFunc.isLocationPermissionGranted(false) && !generalFunc.isLocationEnabled()) {
            if (!generalFunc.retrieveValue("isSmartLoginEnable").equalsIgnoreCase("Yes") ||
                    generalFunc.retrieveValue("isFirstTimeSmartLoginView").equalsIgnoreCase("Yes")) {
                ViewGroup viewGroup = view.findViewById(R.id.MainArea);
                OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
            }
        } else if (isUfxaddress) {
            ViewGroup viewGroup = view.findViewById(R.id.MainArea);
            OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull @NotNull String[] permissions, @NonNull @NotNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        ViewGroup viewGroup = view.findViewById(R.id.MainArea);
        OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
    }

    public void stopLocationUpdates() {
        if (getLastLocation != null) {
            getLastLocation.stopLocationUpdates();
        }
    }

    private void setData() {
        headerLocAddressTxt.setHint(generalFunc.retrieveLangLBl("Enter Location...", "LBL_ENTER_LOC_HINT_TXT"));
        headerLocAddressHomeTxt.setHint(generalFunc.retrieveLangLBl("Enter Location...", "LBL_ENTER_LOC_HINT_TXT"));
        LocStaticTxt.setText(generalFunc.retrieveLangLBl("Location For availing Service", "LBL_LOCATION_FOR_AVAILING_TXT"));


        if (isback) {
            String address = getArguments().getString("address");
            if (address != null && !address.equalsIgnoreCase("")) {
                headerLocAddressTxt.setText(address);
                headerLocAddressHomeTxt.setText(address);
                latitude = getArguments().getString("lat");
                longitude = getArguments().getString("long");
            }
        }
    }

    public Context getActContext() {
        return MyApp.getInstance().getCurrentAct();
    }

    public RealmResults<Cart> getCartData() {
        Realm realm = MyApp.getRealmInstance();
        return realm.where(Cart.class).findAll();
    }

    public void getCategory(String parentId, final String CAT_TYPE_MODE) {
        iVehicleCategoryIdMatch = false;
        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            manageMainCatShimmer();
            isMainShimmerLoading = true;
        } else {
            manageMainSubCatShimmer();
        }

        this.parentId = parentId;

        if (!CAT_TYPE_MODE.equals("0")) {
            subCategoryList.clear();
            manageToolBarAddressView(true);
            MainLayout.setBackgroundColor(color);
            MainArea.setBackgroundColor(color);

            int categoryId = generalFunc.parseIntegerValue(0, UBERX_PARENT_CAT_ID);
            if (categoryId > 0) {
                backImgView.setVisibility(View.GONE);
                uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);
            } else {
                backImgView.setVisibility(View.VISIBLE);
                uberXHomeActivity.binding.rduTopArea.setVisibility(View.GONE);

            }
        } else {
            backImgView.setVisibility(View.GONE);
            manageToolBarAddressView(false);
            uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);
            MainLayout.setBackgroundColor(color);
            MainArea.setBackgroundColor(color);

        }
        if (!CAT_TYPE_MODE.equals("0")) {
            String CateresponseString;

            if (generalFunc.retrieveValue("SubcategoryForAllCategory") != null && mapData.get("eCatType").equals("ServiceProvider")) {
                CateresponseString = generalFunc.retrieveValue("SubcategoryForAllCategory");

                JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, CateresponseString);
                if (messageArray != null) {
                    iVehicleCategoryIdMatch = false;
                    for (int i = 0; i < messageArray.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                        String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp);

                        if (iVehicleCategoryId.equalsIgnoreCase(parentId)) {

                            manageHomeScreenViewLocal(generalFunc.getJsonArray(Utils.message_str, obj_temp));
                            // manageView(LBLresponseString, generalFunc.getJsonValueStr("dataDic", obj_temp));
                            iVehicleCategoryIdMatch = true;
                            break;

                        }
                    }
                }
            }

            if (generalFunc.retrieveValue("SubcategoryForBiddingCategory") != null && mapData.get("eCatType").equals("Bidding")) {
                CateresponseString = generalFunc.retrieveValue("SubcategoryForBiddingCategory");

                JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, CateresponseString);
                if (messageArray != null) {
                    iVehicleCategoryIdMatch = false;
                    for (int i = 0; i < messageArray.length(); i++) {
                        JSONObject obj_temp = generalFunc.getJsonObject(messageArray, i);
                        String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp);

                        if (iVehicleCategoryId.equalsIgnoreCase(mapData.get("iBiddingId"))) {

                            manageHomeScreenViewLocal(generalFunc.getJsonArray(Utils.message_str, obj_temp));
                            // manageView(LBLresponseString, generalFunc.getJsonValueStr("dataDic", obj_temp));
                            iVehicleCategoryIdMatch = true;
                            isVideoConsultEnable = false;
                            if (mapData != null && mapData.get("isVideoConsultEnable") != null && mapData.get("isVideoConsultEnable").equalsIgnoreCase("Yes") && !CAT_TYPE_MODE.equals("0")) {
                                if (mapData.get("VideoConsultSection") != null && mapData.get("VideoConsultSection").equalsIgnoreCase("Yes")) {
//                                    parameters.put("eForVideoConsultation", "Yes");
                                    isVideoConsultEnable = true;
                                }
                            }
                            break;

                        }
                    }
                }
            }


        }


        if (!iVehicleCategoryIdMatch) {
            HashMap<String, String> parameters = new HashMap<String, String>();
            parameters.put("type", "getServiceCategories");
            parameters.put("parentId", "" + parentId);
            parameters.put("userId", generalFunc.getMemberId());
            parameters.put("vLatitude", uberXHomeActivity.mSelectedLatitude);
            parameters.put("vLongitude", uberXHomeActivity.mSelectedLongitude);

            if (mapData != null && mapData.get("eCatType") != null && mapData.get("eCatType").equalsIgnoreCase("Bidding") && !CAT_TYPE_MODE.equals("0")) {
                parameters.put("eCatType", mapData.get("eCatType"));
                parameters.put("parentId", "" + mapData.get("iBiddingId"));
            }
            isVideoConsultEnable = false;
            if (mapData != null && mapData.get("isVideoConsultEnable") != null && mapData.get("isVideoConsultEnable").equalsIgnoreCase("Yes") && !CAT_TYPE_MODE.equals("0")) {
                if (mapData.get("VideoConsultSection") != null && mapData.get("VideoConsultSection").equalsIgnoreCase("Yes")) {
                    parameters.put("eForVideoConsultation", "Yes");
                    isVideoConsultEnable = true;
                }
            }

            ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, responseString -> {
                manageHomeScreenView(responseString);

            });
            exeWebServer.setCancelAble(false);
        }
    }


    private void manageHomeScreenViewLocal(JSONArray mainCataArray) {
        subCategoryList.clear();


        //it's use for deliverAll only
        //if items added in cart then login first time it's redirect to edit cart screen
        if (ServiceModule.isDeliverAllOnly() && !generalFunc.getMemberId().equalsIgnoreCase("") && getCartData() != null &&
                getCartData().size() > 0 && !generalFunc.retrieveValue("IS_DELIVERALL_LOGIN").equalsIgnoreCase("Yes")) {
            generalFunc.storeData("IS_DELIVERALL_LOGIN", "Yes");
            new ActUtils(getActContext()).startAct(EditCartActivity.class);


        }

        isLoading = false;
//                JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);
        int gridCount = 0;
        if (CAT_TYPE_MODE.equals("0")) {
            allMainCategoryList.clear();
        }

//                vParentCategoryName = generalFunc.getJsonValueStr("vParentCategoryName", responseObj);
        int mainCatArraySize = 0;
        if (mainCataArray != null && mainCataArray.length() > 0) {
            mainCatArraySize = mainCataArray.length();
        }
        String LBL_BOOK_NOW = generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW");
        boolean APP_HOME_PAGE_LIST_VIEW_ENABLED = generalFunc.getJsonValue("APP_HOME_PAGE_LIST_VIEW_ENABLED", userProfileJson).equalsIgnoreCase("Yes");

        for (int i = 0; i < mainCatArraySize; i++) {
            HashMap<String, String> map = new HashMap<String, String>();
            JSONObject categoryObj = generalFunc.getJsonObject(mainCataArray, i);
            map.put("eCatType", generalFunc.getJsonValueStr("eCatType", categoryObj));
            map.put("eVideoConsultEnable", generalFunc.getJsonValueStr("eVideoConsultEnable", categoryObj));
            map.put("VideoConsultSection", isVideoConsultEnable ? "Yes" : "No");
            map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", categoryObj));
            map.put("vCategory", generalFunc.getJsonValueStr("vCategory", categoryObj));
            map.put("vLogo_image", generalFunc.getJsonValueStr("vLogo_image", categoryObj));
            map.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj));
            map.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", categoryObj));
            map.put("other", generalFunc.getJsonValueStr("other", categoryObj));
            map.put("vCategoryBanner", generalFunc.getJsonValueStr("vCategoryBanner", categoryObj));
            map.put("vBannerImage", generalFunc.getJsonValueStr("vBannerImage", categoryObj));
            map.put("tBannerButtonText", generalFunc.getJsonValueStr("tBannerButtonText", categoryObj));
            String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", categoryObj);
            map.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
            map.put("LBL_BOOK_NOW", LBL_BOOK_NOW);
            subCategoryList.add((HashMap<String, String>) map.clone());
        }


        // bannerArea.setVisibility(View.VISIBLE);
        bannerArea.setBackgroundColor(Color.parseColor("#f3f3f3"));
        bannerBg.setBackgroundColor(getActContext().getResources().getColor(R.color.appThemeColor_1));
        ufxSubCategoryAdapter = null;
        ufxSubCategoryAdapter = new UberXSubCategory22Adapter(getActContext(), subCategoryList, generalFunc);
        subServiceCategory.setAdapter(ufxSubCategoryAdapter);
        subServiceCategory.getRecycledViewPool().clear();
        ufxSubCategoryAdapter.notifyDataSetChanged();
        ufxSubCategoryAdapter.setOnItemClickList(this);


        if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
            if (generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile) != null && !generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile).equalsIgnoreCase("")) {
                searchArea.setVisibility(View.GONE);
            } else {
                if (generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_SEARCH", obj_userProfile).equalsIgnoreCase("Yes")) {
                    searchArea.setVisibility(View.VISIBLE);
                } else {
                    searchArea.setVisibility(View.GONE);
                }
            }
            mainBannerArea.setVisibility(View.VISIBLE);
        }


        isLoading = false;
        SkeletonViewHandler.getInstance().hideSkeletonView();
        isMainShimmerLoading = false;
        headerLocAddressHomeTxt.setEnabled(true);

    }

    private void manageHomeScreenView(String responseString) {
        JSONObject responseObj = generalFunc.getJsonObject(responseString);
        subCategoryList.clear();
        if (responseObj != null && !responseObj.toString().equals("")) {
            MORE_ICON = generalFunc.getJsonValueStr("MORE_ICON", responseObj);
            boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);
            if (this.CAT_TYPE_MODE.equalsIgnoreCase("0")
                    && !CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                return;
            }
            if (isDataAvail) {

                //it's use for deliverAll only
                //if items added in cart then login first time it's redirect to edit cart screen
                if (ServiceModule.isDeliverAllOnly() && !generalFunc.getMemberId().equalsIgnoreCase("") && getCartData() != null &&
                        getCartData().size() > 0 && !generalFunc.retrieveValue("IS_DELIVERALL_LOGIN").equalsIgnoreCase("Yes")) {
                    generalFunc.storeData("IS_DELIVERALL_LOGIN", "Yes");
                    new ActUtils(getActContext()).startAct(EditCartActivity.class);


                }

                isLoading = false;
                int GRID_TILES_MAX_COUNT = GeneralFunctions.parseIntegerValue(1, generalFunc.getJsonValueStr("GRID_TILES_MAX_COUNT", obj_userProfile));
                JSONArray mainCataArray = generalFunc.getJsonArray("message", responseObj);
                int gridCount = 0;
                if (CAT_TYPE_MODE.equals("0")) {
                    allMainCategoryList.clear();
                }

                vParentCategoryName = generalFunc.getJsonValueStr("vParentCategoryName", responseObj);
                int mainCatArraySize = 0;
                if (mainCataArray != null && mainCataArray.length() > 0) {
                    mainCatArraySize = mainCataArray.length();
                }
                String LBL_BOOK_NOW = generalFunc.retrieveLangLBl("", "LBL_BOOK_NOW");
                boolean APP_HOME_PAGE_LIST_VIEW_ENABLED = generalFunc.getJsonValue("APP_HOME_PAGE_LIST_VIEW_ENABLED", userProfileJson).equalsIgnoreCase("Yes");

                for (int i = 0; i < mainCatArraySize; i++) {
                    HashMap<String, String> map = new HashMap<String, String>();
                    JSONObject categoryObj = generalFunc.getJsonObject(mainCataArray, i);
                    map.put("eCatType", generalFunc.getJsonValueStr("eCatType", categoryObj));
                    map.put("eVideoConsultEnable", generalFunc.getJsonValueStr("eVideoConsultEnable", categoryObj));
                    map.put("VideoConsultSection", isVideoConsultEnable ? "Yes" : "No");
                    map.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", categoryObj));
                    map.put("vCategory", generalFunc.getJsonValueStr("vCategory", categoryObj));
                    map.put("vLogo_image", generalFunc.getJsonValueStr("vLogo_image", categoryObj));
                    map.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj));
                    map.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", categoryObj));
                    map.put("other", generalFunc.getJsonValueStr("other", categoryObj));
                    map.put("vCategoryBanner", generalFunc.getJsonValueStr("vCategoryBanner", categoryObj));
                    map.put("vBannerImage", generalFunc.getJsonValueStr("vBannerImage", categoryObj));
                    map.put("tBannerButtonText", generalFunc.getJsonValueStr("tBannerButtonText", categoryObj));
                    String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", categoryObj);
                    map.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                    map.put("LBL_BOOK_NOW", LBL_BOOK_NOW);
                    subCategoryList.add((HashMap<String, String>) map.clone());
                }

                if (CAT_TYPE_MODE.equals("0")) {
                    JSONArray masterCategoryArr = generalFunc.getJsonArray("HOME_SCREEN_DATA", responseObj);
                    if (masterCategoryArr != null) {
                        generalCategoryWiseMoreList.clear();
                        allMainCategoryList.clear();
                        generalCategoryWiseList.clear();

                        int mParentPos = 0;
                        JSONArray mMedicalServiceSection = null;

                        for (int i = 0; i < masterCategoryArr.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(masterCategoryArr, i);
                            int ListMaxCount = GeneralFunctions.parseIntegerValue(1, generalFunc.getJsonValueStr("ListMaxCount", obj_temp));
                            JSONArray subCategoryArr = generalFunc.getJsonArray("SubCategories", obj_temp);

                            String MedicalServiceSection = generalFunc.getJsonValueStr("MedicalServiceSection", obj_temp);
                            JSONArray MoreSubCategories = generalFunc.getJsonArray("MoreSubCategories", obj_temp);

                            if (MedicalServiceSection.equalsIgnoreCase("Yes")) {
                                if (MoreSubCategories != null) {
                                    mMedicalServiceSection = MoreSubCategories;
                                    mParentPos = i;
                                }
                            }

                            //For Main Category Detail
                            HashMap<String, String> catemap = new HashMap<>();
                            gridCount = 0;

                            //outer
                            catemap.put("vCategoryName", generalFunc.getJsonValueStr("vCategoryName", obj_temp));
                            catemap.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", obj_temp));
                            catemap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", obj_temp));
                            catemap.put("VideoConsultSection", generalFunc.getJsonValueStr("VideoConsultSection", obj_temp));
                            catemap.put("vIconImage", generalFunc.getJsonValueStr("vIconImage", obj_temp));
                            catemap.put("vTextColor", generalFunc.getJsonValueStr("vTextColor", obj_temp));
                            catemap.put("vBgColor", generalFunc.getJsonValueStr("vBgColor", obj_temp));

                            catemap.put("eShowType", generalFunc.getJsonValueStr("eShowType", obj_temp));
                            catemap.put("vBannerImage", generalFunc.getJsonValueStr("vBannerImage", obj_temp));
                            catemap.put("vTitle", generalFunc.getJsonValueStr("vTitle", obj_temp));

                            //For ViewHolder
                            catemap.put("vLogo_image", generalFunc.getJsonValueStr("vLogo_image", obj_temp));

                            catemap.put("vBannerBgColor", generalFunc.getJsonValueStr("vBannerBgColor", obj_temp));
                            catemap.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", obj_temp));
                            catemap.put("ePromoteBanner", generalFunc.getJsonValueStr("ePromoteBanner", obj_temp));
                            catemap.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", obj_temp));

                            catemap.put("vButtonBgColor", generalFunc.getJsonValueStr("vButtonBgColor", obj_temp));
                            catemap.put("vButtonTextColor", generalFunc.getJsonValueStr("vButtonTextColor", obj_temp));
                            catemap.put("eCatType", generalFunc.getJsonValueStr("eCatType", obj_temp));
                            catemap.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", obj_temp));
                            catemap.put("other", generalFunc.getJsonValueStr("other", obj_temp));

                            catemap.put("vCategory", generalFunc.getJsonValueStr("vCategory", obj_temp));
                            catemap.put("eFor", generalFunc.getJsonValueStr("eFor", obj_temp));

                            //For Sub Category Detail
                            ArrayList<HashMap<String, String>> listOfInnerCateMap = new ArrayList<>();
                            ArrayList<HashMap<String, String>> listOfInnerCateMapMore = new ArrayList<>();
                            eShowType = generalFunc.getJsonValueStr("eShowType", obj_temp);
                            if (isOnlyUfxGrid()) {
                                ListMaxCount = GRID_TILES_MAX_COUNT;
                            }
                            HashMap<String, String> innerCateMap = new HashMap<>();

                            if (subCategoryArr != null && subCategoryArr.length() > 0) {

                                try {
                                    //use for local storage subcategory Data of all Uberx category
                                    if (generalFunc.getJsonValueStr("eType", obj_temp).equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                                        MyUtils.updateSubcategoryForAllCategoryType(generalFunc, obj_temp.toString(), latitude, longitude, false);

                                    }
                                    //use for local storage subcategory Data of all Bidding category
                                    if (generalFunc.getJsonValueStr("eType", obj_temp).equalsIgnoreCase("Bidding")) {
                                        MyUtils.updateSubcategoryForAllCategoryType(generalFunc, obj_temp.toString(), latitude, longitude, true);
                                    }

                                    if (generalFunc.getJsonValueStr("eType", obj_temp).equalsIgnoreCase("Deliver")) {
                                        deliverallObjStr = obj_temp.toString();


                                    }
                                } catch (Exception e) {
                                    Logger.d("subcategoryException", ":" + e.getMessage());

                                }
                                for (int j = 0; j < subCategoryArr.length(); j++) {
                                    JSONObject categoryObj = generalFunc.getJsonObject(subCategoryArr, j);
                                    String eShowTerms = generalFunc.getJsonValueStr("eShowTerms", categoryObj);
                                    innerCateMap.put("eShowTerms", Utils.checkText(eShowTerms) ? eShowTerms : "");
                                    if (categoryObj.has("iCategoryId")) {
                                        innerCateMap.put("iCategoryId", generalFunc.getJsonValueStr("iCategoryId", categoryObj));
                                    }
                                    innerCateMap.put("vCategory", generalFunc.getJsonValueStr("vCategory", categoryObj));
                                    innerCateMap.put("eFor", generalFunc.getJsonValueStr("eFor", categoryObj));
                                    //for get pos of list  compare both vCategoryName and iServiceId
                                    innerCateMap.put("vCategoryName", generalFunc.getJsonValueStr("vCategoryName", obj_temp));
                                    innerCateMap.put("tListDescription", generalFunc.getJsonValueStr("tListDescription", obj_temp));
                                    innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", obj_temp));
                                    innerCateMap.put("VideoConsultSection", generalFunc.getJsonValueStr("VideoConsultSection", obj_temp));
                                    innerCateMap.put("eType", generalFunc.getJsonValueStr("eType", obj_temp));
                                    innerCateMap.put("eCatType", generalFunc.getJsonValueStr("eCatType", categoryObj));
                                    innerCateMap.put("isVideoConsultEnable", generalFunc.getJsonValueStr("isVideoConsultEnable", categoryObj));
                                    innerCateMap.put("eVideoConsultEnable", generalFunc.getJsonValueStr("eVideoConsultEnable", categoryObj));
                                    innerCateMap.put("eForMedicalService", generalFunc.getJsonValueStr("eForMedicalService", categoryObj));
                                    if (MoreSubCategories != null) {
                                        innerCateMap.put("vCategoryTitle", generalFunc.getJsonValueStr("vCategoryTitle", obj_temp));
                                        innerCateMap.put("MoreSubCategories", MoreSubCategories.toString());
                                    }
                                    innerCateMap.put("iParentId", generalFunc.getJsonValueStr("iParentId", categoryObj));
                                    innerCateMap.put("iServiceId", generalFunc.getJsonValueStr("iServiceId", categoryObj));
                                    innerCateMap.put("vLogo_image", generalFunc.getJsonValueStr("vLogo_image", categoryObj));
                                    innerCateMap.put("iVehicleCategoryId", generalFunc.getJsonValueStr("iVehicleCategoryId", categoryObj));
                                    innerCateMap.put("iBiddingId", generalFunc.getJsonValueStr("iBiddingId", categoryObj));
                                    innerCateMap.put("other", generalFunc.getJsonValueStr("other", categoryObj));
                                    if (categoryObj.has("eDeliveryType")) {
                                        innerCateMap.put("eDeliveryType", generalFunc.getJsonValueStr("eDeliveryType", categoryObj));
                                    }
                                    innerCateMap.put("eSelectStore", generalFunc.getJsonValueStr("eSelectStore", categoryObj));
                                    innerCateMap.put("vBgColor", generalFunc.getJsonValueStr("vBgColor", categoryObj));
                                    innerCateMap.put("vBorderColor", generalFunc.getJsonValueStr("vBorderColor", categoryObj));
                                    btnArea.setVisibility(View.GONE);
                                    String eShowType = generalFunc.getJsonValueStr("eShowType", categoryObj);
                                    catemap.put("eShowType", eShowType);
                                    innerCateMap.put("eShowType", eShowType);

                                    //add condition for manage lsiting in home screen
                                    if (!APP_HOME_PAGE_LIST_VIEW_ENABLED) {

                                        if (eShowType.equalsIgnoreCase("ICON")
                                                || eShowType.equalsIgnoreCase("ICON-BANNER")) {
                                            catemap.put("eShowType", "List");
                                            innerCateMap.put("eShowType", "List");
                                            if (eShowType.equalsIgnoreCase("ICON-BANNER")) {
                                                catemap.put("eShowType", "Banner");
                                                innerCateMap.put("eShowType", "Banner");
                                            }
                                        } else {
                                        }
                                    } else {
                                        String eCatViewType = generalFunc.getJsonValueStr("eCatViewType", categoryObj);
                                        if (isOnlyUfxGrid()) {
                                            eCatViewType = this.eShowType;
                                        }
                                        if (eCatViewType.equalsIgnoreCase("Icon") || eCatViewType.equalsIgnoreCase("Icon,Banner") ||
                                                eCatViewType.equalsIgnoreCase("Icon,Banner,List") ||
                                                eCatViewType.equalsIgnoreCase("Icon,List")) {
                                            catemap.put("eShowType", "List");
                                            innerCateMap.put("eShowType", "List");


                                            if (eCatViewType.equalsIgnoreCase("Icon")) {
                                                catemap.put("eShowType", "Icon");
                                                innerCateMap.put("eShowType", "Icon");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vLogo_image", categoryObj));
                                            }
                                            if (eCatViewType.equalsIgnoreCase("Icon,Banner")) {
                                                catemap.put("eShowType", "Banner");
                                                innerCateMap.put("eShowType", "Banner");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            }
                                            if (eCatViewType.equalsIgnoreCase("Icon,Banner,List")) {
                                                //Inner and outer
                                                catemap.put("eShowType", "Banner");
                                                innerCateMap.put("eShowType", "Banner");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            }
                                            if (eCatViewType.equalsIgnoreCase("Icon,Banner,List")) {
                                                //Inner and outer
                                                catemap.put("eShowType", "List");
                                                innerCateMap.put("eShowType", "List");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            }
                                            if (eCatViewType.equalsIgnoreCase("Icon,List")) {
                                                catemap.put("eShowType", "List");
                                                innerCateMap.put("eShowType", "List");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            }
                                        } else if (eCatViewType.equalsIgnoreCase("Banner") ||
                                                eCatViewType.equalsIgnoreCase("Banner,List")) {
                                            catemap.put("eShowType", "Banner");
                                            innerCateMap.put("eShowType", "Banner");

                                            //added in list for eCatViewType="  banner list"
                                            if (eCatViewType.equalsIgnoreCase("Banner,List")) {
                                                catemap.put("eShowType", "List");
                                                innerCateMap.put("eShowType", "List");
                                                innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                            }
                                        } else if (eCatViewType.equalsIgnoreCase("Grid")) {
                                            catemap.put("eShowType", "Grid");
                                            innerCateMap.put("eShowType", "Grid");
                                            innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                        } else {
                                            catemap.put("eShowType", "List");
                                            innerCateMap.put("eShowType", "List");
                                            if (this.eShowType.equalsIgnoreCase("CubeXList")) {
                                                catemap.put("eShowType", "CubeXList");
                                                innerCateMap.put("eShowType", "CubeXList");
                                            }
                                            innerCateMap.put("vListLogo", generalFunc.getJsonValueStr("vListLogo", categoryObj));
                                        }
                                    }


                                    if (catemap.get("eShowType").equalsIgnoreCase("ICON") || catemap.get("eShowType").equalsIgnoreCase("Service_Icon")) {
                                        listOfInnerCateMap.add((HashMap<String, String>) innerCateMap.clone());
                                    } else {

                                        if (isOnlyUfxGrid()) {
                                            while (true) {
                                                if (ListMaxCount % 4 != 0) {
                                                    ListMaxCount = ListMaxCount + 1;
                                                } else {
                                                    break;
                                                }
                                            }
                                            if (gridCount < ListMaxCount - 1) {
                                                listOfInnerCateMap.add((HashMap<String, String>) innerCateMap.clone());
                                                if (subCategoryArr.length() > ListMaxCount) {
                                                    listOfInnerCateMapMore.add((HashMap<String, String>) innerCateMap.clone());
                                                }
                                            } else {
                                                listOfInnerCateMapMore.add((HashMap<String, String>) innerCateMap.clone());
                                            }
                                            gridCount = gridCount + 1;


                                        } else {

                                            if (gridCount < ListMaxCount) {
                                                listOfInnerCateMap.add((HashMap<String, String>) innerCateMap.clone());
                                                if (subCategoryArr.length() > ListMaxCount) {
                                                    listOfInnerCateMapMore.add((HashMap<String, String>) innerCateMap.clone());
                                                }
                                            } else {
                                                listOfInnerCateMapMore.add((HashMap<String, String>) innerCateMap.clone());
                                            }
                                            gridCount = gridCount + 1;
                                        }
                                    }

                                }

                                if (!listOfInnerCateMap.isEmpty()) {
                                    allMainCategoryList.add(catemap);

                                    if ((!listOfInnerCateMapMore.isEmpty() || (MedicalServiceSection.equalsIgnoreCase("Yes") && MoreSubCategories != null)) && !eShowType.equalsIgnoreCase("Service_Icon")) {
                                        HashMap<String, String> more = new HashMap<>();
                                        more.put("eShowTerms", "No");
                                        more.put("eShowType", "List");
                                        if (eShowType.equalsIgnoreCase("Service_List")) {
                                            more.put("eShowType", "Service_List");

                                        }

                                        more.put("eType", "");
                                        more.put("vListLogo", MORE_ICON);
                                        more.put("vLogo_image", MORE_ICON);
                                        more.put("vCategoryTitle", generalFunc.getJsonValueStr("vCategoryTitle", obj_temp));
                                        more.put("vCategory", generalFunc.retrieveLangLBl("", "LBL_MORE"));
                                        more.put("eCatType", "More");

                                        if (MedicalServiceSection.equalsIgnoreCase("Yes")) {
                                            if (MoreSubCategories != null) {
                                                more.put("MoreSubCategories", MoreSubCategories.toString());
                                            }
                                            more.put("eForMedicalService", MedicalServiceSection);
                                        }
                                        listOfInnerCateMap.add(more);
                                    }
                                    generalCategoryWiseList.add(i, listOfInnerCateMap);

                                }


                                generalCategoryWiseMoreList.add(i, listOfInnerCateMapMore);
                            } else {
                                allMainCategoryList.add(catemap);
                                generalCategoryWiseList.add(i, listOfInnerCateMap);
                                generalCategoryWiseMoreList.add(i, listOfInnerCateMapMore);
                            }
                        }
                        if (ServiceModule.isOnlyMedicalServiceEnable() && mMedicalServiceSection != null) {

                            ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
                            if (mMedicalServiceSection.length() > 0) {

                                for (int jj = 0; jj < mMedicalServiceSection.length(); jj++) {
                                    HashMap<String, String> innerCateMap = new HashMap<>();
                                    JSONObject moreSubCategoryObj = generalFunc.getJsonObject(mMedicalServiceSection, jj);
                                    JSONArray serviceArr = generalFunc.getJsonArray("Services", moreSubCategoryObj);

                                    innerCateMap.put("vTitle", generalFunc.getJsonValueStr("vTitle", moreSubCategoryObj));
                                    innerCateMap.put("GridView", generalFunc.getJsonValueStr("GridView", moreSubCategoryObj));
                                    innerCateMap.put("Type", "eForMedicalService");
                                    innerCateMap.put("serviceArr", serviceArr.toString());
                                    list_item.add(innerCateMap);
                                }
                            }
                            Drawable backgroundDrawable = DrawableCompat.wrap(viewGradient.getBackground()).mutate();
                            DrawableCompat.setTint(backgroundDrawable, color);

                            dynamicListRecyclerView.setLayoutManager(new LinearLayoutManager(getActContext()));
                            UberXCategoryMoreAdapter ufxCatAdapter = new UberXCategoryMoreAdapter(getActContext(), list_item, mParentPos, generalFunc);
                            dynamicListRecyclerView.setAdapter(ufxCatAdapter);
                            dynamicListRecyclerView.getRecycledViewPool().clear();

                            ufxCatAdapter.setOnItemClickList(new UberXCategoryMoreAdapter.OnItemClickList() {
                                @Override
                                public void onItemClick(int position, int parentPos, HashMap<String, String> mList) {
                                    onItemClickHandle(position, "MORE", parentPos, mList);
                                }

                                @Override
                                public void onMultiItem(String id, boolean b) {

                                }
                            });
                        } else {
                            dynamicListRecyclerView.getRecycledViewPool().clear();
                            ufxCatNewAdapter.notifyDataSetChanged();
                        }
                    }
                } else {
                    dynamicListRecyclerView.setVisibility(View.GONE);
                    subCategoryArea.setVisibility(View.VISIBLE);
                    selectServiceTxt.setText(vParentCategoryName);

                    bannerArea.setBackgroundColor(Color.parseColor("#f3f3f3"));
                    bannerBg.setBackgroundColor(getActContext().getResources().getColor(R.color.appThemeColor_1));

                    subServiceCategory.getRecycledViewPool().clear();
                    ufxSubCategoryAdapter.updateList(subCategoryList);
                }
                JSONArray arr = generalFunc.getJsonArray("BANNER_DATA", responseObj);

                if (arr != null && arr.length() > 0) {
                    isbanner = true;
                    //if (isBannerView && CAT_TYPE_MODE.equals("0")) {
                    bannerArea.setVisibility(View.VISIBLE);
                    mainBannerArea.setVisibility(View.VISIBLE);
                    // }
                    ArrayList<String> imagesList = new ArrayList<String>();
                    statusBarList = new ArrayList<String>();
                    mCardAdapter = new HomeCardPagerAdapter();

                    int bannerWidth = (int) (Utils.getScreenPixelWidth(getActContext()));
                    int bannerHeight = (int) (bannerWidth / 1.77);

                    RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) bannerArea.getLayoutParams();
                    bannerLayoutParams.height = bannerHeight;
                    bannerArea.setLayoutParams(bannerLayoutParams);

                    if (generalFunc.isRTLmode()) {
                        for (int x = arr.length() - 1; x >= 0; x--) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr, x);

                            String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                            String vStatusBarColor = generalFunc.getJsonValueStr("vStatusBarColor", obj_temp);

                            String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, bannerWidth, bannerHeight);

                            imagesList.add(imageURL);
                            statusBarList.add(vStatusBarColor);
                            mCardAdapter.addCardItem(imageURL, getActContext(), bannerHeight);
                        }
                    } else {

                        for (int i = 0; i < arr.length(); i++) {
                            JSONObject obj_temp = generalFunc.getJsonObject(arr, i);

                            String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
                            String vStatusBarColor = generalFunc.getJsonValueStr("vStatusBarColor", obj_temp);

                            String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, bannerWidth, bannerHeight);

                            imagesList.add(imageURL);
                            statusBarList.add(vStatusBarColor);
                            mCardAdapter.addCardItem(imageURL, getActContext(), bannerHeight);
                        }
                    }


                    int imageListSize = imagesList.size();
                    if (imageListSize > 2) {
                        bannerViewPager.setOffscreenPageLimit(3);
                    } else if (imageListSize > 1) {
                        bannerViewPager.setOffscreenPageLimit(2);
                    }
                    mCardShadowTransformer = new ShadowTransformer(bannerViewPager, mCardAdapter);
                    bannerViewPager.setAdapter(mCardAdapter);
                    bannerViewPager.setPageTransformer(false, mCardShadowTransformer);
                    bannerViewPager.setOffscreenPageLimit(3);
                    if (statusBarList != null) {
                        try {
                            getActivity().getWindow().setStatusBarColor(ContextCompat.getColor(getActContext(), R.color.appThemeColor_1));
                        } catch (Exception e) {

                        }
                    }
                    if (arr.length() > 1) {
                        if (generalFunc.isRTLmode()) {
                            bannerViewPager.setCurrentItem(imagesList.size() - 1);
                        }
                        dotsIndicator.setViewPager(bannerViewPager);
                        dotsIndicator.setVisibility(View.VISIBLE);
                        dotsArea.setVisibility(View.VISIBLE);

                        if (mRecurringTask == null) {
                            mRecurringTask = new RecurringTask(5000);
                            mRecurringTask.setTaskRunListener(instance -> {
                                currentBannerPosition++;
                                if (currentBannerPosition < mCardAdapter.getCount()) {
                                    if (generalFunc.isRTLmode()) {
                                        bannerViewPager.setCurrentItem((bannerViewPager.getCurrentItem() - 1), true);
                                    } else {
                                        bannerViewPager.setCurrentItem((bannerViewPager.getCurrentItem() + 1), true);
                                    }
                                } else {
                                    currentBannerPosition = 0;
                                    if (generalFunc.isRTLmode()) {
                                        bannerViewPager.setCurrentItem(imagesList.size() - 1);
                                    } else {
                                        bannerViewPager.setCurrentItem(0, true);
                                    }
                                }
                            });
                            mRecurringTask.avoidFirstRun();
                            mRecurringTask.startRepeatingTask();
                        }

                    } else {
                        dotsArea.setVisibility(View.GONE);
                    }
                } else {
                    isbanner = false;
                    bannerArea.setVisibility(View.GONE);
                    mainBannerArea.setVisibility(View.GONE);

                    if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                        if (generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile) != null && !generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile).equalsIgnoreCase("")) {
                            searchArea.setVisibility(View.GONE);
                        } else {
                            if (generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_SEARCH", obj_userProfile).equalsIgnoreCase("Yes")) {
                                searchArea.setVisibility(View.VISIBLE);
                            } else {
                                searchArea.setVisibility(View.GONE);
                            }
                        }
                        mainBannerArea.setVisibility(View.VISIBLE);
                    }


                }
            } else {
                isLoading = false;
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
            }
        } else {
            generalFunc.showError();
        }
        SkeletonViewHandler.getInstance().hideSkeletonView();
        isMainShimmerLoading = false;
        headerLocAddressHomeTxt.setEnabled(true);

    }

    public static float dpToPixels(int dp, Context context) {
        return dp * (context.getResources().getDisplayMetrics().density);
    }

    @Override
    public void onItemClick(int position) {
        onItemClickHandle(position, "HOME", -1, null);
    }

    @Override
    public void onMultiItem(String id, String category, boolean b) {
        if (b) {
            multiServiceSelect.add(id);
            multiServiceCategorySelect.add(category);
        } else {
            multiServiceSelect.remove(id);
            multiServiceCategorySelect.remove(category);
        }
    }

    @Override
    public void onItemClick(int position, int parentPos) {
        if (headerLocAddressTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("Enter Location...", "LBL_ENTER_LOC_HINT_TXT"))) {
            openSourceLocationView();
            return;
        }
        if (headerLocAddressHomeTxt.getText().toString().equalsIgnoreCase(generalFunc.retrieveLangLBl("", "LBL_LOCATING_YOU_TXT"))) {
            openSourceLocationView();
            return;
        }
        onItemClickHandle(position, "HOME", parentPos, null);


    }

    public void onItemClickHandle(int position, String type, int parentPos, HashMap<String, String> mList) {
        Utils.hideKeyboard(getActContext());
        mapData = null;

        if (type.equalsIgnoreCase("HOME")) {
            if (parentPos == -1) {
                mapData = allMainCategoryList.get(position);
            } else {
                mapData = generalCategoryWiseList.get(parentPos).get(position);
            }

        } else {
            if (mList != null) {
                mapData = mList;
            } else {
                mapData = generalCategoryWiseMoreList.get(parentPos).get(position);
            }
        }
        String eCatType = mapData.get("eCatType");
        if (eCatType != null) {
            String s = eCatType.toUpperCase(Locale.US);
            if (s.equalsIgnoreCase("MORE")) {
                openMoreDialog(parentPos);
                return;
            }


            mapData.put("latitude", latitude);
            mapData.put("longitude", longitude);
            mapData.put("address", headerLocAddressTxt.getText().toString());
            if (!eCatType.equalsIgnoreCase("ServiceProvider")) {
                (new OpenCatType(getActContext(), mapData)).execute();
            }
        }


        if (eCatType != null && (eCatType.equalsIgnoreCase("ServiceProvider") || eCatType.equalsIgnoreCase("Bidding"))) {
            if (CAT_TYPE_MODE.equals("0")) {

                if (mapData.get("other") != null && mapData.get("other").equalsIgnoreCase("Yes")) {

                    Bundle bundle = new Bundle();
                    bundle.putBoolean("isufx", true);
                    bundle.putString("latitude", latitude);
                    bundle.putString("longitude", longitude);
                    bundle.putString("address", headerLocAddressTxt.getText().toString());
                    bundle.putString("SelectvVehicleType", vParentCategoryName);
                    bundle.putString("SelectedVehicleTypeId", mapData.get("iBiddingId"));
                    bundle.putString("parentId", parentId);
                    bundle.putBoolean("isCarwash", true);
                    bundle.putBoolean("isOther", true);
                    bundle.putString("SelectvVehicleType", generalFunc.retrieveLangLBl("", "LBL_OTHER_TXT"));
                    new ActUtils(getActContext()).startActWithData(RequestBidInfoActivity.class, bundle);

                    return;
                }
                if (mapData.get("eForMedicalService") != null && mapData.get("eForMedicalService").equalsIgnoreCase("Yes")) {
                    if (mapData.get("MoreSubCategories") != null) {
                        openMoreDialog(parentPos);
                        return;
                    }
                    if (mCommunicationCallTypeDialog == null) {
                        mCommunicationCallTypeDialog = new CommunicationCallTypeDialog();
                        mCommunicationCallTypeDialog.setListener((mContext, type1, dataProvider) -> {
                            boolean iParentId = mapData.get("iParentId") != null && Objects.requireNonNull(mapData.get("iParentId")).equalsIgnoreCase("0");
                            if (iParentId) {
                                mapData.put("VideoConsultSection", type1 == CommunicationManager.TYPE.VIDEO_CALL ? "Yes" : "No");
                                setSubCategoryList(mapData);
                            } else {
                                openMainActivity(null, null, type1 == CommunicationManager.TYPE.VIDEO_CALL);
                            }
                            if (mCommunicationCallTypeDialog.sheetDialog != null) {
                                mCommunicationCallTypeDialog.sheetDialog.dismiss();
                            }
                        });
                    }
                    boolean eVideoConsultEnable = mapData.get("eVideoConsultEnable") != null && mapData.get("eVideoConsultEnable").equalsIgnoreCase("Yes");
                    if (mList == null && eVideoConsultEnable) {
                        mCommunicationCallTypeDialog.showMedicalServiceSectionDialog(getActContext(), null);
                        return;
                    } else {
                        boolean iParentId = mapData.get("iParentId") != null && Objects.requireNonNull(mapData.get("iParentId")).equalsIgnoreCase("0");
                        if (!iParentId) {
                            openMainActivity(null, null, eVideoConsultEnable);
                            return;
                        }
                    }
                }

                setSubCategoryList(mapData);
                return;
            }
            if (latitude.equalsIgnoreCase("0.0") || longitude.equalsIgnoreCase("0.0")) {
                generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            } else {
                checkServiceAvailableOrNot(mapData.get("iVehicleCategoryId"), mapData.get("vCategory"), latitude, longitude);
            }
        }


    }

    private void openMainActivity(@Nullable String SelectedVehicleTypeId, @Nullable String SelectedCategoryName, boolean mIsVideoConsultEnable) {
        Bundle bundle = new Bundle();
        bundle.putBoolean("isufx", true);
        bundle.putString("latitude", latitude);
        bundle.putString("longitude", longitude);
        bundle.putString("address", headerLocAddressTxt.getText().toString());
        bundle.putString("SelectvVehicleType", vParentCategoryName);

        if (Utils.checkText(SelectedVehicleTypeId)) {
            bundle.putString("SelectedVehicleTypeId", SelectedVehicleTypeId);
            bundle.putString("parentId", parentId);
        } else {
            bundle.putString("SelectedVehicleTypeId", mapData.get("iVehicleCategoryId"));
            bundle.putString("parentId", mapData.get("iParentId"));
        }

        bundle.putBoolean("isCarwash", true);
        bundle.putBoolean("isVideoConsultEnable", mIsVideoConsultEnable);

        if (mapData != null && mapData.get("eCatType") != null && mapData.get("eCatType").equalsIgnoreCase("Bidding")) {
            if (Utils.checkText(SelectedVehicleTypeId) && Utils.checkText(SelectedCategoryName)) {
                bundle.putString("SelectvVehicleType", SelectedCategoryName);
            }
            new ActUtils(getActContext()).startActWithData(RequestBidInfoActivity.class, bundle);
        } else {
            new ActUtils(getActContext()).startActWithData(MainActivity.class, bundle);
        }
    }

    @Override
    public void onMultiItem(String id, boolean b) {
        if (b) {
            multiServiceSelect.add(id);
        } else {
            multiServiceSelect.remove(id);
        }
    }


    public void setInitToolbarArea() {
        manageHedaer(headerLocAddressHomeTxt);
        subCategoryToolArea.setVisibility(View.GONE);
        if (generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile) != null && !generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile).equalsIgnoreCase("")) {
            searchArea.setVisibility(View.GONE);
        } else {
            if (generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_SEARCH", obj_userProfile).equalsIgnoreCase("Yes")) {
                searchArea.setVisibility(View.VISIBLE);
            } else {
                searchArea.setVisibility(View.GONE);
            }
        }
        subCategoryToolHomeArea.setVisibility(View.VISIBLE);
        masterArea.setVisibility(View.GONE);
        bannerArea.setBackgroundColor(getActContext().getResources().getColor(android.R.color.transparent));
        bannerBg.setBackgroundColor(getActContext().getResources().getColor(android.R.color.transparent));
        CAT_TYPE_MODE = "0";

        btnArea.setVisibility(View.GONE);
        uberXHeaderLayout.setVisibility(View.GONE);
        subCategoryArea.setVisibility(View.GONE);
        dynamicListRecyclerView.setVisibility(View.VISIBLE);
    }

    public void setMainCategory() {
        manageHedaer(headerLocAddressHomeTxt);
        if (CAT_TYPE_MODE.equals("1") && UBERX_PARENT_CAT_ID.equalsIgnoreCase("0") && !isLoading) {
            multiServiceSelect.clear();
            multiServiceCategorySelect.clear();
        }
        subCategoryToolArea.setVisibility(View.GONE);
        if (generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile) != null && !generalFunc.getJsonValueStr("UBERX_CAT_ID", obj_userProfile).equalsIgnoreCase("")) {
            searchArea.setVisibility(View.GONE);
        } else {
            if (generalFunc.getJsonValueStr("ENABLE_APP_HOME_SCREEN_SEARCH", obj_userProfile).equalsIgnoreCase("Yes")) {
                searchArea.setVisibility(View.VISIBLE);
            } else {
                searchArea.setVisibility(View.GONE);
            }
        }

        mainBannerArea.setVisibility(View.VISIBLE);
        subCategoryToolHomeArea.setVisibility(View.VISIBLE);
        masterArea.setVisibility(View.GONE);
        bannerArea.setBackgroundColor(getActContext().getResources().getColor(android.R.color.transparent));
        bannerBg.setBackgroundColor(getActContext().getResources().getColor(android.R.color.transparent));
        CAT_TYPE_MODE = "0";

        btnArea.setVisibility(View.GONE);
        uberXHeaderLayout.setVisibility(View.GONE);
        subCategoryArea.setVisibility(View.GONE);
        dynamicListRecyclerView.setVisibility(View.VISIBLE);


        if (statusBarList != null && statusBarList.get(0) != null) {
            bannerArea.setVisibility(View.VISIBLE);

        }


        boolean isRideDeliveryUberx = ServiceModule.ServiceProvider;
        if (isRideDeliveryUberx
                || (ServiceModule.isServiceProviderOnly())) {
            if (isRideDeliveryUberx) {
                uberXHeaderLayout.setVisibility(View.VISIBLE);
            }
            if (multiServiceSelect != null) {
                multiServiceSelect.clear();
            }
        } else {
            uberXHeaderLayout.setVisibility(View.VISIBLE);
        }
    }

    public void manageHedaer(MTextView headerTxtView) {
        Drawable drawable;
        Drawable arrowdrawable;
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.LOLLIPOP) {
            drawable = VectorDrawableCompat.create(getActContext().getResources(), R.drawable.ic_place_address, getActContext().getTheme());
        } else {
            drawable = AppCompatResources.getDrawable(getActContext(), R.drawable.ic_place_address);
        }

        Drawable wrappedDrawable = DrawableCompat.wrap(drawable);
        Drawable mutableDrawable = wrappedDrawable.mutate();
        DrawableCompat.setTint(mutableDrawable, ContextCompat.getColor(getActContext(), R.color.white));

        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.LOLLIPOP) {
            arrowdrawable = VectorDrawableCompat.create(getActContext().getResources(), R.drawable.ic_down_arrow_header, getActContext().getTheme());
        } else {
            arrowdrawable = AppCompatResources.getDrawable(getActContext(), R.drawable.ic_down_arrow_header);
        }

        Drawable arrowwrappedDrawable = DrawableCompat.wrap(arrowdrawable);
        Drawable arrowmutableDrawable = arrowwrappedDrawable.mutate();
        DrawableCompat.setTint(arrowmutableDrawable, ContextCompat.getColor(getActContext(), R.color.white));

        if (generalFunc.isRTLmode()) {
            headerTxtView.setCompoundDrawablesWithIntrinsicBounds(arrowmutableDrawable, null, mutableDrawable, null);
        } else {
            headerTxtView.setCompoundDrawablesWithIntrinsicBounds(mutableDrawable, null, arrowmutableDrawable, null);
        }
    }

    public void setSubCategoryList(HashMap<String, String> dataItem) {
        MainNsLayout.scrollTo(0, 0);
        manageHedaer(headerLocAddressTxt);
        subCategoryToolArea.setVisibility(View.VISIBLE);
        searchArea.setVisibility(View.GONE);
        if (bannerArea.getVisibility() == View.GONE) {
            mainBannerArea.setVisibility(View.GONE);
        }
        subCategoryToolHomeArea.setVisibility(View.GONE);
        masterArea.setVisibility(View.GONE);
        manageMainSubCatShimmer();

        MainNsLayout.fullScroll(View.FOCUS_DOWN);
        MainNsLayout.smoothScrollBy(0, 0);

        CAT_TYPE_MODE = "1";
        dynamicListRecyclerView.setVisibility(View.GONE);
        subCategoryArea.setVisibility(View.VISIBLE);
        uberXHeaderLayout.setVisibility(View.VISIBLE);
        userNameTxt.setVisibility(View.GONE);
        userSinceTxt.setVisibility(View.GONE);
        userImgView.setVisibility(View.GONE);
        backUserImg.setVisibility(View.GONE);
        //homeLabel.setVisibility(View.GONE);
        if (UBERX_PARENT_CAT_ID.equalsIgnoreCase("0")) {
            selectServiceTxt.setText(dataItem.get("vCategory"));
        }
        String iVehicleCategoryId = dataItem.get("iVehicleCategoryId");
        isLoading = true;
        getCategory(iVehicleCategoryId, "1");

        if (generalFunc.getJsonValueStr("SERVICE_PROVIDER_FLOW", obj_userProfile).equalsIgnoreCase("PROVIDER")) {
            btnArea.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        initializeLocationCheckDone();
        if (!mSearchServiceActivityResult) {
            if (generalFunc.prefHasKey(Utils.iServiceId_KEY) && generalFunc != null) {
                generalFunc.removeValue(Utils.iServiceId_KEY);
            }
        }
        mSearchServiceActivityResult = false;
        try {
            if (generalFunc.retrieveValue(Utils.ISWALLETBALNCECHANGE).equalsIgnoreCase("Yes")) {
                getWalletBalDetails();
            } else {
                setUserProfileData("", false);
                setUserInfo();
            }
        } catch (Exception e) {
        }
    }

    @Override
    public void onPause() {
        super.onPause();
    }

    public void getWalletBalDetails() {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "GetMemberWalletBalance");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {

            if (responseString != null && !responseString.equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);

                if (isDataAvail) {
                    try {
                        generalFunc.storeData(Utils.ISWALLETBALNCECHANGE, "No");
                        String userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
                        JSONObject object = generalFunc.getJsonObject(userProfileJson);
                        object.put("user_available_balance", generalFunc.getJsonValue("MemberBalance", responseString));
                        generalFunc.storeData(Utils.USER_PROFILE_JSON, object.toString());
                        setUserProfileData(userProfileJson, true);
                        setUserInfo();
                    } catch (Exception e) {
                    }
                }
            }
        });

    }

    public void setUserInfo() {
        View view = ((Activity) getActContext()).findViewById(android.R.id.content);
        String WalletTextAmount = generalFunc.retrieveLangLBl("", "LBL_WALLET_BALANCE") + ": " + generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("user_available_balance", obj_userProfile));
        userSinceTxt.setText(WalletTextAmount);
        userNameTxt.setText(generalFunc.getJsonValueStr("vName", obj_userProfile) + " "
                + generalFunc.getJsonValueStr("vLastName", obj_userProfile));
        ((MTextView) view.findViewById(R.id.walletbalncetxt)).setText(WalletTextAmount);


        String url = CommonUtilities.USER_PHOTO_PATH + generalFunc.getMemberId() + "/" + generalFunc.getJsonValue("vImgName", userProfileJson);
        generalFunc.checkProfileImage((SelectableRoundedImageView) view.findViewById(R.id.userImgView), url, R.mipmap.ic_no_pic_user, R.mipmap.ic_no_pic_user);

    }

    @Override
    public void onAddressFound(String address, double latitude, double longitude, String geocodeobject) {
        if (isback) {
            if (getArguments() != null && getArguments().getString("address") != null) {
                return;
            }
        }
        if (getArguments() != null && mAddress != null) {
            address = mAddress;
            isUfxaddress = true;
            headerLocAddressTxt.setText(address);
            headerLocAddressHomeTxt.setText(address);
            this.latitude = getArguments().getString("uberXlat");
            this.longitude = getArguments().getString("uberXlong");
        } else {
            if (address != null && !address.equals("")) {
                isUfxaddress = true;
                this.latitude = latitude + "";
                this.longitude = longitude + "";
                headerLocAddressTxt.setText(address);
                headerLocAddressHomeTxt.setText(address);
                if (noSourceLocationdialog != null) {
                    noSourceLocationdialog.dismiss();
                }
            }
        }
        uberXHomeActivity.mSelectedAddress = address;
        DeliverAllServiceCategory.getInstance().loadRestaurantsAllData(this.latitude, this.longitude);
    }

    private void openSourceLocationView() {
        if (noSourceLocationdialog != null) {
            noSourceLocationdialog.dismiss();
            noSourceLocationdialog = null;
        }
        noSourceLocationdialog = new AppCompatDialog(getActContext(), android.R.style.Theme_Translucent_NoTitleBar);
        noSourceLocationdialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        noSourceLocationdialog.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        noSourceLocationdialog.setContentView(R.layout.no_source_location_design);
        Objects.requireNonNull(noSourceLocationdialog.getWindow()).getAttributes().windowAnimations = R.style.DialogAnimation;
        noSourceLocationdialog.setCancelable(false);

        ((RippleBackground) noSourceLocationdialog.findViewById(R.id.rippleBgView)).startRippleAnimation();
        ImageView closeImage = noSourceLocationdialog.findViewById(R.id.closeImage);
        closeImage.setOnClickListener(v -> {
            noSourceLocationdialog.dismiss();
        });
        MTextView locationHintText = noSourceLocationdialog.findViewById(R.id.locationHintText);
        MTextView locationDescText = noSourceLocationdialog.findViewById(R.id.locationDescText);
        MTextView btnTxt = noSourceLocationdialog.findViewById(R.id.btnTxt);
        ImageView btnImg = noSourceLocationdialog.findViewById(R.id.btnImg);
        LinearLayout btnArea = noSourceLocationdialog.findViewById(R.id.btnArea);

        DisplayMetrics displayMetrics = new DisplayMetrics();
        getActivity().getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);
        int height = displayMetrics.heightPixels;

        RippleBackground.LayoutParams buttonLayoutParams = new RippleBackground.LayoutParams(RippleBackground.LayoutParams.MATCH_PARENT, RippleBackground.LayoutParams.MATCH_PARENT);
        buttonLayoutParams.setMargins(0, 0, 0, -(height / 2));
        ((RippleBackground) noSourceLocationdialog.findViewById(R.id.rippleBgView)).setLayoutParams(buttonLayoutParams);

        if (generalFunc.isRTLmode()) {
            btnImg.setRotation(180);
            btnArea.setBackground(getActContext().getResources().getDrawable(R.drawable.login_border_rtl));
        }

        btnTxt.setText(generalFunc.retrieveLangLBl("ENTER", "LBL_ADD_ADDRESS_TXT"));
        locationDescText.setText(generalFunc.retrieveLangLBl("Please wait while we are trying to access your location. meanwhile you can enter your source location.", "LBL_FETCHING_LOCATION_NOTE_TEXT"));
        locationHintText.setText(generalFunc.retrieveLangLBl("Location", "LBL_LOCATION_FOR_FRONT"));

        btnArea.setOnClickListener(v -> {
            Bundle bn = new Bundle();
            bn.putString("locationArea", "source");
            if (!latitude.equalsIgnoreCase("0.0") && !longitude.equalsIgnoreCase("0.0")) {
                bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
                bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
            }
            bn.putString("address", headerLocAddressTxt.getText().toString() + "");

            new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class,
                    bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);

        });
        noSourceLocationdialog.show();
    }

    public void manageMainCatShimmer() {
        headerLocAddressHomeTxt.setEnabled(false);
        if (isfirst) {
            isfirst = false;
            return;
        }
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainTopArea, R.layout.subcategory_shimmer_view_22);
    }

    public void manageMainSubCatShimmer() {
        headerLocAddressHomeTxt.setEnabled(true);
        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(MainTopArea, R.layout.subcategory_shimmer_view_22);
    }

    public void configCategoryView() {
        MainNsLayout.scrollTo(0, 0);
        setMainCategory();
    }

    public void releaseResources() {
        if (getAddressFromLocation != null) {
            getAddressFromLocation.setAddressList(null);
            getAddressFromLocation = null;
        }
    }

    @Override
    public void onLocationUpdate(Location mLastLocation) {
        stopLocationUpdates();
        latitude = mLastLocation.getLatitude() + "";
        longitude = mLastLocation.getLongitude() + "";
        uberXHomeActivity.mSelectedLatitude = latitude;
        uberXHomeActivity.mSelectedLongitude = longitude;
        isUfxaddress = true;
        if (getAddressFromLocation == null) {
            getAddressFromLocation = new GetAddressFromLocation(getActContext(), generalFunc);
            getAddressFromLocation.setLocation(mLastLocation.getLatitude(), mLastLocation.getLongitude());
            getAddressFromLocation.setAddressList(this);
            getAddressFromLocation.execute();
            if (uberXHomeActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes")) {
                getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);
            }
            if (!isMainShimmerLoading) {
                SkeletonViewHandler.getInstance().hideSkeletonView();
                headerLocAddressHomeTxt.setEnabled(true);
            }
        }
    }

    public void checkServiceAvailableOrNot(String iVehicleCategoryId, String vCategory, String latitude, String
            longitude) {
        HashMap<String, String> parameters = new HashMap<String, String>();
        parameters.put("type", "getServiceCategoryTypes");
        parameters.put("iVehicleCategoryId", iVehicleCategoryId);
        parameters.put("userId", generalFunc.getMemberId());
        parameters.put("vLatitude", latitude);
        parameters.put("vLongitude", longitude);
        parameters.put("UserType", Utils.userType);
        parameters.put("eCheck", "Yes");

        final ServerTask exeWebServer = ApiHandler.execute(getActContext(), parameters, true, false, generalFunc, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            if (responseObj != null && !responseObj.toString().equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseObj);

                if (isDataAvail) {
                    Bundle bundle = new Bundle();
                    bundle.putString("iVehicleCategoryId", iVehicleCategoryId);
                    bundle.putString("vCategoryName", vCategory);
                    bundle.putString("latitude", latitude);
                    bundle.putString("longitude", longitude);
                    bundle.putString("address", headerLocAddressTxt.getText().toString());
                    new ActUtils(getActContext()).startActWithData(UberXSelectServiceActivity.class, bundle);
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr("message", responseObj)));
                }
            } else {
                generalFunc.showError();
            }
        });
        exeWebServer.setCancelAble(false);
    }


    public void onClickView(View view) {
        int i = view.getId();
        if (i == submitBtnId) {
            if (latitude.equalsIgnoreCase("0.0") || longitude.equalsIgnoreCase("0.0")) {
                generalFunc.showMessage(view, generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
            } else {
                String SelectedVehicleTypeId = "";
                String SelectedCategoryName = "";
                if (multiServiceSelect.size() > 0) {
                    SelectedVehicleTypeId = android.text.TextUtils.join(",", multiServiceSelect);
                    SelectedCategoryName = android.text.TextUtils.join(",", multiServiceCategorySelect);
                } else {
                    generalFunc.showMessage(view, generalFunc.retrieveLangLBl("Please Select Service", "LBL_SELECT_SERVICE_TXT"));
                    return;
                }
                openMainActivity(SelectedVehicleTypeId, SelectedCategoryName, isVideoConsultEnable);
            }
        }
    }


    public class setOnClickLst implements View.OnClickListener {
        @Override
        public void onClick(View v) {
            Utils.hideKeyboard(getActContext());
            int id = v.getId();
            if (id == R.id.searchArea) {
                new ActUtils(getActContext()).startActForResult(SearchServiceActivity.class, UBER_X_SEARCH_SERVICE_REQ_CODE);
            } else if (id == R.id.headerLocAddressHomeTxt || id == R.id.uberXHeaderLayout) {
                Bundle bn = new Bundle();
                bn.putString("locationArea", "source");
                if (latitude != null && !latitude.equalsIgnoreCase("0.0") && longitude != null && !longitude.equalsIgnoreCase("0.0")) {
                    bn.putDouble("lat", GeneralFunctions.parseDoubleValue(0.0, latitude));
                    bn.putDouble("long", GeneralFunctions.parseDoubleValue(0.0, longitude));
                }
                bn.putString("address", headerLocAddressHomeTxt.getText().toString() + "");

                new ActUtils(getActContext()).startActForResult(SearchLocationActivity.class,
                        bn, Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE);
            } else if (id == R.id.backImgView) {
                if (CAT_TYPE_MODE.equals("1") && UBERX_PARENT_CAT_ID.equalsIgnoreCase("0") && !isLoading) {
                    multiServiceSelect.clear();
                    multiServiceCategorySelect.clear();
                    backImgView.setVisibility(View.GONE);
                    manageToolBarAddressView(false);
                    uberXHomeActivity.binding.rduTopArea.setVisibility(View.VISIBLE);

                    MainLayout.setBackgroundColor(color);
                    MainArea.setBackgroundColor(color);
                    //selectServiceTxt.setBackgroundColor(color);

                    configCategoryView();
                    return;
                }
            }
        }
    }

    public void manageToolBarAddressView(boolean isback) {
        if (isback) {
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            int size2sdp = (int) getActContext().getResources().getDimension(R.dimen._2sdp);
            int size15sdp = (int) getActContext().getResources().getDimension(R.dimen._15sdp);
            params.setMargins(generalFunc.isRTLmode() ? size15sdp : size2sdp, 0, generalFunc.isRTLmode() ? size2sdp : size15sdp, 0);
            headerLocAddressTxt.setLayoutParams(params);
        } else {
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
            int size20sdp = (int) getActContext().getResources().getDimension(R.dimen._20sdp);
            int size15sdp = (int) getActContext().getResources().getDimension(R.dimen._15sdp);
            params.setMargins(generalFunc.isRTLmode() ? size15sdp : size20sdp, 0, generalFunc.isRTLmode() ? size20sdp : size15sdp, 0);
            headerLocAddressTxt.setLayoutParams(params);
        }
    }

    private void openMoreDialog(int pos) {
        if (generalCategoryWiseMoreList.size() == 0) {
            return;
        }
        if (moreDialog != null && moreDialog.isShowing()) {
            return;
        }
        moreDialog = new BottomSheetDialog(getActContext());

        View contentView = View.inflate(getActContext(), R.layout.dialog_more, null);
        moreDialog.setContentView(contentView);
        moreDialog.setCancelable(false);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());

        /*int bannerHeight = (int) (Utils.getScreenPixelHeight(getActContext()) * 0.40);
        mBehavior.setPeekHeight(bannerHeight);
        mBehavior.setHideable(true);*/

        MTextView moreTitleTxt = contentView.findViewById(R.id.moreTitleTxt);
        MTextView cancelTxt = contentView.findViewById(R.id.cancelTxt);
        ImageView closeVideoView = contentView.findViewById(R.id.closeVideoView);
        String vCategoryTitle = mapData.get("vCategoryTitle");
        if (Utils.checkText(vCategoryTitle)) {
            moreTitleTxt.setText(vCategoryTitle);
        } else {
            moreTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SELECT_SERVICE"));
        }
        cancelTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CANCEL_TXT"));
        cancelTxt.setOnClickListener(v -> moreDialog.dismiss());
        closeVideoView.setOnClickListener(v -> moreDialog.dismiss());

        RecyclerView dataListRecyclerView_more = contentView.findViewById(R.id.dataListRecyclerView_more);

        UberXCategoryMoreAdapter ufxCatAdapter;
        if (mapData.get("eForMedicalService") != null && mapData.get("eForMedicalService").equalsIgnoreCase("Yes")) {

            ArrayList<HashMap<String, String>> list_item = new ArrayList<>();
            try {
                if (mapData.get("MoreSubCategories") != null) {
                    JSONArray MoreSubCategories = new JSONArray(mapData.get("MoreSubCategories"));
                    if (MoreSubCategories.length() > 0) {
                        for (int jj = 0; jj < MoreSubCategories.length(); jj++) {
                            HashMap<String, String> innerCateMap = new HashMap<>();
                            JSONObject moreSubCategoryObj = generalFunc.getJsonObject(MoreSubCategories, jj);
                            JSONArray serviceArr = generalFunc.getJsonArray("Services", moreSubCategoryObj);

                            innerCateMap.put("vTitle", generalFunc.getJsonValueStr("vTitle", moreSubCategoryObj));
                            innerCateMap.put("GridView", generalFunc.getJsonValueStr("GridView", moreSubCategoryObj));
                            innerCateMap.put("Type", "eForMedicalService");
                            innerCateMap.put("serviceArr", serviceArr.toString());
                            list_item.add(innerCateMap);
                        }
                    }
                }
            } catch (JSONException e) {
                Logger.e("Exception", "::" + e.getMessage());
            }
            dataListRecyclerView_more.setLayoutManager(new LinearLayoutManager(getActContext()));
            ufxCatAdapter = new UberXCategoryMoreAdapter(getActContext(), list_item, pos, generalFunc);

            int bannerHeight = (int) (Utils.getScreenPixelHeight(getActContext()) - getActContext().getResources().getDimensionPixelSize(R.dimen._50sdp));
            new Handler(Looper.getMainLooper()).postDelayed(() -> {
                if (bannerHeight > dataListRecyclerView_more.getMeasuredHeight()) {
                    LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) dataListRecyclerView_more.getLayoutParams();
                    params.height = bannerHeight;
                    dataListRecyclerView_more.setLayoutParams(params);
                }
            }, 20);
            mBehavior.setDraggable(false);
        } else {
            dataListRecyclerView_more.setLayoutManager(new GridLayoutManager(getActContext(), noColumns));
            ufxCatAdapter = new UberXCategoryMoreAdapter(getActContext(), generalCategoryWiseMoreList.get(pos), pos, generalFunc);
        }
        //UberXCategoryAdapter ufxCatAdapter = new UberXCategoryAdapter(getActContext(), generalCategoryWiseMoreList.get(pos), generalFunc, true);
        dataListRecyclerView_more.setAdapter(ufxCatAdapter);
        mBehavior.setState(BottomSheetBehavior.STATE_EXPANDED);
        mBehavior.setHideable(false);

        ufxCatAdapter.setOnItemClickList(new UberXCategoryMoreAdapter.OnItemClickList() {
            @Override
            public void onItemClick(int position, int parentPos, HashMap<String, String> mList) {
                moreDialog.cancel();
                if (mapData.get("eForMedicalService") != null && mapData.get("eForMedicalService").equalsIgnoreCase("Yes")) {
                    onItemClickHandle(position, "MORE", parentPos, mList);
                } else {
                    onItemClickHandle(position, "MORE", pos, null);
                }
            }

            @Override
            public void onMultiItem(String id, boolean b) {

            }
        });
        LayoutDirection.setLayoutDirection(moreDialog);
        moreDialog.show();
    }

    public Integer getNumOfColumns() {
        try {
            DisplayMetrics displayMetrics = getActContext().getResources().getDisplayMetrics();
            float dpWidth = (displayMetrics.widthPixels - Utils.dipToPixels(getActContext(), 10)) / displayMetrics.density;
            /*int margin_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._10sdp) * 2;
            int menuItem_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp) * 2;*/
            int margin_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._10sdp);
            int menuItem_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen._5sdp);
            int catWidth_int_value = getActContext().getResources().getDimensionPixelSize(R.dimen.category_grid_size_more);
            int screenWidth_int_value = displayMetrics.widthPixels - margin_int_value - menuItem_int_value;
            int noOfColumns = (int) (screenWidth_int_value / catWidth_int_value);
            return noOfColumns;
        } catch (Exception e) {
        }
        return -1;
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        // super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == Utils.MY_PROFILE_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            setUserProfileData("", false);
        } else if (requestCode == SEL_STORE && resultCode == getActivity().RESULT_OK && data != null) {
            String vServiceAddress = data.getStringExtra("vServiceAddress");
            String vStoreName = data.getStringExtra("vstorename");
            //addressStoreTxt!!.text = data.getStringExtra("vServiceAddress")
            //StoreNameTxt!!.text = data.getStringExtra("vstorename")
            //addressStoreTxt !!.visibility = View.VISIBLE
            //isStoreAdded = true;
            String storeLatitude = "";
            if (data.getStringExtra("vLatitude") == null) {
                storeLatitude = "0.0";
            } else {
                storeLatitude = data.getStringExtra("vLatitude");
            }

            String storeLongitude = "";
            if (data.getStringExtra("vLongitude") == null) {
                storeLongitude = "0.0";
            } else {
                storeLongitude = data.getStringExtra("vLongitude");
            }
            Boolean isStoreAdded = false;
            if (!storeLatitude.equalsIgnoreCase("0.0") && !storeLongitude.equalsIgnoreCase("0.0")
            ) {
                isStoreAdded = true;
            }

            Bundle bundle = new Bundle();
            bundle.putString("vServiceAddress", vServiceAddress);
            bundle.putString("vStoreName", vStoreName);
            bundle.putString("latitude", latitude);
            bundle.putString("longitude", longitude);
            bundle.putBoolean("isStoreAdded", isStoreAdded);
            bundle.putString("vCategory", mapData.get("vCategory"));
            bundle.putString("eSelectStore", "Yes");
            new ActUtils(getActContext()).startActWithData(BuyAnythingActivity.class, bundle);

        } else if (requestCode == Utils.UBER_X_SEARCH_PICKUP_LOC_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            headerLocAddressTxt.setText(data.getStringExtra("Address"));
            headerLocAddressHomeTxt.setText(data.getStringExtra("Address"));
            this.latitude = data.getStringExtra("Latitude") == null ? "0.0" : data.getStringExtra("Latitude");
            this.longitude = data.getStringExtra("Longitude") == null ? "0.0" : data.getStringExtra("Longitude");
            if (!this.latitude.equalsIgnoreCase("0.0") && !this.longitude.equalsIgnoreCase("0.0")) {
                isUfxaddress = true;
            }
            uberXHomeActivity.mSelectedAddress = data.getStringExtra("Address");
            uberXHomeActivity.mSelectedLatitude = latitude;
            uberXHomeActivity.mSelectedLongitude = longitude;
            if (uberXHomeActivity.ENABLE_LOCATION_WISE_BANNER.equalsIgnoreCase("Yes") || isUfxaddress) {
                if (noSourceLocationdialog != null) {
                    noSourceLocationdialog.dismiss();
                    noSourceLocationdialog = null;
                }
                if (CAT_TYPE_MODE.equalsIgnoreCase("0")) {
                    getCategory(UBERX_PARENT_CAT_ID, CAT_TYPE_MODE);
                    DeliverAllServiceCategory.getInstance().loadRestaurantsAllData(this.latitude, this.longitude);
                } else {
                    if (mapData == null) {
                        getCategory(UBERX_PARENT_CAT_ID, "1");
                    } else {
                        String iVehicleCategoryId = mapData.get("iVehicleCategoryId");
                        getCategory(iVehicleCategoryId, "1");
                    }
                }
            }
        } else if (requestCode == Utils.CARD_PAYMENT_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            setUserProfileData("", false);
        } else if (requestCode == Utils.SEARCH_PICKUP_LOC_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            isUfxaddress = true;
            headerLocAddressTxt.setText(data.getStringExtra("Address"));
            headerLocAddressHomeTxt.setText(data.getStringExtra("Address"));
            this.latitude = data.getStringExtra("Latitude");
            this.longitude = data.getStringExtra("Longitude");
        } else if (requestCode == UBER_X_SEARCH_SERVICE_REQ_CODE && resultCode == getActivity().RESULT_OK && data != null) {
            if (data.hasExtra("selectedItem")) {
                HashMap<String, String> mapData = (HashMap<String, String>) data.getSerializableExtra("selectedItem");
                this.mapData = mapData;
                if (mapData.get("iParentId").equals("0") && (mapData.get("eCatType").equalsIgnoreCase("ServiceProvider") || mapData.get("eCatType").equalsIgnoreCase("Bidding"))) {
                    if (CAT_TYPE_MODE.equals("0")) {
                        setSubCategoryList(mapData);
                        return;
                    }
                    if (latitude.equalsIgnoreCase("0.0") || longitude.equalsIgnoreCase("0.0")) {
                        generalFunc.showMessage(generalFunc.getCurrentView((Activity) getActContext()), generalFunc.retrieveLangLBl("", "LBL_SET_LOCATION"));
                    } else {
                        checkServiceAvailableOrNot(mapData.get("iVehicleCategoryId"), mapData.get("vCategory"), latitude, longitude);
                    }
                } else {
                    if (mapData != null && mapData.get("eCatType") != null && mapData.get("eCatType").equalsIgnoreCase("Bidding")) {
                        Bundle bundle = new Bundle();
                        bundle.putBoolean("isufx", true);
                        bundle.putString("latitude", latitude);
                        bundle.putString("longitude", longitude);
                        bundle.putString("address", headerLocAddressTxt.getText().toString());
                        bundle.putString("SelectvVehicleType", mapData.get("vCategory"));
                        bundle.putString("SelectedVehicleTypeId", mapData.get("iBiddingId"));
                        bundle.putString("parentId", parentId);
                        bundle.putBoolean("isCarwash", true);
                        new ActUtils(getActContext()).startActWithData(RequestBidInfoActivity.class, bundle);
                        return;
                    }

                    mSearchServiceActivityResult = true;
                    if (data.getStringExtra("eShowTerms") != null && data.getStringExtra("eShowTerms").equalsIgnoreCase("yes")
                            && !CommonUtilities.ageRestrictServices.contains("1")) {
                        new showTermsDialog(uberXHomeActivity, generalFunc, 0, mapData.get("vCategory"), true, () -> {
                            mapData.put("latitude", latitude);
                            mapData.put("longitude", longitude);
                            mapData.put("address", headerLocAddressTxt.getText().toString());
                            (new OpenCatType(getActContext(), mapData)).execute();


                        });
                        return;
                    }
                    mapData.put("latitude", latitude);
                    mapData.put("longitude", longitude);
                    mapData.put("address", headerLocAddressTxt.getText().toString());
                    (new OpenCatType(uberXHomeActivity, mapData)).execute();
                }

            }
        } else if (requestCode == Utils.VERIFY_MOBILE_REQ_CODE) {
            ViewGroup viewGroup = view.findViewById(R.id.MainArea);
            OpenNoLocationView.getInstance(uberXHomeActivity, viewGroup).configView(false);
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        stopLocationUpdates();
        releaseResources();
        showStatusBar();
    }
}